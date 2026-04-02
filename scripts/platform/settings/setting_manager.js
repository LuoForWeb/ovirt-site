//设置IP地址
var Settings_Set_IP = function () {
	var _HOST = location.host;
	var _PRIMARY_NODE = '';	//主节点
	var handleValidation = function() {
        var setipform = $('#setipform');

        setipform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	ipnodelist: {
            		required: true,
            	},
            	networkcard: {
                    required: true,
                },
                ipaddr: {
                    required: false,
                    ipv4: true,
                },
                netmask: {
                	required: false,
                	vNetmask: true,
                },
                gateway: {
                    required: false,
                    vGateway: true,
                },
                dns: {
                    required: false,
                    vDns: true,
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
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
                
            }
            
        });
        
        //IP验证格式
        $.validator.addMethod("ipv4", function(value, element) {
        	return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_IP);
        
        //子网掩码
        $.validator.addMethod("vNetmask", function(value, element) {
        	return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_NETMASK);
        
        //网关
        $.validator.addMethod("vGateway", function(value, element) {
        	return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_GATEWAY);
        
        //dns
        $.validator.addMethod("vDns", function(value, element) {
       
        	for(var i=1;i<value.length;i++){
        		if(value.indexOf(",")!=-1){
            		value = value.split(',');
            		return this.optional(element) ||  /^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$/ || ipV4V6(value[i]);
            	}
        	}
        	return this.optional(element) || /^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$/i.test(value) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_DNS);
        
        $("#ipsubmit").click(function(){
        	if (setipform.validate().form()) {
        		submitCheck();
            }
        });
        
        $("#ipcancel").click(function(){
        	LOCATION('./content/platform/settings/setting_manager.php?tab=0', 'setting_manager');
        });
        
	};

	//提交修改检测
	var submitCheck = function(){
		//如果修改是主节点的IP和访问IP不一样,进行提示
		if(_PRIMARY_NODE == $('select[name=ipnodelist]').val() && $('input[name=ipaddr]').val() != _HOST){
			bootbox.confirm({
	            title: LANG.UI_SETTING_MODIFY_IP,
	            message: LANG.UI_SETTING_MODIFY_IP_TIP1 + '<br>' + 
	            		 LANG.UI_SETTING_MODIFY_IP_TIP2 + '<br>' + 
	            		 LANG.UI_SETTING_MODIFY_IP_TIP3,
	            callback: debounce(function(r) {
	                if(!r) return;
	                submit();
	            }, 300)
	        });
		}else{
			submit();
		}
		
	}
	
	//提交修改
	var submit = function(){
		Metronic.blockUI({target: '#setipform',animate: true});
		var data = {};
		data.nodeuuid = $('select[name=ipnodelist]').val();
		data.NAME = $('select[name=networkcard] option:selected').text();
		data.IPADDR = $('input[name=ipaddr]').val();
		data.NETMASK = $('input[name=netmask]').val();
		data.GATEWAY = $('input[name=gateway]').val();
		data.DNS = $('input[name=dns]').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'setNetworkCardInfo', p:data}, function(d){
			Metronic.unblockUI('#setipform');
	    	if(OPREL(d)){
	    	}
		});
	}
	
	//初始化
	var handleInit = function(){
		//初始化备份节点
		var initBackupNodeList = function(){
			$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getAddStorageNodeSelect',p:{}}, function(d){
				var data = JSON.parse(d);
				var nodeselect = $('select[name=ipnodelist]');
				nodeselect.empty();
				for(var i=0; i<data.length; i++){
					var option = $("<option>").text(data[i].text).val(data[i].uuid);
					nodeselect.append(option);
					//如果是主节点,给主节点变量赋值
					if(1 == data[i].type){
						_PRIMARY_NODE = data[i].uuid;
					}
				}
				
				initNetworkCard();
	    	});
		}
		
		//初始化网卡改变事件,得到所选网卡信息
		var networkCardChange = function(){
			var nodeuuid = $('select[name=ipnodelist]').val();
			var cardName = $('select[name=networkcard] option:selected').text();
			var cardHwaddr = $('select[name=networkcard]').val();
			if(null == cardName) return;
			var data = {nodeuuid:nodeuuid, cardName:cardName, cardHwaddr:cardHwaddr};
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getNetworkCardInfo', p:data}, function(d){
				var data = JSON.parse(d);
				$('input[name=ipaddr]').val(data.IPADDR);
				$('input[name=netmask]').val(data.NETMASK);
				$('input[name=gateway]').val(data.GATEWAY);
				$('input[name=dns]').val(data.DNS);
				_HOST = data.IPADDR;
			});
		}
		
		//初始化网卡
		var initNetworkCard = function(){
			var nodeuuid = $('select[name=ipnodelist]').val();
			var data = {nodeuuid:nodeuuid};
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getNetworkCardList', p:data}, function(d){
				var data = JSON.parse(d);
				var networkcard = $('select[name=networkcard]');
				networkcard.empty();
				for(var i=0; i<data.length; i++){
					var option = $("<option>").text(data[i].name).val(data[i].hwaddr);
					networkcard.append(option);
				}
				networkCardChange();
			});
		}
		
		$('select[name=networkcard]').on('change', networkCardChange);
		$('select[name=ipnodelist]').on('change', initNetworkCard);
		
		initBackupNodeList();
	}
	
    return {
        init: function () {
        	handleInit();
        	handleValidation();
        }
    };

}();

//设置时间
var Settings_Set_Time = function () {
	var _OLDTIME = null;	//之前的时间
	var _AUTHFLAG = 0;		//系统授权状态
	var _AVAILABLE_NTPHOST = '';
	
	var handleValidation = function(){
		var settimeform = $('#settimeform');

		settimeform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	cityinput: {
                    required: true,
                },
                timeinput: {
                    required: true,
                    daterule: true,
                },
                ntphost: {
                	ntpipv4Ordomain: true,
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
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
                
            }
            
        });
        
		$.validator.addMethod("daterule", function(value, element) {
        	return this.optional(element) || /^\d{4}\-\d{1,2}\-\d{1,2}[T ]\d{1,2}\:\d{1,2}\:\d{1,2}[Z]{0,1}$/.test(value);
        }, LANG.UI_SETTING_INPUT_TIME);
		
		$.validator.addMethod("ntpipv4Ordomain", function(value, element) {
			var ntpcheck = $('#ntpcheck').bootstrapSwitch('state');
			if(!ntpcheck) return true;
			var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
			var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
			return domain || ipv4;
	    }, LANG.UI_TOOLS_IP_OR_DOMAIN);
		
        $("#timesubmit").click(function(){
        	if (settimeform.validate().form()) {
        		submitCheck();
            }
        });
        
        $("#timecancel").click(function(){
        	LOCATION('./content/platform/settings/setting_manager.php?tab=1', 'setting_manager');
        });
	}

	
	//提交修改检测
	var submitCheck = function(){
		//如果是NTP时间同步配置,直接提交
		if($('#ntpcheck').bootstrapSwitch('state')){
			submit();
			return;
		}
		//如果修改后的时间在当前时间之前,需要进行提示
		var oldTimestamp = datetime_to_unix(_OLDTIME);
		var newTimestamp = datetime_to_unix($('input[name=timeinput]').val());
		if(_AUTHFLAG == 1 && oldTimestamp >= newTimestamp){
			bootbox.confirm({
	            title: LANG.UI_SETTING_MODIFY_TIME,
	            message: LANG.UI_SETTING_MODIFY_TIME_TIP1 + '<br>' + 
	            		 LANG.UI_SETTING_MODIFY_TIME_TIP2,
	            callback: debounce(function(r) {
	                if(!r) return;
	                submit();
	            }, 300)
	        });
		}else{
			submit();
		}
		
	}
	
	//时间格式(2012-12-12 12:12:12)转时间戳  
	var datetime_to_unix = function(datetime){
	    var tmp_datetime = datetime.replace(/:/g,'-');
	    tmp_datetime = tmp_datetime.replace(/ /g,'-');
	    var arr = tmp_datetime.split("-");
	    var now = new Date(Date.UTC(arr[0],arr[1]-1,arr[2],arr[3]-8,arr[4],arr[5]));
	    return parseInt(now.getTime()/1000);
	}
	
	//提交修改
	var submit = function(){
		Metronic.blockUI({target: '#settimeform',animate: true});
		var data = {};
		data.timezone = $('select[name=cityinput]').val();
		data.timeinput = $('input[name=timeinput]').val();
		data.ntpcheck = $('#ntpcheck').bootstrapSwitch('state');
		data.ntphost = _AVAILABLE_NTPHOST;
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'setTimeInfo', p:data}, function(d){
			Metronic.unblockUI('#settimeform');
	    	if(OPREL(d)){
	    	}
		});
	}
	
	var handlerInit = function(){
		
		//初始化城市
		var initCityInput = function(){
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getAllTimezone', p:{}}, function(d){
				var data = JSON.parse(d);
				var cityinput = $('select[name=cityinput]');
				cityinput.empty();
				for(var i=0; i<data.length; i++){
					var option = $("<option>").text(data[i]).val(data[i]);
					cityinput.append(option);
				}
				initDefaultCityAndTime();
			});
		}
		//初始化默认城市和时间
		var initDefaultCityAndTime = function(){
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getDefaultTimeInfo', p:{}}, function(d){
				var data = JSON.parse(d);
				_OLDTIME = data.date;
				_AUTHFLAG = data.authFlag;
				$('select[name=cityinput]').val(data.timezone);
				$('input[name=timeinput]').val(data.date);
				$(".form_datetime").datetimepicker({
					language:  'zh-CN', 
		            autoclose: true,
		            isRTL: Metronic.isRTL(),
		            format: "yyyy-MM-dd hh:ii:ss",
		            pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
		            forceParse:false
		        });
				//ntp配置初始化
				if(data.ntp.ntpFlag){
					//开启
					$('#ntpcheck').bootstrapSwitch('state', true);
					$('select[name=cityinput]').prop('disabled', true);
					$('input[name=timeinput]').prop('disabled', true);
					$('#ntphostdiv').show();
					$('#timesubmit').prop('disabled', true);
				}else{
					//关闭
					$('select[name=cityinput]').prop('disabled', false);
					$('input[name=timeinput]').prop('disabled', false);
					$('#ntphostdiv').hide();
					$('#timesubmit').prop('disabled', false);
				}
				var hosts = data.ntp.ntpServers;
				var ntphost = $('#ntphost');
				ntphost.empty();
				for(var i=0; i<hosts.length; i++){
					if(0 == i){
						var option = $("<option selected>").text(hosts[i]).val(hosts[i]);
					}else{
						var option = $("<option>").text(hosts[i]).val(hosts[i]);
					}
					
					ntphost.append(option);
				}
				
				$('#ntphost').editableSelect({ filter: false });
			});
		}
		
		//初始化按钮
		var initButtonListerner = function(){
			//启动/关闭NTP配置
			$('#ntpcheck').bootstrapSwitch('onSwitchChange', function (e, data) {
				if(data){
					$('select[name=cityinput]').prop('disabled', true);
					$('input[name=timeinput]').prop('disabled', true);
					$('#ntphostdiv').show();
					$('#timesubmit').prop('disabled', true);
				}else{
					$('select[name=cityinput]').prop('disabled', false);
					$('input[name=timeinput]').prop('disabled', false);
					$('#ntphostdiv').hide();
					$('#timesubmit').prop('disabled', false);
				}
			});
			
			//立即更新
			$('#ntpupdate').on('click', function(){
				$('#settimeform').validate({
					ntphost: {
	                	ntpipv4Ordomain: true,
	                }
				});
				if (!$('#settimeform').validate().form()) {
					return;
	            }
				var ntphost = $('#ntphost').val();
				Metronic.blockUI({target: '#settimeform',animate: true});
				var data = {};
				data.ntphost = $('#ntphost').val();
				data = JSON.stringify(data);
				$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'syncNtpTime', p:data}, function(d){
					Metronic.unblockUI('#settimeform');
			    	if(OPREL(d)){
			    		_AVAILABLE_NTPHOST = $('#ntphost').val();
			    		$('#timesubmit').prop('disabled', false);
			    		//系统时间更新为最新的时间
			    		var data = JSON.parse(d);
			    		$('input[name=timeinput]').val(data.ext.newTime);
			    	}
				});
			})
		}
		
		initCityInput();
		initButtonListerner();
		
	}
    return {
        init: function () {
        	handlerInit();
        	handleValidation();
        }
    };

}();

