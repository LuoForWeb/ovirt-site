var EditDomainServer = function(){
	var SETTINGS;

	// validation using icons
    var handleValidation = function() {
        // for more info visit the official plugin documentation:
            // http://docs.jquery.com/Plugins/Validation

            var form2 = $('#form_editDomainServer');
            var error2 = $('.alert-danger', form2);
            var success2 = $('.alert-success', form2);


            form2.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: {
	            	domain: {
		        		 required: true,
		        		 domainAvailable: true
//		        		 ipv4Ordomain: true
	            	},
					ip: {
						required: true,
					},
	                port: {
	                    required: true,
	                },
	                username: {
	                    required: true,
	                },
	                password: {
	                    required: true,
	                }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit
                    success2.hide();
                    error2.show();
                    Metronic.scrollTo(error2, -200);
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
                    success2.show();
                    error2.hide();
                }

            });

            $("#addsubmit").click(function(){
            	if (form2.validate().form()) {
            		submit();
                }
            });

            $("#cancelBut").click(function(){
            	//返回域服务器列表
            	LOCATION('./content/platform/users/domain_server.php','safety');
            });
    };

    var submit = function(){
    	var data = {};
    	data.domainuuid = $('#domainuuid').val();
    	data.domainname = $('#domain').val();
    	data.domaintype = $('#domaintype').val();
		data.protocol = $('#protocol').val();
		data.ip = $('#ip').val();
		data.port = $('#port').val();
    	data.username = $('#userName').val();
    	data.password = btoa($('#password').val());
		data.checkflag = $("#certificate_check").bootstrapSwitch('state');	//是否需要验证证书
		pAjaxRequest(data,'/api/v1/domains','PUT',registerResult);
    };

    var registerResult = function(d){
		if(operateResponseList(d)){
			LOCATION('./content/platform/users/domain_server.php','safety');
		}
    }

	var initListener = function(){
		$.validator.addMethod(
	    	"domainAvailable",
	    	function(value,
					 element, param) {
	    		var data = JSON.stringify({domainname:value});
	    		var result = false;
	    		$.ajax({
	    			type: "post",
	    	        url: CONF.AJAXPATH,
	    	        async:false,
	    	        data:{m:CONF.M.DOMAINSERVER,f:'domainAvailable',p:data},
	    	        success: function(data){
	    	        	result = JSON.parse(data);
	    	        }
	    		});
		    	return result;
		    },
	    	LANG.UI_DOMAIN_SERVER_EXIST
	    );

		//切换协议
		$('#protocol').on('change', function(){
			if($(this).val() == 1){
				$('.certificateDiv').show();
				$('#port').val('636');
			}else{
				$('.certificateDiv').hide();
				$('#certificate_check').bootstrapSwitch('state', false);
				$('#port').val('389');
			}
		});
	}

	//初始化租户列表
	var initTenantList = function(tenantuuid){

		$.post(CONF.AJAXPATH,{m: CONF.M.USER, f: "getAllTenant", p:{}}, function(d){
			var data = JSON.parse(d);
			var tenant = $('#tenant');
			tenant.empty();
			var option = $("<option>").text(LANG.UI_DOMAIN_SERVER_CONNECT_TENANT).val('');
			tenant.append(option);
			for(var i=0;i<data.length;i++){
				var option = $("<option>").text(data[i].tenant_name).val(data[i].tenant_uuid);
				tenant.append(option);
			}
			tenant.val(tenantuuid);
		});
	}


	//获取域服务器信息
	var initOldInfo = function(){
		var data = {};
		data.domainuuid = $('#domainuuid').val();
		pAjaxRequest(data,'/api/v1/domains/info','GET',function (d){
			var data = d.data;
			$('#domaintype').val(data.domaintype).prop("disabled", true);
			$('#domain').val(data.domain).prop("disabled", true);
			$('#protocol').val(data.protocol).prop("disabled", true);
			$('#port').val(data.port).prop("disabled", true);
			$('#ip').val(data.ip).prop("disabled", true);
			$('#certificate_check').bootstrapSwitch('state', data.checkflag);
			if(data.protocol == 1){
				$('.certificateDiv').show();
			}else{
				$('.certificateDiv').hide();
			}
			$('#userName').val(data.username);
			$('#password').val(atob(data.password));
		})

		//切换协议
		$('#protocol').on('change', function(){
			if($(this).val() == 1){
				$('.certificateDiv').show();
			}else{
				$('.certificateDiv').hide();
				$('#certificate_check').bootstrapSwitch('state', false);
			}
		});
	}

	return {
		init:function(){
			initOldInfo();
			handleValidation();
			initListener();
		}
	}

}();

jQuery(document).ready(function(){
	EditDomainServer.init();
});