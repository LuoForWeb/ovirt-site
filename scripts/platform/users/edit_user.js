var EditUser = function(){
	var zTree,storage;
	var _MANAGER, _OPERATOR, _AUDITOR, _userpermission;
	var oldRoleList = [], oldUsergroupList = [];
	var passTips ="";
	var oldPass;
	var _userTenantuuid = "";
	
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
                        usernameAvailable: true
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
            	//如果是旧密码md5直接返回
            	if(oldPass == value) return true;
            	var match = "";
            	switch(CONF.PASS_COMPLEXITY){
					case 1: //弱(包含字母(不区分大小写),数字)
						var pattern = "^[A-Za-z0-9!@#$%^&*,.]{" + CONF.PASS_LENGTH + ",}$";
						match = this.optional(element) || new RegExp(pattern).test(value);
						break;
					case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
						var pattern = "^(?=.*[0-9])(?=.*[A-Za-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]{"+ CONF.PASS_LENGTH + ",}$";
						match = this.optional(element) || new RegExp(pattern).test(value);
						break;
					case 3: //强(必须包含大小写字母,数字,特称字符)
						var pattern = "^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*,\\.])[0-9a-zA-Z!@#$%^&*,\\\\.]{"+ CONF.PASS_LENGTH + ",}$"
						match = this.optional(element) || new RegExp(pattern).test(value);
						break;
            	}
        		return match;
            }, passTips);
    };
    
    
    var submit = function(){
    	var data = {};
    	var userType = $('#usertype').val();
    	var data = {};
    	data.username = $("input[name=name]").val();
    	data.domainuuid = $('#domainlist').val();
    	if(data.usertype == 1){
    		data.domainuuid = "";
    	}
    	data.useruuid = $('#useruuid').val();
    	data.password = hex_md5($("input[name=password]").val());
    	data.email = $("input[name=email]").val();
    	data.phone = $("input[name=number]").val();
    	data.usertype = $("#usertype").val();
//    	data.permissiontype = $("input[name=auth_type]:checked").val();
//    	data.permission = [];
    	data.roleList = $('#user_Role').selectpicker('val');
    	data.userGroupList = $('#user_Group').selectpicker('val'); 
//    	data.tenantuuid = $('#user_Tenant').val();
    	if($('#storageMode').val() == 1){
    		data.quota = -1;
    	}else{
    		data.quota =parseInt($('#spinnerNumInput').val()) * parseInt(calSize($('#unit option:selected').text()));
    	}
    	data = JSON.stringify(data);
    	Metronic.blockUI({target: '#edituserContent',animate: true,cenrerY: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'editUser',p:data}, registerResult);
    };
    
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
    
    var registerResult = function(data){
    	Metronic.unblockUI('#edituserContent');
    	if(OPREL(data)){
//    		CTLSIDEBAR('users');
        	LOCATION('./content/platform/users/users.php','safety');
    	}
    };
    
    
    $.validator.addMethod(
    	"usernameAvailable",
    	function(value, element, param) {
//    		var tenantuuid = $('#user_Tenant').val();
    		var data = JSON.stringify({username:value, tenantuuid:""});
    		var result = false;
    		$.ajax({ 
    			type: "post", 
    	        url: CONF.AJAXPATH, 
    	        async:false, 
    	        data:{m:CONF.M.USER,f:'usernameAvailable',p:data},
    	        success: function(data){ 
    	        	result = JSON.parse(data);
    	        } 
    		});
	    	return result;
	    },
    	LANG.UI_USER_NAME_EXISTS
    );
    
    //初始化用户信息
    var initUserInfo = function(){
    	var useruuid = $('#useruuid').val();
    	var data = JSON.stringify({useruuid:useruuid});
        $.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getEditUserInfo',p:data}, function(d){
        	var info = JSON.parse(d);
        	$('#name').val(info.username).prop("disabled", true);
        	$('input[name=password]').val(info.pass);
        	$('input[name=rpassword]').val(info.pass);
        	oldPass = info.pass;
        	_userTenantuuid = info.tenantuuid;
        	$('input[name=email]').val(info.email);
        	$('input[name=number]').val(info.telephone);
        	$('#usertype').val(info.usertype).prop("disabled", true);
        	if(info.usertype == 2){
        		$('.locationDiv').show();
        		initDomainList(info.domainuuid);
        	}
        	//加载租户列表
        	oldRoleList = info.role_list;
        	oldUsergroupList = info.usergroup_list;
//        	initTenantList(info.tenant_uuid);
        	initUsergroupList();
        	initRoleList();
        	
        	//刷新列表
        	$(".selectpicker").selectpicker('refresh');
        	initUserDiv(info.usertype);
        	
        	initViews();
        	var quota = parseInt(info.quota);
        	if(quota == -1){
        		quota = 20;
        	}else{
        		$('#storageMode').val(2);
            	$('#custom').show();
        	}
        	$('#spinnerNumInput').val(quota);
        	var unit = info.quota.substring(info.quota.length-2,info.quota.length);
        	if (unit == "MB"){
        		$('#unit').val(1);
        	}else if(unit == "GB"){
        		$('#unit').val(2);
        	}else{
        		$('#unit').val(3);
        	}
        });
    }
    
    var initViews = function(){
    	$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getMaxStorage',p:{}}, function(d){
    		var data = JSON.parse(d);
    		var text = data.stext;
    		storage = data.svalue;
//        	if(text.substr(-2) == 'TB'){
//        		$('#unit').val(2);
//        	}
        	$('#maxNum').append(text);
    	});
    	initspinner();
    	//切换存储方式选择事件
    	$('#custom').hide();
    	$('#storageMode').on('change', modeSelect);
    }
    
    //初始化spinner
    var initspinner = function(){
    	//post 请求数据库备份存储总大小
    	//初始化存储单位
    	$('#spinnerNum').spinner({value: 20, step: 5, min: 1,max: 9999});
    }
    
    var initListener = function(){
    	//初始化多选下拉框
    	$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});
	