//系统名称设置 LOGO设置
var Settings_System_Name = function(){
	var handleValidation = function(){
		var settimeform = $('#setsystemname');

		settimeform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	systemname: {
                    required: true,
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
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
                
            }
            
        });
        
        $("#systemnamesubmit").click(function(){
        	if (settimeform.validate().form()) {
        		submit();
            }
        });
        
        $("#systemnamecancel").click(function(){
        	LOCATION('./content/platform/settings/setting_manager.php', 'setting_manager');
        });
	}
	
	//提交修改
	var submit = function(){
		Metronic.blockUI({target: '#setsystemname',animate: true});
		var data = {};
		data.systemname = $('input[name=systemname]').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'setSystemName', p:data}, function(d){
			Metronic.unblockUI('#setsystemname');
	    	if(OPREL(d)){
	    	}
		});
	}
	
	//初始化系统名称
	var initSystemNameInput = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getSystemName', p:''}, function(d){
			Metronic.unblockUI('#setsystemname');
			if(d){
				$('input[name=systemname]').val(d);
			}
		});
	}
	
	
	
	//设置系统LOGO
	var handleSetLogo = function () {
		var newLogoPath = null;
    	var url = CONF.AJAXPATH + '?m=' + CONF.M.SYSTEM + '&f=selectLogo';
	    $('#fileselect').fileupload({
	        url: url,
	        dataType: 'json',
	        done: function (e, data){
	        	if(!data.result.re){
	        		OPREL(JSON.stringify(data.result));
	        		return;
	        	}
	        	$("#newlogo").attr('src',data.result.ext); 
	        	$('#newlogodiv').show();
	        	newLogoPath = data.result.ext;
	        	$('.uploadbtndiv').hide();
	        	$('.savebtndiv').show();
	        },
	        start: function (e){
	        	Metronic.blockUI({target: '#logo_tab',animate: true});
	        },
	        stop: function (e) {
	            Metronic.unblockUI('#logo_tab');
	        },
	        fail: function (e, data) {
	        	var data = {lev:'error', re:false, title:LANG.UI_SETTING_UPLOAD_FILE, msg:LANG.UI_SETTING_UPLOAD_FILE_FAILURE + ',' + data.errorThrown};
	        	data = JSON.stringify(data);
	        	OPREL(data);
	        	newLogoPath = null;
	        },
	    }).prop('disabled', !$.support.fileInput)
	        .parent().addClass($.support.fileInput ? undefined : 'disabled');
	    
	    $("#cancellogo").click(function(){
        	CTLSIDEBAR('setting_manager');
        	LOCATION('./content/platform/settings/setting_manager.php','setting_manager');
        });
	    
	    $("#savelogo").click(function(){
        	if (newLogoPath) {
        		var data = {};
        		data.file = newLogoPath;
        		data = JSON.stringify(data);
        		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'setNewLogo', p:data}, function(d){
        			OPREL(d);
        		});
            }
        });
	    
    	return;
    }
	
	return {
		init: function(){
			//设置系统名称
			initSystemNameInput();
			handleValidation();
			//设置系统LOGO
			handleSetLogo();
		}
	}
}();

//系统数据管理/导入导出数据库
var Settings_DataBase_Manager = function(){
	var handleExportTask = function(){
		$('#exportTaskData').on('click', function(){
			Metronic.blockUI({target: '#export_tab',animate: true});
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'exportTaskInfo', p:''}, function(d){
				Metronic.unblockUI('#export_tab');
				var data = JSON.parse(d);
				if(!data.re){
					OPREL(d);
				}else{
					window.location.href = data.ext[0];
				}
			});
		});
	}
	
	var handleImportTask = function(){
    	var url = CONF.AJAXPATH + '?m=' + CONF.M.SYSTEM + '&f=importTaskInfo';
	    $('#taskFileImport').fileupload({
	        url: url,
	        dataType: 'json',
	        done: function (e, data){
	        	OPREL(JSON.stringify(data.result));
	        },
	        start: function (e){
	        	Metronic.blockUI({target: '#import_tab',animate: true});
	        },
	        stop: function (e) {
	            Metronic.unblockUI('#import_tab');
	        },
	        fail: function (e, data) {
	        	var data = {lev:'error', re:false, title:LANG.UI_SETTING_UPLOAD_FILE, msg:LANG.UI_SETTING_UPLOAD_FILE_FAILURE + ',' + data.errorThrown};
	        	data = JSON.stringify(data);
	        	OPREL(data);
	        },
	    }).prop('disabled', !$.support.fileInput)
	        .parent().addClass($.support.fileInput ? undefined : 'disabled');
	}
	return {
		init: function(){
			//导出任务信息
			handleExportTask();
			//导入任务信息
			handleImportTask();
		}
	}
}();

