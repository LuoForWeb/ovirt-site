
const $pageContent = $('.page-content');
const $pageContentBody = $('.page-content .page-content-body');
let CURRENT_URL = "./content/platform/databackup_center.php";
let timerTask = {};
var pageLength = {}; //初始化列表每页长度
var _Token = "",
	_AuthToken = "",
	_AgainNum = 0; //用户接口认证，每次像服务器请求时返回更新
var clipboard;	//复制组件实例化，目前在用户信息页面使用
let sameRequestArr = []; // 定义请求的数组
// let LANG = {};


/**
 * 消息结果统一处理
 * @params data[re,lev,title,msg]
 */
var OPREL = function (data) {
	var opRel = function (data) {
		try {
			var data = JSON.parse(data);
			toastr.clear();
			toastr[data['lev']](data['msg'], data['title']);
			return data['re'];
		} catch (e) {
			alert(e.name + " :  " + e.message);
			return false;
		}
	}
	return opRel;
 }();

/**
 * 新消息结果统一处理
 * @params data[message,success]
 * @params operation 原title
 */
var operateResponseList = function (data, operation) {
	var opRel = function (data, operation) {
		try {
			// var data = JSON.parse(data);
			toastr.clear();
			if (data['success'] == true) {
				var lev = 'success';
			} else {
				var lev = 'warning';
			}
			if (data['title'] && operation == undefined) {
			operation = data['title'];
			}
			toastr[lev](data['message'], operation);
			return data['success'];
		} catch (e) {
			alert(e.name + " :  " + e.message);
			return false;
		}
	}
	return opRel;
}();

/**
 * 销毁
 */
const disposeEcharts = () => {
	if (typeof echarts !== 'undefined') {
		let echartsElements = $('[data-chart="echarts"]');
		for (let i = 0; i < echartsElements.length; i++) {
			echarts.dispose(echartsElements[i]);
		}
	}
};
/**
 * 初始化页面中的Tooltips
 */
const initTooltips = function () {
	let tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
	tooltipTriggerList.map(function(tooltipTriggerEl) {
		return new bootstrap.Tooltip(tooltipTriggerEl);
	});
}
 
/**
 * 路由导航
 * @param {*} url 要请求的页面URL
 * @param {*} accorItemHeadId 侧边导航栏 accordionItem 中 accordion-header 的id
 * @param {*} routeName 路由name
 * @param {*} routeParams 自定义路由参数
 */
