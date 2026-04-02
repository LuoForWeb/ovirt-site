// 初始化定义下所有的选择
var firewallcheck,sshcheck,nfscheck,rpcbindcheck,port8080check,port3306check,port5000check,port6080check,apicheck;
var Settings_OS_SAFE = function () {
	var handlerInit = function(){
		$("#ossubmit").click(function(){
			submit();
		});

		$("#oscancel").click(function(){
			LOCATION('./content/platform/settings/setting_manager.php', 'setting_manager');
		});

		initOldConfig();
	}

	//初始化配置
	var initOldConfig = function(){
		pAjaxRequest({}, '/api/v1/system/safe/os', 'GET', function (res) {
			var data = res.data
			firewallcheck = data.firewall_flag;
			sshcheck = data.ssh_flag;
			nfscheck = data.nfs_flag;
			rpcbindcheck = data.rpcbind_flag;
			port8080check = data.port8080_flag;
			port3306check = data.port3306_flag;
			port5000check = data.port5000_flag;
			port6080check = data.port6080_flag;
			apicheck = data.api_flag;
			$('#firewallcheck').bootstrapSwitch('state', firewallcheck);
			$('#sshcheck').bootstrapSwitch('state', sshcheck);
			$('#nfscheck').bootstrapSwitch('state', nfscheck);
			$('#rpcbindcheck').bootstrapSwitch('state', rpcbindcheck);
			$('#port8080check').bootstrapSwitch('state', port8080check);
			$('#port3306check').bootstrapSwitch('state', port3306check);
			$('#port5000check').bootstrapSwitch('state', port5000check);
			$('#port6080check').bootstrapSwitch('state', port6080check);
			$('#apicheck').bootstrapSwitch('state', apicheck);
			$('#tempMode').val(data.temp);

			modeSelect();
		})

		$('#tempMode').on('change', modeSelect);
	}

	//安全模板方式选择
	var modeSelect = function(){
		$('#firewallcheck').bootstrapSwitch("disabled", false);
		$('#sshcheck').bootstrapSwitch("disabled", false);
		$('#nfscheck').bootstrapSwitch("disabled", false);
		$('#rpcbindcheck').bootstrapSwitch("disabled", false);
		$('#port8080check').bootstrapSwitch("disabled", false);
		$('#port3306check').bootstrapSwitch("disabled", false);
		$('#port5000check').bootstrapSwitch("disabled", false);
		$('#port6080check').bootstrapSwitch("disabled", false);
		$('#apicheck').bootstrapSwitch("disabled", false);
		if($('#tempMode').val() == 1){
			// 默认模板都是开
			$('#firewallcheck').bootstrapSwitch('state', true);
			$('#sshcheck').bootstrapSwitch('state', true);
			$('#nfscheck').bootstrapSwitch('state', true);
			$('#rpcbindcheck').bootstrapSwitch('state', true);
			$('#port8080check').bootstrapSwitch('state', true);
			$('#port3306check').bootstrapSwitch('state', true);
			$('#port5000check').bootstrapSwitch('state', true);
			$('#port6080check').bootstrapSwitch('state', true);
			$('#apicheck').bootstrapSwitch('state', true);

			$('#firewallcheck').bootstrapSwitch("disabled", true);
			$('#sshcheck').bootstrapSwitch("disabled", true);
			$('#nfscheck').bootstrapSwitch("disabled", true);
			$('#rpcbindcheck').bootstrapSwitch("disabled", true);
			$('#port8080check').bootstrapSwitch("disabled", true);
			$('#port3306check').bootstrapSwitch("disabled", true);
			$('#port5000check').bootstrapSwitch("disabled", true);
			$('#port6080check').bootstrapSwitch("disabled", true);
			$('#apicheck').bootstrapSwitch("disabled", true);
		} else if ($('#tempMode').val() == 2) {
			// 安全模板，除了防火墙开，api开，其它都是关
			$('#firewallcheck').bootstrapSwitch('state', true);
			$('#sshcheck').bootstrapSwitch('state', false);
			$('#nfscheck').bootstrapSwitch('state', false);
			$('#rpcbindcheck').bootstrapSwitch('state', false);
			$('#port8080check').bootstrapSwitch('state', false);
			$('#port3306check').bootstrapSwitch('state', false);
			$('#port5000check').bootstrapSwitch('state', false);
			$('#port6080check').bootstrapSwitch('state', false);
			$('#apicheck').bootstrapSwitch('state', true);

			$('#firewallcheck').bootstrapSwitch("disabled", true);
			$('#sshcheck').bootstrapSwitch("disabled", true);
			$('#nfscheck').bootstrapSwitch("disabled", true);
			$('#rpcbindcheck').bootstrapSwitch("disabled", true);
			$('#port8080check').bootstrapSwitch("disabled", true);
			$('#port3306check').bootstrapSwitch("disabled", true);
			$('#port5000check').bootstrapSwitch("disabled", true);
			$('#port6080check').bootstrapSwitch("disabled", true);
			$('#apicheck').bootstrapSwitch("disabled", true);
		}else{
			// 自定义 读取后台的信息
			$('#firewallcheck').bootstrapSwitch('state', firewallcheck);
			$('#sshcheck').bootstrapSwitch('state', sshcheck);
			$('#nfscheck').bootstrapSwitch('state', nfscheck);
			$('#rpcbindcheck').bootstrapSwitch('state', rpcbindcheck);
			$('#port8080check').bootstrapSwitch('state', port8080check);
			$('#port3306check').bootstrapSwitch('state', port3306check);
			$('#port5000check').bootstrapSwitch('state', port5000check);
			$('#port6080check').bootstrapSwitch('state', port6080check);
			$('#apicheck').bootstrapSwitch('state', apicheck);

			if (firewallcheck == false) {
				$('#port8080check').bootstrapSwitch("disabled", true);
				$('#port3306check').bootstrapSwitch("disabled", true);
				$('#port5000check').bootstrapSwitch("disabled", true);
				$('#port6080check').bootstrapSwitch("disabled", true);
			}

			if (nfscheck == true) {
				// 开nfs服务会自动启动rpcbind服务
				$('#rpcbindcheck').bootstrapSwitch('state', true);
				$('#rpcbindcheck').bootstrapSwitch("disabled", true);
			}

			if (rpcbindcheck == false) {
				// 关rpcbind服务会导致nfs服务异常，所以最好一起关；
				$('#nfscheck').bootstrapSwitch('state', false);
				$('#nfscheck').bootstrapSwitch("disabled", true);
			}
		}
	}

	$('#firewallcheck').bootstrapSwitch('onSwitchChange', function (e, data) {
		if(data){
			// 开启
			$('#port8080check').bootstrapSwitch("disabled", false);
			$('#port3306check').bootstrapSwitch("disabled", false);
			$('#port5000check').bootstrapSwitch("disabled", false);
			$('#port6080check').bootstrapSwitch("disabled", false);
		}else{
			// 关闭
			// 那么端口的都不能编辑
			$('#port8080check').bootstrapSwitch("disabled", true);
			$('#port3306check').bootstrapSwitch("disabled", true);
			$('#port5000check').bootstrapSwitch("disabled", true);
			$('#port6080check').bootstrapSwitch("disabled", true);
		}
	});

	$('#nfscheck').bootstrapSwitch('onSwitchChange', function (e, data) {
		if(data){
			// 开nfs服务会自动启动rpcbind服务
			$('#rpcbindcheck').bootstrapSwitch('state', true);
			$('#rpcbindcheck').bootstrapSwitch("disabled", true);
		}else{
			// 关闭
			$('#rpcbindcheck').bootstrapSwitch("disabled", false);
		}
	});

	$('#rpcbindcheck').bootstrapSwitch('onSwitchChange', function (e, data) {
		if(data){
			$('#nfscheck').bootstrapSwitch("disabled", false);
		}else{
			// 关rpcbind服务会导致nfs服务异常，所以最好一起关；
			$('#nfscheck').bootstrapSwitch('state', false);
			$('#nfscheck').bootstrapSwitch("disabled", true);
		}
	});

	//提交修改
	var submit = function(){
		Metronic.blockUI({target: '#ossafeform',animate: true});
		var data = {};
		data.firewall_flag = $('#firewallcheck').bootstrapSwitch('state');
		data.ssh_flag = $('#sshcheck').bootstrapSwitch('state');
		data.port8080_flag = $('#port8080check').bootstrapSwitch('state');
		data.port3306_flag = $('#port3306check').bootstrapSwitch('state');
		data.port5000_flag = $('#port5000check').bootstrapSwitch('state');
		data.port6080_flag = $('#port6080check').bootstrapSwitch('state');
		data.nfs_flag = $('#nfscheck').bootstrapSwitch('state');
		data.rpcbind_flag = $('#rpcbindcheck').bootstrapSwitch('state');
		data.api_flag = $('#apicheck').bootstrapSwitch('state');
		pAjaxRequest(data, '/api/v1/system/safe/os', 'POST', function (res) {
			Metronic.unblockUI('#ossafeform');
			if (res.code == 0) {
				return UIToastr.showSuccess(res.message, LANG.UI_PUBLIC_SUCCESS);
			} else {
				return UIToastr.showError(res.message, LANG.UI_DATACENTER_FAILURE);
			}
			// 重新初始化下看看那些修改成功了
			initOldConfig();
		})
	}

	return {
		init: function () {
			handlerInit();
		}
	};

}();

jQuery(document).ready(function(){
	Settings_OS_SAFE.init();
});