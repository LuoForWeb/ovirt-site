var VinchinInit = (() => {
	let getSystemTimer = null;
	let setSystemTimer  = null;
	let timeNum = 0;
	let systemTimeElement = $("#systemTimeTop");

	/**
	 * 初始化配置信息
	 */
	const initConfig = () => {
		pAjaxRequest({}, '/api/v2/system/config/base_info', "GET", function (result) {
			let config = result.data;
			CONF.SYSTEMNAME = config.system_name;
			CONF.VM_TYPE = config.vm_type;
			CONF.VM_DES = config.vm_des;
			CONF.SOFTWARE = config.software;
			CONF.DB_TYPE = config.db_type;
			CONF.DB_DES = config.db_des;
			CONF.IDLETIMEOUT = config.idletime_out;
			UIIdleTimeout.init(); // 初始化超时时间
			CONF.PASS_LENGTH = config.pass_length;
			CONF.PASS_COMPLEXITY = config.pass_complexity;
			CONF.HOST = config.host_name;
			//权限是object，需要转为array
			let permission = config.permission;
			let list = [];
			for(let i in permission){
				list.push(permission[i]);
			}
			CONF.PERMISSION = list;
			CONF.PERMISSION_ARR = Object.values(config.permission_arr);
			CONF.TASK_TYPE = config.task_type;
			CONF.TASK_TYPE_DES = config.task_type_des;
			CONF.TASK_STATUS = config.task_status;
			CONF.TASK_STATUS_DES = config.task_status_des;
			CONF.MODULE_TYPE = config.module_type;
			CONF.MODULE_TYPE_DES = config.module_type_des;
			CONF.FS_SUBMODULE_TYPE_DES = config.fs_submodule_type_des;
			CONF.VM_SUBMODULE_TYPE_DES = config.vm_submodule_type_des;
			CONF.STORAGE_TYPE_DES = config.storage_type_des;
			CONF.ENTERPRISE = config.enterprise;
			CONF.VENDOR = config.vendor; // oem版本
			CONF.REAL_PROTECT_STAGE_LIST = config.real_protect_stage_list;
			CONF.COMMON_STAGE_LIST = config.common_stage_list;
			CONF.AUTH_DB_TYPE = config.auth_db_type;

			let lang = CONF.LANG_CONF[config.language];
			bootbox.setLocale(lang);
			CONF.TENANTUUID = config.tenant_uuid;
			CONF.LANGUAGE = config.language;
			CONF.FUNCTIONS = config.function;
			CONF.PREFIX_STATUS = config.prefix_status;
			CONF.USER_LEVEL = config.user_level;
			CONF.PRODUCT_TYPE = config.product_type;
			CONF.IS_THREE_POWERS = config.is_three_powers;
			CONF.CHANGE_OTHER_PASSWD = config.change_other_passwd;
			CONF.VENDOR_LIST = config.vendor_list;
		});
	}

	/**
	 * 获取全局观察者配置信息
	 */
	const initGlobalObserverConfig = () => {
		pAjaxRequest({ type: 'p' }, '/api/v1/system/auth/base_info', 'GET', (res) => {
			if (res.success) {
				CONF.GLOBAL_OBSERVER_CONFIG = res.data || [];
			} else {
				CONF.GLOBAL_OBSERVER_CONFIG = [];
			}
		});
	}

	/**
	 * 初始化系统时间
	 */
	const initSystemTime = async() => {
		// 清除定时器
		cleanupTimer();
		// 立即获取一次系统时间
		await refreshServerTime();
		if(setSystemTimer == null){
			setSystemTimer = setInterval(updateTime, 1000);
		}
		if(getSystemTimer == null){
			getSystemTimer = setInterval(getSystemTime, 900000); //十五分钟进行一次请求
		}
	}

	const refreshServerTime = () => {
		return new Promise((resolve, reject) => {
			pAjaxRequest({},'/api/v1/system/times/info','GET',(d) => {
				setSystemTime(d);
				resolve();
			},true, {},true);
		});

	}
	const getSystemTime = () => {
	    refreshServerTime();
		if(setSystemTimer){
			clearInterval(setSystemTimer);
			setSystemTimer = setInterval(updateTime, 1000);
		}
	}

	function cleanupTimer() {
		clearInterval(getSystemTimer);
		clearInterval(setSystemTimer);
		getSystemTimer = null;
		setSystemTimer = null;
	}

	//设置初始化时间显示
	const setSystemTime = (d) => {
		if(d.success){
			let nowTime = new Date(d.data.date);
			timeNum = nowTime.getTime();
			nowTime = new Date(timeNum);
			let formattedDateTime = timeStyle(nowTime)
			systemTimeElement.text(formattedDateTime);
		}
	}

	//更新系统时间
	const updateTime = async()=>{
		timeNum += 1000;
        let nowTime = new Date(timeNum);
        let formattedDateTime = timeStyle(nowTime);
		systemTimeElement.text(formattedDateTime);
	}
	//将时间转换成时间yyy-MM-DD HH:mm:ss的格式
	const timeStyle = (time) => {
		return  time.getFullYear() + '-' +
				('0' + (time.getMonth() + 1)).slice(-2) + '-' +
				('0' + time.getDate()).slice(-2) + ' ' +
				('0' + time.getHours()).slice(-2) + ':' +
				('0' + time.getMinutes()).slice(-2) + ':' +
				('0' + time.getSeconds()).slice(-2);
	}

	return {
        init: () => {
			initConfig();
			initGlobalObserverConfig();
			initSystemTime(); // 初始化系统时间
			// 为所有ajax请求默认设置缓存
            $.ajaxSetup({
                cache: true
            });
        }
    };
})();

jQuery(document).ready(function() {
    VinchinInit.init();
});