const LOCATION = function (url, accorItemHeadId, routeName, routeParams = {}) {
	$pageContent.block();

	var urlArr = url.split('.');
	urlArr = urlArr[1].split('/');

	if (urlArr.length > 0) {
		var urlMark = "?" + urlArr[urlArr.length - 1];
	} else {
		var urlMark = "?unknown";
	}

	// TODO：（逻辑待适配新框架）深信服url需要重新修改
	if(CONF.ENTERPRISE == 'sangfor_enterprise'){
		var navigationnew = ''
		var lastpage = urlArr[urlArr.length - 1];
		var navigationflag =  false;
		if(routeName == "" || routeName == undefined){
			//如果没有应该取倒数第二个判断是哪个模块
			navigationflag = true;
			navigationnew =   urlArr[urlArr.length - 1];
		} else {
			navigationnew =  routeName;
		}
		var topmenu = ''

		//这几个特别的先判断		//虚拟化 共有云 私有云  lan-free
		//需要判断最后一个
		if(navigationflag){
			//没有带naviagtion
			//为什么这里要单独处理这几个模块 因为传过来的url是vm/add_vcenter 就算取倒数第二个值也会取到错误的模块
			if(['modify_vcenter','modify_cloud_platform','vcenter_manager','cloud_platform_manager'].includes(lastpage)){
				topmenu = 'resmanagement';
			}
			else{
				//为了处理 jobs/jobs.php这种情况 取倒数第二个
				navigationnew =  urlArr[urlArr.length - 2];
			}
		}else{
			//带了navigation的也要处理
			if(['nasmanager'].includes(lastpage)){
				topmenu = 'resmanagement';
			}
		}
		//资源管理中的一些子模块
		// var resmanagesubmodule = ['vcenter_manager','cloud_platform_manager','client','nasmanager','obsmanager','kubernetes_cluster','exchange_organization','hadoop_cluster','lun_storage_manager','storage_lanfree','virus',
		// 	'storage','tape_equipment',
		// 	'node','cluster_manager','driver_manager','scripts_manager','global_strategy','resource_group'
		// ]
		// var resmanagemodule = ['infrastructure','storage_manager','backup_manager','vm_machine_manager','client','exchange_organization'];
		// //系统设置中一些子模块
		// var systemsubmodule = ['system_network','set_time','system_notice','system_safe','system_poweroff','system_upgrade','message_push','visual_config','system_service','system_br','exercise_platform','black_white_list','system_apikey','system_settings','carbon_monitor_platform']

		// 监控中心
		if(['task','alarm','log','report','vm_report','storage_report','system','jobs'].includes(navigationnew)){
			topmenu = 'monitor';
		}
		//资源管理
		else if(['infrastructure','storage_manager','backup_manager','vm_machine_manager','client','exchange_organization','nas','hadoop_cluster','obsmanager','vcenter_manager','node_manager',
			'resource_group','resource','vcenter_manager','virus'
		].includes(navigationnew)){
			topmenu = 'resmanagement';
		}
		//系统管理
		else if(['setting_manager','safety','tenant_manager','authorization_module','billing_manager','billing','users'].includes(navigationnew)){
			topmenu = 'sysmanagement';
		}
		//数据保护
		else{
			if (topmenu == '') {
				topmenu = 'dataprotect';
			}
		}
		//通过name再跳转左边的导航栏
		// SANGFORCTLHORMENU(topmenu);
		//跳转到对应顶部tab
		localStorage.setItem('history_url', url);
		localStorage.setItem('history_navigation', navigationnew);
		window.location.href ='./'+ topmenu + ".html" + "?" + navigationnew
	}

	TimerManager.clear();
	var urladdress = "";
	var newenterprise = localStorage.getItem('sangfor_enterprise');
	if(newenterprise == 'sangfor_enterprise'){
		urladdress =  "Sangfor Enterprise Data Backup & Recovery System";
	} else {
		urladdress = CONF.SYSTEMNAME;
	}
	// 销毁echarts
	disposeEcharts();
	if(PAGEROUTE[url] != undefined) {
		url = PAGEROUTE[url];
	}
	$.ajax({
		type: "GET",
		cache: false,
		url: url,
		dataType: "html",
		success: function (res) {
			$pageContent.unblock();
			$pageContentBody.html(res);
			
			CURRENT_URL = url;

			History.pushState(Object.assign({ url: url, routeName: routeName || '' }, routeParams), urladdress, urlMark);

			initTooltips(); // 初始化页面中的Tooltips

			if (routeName) {
				CTLSIDEBAR(routeName, accorItemHeadId);
			}
			// 清除蒙层效果
			$('.drawer-backdrop').removeClass('active');
		},
		error: function (xhr, ajaxOptions, thrownError) {
			$pageContentBody.html('<h4>Could not load the requested content.</h4>');
			$pageContent.unblock();
		}
	});
};

/**
  * 路由导航联动
  */
const CTLSIDEBAR = (routeName, accorItemHeadId) => {
    if (!routeName) {
        return;
    }

    // 移除上一次 header-index__button 的高亮样式
    if ($('.accordion-button.header-index__button').hasClass('selected')) {
        $('.accordion-button.header-index__button').removeClass('selected');
    }

    // 移除上一次 header-first__button 的active高亮样式
    if ($('.accordion-button.header-first__button').hasClass('active') && $('.accordion-collapse.accordion-collapse-first').hasClass('active')) {
        $('.accordion-button.header-first__button').removeClass('active');
        $('.accordion-collapse.accordion-collapse-first').removeClass('show active');
        $('.accordion-button.header-first__button').addClass('collapsed');
    }

    // 移除上一次 list-group-first__item 的高亮样式
    if ($('.list-group-first__item').hasClass('selected')) {
        $('.list-group-first__item').removeClass('selected');
    }

    // 移除上一次 header-first__button 的 light-active 样式
    if ($('.accordion-collapse.accordion-collapse-first').hasClass('light-active')) {
        $('.accordion-collapse.accordion-collapse-first').removeClass('show light-active');
        $('.accordion-button.header-first__button').addClass('collapsed');
    }

    let accordionItemFirst = $(`#${accorItemHeadId}`).parents('.accordion-item-first');
    // 判断要跳转的目标页面是属于第一层级 还是 第二层级
    if (accordionItemFirst.hasClass('first-nav')) {
        // 加上active
        accordionItemFirst.find('.accordion-button.header-index__button').addClass('selected');

    } else {
        // 移除 当前accordion 的 light-active
        if (accordionItemFirst.find('.accordion-collapse-first.light-active').length > 0) {
            accordionItemFirst.find('.accordion-collapse-first').removeClass('light-active');
        }

        // 加上active
        accordionItemFirst.find('.header-first__button').addClass('active');
        accordionItemFirst.find('.accordion-collapse-first').addClass('show active');
        accordionItemFirst.find('.header-first__button').removeClass('collapsed');

        // 遍历找到与routeName对应的 list-group-text
        let accorItemFirstBodyId = `#child_${accorItemHeadId.split('parent_')[1]}`;

        // 遍历 menu-first-body 找到name与routeName一致的list-group-item
        $(accorItemFirstBodyId + ' .menu-first-body .list-group-item').each(function() {
            if ($(this).attr('name') === routeName && !$(this).hasClass('selected')) {
                $('.list-group-item').removeClass('selected');
                $(this).addClass('selected');
                // 更新存储在 sessionStorage 中的 SELECTED_FLAG
                sessionStorage.setItem('SELECTED_FLAG', true);

                // 如果导航栏是折叠状态，还要手动加上 show 和 去除 collapsed
                if ($('.page-sidebar-wrapper').hasClass('sidebar-close')) {
                    // 关闭上一次打开的accordion item
                    accordionItemFirst.find('.header-first__button').removeClass('collapsed');
                    accordionItemFirst.find('.accordion-collapse-first').addClass('show');
                }
            }
        });
    }

	// TODO: GMP跳转联调逻辑待修改（如果是GMP 因为GMP只有一级菜单这种情况，所以不需要去sb后面拼接 直接跳转）
	// if(CONF.ENTERPRISE == 'vdms_enterprise'){
	// 	//验证计划 验证平台 报告管理 设备管理
	// 	let  firstarr = ['verification_job','vm_machine_manager','industry_report','clients']
	// 	if(firstarr.indexOf(page) != -1){
	// 		$('.page-sidebar a[name = '+ page+ ']').parent().addClass('active');
	// 	}
	// }
};


