var AddUser = function(){
	var passTips = "";
	var startText;
	var edit_pwd_flag = true;
	// validation using icons
    var handleValidation = function() {
        // for more info visit the official plugin documentation: 
            // http://docs.jquery.com/Plugins/Validation

    	var form2 = $('#form_sample_2');
            var error2 = $('.alert-danger', form2);
            var success2 = $('.alert-success', form2);
            var userType = $('#usertype').val();
            var passwordFlag = true;
            if(userType == 2){
            	passwordFlag = false;
            }
            
            
            form2.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: {
                    name: {
                        minlength: 4,
                        required: true,
                        usernameAvailable: true,
                        username: true
                    },
                    password: {
                    	//minlength: CONF.PASS_LENGTH,
                        required: passwordFlag,
                        passcomplexity: true
                    },
                    rpassword: {
                    	minlength: CONF.PASS_LENGTH,
                    	required: passwordFlag,
                        equalTo: "#password",
                    },
                    email: {
//                        required: true,
                        email: true
                    },
                    number: {
//                        required: true,
                        number: true
                    },
                    
                    usertype: {
                        required: true
                    },
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
            	var currentVal = 0;
				if($('#storageMode').val() == 2){
					currentVal = parseInt($('#spinnerNumInput').val()) * parseInt(calSize($('#unit option:selected').text()));
				}
            	if (form2.validate().form()) {
            		//选择不限制容量，容量无限制，配额小于总配额允许提交
            		if($('#storageMode').val() == 1 || currentVal <= storage || storage == -1){
            			submit();
            		}else{
            			UIToastr.showWarning(LANG.UI_USER_QUOTA_EXCEPTION,LANG.UI_USER_QUOTA_EXCEPTION_EXCEED);
            		}
                }
            });
            
            $("#cancel").click(function(){
//            	CTLSIDEBAR('users');
            	LOCATION('./content/platform/users/users.php','safety');
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
    }
    
    
    
    
    //提交需要的参数
    var submit = function(){
    	var userType = $('#usertype').val();
    	
    	var data = {};
    	data.username = $("input[name=name]").val();
		data.password = hex_md5($("input[name=password]").val());
    	data.email = $("input[name=email]").val();
    	data.phone = $("input[name=number]").val();
    	data.usertype = $("#usertype").val();
    	data.domainuuid = $('#domainlist').val();
    	if(data.usertype == 1){
    		data.domainuuid = "";
			data.force_edit_pass = edit_pwd_flag;
    	}else if(data.usertype == 2){
			data.force_edit_pass = false;
		}
    	data.roleList = $('#user_Role').selectpicker('val');
    	data.userGroupList = $('#user_Group').selectpicker('val');
    	if($('#storageMode').val() == 1){
    		data.quota = -1;
    	}else{
    		data.quota =parseInt($('#spinnerNumInput').val()) * parseInt(calSize($('#unit option:selected').text()));
    	}
    	Metronic.blockUI({target: '#adduserContent',animate: true,cenrerY: true,});
		pAjaxRequest(data,'/api/v1/users','POST',registerResult);
    };
    
    var registerResult = function(d){
    	Metronic.unblockUI('#adduserContent');
		if(operateResponseList(d)){
			LOCATION('./content/platform/users/users.php','safety');
		}
    };
    
    
    
    $.validator.addMethod(
    	"usernameAvailable",
    	function(value, element, param) {
    		var data = {user_name:value, tenantuuid:""};
    		var result = false;
			pAjaxRequest(data, '/api/v1/users/check', 'GET', function (res) {
				result = res.data.value;
			}, async = false);
	    	return result;
	    },
    	LANG.UI_USER_NAME_EXISTS
    );
    
    $.validator.addMethod("username", function(value, element) {
    	return this.optional( element ) || /^[a-zA-z][a-zA-Z0-9_@.-\\]{2,64}$/i.test( value );
    }, LANG.UI_USER_NAME_FORMART);
    
    var initListener = function(){
		//默认修改密码开关开启
		$('#editPassWord').bootstrapSwitch('state', true);
		//
		$('#editPassWord').on('switchChange.bootstrapSwitch', function () {
			if (this.checked) {
				edit_pwd_flag = true;
			} else {
				edit_pwd_flag = false;
			}
		})
    	//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});
	
    	$('#usertype').on('change',	initUserDiv);
    	
    	//活动目录选择切换事件
    	$('#domainlist').on('change', initDomainUserHandler);
    	//切换存储方式选择事件
    	$('#custom').hide();
    	$('#storageMode').on('change', modeSelect);
    	initspinner();
    	initQuota();
    	$('#name').on('keyup', function(){
    		//如果不是以startText开头的，就把文本框内的值设为startText
    		var userType = $('#usertype').val();
    		if(userType == 1) return;
    	    (this.value.indexOf(startText) === 0) || (this.value = startText);
    	});
    };
    
  //初始化spinner
    var initspinner = function(){
    	//post 请求数据库备份存储总大小
    	//初始化存储单位
    	$('#spinnerNum').spinner({value: 20, step: 5, min: 1,max: 9999});
    }
    
    var initQuota = function(){
		pAjaxRequest({},'/api/v1/storages/max_resource','GET',function (d) {
			var res = d.data;
			var text = res.stext;
			storage = res.svalue;
			if (text.substr(-2) == 'TB') {
				$('#unit').val(2);
			}
			$('#maxNum').append(text);
		});
    }
    
  //字节转换
    var calSize = function(result){
    	var type =  ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB"];
    	var i = 1;
    	var j=0;
    	while(type[j] != result){
    		i = i*1024;
			j++;
    	}
    	
		return i;
    }
    
    //存储空间方式选择
    var modeSelect = function(){
    	if($('#storageMode').val() == 2){
    		$('#custom').show();
    	}else{
    		$('#custom').hide();
    	}
    }
    
    
    var initDomainUserHandler = function(){
    	var domainuuid = this.value;
    	var domainname = $('#domainlist option:selected').text();
    	if(domainuuid != ""){
    		var des = domainname + "\\";
    		$('#name').val(des);
    		$('#name').prop("disabled", false);
    	}else{
    		$('#name').empty();
    		$('#name').prop("disabled", true);
    	}
    	startText = $('#name').val(); //获取用户名开头字符串
    	var params = JSON.stringify({domainuuid: domainuuid});
    	$.post(CONF.AJAXPATH, {m: CONF.M.DOMAINSERVER, f:"getDomainUsers", p: params}, function(d){
    		
    	});
    }
    
    var initUserDiv = function(){
    	$('#name').val('');
    	if(this.value == "1"){
    		$('.domainDiv').hide();
    		$('.locationDiv').show();
			$('#force-edit-pwd').show();
    		$('#password').val('');
    		$('input[name=rpassword]').val('');
    		$('#name').prop("disabled", false);
    		if(CONF.TENANTUUID == ""){
    			$('.tenantSelectDiv').show();
    		}
    	}else{
    		$('.locationDiv').hide();
			$('#force-edit-pwd').hide();
    		$('.domainDiv').show();
    		$('#name').prop("disabled", true);
    		$('#domainlist').val('');
    		$('#password').val('Admin@3R');
    		$('input[name=rpassword]').val('Admin@3R');
    		//隐藏租户关联
    		$('.tenantSelectDiv').hide();
    	}
    	startText = $('#name').val(); //获取用户名开头字符串
    	
    }
    
    //初始化租户列表
    var initTenantList = function(){
    	$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getAllTenant',p:{}}, function(d){
			var data = JSON.parse(d);
			var tenant = $("#user_Tenant");
			tenant.empty();
			var option = $("<option>").text(LANG.UI_TENANT_GLOBAL).val('');
			tenant.append(option);
			for(var i=0;i<data.length;i++){
				var option = $("<option>").text(data[i].tenant_name).val(data[i].tenant_uuid);
				tenant.append(option);
			}
			
			$('#user_Tenant').on('change', selectTenantOnchange);
			selectTenantOnchange();
		});
    }
    
    //选择不同租户展示不同角色及用户
	var selectTenantOnchange = function(){
		var userGroupTenantUUID = $('#user_Tenant').val();
		var data = {'tenantuuid':userGroupTenantUUID}
		var data = JSON.stringify(data);
//		initRoleList(data);
		initUsergroupList(data);
	}
	
	//初始化角色列表
	var initRoleList = function(){
		pAjaxRequest({},'/api/v1/users/roles/list','GET',function (d){
			var res = d.data;
			getRoleList = res;
			var roleList = $('#user_Role');
			roleList.empty();
			for (var i = 0; i < res.length; i++) {
				var option = $('<option>').text(res[i].role_name).val(res[i].role_uuid);
				roleList.append(option);
			}
			roleList.selectpicker('refresh');
		});
		
	}
	
	
	//初始化用户组列表
	var initUsergroupList = function(){
		pAjaxRequest({},'/api/v1/users/group/list','GET',function (d){
			var res = d.data;
			var usergroupList = $('#user_Group');
			usergroupList.empty();
			for (var i = 0; i < res.length; i++) {
				var option = $('<option>').text(res[i].usergroup_name).val(res[i].usergroup_uuid);
				usergroupList.append(option);
			}
			usergroupList.selectpicker('refresh');
		});
	}
    
	//初始化AD域供应商列表
	var initDomainList = function(){
		pAjaxRequest({},'/api/v1/domains/list','GET',function (d){
			var res = d.data;
			var domainlist = $('#domainlist');
			domainlist.empty();
			var option = $('<option>').text(LANG.UI_DOMAIN_SERVER_SELECT_PROVIDER).val("");
			domainlist.append(option);
			for (var i = 0; i < res.length; i++) {
				var option = $('<option>').text(res[i].text).val(res[i].value);
				domainlist.append(option);
			}
		})
		
	}
	
	var initPassComplexity = function(){
    	switch(CONF.PASS_COMPLEXITY){
			case 1: //弱(包含字母(不区分大小写),数字,特殊字符)
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
        	initPassComplexity();
            handleValidation();
            initListener();
//            initTenantList();
            initUsergroupList();
            initRoleList();
            initDomainList();
        }

    };
}();

jQuery(document).ready(function() {   
	AddUser.init();
});