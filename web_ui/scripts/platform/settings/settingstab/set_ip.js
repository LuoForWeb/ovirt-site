var Settings_Set_IP = function () {
	var _HOST = location.host;
	var _PRIMARY_NODE = '';	//主节点
	var node_arr = []; // 再集群中运行的节点
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
					ipaddrAvailable: true,
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

		$.validator.addMethod(
			"ipaddrAvailable",
			function(value, element, param) {
				var nodeuuid = $('select[name=ipnodelist]').val();
				var cardName = $('select[name=networkcard] option:selected').text();
				var data = JSON.stringify({ip:value, node_uuid:nodeuuid,network_name:cardName});
				var result = false;
				$.ajax({
					type: "post",
					url: CONF.AJAXPATH,
					async:false,
					data:{m:CONF.M.SYSTEM,f:'ipaddrAvailable',p:data},
					success: function(data){
						result = JSON.parse(data);
					}
				});
				return result;
			},
			LANG.UI_SETTING_INPUT_IP_EXISTS
		);

		//IP验证格式
		$.validator.addMethod("ipv4", function(value, element) {
			if (ipV4V6(value)) {
				if (ipV4(value)) {
					$('#settings_prefix').hide();
					$('#settings_netmask1').show();
				} else {
					$('#settings_prefix').show();
					$('#settings_netmask1').hide();
				}
			} else {
				$('#settings_prefix').hide();
				$('#settings_netmask1').show();
			}

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
	var ipV4 = function (value) {
		return /^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value);
	}
	//提交修改检测
	var submitCheck = function(){
		//如果修改是主节点的IP和访问IP不一样,进行提示
		if(_PRIMARY_NODE == $('select[name=ipnodelist]').val() && $('input[name=ipaddr]').val() != _HOST){
			bootbox.confirm({
				title: LANG.UI_SETTING_MODIFY_IP,
				message: LANG.UI_SETTING_MODIFY_IP_TIP,
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
		if ($.inArray($('select[name=ipnodelist]').val(), node_arr) !== -1) {
			// 处于集群中，提示不能修改ip
			UIToastr.showWarning(LANG.UI_PUBLIC_TIPS, LANG.UI_SET_TIME_TIPS)
			return false;
		}
		Metronic.blockUI({target: '#setipform',animate: true});
		var data = {};
		data.node_uuid = $('select[name=ipnodelist]').val();
		data.NAME = $('select[name=networkcard] option:selected').text();
		data.IPADDR = $('input[name=ipaddr]').val();
		data.NETMASK = $('input[name=netmask]').val();
		data.GATEWAY = $('input[name=gateway]').val();
		data.DNS = $('input[name=dns]').val();
		data.PREFIX = $('input[name=prefix]').val();
		pAjaxRequest(data, '/api/v1/system/network/info', 'POST', function (res) {
			Metronic.unblockUI('#setipform');
			if (operateResponseList(res, LANG.UI_DRILLS_IP_ADDRESS)){

			}
		})
	}

	//初始化
	var handleInit = function(){
		//初始化备份节点
		var initBackupNodeList = function(){
			// 先获取处于集群中的节点
			pAjaxRequest({}, '/api/v1/cluster/config', 'GET', function (d) {
				node_arr = [];
				if (d.data.cluster_status == 2) {
					// 运行中，所在节点不能修改ip
					var nodes = d.data.node_config;
					for(var j in nodes) {
						node_arr.push(nodes[j].node_uuid);
					}
				}
			})
			pAjaxRequest({}, '/api/v1/nodes/select', 'GET', function (d) {
				var data = d.data;
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
			var data = {node_uuid:nodeuuid, card_name:cardName, card_hwaddr:cardHwaddr};
			Metronic.blockUI({target: '#setipform',animate: true});
			pAjaxRequest(data, '/api/v1/system/network/info', 'GET', function (res) {
				Metronic.unblockUI('#setipform');
				if (res.code == 0) {
					var data = res.data
					$('input[name=ipaddr]').val(data.IPADDR);
					$('input[name=netmask]').val(data.NETMASK);
					$('input[name=gateway]').val(data.GATEWAY);
					$('input[name=dns]').val(data.DNS);
					if (ipV4(data.IPADDR)) {
						// 是ipv4 那么就不显示 ipv6的前缀输入框和子网掩码
						$('#settings_prefix').hide();
						$('#settings_netmask1').show();
					} else {
						if (!data.IPADDR) {
							// 如果为空 那么就默认显示 ipv4
							$('#settings_prefix').hide();
							$('#settings_netmask1').show();
						} else {
							$('#settings_prefix').show();
							$('#settings_netmask1').hide();
						}
					}
					$('input[name=prefix]').val(data.PREFIX);
					_HOST = data.IPADDR;
				}
			})
		}

		//初始化网卡
		var initNetworkCard = function(){
			var nodeuuid = $('select[name=ipnodelist]').val();
			var data = {node_uuid:nodeuuid, setip_flag: true};
			Metronic.blockUI({target: '#setipform',animate: true});
			pAjaxRequest(data, '/api/v1/system/network/list', 'GET', function (res) {
				Metronic.unblockUI('#setipform');
				var networkcard = $('select[name=networkcard]');
				networkcard.empty();
				if (res.code == 0) {
					var data = res.data
					for(var i=0; i<data.length; i++){
						// 去除下 vir 和 vnet的网卡
						if (data[i].name.startsWith('vir') || data[i].name.startsWith('vnet') ) {
							continue;
						}
						var option = $("<option>").text(data[i].name).val(data[i].hwaddr);
						networkcard.append(option);
					}
				}
				networkCardChange();
			})
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

jQuery(document).ready(function(){
	Settings_Set_IP.init();
});