/**
 * 定时器管理mega-menu-dropdown
 * 需要循环执行的定时器必须加入到定时器管理
 * 定时器ID为如下格式
 * 定时器对象.页面对象方法全名_定时器作用
 * 如：timerTask.FSJobDetails_basicInfo
 */
const TimerManager = function () {
	return {
		clear: function () {
			for (var task in timerTask) {
				clearTimeout(timerTask[task]);
				delete timerTask[task];
			}
		}
	};
}();

 
/**
 * 防xss转义字符串
 * @param {*} str 
 * @returns 
 */
const reXssEncode = function (str) {
	if (!str || str == "") return true;
	if (str.indexOf("<") != -1 || str.indexOf('>') != -1) {
		UIToastr.showInfo(LANG.UI_ALARM_WRITE_SELECT, LANG.UI_ALARM_WRITE_SELECT_TIPS);
		return false
	} else {
		return str;
	}
}

var getPage = function (name) {
	if (name == "") {
		name = "homepage";
	}
	var params = {
		'name': name
	}
	var url = "";
	params = JSON.stringify(params);
	var a = CONF.AJAXPATH;
	$.post(CONF.AJAXPATH, {
		m: CONF.M.PLATFORM,
		f: 'getPage',
		p: params
	}, function (d) {
		var d = JSON.parse(d);
		//如果是任务详情页面没有获取到url 统一获取保存在SERVER里的request_url
		if (!d.url) {
			if ($('#pageUri').val()) {
				url = $('#pageUri').val();
			} else {
				//判断是默认账户还是租户内部账户
				if (CONF.TENANTUUID == "") {
					url = "./content/platform/databackup_center.php";
				} else {
					url = "./content/platform/tenant_center.php";
				}
			}
		} else {
			url = d.url;
		}

		var pageContent = $('.page-content');
		var resBreakpointMd = vinchinUI.getResponsiveBreakpoint('md');
		//	        vinchinUI.startPageLoading();

		if (vinchinUI.getViewPort().width < resBreakpointMd && $('.page-sidebar').hasClass("in")) { // close the menu on mobile view while laoding a page
			$('.page-header .responsive-toggler').click();
		}
		TimerManager.clear();
		$.ajax({
			type: "GET",
			cache: false,
			url: url,
			dataType: "html",
			success: function (res) {
				//	                vinchinUI.stopPageLoading();
				$pageContentBody.html(res);
				Layout.fixContentHeight(); // fix content height
				vinchinUI.initAjax(); // initialize core stuff
				CURRENT_URL = url;
				//	                History.pushState({url:url}, "", urlMark);
				// 隐藏modal 蒙层
				$('.modal-backdrop').hide();
				$('.drawer-backdrop').hide();
				$('.modal-scrollable').remove();
			},
			error: function (xhr, ajaxOptions, thrownError) {
				$pageContentBody.html('<h4>Could not load the requested content.</h4>');
				vinchinUI.stopPageLoading();
			}
		});

	});

}

