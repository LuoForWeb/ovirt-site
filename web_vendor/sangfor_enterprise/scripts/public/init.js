var VinchinInit = (()=>{
	const _TASKINTERVAL = 5000;		//任务获取延迟时间	5秒钟
    const initThreePartyComponentsConfig = () => {
        setSiderbar(); //深信服单独处理的部分
        Metronic.init(); // init metronic core components
		Layout.init(); // init current layout
        QuickSidebar.init(); // init quick sidebar
        UIToastr.init();	//init toastr msg

        var body = $('body');
		// handle sidebar show/hide
		$('.page-sidebar').hover(function (e) {
			var sidebar = $('.page-sidebar');
			var sidebarMenu = $('.page-sidebar-menu');
			if(!sidebarMenu.hasClass('page-sidebar-menu-closed')) return;
			$(".sidebar-search", sidebar).removeClass("open");
				//展开
				$(".scopyright").show();
				body.removeClass("page-sidebar-closed");
				if ($.cookie) {
					$.cookie('sidebar_closed', '0');
				}
			$(window).trigger('resize');
		}, function (e) {
			  var sidebar = $('.page-sidebar');
			  var sidebarMenu = $('.page-sidebar-menu');
			  if(!sidebarMenu.hasClass('page-sidebar-menu-closed')) return;
			  $(".sidebar-search", sidebar).removeClass("open");
			  //收起
			  $(".scopyright").hide();
			  body.addClass("page-sidebar-closed");
			  if (body.hasClass("page-sidebar-fixed")) {
				  sidebarMenu.trigger("mouseleave");
			  }
			  if ($.cookie) {
				  $.cookie('sidebar_closed', '1');
			  }
			  $(window).trigger('resize');
		});
    }

    /**
	 * 初始化配置信息
	 */
	const initConfig = () => {
        pAjaxRequest({}, '/api/v1/system/config/base_info', "GET", function (result) {
			var config = result.data;
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
			var permission = config.permission;
			var list = [];
			for(var i in permission){
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

			var lang = CONF.LANG_CONF[config.language];
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
		},false);
    }
    //设置深信服顶部和侧边栏样式
    const setSiderbar = () => {
        var pageurl = window.location.href;
		var liarr = $(".sangfor-nav li");
		for(var i = 0; i < liarr.length; i++) {
			//url包含name则加上active样式
			if(pageurl.includes($(liarr[i]).attr("name"))) {
				$(liarr[i]).addClass("active").siblings().removeClass('active');
			}
		}
		/*****************导航样式***********************/
		//如果是深信服
		var urldd = localStorage.getItem('history_url');
		var navigationdd = localStorage.getItem('history_navigation');
		if(urldd && navigationdd){
			LOCATION(urldd, navigationdd);
		}
		//跳转选中侧边栏
		if(pageurl.includes('monitor.html')) {
			if(urldd && navigationdd){
				localStorage.removeItem('history_url');
				localStorage.removeItem('history_navigation');
			}else{
				selectSiderbar()
			}
		}if(pageurl.includes('monihis.html')) {
			if(urldd && navigationdd){
				localStorage.removeItem('history_url');
				localStorage.removeItem('history_navigation');
			}else{
				LOCATION('./content/platform/jobs/jobs.php', 'task', { tabId: 'historyLi' } );
			}
		}else if(pageurl.includes('dataprotect.html')) {
			if(urldd && navigationdd){
				localStorage.removeItem('history_url');
				localStorage.removeItem('history_navigation');
			}else{
				selectSiderbar()
			}
		}else if(pageurl.includes('resmanagement.html')) {
			//存储设备存在  选中存储设备，不存在选中第一个
			if(urldd && navigationdd){
				localStorage.removeItem('history_url');
				localStorage.removeItem('history_navigation');
			}else{
				selectSiderbar();
			}
		}else if(pageurl.includes('sysmanagement.html')) {
	
			if(urldd && navigationdd){
				localStorage.removeItem('history_url');
				localStorage.removeItem('history_navigation');
			}else{
				//系统配置存在  选中系统配置，不存在选中第一个
				selectSiderbar();
			}
		}else if(pageurl.includes('monitask.html')) {
			if(urldd && navigationdd){
				localStorage.removeItem('history_url');
				localStorage.removeItem('history_navigation');
			}else{
				LOCATION('./content/platform/alarm/alarm.php', 'alarm');
			}
		}else if(pageurl.includes('monisystem.html')) {
			if(urldd && navigationdd){
				localStorage.removeItem('history_url');
				localStorage.removeItem('history_navigation');
			}else{
				LOCATION('./content/platform/alarm/alarm.php?tab=1', 'alarm', { tabId: 'system_alarm' });
			}
		}else if(pageurl.includes("resmanagementclient.html")) {//跳到客户端
			if(urldd && navigationdd){
				localStorage.removeItem('history_url');
				localStorage.removeItem('history_navigation');
			}else{
				LOCATION('/content/client/client.php', 'client');
			}
		}else if(pageurl.includes("resmanagementvcenter.html")) {//跳到虚拟化中心
			if(urldd && navigationdd){
				localStorage.removeItem('history_url');
				localStorage.removeItem('history_navigation');
			}else{
				LOCATION('./content/vm/add_vcenter.php', 'vcenter_manager');
			}
		}else if(pageurl.includes("resmanagementnas.html")) {//跳转到nas设备管理
			if(urldd && navigationdd){
				localStorage.removeItem('history_url');
				localStorage.removeItem('history_navigation');
			}else{
				LOCATION('./content/nas/nasmanager.php', 'nasmanager');
			}
		}else if(pageurl.includes("node_manager.html")) {//跳转到备份节点
			if(urldd && navigationdd){
				localStorage.removeItem('history_url');
				localStorage.removeItem('history_navigation');
			}else{
				LOCATION('./content/platform/node/node_manager.php', 'node_manager');
			}
		}
        //首页单独处理顶部样式
        if(pageurl.includes('index.html') || window.location.pathname == "/") {
			$(liarr[0]).addClass("active").siblings().removeClass('active');
		}
		/*****************首页侧边栏css***********************/
		if(window.location.href.includes('?homepage') || window.location.href.includes('index.html')) {
			$(".page-content.sangfor-bgc").addClass("ml0");
		}
    }
    const selectSiderbar = () => {
        var lis = $(".page-sidebar-menu li");
        if(lis.length >= 2) {
            var path = $(".page-sidebar-menu li").eq(1).find("a.ajaxify").attr("href");
            var name = $(".page-sidebar-menu li").eq(1).find("a.ajaxify").attr("name");
            LOCATION(path, name);
        }
    }
	var initAlarmTips = function(){
		var getAlarmInfo = function(){
			$.post(CONF.AJAXPATH, {m:CONF.M.ALARM,f:'getSurveyNoticeInfo',p:{}}, function(d){
				setAlarmInfo(d);
			})
				.complete(function() {
					setTimeout(getAlarmInfo, _TASKINTERVAL);});
		}
		getAlarmInfo();

	}
	var setAlarmInfo = function(d){
	    var d = JSON.parse(d);
		$('.badgemark').remove();
		//初始化告警提示
		$('#alarmtask').html(d.alarm.task.error + d.alarm.task.warn);
		$('#alarmsystem').html(d.alarm.system.error + d.alarm.system.warn);

		var total = d.alarm.task.error + d.alarm.task.warn + d.alarm.system.error + d.alarm.system.warn;
		var errorTotal = d.alarm.task.error + d.alarm.system.error;
		var badgeType = "badge-warning";
		var tips = "";
		if(errorTotal > 0){
			badgeType = "badge-danger";
		}
		if(total > 0){
			var tips = '<span class="badge ' + badgeType + ' badgemark">' + total + '</span>';
		}
		$('#alarmtotal').find('span').remove();
		$('#alarmtotal').append(tips);

		setAlarmLabelIconClour(".alarmhreftask", d.alarm.task);
		setAlarmLabelIconClour(".alarmhrefsystem", d.alarm.system);


		//初始化任务提示
		$('#topcurrenttask').html(d.task.current);
		$('#tophistorytask').html(d.task.history);

		//初始化菜单提示
		var storage_manager = $('.page-sidebar-menu').find('a[name=storage_manager]');
		var authorization_module = $('.page-sidebar-menu').find('a[name=authorization_module]');
		var resource_manager = $('.page-sidebar-menu').find('a[name=resmanagement]');
		var system_manager = $('.page-sidebar-menu').find('a[name=sysmanagement]');

		if(d.storage){
			//管理备份存储
			var tips = '<span class="badge badge-warning badgemark">' + d.storage + '</span>';
			storage_manager.append(tips);
			//父级-系统管理
			resource_manager.append(tips);
		}
		if(d.lisence){
			//系统授权
			var tips = '<span class="badge badge-danger badgemark">' + d.lisence + '</span>';
			authorization_module.append(tips);
			//父级-系统管理
			system_manager.append(tips);
		}
	}

	/**
	 * 设置顶部告警ICON的样式
	 * 无告警:	label-info
	 * 有警告告警:	label-warning
	 * 有错误告警:	label-danger
	 */
	var setAlarmLabelIconClour = function(id, data){
		var clourSytel = "label-info";
		if(data.warn > 0){
			clourSytel = "label-warning";
		}
		if(data.error > 0){
			clourSytel = "label-danger";
		}
		$(id).find('.label-icon').removeClass().addClass("label label-sm label-icon " + clourSytel);
	}
	var updateAlarmTips = function(){
	    
			$.post(CONF.AJAXPATH, {m:CONF.M.ALARM,f:'getSurveyNoticeInfo',p:{}}, function(d){
				setAlarmInfo(d);
			});	
	}

	//因为深信服这里跳转了页面之后获取不到DataBackupCenter 但是新页面又需要DataBackupCenter.updateTopAlarmTips方法,所以这里重写了一遍
	const getDataBackupCenter = () => {
		DataBackupCenter =  { 
			updateTopAlarmTips: function(){
				updateAlarmTips();
			},
		}
		
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
		})
	}
	/**
	 * 获取授权是否过期
	 */
	const initSystemAuthInfo = () => {
		pAjaxRequest({type: 'a'}, '/api/v1/system/auth/base_info', 'GET', (res) => {
			try {
				if (res.success) {
					CONF.AUTH_NOT_EXPIRED = res.data.license_flag;
				} else {
					// 默认给个true 未过期
					CONF.AUTH_NOT_EXPIRED = true;
				}
			} catch (error) {
				
			} finally {
				CONF.AUTH_NOT_EXPIRED = true;
			}
		});
	}

    return {
        init: () => {
			initSystemAuthInfo();
			initThreePartyComponentsConfig();
			initConfig();
			initGlobalObserverConfig();
			initAlarmTips();
			getDataBackupCenter();
			// updateAlarmTips();
            // 为所有ajax请求默认设置缓存
            $.ajaxSetup({
                cache: true
            });
			$('[data-toggle="tooltip"]').tooltip(); // 初始化页面导航栏中的tooltips
        }
    };
})();

jQuery(document).ready(function() {
    VinchinInit.init();
});
window.addEventListener('popstate', (e) => {
  //首页当时没有存history.pushState，所以state为null
  if(e.state == null){
	 location.href = '/index.html';
  }
});