//系统通知
var Settings_System_Notice = function () {
	var _DEFAULT_CONF = {};
	
	//设置开关通知函数, 开关状态bool|开关ID|响应div class
	var setSwitch = function(flag, switchID, divClass){
		//添加change事件
		$('#' + switchID).bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('.' + divClass).show();	//开
			}else{
				$('.' + divClass).hide();	//关
			}
		});
		//设置默认值
		if(flag){
			$('#' + switchID).bootstrapSwitch('state', true); 
			$('.' + divClass).show();
		}else{
			$('#' + switchID).bootstrapSwitch('state', false); 
			$('.' + divClass).hide();
		}
	}
	
	//设置通知等级默认值, 开关|等级[]|设置哪个DIV
	var setNoticeLevel = function(flag, level, levelDiv){
		if(!flag) return;
		for(var i=0; i<level.length; i++){
			switch(level[i]){
				case "1":
					$('.' + levelDiv).find('input[name=level1]').iCheck('check');
					break;
				case "2":
					$('.' + levelDiv).find('input[name=level2]').iCheck('check');
					break;
				case "3":
					$('.' + levelDiv).find('input[name=level3]').iCheck('check');
					break;
			}
		}
	}
	
	//设置报表选择开关
	var setReportCheck = function(list,flag){
		if(!flag) return;
		if(list.storage){
			$('#storageReport').iCheck('check');
		}else{
			$('#storageReport').iCheck('uncheck');
		}
		if(list.vm){
			$('#vmReport').iCheck('check');
		}else{
			$('#vmReport').iCheck('uncheck');
		}
	}
	
	var setTimeStrategyCheck = function(flag,strategy){
		if(!flag) return;
		for(var i=0; i<strategy.length; i++){
			switch(strategy[i].type){
				case 1:
					$('#dayCheck').bootstrapSwitch('state', true);
					break;
				case 2:
					$('#weekCheck').bootstrapSwitch('state', true);
					break;
				case 3:
					$('#monthCheck').bootstrapSwitch('state', true);
					break;
				case 4:
					$('#yearCheck').bootstrapSwitch('state', true);
					break;
			}
		}
	}
	
	//设置邮件通知默认配置
	var setEmailDefault = function(){
		var emailConf = _DEFAULT_CONF.email;
		//设置三个开关
		setSwitch(emailConf.emailFlag, 'emailcheck', 'emailcontent');
		setSwitch(emailConf.systemFlag, 'systemchecke', 'systemcheckediv');
		setSwitch(emailConf.taskFlag, 'taskchecke', 'taskcheckediv');
		if(emailConf.receEmail){
			var receEmail = emailConf.receEmail.join("\r\n");
		}
		$('#emaillist').val(receEmail);
		
		//设置两个等级
		setNoticeLevel(emailConf.systemFlag, emailConf.systemLevel, 'systemcheckediv');
		setNoticeLevel(emailConf.taskFlag, emailConf.taskLevel, 'taskcheckediv');
		
		//设置报表选项
		
		//初始化测试配置
		$('input[name=emailhost]').val(emailConf.host);
		$('input[name=emailport]').val(emailConf.port);
		$('input[name=emailuser]').val(emailConf.user);
		$('input[name=emailpass]').val(emailConf.pass);
		$('select[name=encryption]').val(emailConf.encryption);
		$('#testrecemail').text(_DEFAULT_CONF.user.userEmail);
		//当未初始化的时候,需要先设置邮件通知开关和测试保存按钮不可用
		if(!emailConf.emailFlag){
			$('#emailcheck').bootstrapSwitch('disabled', true); 
			$('#testemailsubmit').prop('disabled', true);
		}
		
		//设置报表
		var reportConf = emailConf.reportConf;
		if(!reportConf){
			var reportFlag = false;
			var reportList = [];
			var timeStrategy = [];
		}else{
			reportFlag = reportConf.reportFlag;
			reportList = reportConf.report_check_list;
			timeStrategy = reportConf.timeStrategy;
		}
		setSwitch(reportFlag, 'reportCheck', 'reportcheckediv');
		setReportCheck(reportList, reportFlag);
		//设置通知时间策略
		initReportNotice(reportFlag, timeStrategy); //初始化报表通知
		setTimeStrategyCheck(reportFlag, timeStrategy);
	}
	
	//设置短信通知默认配置
	var setSmsDefault = function(){
		var smsConf = _DEFAULT_CONF.sms;
		//设置三个开关
		setSwitch(smsConf.smsFlag, 'smscheck', 'smscontent');
		setSwitch(smsConf.systemFlag, 'systemchecks', 'systemchecksdiv');
		setSwitch(smsConf.taskFlag, 'taskchecks', 'taskchecksdiv');
		//设置两个等级
		setNoticeLevel(smsConf.systemFlag, smsConf.systemLevel, 'systemchecksdiv');
		setNoticeLevel(smsConf.taskFlag, smsConf.taskLevel, 'taskchecksdiv');
		//初始化测试配置
		$('.testrecphone').text(_DEFAULT_CONF.user.userPhone);
		$('#smsquantity').text(smsConf.quantity);
		//短信猫初始化设置
		var deviceConfig = smsConf.deviceConfig;
		$('input[name=modemipaddr]').val(deviceConfig.ip);
		$('input[name=modemdatabase]').val(deviceConfig.database);
		$('input[name=modemport]').val(deviceConfig.port);
		$('input[name=modemusername]').val(deviceConfig.user);
		$('input[name=modempassword]').val(atob(deviceConfig.pass));
		//设置发送方式描述
		if(1 == smsConf.sendType){
			$('#smssendtypedes').html($('#sendtypeinternet').html());
		}else if(2 == smsConf.sendType){
			$('#smssendtypedes').html($('#sendtypemodem').html());
		}
		
		//当未初始化的时候,需要先设置短信通知开关不可用
		if(!smsConf.smsFlag){
			$('#smscheck').bootstrapSwitch('disabled', true); 
		}
	}
	
	//设置默认配置
	var setDefaultConf = function(){
		setEmailDefault();
		setSmsDefault();
	}
	
	//得到默认信息
	var initDefaultConf = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getDefaultNoticeConf', p:{}}, function(d){
			var data = JSON.parse(d);
			_DEFAULT_CONF = data;
			setDefaultConf();
		});
	}
	
	//得到设置等级 
	var getLevelSetting = function(divClass){
		var level1 = $('.' + divClass).find('input[name=level1]').is(':checked');
		var level2 = $('.' + divClass).find('input[name=level2]').is(':checked');
		var level3 = $('.' + divClass).find('input[name=level3]').is(':checked');
		var level = [level1, level2, level3];
		return level;
	}
	
	//得到选中的报表
	var getReportCheck = function(){
		var storage = $('#storageReport').is(':checked');
		var vm = $('#vmReport').is(':checked');
		var list = {
			storage: storage,
			vm: vm
		}
		
		return list;
	}
	
	//得到邮件通知时间策略配置
	var getReportStrategy = function(){
		var strategyList = [];
		if($('#dayCheck').bootstrapSwitch('state') == true){
			var time = $('#dayTime').val();
			var strategy = {
				type: 1,
				notice_time: time,
			}
			strategyList.push(strategy);
		}
		
		if($('#weekCheck').bootstrapSwitch('state') == true){
			var time = $('#weekTime').val();
			var days = [];
			$('.weekcheck').find('input').each(function(i, d){
				if(d.checked){
					days[i] = 1;
				}else{
					days[i] = 0;
				}
			});
			var strategy = {
				type: 2,
				notice_time: time,
				days: days
			}
			strategyList.push(strategy);
		}
		
		if($('#monthCheck').bootstrapSwitch('state') == true){
			var time = $('#monthTime').val();
			var days = [];
			$('.monthcheck').find('input').each(function(i, d){
				if(d.checked){
					days[i] = 1;
				}else{
					days[i] = 0;
				}
			});
			var strategy = {
				type: 3,
				notice_time: time,
				days: days
			}
			strategyList.push(strategy);
		}
		
		if($('#yearCheck').bootstrapSwitch('state') == true){
			var time = $('#yearTime').val();
			var strategy = {
				type: 4,
				notice_time: time
			}
			strategyList.push(strategy);
		}
		
		return strategyList;
		
	}
	
	//检查邮件地址
	var checkEmailList = function(emailList){
		var list = emailList.split(/[\n,]/g);
		var reg = /^(\w-*\.*)+@(\w-?)+(\.\w{2,})+$/;
		var emailArr = [];
		for(var i=0; i<list.length; i++){
			var email = $.trim(list[i])
			if("" != email){
				if(!reg.test(email)){
					UIToastr.showWarning(LANG.UI_SETTING_EMAIL_INPUT, LANG.UI_SETTING_EMAIL_INPUT_TIP);
					return false;
				}else{
					emailArr.push(email);
				}
			}
		}
		return emailArr;
	}
	
	var addListeners = function(){
		//切换消息推送配置开关
		$('#pushCheck').on('switchChange.bootstrapSwitch', function(){
			if(this.checked){
				$('.pushChild').show();
			}else{
				$('.pushChild').hide();
			}
		});
		
		$("#emailcancel").click(function(){
        	LOCATION('./content/platform/settings/setting_manager.php?tab=2', 'setting_manager');
        });
		$("#smscancel").click(function(){
        	CTLSIDEBAR('setting_manager');
        	LOCATION('./content/platform/settings/setting_manager.php?tab=2', 'setting_manager');
        });
		
		$('#emailsubmit').on('click', function(){
			
			var data = {};
			data.flag = $('#emailcheck').bootstrapSwitch('state');
			data.systemFlag = $('#systemchecke').bootstrapSwitch('state');
			data.systemLevel = getLevelSetting('systemcheckediv');
			data.taskFlag = $('#taskchecke').bootstrapSwitch('state');
			data.taskLevel = getLevelSetting('taskcheckediv');
			data.recEmail = checkEmailList($('#emaillist').val());
			if(false === data.recEmail){
				return false;
			}
			
			
			data.recEmail = JSON.stringify(data.recEmail);
			
			//报表通知参数提交
			var reportFlag = $('#reportCheck').bootstrapSwitch('state');
			var reportCheckList = getReportCheck();
			var reportTimeStrategy = getReportStrategy();
			var conf = {
				reportFlag: reportFlag,
				report_check_list: reportCheckList,
				timeStrategy: reportTimeStrategy
			}
			
			data.reportConf = JSON.stringify(conf);
			
			jsonData = JSON.stringify(data);
			Metronic.blockUI({target: '#emailtab',animate: true});
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'setEmailConf', p:jsonData}, function(d){
				Metronic.unblockUI('#emailtab');
		    	if(OPREL(d)){
		    	}
			});
		});
		$('#testemail').on('click', testEmail);
		
		$('#smssubmit').on('click', function(){
			Metronic.blockUI({target: '#smstab',animate: true});
			var data = {};
			data.flag = $('#smscheck').bootstrapSwitch('state');
			data.systemFlag = $('#systemchecks').bootstrapSwitch('state');
			data.systemLevel = getLevelSetting('systemchecksdiv');
			data.taskFlag = $('#taskchecks').bootstrapSwitch('state');
			data.taskLevel = getLevelSetting('taskchecksdiv');
			jsonData = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'setSmsConf', p:jsonData}, function(d){
				Metronic.unblockUI('#smstab');
		    	if(OPREL(d)){
		    	}
			});
		});
		$('#testsms').on('click', testSms);
		
		//报表通知监听事件
		$('#dayCheck').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('#dayIcon').show();
				$('#dayDiv').show();	//开
			}else{
				$('#dayIcon').hide();
				$('#dayDiv').hide();	//关
			}
		});
		$('#weekCheck').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('#weekIcon').show();
				$('#weekDiv').show();	//开
			}else{
				$('#weekIcon').hide();
				$('#weekDiv').hide();	//关
			}
		});
		$('#monthCheck').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('#monthIcon').show();
				$('#monthDiv').show();	//开
			}else{
				$('#monthIcon').hide();
				$('#monthDiv').hide();	//关
			}
		});
		$('#yearCheck').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('#yearIcon').show();
				$('#yearDiv').show();	//开
			}else{
				$('#yearIcon').hide();
				$('#yearDiv').hide();	//关
			}
		});
		
		
		
		handleModemValidation();
		
	}
	
	//配置邮件服务器
	var testEmail = function(){
		$('#testemaildiv').modal();
		handleEmailValidation();
	}
	
	//配置邮件服务器验证
	var handleEmailValidation = function() {
        var setipform = $('#testemailform');

        setipform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	emailhost: {
                    required: true,
                    ipv4Ordomain: true,
                },
                emailport: {
                    required: true,
                    number: true,
                },
                emailuser: {
                	required: true,
                	email: true,
                },
//                emailpass: {
//                    required: true,
//                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
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
                
            }
            
        });
        
        $.validator.addMethod("ipv4Ordomain", function(value, element) {
    		return this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
    		var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
    		return domain || ipv4;
        }, LANG.UI_TOOLS_IP_OR_DOMAIN);

    		
    	var getSmtpConf = function(){
    		var data = {};
    		data.host = $('input[name=emailhost]').val();
    		data.port = $('input[name=emailport]').val();
    		data.user = $('input[name=emailuser]').val();
    		data.pass = btoa($('input[name=emailpass]').val());
    		data.encryption = $('select[name=encryption]').val();
    		data.recEmail = $('#testrecemail').text();
    		jsonData = JSON.stringify(data);
    		return jsonData;
    	}
        //测试邮件发送
    	$('#sendtestmail').unbind().on('click', function(){
    		if(!_DEFAULT_CONF.user.userEmailFlag){
    			UIToastr.showWarning(LANG.UI_SETTING_NO_EMAIL, LANG.UI_SETTING_NO_EMAIL_TIPS);
    		}else{
    			if(setipform.validate().form()) {
    				var jsonData = getSmtpConf();
    				Metronic.blockUI({target: '#testemaildiv',animate: true});
    				$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'testEmailNotice', p:jsonData}, function(d){
    					Metronic.unblockUI('#testemaildiv');
    			    	if(OPREL(d)){
    			    		$('#emailcheck').bootstrapSwitch('disabled', false); 
    						$('#testemailsubmit').prop('disabled', false);
    			    	}
    				});
                }
    		}
    	});
    	
        $("#testemailsubmit").unbind().on('click', function(){
        	if (setipform.validate().form()) {
        		var jsonData = getSmtpConf();
				Metronic.blockUI({target: '#testemaildiv',animate: true});
				$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'setSmtpSetting', p:jsonData}, function(d){
					Metronic.unblockUI('#testemaildiv');
			    	if(OPREL(d)){
			    		$('#testemaildiv').modal('hide');
			    	}
				});
            }
        });
        
	};
	
	//配置短信猫验证
	var handleModemValidation = function() {
        var setipform = $('#setmodem');

        setipform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	modemipaddr: {
                    required: true,
                    ipv4Ordomain: true,
                },
                modemdatabase:{
                	required: true,
                },
                modemport: {
                    required: true,
                    number: true,
                },
                modemusername: {
                	required: true,
                },
                modempassword: {
                    required: true,
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
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
                
            }
            
        });
        
        $.validator.addMethod("ipv4Ordomain", function(value, element) {
    		return this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
    		var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
    		return domain || ipv4;
        }, LANG.UI_TOOLS_IP_OR_DOMAIN);


    	var getModemConf = function(){
    		var data = {};
    		data.ip = $('input[name=modemipaddr]').val();
    		data.database = $('input[name=modemdatabase]').val();
    		data.port = $('input[name=modemport]').val();
    		data.user = $('input[name=modemusername]').val();
    		data.pass = btoa($('input[name=modempassword]').val());
    		data.recPhone = $('#modemrecphone').text();
    		jsonData = JSON.stringify(data);
    		return jsonData;
    	}
    	
    	//测试短信猫发送
        $("#sendmodemsms").unbind().on('click', function(){
        	if(!_DEFAULT_CONF.user.userPhoneFlag){
    			UIToastr.showWarning(LANG.UI_SETTING_NO_PHONE_NUM, LANG.UI_SETTING_NO_PHONE_NUM_TIP);
    			return;
    		}
        	if (setipform.validate().form()) {
        		var jsonData = getModemConf();
				Metronic.blockUI({target: '#testsmsdiv',animate: true, cenrerY: true});
				$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'testSmsModemNotice', p:jsonData}, function(d){
					Metronic.unblockUI('#testsmsdiv');
			    	if(OPREL(d)){
			    		$('#testsmsdiv').modal('hide');
			    		$('#smscheck').bootstrapSwitch('disabled', false);
			    		$('#smssendtypedes').html($('#sendtypemodem').html());
			    	}
				});
            }
        });
        
	};
	
	
	//测试短信
	var testSms = function(){
		$('#testsmsdiv').modal();
		//测试短信发送
		$('#sendtestsms').unbind().on('click', function(){
			var count = 120;
			var btn = $('#sendtestsms');
			var oldText = btn.html();
			var newText = LANG.UI_SETTING_DELAY_SECOND;
			var butModal = function(){
				if(count > 0){
					btn.prop("disabled",true);
					btn.html(count-- + newText);
					setTimeout(function(){
						butModal();
		            },1000);
				}else{
					btn.prop("disabled",false);
					btn.html(oldText);
				}
			}
    		if(!_DEFAULT_CONF.user.userPhoneFlag){
    			UIToastr.showWarning(LANG.UI_SETTING_NO_PHONE_NUM, LANG.UI_SETTING_NO_PHONE_NUM_TIP);
    		}else{
				var data = {'phone':_DEFAULT_CONF.user.userPhone};
				var jsonData = JSON.stringify(data);
				Metronic.blockUI({target: '#testsmsdiv',animate: true});
				$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'testSmsNotice', p:jsonData}, function(d){
					Metronic.unblockUI('#testsmsdiv');
			    	if(OPREL(d)){
			    		butModal();	//设置按钮状态
			    		$('#testsmsdiv').modal('hide');
			    		$('#smscheck').bootstrapSwitch('disabled', false);
			    		$('#smssendtypedes').html($('#sendtypeinternet').html());
			    	}
			    	var pData = JSON.parse(d);
			    	if(pData.ext.quantity != null){
			    		_DEFAULT_CONF.sms.quantity = pData.ext.quantity;
			    		$('#smsquantity').text(_DEFAULT_CONF.sms.quantity);
			    	}
				});
    		}
    	});
	}
	
	//初始化报表通知时间配置
	var initReportNotice = function(flag, strategy){
		initDays(flag, strategy);
		initWeeks(flag, strategy);
		initMonth(flag, strategy);
		initYear(flag, strategy);
		
		$('.starttime').timepicker({
            autoclose: true,
            minuteStep: 5,
            showSeconds: true,
            showMeridian: false,
//            defaultTime:'00:00:00'
        });
		
		$('.timepicker').parent('.input-group').on('click', '.input-group-btn', function(e){
            e.preventDefault();
            $(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
        });
		
	}
	
	var initDays = function(flag, strategy){
		var notice_time = "08:00:00";
		if(flag){
			for(var i=0;i<strategy.length;i++){
				if(strategy[i].type == 1){
					notice_time = strategy[i].notice_time;
				}
			}
		}
		var html = '<div class="form-group" style="margin-top: 20px;">' +
						'<div class="col-md-5">' +
						'<label style="display:block;width:80px;float:left;">' + LANG.UI_PUBLIC_NOTICE_TIME + '</label>' +
							'<div class="input-group" style="width: 150px;float:left;">' +
								'<input type="text" value="' + notice_time + '" class="form-control timepicker timepicker-24 starttime" id="dayTime">' +
								'<span class="input-group-btn">' +
								'<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>' +
								'</span>' +
							'</div>' +
						'</div>' +
					'</div>';
		$('#dayDiv').append(html);
	}
	
	var initWeeks = function(flag, strategy){
		var notice_time = "08:00:00";
		var days = [1, 0, 0, 0, 0, 0, 0];
		if(flag){
			for(var i=0;i<strategy.length;i++){
				if(strategy[i].type == 2){
					notice_time = strategy[i].notice_time;
					days = strategy[i].days;
				}
			}
		}
		var html = '<div class="input-group weekcheck">' + 
				    '<row>' + 
				        '<div>';

		for(var j=0; j<7; j++){
			var checked = '';
			var weedDes = [LANG.UI_STRATEGY_MONDAY , LANG.UI_STRATEGY_TUESDAY , LANG.UI_STRATEGY_WEDNESDAY , LANG.UI_STRATEGY_THURSDAY , LANG.UI_STRATEGY_FRIDAY , LANG.UI_STRATEGY_SATURDAY , LANG.UI_STRATEGY_SUNDAY];
			//初始化每周复选框
			if(days[j]){
			  checked = 'checked';
			}
			html += '<label style="padding-right: 10px;"><input type="radio" data-checkbox="icheckbox_square-blue" ' + checked + ' name="week" class="icheck"> ' + weedDes[j] + ' </label>';
		}
		html += '</div></row></div>' + '<div class="form-group" style="margin-top: 20px;">' +
				'<div class="col-md-5">' +
					'<label style="display:block;width:80px;float:left;">' + LANG.UI_PUBLIC_NOTICE_TIME + '</label>' +
					'<div class="input-group" style="width: 150px;float:left">' +
						'<input type="text" value="' + notice_time + '" class="form-control timepicker timepicker-24 starttime" id="weekTime">' +
						'<span class="input-group-btn">' +
						'<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>' +
						'</span>' +
					'</div>' +
				'</div>' +
			'</div>';
		
		$('#weekDiv').append(html);
		
	}
	
	
	var initMonth = function(flag, strategy){
		var notice_time = "08:00:00";
		var days = [1, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
		if(flag){
			for(var i=0;i<strategy.length;i++){
				if(strategy[i].type == 3){
					notice_time = strategy[i].notice_time;
					days = strategy[i].days;
				}
			}
		}
		var html = '<div class="input-group monthcheck">';
		for(var j=0; j<31; j++){
			var checked = '';
			if(days[j]){
				checked = 'checked';
			}
			var thisDay = j + 1;
			html += '<label class="label60"><input type="radio" data-checkbox="icheckbox_square-blue" ' + checked + ' name="month" class="icheck"> ' + thisDay + ' </label>';
		}
		html += '</div>' + '<div class="form-group" style="margin-top: 20px;">' +
				'<div class="col-md-5">' +
					'<label style="display:block;width:80px;float:left;">' + LANG.UI_PUBLIC_NOTICE_TIME + '</label>' +
					'<div class="input-group" style="width: 150px;float:left;">' +
						'<input type="text" value="' + notice_time + '" class="form-control timepicker timepicker-24 starttime" id="monthTime">' +
						'<span class="input-group-btn">' +
						'<button class="btn default" type="button"><i class="fa fa-clock-o"></i></button>' +
						'</span>' +
					'</div>' +
				'</div>' +
			'</div>';
		$('#monthDiv').append(html);
	}
	
	var initYear = function(flag, strategy){
		if(flag){
			for(var i=0;i<strategy.length;i++){
				if(strategy[i].type == 4){
					$('#yearTime').val(strategy[i].notice_time);
				}
			}
		}
		$(".form_datetime").datetimepicker({
			language:  'zh-CN', 
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-MM-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left")
        });
	}
	
	
    return {
        init: function () {
        	//初始化默认选项
        	initDefaultConf();
        	//添加事件
        	addListeners();
        }
    };

}();


//DNS解析
var Settings_DNS = function(){
	var initBackupNodeList = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getAddStorageNodeSelect',p:{}}, function(d){
			var data = JSON.parse(d);
			var nodeselect = $('select[name=dnsnodelist]');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				nodeselect.append(option);
			}
			
			initDnsList($('select[name=dnsnodelist]').val());
    	});
	}
	
	var initDnsList = function(nodeuuid){
		var data = {'nodeuuid':nodeuuid};
		var jsonData = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getNodeDnsHosts', p:jsonData}, function(d){
	    	$('#dnslist').val(d);
		});
	}
	
	var submitDnsSetting = function(){
		var selectNode = $('select[name=dnsnodelist]').val();
		var dnsSetting = $('#dnslist').val();
		var dnscheck = $('#dnscheck').bootstrapSwitch('state');
		var data = {'nodeuuid':selectNode, 'setting':dnsSetting, 'syncnode': dnscheck};
		var jsonData = JSON.stringify(data);
		Metronic.blockUI({target: '#dnsform',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'setNodeDnsHosts', p:jsonData}, function(d){
			Metronic.unblockUI('#dnsform');
			OPREL(d);
		});
	}
	
	var addListeners = function(){
		$("#dnscancel").click(function(){
        	LOCATION('./content/platform/settings/setting_manager.php?tab=3', 'setting_manager');
        });
		
		$("#dnssubmit").on('click', submitDnsSetting);
		
		$('select[name=dnsnodelist]').on('change', function(){
			initDnsList($(this).val());
		});
	}
	
	return {
        init: function () {
        	//初始化备份节点
        	initBackupNodeList();
        	//添加事件
        	addListeners();
        }
    };
}();

