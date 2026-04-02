/*******************************
 ** JS公共方法封装
 ** 操作结果统一处理 OPREL
 **
 **
 **
 *******************************/
 const handlerGather = {}; // 用于存储自定义事件的全局对象
 var pageLength = {}; //初始化列表每页长度
 var _Token = "",
	 _AuthToken = "",
	 _AgainNum = 0; //用户接口认证，每次像服务器请求时返回更新
var clipboard;	//复制组件实例化，目前在用户信息页面使用
let sameRequestArr = []; // 定义请求的数组

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
var disposeEcharts = () => {
	if (typeof echarts !== 'undefined') {
		let echartsElements = $('[data-chart="echarts"]');
		for (var i = 0; i < echartsElements.length; i++) {
			echarts.dispose(echartsElements[i]);
		}
	}
};

/**
 * 等待系统状态
 * @author JackC
 * @email 1366294101@qq.com
 * @returns {Promise<unknown>}
 */
const waitSystemInit = () => {
	const WAIT_INTERVAL_TIME = 1000;  // 等待间隔时间(ms)
	const MAX_WAIT_COUNT = 10;  // 最大等待次数
	let currentWaitCount = 0;  // 当前等待次数
	return new Promise((resolve) => {
		const checkFlag = () => {
			if (CONF.SYSTEM_INIT.SYSTEM_CONFIG_FLAG) {
				Metronic.unblockUI('body');
				resolve();
			} else {
				currentWaitCount++;
				if (currentWaitCount >= MAX_WAIT_COUNT) {
					Metronic.unblockUI('body');
					UIToastr.showError('System Initialization Failed', 'The system configuration initialization timeout occurred');
					resolve();
				} else {
					setTimeout(checkFlag, WAIT_INTERVAL_TIME);
				}
			}
		};
		Metronic.blockUI({target: 'body', animate: true});
		checkFlag();
	});
};

 /**
  * AJAX统一调转页面
  */
 var CURRENT_URL = "./content/platform/databackup_center.php";
  /**
  * 路由导航方法
  * @param {*} url 页面url
  * @param {*} navigation 路由name
  * @param {*} routeParams 自定义路由参数
  */
 const LOCATION = function (url, navigation, routeParams = {}) {
	  Metronic.scrollTop();
	  var pageContent = $('.page-content');
	  var pageContentBody = $('.page-content .page-content-body');
	  var urlArr = url.split('.');
	  urlArr = urlArr[1].split('/');
	  if (urlArr.length > 0) {
		  var urlMark = "?" + urlArr[urlArr.length - 1];
	  } else {
		  var urlMark = "?unknown";
	  }
	  //深信服url需要重新修改
	  if(CONF.ENTERPRISE == 'sangfor_enterprise'){
		  var navigationnew = ''
		  var lastpage = urlArr[urlArr.length - 1];
		  var navigationflag =  false;
		  if(navigation == "" || navigation == undefined){
			  //如果没有应该取倒数第二个判断是哪个模块
			  navigationflag = true;
			  navigationnew =   urlArr[urlArr.length - 1];
		  }else{
			  navigationnew =  navigation;
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
	  Metronic.startPageLoading();
	  TimerManager.clear();
	  var urladdress = "";
	  var newenterprise = localStorage.getItem('sangfor_enterprise');
	  if(newenterprise == 'sangfor_enterprise'){
		  urladdress =  "Sangfor Enterprise Data Backup & Recovery System"
	  }else{
		  urladdress = CONF.SYSTEMNAME;
	  }
	  // 销毁echarts
	  disposeEcharts();
	  $('[data-toggle="tooltip"]').tooltip('hide');
	  $.ajax({
		  type: "GET",
		  cache: false,
		  url: url,
		  dataType: "html",
		  success: function (res) {
			  Metronic.stopPageLoading();
			  pageContentBody.html(res);
			  Layout.fixContentHeight(); // fix content height
			  Metronic.initAjax(); // initialize core stuff
			  CURRENT_URL = url;

			  History.pushState(Object.assign({ url: url, routeName: navigation || '' }, routeParams), urladdress, urlMark);

			  //控制导航,这里可以根据情况调整到顶部或左边
			  if (navigation) {
				  CTLSIDEBAR(navigation);
			  }
			  // 清除蒙层效果
			  $('.drawer-backdrop').removeClass('active');
		  },
		  error: function (xhr, ajaxOptions, thrownError) {
			  pageContentBody.html('<h4>Could not load the requested content.</h4>');
			  Metronic.stopPageLoading();
		  }
	  });
 };

 /**
  * 左侧菜单导航联动逻辑
  * @param {*} page
  */
 const CTLSIDEBAR = function (page) {
	let menu = $('.page-sidebar-menu');
	menu.find('li.active > ul').hide();
	menu.find('li.active').removeClass('open active');
	menu.find('li.open > ul').hide();
	menu.find('.open').removeClass('open');

	let liHeader = "#sb_";
	let liID = liHeader + page;
	let level1Flag = $(liID).hasClass("level1");
	let level2Flag = $(liID).hasClass("level2");

	let sidebarIsFolded = $('.page-sidebar').hasClass('sidebar-close'); // 菜单导航是否已折叠标记
	if (level1Flag) {
		//菜单在第二层
		$(liID).addClass('active');
		let ppLi = $(liID).parent().parent().get(0);
		$(ppLi).addClass('open active');
		let ulDiv = $(liID).find('> ul');

		if (ulDiv.lenth > 0) {
			//标签下有ul
			$(ppLi).find('> a > span.arrow').addClass('open');

			if (!sidebarIsFolded) { // 已折叠的菜单导航无需show();
				$(ppLi).find('ul').show();
			}
		} else {
			//标签下无ul
			if (!sidebarIsFolded) { // 已折叠的菜单导航无需show();
				$(liID).parent().show();
			}

			$(ppLi).find('> a > span.arrow').addClass('open');
			$(liID).show();
		}

		//	    $(ppLi).find('> a > span.arrow').addClass('open');
		//
		//	    $(ppLi).find('ul').show();
	}

	if (level2Flag) {
		//菜单在第三层
		$(liID).addClass('active');
		let ppLi2 = $(liID).parent().parent().get(0);
		$(ppLi2).addClass('open active');
		$(ppLi2).find('> a > span.arrow').addClass('open');
		if (!sidebarIsFolded) {
			$(ppLi2).find('ul').show();
		}

		let ppLi3 = $(ppLi2).parent().parent().get(0);
		$(ppLi3).addClass('open active');
		$(ppLi3).find('> a > span.arrow').addClass('open');

		if (!sidebarIsFolded) {
			$(ppLi3).find('ul').first().show();
		}
	}

	//如果是GMP 因为GMP只有一级菜单这种情况，所以不需要去sb后面拼接 直接跳转
	if(CONF.VENDOR == CONF.VENDOR_LIST.gmp){
		//验证计划 验证平台 报告管理 设备管理 我的待办
		let  firstarr = ['verification_job','vm_machine_manager','industry_report','clients', 'todo_list']
		if(firstarr.indexOf(page) != -1){
			$('.page-sidebar a[name = '+ page+ ']').parent().addClass('active');
		}
	}
 };

 //顶部菜单定位
 //menu: bakandrec, managerment, orch, logalarm
 var CTLHORMENU = function (menu) {
	 $('.classic-menu-dropdown').removeClass('active');
	 $('.mega-menu-dropdown').removeClass('active');
	 $('.hor-menu').find('li[data-name=' + menu + ']').addClass('active');
 }

 var timerTask = {};
 /**
  * 定时器管理mega-menu-dropdown
  * 需要循环执行的定时器必须加入到定时器管理
  * 定时器ID为如下格式
  * 定时器对象.页面对象方法全名_定时器作用
  * 如：timerTask.FSJobDetails_basicInfo
  */
 var TimerManager = function () {
	 return {
		 clear: function () {
			 for (var task in timerTask) {
				 clearTimeout(timerTask[task]);
				 delete timerTask[task];
			 }
		 }
	 };
 }();

 //防xss转义字符串
 var xssEncode = function (str) {
	 if (!str || str == "") return str;
	 var des = str.replace(/</g, '&lt;').replace(/>/g, '&gt;');
	 return des;
 }
 var reXssEncode = function (str) {
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
		 var pageContentBody = $('.page-content .page-content-body');
		 var resBreakpointMd = Metronic.getResponsiveBreakpoint('md');
		 //	        Metronic.startPageLoading();

		 if (Metronic.getViewPort().width < resBreakpointMd && $('.page-sidebar').hasClass("in")) { // close the menu on mobile view while laoding a page
			 $('.page-header .responsive-toggler').click();
		 }
		 TimerManager.clear();
		 $.ajax({
			 type: "GET",
			 cache: false,
			 url: url,
			 dataType: "html",
			 success: function (res) {
				 //	                Metronic.stopPageLoading();
				 pageContentBody.html(res);
				 Layout.fixContentHeight(); // fix content height
				 Metronic.initAjax(); // initialize core stuff
				 CURRENT_URL = url;
				 //	                History.pushState({url:url}, "", urlMark);
				 // 隐藏modal 蒙层
				 $('.modal-backdrop').hide();
				 $('.drawer-backdrop').hide();
				 $('.modal-scrollable').remove();
			 },
			 error: function (xhr, ajaxOptions, thrownError) {
				 pageContentBody.html('<h4>Could not load the requested content.</h4>');
				 Metronic.stopPageLoading();
			 }
		 });

	 });

 }
 /**
  * 存储单位换算,入参单位为字节
  */
 var storageCalculateSize = function (size) {
	 var data = "";
	 if (size < 0.1 * 1024) { //如果小于0.1KB转化成B
		 data = size.toFixed(2) + " B";
	 } else if (size < 0.1 * 1024 * 1024) { //如果小于0.1MB转化成KB
		 data = (size / 1024).toFixed(2) + " KB";
	 } else if (size < 1 * 1024 * 1024 * 1024) { //如果小于0.1GB转化成MB
		 data = (size / (1024 * 1024)).toFixed(2) + " MB";
	 } else if (size < 1 * 1024 * 1024 * 1024 * 1024) { //如果小于0.1TB转化成GB
		 data = (size / (1024 * 1024 * 1024)).toFixed(2) + " GB";
	 } else if (size < 1 * 1024 * 1024 * 1024 * 1024 * 1024) { //如果小于0.1PB转化成TB
		 data = (size / (1024 * 1024 * 1024 * 1024)).toFixed(2) + " TB";
	 } else if (size < 1 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024) { //如果小于0.1EB转化成PB
		 data = (size / (1024 * 1024 * 1024 * 1024 * 1024)).toFixed(2) + " PB";
	 } else if (size < 1 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024) { //如果小于0.1ZB转化成EB
		 data = (size / (1024 * 1024 * 1024 * 1024 * 1024 * 1024)).toFixed(2) + " EB";
	 } else if (size < 1 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024) { //如果小于0.1YB转化成ZB
		 data = (size / (1024 * 1024 * 1024 * 1024 * 1024 * 1024 * 1024)).toFixed(2) + " ZB";
	 }
	 var sizestr = data + "";
	 var len = sizestr.indexOf("\.");
	 var dec = sizestr.substr(len + 1, 4);
	 if (dec == "00") { //当小数点后为00时 去掉小数部分
		 return sizestr.substring(0, len) + sizestr.substr(len + 3, 2);
	 }
	 return sizestr;
 }

 /**
  * 监测输入路径是否符合规则
  */
 var checkPath = function (value, flag) {
	 var value = value.replace(/\//gi, "/"); //正则替换  把输入的\替换成/

	 var re1 = '((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(?![\\d])'; // IPv4 IP Address 1
	 var re2 = '(:)'; // Any Single Character 1
	 var re3 = '((?:\\/[\\w\\.\\-]+)+)'; // Unix Path 1
	 var re4 = '(.*)';
	 var path = /^([a-zA-Z]:|\\\\[^\\\\/:*?"<>|]+)(\\[^\\\\/:*?"<>|]+)*\\?$/;
	 var linux_path = '^(\\/[\\w\\-]+)+\\/?$';//linux路径检测
	 var cn_word = '[\u4e00-\u9fa5]'; //中文检测
	 var p1 = new RegExp(re1 + re2 + re3, ["i"]);
	 //	var p2 = new RegExp(re4+re2+re3, ["i"]);
	 var p2 = new RegExp(path);
	 var p3 = new RegExp(linux_path);
	 var p4 = new RegExp(cn_word, ["g"]);
	 if (flag == 'Linux') { //如果为linux系统则只能输入linux系统目录
		 return !!(p3.exec(value) && !p4.exec(value))
	 } else if (flag == 'Windows') {
		 var dd = p2.exec(value);
		 return !!(p2.exec(value) && !p4.exec(value))
	 }
	 return !!((p2.exec(value) || p3.exec(value)) && !p4.exec(value));

 }
 var checkFilePath = function (value, flag) {
	 var value = value.replace(/\//gi, "/"); //正则替换  把输入的\替换成/

	 var re1 = '((?:(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?)\\.){3}(?:25[0-5]|2[0-4][0-9]|[01]?[0-9][0-9]?))(?![\\d])'; // IPv4 IP Address 1
	 var re2 = '(:)'; // Any Single Character 1
	 var re3 = '((?:\\/[\\w\\.\\-]+)+)'; // Unix Path 1
	 var re4 = '(.*)';
	 var path = /^([a-zA-Z]:|\\\\[^\\\\/:*?"<>|]+)(\\[^\\\\/:*?"<>|]+)*\\?$/;
	 var linux_path = '^\\/([\\w\\.\\-]+\\/?)+$';//linux路径检测
	 var cn_word = '[\u4e00-\u9fa5]'; //中文检测
	 var p1 = new RegExp(re1 + re2 + re3, ["i"]);
	 //	var p2 = new RegExp(re4+re2+re3, ["i"]);
	 var p2 = new RegExp(path);
	 var p3 = new RegExp(linux_path);
	 var p4 = new RegExp(cn_word, ["g"]);
	 if (flag == 'Linux') { //如果为linux系统则只能输入linux系统目录
		 return !!(p3.exec(value) && !p4.exec(value));
	 } else if (flag == 'Windows') {
		 var dd = p2.exec(value);
		 return !!(p2.exec(value));
	 }
	 return !!((p2.exec(value) || p3.exec(value)) && !p4.exec(value));

 }

 //判断数组中是否有相同元素存在
 var arrayIsRepeat = function (arr) {
	 var hash = {};
	 for (var i in arr) {
		 if (hash[arr[i]]) {
			 return true;
		 }
		 hash[arr[i]] = true; // 不存在该元素，则赋值为true，可以赋任意值，相应的修改if判断条件即可
	 }
	 return false;
 }

 var ipV4V6 = function (value) {
	 //var returns = /^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value);
	 //var returns2 = /^([\da-fA-F]{1,4}:){6}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^::([\da-fA-F]{1,4}:){0,4}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:):([\da-fA-F]{1,4}:){0,3}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){2}:([\da-fA-F]{1,4}:){0,2}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){3}:([\da-fA-F]{1,4}:){0,1}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){4}:((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){7}[\da-fA-F]{1,4}$|^:((:[\da-fA-F]{1,4}){1,6}|:)$|^[\da-fA-F]{1,4}:((:[\da-fA-F]{1,4}){1,5}|:)$|^([\da-fA-F]{1,4}:){2}((:[\da-fA-F]{1,4}){1,4}|:)$|^([\da-fA-F]{1,4}:){3}((:[\da-fA-F]{1,4}){1,3}|:)$|^([\da-fA-F]{1,4}:){4}((:[\da-fA-F]{1,4}){1,2}|:)$|^([\da-fA-F]{1,4}:){5}:([\da-fA-F]{1,4})?$|^([\da-fA-F]{1,4}:){6}:$/i.test(value);
	 var ipRule = /^(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){6}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^::([\da-fA-F]{1,4}:){0,4}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:):([\da-fA-F]{1,4}:){0,3}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){2}:([\da-fA-F]{1,4}:){0,2}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){3}:([\da-fA-F]{1,4}:){0,1}((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){4}:((25[0-5]|2[0-4]\d|[01]?\d\d?)\.){3}(25[0-5]|2[0-4]\d|[01]?\d\d?)$|^([\da-fA-F]{1,4}:){7}[\da-fA-F]{1,4}$|^:((:[\da-fA-F]{1,4}){1,6}|:)$|^[\da-fA-F]{1,4}:((:[\da-fA-F]{1,4}){1,5}|:)$|^([\da-fA-F]{1,4}:){2}((:[\da-fA-F]{1,4}){1,4}|:)$|^([\da-fA-F]{1,4}:){3}((:[\da-fA-F]{1,4}){1,3}|:)$|^([\da-fA-F]{1,4}:){4}((:[\da-fA-F]{1,4}){1,2}|:)$|^([\da-fA-F]{1,4}:){5}:([\da-fA-F]{1,4})?$|^([\da-fA-F]{1,4}:){6}:$/;
	 var ipv6c = /^(([a-f0-9]{1,4}:){1,6}:|:(:[a-f0-9]{1,4}){1,6}|::)$/; // 校验简写的ipv6
	 return ipRule.test(value) || ipv6c.test(value);
	 //return returns || returns2;
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
				if (['/api/v1/system/config/base_info', '/api/v1/system/auth/base_info'].indexOf(url) !== -1) {
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

 //ajax公共请求接口调用
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
			 XMLHttpRequest.setRequestHeader("x-api-version", "1.0-rev0"); //当前接口版本
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

 /**
  * 统一初始化数字输入框
  * @param dom	保留策略输入框dom对象
  * @param oldeValue	修改初始化初始值
  */
 var initReserveSpinner = function (dom, oldeValue = 0) {
	 var value = 30;
	 if (oldeValue != 0) {
		 value = oldeValue;
	 }
	 dom.spinner({
		 value: value,
		 step: 5,
		 min: 1,
		 max: 9999
	 });
 }

 //重连认证返回超时信息提示
 var relogin = function (resData) {
	 // UIToastr.showWarning('认证信息超时','请求认证信息超时，请重新访问');
	 return;

 }

 //测试
 //pAjaxRequest({}, "/api/v1/login", "GET", relogin, false);


 /**
  *
  */

 //---------统一部分策略信息开始--------------
 //----------统一时间策略
 //时间策略传参转换,将以前老的返回参数更新为新版PHP接收的参数格式,部分参数名字有变化
 var unifyTimeStrategy = function (data) {
	 var info = {};
	 //初始化数据
	 info.backup_type = 0;
	 info.once_start_time = "";
	 info.full_backup = {};
	 info.incremental_backup = {};
	 info.differential_backup = {};
	 info.forever_incremental = {};
	 if (data == "" || data == undefined || data == null) {
		 //如果没数据直接返回空
		 return info;
	 }
	 if (data.type == "oncetime") {
		 //一次性备份
		 info.backup_type = 2;
		 info.once_start_time = data.datetime;
		 return info;
	 } else {
		 //有备份模式的
		 info.backup_type = 1;
		 //完全备份
		 if (data.fullInfo && typeof data.fullInfo === 'object' && Object.keys(data.fullInfo).length !== 0) {
			 //获取备份类型
			 info.full_backup.strategy_type = data.fullInfo.type;
			 info.full_backup.days = data.fullInfo.days;
			 info.full_backup.start_time = data.fullInfo.startTime;
			 info.full_backup.roll_flag = data.fullInfo.rollFlag;
			 info.full_backup.roll_interval = data.fullInfo.rollInterval;
			 info.full_backup.roll_end_time = data.fullInfo.endTime;
			 info.full_backup.frequency = data.fullInfo.frequency ? data.fullInfo.frequency : "";
			 info.full_backup.des = data.fullInfo.des;
		 }
		 //增量备份
		 if (data.incrInfo && typeof data.incrInfo === 'object' && Object.keys(data.incrInfo).length !== 0) {
			 //获取备份类型
			 info.incremental_backup.strategy_type = data.incrInfo.type;
			 info.incremental_backup.days = data.incrInfo.days;
			 info.incremental_backup.start_time = data.incrInfo.startTime;
			 info.incremental_backup.roll_flag = data.incrInfo.rollFlag;
			 info.incremental_backup.roll_interval = data.incrInfo.rollInterval;
			 info.incremental_backup.roll_end_time = data.incrInfo.endTime;
			 info.incremental_backup.frequency = data.incrInfo.frequency ? data.incrInfo.frequency : "";
			 info.incremental_backup.des = data.incrInfo.des;
		 }
		 //差异备份
		 if (data.diffInfo && typeof data.diffInfo === 'object' && Object.keys(data.diffInfo).length !== 0) {
			 //获取备份类型
			 info.differential_backup.strategy_type = data.diffInfo.type;
			 info.differential_backup.days = data.diffInfo.days;
			 info.differential_backup.start_time = data.diffInfo.startTime;
			 info.differential_backup.roll_flag = data.diffInfo.rollFlag;
			 info.differential_backup.roll_interval = data.diffInfo.rollInterval;
			 info.differential_backup.roll_end_time = data.diffInfo.endTime;
			 info.differential_backup.frequency = data.diffInfo.frequency ? data.diffInfo.frequency : "";
			 info.differential_backup.des = data.diffInfo.des;
		 }
		 //永久增量备份
		 if (data.pIncrInfo && typeof data.pIncrInfo === 'object' && Object.keys(data.pIncrInfo).length !== 0) {
			 //获取备份类型
			 info.forever_incremental.strategy_type = data.pIncrInfo.type;
			 info.forever_incremental.days = data.pIncrInfo.days;
			 info.forever_incremental.start_time = data.pIncrInfo.startTime;
			 info.forever_incremental.roll_flag = data.pIncrInfo.rollFlag;
			 info.forever_incremental.roll_interval = data.pIncrInfo.rollInterval;
			 info.forever_incremental.roll_end_time = data.pIncrInfo.endTime;
			 info.forever_incremental.frequency = data.pIncrInfo.frequency ? data.pIncrInfo.frequency : "";
			 info.forever_incremental.des = data.pIncrInfo.des;
		 }
		 return info
	 }
 }
 //----------限速策略
 //统一限速策略
 var unifySpeedStrategy = function (data) {
	 var info = [];
	 if (!data || data == "" || data == null || data == undefined || data.length == 0) {
		 return info;
	 }
	 for (let i = 0; i < data.length; i++) {
		 switch (data[i].type) {
			 case 1:
			 case 2:
			 case 3:
				 let limitSpeed = {};
				 limitSpeed.speed_type = data[i].type;
				 limitSpeed.start_time = data[i].startTime;
				 limitSpeed.end_time = data[i].endTime;
				 limitSpeed.days = data[i].days;
				 limitSpeed.value = data[i].value;
				 info.push(limitSpeed);
				 break;
			 case 4:
				 //如果为永久限速
				 let foreverSpeed = {};
				 foreverSpeed.speed_type = data[i].type;
				 foreverSpeed.value = data[i].value;
				 info.push(foreverSpeed);
				 break;
		 }
	 }
	 return info;
 }

 /**
  * 获取配置的限速策略
  * @returns {{}}
  */
 var getSpeedStrategyInfo = function (){
	 let info = {};
	 info['level'] = $('#tasklevelselect').val();
	 info['type'] = parseInt($('#speedtypeselect').val());
	 info['speedInfo'] = [];
	 if (info['type'] === 1) {
		 // 选择策略
		 var selectedRow = $('.strategy-table #table').bootstrapTable('getSelections');
		 if (selectedRow.length === 1) {
			 info['uuid'] = selectedRow[0].uuid
			 info['name'] = selectedRow[0].name
			 info['strategy_type'] = selectedRow[0].type
			 info['speedInfo'] = [{'des': selectedRow[0].detail}]
		 }
	 } else {
		 // 自定义
		 info['speedInfo'] = $('#speedstrategy').getSpeedStrategyConfigFinal();
	 }
	 return info;
 }

 //----------存储策略
 var unifyStorageStrategy = function (data) {
	 var info = {};
	 if (data == "" || data == undefined || data == null || Object.keys(data).length == 0) {
		 return info;
	 }
	 //重复数据删除
	 if (typeof data.deduplication !== "undefined" && data.deduplication !== null) {
		 info.deduplication_flag = data.deduplication;
	 }
	 //压缩存储
	 if (typeof data.compress !== "undefined" && data.compress !== null) {
		 info.compress_flag = data.compress;
	 }
	 //数据加密
	 if (typeof data.encrypt !== "undefined" && data.encrypt !== null) {
		 info.encrypt_flag = data.encrypt;
	 }
	 //自动生成密码
	 if (typeof data.password_auto_flag !== "undefined" && data.password_auto_flag !== null) {
		 info.auto_create_password_flag = data.password_auto_flag;
	 }
	 //数据加密密码
	 if (typeof data.password !== "undefined" && data.password !== null) {
		 info.password = data.password;
	 }
	 //存储块大小
	 if (typeof data.blocksize !== "undefined" && data.blocksize !== null) {
		 info.block_size = data.blocksize;
	 }
	 return info;
 }

 //----------保留策略
 var UnifyReserveStrategy = function (data) {
	 var info = {};
	 if (data == "" || data == undefined || data == null || Object.keys(data).length == 0) {
		 return info;
	 }
	 //保留类型
	 if (typeof data.type !== "undefined" && data.type !== null) {
		 info.reserved_type = data.type;
	 }
	 //保留值
	 if (typeof data.value !== "undefined" && data.value !== null) {
		 info.value = data.value;
	 }
	 //自动归档
	 if (typeof data.auto_archive_flag !== "undefined" && data.auto_archive_flag !== null) {
		 info.auto_archive_flag = data.auto_archive_flag;
	 }
	 //GFS
	 if (typeof data.gfs_strategy_item_list !== "undefined" && data.gfs_strategy_item_list !== null) {
		 info.gfs_reserved_strategy = [];
		 if (data.gfs_strategy_item_list.length != 0) {
			 for (let i = 0; i < data.gfs_strategy_item_list.length; i++) {
				 let gfe_each = {};
				 gfe_each.gfs_reserved_type = data.gfs_strategy_item_list[i].level1_type;
				 gfe_each.gfs_reserved_start = data.gfs_strategy_item_list[i].level2_type;
				 gfe_each.gfs_reserved_value = data.gfs_strategy_item_list[i].retention_num;
				 info.gfs_reserved_strategy.push(gfe_each);
			 }
		 }
	 }
	 return info;
 }
 //---------统一部分策略信息结束--------------



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

//处理删除按钮样式切换
const modifyDelStyle = function (tableid, opid) {
	var selectedRow = $('#' + tableid).bootstrapTable('getSelections');
	if (selectedRow.length < 1) {
		$('#' + opid).addClass('exch-forbid-event').removeClass('green-haze');
		$('#' + opid).parent().css({"cursor": "not-allowed"});
		$('#' + opid).css({"color": "#d7d9db"});
	} else {
		$('#' + opid).removeClass('exch-forbid-event').addClass('green-haze');
		$('#' + opid).parent().css({"cursor": "pointer"});
		$('#' + opid).css({"color": "#FFFFFF"});
	}
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
  * 全局派发更新方法
  * @param event 派发更新的事件名
  * @param data 回调函数传递的数据
  * @returns 返回值
  */
 window.$emit = function(event, data) {
	 const fn = (ev, d) => {
		 const len = handlerGather[ev].length;
		 for (let i = 0; i < len; i++) {
			 const ele = handlerGather[ev][i];
			 // 派发更新数据
			 ele(d);
		 }
	 };
	 if (Array.isArray(event)) {
		 // 监听的事件是一个数组时
		 event.forEach((item) => {
			 fn(item, data);
		 });
	 } else {
		 // 监听的事件只有一个时
		 fn(event, data);
	 }
	 return window;
 };

 /**
  * 全局监听方法
  * @param event 监听的事件名
  * @param fn 回调函数
  * @returns 返回值
  */
 window.$on = function(event, fn) {
	 if (Array.isArray(event)) {
		 // 监听的事件是一个数组时：遍历取出每个事件单独监听
		 event.forEach((item) => {
			 window.$on(item, fn);
		 });
	 } else {
		 // 监听的事件不为数组时
		 (handlerGather[event] || (handlerGather[event] = [])).push(fn);
	 }
	 return window;
 };

 /**
  * 销毁全局自定义事件
  * @param event 需要销毁的事件名
  * @returns
  */
 window.$off = function(event) {
	 // 事件不存在则终止执行
	 if (!handlerGather[event]) {
		 return;
	 }
	 // 销毁的事件为数组时
	 if (Array.isArray(event)) {
		 event.forEach((item) => {
			 if (handlerGather[event]) {
				 // 销毁对应事件
				 handlerGather[event] = [];
			 }
		 });
	 } else {
		 // 销毁的事件不为数组时
		 handlerGather[event] = [];
	 }
	 return window;
 };

 /**
     * 匹配 start 和 end 之间的内容，并替换为指定的内容
     * @param {*} str 要处理的字符串
     * @param {*} start 起始字符
     * @param {*} end 结束字符
     * @param {*} replaceWith 替换字符串
     */
 const replaceBetweenStartEnd = (str, start, end, replaceWith) => {
	// 使用了非贪婪匹配（*?），以防止匹配到最远的 end 字符
	const regex = new RegExp('\\' + start + '([^' + '\\' + end + ']*)' + '\\' + end, 'g');

	// 使用 replace() 方法替换匹配到的内容
	return str.replace(regex, start + replaceWith + end);
}

//获取源列表名称(文件、nas、hadoop、对象存储用)
var getSrcListName = function (data) {
	var des = '';
	var num = 0;
	if (data.taskTypeFlag == 1) {//备份
		num = parseInt(data.des_module_type);
	} else {//恢复
		num = parseInt(data.src_sub_module_type);
	}
	switch (parseInt(num)) {
		case 1:   //fs
			des = '<i class="viconfont vicon-ge_backup_host "></i>' + LANG.UI_COPY_DETAIL_FS_LIST;
			break;
		case 2:   //nas
			des = '<i class="viconfont vicon-nasmanager"></i>' + LANG.UI_COPY_DETAIL_NAS_LIST;
			break;
		case 3:   //hadoop
			des = '<i class="viconfont vicon-ge_backup_host "></i>' + LANG.UI_HADOOP_CLUSTER_LIST;
			break;
		case 4:   //obs
			des = '<i class="viconfont vicon-ge_backup_host "></i>' + LANG.UI_PLATFORM_DES_OBS_list;
			break;
	}
	$('#srcList').html(des);
}
var safeData = function (wore, virus_scan, complete) {
	let safe_config_strategy= {}
	if(wore) {
		safe_config_strategy.worm_flag = wore.worm_flag;
		safe_config_strategy.worm_protection_time = wore.worm_protection_time;
	} else {
		safe_config_strategy.worm_flag = 0;
		safe_config_strategy.worm_protection_time = 0
	}
	if(virus_scan) {
		safe_config_strategy.virus_scan_flag = virus_scan.virus_scan_flag;
		safe_config_strategy.virus_scan_config_list = JSON.stringify(virus_scan.virus_scan_config_list);
	} else {
		safe_config_strategy.virus_scan_flag = 0;
		safe_config_strategy.virus_scan_config_list = '';
	}
	if(complete) {
		safe_config_strategy.integrity_check_flag = complete.integrity_check_flag;
		safe_config_strategy.integrity_check_config = complete.integrity_check_config;
	} else {
		safe_config_strategy.integrity_check_flag = 0;
		safe_config_strategy.integrity_check_config = {};
	}
	return safe_config_strategy
}

/**
 * 获取授权信息
 * @param {*} module 模块类型【文件file 虚拟机vm 数据库oracle 操作系统os nas hadoop 对象存储obs 实时容灾cdp exchange 私有云private_cloud 公有云cloud 实时接管vol_cdp 内嵌embd 跨平台恢复v2v】
 * @param {*} showMetronic 是否显示请求授权接口的加载动画
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
const getModuleAuthInfo = ({module, currentUse = 1, showMetronic = true, showAlertMsg = true, judge = true,uuids=[],task_uuid = ''}) => {
	return new Promise(resolve => {
		if (showMetronic) {
			Metronic.blockUI({target: 'body', animate: true});
		}
		let reqData = {
			module,
			type: 'a',
			uuids: uuids,
			task_uuid: task_uuid,
		};
		pAjaxRequest(reqData, `/api/v1/system/auth/base_info`, `GET`, res => {
			if (showMetronic) {
				Metronic.unblockUI('body');
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
			if (!res.data.license_flag) {  // 授权过期了
				UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
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
 * 获取备份节点资源限制参数
 * 用于各模块备份页面-高级配置-过载保护处显示
 */
const initResourceLimit = (node_uuid_list) => {
	const WEEK_DES_MAP = {
		1: LANG.UI_PUBLIC_NUMBER_ONE,
		2: LANG.UI_PUBLIC_NUMBER_TWO,
		3: LANG.UI_PUBLIC_NUMBER_THREE,
		4: LANG.UI_PUBLIC_NUMBER_FOUR,
		5: LANG.UI_PUBLIC_NUMBER_FIVE,
		6: LANG.UI_PUBLIC_NUMBER_SIX,
		7: LANG.UI_PUBLIC_NUMBER_SEVEN,
	}
	Metronic.blockUI({target: '#resourceLimitModal', animate: true});
	var options = {
		pagination: true,
		pageList: [5, 10, 25, 50],
		detailView: false,
		resizable: false, //可变宽度
		vin_params:function(){
			return {node_uuid_list: node_uuid_list};
		},
		vin_url: "/api/v1/nodes/resources_limit/batch",
		vin_method: "GET",
		onPostBody: (data) => {
			$('#nodeLimitTable th[data-field="max_task_running_num"]').css('width', '25%');
			$('#nodeLimitTable th[data-field="time"]').css('width', '42%');
			if (data.length <= 5) {
				$('.node-limit-form .fixed-table-pagination').hide();
			} else {
				$('.node-limit-form .fixed-table-pagination').show();
			}

		},
		columns: [
			{
				field: 'name',
				title: LANG.UI_NODE_REMOTE_NODE_NAME,
				sortable: false,
				align: 'center',
				formatter: function (value, data, row) {
					if (!data.node_config_flag || !data.init_flag) {
						return '--';
					}
					let name = (data.node_nickname != "" ? data.node_nickname : data.host_name) + `(` + data.ip + `)`;
					return `<span title="`+ name +`">`+ name +`</span>`;
				}
		},
			{
				field: 'max_task_running_num',
				title: LANG.UI_NODE_RESOURCE_LIMIT_TASK_MAX_CONCURRENT,
				sortable: false,
				align: 'center',
				formatter: function (value, data, row) {
					if (!data.node_config_flag || !data.init_flag) {
						return '--';
					}
					return data.max_task_running_num;
				}
		},
			{
				field: 'time',
				title: LANG.UI_NODE_RESOURCE_LIMIT_TASK_PROHIBIT_PERIOD,
				sortable: false,
				align: 'center',
				formatter: function (value, data, row) {
					let des = '';
					if (!data.node_config_flag || !data.init_flag) {
						return '--';
					}
					let timeType = data.prohibit_time_type;
					let timeList = data.prohibit_time_info;
					switch (timeType) {
						case 1: // 每天
							des = timeList.map(item => {
								return `
										${LANG.UI_STRATEGY_DAY}${item.start_time}${LANG.UI_STRATEGY_START}，
										${item.end_time}${LANG.UI_STRATEGY_END}
										;`}).join('');
							break;
						case 2: // 每周
							des = timeList.map(item => {
								return `
										${LANG.UI_STRATEGY_WEEK}
										${item.days.map((v, i) => v === 1 ? WEEK_DES_MAP[i + 1] : null).filter(day => day !== null).join('，')}
										${item.start_time}${LANG.UI_STRATEGY_START}，
										${item.end_time}${LANG.UI_STRATEGY_END}
										;` }).join('');
							break;
						case 3: // 每月
							des = timeList.map(item => {
								return `
										${LANG.UI_STRATEGY_MONTH}
										${item.days.map((v, i) => v === 1 ? i + 1 : null).filter(day => day !== null).join(',')}
										${item.start_time}${LANG.UI_STRATEGY_START}，
										${item.end_time}${LANG.UI_STRATEGY_END}
										;` }).join('');
							break;
						case 4: // 自定义
							des = timeList.map(item => {
								return `
										${item.start_time}${LANG.UI_STRATEGY_START}，
										${item.end_time}${LANG.UI_STRATEGY_END};` }).join('');
							break;
						default:
							break;
					}
					let title = des.replace(/\s+/g, ' ').replace(/;/g, '\n');
					if (des.replace(/\s+/g, '') == "") {
						return `--`;
					}
					return `<span title = "`+ title +`">` + des + `</span>`;
				}
		},
		],
	}
	$('#nodeLimitTable').bootstrapTable('destroy').baseTableConfig().init(options);
};

/**
 * 退出登录/锁定屏幕
 * type 1表示退出登录，0表示锁屏
 * */
var loginOut = function (type = 0){
	pAjaxRequest({out: type}, '/api/v1/login_out', "POST", function (result) {
		if (result.code == 0) {
			UIToastr.showSuccess(result.message, LANG.UI_DATACENTER_SUCCESS);
			sessionStorage.removeItem('current_job_filter_params');
			sessionStorage.removeItem('history_job_filter_params');
			var url = './lock.php';
			if (type > 0) {
				url = './login.php';
			}
			setTimeout(function (){
				window.location.href = url;
			}, 1000)
		} else {
			return UIToastr.showError(result.message, LANG.UI_DATACENTER_FAILURE);
		}
	}, false);
}

/**
 * 秒数转换成天时分秒
 */
const secondsToTime = (totalSeconds) => {
	const days = Math.floor(totalSeconds / 86400);
	const hours = Math.floor((totalSeconds % 86400) / 3600);
	const minutes = Math.floor((totalSeconds % 3600) / 60);
	const seconds = totalSeconds % 60;
	return { days, hours, minutes, seconds };
}

/**
 * 校验时间输入并返回详细结果
 * @param {number|string} d 天数
 * @param {number|string} daysLimit 天数限制
 * @param {number|string} h 小时数
 * @param {number|string} m 分钟数
 * @param {number|string} s 秒数
 * @returns {object} { isValid: boolean, message: string }
 */
const validateTimeInputDetailed = (d,daysLimit, h, m, s) => {
	const result = { isValid: true, message: LANG.UI_PUBLIC_TIME_IS_VALID };
	const validateUnit = (value, unitName, max) => {
		const num = Number(value);

		if (value === '' || value === null || value === undefined) {
			return 0; // 空输入视为0
		}

		if (isNaN(num)) {
			result.isValid = false;
			result.message = `${unitName}`+ LANG.UI_PUBLIC_NUMERIC_ONLY;
			return NaN;
		}

		if (!Number.isInteger(num)) {
			result.isValid = false;
			result.message = `${unitName}` + LANG.UI_PUBLIC_INTEGERS_ONLY;
			return NaN;
		}

		if (num < 0) {
			result.isValid = false;
			result.message = `${unitName}` + LANG.UI_PUBLIC_NO_NEGATIVE_VALUES;
			return NaN;
		}

		if (max !== undefined && num >= max) {
			result.isValid = false;
			result.message = `${unitName}`+ LANG.UI_PUBLIC_VALUE_MUST_BE_LESS_THAN +`${max}`;
			return NaN;
		}

		return num;
	};

	const days = validateUnit(d, LANG.UI_BACKUP_DAY,daysLimit);
	const hours = validateUnit(h, LANG.UI_PUBLIC_HOURS, 24);
	const minutes = validateUnit(m, LANG.UI_PUBLIC_MINUTES, 60);
	const seconds = validateUnit(s, LANG.UI_PUBLIC_SECONDS, 60);

	// 如果前面已经有错误，直接返回
	if (!result.isValid) return result;

	// 检查所有值为0的情况
	if (days === 0 && hours === 0 && minutes === 0 && seconds === 0) {
		result.isValid = false;
		result.message = LANG.UI_PUBLIC_REQUIRE_NON_ZERO_TIME_UNIT;
	}

	return result;
}

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
