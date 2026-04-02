var AuthorizationModule = function () {

	const initListener = ()=>{
		$('.top-title-right').popover({
			trigger:'hover',
		})
		//下载指纹文件
		$(".download_auth").on('click',function(){
			getThumbprint();
		})
		initUpload();
	}
	//获取指纹文件
	const getThumbprint = ()=>{
		var getfile = (res)=>{
			if(res.success&&res.data.length ==32){
				window.location.href = "/api/v1/system/auth/get/thumbprint/file?x-api-version=1.0-rev0";
			}else{
				UIToastr.showWarning(LANG.UI_LICENSE_GET_THUMBPRINT_INFO,res.message);
			}
		}
		pAjaxRequest({}, "/api/v1/system/auth/get/thumbprint", "GET",getfile,true);
	}
	//上传指纹文件
	const initUpload = ()=>{
		let uploadconfig = {
			url: '/api/v1/system/auth/upload_license',
			paramName: 'files',
			maxFiles: 1,
			maxFilesize: null, // 无限制
			addRemoveLinks: false,
			headers: {
				'x-api-version': '1.0-rev0'
			},
			success:function(file,response){
				console.log("response",response);
				if(response.success){
					UIToastr.showSuccess (LANG.UI_LICENSE_UPLOAD_AUTH_FILE_SUCCESS, LANG.UI_SETTINGS_ADD_STSTEM_LICENSE_TIPS);
					$('.auth-card').hide();
					$('.no-auth-card').hide();
					$('.second-div').hide();
					$('.auth-capacity').hide();
					$('#capacity_auth').hide();
					$('#realtime_capacity_auth').hide();
					$('#fs_capacity_auth').hide();
					initBasicInfo();
					setTimeout(function(){window.location = './loginout.php'}, 5000);
				}else{
					UIToastr.showWarning(LANG.UI_LICENSE_UPLOAD_AUTH_FILE_FAIL,response.message);
					this.removeAllFiles();
				}
			},
			error:function(file,response){
				UIToastr.showWarning(LANG.UI_LICENSE_UPLOAD_AUTH_FILE_FAIL)
			}
		};
		//上传授权文件
		const dropzone1 = new Dropzone('#upload_noauth',uploadconfig );
		dropzone1.on('addedfile', function(file) {
			file.previewElement.style.display = 'none';
		})
		const dropzone2 = new Dropzone('#upload_auth',uploadconfig );
		dropzone2.on('addedfile', function(file) {
			file.previewElement.style.display = 'none';
		})
	}
	//获取基本信息
	const initBasicInfo = ()=>{
		pAjaxRequest({}, '/api/v1/system/auth/basic/info', 'get', (res) => {
			if(!res.success){
				UIToastr.showWarning(LANG.UI_LICENSE_GET_AUTH_INFO_FAIL);
				return;
			}
			//设置基本信息
			let data =  res.data;
			let first_type =  data.first_type;  //授权类型 1容量 2定时实时分离 3其他
			let second_type =  data.second_type; // 1 2 3 4
			let status =  data.system_info.status; //系统授权状态 1授权 2 未授权 3过期 4 授权异常 
			$('#statusDes').html(data.system_info.statusDes);
			$('#expireDays').html(data.system_info.expireDays);
			$('#expireTime').html(data.system_info.expireTime);
			//如果系统初始化的时候没有status,则默认为未授权
			if(status == null){
				status = 2;
			}
			//设置左上角卡片颜色
			if(status == 1){
				if(data.system_info.trial == 1){
					$('#authCard').addClass('auth-trial')
					//试用授权
				}else if(data.system_info.trial == 2){
					//永久授权
					$('#authCard').addClass('auth-permanent')
				}
			}else if(status ==  2){
				$('#authCard').addClass('auth-noauth');
			}else if(status ==  3 || status ==  4){
				$('#authCard').addClass('auth-exception');
			}
			//设置右上角卡片
			if(status == 2){
				$('.no-auth-card').show();
			}else{
				$('.auth-card').show();
				$('.second-div').show();
				//右上角设置授权信息
				$('#customer').html(data.system_info.customer); //用户名称
				$('#software').html(getSoftwareDes(data.system_info.software, data.extension)); //软件版本
				//型号
				if(data.extension){
					//型号
					data.extension.softwareNameModel?$('#softwareNameModel').html(data.extension.softwareNameModel):$('#softwareNameModelSpan').hide();
					//产品名称
					if(data.extension.softwareNameDiy){
						$('#softwareNameDiy').html(data.extension.softwareNameDiy);
					};
				}
				//服务类型 服务到期时间	
				if(data.server_info.serviceFlag){
					$('#serviceType').html(data.server_info.serverType);
					$('#serviceTime').html(data.server_info.serverTime)
				}else{
					$('#serviceTypeSpan').hide();
					$('#serviceTimeSpan').hide();
					$('#license .card-info-left').css('line-height','24px');
				}
			}

			//设置容量授权卡片
			if(first_type == 1 || first_type == 2 || (first_type == 3 && second_type == 3) || (first_type == 3 && second_type == 4)){
				// $('.auth-capacity').show();
				if(first_type == 1){
					//容量授权
					setStorageinfo(data.module_info.capacity);
				}else if(first_type == 2){
					//定时实时容量授权
					setStorageinfo(data.module_info.storage)
					setvolStorageinfo(data.module_info.vol_capacity);
				}else {
					//文件系列容量授权
					setfileStorageinfo(data.file_capacity);
					//设置文件系列授权卡片
					let fsstr = '';
					// data.module_info.file = {};
					// data.module_info.nas = {};
					// data.module_info.hadoop = {};
					// data.module_info.obs = {};
					// data.module_info.file.show_flag = data.module_info.nas.show_flag = data.module_info.hadoop.show_flag =data.module_info.obs.show_flag  =true;
					if(data.show_flag.file){
						fsstr += '<div class="modellabel">'+ LANG.UI_FILE_FILE +'</div>'
					}
					if(data.show_flag.nas){
						fsstr += '<div class="modellabel">NAS</div>'
					}
					if(data.show_flag.hadoop){
						fsstr += '<div class="modellabel">Hadoop</div>'
					}
					if(data.show_flag.obs){
						fsstr += '<div class="modellabel">'+ LANG.UI_VISUAL_OBS + '</div>'
					}
					$('.file-capacity-label').html(fsstr);
				}
			}else {
				//隐藏容量授权模块信息
				$('.auth-capacity').hide();
			}
			//设置数量授权卡
			if(status != 2){
				initAuthoModule(data);
			}
	
		});
	}
	const setStorageinfo = (data)=>{
		// $("#capacity_auth").show();
		// 已用容量
		$("#capacity_auth_used_des").html(data.used_des)
		//总容量
		$("#capacity_auth_total_des").html(data.total_des);
		//可用容量
		$("#capacity_auth_valid").html(data.avail_des);
		let percent = calculatePercentage(data.used, data.total);
		let widthpercent = percent + "%";
		$("#capacity_auth_used_percent").html(widthpercent);
		$("#capacity_auth .progress-bar").css('width',percent+'%');
	}
	const setvolStorageinfo = (data)=>{
		$("#realtime_capacity_auth").show();
		// 已用容量
		$("#realtime_capacity_auth_used_des").html(data.used_des)
		//总容量
		$("#realtime_capacity_auth_total_des").html(data.total_des);
		//可用容量
		$("#realtime_capacity_auth_valid").html(data.avail_des);
		let percent = calculatePercentage(data.used, data.total);
		let widthpercent = percent + "%";
		$("#realtime_capacity_auth_used_percent").html(widthpercent);
		$("#realtime_capacity_auth .progress-bar").css('width',percent+'%');
	}
	const setfileStorageinfo = (data)=>{
		$("#fs_capacity_auth").show();
		// 已用容量
		$("#fs_capacity_auth_used_des").html(data.used_des)
		//总容量
		$("#fs_capacity_auth_total_des").html(data.total_des);
		//可用容量
		$("#fs_capacity_auth_valid").html(data.avail_des);
		let percent = calculatePercentage(data.used, data.total);
		let widthpercent = percent + "%";
		$("#fs_capacity_auth_used_percent").html(widthpercent);
		$("#fs_capacity_auth .progress-bar").css('width',percent+'%');
	}
	//初始化数量授权卡片信息
	const  initAuthoModule = (data)=>{
		var htmlStr = '';
		// 首先需要放主控服务器
		var nodestr = '';
		var first_type = data.first_type;
		if(data.master_info.node_num != 0){
			//节点不为0时才显示
			nodestr = '<div class="card-item-bottom">'+ LANG.UI_BACKUP_NODE +':'+'<span>'+data.master_info.node_num+'</span></div>'
		}
		// GMP显示不同
		// 定时备份
		// 异地副本
		// 实时备份
		// 数据复制
		// 数据验证
		var gmpModule = [];
		var moduleCount = 0;
		if (first_type == 1) {
			// 容量授权(显示备份容量卡片，没有定时和实时备份) --去掉数据验证 data_verify 去掉数据复制 data_copy
			gmpModule = ['backup_capacity','diff_site_copy','data_archive'];
		} else if (first_type == 2) {
			//容量区分定时和实时备份
			gmpModule = ['timed_capacity','real_time_capacity','diff_site_copy','data_archive'];
		} else {
			//去掉实时备份
			gmpModule = ['data_verify','diff_site_copy','data_archive'];
		}
			
		htmlStr += '<div class="card-item">'+
						'<div class="card-item-title">'+
							'<i class="viconfont vicon-zhukongfuwuqi"></i>'+
							'<span>'+ LANG.UI_SETTING_MASTER_SERVE_DIVS +'</span>'+
						'</div>'+
						'<div class="module-des">'+
							'数据智能验证系统基础管理平台，支持用户管理、设备管理、日志管理、权限管理待办管理、验证计划管理和存储管理等功能'+
						'</div>'+
						'<div class="numdes">'+
						LANG.UI_CLOUD_PLATFORM_AUTHORIZED +
						'</div>'+nodestr+'</div>';
	    for(let i = 0; i < gmpModule.length; i++){
			var key = gmpModule[i];
			switch(key){
				default:
					//如果module_info中不存在这个key,则显示成无限制
					var numclass = '';
					// GMP计算各个授权模块总数和总使用个数
					var desinfo = getGMPAuthDes(key, data);
					if(!desinfo.unlimitedFlag){
						numclass = "numclass";
					}
					var currentinfo = getModuleName(key);
					var name = currentinfo['name'];
					var icon = currentinfo['icon'];
					var module_des = currentinfo['module_des'];
					var otherinfo = "";
					var totaldes = LANG.UI_TOTAL;
					//如果是容量授权，那么总数这里显示成总容量/总数量
					if(key == 'backup_capacity'){
						if(data.module_info['capacity'].auth_type == 1 ){
							totaldes = "总数量"
						}else{
							totaldes = "总容量"
						}
					}
					htmlStr += '<div class="card-item">'+
							'<div class="card-item-title">'+
								'<i class = "viconfont '+ icon +'"></i>'+
								'<span>'+name+'</span>'+ otherinfo +
							'</div>'+
							'<div class="module-des">'+module_des+'</div>'+
							'<div class="numdes ' + numclass+' ">'+
								desinfo.des +
							'</div><div class="card-item-bottom">';
							if (key != 'diff_site_copy') {
								htmlStr += 		LANG.UI_PUBLIC_VM_USED +' / '+ totaldes;
							}
							htmlStr += 		'</div></div>';
					break;
			}
			
		}
		$('#auth-num-card').html(htmlStr);
		//设置模块样式
		pageResizeFix();
	}

	const getGMPAuthDes = (key, data)=>{
		var totalDes = 0;
		var usedDes = 0;
		var unlimitedFlag = false;
		var des = LANG.UI_SETTING_AUTH_UNLIMITED;
		switch (key) {
			case 'timed_backup': //定时备份
				var moduleArr = ['exchange','file','hadoop','nas','obs','oracle','os','private_cloud','vm'];
				moduleArr.forEach(element => {
					if (!data.module_info.hasOwnProperty(element)) {
						return;
					}
					if (data.module_info[element].total_des == -1) {
						unlimitedFlag = true; //显示成无限制
					} else {
						totalDes += data.module_info[element].total_des;
						usedDes += data.module_info[element].used_des;
					}
				});
				break;
			case 'diff_site_copy': //异地副本
				if (!data.module_info.hasOwnProperty('v2v')) {
					return;
				}
				if (data.module_info['v2v'].total_des == -1) {
					unlimitedFlag = true; //显示成无限制
				} else {
					totalDes = data.module_info['v2v'].total_des;
					usedDes = data.module_info['v2v'].used_des;
				}
				break;
			case 'real_time_backup': //实时备份
				var moduleArr = ['cdp','vol_cdp'];
				moduleArr.forEach(element => {
					if (!data.module_info.hasOwnProperty(element)) {
						return;
					}
					if (data.module_info[element].total_des == -1) {
						unlimitedFlag = true; //显示成无限制
					} else {
						totalDes += data.module_info[element].total_des;
						usedDes += data.module_info[element].used_des;
					}
				});
				break;
			case 'data_copy': //数据复制
				var moduleArr = ['copy_machine','copy_db','copy_fs','copy_nas','copy_hadoop','copy_obs'];
				moduleArr.forEach(element => {
					if (!data.module_info.hasOwnProperty(element)) {
						return;
					}
					if (data.module_info[element].total_des == -1) {
						unlimitedFlag = true; //显示成无限制
					} else {
						totalDes += data.module_info[element].total_des;
						usedDes += data.module_info[element].used_des;
					}
				});
				break;
			case 'data_verify': //数据验证
				if (!data.module_info.hasOwnProperty('verify')) {
					return usedDes + "/" + totalDes;
				}
				if (data.module_info['verify'].total_des == -1) {
					unlimitedFlag = true; //显示成无限制
				} else {
					totalDes = data.module_info['verify'].total_des;
					usedDes = data.module_info['verify'].used_des;
				}
				break;
			case 'backup_capacity': 
				if (!data.module_info.hasOwnProperty('capacity')) {
					return;
				}
				if (data.module_info['capacity'].total_des == -1) {
					unlimitedFlag = true; //显示成无限制
				} else {
					totalDes = data.module_info['capacity'].total_des;
					usedDes = data.module_info['capacity'].used_des;
				}
				break;
			case 'timed_capacity':
				if (!data.module_info.hasOwnProperty('storage')) {
					return;
				}
				if (data.module_info['storage'].total_des == -1) {
					unlimitedFlag = true; //显示成无限制
				} else {
					totalDes = data.module_info['storage'].total_des;
					usedDes = data.module_info['storage'].used_des;
				}
				break;
			case 'real_time_capacity':
				if (!data.module_info.hasOwnProperty('vol_capacity')) {
					return;
				}
				if (data.module_info['vol_capacity'].total_des == -1) {
					unlimitedFlag = true; //显示成无限制
				} else {
					totalDes = data.module_info['vol_capacity'].total_des;
					usedDes = data.module_info['vol_capacity'].used_des;
				}
				break;
			case 'data_archive':
				unlimitedFlag = true;// 显示无限制
				break;

		}

		if (unlimitedFlag) {
			des = LANG.UI_SETTING_AUTH_UNLIMITED;
		} else {
			des = usedDes + "/" + totalDes;
		}
		if (key == 'diff_site_copy' || key == 'data_archive') {
			unlimitedFlag =  true;
			des =  LANG.UI_CLOUD_PLATFORM_AUTHORIZED;
		}

		var desinfo = {
			"des":des,
			"unlimitedFlag":unlimitedFlag
		}
		return desinfo;
	}

	const pageResizeFix = ()=>{
	    window.onresize = function(){
			var modules = $("#auth-num-card .card-item");
			var width = document.documentElement.clientWidth || document.body.clientWidth;
			if(width >= 1440){
				for(var i = 0; i < modules.length; i++){
					$(modules[i]).removeClass('me-0');
					$(modules[i]).removeClass('mb0');
					if((i+1) % 3 == 0){
						$(modules[i]).addClass('me-0');
					}
				}
				//设置最后一排样式
				if(modules.length % 3 != 0){
					var index = Math.floor(modules.length / 3);
					for(var i = index * 3; i < modules.length; i++){
						$(modules[i]).addClass('mb0');
					}
				}else{
					var index = Math.floor(modules.length / 3)-1;
					for(var i = index * 3; i < modules.length; i++){
						$(modules[i]).addClass('mb0');
					}
				}
			}else{
				for(var i = 0; i < modules.length; i++){
					$(modules[i]).removeClass('me-0');
					$(modules[i]).removeClass('mb0');
					if((i+1) % 3 == 0){
						$(modules[i]).addClass('me-0');
					}
				}
				//设置最后一排样式
				if(modules.length % 3 != 0){
					var index = Math.floor(modules.length / 3);
					for(var i = index * 3; i < modules.length; i++){
						$(modules[i]).addClass('mb0');
					}
				}else{
					var index = Math.floor(modules.length / 3)-1;
					for(var i = index * 3; i < modules.length; i++){
						$(modules[i]).addClass('mb0');
					}
				}
			}
		}
		$(window).unbind('resize'); //解绑表格插件中绑定的全局事件resize避免在没有表格的地方触发
		$(window).resize();
	}
	//得到模块名称
	const getModuleName = (name)=>{
		let eachModule = {};
		eachModule.name = LANG.UI_PUBLIC_UNKNOWN;
		eachModule.icon = "";
		switch(name){
			case 'timed_backup':
				eachModule.name = `数据验证`;
				eachModule.icon = 'vicon-Frame7';
				eachModule.module_des = '支持数据备份、数据验证、报告模板管理、审批流程管理和报告管理等功能';
				break;
			case 'diff_site_copy':
				eachModule.name = `数据副本`;
				eachModule.icon = 'vicon-Frame-16';
				eachModule.module_des = '将本地数据副本到异地灾备中心，通过安全、高效的数据传输手段复制到异地存储系统或存储设备上，创建备份数据的冗余副本';
				break;
			case 'real_time_backup':
				eachModule.name = `实时备份`;
				eachModule.icon = 'vicon-Frame-23';
				eachModule.module_des = '支持主流操作系统的持续数据备份，确保数据接近于零丢失'
				break;
			case 'data_copy':
				eachModule.name = `数据复制`;
				eachModule.icon = 'vicon-Frame-32';
				eachModule.module_des = '支持主流操作系统、NAS设备和数据库的数据复制，生产系统故障后灾备系统可接管'
				break;
			case 'data_verify':
				eachModule.name = `数据验证`;
				eachModule.icon = 'vicon-yanzhengshuliang';
				eachModule.module_des = '支持数据备份、数据验证、报告模板管理、审批流程管理和报告管理等功能'
				break;
			case 'data_archive':
				eachModule.name = `数据归档`;
				eachModule.icon = 'vicon-Frame-32';
				eachModule.module_des = '将备份数据有序地写入磁带介质进行存储，实现了数据的长期保存，为数据的后续恢复和查询提供了可靠保障'
				break;
			case 'backup_capacity':
				eachModule.name = `数据保护`;
				eachModule.icon = 'vicon-yanzhengshuliang';
				eachModule.module_des = '支持主流系统和数据的备份与恢复'
				
				break;
			case 'timed_capacity':
				eachModule.name = `定时容量`;
				eachModule.icon = 'vicon-Frame7';
				eachModule.module_des = ''
				break;
			case 'real_time_capacity':
				eachModule.name = `实时容量`;
				eachModule.icon = 'vicon-Frame-23';
				eachModule.module_des = ''
				break;
		}
		return eachModule;
	}
	
	const calculatePercentage = (numerator, denominator)=> {
		if (denominator === 0 || isNaN(numerator) || isNaN(denominator)) {
			return 0;
		}
		return (numerator / denominator * 100).toFixed(2);
	}
	//得到软件版本描述
	const getSoftwareDes = (software, extension)=>{
		var des = "";
		if(1 == software){
			des = LANG.UI_SETTING_AUTH_STANDARD;
		}else if(2 == software){
			des = LANG.UI_SETTING_AUTH_ENTERPRISE;
		}else if(3 == software){
			des = LANG.UI_SETTING_AUTH_ADVANCE;
		}else if(4 == software){
			des = LANG.UI_SETTING_AUTH_EN_FREE_EDITION;
		}else if(9 == software){
			des = LANG.UI_SETTING_AUTH_ESSENTIAL_EN;
		}else if(6 == software){
			des = LANG.UI_SETTING_AUTH_STANDARD_EN;
		}else if(7 == software){
			des = LANG.UI_SETTING_AUTH_ENTERPRISE_EN;
		}else if(11 == software){
			des = LANG.UI_SETTING_AUTH_ENTERPRISE_LR_EN;
		}else if(12 == software){
			des = LANG.UI_SETTING_AUTH_PROFESSIONAL;
		}else if(100 == software){
			des = extension.softwareVersionDiy;
		}
		return des;
	}
	

    return {
        //main function to initiate the module
        init: function () {
            initListener(); 
			initBasicInfo();
        }

    };

}();

jQuery(document).ready(function() {    
	AuthorizationModule.init();
});