/**
 * js端的加解密
 * js端使用 CryptoJS 封装的库。
 */
//加密对应秘钥
var IV = 'sNONwyJtvi2ch2in';
var KEY = '7ebec7acd38b0643c34b09c84ea54393';
IV = CryptoJS.enc.Utf8.parse(IV);
KEY = CryptoJS.enc.Utf8.parse(KEY);
// sign加密
function signEncrypt(str) {
	var encrypted = CryptoJS.AES.encrypt(str, KEY, {
		iv: IV,
		mode: CryptoJS.mode.CBC,
		padding: CryptoJS.pad.Pkcs7
	});
	return encrypted.ciphertext.toString();
}

// sign解密
function signDecrypt(str) {
	// 将十六进制字符串转换为CipherParams对象
	var cipherParams = CryptoJS.lib.CipherParams.create({
		ciphertext: CryptoJS.enc.Hex.parse(str)
	});
	var decrypted = CryptoJS.AES.decrypt(cipherParams, KEY, {
		iv: IV,
		padding: CryptoJS.pad.Pkcs7
	});
	return decrypted.toString(CryptoJS.enc.Utf8);
}

// 全局变量管理所有 AJAX 请求
const ajaxRequestManager = {
	pendingRequests: [],

	// 添加请求到管理列表
	addRequest: function(xhr) {
		this.pendingRequests.push(xhr);
	},

	// 取消所有未完成请求
	abortAll: function() {
		this.pendingRequests.forEach(xhr => {
			if (xhr && xhr.readyState !== 4) { // 4 = DONE
				let url = xhr.url || xhr.options?.url; // jQuery 通常会把 url 放在 xhr.url
				const questionIndex = url.indexOf('?');
				if (questionIndex !== -1) {
					url = url.slice(0, questionIndex);
				}
				if (url != '/api/v1/system/config/base_info') {
					xhr.abort();
				}
			}
		});
		this.pendingRequests = [];
	},

	// 从列表中移除已完成请求
	removeRequest: function(xhr) {
		this.pendingRequests = this.pendingRequests.filter(req => req !== xhr);
	}
};

// 全局事件绑定（兼容性更好）
$(document).ajaxSend(function(event, jqXHR, settings) {
	// ✅ 关键：把请求的 URL 保存到 jqXHR 上
	jqXHR.url = settings.url;

	// 如果你还想保存其他信息，也可以：
	// jqXHR.type = settings.type;
	// jqXHR.data = settings.data;

	ajaxRequestManager.addRequest(jqXHR);
});
$(document).ajaxComplete(function(e, jqXHR) {
	ajaxRequestManager.removeRequest(jqXHR);
});

/**
 *
 * @param requestData 消息对象
 * @param url	接口url：/api/v1/login
 * @param httpType	get|post
 * @param requestFun 回调函数
 * @param async		是否为异步
 * @param backInfo  超时重连接口消息
 * @returns
 */
