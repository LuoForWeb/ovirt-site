var EditPassword = function(){
	var passTips ="";
	var div  =".form-body";
	var submitstep =  0; //点击提交的次数
	var _USERLEVEL = ''; //用户等级
	var _PRODUCTTYPE = ''; //版本
	var _ISTHREEPOWERS = ''; //是否是三权
	// validation using icons
    var handleValidation = function() {
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
                    password: {
                    	//minlength: CONF.PASS_LENGTH,
                        required: true,
                        passcomplexity: true,
						compareOldcode:true
                    },
                    rpassword: {
                    	// minlength: CONF.PASS_LENGTH,
                    	required: true,
                        equalTo: "#password",
						compareOldcode: true
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
            
            $("#addsubmit").click(function(){
				submitstep++;
				if(submitstep>=2){
					return;
				}
            	if (form2.validate().form()) {
                	submit();
                }else{
					submitstep = 0;
				}
            });
            
            $("#cancel").click(function(){
				CTLSIDEBAR('survey');
				var url = "./content/platform/databackup_center.php";
				if(CONF.ENTERPRISE == 'inspur_enterprise'){
					//同sidebar
					url = "./content/platform/databackup_center_vinchin.php";
				}
				//默认新版路径
				switch (_PRODUCTTYPE) {
					//专业版
					case 'professional':
						//如果是专业版: 老版路径
						url = './content/platform/databackup_center_vinchin.php';
						//如果不是三权分立,则不处理
						if (!_ISTHREEPOWERS) {
							//以下不是三权时
							switch (_USERLEVEL) {
								//admin
								case 1:
									break;
								default:
									url = './content/platform/databackup_center.php';
									break;
							}
							break;
						}
						//以下是三权时
						switch (_USERLEVEL) {
							//老版首页
							case 2:
								url = './content/platform/databackup_center_vinchin.php';
								break;
							//新版首页(个人首页)
							case 5:
								url = './content/platform/databackup_center.php';
								break;
							//没有首页
							case 3:
							case 4:
								url = 'javascript:;';
								break;
						}
						break;
					//基础版: 显示新版首页
					case 'basic':
						break;
					//白牌版: 显示新版首页
					case 'special':
						break;
					//项目版: 显示新版首页
					case 'project':
						url = './content/platform/databackup_center_pro.php';
						//以下是三权时
						switch (_USERLEVEL) {
							case 2:
							case 5:
								url = './content/platform/databackup_center_pro.php';
								break;
							//没有首页
							case 3:
							case 4:
								url = 'javascript:;';
								break;
						}
						break;
				};
				//先获取版本是哪种
				//判断是默认账户还是租户内部账户
				//深信服需要特殊处理跳到index.html
				if(CONF.ENTERPRISE == 'sangfor_enterprise'){
					if(CONF.TENANTUUID != ""){
						url = "./content/platform/tenant_center.php";
						LOCATION(url);
					}else {
						window.location.href = "./index.html";
					}
				}else{
					if(CONF.TENANTUUID != ""){
						url = "./content/platform/tenant_center.php";
					}
					LOCATION(url);
				}
    			
            	
            });
            $.validator.addMethod("passcomplexity", function(value, element) {
            	var match = "";
				switch(CONF.PASS_COMPLEXITY){
					case 1: //弱(包含字母(不区分大小写),数字)
						var pattern = "^[\\S]{" + CONF.PASS_LENGTH + ",}$";
						match = this.optional(element) || new RegExp(pattern).test(value);
						break;
					case 2: //中(必须包含字母(不区分大小写),数字)
						var pattern = "^(?=.*[0-9])(?=.*[A-Za-z])[\\S]{" + CONF.PASS_LENGTH + ",}$";
						match = this.optional(element) || new RegExp(pattern).test(value);
						break;
					case 3: //强(必须包含大小写字母,数字)
						var pattern = "^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])[\\S]{" + CONF.PASS_LENGTH + ",}$"
						match = this.optional(element) || new RegExp(pattern).test(value);
						break;
				}
        		return match;
            }, passTips);
            
            $.validator.addMethod(
                	"oldpassAvailable",
                	function(value, element, param) {
                		var data = {password:value};
                		var result = false;
						pAjaxRequest(data,'/api/v1/users/oldpass','GET',function (d){
							let data = d.data;
							result = data.result;
						},false);
            	    	return result;
            	    },
                	LANG.UI_USER_OLD_PASSWORD_ERROR
			);

		$.validator.addMethod("compareOldcode",function (value, element) {
			var oldpass = hex_md5($('input[name=oldpass]').val());
			var password =hex_md5(value);
			if(oldpass == password){
				return false;
			}
			return true;
			},
			LANG.UI_USER_NOT_SAME_OLD_PASSWORD
		);
    };
    
    
    var submit = function(){
    	var data = {};
    	data.username = $('#username').val();
    	data.oldPassword = hex_md5($("input[name=oldpass]").val());
    	data.newPassword = hex_md5($("input[name=password]").val());
		Metronic.blockUI({target:div,animate:true});
		pAjaxRequest(data,'/api/v1/users/edit/password','POST',editResult);
    };
    
    var editResult = function(d){
    	if(d.success){
    		d.title += "," + LANG.UI_USER_RELOGIN_TIPS;
    	}
		if(operateResponseList(d)){
			setTimeout(function(){Metronic.unblockUI(div);window.location = './loginout.php',submitstep = 0;}, 5000);
		}
    };
    
    var initBasicInfo = function(){
    	_USERLEVEL = CONF.USER_LEVEL;
    	_PRODUCTTYPE = CONF.PRODUCT_TYPE;
    	_ISTHREEPOWERS = CONF.IS_THREE_POWERS;
    	switch(CONF.PASS_COMPLEXITY){
			case 1: //弱(包含字母(不区分大小写),数字)
				passTips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
				break;
			case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
				passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM;
				break;
			case 3: //强(必须包含大小写字母,数字,特称字符)
				passTips =LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_STRONG;
				break;
			default:
				passTips =LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
				break;
			
		}
    }
    
    return {
        //main function to initiate the module
        init: function () {
			initBasicInfo();
            handleValidation();
        }

    };
}();

jQuery(document).ready(function() {   
	EditPassword.init();
});