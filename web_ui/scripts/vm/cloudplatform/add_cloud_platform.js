var AddVcenter = function(){
	var lastIp = '';//最终传参的ip
	var _cloudType = $('#cloudType').val();
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
//                    error2.show();
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
//                    error2.hide();
                    
                }
                
            });
            
            $("#addsubmit").click(function(){
            	if (form2.validate().form()) {
//            		success2.show();
//                    error2.hide();
                    submit();
                }
            });
    };

    $('#httpType').on('change', function () {
		formatIp();
	});
    
    $('input[name=ipaddr]').on('change',function(){
    	formatIp();
    	$('input[name=rname]').val(this.value);
		if (CONF.VM_TYPE.ZSTACK == $('#vmtype').val() || CONF.VM_TYPE.NEXAVMNCSSV == $('#vmtype').val()) {
			generateZstackRname();
		}
    });

	$('input[name=username]').on('change',function(){
		if (CONF.VM_TYPE.ZSTACK == $('#vmtype').val() || CONF.VM_TYPE.NEXAVMNCSSV == $('#vmtype').val()) {
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
    
    
    //虚拟化类型改变增加用户名提示，改变用户名默认placeholder
    var vmTypeChangeTips = function(type){
    	var usernamePlaceholder = $('input[name=username]');
    	$('input[name=ipaddr]').prop('placeholder', "192.168.1.110");
    	switch(type){
			case 26:
			case 30:
				//ZStack/XECCP
				$('input[name=ipaddr]').prop('placeholder', "192.168.1.110:8080");
				usernamePlaceholder.prop('placeholder', "admin");
				break
    		default:
    			usernamePlaceholder.prop('placeholder', "");
    			break;
    	}
    }
    
    $('#vmtype').on('change', function(){
    	// vmTypeChangeTips(parseInt(this.value));
		var selectText = $(this).find('option:selected').text();
		if ('' == this.value) {
			$('#agentdownload').hide();
			$('#agentInit').hide();
		} else {
			if ('private' == _cloudType) {
				var html = '<i class="fa fa-warning "></i> ';
				html += '<strong>' + selectText + '</strong>';
				html += LANG.UI_VCENTER_ADD_HYPERVISOR_TIPS;
				$('#agentdownloadtips').html(html);
				$('#agentdownload').show();
				$('#agentInit').show();
				if (CONF.VM_TYPE.HUAWEICLOUDSTACK == this.value) {
					$('.ip-port').hide();
					$('#domainDiv').hide();
					$('#huaweistackPortDiv').show();
					$('#huaweistackDomainDiv').show();
					$('#highConfigDiv').hide();
					$('.highInfo').hide();
					$('.zstackLoginDiv').hide();
					$('.applianceSwitchDiv').show();
					$('#httpTypeDiv').show();
					$('#ipaddrDiv').removeClass('col-md-12').addClass('col-md-9');
				} else if (CONF.VM_TYPE.ZSTACK == this.value || CONF.VM_TYPE.NEXAVMNCSSV == this.value || CONF.VM_TYPE.XSKY == this.value) {
					$('.zstackLoginDiv').show();
					$('#zstackLoginType').val('account').trigger('change');
					$('.ip-port').hide();
					$('#domainDiv').hide();
					$('#highConfigDiv').hide();
					$('.highInfo').hide();
					$('.applianceSwitchDiv').hide();
					$('#httpTypeDiv').hide();
					$('#ipaddrDiv').removeClass('col-md-9').addClass('col-md-12');
				} else {
					$('.ip-port').show();
					$('#domainDiv').show();
					$('#huaweistackPortDiv').hide();
					$('#huaweistackDomainDiv').hide();
					$('#highConfigDiv').show();
					$('#showHighInfo').bootstrapSwitch('state', false);
					$('.zstackLoginDiv').hide();
					$('#zstackLoginType').val('account');
					$('.applianceSwitchDiv').show();
					$('#httpTypeDiv').show();
					$('#ipaddrDiv').removeClass('col-md-12').addClass('col-md-9');
				}
			} else {
				$('input[name=username]').val('');
				$('input[name=password]').val('');

				$('.publicCloudDiv').hide();
				$('#awsDiv').hide();
				$('#huaweiCloudAkTips').hide();
				let nicknamePrefix = '';
				if (CONF.VM_TYPE.AWS == this.value) {
					//AWS
					$('.publicCloudDiv').show();
					$('#awsDiv').show();
					nicknamePrefix = 'AWS';
				} else if (CONF.VM_TYPE.HUAWEICLOUD == this.value) {
					// Huawei Cloud
					$('.publicCloudDiv').show();
					$('#huaweiCloudAkTips').show();
					$('input[name=rname]').val();
					nicknamePrefix = 'HuaweiCloud';
				}

				//填充默认别名
				pAjaxRequest({prefix: nicknamePrefix}, "/api/v1/cloud/platform/nickname", "GET", function (d) {
					if (d.success) {
						$('input[name=rname]').val(d.data.nickname);
					}
				}, false);
			}
		}
    });
    
    //初始化对应虚拟化插件版本
    var initAgentVersion = function(){
    	var name = $('#vmtype').find("option:selected").text();
    	$('#agentVendor').html(name);
    	var hypervisor = $('#vmtype').val();
    	if(hypervisor == 14 || hypervisor == 22){
    		name = "OpenStack";
    	}
		pAjaxRequest({name: name}, "/api/v1/agents/versions", "GET", function (d) {
			let data = d.data.versions;
			let agentSelect = $('#vmAgent');
			agentSelect.empty();
			for (var i = 0; i < data.length; i++) {
				var option = $("<option>").text(data[i].text).val(data[i].value);
				agentSelect.append(option);
			}
		}, false);
    }
    
    var href2VcenterManager = function(){
		if ('private' == $('#cloudType').val()) {
			LOCATION('./content/vm/cloudplatform/cloud_platform_manager.php?cloudType=private', 'infrastructure');
		} else {
			LOCATION('./content/vm/cloudplatform/cloud_platform_manager.php?cloudType=public', 'infrastructure');
		}
    }
    
    var submit = function(){
    	var data = {};
    	var openstackPort = parseInt($('#openstackPort').val());
    	var url_interface = $('#portType').val();
    	var domain = $('#domain').val();
    	//未填写域默认Default
    	if(domain == ""){
    		domain = "Default";
    	}
    	data.hypervisor_type = parseInt($('#vmtype').val());
    	// data.ip = $('input[name=ipaddr]').val();
    	data.ip = lastIp;
    	data.username = $("input[name=username]").val();
    	data.password = btoa($("input[name=password]").val());
    	data.rname = $('input[name=rname]').val();
    	data.detail = {};
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
	    		data.detail ={
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
				data.detail = {
					account_type: logintype
				}
				break;
			case CONF.VM_TYPE.AWS:
			case CONF.VM_TYPE.HUAWEICLOUD:
				var accountType = $('#accountType').val();
				var bsLocation = $('#bsLocation').val();
				data.detail = {
					account_type: accountType,
					backup_server_location: bsLocation
				}
				break;
				
    	}
		Metronic.blockUI({target: '#addcontent',animate: true});
		pAjaxRequest(data, "/api/v1/vm/platforms", "POST", function (data) {
			Metronic.unblockUI('#addcontent');
			if (data.success) {
				UIToastr.showSuccess(LANG.UI_CLOUD_PLATFORM_ADD, LANG.UI_CLOUD_PLATFORM_ADD_SUCCESS_TIPS);
				DataBackupCenter.updateTopAlarmTips();
				href2VcenterManager();
			} else {
				UIToastr.showWarning(LANG.UI_CLOUD_PLATFORM_ADD, data.message);
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

	//初始化虚拟化类型
	var initVMType = function () {
		if ('private' == _cloudType) {
			$('.applianceSwitchDiv').show();
		} else {
			$('.applianceSwitchDiv').hide();
		}
		var data = {};
		data.cloud_flag = true;
		data.cloud_type = $('#cloudType').val();
		pAjaxRequest(data, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
			let hypervisors = d.data.hypervisors;
			var vmtypeselect = $('#vmtype');
			vmtypeselect.empty();
			var option = $("<option>").text(LANG.UI_CLOUD_PLATFORM_SELECT).val('');
			vmtypeselect.append(option);
			for (var i = 0; i < hypervisors.length; i++) {
				option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
				vmtypeselect.append(option);
			}
			vmtypeselect.val('');
		}, false);
	}

	// 初始化表单
	var initForm = function () {
		// 公有云初始全部隐藏
		if ('public' == _cloudType) {
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
	
	var initListener = function(){
		//初始化下载插件模态框
		$('#agentInit').on('click', initAgentModal);
		
		//下载插件包
		$('#downloadAgent').on('click', function(){
			var href = $('#vmAgent').val();
			window.location = href;
			$('#agentModal').modal('hide');
		});
		
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

	var initAgentModal = function(){
		$('#agentModal').modal({'width':'800px', 'height':'200px'});
		initAgentVersion();
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

	//格式化ip，若输入的ip带http/https前缀则以输入为准，否则拼接
	var formatIp = function () {
		var httpType = $('#httpType').val();
		var ip = $('input[name=ipaddr]').val();
		if (ip.match(/^http:\/\//)) {
			lastIp = ip;
			$('#httpType').val('http://');
		} else if (ip.match(/^https:\/\//)) {
			lastIp = ip;
			$('#httpType').val('https://');
		} else {
			if (CONF.VM_TYPE.ZSTACK == $(`#vmtype`).val() || CONF.VM_TYPE.NEXAVMNCSSV == $(`#vmtype`).val() || CONF.VM_TYPE.XSKY == $(`#vmtype`).val()) {
				lastIp = ip;
			} else {
				lastIp = httpType + ip;
			}
		}
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
				$('#addsubmit').prop('disabled', true);
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
		}
		Metronic.blockUI({target: '#addcontent',animate: true});
		pAjaxRequest(p, "/api/v1/vm/platforms/user/roles", "GET", function (d) {
			Metronic.unblockUI('#addcontent');
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
				let option = $("<option>").text(ROLE_DES[role]).val(ROLE_VALUE[role]);
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
	AddVcenter.init();
});