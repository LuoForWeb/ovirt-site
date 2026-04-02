var add_UserGroup = function(){
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
	
//		$('#userGroup_Tenant').on('change',selectTenantOnchange);
		$('#addsubmit').on('click',function(){
			var form = $("#form_addUserGroup");
			if (form.validate().form()){
				get_data()
			}
		});
		$('#cancelBut').on('click',function(){
			LOCATION('./content/platform/users/user_group.php','safety');
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
					minlength:3,
					maxlength:64
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
		
	};
	
	//初始化表单数据
	var getTenant_data = function(){
		
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getAllTenant',p:{}}, function(d){
			var data = JSON.parse(d);
			var tenant = $("#userGroup_Tenant");
			for(var i=0;i<data.length;i++){
				var option = $("<option>").text(data[i].tenant_name).val(data[i].tenant_uuid);
				tenant.append(option);
			}
		});
		
		
		
		
	};
	
	//选择不同租户展示不同角色及用户
	//bootstrap-select组件
	var selectTenantOnchange = function(){
		
		var userGroupTenantUUID = $('#userGroup_Tenant').val();
		var data = {'tenantuuid':userGroupTenantUUID}
		var data = JSON.stringify(data);
		
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
			user_uuid_name_id.selectpicker('refresh');
		})
	}
	
	
	//初始化角色列表
	var initRoleList = function(){
		pAjaxRequest({  'offset': 0, 'limit': 1000, role_type: is_super_role},'/api/v1/roles','GET',function (d){
			var rows = d.data.rows;
			var role_uuid_name_id = $("#userGroup_Role");
			role_uuid_name_id.empty();
			for(var i=0;i<rows.length;i++){
				var option = $("<option>").text(rows[i].role_name).val(rows[i].role_uuid);
				role_uuid_name_id.append(option);
			}
			role_uuid_name_id.selectpicker('refresh');
		});
	}


	
	
	
	//获取并且提交整个页面填写数据
	var get_data = function(){
		var data ={};
		var userGroupName = $('#userGroupName').val();
		var userGroupDescription = $('#userGroupDescription').val();
		var userGroupRoleUUID =  $('#userGroup_Role').selectpicker('val');
		var userGroupUserUUID =  $('#userGroup_User').selectpicker('val');
        data['user_group_name'] = userGroupName;
		data['description'] = userGroupDescription;
		data['role'] = userGroupRoleUUID;
		data['user'] = userGroupUserUUID;
		pAjaxRequest(data,'/api/v1/usergroups','POST',function (res){
			if (operateResponseList(res, LANG.UI_USER_GROUP_ADD)) {
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
//			getTenant_data();
			initUserList();
			initRoleList();
			rule_form();
//			selectTenantOnchange();
		}
	} 
}();
jQuery(document).ready(function() {    
	add_UserGroup.init();
});