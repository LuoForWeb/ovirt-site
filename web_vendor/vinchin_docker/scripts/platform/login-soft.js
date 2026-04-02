var Login = function () {
	var _TYPE, _VENDER;
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

//	            messages: {
//	                username: {
//	                    required: ""
//	                },
//	                password: {
//	                    required: ""
//	                }
//	            },

	            invalidHandler: function (event, validator) { //display error alert on form submit
	                $('.login-alert', $('.login-form')).show();
	            },

	            highlight: function (element) { // hightlight error inputs
	                $(element)
                    .closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group   
	            },

	            success: function (label, element) {
//	            	 label.closest('.form-group').removeClass('has-error');
//		             label.remove();
	            	$(element).closest('.form-group').removeClass('has-error').addClass('has-success');
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
		var username = $.trim($('input[name=username]').val());
		var password = hex_md5($('input[name=password]').val());
		var data = JSON.stringify({username:username,password:password});
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'login',p:data}, loginResult);
	}
	
	var setCookie = function(){
		if ($("input[name=remember]").attr("checked")) {
            var username = btoa($("input[name=username]").val());
            var password = btoa($("input[name=password]").val());
            $.cookie("remember", "true", { expires: 7 }); //存储一个带7天期限的cookie
            $.cookie("username", username, { expires: 7 });
            $.cookie("password", password, { expires: 7 });
        }
        else {
            $.cookie("remember", "false", { expire: -1 });
            $.cookie("username", "", { expires: -1 });
            $.cookie("password", "", { expires: -1 });
        }
	}
	
	var loginResult = function(data){
		var loginResult = JSON.parse(data);
		var tips = '';
		if(1 == loginResult){
			//login success
			setCookie();
			window.location = './';
			return;
		}else if(2 == loginResult){
			tips = LANG.UI_LOGIN_ERROR_TIPS;
		}else if(3 == loginResult){
			tips = LANG.UI_LOGIN_USER_LOCKED_TIPS;
		}else{
			tips = LANG.UI_PUBLIC_UNKNOWN_ERROR;
		}
		$('.login-alert span', $('.login-form')).html(tips);
		$('.login-alert ', $('.login-form')).show();
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
			var type = parseInt(this.value);
			switch(type){
				case 0:	//虚拟机备份插件
					$('.fsagent').hide();
					$('.dbagent').hide();
					$('.dbcdp').hide();
					$('.vmagent').show();
					if($('#vendor').val() == "0"){
						$('#vmagent').hide();
					}
					break;
				case 1:	//备份节点扩展
					$('.dbagent').hide();
					$('.vmagent').hide();
					$('.dbcdp').hide();
					$('.fsagent').hide();
					break;
				case 2:	//文件备份插件
					$('.dbagent').hide();
					$('.vmagent').hide();
					$('.dbcdp').hide();
					$('.fsagent').show();
					break;
				case 3:	//数据库备份客户端
					$('.vmagent').hide();
					$('.fsagent').hide();
					$('.dbcdp').hide();
					$('.dbagent').show();
					break;
				case 4:	//数据库实时客户端
					$('.vmagent').hide();
					$('.fsagent').hide();
					$('.dbagent').hide();
					$('.dbcdp').show();
					break;
			}
        });
        
        //选择厂商
        $('#vendor').on('change', function(){
        	var value = parseInt(this.value);
        	if(value == 0){
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
		if ($.cookie("remember") == "true") {
			var span = $("input[name=remember]")[0].parentNode;
			$(span).attr("class", "checked");
	        $("input[name=remember]").attr("checked", true);
	        $("input[name=username]").val(atob($.cookie("username")));
	        $("input[name=password]").val(atob($.cookie("password")));
	    }
	}
	
	var initAgentInfo = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.AGENT,f:'getDoloadAgentName',p:{}}, function(d){
			var agentInfo = JSON.parse(d);
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
				}
			}
			
			//文件
			var fileInfo = agentInfo.file;
			var filesystem = $('#filesystem');
			filesystem.empty();
			for(var j=0;j<fileInfo.length;j++){
				var option = $("<option>").text(fileInfo[j].text).val(fileInfo[j].value);
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
		});
	}
    
    return {
        //main function to initiate the module
        init: function () {
            handleLogin();
            handleListener();
            initUserInfo();
            initAgentInfo();
        }

    };

}();