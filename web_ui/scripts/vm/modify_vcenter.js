var ModifyVcenter = function(){
	var authFun = [];
	var applianceuuid = "";
	// validation using icons
	var oldEPassword; // ovirt engine原密码
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
                        required: true,
                        ipv4Ordomain: true,
                    },
                    username: {
						valueRequire: true,
                    },
                    password: {
						valueRequire: true,
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
            
            //测试连接
            $('#testConnect').on('click', function(){
    			var username = $('#ename').val();
    			var password = $('#epassword').val();
				if (password != oldEPassword) {
					// 已修改过则输入框为明文，需base64encode
					password = btoa(password);
				}
    			var ip = $('input[name=ipaddr]').val();
    			var hypervisor = $('#vmtype').val();
    			if (form2.validate().form() && username != "" && password != "") {
        			var p = {username: username, password: password, ip: ip, hypervisor_type: hypervisor};
        			Metronic.blockUI({target: '#modifycontent',animate: true});
					pAjaxRequest(p, "/api/v1/vm/platforms/engine", "POST", function (d) {
						Metronic.unblockUI('#modifycontent');
						if (operateResponseList(d)) {
							$('#addsubmit').prop('disabled', false);
						}
					}, false);
                }
    		});
            

    		//测试云宏高安全版本连接
    		$('#testhighsafeIP').on('click', function(){
    			var hypervisor = $('#vmtype').val();
    			var ip = $('#highsafeIP').val();
    			var username = "";
    			var password = "";
    				
    			if (form2.validate().form()) {
        			var p = JSON.stringify({username: username, password: password, ip: ip, hypervisor: hypervisor});
        			Metronic.blockUI({target: '#modifycontent',animate: true});
        			$.post(CONF.AJAXPATH, {m:CONF.M.VCENTER,f:'testHighsafeIp',p: p}, function(d){
        				Metronic.unblockUI('#modifycontent');
        				if(OPREL(d)){
        	    			$('#addsubmit').prop('disabled', false);
        	        	}
        	    	});
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
		if (CONF.VM_TYPE.ZSTACK == $('#vmtype').val() || CONF.VM_TYPE.ZSTACKZSPHERE == $('#vmtype').val() || CONF.VM_TYPE.NEXAVM == $('#vmtype').val()) {
			generateZstackRname();
		}
    });

	$('input[name=username]').on('change',function(){
		if (CONF.VM_TYPE.ZSTACK == $('#vmtype').val() || CONF.VM_TYPE.ZSTACKZSPHERE == $('#vmtype').val() || CONF.VM_TYPE.NEXAVM == $('#vmtype').val()) {
			generateZstackRname();
		}
	});

	// 生成zstack的别名为：域名/用户名
	var generateZstackRname = function () {
		let username = $('input[name=username]').val() ? '/' + $('input[name=username]').val() : '';
		$('input[name=rname]').val($('input[name=ipaddr]').val() + username);
	}
    
    $('#cancelBut').on('click', function(){
    	href2VcenterManager();
    });
    
    
    var href2VcenterManager = function(){
    	LOCATION('./content/vm/vcenter_manager.php', 'infrastructure');
    }
    
    var submit = function(){
		let vmtype = parseInt($('#vmtype').val());
		if(CONF.VMTYPE_GROUP.REDHAT.includes(vmtype) && $('#enginecheck').is(":checked") && $("#spinnerNumInput").val() == ""){  //如果是ovirt 自动平台打开后 保留个数必须输入
			return UIToastr.showWarning(LANG.UI_VCENTER_OVIRT_TITLE, LANG.UI_VCENTER_OVIRT_HITE);
		}
		// ovirt自动平台备份，备份时间不为空，存储不为空，保留个数不为空
		if(CONF.VMTYPE_GROUP.REDHAT.includes(vmtype) && $('#enginecheck').is(":checked")){
			if($('#selectstorage').val() == '' || $('#selectstorage').val() == null){
				UIToastr.showWarning(LANG.UI_VCENTER_EMPTY_STORAGE_TIPS);
				return false;

			}
			if($('.backupTime').val() == ''){
				UIToastr.showWarning(LANG.UI_VCENTER_EMPTY_TIME_STRATEGY_TIPS);
				return false;
			}
			if($('#spinnerNumInput').val() == "00" || $('#spinnerNumInput').val() == "0000" || $('#spinnerNumInput').val() == "000"){
				UIToastr.showWarning(LANG.UI_VCENTER_RESERVED_NUM_ZERO_TIPS);
				return false;
			}
		}
		// sangfor vddk ip地址必须带4430端口
		if (CONF.VM_TYPE.SANGFORVVDK == $('#vmtype').val() && !$('input[name=ipaddr]').val().endsWith(':4430')) {
			UIToastr.showWarning(LANG.UI_VCENTER_SANGFORVDDK_IP_PORT_TIPS);
			return false;
		}
    	var data = {};
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
    	data.rname = $('input[name=rname]').val().trim();
    	data.extra = {};
		if(!data.rname.trim()){
			UIToastr.showWarning(LANG.UI_VCENTER_EMPTY_NICKNAME_TIPS);
			return false;
		}
    	switch(data.hypervisor_type){
			case CONF.VM_TYPE.VMWARE:
				data.extra = {
					"login_type": "token",
					"language": "zh-cn",
					"agent_uuid": ""
				}
				break;
			case CONF.VM_TYPE.HYPERV:
				var hypervType = $('#hypervType').val();
	    		data.extra = {
	    			"type": hypervType	
	    		};
				break;
			case CONF.VM_TYPE.H3C:
			case CONF.VM_TYPE.H3CCASCVD:
				var httpType = $('#httpType').val();
				data.detail = {
					"http_type": httpType
				};
				break;
			case CONF.VM_TYPE.RHV:
			case CONF.VM_TYPE.OVIRT:
			case CONF.VM_TYPE.ZVIRT:
			case CONF.VM_TYPE.OLVM:
			case CONF.VM_TYPE.HOSTVM:
			case CONF.VM_TYPE.REDVIRT:
			case CONF.VM_TYPE.ROSAVIRT:
				if($('#enginecheck').is(":checked")){
	    			var user = $('#ename').val();
	        		var password =  $('#epassword').val();
					if (password != oldEPassword) {
						// 已修改过则输入框为明文，需base64encode
						password = btoa(password);
					}
	        		var backupTime = $('.backupTime').val();
	        		var reservedNum = $('#spinnerNumInput').val();
	        		var nodeuuid = $('#selectnode').val();
	        		var storageuuid = $('#selectstorage').val();
	        		data.extra ={
	    	    		"user": user,
	    	    		"password": password,
	    	    		'backup_time': backupTime,
	    	    		'reserved_num': reservedNum,
	    	    		'node_uuid': nodeuuid,
	    	    		'storage_uuid': storageuuid
	        	    };
	    		}
				break;
			case CONF.VM_TYPE.WINSERVER:
			case CONF.VM_TYPE.WINDIY:
				if($('#highsafecheck').is(":checked")){
	    			var vcenterIP = $('#highsafeIP').val();
	        		data.extra ={
	    	    		"wincenter_url": vcenterIP,
	    	    		"username": "",
	    	    		'password': ""
	        	    };
	    		}
				break;
			case CONF.VM_TYPE.INCLOUDKVM:
			case CONF.VM_TYPE.INSPURVVDK:
			case CONF.VM_TYPE.KSPHERE:
				var accessId = $('#accessId').val();
				var accessSecret = $('#accessSecret').val();
				var logintype = $('#icsLoginType').val();
				if ('token' == logintype) {
					accessId = '';
					accessSecret = '';
				} else {
					data.username = '';
					data.password = '';
				}
				if (!(data.username && data.password) && !(accessId && accessSecret)) {
					//两组必选其一
					UIToastr.showWarning(LANG.UI_VCENTER_PARAMS_EMPTY_TIPS, LANG.UI_VCENTER_ICS_ADD_TIPS);
					return false;
				}
				getAppliance();
	    		data.extra ={
		    		"access_key_id": accessId,
		    		"access_key_secret": accessSecret,
					"agent_uuid": applianceuuid
	    	    };
				break;
			case CONF.VM_TYPE.SMARTX:
				var logintype = $('#loginType').val();
				getAppliance();
	    		data.extra ={
		    		"login_type": logintype,
					"agent_uuid": applianceuuid
	    	    };
				break;
			case CONF.VM_TYPE.ARCFRA:
				getAppliance();
				data.extra ={
					"login_type": '',
					"agent_uuid": applianceuuid
				};
				break;
			case CONF.VM_TYPE.SANGFORVVDK:
				var logintype = $('#sangforvvdkLoginType').val();
				var lang = $('#lang').val();
				getAppliance();
				data.extra = {
					"login_type": logintype,
					"language": lang,
					"agent_uuid": applianceuuid
				}
				break;
			case CONF.VM_TYPE.FUSIONKVM:
			case CONF.VM_TYPE.XFUSIONKVM:
				data.extra = {
					"auth_user_type": $('#huaweikvmUserType').val(),
					"auth_type": $('#huaweikvmAuthType').val(),
				}
				break;
			case CONF.VM_TYPE.VOLC:
				getAppliance();
				data.detail ={
					"agent_uuid": applianceuuid
				};
				break;
			case CONF.VM_TYPE.ZSTACK:
			case CONF.VM_TYPE.XSKY:
				var logintype = $('#zstackLoginType').val();
				data.extra = {
					account_type: logintype
				}
				break;
			case CONF.VM_TYPE.ZSTACKZSPHERE:
			case CONF.VM_TYPE.NEXAVM:
				data.extra = {
					account_type: 'account',
				}
				break;
		}
		Metronic.blockUI({target: '#modifycontent',animate: true});
		pAjaxRequest(data, "/api/v1/vm/platforms", "PUT", function (data) {
			Metronic.unblockUI('#modifycontent');
			if (data.success) {
				UIToastr.showSuccess(LANG.UI_VCENTER_MODIFY, LANG.UI_VCENTER_MODIFY_SUCCESS);
				href2VcenterManager();
			} else {
				UIToastr.showWarning(LANG.UI_VCENTER_MODIFY, data.message);
				return false;
			}
		}, true);
    };

	var getAppliance = function () {
		applianceuuid = "";
		if($('#appliancecheck').get(0).checked){
			applianceuuid = $('#applianceSelect').val();
			if(applianceuuid == ""){
				UIToastr.showWarning(LANG.UI_APPLIANCE_SELECT, LANG.UI_APPLIANCE_SELECT_NO_TIPS);
				return false;
			}
		}
	}
    
    var modifyResult = function(data){
    	Metronic.unblockUI('#modifycontent');
    	if(OPREL(data)){
    		href2VcenterManager();
    	}
    	return;
    };
    
	$.validator.addMethod("valueRequire", function (value, ele) {
		var hypervisor = parseInt($('#vmtype').val());
		if (CONF.VM_TYPE.INCLOUDKVM == hypervisor || CONF.VM_TYPE.INSPURVVDK == hypervisor || CONF.VM_TYPE.KSPHERE == hypervisor) {
			return true;
		}
		return '' != $.trim(value);
	}, LANG.UI_PUBLIC_EMPTY_TIPS);
    
	$.validator.addMethod("ipv4Ordomain", function(value, element) {
		var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
		var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
		//http|https
		var domainHTTP = this.optional( element ) || /^(http|https):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
		var ipv4HTTP = this.optional(element) || /^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
		return domain || ipv4 || domainHTTP || ipv4HTTP;
    }, LANG.UI_TOOLS_IP_OR_DOMAIN);


	//初始化虚拟化类型
	var initVMType = function(){
		pAjaxRequest({}, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
			let hypervisors = d.data.hypervisors;
			var vmtypeselect = $('#vmtype');
			vmtypeselect.empty();
			var option = $("<option>").text('').val('');
			vmtypeselect.append(option);
			for (var i = 0; i < hypervisors.length; i++) {
				option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
				vmtypeselect.append(option);
			}
			vmtypeselect.val('');
			initOldData();
		}, false);
	}
		
	var initOldData = function(){
		//初始化时间选择
		$('.backupTime').timepicker({
			autoclose: true,
			minuteStep: 5,
			showSeconds: true,
			showMeridian: false
		});
		//初始化保留个数
		$('#spinnerNum').spinner({value:30, step: 5, min: 1, max: 1000});

		var data = {};
		data.platform_uuid = $('#vcenterUUID').val();
		pAjaxRequest(data, "/api/v1/vm/platforms/detail", "GET", function (d) {
			var data = d.data.info;
			$('#vmtype').val(data.hypervisor_type);
			$('input[name=ipaddr]').val(data.ip);
			$("input[name=username]").val(data.username);
			$("input[name=password]").val(data.password);
			$('input[name=rname]').val(data.rname);

			// h3c UIS/CAS
			if (CONF.VM_TYPE.H3C == data.hypervisor_type || CONF.VM_TYPE.H3CCASCVD == data.hypervisor_type) {
				$('#httpTypeDiv').show();
				$('#ipaddrDiv').removeClass('col-md-12').addClass('col-md-9');
				$('#httpType').val(data.detail.http_type ?? 'http://');
			} else {
				$('#httpTypeDiv').hide();
				$('#ipaddrDiv').removeClass('col-md-9').addClass('col-md-12');
			}

			// huawei kvm
			if (CONF.VM_TYPE.FUSIONKVM == data.hypervisor_type || CONF.VM_TYPE.XFUSIONKVM == data.hypervisor_type) {
				$('.huaweikvmLoginDiv').show();
				$('#huaweikvmUserType').val(!data.detail ? 2 : data.detail.auth_user_type);
				$('#huaweikvmAuthType').val(!data.detail ? 0 : data.detail.auth_type);
			} else {
				$('.huaweikvmLoginDiv').hide();
			}

			if(!data.detail){
				if(CONF.VMTYPE_GROUP.REDHAT.includes(data.hypervisor_type)){
					if (CONF.FUNCTIONS.includes('engine')) {
						$('.engineCheckDiv').show();
						$('#enginecheck').bootstrapSwitch('state', false);
					}
					$('.engineDiv').hide();
					$('#addsubmit').prop('disabled', false);
				}else if("18" == data.hypervisor_type || "25" == data.hypervisor_type){
					$('.highsafeCheckDiv').show();
					$('#highsafecheck').bootstrapSwitch('state', false);
					$('.highsafeDiv').hide();
					$('#addsubmit').prop('disabled', false);
				}
				return;
			}
			var detail = data.detail;
			if (detail) {
				$('#hypervType').val(detail.type);
				$('#selectnode').val(detail.node_uuid);
				initStorageSelect();
				$('#selectstorage').val(detail.storage_uuid);
			}
			if (CONF.VMTYPE_GROUP.REDHAT.includes(data.hypervisor_type) && CONF.FUNCTIONS.includes('engine')) {
				$('.engineCheckDiv').show();
				if (detail) {
					$('#enginecheck').bootstrapSwitch('state', true);
					$('.engineDiv').show();
					oldEPassword = detail.password;
					$('#ename').val(detail.user);
					$('#epassword').val(detail.password);
					$('.backupTime').val(detail.backup_time);
					$('#selectnode').val(detail.node_uuid);
					$('#selectstorage').val(detail.storage_uuid);
					$('#spinnerNumInput').val(detail.reserved_num);
				}
				$('#addsubmit').prop('disabled', true);
			}
			if("18" == data.hypervisor_type || "25" == data.hypervisor_type){
				$('.highsafeCheckDiv').show();
				$('.highsafeDiv').show();
				$('#highsafeIP').val(detail.wincenter_url);
				$('#highsafecheck').bootstrapSwitch('state', true);
        		$('#addsubmit').prop('disabled', true);
			}
			
			//smartX
			if (data.hypervisor_type == "33") {
				$('.loginDiv').show();
				$('.smartxtips').show();
				$('.arcfratips').hide();
				$('.iptips').hide();
				$('#loginType').val(detail.login_type);
				$('.applianceDiv').show();
			} else if (data.hypervisor_type == CONF.VM_TYPE.ARCFRA) {
				$('.loginDiv').hide();
				$('.smartxtips').hide();
				$('.arcfratips').show();
				$('.iptips').hide();
				$('.applianceDiv').show();
			} else {
				$('.loginDiv').hide();
				$('.smartxtips').hide();
				$('.arcfratips').hide();
				$('.iptips').show();
			}
	    	//ICS
	    	if(data.hypervisor_type == "24" || CONF.VM_TYPE.INSPURVVDK == data.hypervisor_type || CONF.VM_TYPE.KSPHERE == data.hypervisor_type){
				$('.icsLoginDiv').show();
	    		$('#accessId').val(detail.access_key_id);
				$('#accessSecret').val(detail.access_key_secret);
				if (data.username == detail.access_key_id) {
					$("input[name=username]").closest('.username').hide();
					$("input[name=password]").closest('.password').hide();
					$("input[name=username]").val('');
					$("input[name=password]").val('');
					$('.icsDiv').show();
					$('#icsLoginType').val('ak/sk');
				} else {
					$('#accessId').val('');
					$('#accessSecret').val('');
					$('.icsDiv').hide();
					$('#icsLoginType').val('token');
				}
				if (CONF.VM_TYPE.INSPURVVDK == data.hypervisor_type || CONF.VM_TYPE.KSPHERE == data.hypervisor_type) {
					$('.applianceDiv').show();
				}
	    	}else{
				$('.icsLoginDiv').hide();
	    		$('.icsDiv').hide();
	    	}

			// sangfor vddk
			if (CONF.VM_TYPE.SANGFORVVDK == data.hypervisor_type) {
				$('#sangforvvdkLoginType').val(detail.login_type);
				initScpLoginInput(detail.login_type);
				$('.sangforvvdkLoginDiv').show();
				$('.applianceDiv').show();
			} else {
				$('.sangforvvdkLoginDiv').hide();
			}

			// Volcano Cloud
			if (CONF.VM_TYPE.VOLC == data.hypervisor_type) {
				$('.applianceDiv').show();
			}

			// zsphere禁止修改用户名
			if (CONF.VM_TYPE.ZSTACKZSPHERE == data.hypervisor_type || CONF.VM_TYPE.NEXAVM == data.hypervisor_type) {
				$('input[name=username]').prop('disabled', true);
			}

			// 初始化传输代理
			$('.applianceSelectDiv').hide();
			if (detail && detail.agent_uuid) {
				$('#appliancecheck').bootstrapSwitch('state', true);
				$('#applianceSelect').val(detail.agent_uuid);
				$('.applianceSelectDiv').show();
			}
		}, false);

		//初始化节点
		initSelectNode();
	}
	
	var initListener = function(){
		 $('.backupTime').parent('.input-group').on('click', '.input-group-btn', function(e){
             e.preventDefault();
             $(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
         });
		$('#enginecheck').on('switchChange.bootstrapSwitch', engineChange);
		
		$('#highsafecheck').on('switchChange.bootstrapSwitch', highsafeChange);
		
		//节点改变
		$('#selectnode').on('change', initStorageSelect);
		
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

		// Sangfor Cloud Platform切换登录类型
		$('#sangforvvdkLoginType').on('change', scpLoginTypeChange);

		// ics/ics vvdk切换登录类型
		$('#icsLoginType').on('change', icsLoginTypeChange);
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
	
	
	var engineChange = function(){
		if(this.checked){
			$('.engineDiv').show();
			$('#addsubmit').prop('disabled', true); 
		}else{
			$('.engineDiv').hide();
			$('#addsubmit').prop('disabled', false); 
		}
	}
	
	var highsafeChange = function(){
		if(this.checked){
			$('.highsafeDiv').show();
			$('#addsubmit').prop('disabled', true); 
		}else{
			$('.highsafeDiv').hide();
			$('#addsubmit').prop('disabled', false); 
		}
	}

	var scpLoginTypeChange = function () {
		initScpLoginInput(this.value);
	}

	var initScpLoginInput = function (value) {
		if ('token' == value) {
			$('.username-label').show();
			$('.username-label2').hide();
			$('.username-tip').css('display', 'block');
			$('.username-tip2').hide();
			$('.password-label').show();
			$('.password-label2').hide();
			$('.password-tip').css('display', 'block');
			$('.password-tip2').hide();
		} else {
			$('.username-label').hide();
			$('.username-label2').show();
			$('.username-tip').hide();
			$('.username-tip2').css('display', 'block');
			$('.password-label').hide();
			$('.password-label2').show();
			$('.password-tip').hide();
			$('.password-tip2').css('display', 'block');
		}
	}

	var icsLoginTypeChange = function () {
		if ('token' == this.value) {
			$('.form-body .username').show();
			$('.form-body .password').show();
			$('.icsDiv').hide();
		} else {
			$('.form-body .username').hide();
			$('.form-body .password').hide();
			$('.icsDiv').show();
		}
	}
		
	var initSelectNode = function(){
		pAjaxRequest({}, "/api/v1/nodes/select", "GET", function (d) {
			var data = d.data;
			var softselect = $('#selectnode');
			softselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				softselect.append(option);
			}
			initStorageSelect();  //初始化存储下拉框
		}, false);
	}
	
	//初始化存储下拉框
	var initStorageSelect = function(){
		var data = {};
		data.node_uuid = $('#selectnode').val();
		pAjaxRequest(data, "/api/v1/storages/backup", "GET", function (d) {
			let data = d.data;
			var softselect = $('#selectstorage');
			softselect.empty();
			for(var i=0; i<data.length; i++){
				if (CONF.VMTYPE_GROUP.REDHAT.includes(parseInt($('#vmtype').val())) && CONF.BD_STORAGE_TYPE.CLOUD == data[i].storage_type) {
					//ovirt自动平台备份暂时不支持云存储，屏蔽
					continue;
				}
				var option = $("<option>").text(data[i].text).val(data[i].storage_uuid);
				softselect.append(option);
			}
		}, false);
	}
	var initAuthFun = function(){
		pAjaxRequest({}, "/api/v1/users/auth_func", "GET", function (d) {
			authFun = d.data;
		}, false);
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

	//解码html标签
	var htmlDecode= function(input){
		var e = document.createElement('div');
		e.innerHTML = input;
		return e.childNodes[0].nodeValue;
	}
		
    return {
        //main function to initiate the module
        init: function () {
			initAuthFun(); //初始化授权功能
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