//关机/重启
var Settings_Poweroff = function(){
	
	var initBackupNodeList = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getAddStorageNodeSelect',p:{}}, function(d){
			var data = JSON.parse(d);
			var nodeselect = $('select[name=powernodelist]');
			nodeselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].text).val(data[i].uuid);
				nodeselect.append(option);
			}
    	});
	}
	
	//初始化正在运行的任务名
	var initRunningTaskName = function(data){
		$('#runningtaskdiv').html('');
		var taskname = '';
		for(var i=0; i<data.length; i++){
			taskname += data[i].taskname + "<br><br>";
		}
		$('#runningtaskdiv').html(taskname);
	}
	
	//重启
	var reboot = function(){
		var data = {};
		data.nodeuuid = $('select[name=powernodelist]').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeRunningTaskList',p:data}, function(d){
			var data = JSON.parse(d);
			var nodeText = $('select[name=powernodelist] option:selected').text();
			
			if(data.length > 0){
				//如果有任务正在运行,提示后再提交
				return UIToastr.showWarning(LANG.UI_SETTING_POWEROFF_RESTART, LANG.UI_SETTING_POWEROFF_POWER_STOP_RUNNING_TASK_TIPS);
//				var titel = '<i class="fa  fa-circle-o-notch"></i> ' + LANG.UI_SETTING_POWEROFF_RESTART + nodeText;
//				$('#powermodaldiv').find('.modal-title').html(titel);
//				$('input[name=powertype]').val(1);
//				initRunningTaskName(data);
//				$('#powermodaldiv').modal();
			}else{
				//没有在运行的任务,直接确认提示
				bootbox.confirm({
		            title: LANG.UI_SETTING_POWEROFF_RESTART,
		            message: LANG.UI_SETTING_POWEROFF_RESTART_TIPS + nodeText + "?",
		            callback: debounce(function(r) {
		                if(!r) return;
		                Metronic.blockUI({target: '#powermodaldiv',animate: true});
		                rebootSubmit('#powermodaldiv');
		            }, 300)
		        });
			}
    	});
	}
	
	//关机
	var poweroff = function(){
		var data = {};
		data.nodeuuid = $('select[name=powernodelist]').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeRunningTaskList',p:data}, function(d){
			var data = JSON.parse(d);
			var nodeText = $('select[name=powernodelist] option:selected').text();
			
			if(data.length > 0){
				return UIToastr.showWarning(LANG.UI_SETTING_POWEROFF_POWER, LANG.UI_SETTING_POWEROFF_POWER_STOP_RUNNING_TASK_TIPS);
				//如果有任务正在运行,提示后再提交
//				var titel = '<i class="fa fa-power-off"></i> ' + LANG.UI_SETTING_POWEROFF_POWER + nodeText;
//				$('#powermodaldiv').find('.modal-title').html(titel);
//				$('input[name=powertype]').val(2);
//				initRunningTaskName(data);
//				$('#powermodaldiv').modal();
			}else{
				//没有在运行的任务,直接确认提示
				bootbox.confirm({
		            title: LANG.UI_SETTING_POWEROFF_POWER,
		            message: LANG.UI_SETTING_POWEROFF_POWER_TIPS + nodeText + "?",
		            callback: debounce(function(r) {
		                if(!r) return;
		                Metronic.blockUI({target: '#powermodaldiv',animate: true});
		                poweroffSubmit('#powermodaldiv');
		            }, 300)
		        });
			}
    	});
	}
	
	//提交重启
	var rebootSubmit = function(blockID){
		var data = {};
		data.nodeuuid = $('select[name=powernodelist]').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'doRebootBackupNode',p:data}, function(data){
    		Metronic.unblockUI(blockID);
    		if(OPREL(data)){
    		}
    	});
	}
	
	//提交关机
	var poweroffSubmit = function(blockID){
		var data = {};
		data.nodeuuid = $('select[name=powernodelist]').val();
		data = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'doPoweroffBackupNode',p:data}, function(data){
    		Metronic.unblockUI(blockID);
    		if(OPREL(data)){
    		}
    	});
	}
	
	var powersubmit = function(){
		var type = $('input[name=powertype]').val();
		Metronic.blockUI({target: '#powerform',animate: true});
		if("1" == type){
			//重启
			rebootSubmit("#powerform");
		}else if("2" == type){
			//关闭
			poweroffSubmit("#powerform");
		}
	}
	
	var addListeners = function(){
		$('#powerreboot').on('click', reboot);
		$('#poweroff').on('click', poweroff);
		$('#powersubmit').on('click', powersubmit);
	}
	
	return {
        init: function () {
        	//初始化备份节点
        	initBackupNodeList();
        	//添加事件
        	addListeners();
        }
    };
}();

