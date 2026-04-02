var Login = function () {
	var _TYPE, _VENDER;
	var passTips ="";
	var _USERUUID = "";
	
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

	            messages: {
	                username: {
	                    required: LANG.UI_LOGIN_TIPS_INPUT_USERNAME
	                },
	                password: {
	                    required: LANG.UI_LOGIN_TIPS_INPUT_PASSWORD
	                }
	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit   
	                $('.alert-danger', $('.login-form')).show();
	            },

	            highlight: function (element) { // hightlight error inputs
	                $(element)
	                    .closest('.form-group').addClass('has-error'); // set error class to the control group
	            },

	            success: function (label) {
	                label.closest('.form-group').removeClass('has-error');
	                label.remove();
	            },

	            errorPlacement: function (error, element) {
	                error.insertAfter(element.closest('.input-icon'));
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
		var data = JSON.stringify({username:username,password:password, remember:remember});
		Metronic.blockUI({target: '.contentlogin',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'login',p:data}, loginResult);
	}
	
	var loginResult = function(data){
		Metronic.unblockUI('.contentlogin');
		var loginResult = JSON.parse(data);
		var tips = '';
		if(1 == loginResult.result){
			window.location = './';
			return;
		}else if(2 == loginResult.result){
			tips = LANG.UI_LOGIN_ERROR_TIPS;
		}else if(3 == loginResult.result){
			tips = LANG.UI_LOGIN_USER_LOCKED_TIPS;
		}else if(4 == loginResult.result){
			tips = LANG.UI_PLATFORM_SERVER_CONNEC_ERROR;
		}else if(5 == loginResult.result){
			_USERUUID = loginResult.useruuid;

			// 初始化修改密码
			CONF.PASS_LENGTH = loginResult.passlength;
			CONF.PASS_COMPLEXITY = loginResult.passcomplexity;
			initPassComplexity();
			handleEditPassword();
			// 页面切换
			$('.contentpassword').show();
			$('.contentlogin').hide();
			tips = LANG.UI_PLATFORM_PSW_EXPIRED_CONNEC_ADMIN;
		}else{
			tips = LANG.UI_PUBLIC_UNKNOWN_ERROR;
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
        $('#agenttype').on('change', function(){
        	$('.dbagent').hide();
			$('.vmagent').hide();
			$('.dbcdp').hide();
			$('.fsagent').hide();
			$('.dbprotectagent').hide();
			var type = parseInt(this.value);
			switch(type){
				case 0:	//虚拟机备份插件
					$('.vmagent').show();
					var text = $('#vendor').find("option:selected").text();
					if(text == "Microsoft Hyper-V"){
						$('#vmagent').hide();
					}
					break;
				case 1:	//备份节点扩展
					break;
				case 2:	//客户端备份插件
					$('.fsagent').show();
					break;
				case 4:	//数据库实时客户端
					$('.dbcdp').show();
					break;
			}
        });
        
        //选择厂商
        $('#vendor').on('change', function(){
        	var value = this.value;
        	var text = $('#vendor').find("option:selected").text();
        	if(text == "Microsoft Hyper-V"){
        		$('#vmagent').hide();
        	}else{
        		$('#vmagent').show();
        	}
        	var version = $('#version');
        	version.empty();
        	var eachVersion = _VENDER[value].version;
        	for(var i=0; i<eachVersion.length; i++){
        		var option = $("<option>").text(eachVersion[i].text).val(eachVersion[i].value);
				version.append(option);
        	}
        });
        //下载软件包
        $('#download').on('click', function(){
        	var type = parseInt($('#agenttype').val());
			var href = "#";
			switch(type){
				case 0:
					//虚拟机代理
					href = $('#version').val();
					break;
				case 1:
					//文件或节点代理
					href = _TYPE[type].href;
					break;
				case 2:
					//文件代理
					href = $('#filesystem').val();
					break;
				case 3:
					//数据库定时客户端
					href = $('#dbtimingClient').val();
					break;
				case 4:
					//数据库实时客户端
					href = $('#dbcdpClient').val();
					break;
			}
        	downloadPackage(href);
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
	
	var initAgentInfo = function(){
		var agentInfo = $('#agent_info').html();
		agentInfo = eval("("+agentInfo+")");
		//$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'getDoloadAgentName',p:{}}, function(d){
			//var agentInfo = JSON.parse(d);
			var agenttype = $('#agenttype');
			for(var i=0; i<agentInfo.type.length; i++){
				var option = $("<option>").text(agentInfo.type[i].text).val(agentInfo.type[i].value);
				agenttype.append(option);
			}
			var vendor = $('#vendor');
			var version = $('#version');
			for(var i=0; i<agentInfo.vendor.length; i++){
				var option = $("<option>").text(agentInfo.vendor[i].text).val(i);
				vendor.append(option);
				if(0 == i){
					//默认添加第一项的版本
					var eachversion = agentInfo.vendor[0].version;
					for(var j=0; j<eachversion.length; j++){
						var option = $("<option>").text(eachversion[j].text).val(eachversion[j].value);
						version.append(option);
					}
					var text = $('#vendor').find("option:selected").text();
		        	if(text == "Microsoft Hyper-V"){
		        		$('#vmagent').hide();
		        	}else{
		        		$('#vmagent').show();
		        	}
				}
			}
			
			//客户端
			var clientInfo = agentInfo.client;
			var filesystem = $('#filesystem');
			filesystem.empty();
			for(var j=0;j<clientInfo.length;j++){
				var option = $("<option>").text(clientInfo[j].text).val(clientInfo[j].value);
				filesystem.append(option);
			}
			
			//数据库定时客户端
			var dbtimingInfo = agentInfo.dbtiming;
			var dbtimingClient = $('#dbtimingClient');
			dbtimingClient.empty();
			for(var k=0;k<dbtimingInfo.length;k++){
				var option = $("<option>").text(dbtimingInfo[k].text).val(dbtimingInfo[k].value);
				dbtimingClient.append(option);
			}
			
			//数据库实时客户端
			var dbcdpInfo = agentInfo.dbcdp;
			var dbcdpClient = $('#dbcdpClient');
			dbcdpClient.empty();
			for(var k=0;k<dbcdpInfo.length;k++){
				var option = $("<option>").text(dbcdpInfo[k].text).val(dbcdpInfo[k].value);
				dbcdpClient.append(option);
			}
			
			_TYPE = agentInfo.type;
			_VENDER = agentInfo.vendor;
		//});
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
                        passcomplexity: true
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
            		case 1: //弱(包含字母(不区分大小写),数字)
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
    	}
    };
    
    var initPassComplexity = function(){
    	switch(CONF.PASS_COMPLEXITY){
			case 1: //弱(包含字母(不区分大小写),数字)
				passTips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
				break;
			case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
				passTips = LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM;
				break;
			case 3: //强(必须包含大小写字母,数字,特称字符)
				passTips = LANG.UI_USER_PASSWORD_STRENGTH_STRONG;
				break;
			
		}
    }
	
    //初始化登录配置
    /*var initConfig = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.PLATFORM,f:'getConfig',p:{}}, function(d){
			var config = JSON.parse(d);
			CONF.PASS_LENGTH = config.PASS_LENGTH;
			CONF.PASS_COMPLEXITY = config.PASS_COMPLEXITY;
			initPassComplexity();
			handleEditPassword();
		});
	}*/
    
    return {
        //main function to initiate the module
        init: function () {
            handleLogin();
            handleListener();
            initUserInfo();
            initAgentInfo();
            // initConfig();
        }

    };

}();

jQuery(document).ready(function() {     
	  Metronic.init(); // init metronic core components
	  Layout.init(); // init current layout
	  Login.init();
	  Demo.init();
});