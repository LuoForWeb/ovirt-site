var Login = function () {
	var _TYPE, _VENDER,_OS;;
	var passTips ="";
	var _USERUUID = "";
	var  _FailNum=window.localStorage;
	var _CodeState=-1;//验证码的状态
	var _CodeShow=false; //验证码是否显示，默认为否
	var loginFlag = false;
	var handleLogin = function() {
		$('.login-form').validate({
	            errorElement: 'span', //default input error message container
	            errorClass: 'help-block', // default input error message class
	            focusInvalid: false, // do not focus the last invalid input
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
	            invalidHandler: function (event, validator) { //display error alert on form submit   
	                $('.alert-danger', $('.login-form')).show();
	            },

	            highlight: function (element) { // hightlight error inputs
	                $(element).closest('.form-group').addClass('has-error'); // set error class to the control group
	            },


	            success: function (label) {
//	                label.closest('.form-group').removeClass('has-error');
//	                label.remove();
	            },

	            errorPlacement: function (error, element) {
//	                error.insertAfter(element.closest('.input-icon'));
	            },

	            submitHandler: function (form) {
	            	submit();
	                //form.submit();
	            }
	        });

		$('.login-form input').keypress(function (e) {
			if (e.which == 13) {
				if ($('.login-form').validate().form()) {
					submit();
					//$('.login-form').submit();
				}
				return false;
			}
		});
	}
	
	var submit = function(){
		var encrypt = new JSEncrypt();
        encrypt.setPublicKey(CONF.PUBLIC_KEY);
		var username = encrypt.encrypt($.trim($('input[name=username]').val()));
		var password = encrypt.encrypt($('input[name=password]').val());
		var remember = $("input[name=remember]").prop("checked");
		//OEM flag
		// var OemFalg = true;
		var very_code = ($('#codeContent').val());
		if(_CodeShow && very_code == ''){
			// 有验证码但是没有输入 提示验证码必须
			tips=LANG.UI_LOGIN_EMPTY_VEFIFY_CODE;
			$('.alert-danger span', $('.login-form')).html(tips);
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
        //退出登录，移除exchange用于恢复页面验证身份的session
		sessionStorage.removeItem('exchange_recovery_pass');
		var loginResult = data.data;
        var loginErrorCount = loginResult.faliCount;//获取登录错误次数
		var tips = '';
		if (1 ==  data.message){
			if ($.trim($('input[name=username]').val()) == _FailNum['username']) {
				_FailNum['count'] = 0;
				_FailNum['username'] = '';
			}
			window.location = './';
			return;
		}else if(2 == data.message){ //  用户名或密码错误
			_FailNum={
				'username': loginResult.username,
				'count' : loginErrorCount,
			}
			var loginCount = loginResult.maxCount - loginResult.faliCount;
			var tipFail = '';
			if (loginCount>0 && loginCount <= 5 && loginResult.usertype != 3) {
				tipFail = LANG.UI_LOGIN_LOGIN_COUNT+loginCount;
			}
			if(_FailNum['count']>=3 ) {  //有验证码的情况
				console.log("comein");
				refresh_code();  //刷新验证码
                _CodeShow = true;
                $('.input-val').val('');
                $(".code").show();
			}
			tips = LANG.UI_LOGIN_ERROR_TIPS  + tipFail;;
		}else if(3 == data.message) {
			tips = LANG.UI_LOGIN_USER_LOCKED_TIPS;
		}else if(4 ==  data.message){
			tips = LANG.UI_PLATFORM_SERVER_CONNEC_ERROR;
		}else if(5 ==  data.message){
			_USERUUID = loginResult.useruuid;
			$('.contentpassword').show();
			$('.contentlogin').hide();
			tips = LANG.UI_PLATFORM_PSW_EXPIRED_CONNEC_ADMIN;
		}else if(6 == data.message){
			tips = LANG.UI_LOGIN_ERROR_TIPS;
		}else if(9 == data.message){
			// 验证码错误
			tips=LANG.UI_LOGIN_ERROR_VERIFY_CODE;
			$('.input-val').val('');
			refresh_code();
		}else if(10 == data.message){
			$('.input-val').val('');
			refresh_code();
			//限制十分钟
			var minutes = Math.floor(loginResult.time/60);
			var remainingSeconds = loginResult.time % 60;
			minutes == 0 ? tips = LANG.UI_LOGIN_PLEASE+ remainingSeconds + LANG.UI_LOGIN_AFTER_SECOND : tips = LANG.UI_LOGIN_PLEASE+minutes+LANG.UI_PUBLIC_MINUTE+remainingSeconds+LANG.UI_LOGIN_AFTER_SECOND;
		}else if(11 == data.message){
			_USERUUID= loginResult.useruuid;
			// window.sessionStorage.setItem('useruuid',_USERUUID);
			if(loginResult.usertype == 3){
				//admin用户需要选择是否修改密码
				$('#overlay').show();
				$('#editPwdButton').on('click', function(event){
					// event.preventDefault();
					// window.location.href = './edit_pwd.php?key='+loginResult.tokenData+'&show='+'first_login';
					$('#overlay').hide();
					$('.contentpassword').show();
					$('.contentlogin').hide();
					$('.contentpassword .alert-danger span').html("首次登录请修改初始密码");
				})
				$('#loginSuccessButton').on('click', function(event){
					// event.preventDefault();
					loginFlag = true;
					submit();
				});
			}else{
				// window.location.href = './edit_pwd.php?key='+loginResult.tokenData+'&show='+'first_login';
				$('#overlay').hide();
				$('.contentpassword').show();
				$('.contentlogin').hide();
				$('.contentpassword .alert-danger span').html("首次登录请修改初始密码");
			}
			return;
		}else if(12 == data.message){
			tips = LANG.UI_LOGIN_USER_LOGIN_CONTACT_MANAGER;
			$('.input-val').val('');
            refresh_code();
		}

		$('.alert-danger span', $('.login-form')).html(tips);
		$('.alert-danger', $('.login-form')).show();
		$('input[name=password]').val('');
	}

	var handleListener = function () {
        $('#download-agent').click(function () {
            jQuery('.contentlogin').hide();
            jQuery('.contentpakage').show();
        });
        
        $('#downloadback').click(function () {
            jQuery('.contentlogin').show();
            jQuery('.contentpakage').hide();
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
                    if (text == "Microsoft Hyper-V" || text == "FusionOne Compute" || text == "Huawei FusionCompute KVM") {
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
            } else {
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
		$('#download').on('click', function(){
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
	
	var initUserInfo = function(){
		var rememberCheck = $("input[name=remember]").data('check');
		if (rememberCheck) {
			var span = $("input[name=remember]")[0].parentNode;
			$(span).prop("class", "checked");
	        $("input[name=remember]").prop("checked", true);
	    }
	}

	//刷新页面是否显示验证码
	var codeShow = function () {
		if ($('#login_faild_num').val() > 2) {
			_CodeShow = true;
			$('.input-val').val('');
			$("#loginCode").show();
		}
	}

	var initAgentInfo = function(){
		var agentInfo = $('#agent_info').html();
		agentInfo = eval("("+agentInfo+")");
		var agenttype = $('.agenttype-select');
		for(var i=0; i<agentInfo.type.length; i++){
			var option = $("<option>").text(agentInfo.type[i].text).val(agentInfo.type[i].value);
			agenttype.append(option);
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
    
	// validation using icons
    var handleEditPassword = function() {
        // for more info visit the official plugin documentation: 
            // http://docs.jquery.com/Plugins/Validation
            var form2 = $('#editpass');
            var error2 = $('.alert-danger', form2);
            var success2 = $('.alert-success', form2);
            form2.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: {
                	oldpass: {
                        required: true,
                        oldpassAvailable: true
                    },
                    editpassword: {
                    	minlength: CONF.PASS_LENGTH,
                        required: true,
                        passcomplexity: true,
						compareOldcode: true
                    },
                    rpassword: {
                    	minlength: CONF.PASS_LENGTH,
                    	required: true,
                        equalTo: "#password",
                    },
                    
                },

                invalidHandler: function (event, validator) { //display error alert on form submit  
                	return true;
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

                
            });

			$.validator.addMethod("compareOldcode",function (value, element) {
				var oldPassword =hex_md5($("input[name=oldpass]").val());
				var password = hex_md5(value);
					if(oldPassword == password){
						return false;
					}
					return true;
				},
				LANG.UI_USER_NOT_SAME_OLD_PASSWORD
			);
            
            $("#editsubmit").click(function(){
            	if (form2.validate().form()) {
                	editSubmit();
                }
            });
            
            $("#editcancel").click(function(){
            	$('.contentpassword').hide();
    			$('.contentlogin').show();
            });
            $.validator.addMethod("passcomplexity", function(value, element) {
            	var match = "";
            	switch(CONF.PASS_COMPLEXITY){
            		case 1: //弱(包含字母(不区分大小写),数字,特殊字符(不是必须))
            			match = this.optional( element ) || /^[A-Za-z0-9!@#$%^&*,.]/.test(value);
            			break;
            		case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
            			match = this.optional( element ) || /^(?=.*[0-9])(?=.*[A-Za-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]/.test(value);
            			break;
            		case 3: //强(必须包含大小写字母,数字,特称字符)
            			match = this.optional( element ) || /^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]/.test(value);
            			break;
            		
            	}
        		return match;
            }, passTips);
            
            $.validator.addMethod(
                	"oldpassAvailable",
                	function(value, element, param) {
                		var data = JSON.stringify({password:hex_md5(value), useruuid:_USERUUID});
                		var result = false;
                		$.ajax({ 
                			type: "post", 
                	        url: CONF.AJAXPATH, 
                	        async:false, 
                	        data:{m:CONF.M.USER,f:'oldpassAvailable',p:data},
                	        success: function(data){ 
                	        	result = JSON.parse(data);
                	        } 
                		});
            	    	return result;
            	    },
                	LANG.UI_USER_OLD_PASSWORD_ERROR
                );

    };
    
    
    //修改密码确认
    var editSubmit = function(){
    	var data = {};
    	data.useruuid = _USERUUID;
    	data.oldPassword = hex_md5($("input[name=oldpass]").val());
    	data.newPassword = hex_md5($("input[name=editpassword]").val());
    	data = JSON.stringify(data);
    	$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'editPassword',p:data}, editResult);
    };
    
    var editResult = function(data){
    	var jsonData = JSON.parse(data);
    	if(jsonData.re){
    		$('.contentpassword').hide();
			$('.contentlogin').show();
			$('.alert-danger', $('.login-form')).hide();
			$('#edittips').show();
			refresh_code();
    	}
    };
    
    var initPassComplexity = function(){
    	switch(CONF.PASS_COMPLEXITY){
			case 1: //弱(包含字母(不区分大小写),数字,特殊字符(不是必须))
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
            case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM;
                break;
            case 3: //强(必须包含大小写字母,数字,特称字符)
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_STRONG;
                break;
            default:
                passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                break;
			
		}
    }

	// 获取密码配置信息
	var initPasswordConfig = function () {
		$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:'getPassComplexity',p:{}},function (d) {
			var config = JSON.parse(d);
			CONF.PASS_LENGTH = config.passlength;
			CONF.PASS_COMPLEXITY = config.passcomplexity;
			initPassComplexity();
			handleEditPassword();
		})
	}

    return {
        //main function to initiate the module
        init: function () {
            handleLogin();
            handleListener();
            initUserInfo();
            initAgentInfo();
			initPasswordConfig();
			// codeShow();
        }

    };

}();

jQuery(document).ready(function() {     
	  Metronic.init(); // init metronic core components
	  Layout.init(); // init current layout
	  Login.init();
	  Demo.init();
});