//系统升级
var Settings_System_Upgrade = function () {
	var patchGrid,patchGridFlag = false;
	var historyGrid, historyGridFlag = false;
	var initFlag = false;
	var updateFlag = false;
	var masterFlag = false;
	
	var addListeners = function(){
		//检测最新版本
	    
	    $('#upgrade').on('click', function(){
	    	$('#cancelUpdate').show();
			$('#upgradeSubmit').show();
			$('#upgradeModal .close').show();
        	$('#nodeSelect').prop('disabled', false);
	    	var select = patchGrid.getSelectedRows();
	    	if(!select.length){
	    		return UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_SYSTEM, LANG.UI_SETTINGS_UPDATE_SYSTEM_NO_SELECT);
	    	}
	    	
	    	if(select.length > 1){
				return UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_SYSTEM, LANG.UI_SETTINGS_UPDATE_SYSTEM_SELECT_TOO_MANY);
			}
	    	$('#upgradeModal').modal({'width':'630px', 'height':'500px'});
	    	initUpgradeModal(select);
	    	initNodeSelect();
	    	$('#childDiv').hide();
	    	updateFlag = false;
	    });
	    $('#fileupload').on('click',function(){
	    	$('#uploadModal').modal({'width':'600px', 'height':'500px'});
	    	$("#picker div:eq(1)").attr("style","position: absolute; top: 0px; left: 0px; width: 72px; height: 28px; overflow: hidden; bottom: auto; right: auto;");
	    });
	    
	    $("#deletePatch").on('click', function(){
    		deletePatch(patchGrid);
    	});
	    
	    $('#deleteHistoryLog').on('click', function(){
	    	deletePatchLog(historyGrid);
	    });
	    
	    $('#upgradeSubmit').on('click', upgradeSubmit);
	    
	    $('#cancelUpdate').on('click',function(){
	    	$('#updateList').empty();
        	$('#progressDiv').hide();
        	$('#updateAlarm').hide();
	    });
	    
	    $('#closeBtn').on('click', function(){
	    	$('#upgradeModal').modal('hide');
	    	$('#updateList').empty();
        	$('#progressDiv').hide();
        	$('#updateAlarm').hide();
        	$('#updateSuccess').hide();
			$('#closeBtn').hide();
        	if(masterFlag){
        		UIToastr.showSuccess(LANG.UI_SETTINGS_UPDATE_SYSTEM, LANG.UI_SETTINGS_UPDATE_SYSTEM_SUCCESS_TIPS)
            	setTimeout(function(){window.location = './loginout.php'}, 5000);
        	}
	    });
	    
	    
	}
	
	//确定系统升级
	var upgradeSubmit = function(){
		
		var nodeuuids = [];
		var initflag = false;
		var select = patchGrid.getSelectedRows();
		var patchuuid = select[0];
		var name = $('#patchName').text();
		if($('#nodeSelect').val()!=2){
			masterFlag = true;
			nodeuuids.push($('#nodeSelect').val());
		}else{
			masterFlag = false;
			$("#childNodes input:checked").each(function(i){
				nodeuuids[i] = $(this).val();
			});
		}
		if(nodeuuids.length==0){
			return UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_SELECT_NODE, LANG.UI_SETTINGS_UPDATE_SELECT_NODE_TIPS);
		}
		var params = {
			'nodeuuids': nodeuuids,
			'uuid': patchuuid,
			'masterFlag': masterFlag,
			'name': name
		}
		var p = JSON.stringify(params);
		if(!updateFlag){
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'upgradeCheck',p:p}, function(d){
				if(OPREL(d)){
					startUpdate(p, nodeuuids,initflag); //启动升级
					$('#nodeSelect').prop('disabled', true);
				}
			});
		}else{
			$('#upgradeModal').modal('hide');
		}
		
		
	}
	
	//启动升级调用
	var startUpdate = function(p, nodeuuids, initflag){
		$('#updateAlarm').show();
		$('#updateList').empty();
		var li = "";
		for(var i=0;i<nodeuuids.length;i++){
			li = '<li style="width: 92%;position:relative;" id="'+nodeuuids[i]+'"><div><span class="nodeName"></span><span class="updateStatus" style="float:right;"></span></div>' +
				'<div class="progress progress-striped active" style="margin-bottom:10px;"><div class="progress-bar progress-bar-success total-progress" role="progressbar" aria-valuemin="0" aria-valuemax="100" style="width: 0%">'+
		        '</div><span  style="position:absolute;right:-35px;"class=" progressright"></span></div></li>';
			$('#updateList').append(li);
		}
		initProgress(nodeuuids,initflag);

		setTimeout(function(){$('#progressDiv').show();}, 2000);
		Metronic.blockUI({target: '#upgradeModal',animate: true});
		$('#upgradeSubmit').prop('disabled',"true");
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'upgradeSystem',p:p}, function(d){
			Metronic.unblockUI('#upgradeModal');
		});
	}
	
	//设置升级中基本信息
	var setBasicInfo = function(data,timeoutID, nodeuuids){
		var data = JSON.parse(data);
		var statusList = [];
		for (var i=0;i<data.length;i++){
			$('#'+data[i].node_uuid +' .nodeName').html(data[i].node_name);
			$('#'+data[i].node_uuid +' .updateStatus').html(data[i].statusDes);
			$('#'+data[i].node_uuid +' .total-progress').css({width: data[i].progress});
			$('#'+data[i].node_uuid +' .progressright').html(data[i].progress);
			setStatusClass(data[i]);
			statusList.push(data[i].status);
		}
		if(statusList.length != 0){
			if(nodeuuids.length == statusList.length && $.inArray(6, statusList) == -1 && $.inArray(5, statusList) == -1 
					&& $.inArray(1, statusList) == -1 && $.inArray(7, statusList) == -1){
				$('#upgradeSubmit').removeAttr("disabled");
				clearTimeout(timerTask.SystemUpgrade_RunningInfo);
				historyGrid.getRefresh(getHistoryParams());
			}
			if(nodeuuids.length == statusList.length && $.inArray(4, statusList) != -1){
				updateFlag = true;
				$('#updateAlarm').hide();
				$('#cancelUpdate').hide();
				$('#upgradeSubmit').hide();
				$('#upgradeModal .close').hide();
				
				var des = "";
				if(masterFlag){
					des += '<p>' + LANG.UI_SETTINGS_UPDATE_PRIMARY_NODE_SUCCESS_TIPS + '</p>';
				}else{
					des += '<p>' + LANG.UI_SETTINGS_UPDATE_CHILD_NODE_SUCCESS_TIPS + '</p>';
				}
				$('#updateSuccess').html(des);
				$('#updateSuccess').show();
				$('#closeBtn').show();
			}
		}
	}
	
	//设置升级状态值颜色
	var setStatusClass = function(data){
		var status = data.status;
		var statusDiv = $('#'+data.node_uuid +' .updateStatus');
		var levelClass = ""
		switch(status){
		case 1:
			levelClass = "update-info";
			break;
		case 2:
			levelClass = "update-error";
			break;
		case 3:
			levelClass = "update-error";
			break;
		case 4:
			levelClass = "update-success";
			break;
		case 5:
			levelClass = "update-info";
			break;
		case 6:
			levelClass = "update-info";
			break;
		case 7:
			levelClass = "update-info";
			break;
		case 8:
			levelClass = "update-error";
			break;
		default:
			levelClass = "update-info";
			break;
		}
		statusDiv.removeClass('update-error update-info update-success').addClass(levelClass);
		return;
	}
	
	//获取升级进度信息
	var initProgress = function(nodeuuids, initflag){
		var updateInterval = 2000;
		var init = function(){
			if(0 == $('#patch_uuid').size()){
        		clearTimeout(timerTask.SystemUpgrade_RunningInfo);
        		return;
        	}
			var data = {};
			data.uuid = $("#patch_uuid").val();
			data.nodeuuids = nodeuuids;
			data.initflag = initflag;
			data = JSON.stringify(data);
			$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getUpgradeInfo',p:data}, function(d){setBasicInfo(d, timerTask.SystemUpgrade_RunningInfo, nodeuuids)});
			timerTask.SystemUpgrade_RunningInfo = setTimeout(init, updateInterval);
			initflag = true;
		}
		init();
	}
	
	//初始化升级模态框
	var initUpgradeModal = function(select){
		var params = {
			"uuid": select[0]
		}
		var p = JSON.stringify(params);
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getSelectPatch',p:p}, function(d){
			var data = JSON.parse(d);
			$('#patchName').html(data.name);
			$('#patch_uuid').attr("value", select[0]);
		});
	}
	
	//删除升级历史日志
	var deletePatchLog = function(grid){
		var select = grid.getSelectedRows();
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_DELETE_HISTORY, LANG.UI_SETTINGS_UPDATE_DELETE_HISTORY_NO_SELECT);
		}
		bootbox.confirm({
            title: LANG.UI_SETTINGS_UPDATE_DELETE_HISTORY,
            message: LANG.UI_SETTINGS_UPDATE_DELETE_HISTORY_CONFIRM,
            callback: debounce(function(r) {
                if(!r) return;
                submitDeleteHistory(select, grid);
            }, 300)
        });
	}
	
	//调用删除升级历史日志
	var submitDeleteHistory = function(select, grid){
		var data = {};
		data.ids = select;
		data = JSON.stringify(data);
		var grid = grid;
		Metronic.blockUI({target: '#patchHistory',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'deletePatchHistory',p:data}, function(data){
    		Metronic.unblockUI('#patchHistory');
    		if(OPREL(data)){
    			historyGrid.getRefresh(getHistoryParams());
    		}
    	});
	}
	
	//删除升级包tips
	var tipDelete = function(){
		UIToastr.showInfo(LANG.UI_SETTINGS_UPDATE_DELETE_PATCH, LANG.UI_SETTINGS_UPDATE_DELETE_PATCH_NO_SELECT);
	}
	
	//删除升级包
	var deletePatch = function(grid){
		var select = grid.getSelectedRows();
		if(!select.length){
			return tipDelete();
		}
		bootbox.confirm({
            title: LANG.UI_SETTINGS_UPDATE_DELETE_PATCH,
            message: LANG.UI_SETTINGS_UPDATE_DELETE_PATCH_CONFIRM,
            callback: debounce(function(r) {
                if(!r) return;
                submitDelete(select, grid);
            }, 300)
        });
	}
	
	//执行删除操作
	var submitDelete = function(select, grid){
    	var data = {};
		data.uuids = select;
		data = JSON.stringify(data);
		var grid = grid;
		Metronic.blockUI({target: '#patchManage',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'deletePatch',p:data}, function(data){
    		Metronic.unblockUI('#patchManage');
    		if(OPREL(data)){
    			patchGrid.getRefresh(getUpdateParams());
    		}
    	});
    }
	
	//初始化上传插件
	var initUploader = function(){
		$list = $('#thelist');
        var flie_count = 0;
        var uploader = WebUploader.create({
            //设置选完文件后是否自动上传
            auto: false,
            //swf文件路径
            swf: '../img/Uploader.swf',
            // 文件接收服务端。
            server: '/api/?m=8&f=uploadPatch',
            // 选择文件的按钮。可选。
            // 内部根据当前运行是创建，可能是input元素，也可能是flash.
            pick: '#picker',
            accept: {
			   title: 'upgrade package file',
			   extensions: 'tar.gz',
			   mimeTypes: ''
			},
			chunked: true, //分片上传大文件
			chunkSize: 5 * 1024 * 1024,
			chunkRetry: 3,//如果某个分片由于网络问题出错，允许自动重传多少次
            resize: false//不压缩
		});
		//当文件被添加到队列之前
		uploader.on('beforeFileQueued', function (file) {
			var name = $.trim(file.name);
			var txt = name.substring(name.length-6, name.length); //只允许tar.gz添加
			if(txt != "tar.gz" || file.size == 0 || name.indexOf(" ")!=-1){
				UIToastr.showWarning(LANG.UI_SETTINGS_UPDATE_ADD_QUEUE_ERROR, LANG.UI_SETTINGS_UPDATE_ADD_QUEUE_ERROR_TIPS);
				return false;
			}
		});

        // 当有文件被添加进队列的时候
        uploader.on( 'fileQueued', function(file) {
            $list.append( '<div id="' + file.id + '" class="item">' +
                '<h4 class="info">' + file.name + '<a id="delete'+file.id+'" style="margin-left:20px;font-size:14px;">'+LANG.UI_PUBLIC_DELETE+'</a></h4>' +
                '<p class="state">'+LANG.UI_SETTINGS_UPDATE_WAIT_UPLOAD+'</p>' +
            '</div>' );
            $('#delete'+file.id).on('click',function(){
            	$('#'+file.id).remove();
            	uploader.removeFile(uploader.getFile(file.id), true); //把对应文件从队列中删除
            });
            
		});
        $('#ctlBtn').on('click',function(){
        	uploader.upload();
        });
        $('#pause').on('click',function(){
        	uploader.stop(true);
        	$('.pauseDiv').hide();
        	$('.startDiv').show();
        });
        
        $('#continue').on('click',function(){
        	uploader.upload();
        	$('.pauseDiv').show();
        	$('.startDiv').hide();
        });
        
        $('#retry').on('click',function(){
        	uploader.retry();
        });
        
        // 文件上传过程中创建进度条实时显示。
        uploader.on( 'uploadProgress', function( file, percentage ) {
            var $li = $( '#'+file.id ),
                $percent = $li.find('.progress .progress-bar');

            // 避免重复创建
            if ( !$percent.length ) {
                $percent = $('<div class="progress progress-striped active">' +
                  '<div class="progress-bar" role="progressbar" style="width: 0%">' +
                  '</div>' +
                '</div>').appendTo( $li ).find('.progress-bar');
            }

            $li.find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOADING);
            $percent.css( 'width', percentage * 100 + '%' );
            $('.pauseDiv').show();
            $('.retryDiv').hide();
        });
        
        //文件上传成功回调
        uploader.on( 'uploadSuccess', function( file,res) {
        	$('.pauseDiv').hide();
          	$('.startDiv').hide();
          	$('.retryDiv').hide();
        	if(res.error){
        		$( '#'+file.id ).find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOAD_ERROR +"(" + res.error.message +")");
        		return;
        	}
        	
        	//把数据插入数据库
        	var name = file.name;
        	var fileSize = file.size;
        	var data = {
        		'name': name,
        		'size': fileSize
        	}
        	var data = JSON.stringify(data);
        	$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'updatePatchList',p:data}, function(d){
        		if(OPREL(d)){
        			$( '#'+file.id ).find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOAD_SUCCESS);
        			patchGrid.getRefresh(getUpdateParams());
        		}else{
        			$( '#'+file.id ).find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOAD_ERROR);
        		}
        	});
        	
        });
        
        //文件上传错误回调
        uploader.on( 'uploadError', function( file, res) {
            $( '#'+file.id ).find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOAD_ERROR +"(" + res.error.message +")");
            $('.pauseDiv').hide();
        	$('.startDiv').hide();
        	$('.retryDiv').show();
        	$( '#'+file.id ).find('.progress').remove();
        });
        
        //文件上传完回调
        uploader.on( 'uploadComplete', function( file ) {
            $( '#'+file.id ).find('.progress').fadeOut();
            $('.close').on('click', function () {
            	uploader.reset();
            	$('#thelist').empty();
            	$('#updateList').empty();
            	$('#progressDiv').hide();
            	$('#updateAlarm').hide();
    	    });
        });
        
        //当所有文件上传结束时关闭模态框
        uploader.on('uploadFinished', function(){
        	$('#uploadModal').modal('hide');
        	uploader.reset();
        });
        
	}
	
	var handleRecords = function () {
    	var dataTableOpt = {
			'columnDefs' : [{
                'orderable': false,
                'targets': [0, 1, 2, 3]
			}],
			"order": [
                [4, "desc"]
            ],
    	};
    	var initGrid = function(){
    		if(0 == $('#patchTable').size()){
        		clearTimeout(timerTask.StorageManagerTable);
        		return;
        	}
    		if(!patchGridFlag){
    			//加载显示页数分页条
    			patchGrid = new Datatable();
        		var data = {m:CONF.M.SYSTEM,f:'getPatches',p:getUpdateParams()};
        		patchGrid.setAjaxParam(data);
        		patchGrid.init({src: $("#patchTable"), showDetail:false, dataTable:dataTableOpt});
            	patchGridFlag = true;
    		}else{
    			var deleteModal = $('.modal-scrollable').length;
    			var bootboxConfirm = $('div.bootbox-confirm.in').length;
    			if(bootboxConfirm == 0 && deleteModal == 0){
    				patchGrid.getRefresh(getUpdateParams());
    			}
    		}
    	}
    	initGrid();
    	return;
	}
	
	
	var historyRecords = function () {
    	var dataTableOpt = {
			'columnDefs' : [{
                'orderable': false,
                'targets': [0, 1, 2, 3, 4, 7]
			}],
			"order": [
                [5, "desc"]
            ],
    	};
    	var initGrid = function(){
    		if(0 == $('#patchHistoryTable').size()){
        		clearTimeout(timerTask.StorageManagerTable);
        		return;
        	}
    		if(!historyGridFlag){
    			//加载显示页数分页条
    			historyGrid = new Datatable();
        		var data = {m:CONF.M.SYSTEM,f:'getPatchHistory',p:{start:0, length:10, search: {}}};
        		historyGrid.setAjaxParam(data);
        		historyGrid.init({src: $("#patchHistoryTable"), showDetail:false, dataTable:dataTableOpt,onDataLoad:initRow});
        		historyGridFlag = true;
    		}else{
    			var deleteModal = $('.modal-scrollable').length;
    			var bootboxConfirm = $('div.bootbox-confirm.in').length;
    			if(bootboxConfirm == 0 && deleteModal == 0){
    				historyGrid.getRefresh(getHistoryParams());
    			}
    		}
    	}
    	initGrid();
    	return;
	}
	
	var initRow =function(){
		var data = historyGrid.getDataTable().data();
		var levelDiv = $('tbody > tr').find('td:eq(6)');
		for(var i=0; i<levelDiv.length; i++){
			setLevel(levelDiv[i], data[i]);
		}
		for(var j=0;j<data.length;j++){
			var path = data[j][10];
			$('#download'+data[j][9]).on('click',function(){
				var data = historyGrid.getDataTable().data();
				var params ={};
				params.path = this.attributes[1].nodeValue;
				var p = JSON.stringify(params);
 				$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'downloadHistory',p:p}, function(d){
 					var jsonFlag = false;
 					try {
 				        $.parseJSON(d);
 				       jsonFlag = true;
 				    } catch (e) {
 				    	jsonFlag = false;
 				    }
					if(jsonFlag && !OPREL(d)){
						return ;
					}else{
						window.location.href = CONF.AJAXPATH + '?m=' + CONF.M.SYSTEM + '&f=downloadHistory&p='+p;
					}
					
				});
			})
		}
	}
	
	var setLevel = function(div, data){
		if(!data) return;
		var labelClass = getLevelClass(data[8]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[6] + '</span>';
		$(div).html(content);
	}
	//得到日志的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
		if(level == 0){
			levelClass = "label-success";
		}else{
			levelClass = "label-danger";
		}
		return levelClass;
	}
	
	//得到升级包列表参数
	var getUpdateParams = function(){
		//页码
		var page = $('#patchTable .pagination-panel-input').val();
		//每页条数
		var size = $('select[name=patchTable_length]').val();
		//搜索项
		var name = $.trim($('.searchinput').val());
		var p ={start:0, length:10, search:{name:''}};
		if(undefined != page){
			p.start = parseInt(page) - 1;
			p.length = 10;
			p.search = {name: name};
		}
		return p;
	}
	
	//得到升级历史日志参数
	var getHistoryParams = function(){
		//页码
		var page = $('#patchHistoryTable .pagination-panel-input').val();
		//每页条数
		var size = $('select[name=patchHistoryTable_length]').val();
		//搜索项
		var name = $.trim($('.searchinput').val());
		var p ={start:0, length:10, search:{name:''}};
		if(undefined != page){
			p.start = parseInt(page) - 1;
			p.length = 10;
			p.search = {name: name};
		}
		return p;
	}
	
	//初始化所有备份节点
    var initNodeSelect = function(){
    	$('#upgradeSubmit').removeAttr("disabled");
    	var select = patchGrid.getSelectedRows();
    	var params = {
    		'uuid': select[0]
    	}
    	var p = JSON.stringify(params);
    	$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'getUpdateNodeList',p:p}, function(d){
			var data = JSON.parse(d);
			var nodeSelect = $('#nodeSelect');
			nodeSelect.empty();
			var option1 = $("<option>").text(LANG.UI_SETTINGS_UPDATE_MASTER_NODE +data[0].node_name).val(data[0].node_uuid);
			nodeSelect.append(option1);
			if(data.length > 1){
				var option2 = $("<option>").text(LANG.UI_SETTINGS_UPDATE_CHILD_NODE).val(2);
				nodeSelect.append(option2);
				$('#childNodes').empty();
				var list ="";
				for(var i=1;i<data.length;i++){
					if(data[i].update_flag){
						list = '<li><label><input disabled="true" data-checkbox="icheckbox_square-blue"  class="icheck" style="margin-right:4px;" type="checkbox" id="icheck'+ data[i].node_uuid +'" value="'+ data[i].node_uuid +'">'+data[i].node_name+'<span style="color:#34D48D;">'+LANG.UI_TENANT_UPGRADED+'</span></label</li>';
					}else{
						list = '<li><label><input data-checkbox="icheckbox_square-blue"  class="icheck" style="margin-right:4px;" type="checkbox" value="'+ data[i].node_uuid +'">'+data[i].node_name+'</label></li>';
					}
					$('#childNodes').append(list);
				}
			}
			$('#childNodes .icheck').iCheck({ 
				  labelHover : true, 
				  cursor : true, 
				  checkboxClass : 'icheckbox_square-blue', 
				  radioClass : 'iradio_square-blue', 
				  increaseArea : '20%' 
				}); 
			if(!initFlag){
				$('#nodeSelect').on('change', function(){
					if(this.value == 2){
						var select = patchGrid.getSelectedRows();
						var params = {};
						params.nodeuuid = data[0].node_uuid;
						params.patchuuid = select[0];
						var p = JSON.stringify(params);
						$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'checkMasterUpdate',p:p}, function(d){
							if(d && !OPREL(d)){
								$('#upgradeSubmit').prop('disabled',"true");
							}
						});
						$('#childDiv').show();
					}else{
						$('#childDiv').hide();
						$('#upgradeSubmit').removeAttr("disabled");
					}
				});
				initFlag = true;
			}
			
		});
    }
	
    return {
        init: function () {
        	//添加事件
        	addListeners();
        	initUploader();
        	handleRecords();
        	historyRecords();
//        	initNodeSelect();
        }
    };

}();

