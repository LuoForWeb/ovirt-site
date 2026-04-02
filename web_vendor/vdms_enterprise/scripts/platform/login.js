var Login = function () {
	var _TYPE, _VENDER, _OS;
	var passTips ="";
	var _USERUUID = "";
	var  _FailNum=window.localStorage;
	var _CodeState=-1;//验证码的状态
	var _CodeShow=false; //验证码是否显示，默认为否
	var loginFlag = false;


	var handleLogin = function() {
		$('.login-form').validate({
			errorElement: 'span',
			errorClass: 'help-block',
			focusInvalid: false,
			rules: {
				username: {
					required: true
				},
				password: {
					required: true
				},
				remember: {
					required: false
				}
			},

			messages: {
				username: {
					required: `<i class="viconfont vicon-error-warning-fill"></i>` + LANG.UI_ISCSI_CHAT_USERNAME_TIPS
				},
				password: {
					required: `<i class="viconfont vicon-error-warning-fill"></i>` + LANG.UI_ISCSI_CHAT_PASSWORD_TIPS
				}
			},
			invalidHandler: function (event, validator) {
				$('.alert', $('.login-form')).show();
			},

			highlight: function (element) {
				$(element).closest('.form-group').addClass('has-error');
				//去掉返回的提示信息
				$('#login_tip', $('.login-form')).hide();
			},
			unhighlight: function(element){
				$(element).closest('.form-group').removeClass('has-error');
			},
			errorPlacement: function (error, element) {
				error.insertAfter(element.closest('.input-icon'));
			},

			submitHandler: function (form) {
				 submit();
			}
		});
	}
	var submit = function(){
		var encrypt = new JSEncrypt();
		encrypt.setPublicKey(CONF.PUBLIC_KEY);
		var username = encrypt.encrypt($.trim($('input[name=username]').val()));
		var password = encrypt.encrypt($('input[name=password]').val());
		var very_code = ($('input[name=very_code]').val());
		var remember=false;
		if($('.form-actions .remPwd').is(':checked')){
			remember=true;
		}
		var data = JSON.stringify({username:username,password:password,very_code:very_code, remember:remember});
		//  如果有验证码的情况先比对验证码
		if(_CodeShow && very_code == ''){
			// 有验证码但是没有输入 提示验证码必须
			tips=LANG.UI_LOGIN_EMPTY_VEFIFY_CODE;
			$('#login_tip', $('.login-form')).html(tips).show();
		}else{
			let data = {};
			data['username'] = username;
			data['password'] = password;
			data['very_code'] = very_code;
			data['remember'] = remember;
			data['loginFlag'] = loginFlag;
			pAjaxRequest(data, '/api/v1/login', 'post', loginResult);
		}
	}
	var loginResult = function(data){
		$('#login_tip', $('.login-form')).hide()
		//退出登录，移除exchange用于恢复页面验证身份的session
		sessionStorage.removeItem('exchange_recovery_pass');
		// var loginResult = JSON.parse(data);
		var loginResult = data.data;
		var loginErrorCount=loginResult.faliCount;//获取登录错误次数
		var tips = '<i class="viconfont vicon-error-warning-fill"></i>';
		//用户名密码正确
		if(1 == loginResult.result) {
				if ($.trim($('input[name=username]').val()) == _FailNum['username']) {
					_FailNum['count'] = 0;
					_FailNum['username'] = '';
				}
				localStorage.removeItem('visited');
				window.location = './';
				//任务页面清除分页配置信息
				sessionStorage.removeItem('current_table_pageRecord');
				sessionStorage.removeItem('history_table_pageRecord');
				tips = '';
				return;
		}else if(2 == loginResult.result){ //  用户名或密码错误
			_FailNum={
				'username': loginResult.username,
				'count' : loginErrorCount,
			}
			var loginCount = loginResult.maxCount - loginResult.faliCount;
			var tipFail = '';
			if( loginCount>0 && loginCount <= 5 && loginResult.usertype != 3){
			 	tipFail = LANG.UI_LOGIN_LOGIN_COUNT+loginCount;
			}
			if(_FailNum['count']>=3 ) {  //有验证码的情况
				 refresh_code();  //刷新验证码
				_CodeShow=true;
				$('.input-val').val('');
				$(".Code").show();
			}
			$('.form-group').addClass('has-error');
			tips += LANG.UI_LOGIN_ERROR_TIPS+tipFail;
		}else if(3 == loginResult.result){
			tips += LANG.UI_LOGIN_USER_LOCKED_TIPS;  //用户被锁定
		}else if(4 == loginResult.result){
			tips += LANG.UI_PLATFORM_SERVER_CONNEC_ERROR;  //域服务器连接错误
		}else if(5 == loginResult.result){
			// 过期修改密码
			_USERUUID= loginResult.useruuid;
			window.sessionStorage.setItem('useruuid',_USERUUID);
			window.location.href = '/edit_pwd.php?show='+'over_time';
			return;
		}else if(6 == loginResult.result){
			tips += LANG.UI_LOGIN_ERROR_TIPS;
		}else if(9 == loginResult.result){
			// 验证码错误
			tips+=LANG.UI_LOGIN_ERROR_VERIFY_CODE;
			$('.input-val').val('');
			refresh_code();
		}else if(10 == loginResult.result){
			$('input[name = very_code]').val('');
			//限制十分钟
			var minutes = Math.floor(loginResult.time/60);
			var remainingSeconds = loginResult.time % 60;
			minutes == 0 ? tips += LANG.UI_LOGIN_PLEASE+ remainingSeconds + LANG.UI_LOGIN_AFTER_SECOND : tips += LANG.UI_LOGIN_PLEASE+minutes+LANG.UI_PUBLIC_MINUTE+remainingSeconds+LANG.UI_LOGIN_AFTER_SECOND;
		}else if(11 == loginResult.result){
			_USERUUID= loginResult.useruuid;
			window.sessionStorage.setItem('useruuid',_USERUUID);
			if(loginResult.usertype == 3){
				//admin用户需要选择是否修改密码
				$('.overlay').show();
				$('.edit_pwd_button').on('click', function(event){
					event.preventDefault();
					window.location.href = './edit_pwd.php?key='+loginResult.tokenData+'&show='+'first_login';
				})
				$('.login_success_button').on('click', function(event){
					event.preventDefault();
					loginFlag = true;
					submit();
				});
			}else{
				window.location.href = './edit_pwd.php?key='+loginResult.tokenData+'&show='+'first_login';
			}
			return;
		}else if(12 == loginResult.result){
			tips += LANG.UI_LOGIN_USER_LOGIN_CONTACT_MANAGER;
			$('.input-val').val('');
			refresh_code();
		}
		 $('.alert', $('.login-form')).show();
		 $('#login_tip', $('.login-form')).html(tips).show();
		 $('input[name=password]').val('');
	}
		//自定义验证码函数
	var Show = function () {
			var show_num = [];
			//验证码标识
			 draw(show_num);
			$(".code-canvas").on('click', function () {
				draw(show_num);
			})
			$('.login-btn').unbind('click').click(function () {
				var val = $(".input-val").val().toLowerCase();
				var num = show_num.join("");
				var state = $(".code-canvas").css("float");
				var tips='';
				if (val == '' && state == 'right') {
					$('.alert', $('.login-form')).show();
					_CodeState = 0;
					tips=LANG.UI_LOGIN_EMPTY_VEFIFY_CODE;
					$('.alert span', $('.login-form')).html(tips);
				} else if (val == num && state == 'right') {
					_CodeState = 1;
				} else if (state == 'right') {
					_CodeState = 2;
					tips=LANG.UI_LOGIN_ERROR_VERIFY_CODE;
					$('.alert span', $('.login-form')).html(tips);
					$(".input-val").val('');
					draw(show_num);
				}
			})
	}
	var handleListener = function () {
		$('.download-agent').click(function () {
			$('.contentpakage').toggle();
		});
		//选择类型
		$('.agenttype-select').on('change', function(){
			$('.dbagent').hide();
			$('.vmagent').hide();
			$('.dbcdp').hide();
			$('.osagent').hide();
			$('.fsagent').hide();
			$('.dbprotectagent').hide();
			var type = parseInt(this.value);
			switch(type){
				case 0:	//虚拟机备份插件
					$('.vmagent').show();
					var text = $('.vendor_select').find("option:selected").text();
					if(text == "Microsoft Hyper-V" || text == "Microsoft Hyper-V" || text == "FusionOne Compute" || text == "Huawei FusionCompute KVM"){
						$('.vmagent_div').hide();
					}
					break;
				case 1:	//备份节点扩展
					break;
				case 2:	//客户端备份插件
					$('.osagent').show();
					var text = $('.ossystem_select').find("option:selected").text();
					if(text == "Windows"){
						$('.fsagent').hide();
					}else{
						$('.fsagent').show();
					}
					break;
				case 3:	//数据库备份客户端
					$('.dbagent').show();
					break;
				case 4:	//数据库实时客户端
					$('.dbcdp').show();
					break;
			}
		});

		//选择厂商
		$('.vendor_select').on('change', function(){
			var value = this.value;
			var text = $('.vendor_select').find("option:selected").text();
			if(text == "Microsoft Hyper-V" || text == "FusionOne Compute" || text == "Huawei FusionCompute KVM"){
				$('.vmagent_div').hide();
			}else{
				$('.vmagent_div').show();
			}
			var version = $('.version_select');
			version.empty();
			var eachVersion = _VENDER[value].version;
			for(var i=0; i<eachVersion.length; i++){
				var option = $("<option>").text(eachVersion[i].text).val('#');
				version.append(option);
			}
		});
		// 选择操作系统
		$('.ossystem_select').on('change', function(){
			var value = this.value;
			var text = $('.ossystem_select').find("option:selected").text();
			if(text == "Windows"){
				$('.fsagent').hide();
			}else {
				$('.fsagent').show();
			}
			var filesystem = $('.filesystem_select');
			filesystem.empty();
			_OS.forEach(item=>{
				if(item.text == text){
					item.version.forEach(item=>{
						var option = $("<option>").text(item.text).val('#');
						filesystem.append(option);
					})
				}
			});
		});
		//下载软件包
		$('.download_btn').on('click', function(){
			var type = parseInt($('.agenttype-select').val());
			var version = $('.version_select').find('option:selected').text();
			var href = "#";
			var selectedText = '';
			switch(type){
				case 0:
					//虚拟机代理
					selectedText = $('.vendor_select').find("option:selected").text();
					break;
				case 1:
					//文件或节点代理
					href = _TYPE[type].href;
					break;
				case 2:
					//文件代理
					selectedText = $('.filesystem_select').find("option:selected").text();
					if (!selectedText) { // 检查 selectedText 是否为假值（包括空字符串）
						selectedText = 'Windows';
					}
					break;
				case 3:
					//数据库定时客户端
					href = $('.dbtimingClient_select').val();
					break;
				case 4:
					//数据库实时客户端
					selectedText = $('.dbcdpClient_select').find("option:selected").text();
					break;
			}
			//获取下载链接
			var data = {selectedText:selectedText,type:type,version:version};
			pAjaxRequest(data,'/api/v1/agents/download/plugins','GET',function(d){
				var res = d.data;
				if(res.linkStr == '#'){
					$('.nosupporttips').show();
					return;
				}
				var data = {};
				data.filepath = res.path+res.linkStr;  // 绝对路径
				data.filename = res.showName;
				pAjaxRequest(data,'/api/v1/system/generate/download','GET',function (res){
					downloadPackage(res.data.url);
				});
			});
		});

	}

	//下载包
	var downloadPackage = function(href){
		if("#" == href){
			$('.nosupporttips').show();
			return;
		}
		$('.nosupporttips').hide();
		window.location = href;
	}
	var toggleInput=function () {
		$('.username').focus();
		$('.username').siblings().addClass('focusIcon');
		$('.form-group .form-control').focus(function () {
			$(this).siblings().addClass('focusIcon');
			$(this).addClass('focusInput')
		})
		$('.form-group .form-control').blur(function () {
			$(this).siblings().removeClass('focusIcon');
			$(this).removeClass('focusInput')
		})

	}
	var initUserInfo = function () {
        var rememberCheck = $("input[name=remember]").data('check');
        if (rememberCheck) {
            var span = $("input[name=remember]")[0].parentNode;
            $(span).prop("class", "checked");
            $("input[name=remember]").prop("checked", true);
        }
    }

	var downloadIcon=function () {
		$('.agent-form .download_btn').hover(function () {
			$('.downLoad').css('color','#000f14');
			$('.download_btn_project').css('color','#FFF');
			$('.download_btn_project').css('background-color','#3A73FB');
		},function () {
			$('.downLoad').css('color','#00E2DB');
			$('.download_btn_project').css('color','#3A73FB');
			$('.download_btn_project').css('background-color','transparent');
		})
	}
	// var passwordToggle=function(){
	// 	//默认密码是不可见的
	// 	$('.input-icon .eye').hide();
	// 	$('.input-icon .eye-close').click(function () {
	// 		$('.input-icon .eye-close').hide();
	// 		$('.input-icon .eye').show();
	// 		$('.passwordInput').attr('type','text');
	// 	})
	// 	$('.input-icon .eye').click(function () {
	// 		$('.input-icon .eye').hide();
	// 		$('.input-icon .eye-close').show();
	// 		$('.passwordInput').attr('type','password');
	// 	})
	// }

	//刷新页面是否显示验证码
	var codeShow = function () {
		if ($('.login_faild_num').val() > 2) {
			_CodeShow = true;
			$('.input-val').val('');
			$(".Code").show();
		}
	}
	var initAgentInfo = function() {
		var agentInfo = $('#agent_info').html();
		agentInfo = eval("("+agentInfo+")");
		var agenttype = $('.agenttype-select');
		if(agentInfo.type.length == 0){
			// 未授权不能显示插件
			$('.contentpakage').hide();
			$('.loginLine').hide();
		}else {
			for (var i = 0; i < agentInfo.type.length; i++) {
				var option = $("<option>").text(agentInfo.type[i].text).val(agentInfo.type[i].value);
				agenttype.append(option);
			}
		}
		var vendor = $('.vendor_select');
		var version = $('.version_select');
		for (var i = 0; i < agentInfo.vendor.length; i++) {
			var option = $("<option>").text(agentInfo.vendor[i].text).val(i);
			vendor.append(option);
			if (0 == i) {
				//默认添加第一项的版本
				var eachversion = agentInfo.vendor[0].version;
				for (var j = 0; j < eachversion.length; j++) {
					var option = $("<option>").text(eachversion[j].text).val('#');
					version.append(option);
				}
				var text = $('.vendor_select').find("option:selected").text();
				if (text == "Microsoft Hyper-V" || text == "Microsoft Hyper-V" || text == "FusionOne Compute" || text == "Huawei FusionCompute KVM") {
					$('.vmagent_div').hide();
				} else {
					$('.vmagent_div').show();
				}
			}
		}

		//操作系统
		var clientOsInfo = agentInfo.clientOs;
		var ossystem = $('.ossystem_select');
		ossystem.empty();
		for (var j = 0; j < clientOsInfo.length; j++) {
			var option = $("<option>").text(clientOsInfo[j].text).val('#');
			ossystem.append(option);
		}

		//客户端
		var clientInfo = agentInfo.client;
		var filesystem = $('.filesystem_select');
		filesystem.empty();
		for (var j = 0; j < clientInfo.length; j++) {
			var option = $("<option>").text(clientInfo[j].text).val('#');
			filesystem.append(option);
		}

		//数据库定时客户端
		var dbtimingInfo = agentInfo.dbtiming;
		var dbtimingClient = $('.dbtimingClient_select');
		dbtimingClient.empty();
		for (var k = 0; k < dbtimingInfo.length; k++) {
			var option = $("<option>").text(dbtimingInfo[k].text).val(dbtimingInfo[k].value);
			dbtimingClient.append(option);
		}

		//数据库实时客户端
		var dbcdpInfo = agentInfo.dbcdp;
		var dbcdpClient = $('.dbcdpClient_select');
		dbcdpClient.empty();
		for (var k = 0; k < dbcdpInfo.length; k++) {
			var option = $("<option>").text(dbcdpInfo[k].text).val('#');
			dbcdpClient.append(option);
		}
		_TYPE = agentInfo.type;
		_VENDER = agentInfo.vendor;
		_OS = agentInfo.clientOs;
	}

	return {
		init: function () {
			handleLogin();
			handleListener();
			toggleInput();
			downloadIcon();
			initAgentInfo();
			// passwordToggle();
			initUserInfo();
			codeShow();
		}
	};
}();
$(function () {
	Login.init();
})
