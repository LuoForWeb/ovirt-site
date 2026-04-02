var Settings_STORAGE_SAFE = function () {
	var _UserPassword = '';
	var initErrorFlag = false;
	var handlerInit = function(){
		$("#spsubmit").click(function(){
			submit();
        });
        
        $("#spcancel").click(function(){
        	LOCATION('./content/platform/settings/setting_manager.php', 'setting_manager');
        });
        
        initOldConfig();
		initUserPassword();
	}

	//初始化当前用户密码用于删除二次确认
	var initUserPassword = function () {
		pAjaxRequest({}, "/api/v1/users/password", "GET", function (result) {
			if (result.success) {
				_UserPassword = result.data.password;
			}
		});
	}
	
	//初始化配置
	var initOldConfig = function(){
		pAjaxRequest({}, '/api/v1/system/safe/storage', 'GET', function (res) {
			$('#storageprotectcheck').bootstrapSwitch('state', res.data.protect_flag, true);
		})
	}

	$('#storageprotectcheck').bootstrapSwitch('onSwitchChange', function (e, data) {
		if(data){
			// 开启
			submit();
		}else{
			// 关闭 需要验证当前用户的密码
			$('#storageprotectcheck').bootstrapSwitch('state', true, true); // 第二个参数 true 表示不触发 change 事件
			// 密码错误，阻止事件关闭
			// $('#storageprotectcheck').bootstrapSwitch('state', true, true); // 第二个参数 true 表示不触发 change 事件
			bootbox.confirm({
				title: LANG.UI_SETTINGS_STORAGE_SAFE,
				message: LANG.UI_SETTINGS_STORAGE_SAFE_SET,
				callback: debounce(function (r) {
					if (!r) return;
					initErrorFlag = false;
					bootbox.prompt({
						title: LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM,
						inputType: 'password',
						callback: function (result) {
							if (result == null) return;
							if (hex_md5(result) == _UserPassword) {
								$('#storageprotectcheck').bootstrapSwitch('state', false, true);
								submit();
								return true;
							} else {
								UIToastr.showWarning(LANG.UI_SETTINGS_STORAGE_SAFE, LANG.UI_SETTINGS_STORAGE_SAFE_PWD_ERR);
								return false;
							}
						}
					});
				}, 300)
			});
		}
	});
	
	//提交修改
	var submit = function(){
		Metronic.blockUI({target: '#storagesafeform',animate: true});
		var data = {};
		data.protect_flag = $('#storageprotectcheck').bootstrapSwitch('state');
		pAjaxRequest(data, '/api/v1/system/safe/storage', 'POST', function (res) {
			Metronic.unblockUI('#storagesafeform');
			operateResponseList(res, LANG.UI_SETTINGS_STORAGE_SAFE);
			// 判断下是否操作成功
			if (res['success'] != true) {
				// 还原开关之前的状态
				$('#storageprotectcheck').bootstrapSwitch('state', !data.protect_flag, true);
			}
		})
	}
	
    return {
        init: function () {
        	handlerInit();
        }
    };

}();

jQuery(document).ready(function(){
	Settings_STORAGE_SAFE.init();
});