//恢复出厂设置
var Settings_Factory_Reset = function () {
	
	var doFactory = function(){
		bootbox.confirm({
            title: LANG.UI_SETTING_FACTORY_TITLE,
            message: LANG.UI_SETTING_FACTORY_TITLE_INFO,
            callback: debounce(function(r) {
                if(!r) return;
                Metronic.blockUI({target: '#factoryform',animate: true});
            	$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'doFactoryReset',p:{}}, function(data){
            		Metronic.unblockUI('#factoryform');
            		if(OPREL(data)){
            		}
            	});
            }, 300)
        });
	}
	
	var addListeners = function(){
		$('#dofactory').on('click', doFactory);
	}
	
    return {
        init: function () {
        	//添加事件
        	addListeners();
        }
    };

}();

//API消息推送
var Settings_Message_Push = function () {
	
	var Message = function(){
		var data = {};
		data.pushflag = $('#pushCheck').get(0).checked;
		data.pushtype = parseInt($('#pushType').val());
		data.mode = parseInt($('#pushMode').val());
		data.protocol = parseInt($('#protocol').val());
		data.domain = $('input[name=pushDomain]').val();
		data.port = $('input[name=pushPort]').val();
		data.username = $('input[name=pushName]').val();
		data.password = btoa($('input[name=pushPassword]').val());
		var jsonData = JSON.stringify(data); 
		Metronic.blockUI({target: '#messagepushdiv',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'messagePush',p:jsonData}, function(data){
    		Metronic.unblockUI('#messagepushdiv');
    		if(OPREL(data)){
    		}
    	});
	}
	
	var addListeners = function(){
		var messagepushform = $('#messagepushform');
		messagepushform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	pushDomain: {
                    required: true,
                    domain: true,
                },
                pushPort: {
                	required: true,
                	port: true,
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
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
                
            }
            
        });
        
        //IP域名验证格式
        $.validator.addMethod("domain", function(value, element) {
        	var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
            var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
        	return domain || ipv4;
        	//        	return this.optional(element) || /^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /([a-z0-9][a-z0-9\-]*?\.(?:com|cn|net|org|gov|info|la|cc|co)(?:\.(?:cn|jp))?)$/i.test(value);
        }, LANG.UI_SETTING_INPUT_DOMAIN);
      //端口号验证
        $.validator.addMethod("port", function(value, element) {
        	return this.optional(element) ||  /^([0-9]|[1-9]\d|[1-9]\d{2}|[1-9]\d{3}|[1-5]\d{4}|6[0-4]\d{3}|65[0-4]\d{2}|655[0-2]\d|6553[0-5])$/i.test(value);
        }, LANG.UI_SETTING_INPUT_PORT);
        
		$("#messageSubmit").click(function(){
			if (messagepushform.validate().form()) {
				Message();
			}
		});
		 
		$("#messageCancel").click(function(){
	        LOCATION('./content/platform/settings/setting_manager.php?tab=6', 'setting_manager');
	    });
		
		$('#protocol').on("change", function(){
			if("1" == this.value){
				$('input[name=pushPort]').val(61613);
			}else if("2" == this.value){
				$('input[name=pushPort]').val(61616);
			}
		});
		
		if(2 != CONF.SOFTWARE){
			$('#messagepush').hide();
			$('#smsDiv').hide();
		}
	}

	//加载历史消息推送数据
	var initOldInfo = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:'messagePushOldInfo',p:{}}, function(data){
			data = JSON.parse(data);
			$('#pushCheck').bootstrapSwitch('state', data.pushflag);
			if(!data.pushtype){
				$('#pushType').val('1');
			}else{
				$('#pushType').val(data.pushtype);
			}
			if(!data.mode){
				$('#pushMode').val('1');
			}else{
				$('#pushMode').val(data.mode);
			}
			if(!data.protocol){
				$('#protocol').val('1');
			}else{
				$('#protocol').val(data.protocol);
			}
			$('input[name=pushDomain]').val(data.domain);
			if(data.port){
				$('input[name=pushPort]').val(data.port);
			}
			$('input[name=pushName]').val(data.username);
			$('input[name=pushPassword]').val(data.password);
    	});
	}
	
	
    return {
        init: function () {
        	//添加事件
        	addListeners();
        	initOldInfo();
        }
    };

}();

