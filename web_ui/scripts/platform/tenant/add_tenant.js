var AddTenant = function(){
	
	var passTips ="";
	
	// validation using icons
    var handleValidation = function() {
        // for more info visit the official plugin documentation: 
            // http://docs.jquery.com/Plugins/Validation

            var form2 = $('#form_addTenant');
            var error2 = $('.alert-danger', form2);
            var success2 = $('.alert-success', form2);
            
            
            form2.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: {
                    name: {
                        minlength: 4,
                        maxlength: 24,
                        required: true,
                        tenantnameAvailable: true,
                        namevalue: true
                    },
                    nickname: {
                    	required: true
                    },
                    username: {
                    	required: true,
                    	minlength: 4,
                    	maxlength: 30,
                    	namevalue: true,
                    	
                    },
                    password: {
                    	minlength: CONF.PASS_LENGTH,
                    	maxlength: 32,
                        required: true,
//                        passwordvalue: true,
                        passcomplexity: true
                    },
                    rpassword: {
                    	minlength: CONF.PASS_LENGTH,
                    	maxlength: 32,
                    	required: true,
                        equalTo: "#password",
                    },
                    email: {
//                        required: true,
                        email: true
                    }
                },

                invalidHandler: function (event, validator) { //display error alert on form submit              
                    success2.hide();
                    error2.show();
//                    Metronic.scrollTo(error2, -200);
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
            
            $("#submitBut").click(function(){
            	if (form2.validate().form()) {
            		submit();
                }
            });
            
            $("#cancelBut").click(function(){
            	LOCATION('./content/platform/tenant/tenant_manager.php','tenant_manager');
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
    }
    
    //提交需要的参数
    var submit = function(){
    	var data = {};
    	data.tenantname = $('#tenantname').val();
    	data.nickname = $('#nickname').val();
    	data.username = $('#adminname').val();
    	data.password = hex_md5($('#password').val());
    	data.email = $('#email').val();
    	
    	data = JSON.stringify(data);
    	Metronic.blockUI({target: '#addtenantContent',animate: true,cenrerY: true,});
    	$.post(CONF.AJAXPATH, {m:CONF.M.TENANT,f:'addTenant',p:data}, registerResult);
    };
    
    var registerResult = function(data){
    	Metronic.unblockUI('#addtenantContent');
    	if(OPREL(data)){
        	LOCATION('./content/platform/tenant/tenant_manager.php','tenant_manager');
    	}
    };
    
    
    
    $.validator.addMethod(
    	"tenantnameAvailable",
    	function(value, element, param) {
    		var data = JSON.stringify({tenantname:value});
    		var result = false;
    		$.ajax({ 
    			type: "post", 
    	        url: CONF.AJAXPATH, 
    	        async:false, 
    	        data:{m:CONF.M.TENANT,f:'tenantnameAvailable',p:data},
    	        success: function(data){ 
    	                                                                                              	result = JSON.parse(data);
    	        } 
    		});
	    	return result;
	    },
    	LANG.UI_TENANT_EXIST
    );
    
    $.validator.addMethod("namevalue", function(value, element) {
    	return this.optional( element ) ||  /^\w+$/.test( value );
    }, LANG.UI_TENANT_USER_NAME_RULE);
	
    $.validator.addMethod("passwordvalue", function(value, element) {
    	return this.optional( element ) ||  /^\w+$/.test( value );
    }, LANG.UI_TENANT_PASSWORD_RULE);
	
	
	var initListener = function(){
		
	}
	
	//初始化密码复杂度
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
			default:
				passTips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
				break;			
		}
    }
	
	return	{
		init: function(){
			initPassComplexity();
			handleValidation();
			initListener();
			
		}
	}
}();

jQuery(document).ready(function(){
	AddTenant.init();
});