//    	$('#usertype').on('change',	initUserDiv);
    }
    
    var initUserDiv = function(type){
    	if(type == 1){
    		$('.domainDiv').hide();
    		$('.locationDiv').show();
    		$('.tenantSelectDiv').show();
    	}else{
    		$('.locationDiv').hide();
    		$('.domainDiv').show();
    		$('.tenantSelectDiv').hide();
    	}
    }
    
  //初始化租户列表
    var initTenantList = function(tenantuuid){
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
			if(tenantuuid != ""){
				$('#user_Tenant').val(tenantuuid).prop("disabled", true);
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
	}
	
	//初始化角色列表
	var initRoleList = function(){
		$.post(CONF.AJAXPATH, {m: CONF.M.USER, f: "getRoleList", p: {}}, function(d){
			var jsondata = JSON.parse(d);
			var roleList = $('#user_Role');
			roleList.empty();
			for(var i=0;i<jsondata.length;i++){
				var option = $('<option>').text(jsondata[i].role_name).val(jsondata[i].role_uuid);
				roleList.append(option);
			}
			//加载角色列表
        	$('#user_Role').selectpicker('val', oldRoleList);
        	roleList.selectpicker('refresh');
			
		});
	}
	
	
	//初始化用户组列表
	var initUsergroupList = function(){
		var data = {};
		data.useruuid = $('#useruuid').val();
		data.editflag = true;
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m: CONF.M.USER, f: "getUsergroupList", p: p}, function(d){
			var jsondata = JSON.parse(d);
			var usergroupList = $('#user_Group');
			usergroupList.empty();
			for(var i=0;i<jsondata.length;i++){
				var option = $('<option>').text(jsondata[i].usergroup_name).val(jsondata[i].usergroup_uuid);
				usergroupList.append(option);
			}
			//加载用户组列表
        	$('#user_Group').selectpicker('val', oldUsergroupList);
			if(_userTenantuuid && _userTenantuuid != ""){
				usergroupList.prop('disabled', true);
			}
			usergroupList.selectpicker('refresh');
		});
	}
    
	//初始化AD域供应商列表
	var initDomainList = function(domainuuid){
		$.post(CONF.AJAXPATH, {m:CONF.M.DOMAINSERVER, f:"getDomainSelectList", p:{}}, function(d){
			var jsonData = JSON.parse(d);
			var domainlist = $('#domainlist');
			domainlist.empty();
			var option = $('<option>').text(LANG.UI_DOMAIN_SERVER_SELECT_PROVIDER).val("");
			domainlist.append(option);
			for(var i=0;i<jsonData.length;i++){
				var option = $('<option>').text(jsonData[i].text).val(jsonData[i].value);
				domainlist.append(option);
			}
			domainlist.val(domainuuid).prop("disabled", true);
		});
		
	}
	
	var initPassComplexity = function(){
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
        	initPassComplexity();
        	initUserInfo();
            handleValidation();
            initListener();
        }

    };
}();

jQuery(document).ready(function() {   
	EditUser.init();
});