//初始化可视化配置
var Settings_Visual_Config = function(){
	
	var addListeners = function(){
		
		var visualform = $('#visualform');
		visualform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	pushTitle: {
                    required: true,
                    title: true,
                },
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
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
                
            }
            
        });
        
        //IP域名验证格式
        $.validator.addMethod("title", function(value, element) {
        	var title = this.optional( element ) || /^(([^\^\.<>%&',;=?$"':#@!~\]\[{}\\/`\|])*)$/i.test( value );
        	return title;
        }, LANG.UI_VISUAL_SETTING_TITLE);
        	
    	$("#visualsubmit").click(function(){
			if (visualform.validate().form()) {
				submit();
			}
        });
		$("#visualcancel").click(function(){
        	LOCATION('./content/platform/settings/setting_manager.php?tab=7', 'setting_manager');
        });
        
	}
	
	var submit = function(){
		Metronic.blockUI({target: '#visualform',animate: true});
		var data = {};
		data.title = $('#visualTitle').val();
		var p = JSON.stringify(data);
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'setVisualInfo', p:p}, function(d){
			Metronic.unblockUI('#visualform');
	    	if(OPREL(d)){
	    	}
		});
	}
	
	var initOldInfo = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f:'getDefaultVisualInfo', p:{}}, function(d){
			var data  = JSON.parse(d);
			if(data.length == 0) return;
			//可视化配置初始化
			$('#visualTitle').val(data.config.title);
			
		});
	}
	
	 return {
	        init: function () {
	        	//添加事件
	        	addListeners();
	        	initOldInfo();
	        }
	    };
}();