function pAjaxRequest(requestData, url, httpType, requestFun, async = true, backInfo = {}, isVisualScreen = false) {
	// 将请求参数序列化后作为键存储在 sessionStorage 中
	const requestKey = JSON.stringify({ requestData, url, httpType, async, isVisualScreen });

	//初始化超时重连接收参数
	var backs = {};
	backs['requestData'] = requestData;
	backs['url'] = url;
	backs['httpType'] = httpType;
	backs['requestFun'] = requestFun;
	backs['async'] = async;
	requestData = deepCloneObjectString(requestData);
	// 参与签名的对象
	var requestSignData = deepCloneObjects(requestData, httpType.toLowerCase() === 'get');
	//获取当前操作时间戳
	requestData.timestamp = new Date().getTime();
	requestSignData.timestamp = requestData.timestamp;
	//对请求参数键名升序排序
	var keyList = [];
	var len = 0;
	$.each(requestSignData, function (key, val) {
		keyList[len] = key;
		len++;
	});

	keyList.sort();
	var list = {};
	$.each(keyList, function (i, key) {
		list[key] = requestSignData[key];
	});
	//sign加密消息
	var sign = signEncrypt(JSON.stringify(list));

	//将加密字符串放入消息对象里
	requestData.sign = sign;

	//消息对象转为json统一格式
	var jsonData;
	//处理Get请求
	var URL = url;
	if(CONF.HOST != undefined){
		URL = CONF.HOST + url;
	}
	if (httpType == "GET" || httpType == "get") {
		jsonData = requestData;
	} else {
		jsonData = JSON.stringify(requestData);
	}

	if (_Token == '') {
		_Token = window.localStorage.getItem('csrf_token');
	}

	if (_AuthToken == '') {
		_AuthToken = window.localStorage.getItem('access_token');
	}

	var _visualToken = _AuthToken;
	// 判断是否是大屏的url
	if (isVisualScreen) {
		_visualToken = 'auth_token_visualscreen_' +  _AuthToken;
	}

	//执行ajax请求
	$.ajax({
		url: URL, //请求接口url链接
		beforeSend: function (XMLHttpRequest) {
			// 添加到请求管理器
			ajaxRequestManager.addRequest(XMLHttpRequest);
			//设置headers
			XMLHttpRequest.setRequestHeader("X-Csrf-Token", _Token); //接口认证
			XMLHttpRequest.setRequestHeader("x-api-version", "2.0-rev0"); //当前接口版本
			XMLHttpRequest.setRequestHeader("Authorization", _visualToken); //身份认证
		},
		type: httpType, //请求类型 GET|POST|PUT|DELETE
		async: async, //请求是否异步处理，true|false
		data: jsonData, //需要发送到服务器的消息数据
	//  dataType: 'json', //服务器响应的数据类型 （注释原因：参数为两个以上英文问号即 ?? 时，会被序列化成 'jQuery3610956312798033351_1740041578334'，导致PHP校验前后端SIGN不一致而报错）
		success: function (res, status, xhr) {
			if (xhr.statusText === 'abort') return; // 忽略被取消的请求
			// 请求完成后从管理器中移除
			ajaxRequestManager.removeRequest(xhr);
			//成功处理执行成功步骤

			//设置接口认证信息
			_Token = xhr.getResponseHeader('__token__');
			window.localStorage.setItem('csrf_token', _Token);

			//认证成功后保存用户登录信息到localstorage
			if (url == "/api/v1/login" && res.message == 1) {
				//设置身份认证信息
				_AuthToken = res.data.access_token;
				window.localStorage.setItem('access_token', _AuthToken);
				window.localStorage.setItem('username', requestData.username);
				window.localStorage.setItem('password', requestData.password);
			}

			//授权超时需要重新认证
			if (!res.success && res.code == 910086) {
				var username = window.localStorage.getItem('username');
				var password = window.localStorage.getItem('password');
				if (username != '' && password != '') {
					var request = {
						username: username,
						password: password
					};

					return pAjaxRequest(request, "/api/v1/login", "POST", relogin, false, backs);
				}
			}

			// 如果是token失败，那么重新请求一次,最多重试3次，避免进入死循环
			if (!res.success && res.code == 910087) {
			if ((sameRequestArr[requestKey] != undefined && sameRequestArr[requestKey] < 3) || sameRequestArr[requestKey] == undefined) {
				var num = sameRequestArr[requestKey] == undefined ? 1 : sameRequestArr[requestKey] + 1;
				// _AgainNum++;
				sameRequestArr[requestKey] = num;
				return pAjaxRequest(backs['requestData'], backs['url'], backs['httpType'], backs['requestFun'], backs['async']);
			}
			}
			sameRequestArr[requestKey] = 0; // 重置次数
			// _AgainNum = 0; // 重置次数
			//发多个请求，根据队列返回,服务端已处理多个接口依次返回
			if (backInfo.requestData) {
				// 进行重连之前的请求
				return pAjaxRequest(backInfo['requestData'], backInfo['url'], backInfo['httpType'], backInfo['requestFun'], backInfo['async']);
			}

			//执行返回成功函数
			requestFun(res);
		},
		error: function (err) {
			// 请求完成后从管理器中移除
			ajaxRequestManager.removeRequest(err);
			const KEEP_REQUEST_URL = ['/api/v1/system/packet_status', '/api/v1/system/upgrade_log']

			// 获取升级日志过程中，后台文件会被替换，报500/502错误，此时跳过错误继续请求获取日志
			if (KEEP_REQUEST_URL.indexOf(URL) > -1) {
				setTimeout(() => {
					return pAjaxRequest(backs['requestData'], backs['url'], backs['httpType'], backs['requestFun'], backs['async']);
				}, 2000);
			}
			// return UIToastr.showWarning(LANG.UI_JOB_NETWORK_FAULT, LANG.UI_JOB_NETWORK_FAULT_TIPS);

		},
		complete: function(xhr) {
			// 确保请求完成后从管理器中移除
			ajaxRequestManager.removeRequest(xhr);
		}
	});

}

