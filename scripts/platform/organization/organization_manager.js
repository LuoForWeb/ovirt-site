var Organization = function(){
	var grid, gridInitFlag =false;
	var organTree, moveOrganTree;
	var _operate;
	var organForm, departForm, passwordForm;
	var passTips = "";
	var _userOrgan;
	
	
	var initOrganTree = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT, f:'getOrganizationTree', p:{}}, setOrganTree);
	}
	
	var initMoveOrganTree = function(){
		var node = organTree.getSelectedNodes();
		var data = {};
		data.uuid = node[0].organuuid;
		data.selectFlag = true;
		var params = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT, f:'getOrganizationTree', p:params}, setMoveOrganTree);
	}
	
	var setMoveOrganTree = function(zNodes){
		if(zNodes == "[]"){
			return;
		}
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true
					},
					key: {
						title: "title"
					}
				},
				callback: {
//					beforeClick: nodeMoveSelect
				}
			};
		var nodes = JSON.parse(zNodes);
		moveOrganTree = $.fn.zTree.init($("#move_organ_tree"), setting, nodes);
	}
	var setOrganTree = function(zNodes){
		if(zNodes == "[]"){
			return;
		}
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				data: {
					simpleData: {
						enable: true
					},
					key: {
						title: "title"
					}
				},
				callback: {
					beforeClick: nodeSelect,
					onClick: nodeClick
				}
			};
		var nodes = JSON.parse(zNodes);
		organTree = $.fn.zTree.init($("#organ_tree"), setting, nodes);
		var nodes = organTree.getNodes();
		if(nodes.length !=0){
			organTree.selectNode(nodes[0]);
			nodeSelect("organ_tree", nodes[0]);
		}
	}
	
	var nodeClick = function(e, treeId, treeNode) {
		if(treeNode.organuuid != "allusers"){
			getOrganQuotaFree();
		}else{
			$('#organQuota').html('');
		}
	}
	
	var nodeSelect = function(treeId,treeNode){
		organTree.expandNode(treeNode, true);
		deleteForbidButton('organMenu');
		if(treeNode.type == 1){
			addForbidButton('editDepartment');
			addForbidButton('deleteDepartment');
			addForbidButton('moveDepartTo');
		}else if(treeNode.type == 2 && treeNode.organuuid == "allusers"){
			addForbidButton('editOrgan');
			addForbidButton('deleteOrgan');
			addForbidButton('addDepartment');
			addForbidButton('editDepartment');
			addForbidButton('deleteDepartment');
			addForbidButton('moveDepartTo');
			addForbidButton('addUserTo');
		}else if(treeNode.type == 2){
			addForbidButton('editOrgan');
			addForbidButton('deleteOrgan');
		}
		handleRecords(treeNode.organuuid);
	}
	
	//添加禁止点击的按钮样式
	var addForbidButton = function(id){
		$('#' + id).unbind();
		$('#' + id + ' a').css("opacity",".4");
		$('#' + id + ' a').css("cursor","default");
	}
	//移除禁止点击的按钮样式
	var deleteForbidButton = function(id){
		initOrganEvents();
		$('#' + id + ' a').css("opacity","1");
		$('#' + id + ' a').css("cursor","pointer");
	}
	
	
	var handleRecords = function(uuid){
		var search = $('#search').val();
		var jsonData = {uuid: uuid, search:{name:search}};
		if(!gridInitFlag){
			//初始化表格
			var dataTableOpt = {
	    			'columnDefs' : [{
		                'orderable': false,
		                'targets': [0]
	    			}],
	    			"order": [
	                    [1, "asc"]
	                ],
	    	};
			grid = new Datatable();
			var data = {m:CONF.M.TENANT,f:'getOrganUsers',p:jsonData};
			grid.setAjaxParam(data);
			grid.init({src: $("#userDatatable"), dataTable:dataTableOpt});
	    	gridInitFlag = true;
		}else{
			//刷新表格
			grid.getRefresh(jsonData,function(){},true);
		}
	}
	
	var initOrganEvents = function(){
		$('#addOrgan').unbind().on('click', addOrgan);
		$('#editOrgan').unbind().on('click', editOrgan);
		$('#deleteOrgan').unbind().on('click', deleteOrgan);
		$('#addDepartment').unbind().on('click', addDepartment);
		$('#editDepartment').unbind().on('click', editDepartment);
		$('#deleteDepartment').unbind().on('click', deleteDepartment);
		$('#moveDepartTo').unbind().on('click', moveDepartTo);
		$('#addUserTo').unbind().on('click', addUserTo);
	}
	
	var initUsersEvents = function(){
		$('#moveUserTo').unbind().on('click', moveUserTo);
		$('#lockUser').unbind().on('click', lockUser);
		$('#unlockUser').unbind().on('click', unlockUser);
		$('#deleteUser').unbind().on('click', deleteUser);
		$('#resetPassword').unbind().on('click', resetPassword);
		$('#setQuota').unbind().on('click', setQuota);
	}
	
	var initListeners = function(){
		initOrganEvents();
		initUsersEvents();
		$('#submit').on('click',function(){
			submit();
		});
		
		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH
		});
		
		$('#searchbtn').on('click', searchUser);
		$('#search').on('change', setParam);
		$('#search').keypress(function (e) {
            if (e.which == 13) {
            	searchUser();
            }
        });
		
	}
	
	var searchUser = function(){
		var node = organTree.getSelectedNodes();
		var searchValue = $.trim($('#search').val());
		var data = {m:CONF.M.TENANT,f:'getOrganUsers',p:{uuid: node[0].organuuid, search:{name:searchValue}}};
		grid.setAjaxParam(data);
		grid.getRefresh({});
	}
	
	var setParam = function(){
		var node = organTree.getSelectedNodes();
		var searchValue = $.trim($('#search').val());
		var data = {m:CONF.M.TENANT,f:'getOrganUsers',p:{uuid: node[0].organuuid, search:{name:searchValue}}};
		grid.setAjaxParam(data);
	}
	
	
	var addOrgan = function(){
		//清空原来数据
		$('#organName').val('');
		initModalDiv('addOrgan');
	}
	
	var editOrgan = function(){
		var node = organTree.getSelectedNodes();
		$('#organName').val(node[0].name);
		var quota = node[0].quota;
		if(quota != -1){
			var quotaValue = parseInt(quota/calSize(3));
			$('#quotaInput').val(quotaValue);
		}
		initModalDiv('editOrgan');
	}
	
	var deleteOrgan = function(){
		var node = organTree.getSelectedNodes();
		bootbox.confirm({
            title: LANG.UI_ORGAN_DELETE_ORGANIZATION,
            message: LANG.UI_ORGAN_DELETE_ORGANIZATION_CONFIRM,
            		 
            callback: function(r) {
                if(!r) return;
                var data = {};
                data.uuid = node[0].organuuid;
                var p = JSON.stringify(data);
                $.post(CONF.AJAXPATH, {m:CONF.M.TENANT,f:"deleteOrgan",p:p}, function(d){
                	if(OPREL(d)){
                		organTree.removeNode(node[0]);
                	}
                });
            }
        });
	}
	
	var addDepartment = function(){
		var node = organTree.getSelectedNodes();
		//清空原来数据
		$('#departName').val('');
		$('#parentDepart').val(node[0].name);
		initModalDiv('addDepartment');
	}
	
	var editDepartment = function(){
		var node = organTree.getSelectedNodes();
		$('#departName').val(node[0].name);
		$('#parentDepart').val(node[0].parentname);
		var quota = node[0].quota;
		if(quota != -1){
			var quotaValue = parseInt(quota/calSize(3));
			$('#quotaInput').val(quotaValue);
		}
		initModalDiv('editDepartment');
	}
	
	var deleteDepartment = function(){
		var node = organTree.getSelectedNodes();
		bootbox.confirm({
            title: LANG.UI_ORGAN_DELETE_DEPARTMENT,
            message: LANG.UI_ORGAN_DELETE_DEPARTMEN_CONFIRM,
            		 
            callback: function(r) {
                if(!r) return;
                var data = {};
                data.uuid = node[0].organuuid;
                var p = JSON.stringify(data);
                $.post(CONF.AJAXPATH, {m:CONF.M.TENANT, f:"deleteDepartment", p:p}, function(d){
                	if(OPREL(d)){
                		organTree.removeNode(node[0]);
                	}
                });
            }
        });
	}
	
	var moveDepartTo = function(){
		var node = organTree.getSelectedNodes();
		var name = '"' + node[0].name +'"';
		$('#oldDepart').html(name);
		initMoveOrganTree();
		initModalDiv('moveDepartTo');
	}
	
	var addUserTo = function(){
		var node = organTree.getSelectedNodes();
		var name = '"' + node[0].name +'"';
		$('#addUserDepart').html(name);
		initSelectUser();
		initModalDiv('addUserTo');
	}
	
	//移动用户
	var moveUserTo = function(){
		var select = grid.getSelectedRows();
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_ORGAN_MOVE_USER, LANG.UI_ORGAN_MOVE_USER_NO_SELECT);
		}
		initMoveOrganTree();
		initModalDiv('moveUserTo');
	}
	
	//禁用用户
	var lockUser = function(){
		var select = grid.getSelectedRows();
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_USER_DISABLE, LANG.UI_USER_DISABLE_NO_SELECT);
		}
		bootbox.confirm({
            title: LANG.UI_USER_DISABLE,
            message: LANG.UI_USER_DISABLE_CONFIRM,
            callback: function(r) {
                if(!r) return;
                var data = {};
        		data.users = select;
        		data = JSON.stringify(data);
            	$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'lockUser',p:data}, function(data){
        			if(OPREL(data)){
            			grid.getRefresh({});
            		}
            	});
            }
        });
	}
	
	//启用用户
	var unlockUser = function(){
		var select = grid.getSelectedRows();
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_USER_ENABLE, LANG.UI_USER_ENABLE_NO_SELECT);
		}
		var data = {};
		data.users = select;
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'unlockUser',p:data}, function(data){
    		if(OPREL(data)){
    			grid.getRefresh({});
    		}
    	});
	}
	
	//移除用户
	var deleteUser = function(){
		var node = organTree.getSelectedNodes();
		var select = grid.getSelectedRows();
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_ORGAN_DELETE_USER, LANG.UI_ORGAN_DELETE_USER_NO_SELECT);
		}	
		
		bootbox.confirm({
            title: LANG.UI_ORGAN_DELETE_USER,
            message: LANG.UI_ORGAN_DELETE_USER_CONFIRM,
            callback: function(r) {
                if(!r) return;
                var data = {};
                data.organuuid = node[0].organuuid;
        		data.users = select;
        		data = JSON.stringify(data);
            	$.post(CONF.AJAXPATH, {m:CONF.M.TENANT,f:'removeUser',p:data}, function(data){
        			if(OPREL(data)){
            			grid.getRefresh({});
            		}
            	});
            }
        });
	}
	
	
	//重置用户密码
	var resetPassword = function(){
		var select = grid.getSelectedRows();
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_ORGAN_USER_RESET_PASSWORD, LANG.UI_ORGAN_USER_RESET_PASSWORD_NO_SELECT);
		}
		if(select.length > 1){
			return UIToastr.showInfo(LANG.UI_ORGAN_USER_RESET_PASSWORD, LANG.UI_ORGAN_USER_RESET_PASSWORD_SELECT_ONE);
		}
		initModalDiv('resetPassword');
	}
	
	//设置用户配额
	var setQuota = function(){
		var select = grid.getSelectedRows();
		var data = grid.getDataTable().data();
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_ORGAN_USER_SET_QUOTA, LANG.UI_ORGAN_USER_SET_QUOTA_NO_SELECT);
		}
		if(select.length > 1){
			return UIToastr.showInfo(LANG.UI_ORGAN_USER_SET_QUOTA, LANG.UI_ORGAN_USER_SET_QUOTA_SELECT_ONE);
		}
		for(var i=0;i<data.length;i++){
			if(select[0] == data[i][5]){
				var des = '"' + data[i][1] +'"';
				$('#quotaUser').html(des);
				_userOrgan = data[i][6];
			}
		}
		initModalDiv('setQuota');
	}
	
	var initModalDiv = function(operate){
		$('.addOrganDiv').hide();		//添加组织
		$('.editOrganDiv').hide();		//编辑组织
		$('.addDepartDiv').hide();		//添加部门
		$('.editDepartDiv').hide();		//编辑部门
		$('.moveDepartToDiv').hide();	//移动部门
		$('.addUserToDiv').hide();		//添加用户到部门
		$('.moveUserToDiv').hide();		//移动用户
		$('.resetPasswordDiv').hide();	//重置用户密码
		$('.setQuotaDiv').hide();		//设置用户配额
		$('.moveTreeDiv').hide();		//移动部门树
		$('.organDiv').hide();			//组织
		$('.departDiv').hide();			//部门
		$('.quotaDiv').hide();			//配额
		var height = '170px';
		switch(operate){
			case "addOrgan":
				$('.addOrganDiv').show();
				$('.organDiv').show();
				$('.quotaDiv').show();
				break;
			case "editOrgan":
				$('.editOrganDiv').show();
				$('.organDiv').show();
				$('.quotaDiv').show();
				break;
			case "addDepartment":
				$('.addDepartDiv').show();
				$('.departDiv').show();
				$('.quotaDiv').show();
				break;
			case "editDepartment":
				$('.editDepartDiv').show();
				$('.departDiv').show();
				$('.quotaDiv').show();
				break;
			case "moveDepartTo":
				height = '300px';
				$('.moveTreeDiv').show();
				$('.moveUserToDiv').hide();
				$('.moveDepartToDiv').show();
				break;
			case "addUserTo":
				height = '240px';
				$('.addUserToDiv').show();
				$('.quotaDiv').show();
				break;
			case "moveUserTo":
				height = '300px';
				$('.moveTreeDiv').show();
				$('.moveDepartToDiv').hide();
				$('.moveUserToDiv').show();
				break;
			case "resetPassword":
				$('.resetPasswordDiv').show();
				break;
			case "setQuota":
				$('.setQuotaDiv').show();
				$('.quotaDiv').show();
				break;
		}
		_operate = operate;
		$('#modaldiv').modal({'width': '500px', height: height});
	}
	
	
	// validation using icons
    var initPasswordForm = function() {
        // for more info visit the official plugin documentation: 
            // http://docs.jquery.com/Plugins/Validation

    	passwordForm = $('#repasswordForm');
            var error2 = $('.alert-danger', passwordForm);
            var success2 = $('.alert-success', passwordForm);
            
            
            passwordForm.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: {
                	upassword: {
                     	minlength: CONF.PASS_LENGTH,
                         required: true,
                         passcomplexity: true
                     },
                     rpassword: {
                     	minlength: CONF.PASS_LENGTH,
                     	required: true,
                        equalTo: "#upassword",
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
	
	// validation using icons
    var initDepartForm = function() {
        // for more info visit the official plugin documentation: 
            // http://docs.jquery.com/Plugins/Validation

    	departForm = $('#departForm');
            var error2 = $('.alert-danger', departForm);
            var success2 = $('.alert-success', departForm);
            
            
            departForm.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: {
                    departname: {
                        required: true,
                        departnameAvailable: true,
                        maxlength: 16
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
            
    }
	
	// validation using icons
    var initOrganForm = function() {
        // for more info visit the official plugin documentation: 
            // http://docs.jquery.com/Plugins/Validation

    	organForm = $('#organForm');
            var error2 = $('.alert-danger', organForm);
            var success2 = $('.alert-success', organForm);
            
            
            organForm.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: {
                    organname: {
                        required: true,
                        organnameAvailable: true,
                        maxlength: 16
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
            
    }
    

    $.validator.addMethod(
    	"organnameAvailable",
    	function(value, element, param) {
    		var params = {organname:value, type: 1};
    		var node = organTree.getSelectedNodes();
    		if(_operate == "editOrgan"){
    			params.uuid = node[0].organuuid;
    		}
    		var data = JSON.stringify(params);
    		var result = false;
    		$.ajax({ 
    			type: "post", 
    	        url: CONF.AJAXPATH, 
    	        async:false, 
    	        data:{m:CONF.M.TENANT,f:'organnameAvailable',p:data},
    	        success: function(data){ 
    	        	result = JSON.parse(data);
    	        } 
    		});
	    	return result;
	    },
	    LANG.UI_ORGAN_ORGANIZATION_NAME_EXIST
    );
    

    $.validator.addMethod(
    	"departnameAvailable",
    	function(value, element, param) {
    		var params = {organname:value, type: 2};
    		var node = organTree.getSelectedNodes();
    		if(_operate == "editDepartment"){
    			params.uuid = node[0].organuuid;
    		}
    		var data = JSON.stringify(params);
    		var result = false;
    		$.ajax({ 
    			type: "post", 
    	        url: CONF.AJAXPATH, 
    	        async:false, 
    	        data:{m:CONF.M.TENANT,f:'organnameAvailable',p:data},
    	        success: function(data){ 
    	        	result = JSON.parse(data);
    	        } 
    		});
	    	return result;
	    },
    	LANG.UI_ORGAN_DEPARTMENT_NAME_EXIST
    );
    
    //最终确认信息
	var submit = function(){
		switch(_operate){
			case "addOrgan":
				addOrganSubmit();
				break;
			case "editOrgan":
				editOrganSubmit();
				break;
			case "addDepartment":
				addDepartmentSubmit();
				break;
			case "editDepartment":
				editDepartmentSubmit();
				break;
			case "moveDepartTo":
				moveDepartToSubmit();
				break;
			case "addUserTo":
				addUserToSubmit();
				break;
			case "moveUserTo":
				moveUserToSubmit();
				break;
			case "resetPassword":
				resetPasswordSubmit();
				break;
			case "setQuota":
				setQuotaSubmit();
				break;
		}
	}
	

	var addOrganSubmit = function(){
		if (!organForm.validate().form()) return;
		var data = {};
		data.organname = $('#organName').val();
		data.parentuuid = "";
		data.type = 1;
		data.quota = -1;
		var params = JSON.stringify(data);
		Metronic.blockUI({target: '#modaldiv',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT, f:"addOrgan", p: params}, function(d){
			Metronic.unblockUI('#modaldiv');
			if(OPREL(d)){
				var jsonData = JSON.parse(d);
				var newNode = jsonData.ext;
				var parentNode = organTree.getNodeByParam("id", newNode.pId, null);
				organTree.addNodes(parentNode, newNode);
    			$('#modaldiv').modal('hide');
    			getOrganQuotaFree();
    		}
		});
	}
	
	var editOrganSubmit = function(){
		if (!organForm.validate().form()) return;
		var node = organTree.getSelectedNodes();
		var data = {};
		data.uuid = node[0].organuuid;
		data.organname = $('#organName').val();
		data.quota = parseInt($('#quotaInput').val()) * calSize(3);
		var params = JSON.stringify(data);
		Metronic.blockUI({target: '#modaldiv',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT, f:"editOrgan", p: params}, function(d){
			Metronic.unblockUI('#modaldiv');
			if(OPREL(d)){
				node[0].name = data.organname;
				node[0].quota = data.quota;
				organTree.updateNode(node[0]);
    			$('#modaldiv').modal('hide');
    			getOrganQuotaFree();
    		}
		});
	}
	
	var addDepartmentSubmit = function(){
		if (!departForm.validate().form()) return;
		var node = organTree.getSelectedNodes();
		var data = {};
		data.parentname = node[0].name;
		data.parentuuid = node[0].organuuid;
		data.departname = $('#departName').val();
		data.type = 2;
		var a  = parseInt($('#quotaInput').val());
		data.quota = parseInt($('#quotaInput').val()) * calSize(3);
		var params = JSON.stringify(data);
		Metronic.blockUI({target: '#modaldiv',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT, f:"addDepartment", p: params}, function(d){
			Metronic.unblockUI('#modaldiv');
			if(OPREL(d)){
				var jsonData = JSON.parse(d);
				var newNode = jsonData.ext;
				var parentNode = organTree.getNodeByParam("id", newNode.pId, null);
				organTree.addNodes(parentNode, newNode);
    			$('#modaldiv').modal('hide');
    			getOrganQuotaFree();
    		}
		});
	}
	
	var editDepartmentSubmit = function(){
		if (!departForm.validate().form()) return;
		var node = organTree.getSelectedNodes();
		var data = {};
		data.uuid = node[0].organuuid;
		data.parentuuid = node[0].parentuuid;
		data.departname = $('#departName').val();
		data.quota = parseInt($('#quotaInput').val()) * calSize(3);
		var params = JSON.stringify(data);
		Metronic.blockUI({target: '#modaldiv',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT, f:"editDepartment", p: params}, function(d){
			Metronic.unblockUI('#modaldiv');
			if(OPREL(d)){
				node[0].name = data.departname;
				node[0].quota = data.quota;
				organTree.updateNode(node[0]);
				$('#modaldiv').modal('hide');
				getOrganQuotaFree();
    		}
		});
	}
	
	var moveDepartToSubmit = function(){
		var node = organTree.getSelectedNodes();
		var desNode = moveOrganTree.getSelectedNodes();
		var data = {};
		data.uuid = node[0].organuuid;
		data.parentuuid =  desNode[0].organuuid;
		data.parentname = desNode[0].name;
		var params = JSON.stringify(data);
		Metronic.blockUI({target: '#modaldiv',animate: true});
		$.post(CONF.AJAXPATH,{m:CONF.M.TENANT,f:"moveDepartTo",p:params},function(d){
			Metronic.unblockUI('#modaldiv');
			if(OPREL(d)){
				var allNodes = organTree.getNodes();
				for(var i=0;i<allNodes.length;i++){
					if(allNodes[i].id == desNode[0].id){
						organTree.moveNode(allNodes[i], node[0], "inner");
					}
				}
				$('#modaldiv').modal('hide');
			}
		});
	}
	
	var addUserToSubmit = function(){
		var userList = $('#selectUser').selectpicker('val'); 
		if(userList.length == 0){
			return UIToastr.showInfo(LANG.UI_ORGAN_ADD_USER_TO_DEPARTMENT, LANG.UI_ORGAN_ADD_USER_TO_DEPARTMENT_NO_SELECT);
		}
		var node = organTree.getSelectedNodes();
		var data = {};
		data.users = userList;
		data.uuid = node[0].organuuid;
		data.quota = parseInt($('#quotaInput').val()) * calSize(3);
		var params = JSON.stringify(data);
		Metronic.blockUI({target: '#modaldiv',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT,f:"addUserTo", p:params}, function(d){
			Metronic.unblockUI('#modaldiv');
			if(OPREL(d)){
				handleRecords(node[0].organuuid);
				$('#modaldiv').modal('hide');
			}
		});
	}
	
	var moveUserToSubmit = function(){
		var select = grid.getSelectedRows();
		var node = moveOrganTree.getSelectedNodes();
		var oldNode = organTree.getSelectedNodes();
		var data = {};
		data.oldorganuuid = oldNode[0].organuuid;
		data.organuuid = node[0].organuuid;
		data.users = select;
		var params = JSON.stringify(data);
		Metronic.blockUI({target: '#modaldiv',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT,f:"moveUserToOrgan",p:params}, function(d){
			Metronic.unblockUI('#modaldiv');
			if(OPREL(d)){
				handleRecords(oldNode[0].organuuid);
				$('#modaldiv').modal('hide');
			}
		});
		
	}
	
	var resetPasswordSubmit = function(){
		if (!passwordForm.validate().form()) return;
		var node = organTree.getSelectedNodes();
		var select = grid.getSelectedRows();
		var data = {};
		data.uuid = select[0];
		data.password = hex_md5($("input[name=upassword]").val());
		var params = JSON.stringify(data);
		Metronic.blockUI({target: '#modaldiv',animate: true});
		$.post(CONF.AJAXPATH,{m:CONF.M.TENANT,f:"organRepassword",p:params}, function(d){
			Metronic.unblockUI('#modaldiv');
			if(OPREL(d)){
				handleRecords(node[0].organuuid);
				$('#modaldiv').modal('hide');
			}
		});
	}
	
	var setQuotaSubmit = function(){
		var select = grid.getSelectedRows();
		var data = {};
		data.uuid = select[0];
		data.quota = parseInt($('#quotaInput').val()) * calSize(3);
		data.organuuid = _userOrgan;
		var params = JSON.stringify(data);
		Metronic.blockUI({target: '#modaldiv',animate: true});
		$.post(CONF.AJAXPATH,{m:CONF.M.TENANT,f:"setUserQuota",p:params}, function(d){
			Metronic.unblockUI('#modaldiv');
			if(OPREL(d)){
				handleRecords(node[0].organuuid);
				$('#modaldiv').modal('hide');
			}
		});
		
	}
	
	var initSpinner = function(){
		//初始化容量单位
    	$('#spinnerNum').spinner({value: 100, step: 5, min: 1,max: 999999});
	}
	
	//初始化用户列表
	var initSelectUser = function(data){
		var node = organTree.getSelectedNodes();
		var data = {};
		data.uuid = node[0].organuuid;
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.TENANT,f:'getOrganAddUsers',p:p}, function(p){
			var jsondata = JSON.parse(p);
			var userList = $("#selectUser");
			userList.empty();
			for(var i=0;i<jsondata.length;i++){
				var option = $("<option>").text(jsondata[i].user_name).val(jsondata[i].user_uuid);
				userList.append(option);
			}
			userList.selectpicker('refresh');
		});
	}
	
	//字节转换
    var calSize = function(result){
    	if(!result || result == ""){
    		return 0;
    	}
    	var type =  ["B", "KB", "MB", "GB", "TB", "PB", "EB", "ZB", "YB", "BB", "NB", "DB"];
    	var i = 1;
    	var j=0;
    	for(var j=0; j<result;j++){
    		i = i*1024;
    	}
    	
		return i;
    }
    
    //初始化验证
    var initForm = function(){
    	initOrganForm();
		initDepartForm();
		initPasswordForm();
    }
    
    var getOrganQuotaFree = function(){
    	var node = organTree.getSelectedNodes();
    	var data = {};
    	data.uuid = node[0].organuuid;
    	data.quota = node[0].quota;
    	data.type = node[0].type;
    	var params = JSON.stringify(data);
    	$.post(CONF.AJAXPATH,{m:CONF.M.TENANT,f:"getOrganQuotaFree",p:params},function(d){
    			var data = JSON.parse(d);
    			var des = '<img src="'+data.icon+'">' + node[0].name + ': ' + data.quotaDes;
    			$('#organQuota').html(des);
    	});
    }
    
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
	
	return {
		init: function(){
			initPassComplexity();
			initSpinner();
			initListeners();
			initOrganTree();
			handleRecords();
			initForm();
		}
	}
	
}();

jQuery(document).ready(function(){
	Organization.init();
});