var Settings_System_Tool = function(){
	var grid;
	var gridInitFlag = false;
	var _SERVERNODE = "";
	
	var addListeners = function(){
		$('#toolSelect').selectpicker({
            iconBase: 'fa',
            tickIcon: 'fa-check'
        });
		
		
		$('#servicenodeSelect').on('change', function(){
			grid.getRefresh(getServiceParams());
		});
		
		$('#service_searchbtn').on('click',function(){
			var name = $.trim($('.service_searchinput').val());
			//节点uuid
			var nodeuuid = $('#servicenodeSelect').val();
			var p = {nodeuuid:nodeuuid, start:0, length:10, search:{name:name}};
			var data = {m:CONF.M.SYSTEM,f:'getServiceList',p:p};
    		grid.setAjaxParam(data);
			grid.getRefresh(p, undefined, true);
			clearTimeout(timerTask.System_Service);
		});
		
		$('.service_searchinput').keypress(function (e) {
            if (e.which == 13) {
            	var name = $.trim($('.service_searchinput').val());
            	//节点uuid
    			var nodeuuid = $('#servicenodeSelect').val();
    			var p = {nodeuuid:nodeuuid, start:0, length:10, search:{name:name}};
            	grid.getRefresh(p, undefined, true);
            	clearTimeout(timerTask.System_Service);
            }
        });
		
		$('#toolSelect').on('change', toolHandler);
	}
	
	var toolHandler = function(){
		if(this.value == "telnet"){
			$('#pingContent').hide();
			$('#telnetContent').show();
			$('.portDiv').show();
			if(_SERVERNODE !=""){
				$('#toolnodeSelect').val(_SERVERNODE);
			}
			$('#toolnodeSelect').prop('disabled', true);
			$('#toolnodeSelect').selectpicker('refresh');
		}else{
			$('#telnetContent').hide();
			$('#pingContent').show();
			$('.portDiv').hide();
			$('#toolnodeSelect').prop('disabled', false);
			$('#toolnodeSelect').selectpicker('refresh');
		}
	}
	
	var testHandler = function(){
		var data ={};
		data.ip = $('#testIP').val();
		data.nodeuuid = $('#toolnodeSelect').val();
		data.tooltype = $('#toolSelect').val();
		data.port = $('#testport').val();
		var params = JSON.stringify(data);
		Metronic.blockUI({target:'#networkTool', animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM, f: 'testConnectTool', p:params}, function(d){
			Metronic.unblockUI('#networkTool');
			if(OPREL(d)){
				
			}
		});
		
	}
	
	
	var initSelectNode = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getAddStorageNodeSelect',p:{}}, function(d){
			var data = JSON.parse(d);
			var servicenodeSelect = $('#servicenodeSelect');
			var toolnodeSelect = $('#toolnodeSelect');
			if(0 == data.length){
				servicenodeSelect.prop('disabled', true);
				toolnodeSelect.prop('disabled', true);
				return;
			}
			servicenodeSelect.empty();
			toolnodeSelect.empty();
			for(var i=0; i<data.length; i++){
				var option1 = $("<option>").text(data[i].text).val(data[i].uuid);
				var option2 = $("<option>").text(data[i].text).val(data[i].uuid);
				servicenodeSelect.append(option1);
				toolnodeSelect.append(option2);
			}
			_SERVERNODE = data[0].uuid;
			$('.nodeselect').selectpicker({
	            iconBase: 'fa',
	            tickIcon: 'fa-check'
	        });
    	});
	}
	
	var addOpButtonListener = function(){
		$('.start').unbind().on('click', startService); //启动服务
		$('.stop').unbind().on('click', stopService);   //停止服务
		$('.restart').unbind().on('click', restartService); //重启服务
	}
	
	var  startService = function(){
		opJob(this, 'startService');
	}
	
	
	var  stopService = function(){
		opJob(this, 'stopService');
	}
	
	var  restartService = function(){
		opJob(this, 'resetService');
	}
	
	
	var opJob = function(button, funName){
		var data = grid.getDataTable().data();
		var row = $(button).parents('tr').get(0)._DT_RowIndex;
		var params = {};
		params.name = data[row][1];
		params.nodeuuid = $('#servicenodeSelect').val();
		params = JSON.stringify(params);
		Metronic.blockUI({target: '#serviceManage',animate: true});
    	$.post(CONF.AJAXPATH, {m:CONF.M.SYSTEM,f:funName,p:params}, function(data){
    		Metronic.unblockUI('#serviceManage');
    		if(OPREL(data)){
    			grid.getRefresh(getServiceParams());
    		}
    	});
	}
	
	var addOpButton = function(){
		var data = grid.getDataTable().data();
		if(0 == data.length) return;
		var opDiv = $('#serviceTable tbody > tr').find('td:eq(3)');
		var levelDiv = $('#serviceTable tbody > tr').find('td:eq(2)');
		//有几列 添加 几次
		for(var i=0; i<opDiv.length; i++){
			opButton(opDiv[i], data[i][3], data[i][1]);
			setLevel(levelDiv[i],data[i]);
		}
		
		addOpButtonListener();
		for(var i=0; i<opDiv.length; i++){
			var status = data[i][4];
			if(status == 0){
				addForbidButton(escapeJquery(data[i][1]), 'start');
			}else{
				addForbidButton(escapeJquery(data[i][1]), 'stop');
			}
		}
	}
	
	var setLevel = function(div, data){
		var labelClass = getLevelClass(data[4]);
		var content = '<span class="label label-sm ' + labelClass + '">' + data[2] + '</span>';
		$(div).html(content);
	}
	
	//得到状态的显示类型
	var getLevelClass = function(level){
		var levelClass = '';
		if(level != 0){
			levelClass = "label-default";
		}else{
			levelClass = "label-success";
		}
		return levelClass;
	}
	
	//添加禁止点击的按钮样式
	var addForbidButton = function(servicename, option){
		$('#' + servicename + ' .' +  option).unbind();
		$('#' + servicename + ' .' +  option + ' a').css("opacity",".4");
		$('#' + servicename + ' .' +  option + ' a').css("cursor","default");
		$('#' + servicename).on("click", "." +  option + " a", function(e) {
	        e.stopPropagation();
	    });
	}
	
	var escapeJquery = function(srcString){  
        // 转义之后的结果  
        var escapseResult = srcString.toString();  
        // javascript正则表达式中的特殊字符  
        var jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",  
                "]", "|", "{", "}"];  
        // jquery中的特殊字符,不是正则表达式中的特殊字符  
        var jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",  
                ":", ";", "<", ">", ",", "/"];  
        for (var i = 0; i < jsSpecialChars.length; i++) {  
            escapseResult = escapseResult.replace(new RegExp("\\"  
                                    + jsSpecialChars[i], "g"), "\\"  
                            + jsSpecialChars[i]);  
        }  
        for (var i = 0; i < jquerySpecialChars.length; i++) {  
            escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],  
                            "g"), "\\" + jquerySpecialChars[i]);  
        }  
        return escapseResult;  
    } 
	
	//添加操作按钮
	var opButton = function(div, opCode, servicename){
		var button = '<div class="btn-group positionabs">';
		button += '<button type="button" class="btn btn-success btn-sm   dropdown-toggle" data-toggle="dropdown" ' + 
				'data-hover="dropdown" data-delay="1000" data-close-others="true">' + 
				'<i class="glyphicon glyphicon-hand-up"></i> ' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
				'</button>' + 
				'<ul class="dropdown-menu min-width100" role="menu" id="'+ servicename +'">';
		
		$.each(opCode, function(i, d){
			switch(d){
				case 1:
					button += '<li class="start"><a href="javascript:;" ><i class="glyphicon glyphicon-play"></i> ' + LANG.UI_VCENTER_VM_START + '</a></li>';
					break;
				case 2:
					button += '<li class="stop"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
					break;
				case 3:
					button += '<li class="restart"><a href="javascript:;"><i class="viconfont vicon-ge_refresh"></i> ' + LANG.UI_SETTINGS_SERVICE_RESTART + '</a></li>';
					break;
			}
		});
		button += '</ul></div>';
		$(div).html(button);
		
	}
	
	var initServiceTable = function(){
		var updateInterval = 30000;
    	var dataTableOpt = {
    			'columnDefs' : [{
	                'orderable': false,
	                'targets': [0, 1, 2, 3]
    			}],
    			"order": [
                    [2, "desc"]
                ]
    	};
    	var initGrid = function(){
    		var nodeuuid = $('#servicenodeSelect').val();
    		if(0 == $('#serviceManage').size()){
        		clearTimeout(timerTask.System_Service);
        		return;
        	}
    		if(!gridInitFlag){
    			//加载显示页数分页条
    			grid = new Datatable();
        		var data = {m:CONF.M.SYSTEM,f:'getServiceList',p:{nodeuuid: nodeuuid}};
        		grid.setAjaxParam(data);
            	grid.init({src: $("#serviceTable"), showDetail:false, dataTable:dataTableOpt, onDataLoad:addOpButton});
            	gridInitFlag = true;
            	
    		}else{
    			var expandDiv = $('div.btn-group.open').length;
    			var bootboxConfirm = $('div.bootbox-confirm.in').length;
    			if(expandDiv == 0 && bootboxConfirm == 0){
    				grid.getRefresh({nodeuuid: nodeuuid});
    			}
    		}
    		timerTask.System_Service = setTimeout(initGrid, updateInterval);
    	}
    	initGrid();
    	return
	}
	
	//得到参数
	var getServiceParams = function(){
		//页码
		var page = $('#serviceTable .pagination-panel-input').val();
		//每页条数
		var size = $('select[name=serviceTable_length]').val();
		//节点uuid
		var nodeuuid = $('#servicenodeSelect').val();
		//搜索项
		var name = $('.service_searchinput').val();
		var p = {nodeuuid: nodeuuid, start:0, length:10, search:{name: name}};
		if(undefined != page){
			p.start = parseInt(page) - 1;
			p.length = 10;
			p.search = {name: name};
			p.nodeuuid = nodeuuid;
		}
		return p;
	}

	var handleValidation = function() {
        var toolform = $('#toolform');

        toolform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	toolip: {
            		required: true,
            		ipv4: true
            	},
            	toolport: {
                    required: false,
                }
            },

            invalidHandler: function (event, validator) { //display error alert on form submit              
            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");  
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
            },

            highlight: function (element) { // hightlight error inputs
                $(element)
                    .closest('.form-test').removeClass("has-success").addClass('has-error'); // set error class to the control group   
            },

            unhighlight: function (element) { // revert the change done by hightlight
                
            },

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form-test').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            },

            submitHandler: function (form) {
                
            }
            
        });
        
        //IP验证格式
        $.validator.addMethod("ipv4", function(value, element) {
        	var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
    		var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
    		//http|https
    		var domainHTTP = this.optional( element ) || /^(http|https):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
    		var ipv4HTTP = this.optional(element) || /^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
    		return domain || ipv4 || domainHTTP || ipv4HTTP;
        }, LANG.UI_SETTING_INPUT_IP);
        
        $("#testTool").click(function(){
        	if (toolform.validate().form()) {
        		testHandler();
            }
        });
        
        
	};
	
	return {
		init: function () {
			initSelectNode();
			addListeners();
			initServiceTable();
			handleValidation();
		}
	}
	
}();


jQuery(document).ready(function() {    
	Settings_Set_IP.init();		
	Settings_Set_Time.init();
//	Settings_System_Name.init();
//	Settings_DataBase_Manager.init();
	Settings_System_Notice.init();
	Settings_DNS.init();
	Settings_Poweroff.init();
	Settings_System_Upgrade.init();
//	Settings_Factory_Reset.init();
	Settings_Message_Push.init();
	Settings_Visual_Config.init();
	Settings_System_Tool.init();
});