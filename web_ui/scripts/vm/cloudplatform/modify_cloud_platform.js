var ModifyVcenter = function(){
	var platformUuid;
	var oldRole; // zstack 原角色
	var oldUsername;
	var detail;
	// validation using icons
    var handleValidation = function() {
            var form2 = $('#form_sample_2');
            var error2 = $('.alert-danger', form2);
            var success2 = $('.alert-success', form2);

            form2.validate({
                errorElement: 'span', //default input error message container
                errorClass: 'help-block help-block-error', // default input error message class
                focusInvalid: false, // do not focus the last invalid input
                ignore: "",  // validate all fields including form hidden input
                rules: {
                	vmtype: {
                        required: true,
                    },
                    ipaddr: {
                        ipv4Ordomain: true,
                    },
                    username: {
                    	required: true,
                    },
                    password: {
                        required: true,
                    },
                    rname: {
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
//            		success2.show();
                    error2.hide();
                    submit();
                }
            });
    };
    
    $('input[name=ipaddr]').on('change',function(){
    	$('input[name=rname]').val(this.value);
    });
    
    $('#cancelBut').on('click', function(){
    	href2VcenterManager();
    });
    
    
    var href2VcenterManager = function(){
		if ('private' == $('#cloudType').val()) {
			LOCATION('./content/vm/cloudplatform/cloud_platform_manager.php?cloudType=private', 'infrastructure');
		} else {
			LOCATION('./content/vm/cloudplatform/cloud_platform_manager.php?cloudType=public', 'infrastructure');
		}
    }
    
    var submit = function(){
    	Metronic.blockUI({target: '#modifycontent',animate: true});
    	var data = {};
    	var openstackPort = parseInt($('#openstackPort').val());
    	var url_interface = $('#portType').val(); 
    	var domain = $('#domain').val();
    	//未填写域默认Default
    	if(domain == ""){
    		domain = "Default";
    	}
    	data.platform_uuid = $('#vcenterUUID').val();
    	data.hypervisor_type = parseInt($('#vmtype').val());
    	data.ip = $('input[name=ipaddr]').val();
    	data.username = $("input[name=username]").val();
    	data.password = btoa($("input[name=password]").val());
    	data.rname = $('input[name=rname]').val();
    	data.extra = {};
    	switch(data.hypervisor_type){
			case CONF.VM_TYPE.FLEXCLOUD:
			case CONF.VM_TYPE.OPENSTACK:
			case CONF.VM_TYPE.FLEXHCS:
			case CONF.VM_TYPE.INCLOUDOPENSTACK:
			case CONF.VM_TYPE.SUGONCLOUDVIEW: //34
			case CONF.VM_TYPE.INSPURCLOUDPLATFORM: //35
			case CONF.VM_TYPE.EASYSTACK: //36
			case CONF.VM_TYPE.FIBERHOMEOPENSTACK: //37
			case CONF.VM_TYPE.CTSIOPENSTACK: //38
			case CONF.VM_TYPE.AWCLOUD: //39
			case CONF.VM_TYPE.HUAWEICLOUDSTACK: //54
				var applianceuuid = "";
	    		if($('#appliancecheck').get(0).checked){
	    			applianceuuid = $('#applianceSelect').val();
	    			if(applianceuuid == ""){
	    				UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
	    				return false;
	    			}
	    		}
				if (CONF.VM_TYPE.HUAWEICLOUDSTACK == data.hypervisor_type) {
					url_interface = 'public';
					openstackPort = parseInt($('#huaweistackPort').val());
					domain = $('#huaweistackDomain').val();
				}
	    		data.extra ={
					"login_type": "token",
					"language": "zh-cn",
					"appliance_uuid": "",
		    		"keystone_public_port": openstackPort,
		    		"url_interface": url_interface,
		    		'domain': domain,
		    		'agent_uuid': applianceuuid
		    	};
				break;
			case CONF.VM_TYPE.ZSTACK:
			case CONF.VM_TYPE.NEXAVMNCSSV:
			case CONF.VM_TYPE.XSKY:
				var logintype = $('#zstackLoginType').val();
				if ('tenant' == logintype) {
					logintype = $('#zstackRole').val();
				}
				data.extra = {
					account_type: logintype
				}
				break;
			case CONF.VM_TYPE.AWS:
			case CONF.VM_TYPE.HUAWEICLOUD:
				var accountType = $('#accountType').val();
				var bsLocation = $('#bsLocation').val();
				data.extra = detail;
				data.extra.account_type = accountType;
				data.extra.backup_server_location = bsLocation;
				break;
		}
		pAjaxRequest(data, "/api/v1/vm/platforms", "PUT", function (data) {
			Metronic.unblockUI('#modifycontent');
			if (data.success) {
				UIToastr.showSuccess(LANG.UI_CLOUD_PLATFORM_MODIFY, LANG.UI_CLOUD_PLATFORM_MODIFY_SUCCESS);
				href2VcenterManager();
			} else {
				UIToastr.showWarning(LANG.UI_CLOUD_PLATFORM_MODIFY, data.message);
				return false;
			}
		}, true);
    };
    
	$.validator.addMethod("ipv4Ordomain", function(value, element) {
		var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
		var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
		//http|https
		var domainHTTP = this.optional( element ) || /^(http|https):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
		var ipv4HTTP = this.optional(element) || /^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
		return domain || ipv4 || domainHTTP || ipv4HTTP;
    }, LANG.UI_TOOLS_IP_OR_DOMAIN);

	// 初始化表单
	var initForm = function () {
		// 公有云初始全部隐藏
		if ('public' == $('#cloudType').val()) {
			$('#agentdownload').hide();
			$('#ipDiv').hide();
			$('#highConfigDiv').hide();
			$('.publicCloudDiv').hide();
			$('#awsDiv').hide();
			$('.username-label').text(LANG.UI_CLOUD_PLATFORM_AK);
			$('.password-label').text(LANG.UI_CLOUD_PLATFORM_SK);
		} else {
			$('#ipDiv').show();
			$('#highConfigDiv').show();
		}
	}
		
	//初始化虚拟化类型
	var initVMType = function(){
		var data = {};
		data.cloud_flag = true;
		data.cloud_type = $('#cloudType').val();
		pAjaxRequest(data, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
			let hypervisors = d.data.hypervisors;
			var vmtypeselect = $('#vmtype');
			vmtypeselect.empty();
			var option = $("<option>").text('').val('');
			vmtypeselect.append(option);
			for (var i = 0; i < hypervisors.length; i++) {
				option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
				vmtypeselect.append(option);
			}
			initOldData();
		}, false);
	}
		
	var initOldData = function(){
		var data = {};
		data.platform_uuid = $('#vcenterUUID').val();
		pAjaxRequest(data, "/api/v1/vm/platforms/detail", "GET", function (d) {
			var data = d.data.info;
			$('#vmtype').val(data.hypervisor_type);
			$('input[name=ipaddr]').val(data.ip);
			$("input[name=username]").val(data.username);
			$("input[name=password]").val(data.password);
			$('input[name=rname]').val(data.rname);
			if(data.detail == ""){
				return;
			}
			detail = data.detail;
			if (CONF.VM_TYPE.AWS == data.hypervisor_type) {
				//AWS
				$('#ipDiv').hide();
				$('#highConfigDiv').hide();
				$('.publicCloudDiv').show();
				$('#bsLocation').val(detail.backup_server_location ?? 'local');
				$('#awsDiv').show();
				$('#accountType').val(detail.account_type);
				$('input[name=username]').prop('placeholder', 'access key id');
				$('input[name=password]').prop('placeholder', 'Secret access key');
			} else if (CONF.VM_TYPE.HUAWEICLOUD == data.hypervisor_type) {
				//Huawei Cloud
				$('#ipDiv').hide();
				$('#highConfigDiv').hide();
				$('.publicCloudDiv').show();
				$('#bsLocation').val(detail.backup_server_location ?? 'local');
				$('#awsDiv').hide();
				$('#accountType').val(detail.account_type);
				$('input[name=username]').prop('placeholder', 'access key id');
				$('input[name=password]').prop('placeholder', 'Secret access key');
			} else {
				$('.ip-port').show();
				$('#openstackPort').val(detail.keystone_public_port);
				if(!detail.url_interface){
					$('#portType').val("public");	//适配老版本升级后改动给个默认的端口类型
				}else{
					$('#portType').val(detail.url_interface);
				}
				if (CONF.VM_TYPE.HUAWEICLOUDSTACK == data.hypervisor_type) {
					$('.ip-port').hide();
					$('#domainDiv').hide();
					$('#huaweistackPortDiv').show();
					$('#highConfigDiv').hide();
					$('.highInfo').hide();
					$('.zstackLoginDiv').hide();
					$('.applianceSwitchDiv').show();
					$('.applianceDiv').show();
					if (detail.domain) {
						$('#huaweistackDomain').val(detail.domain);
						$('#huaweistackDomainDiv').show();
					} else {
						$('#huaweistackDomain').val('');
						$('#huaweistackDomainDiv').hide();
					}
				} else if (CONF.VM_TYPE.ZSTACK == data.hypervisor_type || CONF.VM_TYPE.NEXAVMNCSSV == data.hypervisor_type || CONF.VM_TYPE.XSKY == data.hypervisor_type) {
					// Zstack/xsky xeccp
					$('.ip-port').hide();
					$('#domainDiv').hide();
					$('#huaweistackPortDiv').hide();
					$('#highConfigDiv').hide();
					$('.highInfo').hide();
					$('.zstackLoginDiv').show();
					$('.applianceSwitchDiv').hide();
					if (undefined !== detail.account_type) {
						// 适配tenant/tenant_platform/tenant_project
						if (0 === detail.account_type.indexOf('tenant')) {
							$('#zstackLoginType').val('tenant').trigger('change');
						} else {
							$('#zstackLoginType').val('account').trigger('change');
						}
						platformUuid = data.platform_uuid;
						oldRole = detail.account_type;
						oldUsername = data.username;
						if ('tenant_platform' === oldRole) {
							$('#zstackRole').empty().append(`<option value="tenant_platform">${LANG.UI_VCENTER_PLATFORM_USER}</option>`)
						}
						if ('tenant_project' === oldRole) {
							$('#zstackRole').empty().append(`<option value="tenant_project">${LANG.UI_VCENTER_PROJECT_USER}</option>`)
						}
					} else {
						$('#zstackLoginType').val('account');
					}
					if ('account' == $('#zstackLoginType').val()) {
						$('.username-tip').text(LANG.UI_VCENTER_USERNAME_TIPS);
						$('.password-tip').text(LANG.UI_VCENTER_PASSWORD_TIPS);
					} else {
						$('.username-tip').text(LANG.UI_VCENTER_TENANT_USERNAME_TIPS);
						$('.password-tip').text(LANG.UI_VCENTER_TENANT_PASSWORD_TIPS);
					}
					$('#zstackLoginType').prop('disabled', true);
					$('.applianceDiv').hide();
				} else {
					$('#highConfigDiv').show();
					$('#showHighInfo').bootstrapSwitch('state', false);
					$('.zstackLoginDiv').hide();
					$('.applianceSwitchDiv').show();
					$('.applianceDiv').show();
					if(detail.domain){
						$('#domain').val(detail.domain);
						$('#domainDiv').show();
					}else{
						$('#domain').val('Default');
						$('#domainDiv').hide();
					}
				}
				
				//修改Oepsntack虚拟化禁止修改租户关键信息
				$('#portType').prop('disabled', true);
				$('#openstackPort').prop('disabled', true);
				$('#keystoneVersion').prop('disabled', true);
				$('#domain').prop('disabled', true);
				
				if(detail.agent_uuid && detail.agent_uuid != ""){
					$('#appliancecheck').bootstrapSwitch('state', true);
					$('.applianceSelectDiv').show();
					$('#applianceSelect').val(detail.agent_uuid);
				}else{
					$('.applianceSelectDiv').hide();
				}

				$('input[name=username]').prop('placeholder', '');
				$('input[name=password]').prop('placeholder', '');
	    	}
		});
	}
	
	var initListener = function(){
		//openstack虚拟化选择端口类型切换
		$('#portType').on('change', portHandler);

		//切换appliance
		$('#appliancecheck').on('switchChange.bootstrapSwitch', function(){
			if(this.checked){
				$('.applianceSelectDiv').show();
			}else{
				$('.applianceSelectDiv').hide();
			}
		});

		//高级配置显示开关
		$('#showHighInfo').on('switchChange.bootstrapSwitch', function(){
			if(this.checked){
				$('.highInfo').show();
			}else{
				$('.highInfo').hide();
			}
		});

		// Zstack切换登录类型
		$('#zstackLoginType').on('change', zstackLoginTypeChange);

		// zstack获取角色类型
		$('#getZstackRole').on('click', zstackGetUserRole);

		// zstack变更用户名后放开角色的选择
		$(`input[name=username]`).on('input', function () {
			let hypervisor = $('#vmtype').val();
			if ((CONF.VM_TYPE.ZSTACK == hypervisor || CONF.VM_TYPE.NEXAVMNCSSV == hypervisor || CONF.VM_TYPE.XSKY == hypervisor) && oldUsername != $(this).val()) {
				$('#zstackRole').prop('disabled', false);
				$('#getZstackRole').show();
			} else {
				$('#zstackRole').val(oldRole).prop('disabled', true);
				$('#getZstackRole').hide();
			}
		});
	}
	
	//openstack虚拟化选择端口类型切换
	var portHandler = function(){
		var port = "5000";
		switch(this.value){
			case "public": 
				port = "5000";
				break;
			case "admin": 
				port = "35357";	
				break;
			case "internal": 
				port = "5000";
				break;
		}
		$('#openstackPort').val(port);
	}
	
	//初始化appliance下拉框
	var initApplianceSelect = function(){
		pAjaxRequest({}, "/api/v1/nodes/appliance", "GET", function (d) {
			let data = d.data.appliance;
			if (!data.length) return;
			var applianceSelect = $('#applianceSelect');
			applianceSelect.empty();
			for (var i = 0; i < data.length; i++) {
				var option = $("<option>").text(data[i].text).val(data[i].value);
				applianceSelect.append(option);
			}
		}, false);
	}

	var zstackLoginTypeChange = function () {
		let hypervisor = $('#vmtype').val();
		if ('account' == this.value) {
			$('.username-tip').text(LANG.UI_VCENTER_USERNAME_TIPS);
			$('.password-tip').text(LANG.UI_VCENTER_PASSWORD_TIPS);
			$('.zstackRoleDiv').hide();
			$('#addsubmit').prop('disabled', false);
		} else {
			$('.username-tip').text(LANG.UI_VCENTER_TENANT_USERNAME_TIPS);
			$('.password-tip').text(LANG.UI_VCENTER_TENANT_PASSWORD_TIPS);
			if (CONF.VM_TYPE.ZSTACK == hypervisor || CONF.VM_TYPE.NEXAVMNCSSV == hypervisor || CONF.VM_TYPE.XSKY == hypervisor) {
				// 以租户添加必须选择角色
				$('.zstackRoleDiv').show();
			} else {
				$('.zstackRoleDiv').hide();
				$('#addsubmit').prop('disabled', false);
			}
		}
	}

	const zstackGetUserRole = function () {
		const ROLE_VALUE = {
			3: 'tenant_platform',
			4: 'tenant_project',
		}
		const ROLE_DES = {
			3: LANG.UI_VCENTER_PLATFORM_USER,
			4: LANG.UI_VCENTER_PROJECT_USER,
		}
		let p = {
			hypervisor_type: $('#vmtype').val(),
			ip: $(`input[name=ipaddr]`).val(),
			username: $(`input[name=username]`).val(),
			password: btoa($(`input[name=password]`).val()),
			platform_uuid: platformUuid
		}
		Metronic.blockUI({target: '#modifycontent',animate: true});
		pAjaxRequest(p, "/api/v1/vm/platforms/user/roles", "GET", function (d) {
			Metronic.unblockUI('#modifycontent');
			$('#zstackRole').empty();
			if (!d.success) {
				$('#addsubmit').prop('disabled', true);
				$('#zstackRole').append(`<option value=""> -- </option>`);
				UIToastr.showWarning(d.title, d.message);
				return false;
			}
			let data = d.data;
			if (!data.length) {
				$('#addsubmit').prop('disabled', true);
				$('#zstackRole').append(`<option value=""> -- </option>`);
				UIToastr.showWarning(LANG.UI_VCENTER_GET_USER_ROLE, LANG.UI_VCENTER_GET_USER_ROLE_FAILED_TIPS);
				return false;
			}
			for (let i = 0; i < data.length; i++) {
				let role = data[i].user_role;
				let selected = ROLE_VALUE[role] === oldRole; // 匹配到则选中
				let option = $(`<option ${selected}>`).text(ROLE_DES[role]).val(ROLE_VALUE[role]);
				$('#zstackRole').append(option);
			}
			$('#zstackRole').prop('disabled', 1 === data.length);
			$('#addsubmit').prop('disabled', false);
		}, true);
	}
		
    return {
        //main function to initiate the module
        init: function () {
			// initForm();
        	initListener();
        	initVMType();
            handleValidation();
            initApplianceSelect();
        }

    };
}();

jQuery(document).ready(function() {   
	ModifyVcenter.init();
});