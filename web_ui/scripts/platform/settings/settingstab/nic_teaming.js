var Settings_NIC_TEAMING = function () {
	var _HOST = location.host;
	var _PRIMARY_NODE = '';	//主节点
	var _IS_INIT = false; // 标记是否聚合过
	var handleValidation = function() {
        var setipform = $('#nictemingform');

        setipform.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: {
            	nicnodelist: {
            		required: true,
            	},
            	nictype:{
            		required: true,
            	},
            	nicnetworkcard: {
                    required: true,
                },
                nicipaddr: {
                    required: true,
                    nicipv4: true,
                },
                nicnetmask: {
					required: true,
                	nicvNetmask: true,
                },
                nicgateway: {
                    required: false,
                    nicvGateway: true,
                },
                nicdns: {
                    required: false,
                    nicvDns: true,
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
        $.validator.addMethod("nicipv4", function(value, element) {
			if (ipV4V6(value)) {
				if (ipV4(value)) {
					$('#settings_prefix_nic').hide();
					$('#settings_netmask1_nic').show();
				} else {
					$('#settings_prefix_nic').show();
					$('#settings_netmask1_nic').hide();
					$('input[name=nicnetmask]').val("255.255.255.0");
				}
			} else {
				$('#settings_prefix_nic').hide();
				$('#settings_netmask1_nic').show();
			}
        	return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_IP);
        
        //子网掩码
        $.validator.addMethod("nicvNetmask", function(value, element) {
        	return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_NETMASK);
        
        //网关
        $.validator.addMethod("nicvGateway", function(value, element) {
        	return this.optional(element) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_GATEWAY);
        
        //dns
        $.validator.addMethod("nicvDns", function(value, element) {
       
        	for(var i=1;i<value.length;i++){
        		if(value.indexOf(",")!=-1){
            		value = value.split(',');
            		return this.optional(element) ||  /^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$/ || ipV4V6(value[i]);
            	}
        	}
        	return this.optional(element) || /^(?=^.{3,255}$)[a-zA-Z0-9][-a-zA-Z0-9]{0,62}(\.[a-zA-Z0-9][-a-zA-Z0-9]{0,62})+$/i.test(value) || ipV4V6(value);
        }, LANG.UI_SETTING_INPUT_DNS);
        
        $("#nicsubmit").click(function(){
        	if (_IS_INIT) {
        		// 已经初始化过 那么就提示 先清除聚合
				return UIToastr.showInfo(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_NIC_TEAMING_CLEAR_NODE_EXISIS_INFO_TIPS);
			}

			var cardList = $('select[name=nicnetworkcard]').selectpicker('val');

			if(cardList.length <= 1){
				return UIToastr.showWarning(LANG.UI_NIC_TEAMING_QUANTITY_TIP,LANG.UI_NIC_TEAMING_TIP_TWO);
			}

        	if (setipform.validate().form()) {
        		submitCheck();
            }
        });
        
        $("#niccancel").click(function(){
        	LOCATION('./content/platform/settings/setting_manager.php?tab=0', 'setting_manager');
        });
        
        //初始化多选框
        $(".selectpicker").selectpicker({
        	noneSelectedText: LANG.UI_NIC_TEAMING_SELECT
		});
        
        $('#cleansubmit').click(cleanNicInfo);
        
	
	};

	var ipV4 = function (value) {
		return /^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value);
	}
	
	//清除网卡聚合信息
	var cleanNicInfo = function(){
		if (_IS_INIT === false) {
			// 没有初始化过 那么就提示 先聚合
			return UIToastr.showInfo(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_NIC_TEAMING_CLEAR_NODE_EXISIS_INFO_TIPS2);
		}
		bootbox.confirm({
            title: LANG.UI_NIC_TEAMING_CLEAR_NODE_INFO,
            message: LANG.UI_NIC_TEAMING_CLEAR_NODE_INFO_TIPS1,
            callback: debounce(function(r) {
                if(!r) return;
                var data = {};
                data.node_uuid = $('select[name=nicnodelist]').val();
                Metronic.blockUI({target: '#nictemingform',animate: true,cenrerY: true});
				pAjaxRequest(data, '/api/v1/system/network/nic', 'DELETE', function (res) {
					Metronic.unblockUI('#nictemingform');
					if (res.code == 0) {
						res['data'] += "," + LANG.UI_NIC_TEAMING_CLEAR_NODE_INFO_TIPS2;
					}
					if (operateResponseList(res, LANG.UI_NIC_TEAMING_MANAGER)){
						setTimeout(function(){window.location.reload();}, 5000);
					}
				})
            }, 300)
        });
	}
	
	//提交修改检测
	var submitCheck = function(){
		
		bootbox.confirm({
            title: LANG.UI_NIC_TEAMING_ADD,
            message: LANG.UI_NIC_TEAMING_CONFIRM,
            callback: debounce(function(r) {
                if(!r) return;
                submit();
            }, 300)
        });
		
	}
	
	//提交表单
	var submit = function(){
		var data = {};
		data.node_uuid = $('select[name=nicnodelist]').val();
		data.nicType = $('select[name=nictype]').val();
		data.cardList = $('select[name=nicnetworkcard]').selectpicker('val');
		data.nicipaddr = $('input[name=nicipaddr]').val();
		data.nicnetmask = $('input[name=nicnetmask]').val();
		data.nicgateway = $('input[name=nicgateway]').val();
		data.nicdns = $('input[name=nicdns]').val();
		data.prefix = $('input[name=nicprefix]').val();
		data.ipv4flag = ipV4($('input[name=nicipaddr]').val());	//判断是ipv4还是ipv6

		Metronic.blockUI({target: '#nictemingform',animate: true,cenrerY: true});
		pAjaxRequest(data, '/api/v1/system/network/nic', 'POST', function (res) {
			Metronic.unblockUI('#nictemingform');
			if (operateResponseList(res, LANG.UI_NIC_TEAMING_MANAGER)){
				_IS_INIT = true;
			}
		})
	}
	
	
	//初始化
	var handleInit = function(){
		//初始化备份节点
		var initBackupNodeList = function(){
			$.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getAddStorageNodeSelect',p:{}}, function(d){
				var data = JSON.parse(d);
				var nodeselect = $('select[name=nicnodelist]');
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
		
		
		//初始化网卡
		var initNetworkCard = function(){
			var nodeuuid = $('select[name=nicnodelist]').val();
			var data = {
				node_uuid:nodeuuid,
				type: 'NIC_DEVICE_TYPE_COMMON,NIC_DEVICE_TYPE_BOND_SLAVE'
			};
			Metronic.blockUI({target: '#nictemingform',animate: true});
			pAjaxRequest(data, '/api/v1/virtual/netcard', 'GET', function (res) {
				Metronic.unblockUI('#nictemingform');
				var networkcard = $('select[name=nicnetworkcard]');
				networkcard.empty();
				if (res.code == 0) {
					var data = res.data.rows;
					for(var i=0; i<data.length; i++){
						// 去除下 vir 和 vnet的网卡
						if (data[i].name.startsWith('vir') || data[i].name.startsWith('vnet') ) {
							continue;
						}
						var option = $("<option>").text(data[i].name).val(data[i].value);
						networkcard.append(option);
					}
				}
				networkcard.selectpicker('refresh');
				initNicTeamOldInfo();
			})
		}
		
		//初始化旧的信息
		var initNicTeamOldInfo = function(){
			_IS_INIT = false
			var nodeuuid = $('select[name=nicnodelist]').val();
			Metronic.blockUI({target: '#nictemingform',animate: true});
			pAjaxRequest({node_uuid: nodeuuid}, '/api/v1/system/network/nic', 'GET', function (res) {
				Metronic.unblockUI('#nictemingform');
				if (res.code == 0) {
					var data = res.data;
					if(data == "" || data.length == 0 ){
						$('select[name=nictype]').val(0);
						$('input[name=nicipaddr]').val("");
						$('input[name=nicnetmask]').val("");
						$('input[name=nicgateway]').val("");
						$('input[name=nicdns]').val("");
						return;
					}
					_IS_INIT = true
					$('select[name=nictype]').val(data.nictype);
					$('input[name=nicipaddr]').val(data.ipaddr);
					$('input[name=nicnetmask]').val(data.netmask);
					$('input[name=nicgateway]').val(data.gateway);
					$('input[name=nicdns]').val(data.dns);

					if (ipV4(data.ipaddr)) {
						// 是ipv4 那么就不显示 ipv6的前缀输入框和子网掩码
						$('#settings_prefix_nic').hide();
						$('#settings_netmask1_nic').show();
					} else {
						if (!data.ipaddr) {
							// 如果为空 那么就默认显示 ipv4
							$('#settings_prefix_nic').hide();
							$('#settings_netmask1_nic').show();
						} else {
							$('#settings_prefix_nic').show();
							$('#settings_netmask1_nic').hide();
						}
					}
					$('input[name=nicprefix]').val(data.prefix);

					$('select[name=nicnetworkcard]').selectpicker('val', data.cardList);
					$('select[name=nicnetworkcard]').selectpicker('refresh');
				}
			})
		}
		
		initBackupNodeList();
		
		 //切换节点刷新网卡列表
        $('select[name=nicnodelist]').on('change', function(){
        	initNetworkCard();
        });
	}
	
    return {
        init: function () {
        	handleInit();
        	handleValidation();
        }
    };

}();

jQuery(document).ready(function(){
	Settings_NIC_TEAMING.init();
});