//重连认证返回超时信息提示
var relogin = function (resData) {
	// UIToastr.showWarning('认证信息超时','请求认证信息超时，请重新访问');
	return;
}

 /**
  * 禁止F5刷新
  */
 document.onkeydown = function (event) {
	 var e = event || window.event || arguments.callee.caller.arguments[0];
	 if (e && e.keyCode == 116) { // 按F5
		e.preventDefault(); //首先禁止按下F5刷新页面
		var param = window.location.search;
		if(CONF.ENTERPRISE == 'sangfor_enterprise'){
			var name = param.substring(1, param.length);
			//处理F5刷新当前页和数据保护
			if(window.location.href.includes("monitor.html?jobs")) {
				name = "task";
			}else if(window.location.href.includes("dataprotect.html?vmreport")) {
				//数据保护获取侧边栏下的name
				// name = $(".page-sidebar-menu li").eq(1).find("a.ajaxify").attr("name");
				name ="vm_overview"
			}else if(window.location.href.includes("resmanagement.html?storage")) {
				//资源管理
				name = "storage_manager";
			}else if(window.location.href.includes("sysmanagement.html?setting_manager")) {
				//系统管理
				name = "setting_manager";
			}
			if(name == 'homepage') {
			//首页
			window.location.reload()
			}else {
				getPage(name);
			}
		}else{
			if(param == "?homepage") return;
			var name = param.substring(1, param.length);
			getPage(name);
		}
	 }
 };

 /**
  * 判断是否为Object类型
  * @param {*} val
  * @returns
  */
 const isPlainObject = (val) => {
	 return  val !== null && typeof val === 'object';
 }
 const deepCloneObject = (target, hash = new WeakMap(), i=0) => {
	 return target;
 }
 /**
  * 接口ajax data对象深拷贝，{}转[], null转'', 数字字符串转Number
  * @param {*} target 接口传参对象
  * @param {*} isGet 是否为get请求
  * @param {*} hash weakMap对象，用于存储已拷贝过的对象
  * @returns 拷贝后的新对象
  */
 const deepCloneObjects = (target, isGet = true, hash = new WeakMap()) => {
	 if (!isPlainObject) {
		 return target;
	 }

	 if (hash.get(target)) {
		 return hash.get(target);
	 }

	 let newObj = Array.isArray(target) ? [] : {};
	 hash.set(target, newObj);

	 for (let key in target) {
		 if (Object.prototype.hasOwnProperty.call(target, key)) {
			 if (isPlainObject(target[key])) {
				 // 如果是{}则转成[]
				 if (JSON.stringify(target[key]) === '{}') {
					 newObj[key] = [];
				 } else if (isGet && JSON.stringify(target[key]) === '[]') {
					 // skip
				 } else {
					 // 递归拷贝
					 newObj[key] = deepCloneObjects(target[key], isGet, hash);
				 }
			 } else {
				 if (target[key] == parseInt(target[key])) { // 判断是否为纯数字字符串
					 // 判断数字大小是否超过JS能精准表示的最大整数Math.pow(2, 53)
					 if (Math.abs(parseInt(target[key])) > Math.pow(2, 53)) {
						 newObj[key] = String(target[key]);
					 } else {
						 newObj[key] = parseInt(target[key]);
					 }
				 } else if (target[key] === null) { // 判断是否为null
					 newObj[key] = '';
				 } else {
					 newObj[key] = target[key];
				 }
			 }
		 }
	 }

	 return newObj;
 }

/**
 * 接口ajax data对象深拷贝，{}转[], null转'', 数字字符串转Number
 * @param {*} target 接口传参对象
 * @param {*} hash weakMap对象，用于存储已拷贝过的对象
 * @returns 拷贝后的新对象
 */
const deepCloneObjectString = (target, hash = new WeakMap()) => {
	if (!isPlainObject) {
		return target;
	}

	if (hash.get(target)) {
		return hash.get(target);
	}

	let newObj = Array.isArray(target) ? [] : {};
	hash.set(target, newObj);

	for (let key in target) {
		if (Object.prototype.hasOwnProperty.call(target, key)) {
			if (isPlainObject(target[key])) {
				// 如果是{}则转成[]
				if (JSON.stringify(target[key]) === '{}') {
					newObj[key] = [];
				} else {
					// 递归拷贝
					newObj[key] = deepCloneObjectString(target[key], hash);
				}
			} else {
				if (target[key] === null) { // 判断是否为null
					newObj[key] = '';
				} else {
					newObj[key] = target[key];
				}
			}
		}
	}

	return newObj;
}

/**
 * 退出登录/锁定屏幕
 * type 1表示退出登录，0表示锁屏
 * */
