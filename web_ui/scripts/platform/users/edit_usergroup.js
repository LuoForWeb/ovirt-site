var edit_UserGroup = function(){
	var role_list;
	var user_list;
	var tenantUUID;
	var is_super_role = 1; // 1表示其它角色，2表示全局观察者
	var isMaster = false; // 标记是否是超级管理员
	
	//添加监听事件
	var addListeners = function(){
		
		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});

		$('#choose_global_flag').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				//开
				is_super_role = 2;
			}else{
				//关
				is_super_role = 1;
			}
			// 重新渲染角色列表
			initRoleList();
			initUserList();
		});
//		$('#userGroup_Tenant').on('change', selectTenantOnchange);
		
		
	};
	
	//表单规则
	var rule_form = function(){
		var form = $("#form_addUserGroup");
		var error2 = $('.alert-danger', form);
		var success2 = $('.alert-success', form);
		
		form.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	userGroupName: {
            		required:true,
            		
            		
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
		$('#addsubmit').on('click',function(){
			if (form.validate().form()){
				get_data();
			}
		});
		$('#cancelBut').on('click',function(){
			LOCATION('./content/platform/users/user_group.php','safety');
		});
		
	};
	
	var getTenant_data = function(){
		//初始化租户列表
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getAllTenant',p:{}}, function(d){
			var data = JSON.parse(d);
			var tenant = $("#userGroup_Tenant");
			for(var i=0;i<data.length;i++){
				var option = $("<option>").text(data[i].tenant_name).val(data[i].tenant_uuid);
				tenant.append(option);
			}
			
			$('#userGroup_Tenant').val(tenantUUID).prop("disabled", true);
		});
	}
	
	//初始化表单数据
	var getUserGroupOldInfo = function(){
		//初始化用户组配置信息
		var uuid = $('#usergroupuuid').val();
		var data = {'usergroupuuid':uuid};
		
		pAjaxRequest(data,'/api/v1/usergroups/data','GET',function(d){
			var data = d.data;
			var user_group_name = data['user_group_name'];
			var description = data['description'];
			tenantUUID = data['tenant_uuid'];
			role_list = data['role'];
			user_list = data['user'];
			$('#choose_global_flag').bootstrapSwitch('state', data['is_super_role']);
			if (data['is_super_role']) {
				is_super_role = 2;
			}
			//$('#userGroupName').val(user_group_name).prop("disabled", true);
			$('#userGroupName').val(user_group_name); // 更改为可编辑
			$('#userGroupDescription').val(description);
//			selectTenantOnchange();
			initUserList();
			initRoleList();
//			getTenant_data();	//获取租户列表
		});
		
	};
	
	//选择不同租户展示不同角色及用户
	//bootstrap-select组件
	var selectTenantOnchange = function(){
		var userGroupTenantUUID = $('#userGroup_Tenant').val();
		var data = {'tenantuuid':userGroupTenantUUID}
		var data = JSON.stringify(data);
		initUserList(data);
	}
	
	
	//初始化角色列表
	var initRoleList = function(){
		var data = {};
		data.editflag = true;
		data.usergroupuuid = $('#usergroupuuid').val();
		data.offset = 0;
		data.limit = 1000;
		data.role_type = is_super_role;
		pAjaxRequest(data,'/api/v1/roles','GET',function(d){
			var rows = d.data.rows;
			var role_uuid_name_id = $("#userGroup_Role");
			role_uuid_name_id.empty();
			for(var i=0;i<rows.length;i++){
				var option = $("<option>").text(rows[i].role_name).val(rows[i].role_uuid);
				role_uuid_name_id.append(option);
			}
			$('#userGroup_Role').selectpicker('val',role_list);
			role_uuid_name_id.selectpicker('refresh');
		});
	}
	
	//初始化用户列表
	var initUserList = function(){
		var data = {};
		data.usergroupflag = true;
		data.role_type = is_super_role;
		pAjaxRequest(data,'/api/v1/roles/userlist','GET',function(d){
			var data = d.data;
			var user_uuid_name_id = $("#userGroup_User");
			user_uuid_name_id.empty();
			for(var i=0;i<data.length;i++){
				var option = $("<option>").text(data[i].user_name).val(data[i].user_uuid);
				user_uuid_name_id.append(option);
			}
			$('#userGroup_User').selectpicker("val",user_list);
			user_uuid_name_id.selectpicker('refresh');
		});
		
	}
	
	
	//获取并且提交整个页面填写数据
	var get_data = function(){
		var data ={};
		var userGroupName = $('#userGroupName').val();
		var userGroupDescription = $('#userGroupDescription').val();
//		var userGroupTenantUUID = $('#userGroup_Tenant').val();
		var userGroupRoleUUID =  $('#userGroup_Role').selectpicker('val');
		var userGroupUserUUID =  $('#userGroup_User').selectpicker('val');
		var userGroupUUID = $('#usergroupuuid').val();
		data['user_group_name'] = userGroupName;
		data['description'] = userGroupDescription;
//        dict_usergroup_data['userGroupTenantUUID'] = userGroupTenantUUID;
		data['role'] = userGroupRoleUUID;
		data['user'] = userGroupUserUUID;
		data['user_group_uuid'] = userGroupUUID;
		pAjaxRequest(data,'/api/v1/usergroups','PUT',function (res){
			if (operateResponseList(res, LANG.UI_USER_GROUP_MODIFY)) {
				LOCATION('./content/platform/users/user_group.php','safety');
			}
		});
	};

	// 初始化用户信息
	var initUserInfo = function () {
		pAjaxRequest({},'/api/v1/users/self/info','GET',function (d){
			if (d.code == 0) {
				isMaster = d.data.user_level == 1;
			}
			if (!isMaster) {
				$('#div_global_auth').hide();
			}
		});
	}

	return{
		init: function(){
			addListeners();
			initUserInfo();
			getUserGroupOldInfo();
			rule_form();
			
		}
	} 
}();
jQuery(document).ready(function() {    
	edit_UserGroup.init();
});