var loginOut = function (type = 0){
	pAjaxRequest({out: type}, '/api/v1/login_out', "POST", function (result) {
		if (result.code == 0) {
			UIToastr.showSuccess(result.message, LANG.UI_DATACENTER_SUCCESS);
			var url = '/lock';
			if (type > 0) {
				url = '/login';
			}
			setTimeout(function (){
				// 必须使用你当初 setItem 时用的 key 名
				localStorage.removeItem('access_token');
				localStorage.removeItem('username');
				localStorage.removeItem('password');

				window.location.href = url;
			}, 1000)
		} else {
			return UIToastr.showError(result.message, LANG.UI_DATACENTER_FAILURE);
		}
	}, false);
}

/**
 * 获取授权信息
 * @param {*} module 模块类型【文件file 虚拟机vm 数据库oracle 操作系统os nas hadoop 对象存储obs 实时容灾cdp exchange 私有云private_cloud 公有云cloud 实时接管vol_cdp 内嵌embd 跨平台恢复v2v】
 * @param {*} showvinchinUI 是否显示请求授权接口的加载动画
 * @param {*} showAlertMsg 是否显示授权不足的提示信息
 * @param {*} judge 判断是否满足授权
 * @param {*} uuids 用于可以重复使用主机的模块去计算已用授权个数
 * @param {*} task_uuid 任务uuid,用于修改任务处判断授权
 * @returns {
 *  code: 请求码
 * 	success: 是否成功
 *  message: 提示信息
 *  data: {
 *   auth_type: 授权方式【1: 按数量授权 2: 按容量授权】
 *   license_type: 主机授权方式【1: 独立数量授权 2: 整机数量授权 3: 文件系列生产容量+其他部分数量授权 4: 文件系列生产容量+其他部分整机授权】
 * 				   虚拟机授权方式【1: 按宿主机(HOST)个数授权 2: 按处理器(CPU)个数授权 4: 按虚拟机(VM)个数授权】
 *   total: 总容量/数量
 *   used: 已使用容量/数量
 *  },
 * }
 */
const getModuleAuthInfo = ({module, currentUse = 1, showvinchinUI = true, showAlertMsg = true, judge = true,uuids=[],task_uuid = ''}) => {
	return new Promise(resolve => {
		if (showvinchinUI) {
			vinchinUI.blockUI({target: 'body', animate: true});
		}
		let reqData = {
			module,
			type: 'a',
			uuids: uuids,
			task_uuid: task_uuid,
		};
		pAjaxRequest(reqData, `/api/v1/system/auth/base_info`, `GET`, res => {
			if (showvinchinUI) {
				vinchinUI.unblockUI('body');
			}
			if (!judge) {
				resolve(res);
				return;
			}
			if (!res.success) {
				UIToastr.showWarning(LANG.UI_PUBLIC_OBTAIN_LICENSE_INFO, res.message);
				resolve(false);
				return;
			}
			//按数量授权，数量无限制的情况
			if (parseInt(res.data.auth_type) === 1 && res.data.total == -1) {  // total为-1, 代表数量无限制
				resolve(true);
				return;
			}
			if (parseInt(res.data.auth_type) === 1) {  // 数量授权
				if (parseInt(res.data.total) < parseInt(res.data.used) + currentUse) {
					if (showAlertMsg) {
						let remain = parseInt(res.data.total) - parseInt(res.data.used);
						remain = remain < 0 ? 0 : remain;
						message = LANG.UI_PUBLIC_OBTAIN_LICENSE_INSUFFICIENT_AMOUNT
							.replace('%REMAIN%', remain)
							.replace('%USE%', currentUse);
						UIToastr.showWarning(LANG.UI_PUBLIC_OBTAIN_LICENSE_INFO, message);
					}
					resolve(false);
					return;
				}
			}
			resolve(true);
		});
	});
};

/**
 * 操作权限校验
 * @param data      请求构造的数据
 * @param callback 是原来正常的操作方法
 * */
const checkOperateAuth = function(data, callback)
{
	pAjaxRequest(data, "/api/v1/users/check/operation/", "POST", function (result) {
		if (!result.success) {
			UIToastr.showError(LANG.UI_PUBLIC_TIPS, result.message);
			return false;
		} else {
			callback();
		}
	}, false)
}

//判断存储是否是磁带并切换保留策略展示切换线程配置展示(创建备份任务使用)
const getTapeStrategy = function (storageUuid, storageType, threadDiv, threadInputDiv, strategyDiv, module_type, $editFlag = false) {
	if(storageType == CONF.BD_STORAGE_TYPE.TAPE) {
		$(threadInputDiv).spinner("value", 1); //线程输入框置为1
		$(threadDiv).hide(); //隐藏线程配置div
		$('.reserve-strategy-form').hide();
		//获取磁带策略并设置到保留策略
		pAjaxRequest({group_uuid:storageUuid}, '/api/v1/tapes/group/strategy', "GET", (res) => {
			$('.tape-strategy-form .reserveDes').text(LANG.UI_TAPE_USE_GROUP_STRATEGY);
			$('.tape-strategy-form .reserveDes').attr('title', LANG.UI_TAPE_USE_GROUP_STRATEGY);
			$('.tape-strategy-form span.font-green-seagreen').text(`${LANG.UI_GLOBAL_STRATEGY_NAME}`);
			$('.tapeStrategyDiv').show();
			$('.generate').text(res.data.backup_set_strategy_des);
			$('.reserve').text(res.data.reserve_strategy_des);
			$('.tape-strategy-form').show();
			//配置总览页面
			$('.reserveDiv>.control-label').text(LANG.UI_TAPE_GROUP_STRATEGY + ':');
			$('.reserveDiv .reservetypeshow').html(`${LANG.UI_TAPE_SELECT_GENERATE_STRATEGY}:${res.data.backup_set_strategy_des} <br> ${LANG.UI_TAPE_RESERVE_STRATEGY}:${res.data.reserve_strategy_des}`);
		}, true);
	} else {
		$(threadDiv).show();
		$('.reserve-strategy-form').show();
		$('.tapeStrategyDiv').hide();
		$('.tape-strategy-form').hide();
		$('.tape-strategy-form span.font-green-seagreen').text(`${LANG.UI_STRATEGY_RESERVE}`);
		$('.reserveDiv>.control-label').text(LANG.UI_STRATEGY_RESERVE + ':');
	}
}

/**
 * 获取语言配置
 */
// eslint-disable-next-line no-unused-vars
const readLang = (key, module = '') => {
	// 先检查缓存里是否有该key，有就直接返回
	if (LANG[key]) {
		return LANG[key];
	}

	pAjaxRequest({key: key, module: module}, '/api/v1/lang', 'GET', (res) => {
		if (res.code === 0) {
			$.extend(LANG, res.data);
		}
	}, false);

	return LANG[key];
};

/**
  * 递归遍历拷贝请求参数
  * @param {*} target
  * @param {*} hash
  * @returns
  */
const recursiveCloneRequestData = (target, hash = new WeakMap()) => {
    if (!isPlainObject) {
        return target;
    }

    if (hash.get(target)) {
        return hash.get(target);
    }

    let newObj = Array.isArray(target) ? [] : {};
    hash.set(target, newObj);

    for (let key in target) {
        if (Object.prototype.hasOwnProperty.call(target, key)) {
            if (isPlainObject(target[key])) {
                // 如果是{}则转成[]
                if (JSON.stringify(target[key]) === '{}') {
                    newObj[key] = [];
                } else {
                    // 递归拷贝
                    newObj[key] = recursiveCloneRequestData(target[key], hash);
                }
            } else if (target[key] === null) { // 判断是否为null
                newObj[key] = '';
            } else {
                newObj[key] = target[key];
            }
        }
    }

    return newObj;
};

/**
 * 递归遍历处理签名数据
 * @param {*} target 要克隆的目标对象
 * @param {*} isGet 是否为GET请求
 * @param {*} hash hash
 * @returns
 */
const recursiveCloneSignData = (target, isGet, hash = new WeakMap()) => {
    if (!isPlainObject) {
        return target;
    }

    if (hash.get(target)) {
        return hash.get(target);
    }

    let newObj = Array.isArray(target) ? [] : {};
    hash.set(target, newObj);

    for (let key in target) {
        if (Object.prototype.hasOwnProperty.call(target, key)) {
            if (isPlainObject(target[key])) {
                // 如果是{}则转成[]
                if (JSON.stringify(target[key]) === '{}') {
                    newObj[key] = [];
                } else if (isGet && JSON.stringify(target[key]) === '[]') {
                    // skip
                } else {
                    // 递归拷贝
                    newObj[key] = recursiveCloneSignData(target[key], isGet, hash);
                }
            // eslint-disable-next-line eqeqeq
            } else if (target[key] == parseInt(target[key])) { // 判断是否为纯数字字符串，只比较值不比较类型，用 ==
                // 判断数字大小是否超过JS能精准表示的最大整数Math.pow(2, 53)
                if (Math.abs(parseInt(target[key])) > Math.pow(2, 53)) {
                    newObj[key] = String(target[key]);
                } else {
                    newObj[key] = parseInt(target[key]);
                }
            } else if (target[key] === null) { // 判断是否为null
                newObj[key] = '';
            } else if (isGet && typeof target[key] === 'undefined') {
                // skip
            } else {
                newObj[key] = target[key];
            }
        }
    }

    return newObj;
};
