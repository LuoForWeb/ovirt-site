var AuthorizationModule = function () {
	let emptymodulestr = [];
	const initListener = ()=>{
		$('.top-title-right').popover({
			trigger:'hover',
		})
		//下载指纹文件
		$(".download_auth").on('click',function(){
			getThumbprint();
		})
		if ($.inArray('p_authorization_module_upload', CONF.PERMISSION_ARR) !== -1) {
			initUpload();
		}
		
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
				if(response.success){
					UIToastr.showSuccess (LANG.UI_LICENSE_UPLOAD_AUTH_FILE_SUCCESS, LANG.UI_SETTINGS_ADD_STSTEM_LICENSE_TIPS);
					$('.auth-card').hide();
					$('.no-auth-card').hide();
					$('.second-div').hide();
					$('.storage-module').hide();
					$('.auth-capacity').hide();
					$('.file-auth-capacity').hide();
					$('.backup-module').hide();
					$('.module-auth-des').hide();
					$('.extend-content').hide();
					$('.copy-module').hide();
					$('.cdp-module').hide();
					$('.advanced-module').hide();
					//清空#authCard 除了basic-card类之外的其他的类
					$("#authCard").attr('class','basic-card');
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
	//初始化授权帮助提示框
	const initAuthTips = () => {
		$('#authtip-box').popover('destroy');

		$('#authtip-box').popover({
			trigger: 'hover',
			delay: { show: 150, hide: 200 },
			placement: 'bottom', // 仅作为参考，实际位置由 JS 控制
			html: true,
			content: LANG.UI_LICENSE_AUTH_TIPS,
			template: '<div class="popover auth-custom-popover" role="tooltip">' +
					'<div class="arrow"></div>' +
					'<div class="popover-content"></div>' +
					'</div>'
		})
		.on('inserted.bs.popover', function () {
			// 隐藏 popover 避免闪现
			var $popover = $(this).data('bs.popover').$tip;
			if ($popover) {
				$popover.css({
					opacity: 0,
					visibility: 'hidden',
					display: 'block' // 确保能获取 outerWidth
				});
			}
		})
		.on('shown.bs.popover', function () {
			var popoverInstance = $(this).data('bs.popover');
			if (!popoverInstance || !popoverInstance.$tip) return;

			var $popover = popoverInstance.$tip;
			var $trigger = $(this);

			// 获取 popover 宽度（此时已渲染，可准确测量）
			var popoverWidth = $popover.outerWidth(); // 包括 padding/border

			// 计算：右边缘距屏幕右边 56px
			var viewportWidth = $(window).width(); // 或 window.innerWidth
			var desiredRight = 56;
			var left = viewportWidth - desiredRight - popoverWidth;

			// 可选：确保不超出左侧（最小 left = 0）
			left = Math.max(0, left);
			$popover.css({
				left: left + 'px',
				opacity: 1,
				visibility: 'visible'
			});
		});
	};
	//获取基本信息
	const initBasicInfo = ()=>{
	    pAjaxRequest({}, '/api/v1/system/auth/basic/info', 'get', (res) => {
			if(!res.success){
				UIToastr.showWarning(LANG.UI_LICENSE_GET_AUTH_INFO_FAIL);
				return;
			}
			//设置基本信息
			let data =  res.data;
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
					$('#authCard').addClass('auth-trial');
					if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.ENTERPRISE != 'backup-system'){
						hiddlephone();
					}
					//试用授权
				}else if(data.system_info.trial == 2){
					//永久授权
					$('#authCard').addClass('auth-permanent')
				}
			}else if(status ==  2){
				$('#authCard').addClass('auth-noauth');
				if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.ENTERPRISE != 'backup-system'){
					hiddlephone();
				}
			}else if(status ==  3 || status ==  4){
				$('#authCard').addClass('auth-exception');
				if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" && CONF.ENTERPRISE != 'backup-system'){
					if(data.system_info.trial ==  1 ){
						hiddlephone();
					}
				}
			}
			//设置右上角卡片
			if(status == 2){
				$('.auth-card').hide();
				$('.no-auth-card').show();
			}else{
				$('.no-auth-card').hide();
				$('.auth-card').show();
				$('.second-div').show();
				//右上角设置授权信息
				$('#customer').html(data.system_info.customer); //用户名称
				$('#software').html(getSoftwareDes(data.system_info.software, data.extension)); //软件版本
				$('#softwareVersion').html(data.version_info); //当前版本
				//型号
				setOEMShow(data);
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
				//设置备份节点显示
				$("#node_num").html(data.master_info.node_num);
				//设置存储信息显示
				setStorageCardInfo(data);
				//设置备份授权信息显示
				setBackupAuthCardInfo(data);
				//设置复制授权信息显示
				setCopyAuthCardInfo(data);
				//设置实时授权信息显示
				setCDPAuthCardInfo(data);
				//设置高级模块
				setAdvanceCardInfo(data);
				//设置模块样式
				pageResizeFix();
				//设置tooltip
				initToolTip();
				
			}
		});
	}
	//设置OEM展示
	const setOEMShow = (data)=>{
		if(CONF.ENTERPRISE == 'inspur_enterprise' && CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw" ){
			$('#softwareNameDiy').text("InCloud DP");
			$('#copyRight').text("InCloud DP");
		}
		if(CONF.ENTERPRISE == 'vinchin_enterprise_ck' || CONF.ENTERPRISE == 'vinchin_enterprise_ak'){
			$('#softWareTitle').text(LANG.UI_SETTING_AUTH_VERSION_TYPE);
			$('.ck-show').show();
			$('.no-ck-show').hide();
		}
	}
	const setStorageCardInfo = (data)=>{
	    //设置容量卡片是否显示
		$(".storage-module").show();
		let first_type =  data.first_type;  //授权类型 1容量 2定时实时分离 3其他
		let second_type =  data.second_type; // 1 2 3 4
		if(first_type == 3){
			if(second_type == 3 || second_type == 4){
				$('.file-auth-capacity').show();
			}else{
				$('.file-auth-capacity').hide();
			}
			if(data.module_info.cdp.auth_type == 1){
				$('.auth-capacity').hide();
			}else{
				$('.auth-capacity').show();
			}
		}else{
			$('.auth-capacity').show();
			$('.file-auth-capacity').hide();
		}
		//设置容量信息
		if(first_type == 1){
			setStorageInfo(data.module_info.capacity);
		}else if(first_type == 2){
			setStorageInfo(data.all_storage_capacity);
		}else if(first_type == 3){
			if(second_type == 3 || second_type == 4){
				setFileStorageInfo(data.file_capacity);
			}
			if(data.module_info.cdp.auth_type == 2){
				setStorageInfo(data.all_storage_capacity);
			}
		}
	}

	const setFileStorageInfo = (data)=>{
		$('#file_capacity_auth_total_des').html(data.total_des);
		if(data.used > data.total){
			$("#file_capacity_auth_valid_des").html("0B");
		}else{
			$("#file_capacity_auth_valid_des").html(data.avail_des);
		}
		let percent = calculatePercentage(data.used, data.total);
		$(".file-auth-capacity .progress-bar").css('width',percent+'%');
	}
	const setStorageInfo = (data)=>{
		$('#capacity_auth_total_des').html(data.total_des);
		if(data.used > data.total){
			$("#capacity_auth_valid_des").html("0B");
		}else{
			$("#capacity_auth_valid_des").html(data.avail_des);
		}
		let percent = calculatePercentage(data.used, data.total);
		$(".auth-capacity .progress-bar").css('width',percent+'%');
	}

	const calculatePercentage = (numerator, denominator)=> {
		if (denominator === 0 || isNaN(numerator) || isNaN(denominator)) {
			return 0;
		}
		return (numerator / denominator * 100).toFixed(2);
	}
	//设置备份授权信息显示
	const setBackupAuthCardInfo = (data)=>{
		//设置基本模块和扩展模块
		var basic_backupmodule = ['vm','private_cloud','cloud','machine_os','client','os','file','oracle','os_client'];
		var basicstr = ''
		var extendstr = '';
		var itemstr = ``;
		var showflag = true;
		if(data.module_info.os){
			data.module_info.machine_os = data.module_info.os;
		}
		// 当整机和卷都已经授权的时候,加个客户端
		if(data.backup_show_flag.machine_os || data.backup_show_flag.os){
			data.backup_show_flag.os_client = true;
		}
		for(let key in data.backup_show_flag){
			if(data.backup_show_flag[key] == false){
				continue;
			}
			//如果是整机授权 那么文件数据库os都不应该显示
			if(data.first_type == 3){
				if(data.second_type ==  2 && (key == "file" || key == "oracle" || key == "machine_os" || key == "os" )){
					continue;
				}
				if(data.second_type ==  4 && (key == "oracle" || key == "machine_os" || key == "os" )){
					continue;
				}
			}
			if(data.backup_show_flag.os_client && (key == "os" || key == "machine_os")){
				//当两个都授权时，则跳过
				continue;
			}
			var modulename = getModuleName(key)['name'];
			var titledes = ''
			//容量:无限制 改成容量授权 这里加一个flag判断 默认为true
			var capacity_unlimited = true;
			//如果module_info中不存在这个key,则显示成容量
			var authtype = LANG.UI_VOL_CDP_JOB_STORAGE
			var usedes = LANG.UI_SETTING_AUTH_UNLIMITED;
			switch(key){
				case "client":
					authtype = LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT
					capacity_unlimited = false;
					var titlearr = [];
					if(data.backup_show_flag.file && data.second_type == 2){ //整机+其他模块数量授权
						titlearr.push(LANG.UI_VISUAL_FILE);
						if(data.module_info.file.total_des != -1){
							showflag =  false;
							usedes  = data.module_info.file.used_des + " / "+data.module_info.file.total_des;
						}
					}
					if(data.backup_show_flag.oracle){
						titlearr.push(LANG.UI_VISUAL_DB);
						if(data.module_info.oracle.total_des != -1){
							showflag =  false;
							usedes  = data.module_info.oracle.used_des + " / "+data.module_info.oracle.total_des;
						}
					}
					if(data.backup_show_flag.machine_os){
						titlearr.push(LANG.UI_BACKUP_DATA_MODULE_OS);
						if(data.module_info.os.total_des != -1){
							showflag =  false;
							usedes = data.module_info.os.used_des + " / "+data.module_info.os.total_des; //整机和卷公用个数
						}
					}
					if(data.backup_show_flag.os){
						titlearr.push(LANG.UI_VOL_CDP_RECOVER_VOL);
						if(data.module_info.os.total_des != -1){
							showflag =  false;
							usedes = data.module_info.os.used_des + " / "+data.module_info.os.total_des;
						}
					}
					if(titlearr.length == 0){
						continue;
					}
					titledes = '<span class="title-des">'+ titlearr.join("/") + '</span>';
					break;
				case "os_client":
					//如果有了client,则不显示os_client
					if(data.backup_show_flag.client){
						continue;
					}
					var titlearr  = [];
					if(data.backup_show_flag.machine_os){
						titlearr.push(LANG.UI_BACKUP_DATA_MODULE_OS);
					}
					if(data.backup_show_flag.os){
						titlearr.push(LANG.UI_VOL_CDP_RECOVER_VOL);
					}
					if(titlearr.length  ==  0){
						continue; //如果两个都没授权，则不显示
					}
					titledes = '<span class="title-des">'+ titlearr.join(" / ") + '</span>';
					if(data.module_info.os && data.module_info.os.auth_type == 1){
						authtype = LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT;
						capacity_unlimited =  false;
						if( data.module_info.os.total_des != -1){
							showflag =  false;
							usedes = data.module_info.os.used_des + " / "+data.module_info.os.total_des;
						}
					}
					break;
				default:
					//如果是按照文件系列容量授权 文件 nas hadoop 对象存储都要显示成无限制
					var flag = (data.second_type == 3 || data.second_type == 4) && (key == "file" || key == "nas" || key == "obs" || key == "hadoop");
					// 如果是exchange_online 需要用小字展示exchange_online
					if(key == "exchange_online"){
						titledes =  '<span class="title-des">'+ 'Exchange Online' + '</span>';
					}
					if(data.module_info.hasOwnProperty(key) && data.module_info[key].auth_type == 1){
						authtype = LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT;
						capacity_unlimited =  false;
						if(data.module_info[key].total_des != -1){
							showflag =  false;
							usedes = data.module_info[key].used_des + " / "+data.module_info[key].total_des;
						}
					}
					if(key == "vm" && data.first_type == 3){
						var vmauthtype = ''
						if(data.module_info.vm.license_type == 1){
							vmauthtype = LANG.UI_VISUAL_HOSTS
						}else if(data.module_info.vm.license_type == 2){
						    vmauthtype = "CPU"
						}else if(data.module_info.vm.license_type == 4){
						    vmauthtype = LANG.UI_VISUAL_VMS
						}
						authtype = vmauthtype + LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT;
						//如果是CPU授权并且ztack授权了才会显示（也就是授权页面可备份的地方支持ztack） 需要显示成CPU数量（含Zstack Cloud）
						if(data.module_info.vm.license_type == 2 && data.other_info.ztack_auth){
						    authtype += LANG.UI_SETTING_AUTH_CPU_DES;
						}
					}
					break;
			}
			let authdes = '';
			// 容量:无限制 改成容量授权
			if(capacity_unlimited){
				authdes = `<span class="auth-method">${LANG.UI_SETTING_AUTH_CAPACITY_AUTH}</span>`
			}else{
				authdes = `<span class="auth-method">
								${authtype}：
							</span>
							<span>
								${usedes}
							</span>`
			}
			itemstr = `<div class="item-content">
						<div class="module-item-title ellipsis-tooltip">
							${modulename} ${titledes}
						</div>
						<div class="module-item-des">
							${authdes}
						</div>
					</div>`;
			if(basic_backupmodule.indexOf(key) != -1){
				basicstr += itemstr;
			}else{
				extendstr += itemstr;
			}
		}
		//统一调用显示函数
		moduleShowFun(".backup-module",basicstr,extendstr);
		//设置备份模块左上角容量信息显示
		if(data.first_type == 2){
			$('.backup-module .module-auth-num').html(data.module_info.storage.used_des + '/' + data.module_info.storage.total_des);
			$('.backup-module .module-auth-des').show();
		}else if(data.first_type == 1){
			$('.backup-module .module-auth-title').html( LANG.UI_VOL_CDP_JOB_STORAGE)
			$('.backup-module .module-auth-num').html(LANG.UI_SETTING_AUTH_UNLIMITED)
			// $('.backup-module .module-auth-des').show();
		}else if (data.first_type == 3){
			if(showflag){
				if(data.second_type == 1 || data.second_type == 2){
					//第一大类和第二大类跟CDP授权方式无关 都是显示数量无限制
					$('.backup-module .module-auth-title').html( LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT)
					$('.backup-module .module-auth-num').html(LANG.UI_SETTING_AUTH_UNLIMITED)
				}else{
					$('.backup-module .module-auth-title').html("");
					$('.backup-module .module-auth-num').html(LANG.UI_SETTING_AUTH_UNLIMITED)
				}
				// $('.backup-module .module-auth-des').show();
			}
		}
	}
	//设置复制授权信息显示
	const setCopyAuthCardInfo = (data)=>{
		//设置基本模块和扩展模块
		var basic_copymodule = ['copy_machine','copy_os','copy_nas','copy_fs','copy_os_client'];
		var basicstr = ''
		var extendstr = '';
		var itemstr = ``;
		var showflag = true;
		if(data.module_info.copy_machine){
			data.module_info.copy_os = data.module_info.copy_machine;
		}
		if(data.copy_show_flag.copy_machine || data.copy_show_flag.copy_os){
			data.copy_show_flag.copy_os_client = true;
		}
		for(let key in data.copy_show_flag){
			if(data.copy_show_flag[key] == false){
				continue;
			}
			if(data.copy_show_flag.copy_os_client && (key == "copy_os" || key == "copy_machine")){
				//当两个都授权时，则跳过
				continue;
			}
			var modulename = getModuleName(key)['name'];
			var titledes = ''
			var authtype = LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT;
			var usedes = LANG.UI_SETTING_AUTH_UNLIMITED;
			switch(key){
				case "copy_os_client":
					var titlearr  = [];
					if(data.copy_show_flag.copy_machine){
						titlearr.push(LANG.UI_BACKUP_DATA_MODULE_OS);
					}
					if(data.copy_show_flag.copy_os){
						titlearr.push(LANG.UI_VOL_CDP_RECOVER_VOL);
					}
					if(titlearr.length  ==  0){
						continue; //如果两个都没授权，则不显示
					}
					titledes = '<span class="title-des">'+ titlearr.join(" / ") + '</span>';
					if( data.module_info.copy_machine.total_des != -1){
						showflag =  false;
						usedes = data.module_info.copy_machine.used_des + "/ "+data.module_info.copy_machine.total_des;
					}
					break;
				default:
					if(key == "copy_db"){
						 //数据库复制后面加小字展示 Oracle RAC
						titledes = '<span class="title-des">'+ "Oracle RAC" + '</span>';
					}
					if(data.module_info.hasOwnProperty(key) && data.module_info[key].total_des != -1){
						showflag = false;
						usedes = data.module_info[key].used_des + " / "+data.module_info[key].total_des;
					}
			}
			itemstr = `<div class="item-content">
						<div class="module-item-title ellipsis-tooltip">
							${modulename} ${titledes}
						</div>
						<div class="module-item-des">
							<span class="auth-method">
								${authtype}：
							</span>
							<span>
								${usedes}
							</span>
						</div>
					</div>`;
			if(basic_copymodule.indexOf(key) != -1){
				basicstr += itemstr;
			}else{
				extendstr += itemstr;
			}
		}
		//设置左上角数量显示
		if(showflag){
			// $('.copy-module .module-auth-des').show();
			$('.copy-module .module-auth-num').html(LANG.UI_SETTING_AUTH_UNLIMITED)
		}
		moduleShowFun(".copy-module",basicstr,extendstr);
		
	}	

	//设置实时授权信息显示
	const setCDPAuthCardInfo = (data)=>{
		var basic_cdpmodule = ['cdp_machine','cdp_vol','cdp_os_client'];
		var basicstr = ''
		var extendstr = '';
		var itemstr = ``;
		if(data.cdp_show_flag.cdp_machine || data.cdp_show_flag.cdp_vol){
			data.cdp_show_flag.cdp_os_client = true;
		}
		// 第一大类和第二大类 容量都是无限制
		for(let key in data.cdp_show_flag){
			if(data.cdp_show_flag[key] == false){
				continue;
			}
			if(data.cdp_show_flag.cdp_os_client && (key == "cdp_machine" || key == "cdp_vol")){
				//当两个都授权时，则跳过
				continue;
			}
			//容量:无限制 改成容量授权 这里加一个flag判断 默认为true
			var capacity_unlimited = true;
			var authtype = LANG.UI_VOL_CDP_JOB_STORAGE;
			var usedes = LANG.UI_SETTING_AUTH_UNLIMITED;
			var titledes = '';
			var modulename = getModuleName(key)['name'];
			switch(key){
				case "cdp_os_client":
					var titlearr  = [];
					if(data.cdp_show_flag.cdp_machine){
						titlearr.push(LANG.UI_BACKUP_DATA_MODULE_OS);
					}
					if(data.cdp_show_flag.cdp_vol){
						titlearr.push(LANG.UI_VOL_CDP_RECOVER_VOL);
					}
					if(titlearr.length  ==  0){
						continue; //如果两个都没授权，则不显示
					}
					titledes = '<span class="title-des">'+ titlearr.join(" / ") + '</span>';
					if(data.first_type == 3 && data.module_info.cdp.auth_type == 1){
						authtype = LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT;
						capacity_unlimited = false;
					}
					if(data.first_type == 3 && data.module_info.cdp.total_des != -1){
						usedes = data.module_info.cdp.used_des + '/' + data.module_info.cdp.total_des;
					}
					break;
				case "third_db_cdp": 
					authtype = LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT;
					capacity_unlimited = false;
					usedes = data.module_info.third_db_cdp.used + "/ "+data.module_info.third_db_cdp.total;
					if(data.module_info.third_db_cdp.total != 0 && !data.module_info.third_db_cdp.valid){
						//后面加上已过期
						usedes += "(" + LANG.UI_BACKUP_DATA_LABEL_EXPIRED + ")";
					}
					break;
			}
			let authdes = '';
			// 容量:无限制 改成容量授权
			if(capacity_unlimited){
				authdes = `<span class="auth-method">${LANG.UI_SETTING_AUTH_CAPACITY_AUTH}</span>`
			}else{
				authdes = `<span class="auth-method">
								${authtype}：
							</span>
							<span>
								${usedes}
							</span>`
			}
			itemstr = `<div class="item-content">
						<div class="module-item-title ellipsis-tooltip">
							${modulename} ${titledes}
						</div>
						<div class="module-item-des">
							${authdes}
						</div>
					</div>`;
			if(basic_cdpmodule.indexOf(key) != -1){
				basicstr += itemstr;
			}else{
				extendstr += itemstr;
			}
		}
		moduleShowFun(".cdp-module",basicstr,extendstr);
		//设置实时左上角容量信息显示
		if(data.first_type == 2){
			$('.cdp-module .module-auth-num').html(data.module_info.vol_capacity.used_des + '/' + data.module_info.vol_capacity.total_des);
			$('.cdp-module .module-auth-des').show();
		}else if(data.first_type == 3 && data.module_info.cdp.total_des != -1){
				$('.cdp-module .module-auth-num').html(data.module_info.cdp.used_des + '/' + data.module_info.cdp.total_des);
				$('.cdp-module .module-auth-title').html(data.module_info.cdp.auth_type == 1 ? LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT : LANG.UI_VOL_CDP_JOB_STORAGE);
		}else{
			//都显示成无限制
			if(data.first_type == 3){
				$('.cdp-module .module-auth-title').html(data.module_info.cdp.auth_type == 1 ? LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT : LANG.UI_VOL_CDP_JOB_STORAGE);
			}else{
				$('.cdp-module .module-auth-title').html(LANG.UI_VOL_CDP_JOB_STORAGE);
			}
			$('.cdp-module .module-auth-num').html(LANG.UI_SETTING_AUTH_UNLIMITED)
		}
		//目前这里数据库实时按照容量进行授权判断 实时容灾的副标题都会显示 因此不会显示隐藏判断
	}
	//设置高级模块信息显示
	const setAdvanceCardInfo = (data)=>{
		var allstr = '';
		var showflag = true;
		// 内嵌和接管暂时先不显示
		var showarr = ['virusKill','verify','copy','archive','tapestorage','lanfree','dedupication','visualization','tenant','supportCluster','v2v','worm','taskSchedule','threepowers','ddBoost']
		//先单独设置内嵌和验证
		for(let key in data.advance_show_flag){
			if(data.advance_show_flag[key] == false){
				continue;
			}
			var itemstr = ``;
			var authtype = LANG.UI_PLATFORM_INDUSTRY_REPORT_COUNT;
			var usedes = LANG.UI_SETTING_AUTH_UNLIMITED;
			var modulename = getModuleName(key)['name'];
			if(data.module_info.hasOwnProperty(key) && data.module_info[key].total_des != -1){
				showflag = false;
				usedes = data.module_info[key].used_des + " / "+data.module_info[key].total_des;
			}
			itemstr = `<div class="item-content">
						<div class="module-item-title ellipsis-tooltip">
							${modulename}
						</div>
						<div class="module-item-des">
							<span class="auth-method">
								${authtype}：
							</span>
							<span>
								${usedes}
							</span>
						</div>
					</div>`;
			allstr += itemstr;
		}
		//还需要添加功能授权信息显示
		const authfuns = data.function_info;
		// 1. 获取所有键并过滤出值为 true 的键
		const allauthfun = Object.keys(authfuns).filter(key => authfuns[key] === true);
		allauthfun.forEach(itemkey => {
			var titledes = ''
			if(showarr.indexOf(itemkey) == -1){
				return;
			}
			var eachFunName = getEachFunName(itemkey);
			if(itemkey == "virusKill"){
				//如果是病毒查杀 需要用小字显示病毒库名称
				titledes = '<span class="title-des">'+ data.virus_name.join("/") + '</span>';
			}
			var itemstr = `<div class="item-content">
						<div class="module-item-title ellipsis-tooltip">
							${eachFunName}${titledes}
						</div>
						<div class="bottom-line">
							<span>
								${LANG.UI_SETTING_AUTH_FUNC}
							</span>
						</div>
					</div>`;
			allstr += itemstr;
		});
		$('.advanced-module .module-content-top').html(allstr);
		//设置左上角数量显示
		if(showflag){
			// $('.advanced-module .module-auth-des').show();
			$('.advanced-module .module-auth-num').html(LANG.UI_SETTING_AUTH_UNLIMITED)
		}
		if(allstr != ""){
			$('.advanced-module').show();
		}

	}
	const moduleShowFun = (modulename, basicstr, extendstr)=>{
		$(`${modulename} .module-content-top`).html(basicstr);
		if(extendstr != ""){
			if(basicstr != ""){
				$(`${modulename} .extend-content`).show();
			}
			$(`${modulename} .module-content-bottom`).html(extendstr);
		}
		if(extendstr != "" || basicstr != ""){
			//这两个都存在 则备份模块显示
			$(`${modulename}`) .show();
		}else{
			//这个模块为空
			emptymodulestr.push(modulename);
		}
	}
	//设置小模块样式
	const pageResizeFix = ()=>{
		$(document).ready(function() {
			function updateItemWidth() {
				const itemwidth = $(".backup-module .item-content").first().outerWidth();
				// 设置第二个容器所有模块的宽度
				$(".copy-module .item-content , .cdp-module .item-content").css({
					"width": `${itemwidth}px`
				});
				//设置实时保护模块的宽度
				const cdpmodulewidth = itemwidth*2+12+40;
				$(".cdp-module").css({
					"width": `${cdpmodulewidth}px`
				})
				$(".copy-module").css({
					"width": `calc(100% - ${cdpmodulewidth}px - 20px)`
				})
			}

			// 初始化计算
			updateItemWidth();

			// 使用 ResizeObserver 监听容器变化
			const observer = new ResizeObserver(updateItemWidth);
			observer.observe($(".backup-module")[0]);
		});

		let allmodules = {
			"backup_modules":$(".backup-module .module-content-top .item-content"),
			'backup_modules_bottom':$(".backup-module .module-content-bottom .item-content"),
			"advanced_modules":$(".advanced-module .item-content")
		}
		for(var key in allmodules){
			 var modules  = allmodules[key];
			 for(var i = 0; i < modules.length; i++){
					if((i+1) % 6 == 0){
							$(modules[i]).addClass('me-0');
						}
			 }
		}
		//设置复制和实时模块宽度
		if(emptymodulestr.indexOf('copy-module') != -1 && emptymodulestr.indexOf('cdp-module') == -1){
			$('.copy-module').addClass('copy-cdp-width100')
		}
		if (emptymodulestr.indexOf('cdp-module') != -1 && emptymodulestr.indexOf('copy-module') == -1) {
			$('.cdp-module').addClass('copy-cdp-width100')
		}
	}


	//得到模块名称
	const getModuleName = (name)=>{
		let eachModule = {};
		eachModule.name = LANG.UI_PUBLIC_UNKNOWN;
		switch(name){
			case 'client':
			case 'os_client':
			case 'copy_os_client':
			case 'cdp_os_client':
				eachModule.name = LANG.UI_REPORT_AGENT;
				break;
			case 'vol_cdp':
				eachModule.name = LANG.UI_LICENSE_CDP_TAKEOVER_NUM;
				break;
			case 'embed':
				eachModule.name = LANG.UI_LICENSE_EMBED_TAKEOVER_NUM;
				break;
			case 'v2v':
				eachModule.name = LANG.UI_FILE_CROSS_RESTORE;
				break;
			case 'machine_os':
				eachModule.name = LANG.UI_BACKUP_DATA_MODULE_OS;
				break;
			case 'os':
				eachModule.name = LANG.UI_VOL_CDP_RECOVER_VOL;
				break;
			case 'file':
				eachModule.name = LANG.UI_FILE_FILE;
				break;
			case 'nas':
				eachModule.name = LANG.UI_BACKUP_DATA_MODULE_NAS;
				break;
			case 'hadoop':
				eachModule.name = LANG.UI_BACKUP_DATA_MODULE_HADOOP;
				break;
			case 'obs':
				eachModule.name = LANG.UI_VISUAL_OBS;
				break;
			case 'oracle':
				eachModule.name = LANG.UI_DB_RECOVERY_CONTENT_DB;
				break;
			case 'vm':
				eachModule.name = LANG.UI_BACKUP_DATA_MODULE_VM;
				break;
			case 'private_cloud':
				eachModule.name = LANG.UI_BACKUP_DATA_MODULE_PRIVATE_CLOUD;
				break;
			case 'cloud':
				eachModule.name = LANG.UI_BACKUP_DATA_MODULE_PUBLIC_CLOUD;
				break;
			case 'exchange':
				eachModule.name = "Exchange Server";
				break;
			case 'exchange_online':
				eachModule.name = "Microsoft 365";
				break;
			case 'k8s':
				eachModule.name = "Kubernetes";
				break;
			case 'oracle_hana':
				eachModule.name = "SAP HANA";
				break;
			case 'file_cdp_auth':
				eachModule.name = LANG.UI_SETTING_FILE_SYNCHRONIZATION;
				break;
			case 'data_cdp_auth':
				eachModule.name = LANG.UI_SETTING_DATABASE_CDP;
				break;
			case 'verify': 
				eachModule.name = LANG.UI_LICENSE_VERIFY_NUM;
				break;
			case 'copy_machine':
				eachModule.name = LANG.UI_BACKUP_DATA_MODULE_OS;
				break;
			case 'copy_os':
				eachModule.name = LANG.UI_VOL_CDP_RECOVER_VOL;
				break;
			case 'copy_db':
				eachModule.name = LANG.UI_DB_RECOVERY_CONTENT_DB;
				break;
			case 'copy_fs':
				eachModule.name = LANG.UI_FILE_FILE;
				break;
			case 'copy_nas':
				eachModule.name = LANG.UI_BACKUP_DATA_MODULE_NAS;
				break;
			case 'copy_hadoop':
				eachModule.name = LANG.UI_BACKUP_DATA_MODULE_HADOOP;
				break;
			case 'copy_obs':
				eachModule.name = LANG.UI_VISUAL_OBS;
				break;
			case 'cdp_machine':
				eachModule.name = LANG.UI_BACKUP_DATA_MODULE_OS;
				break;
			case 'cdp_vol':
				eachModule.name = LANG.UI_VOL_CDP_RECOVER_VOL;
				break;
			case 'third_db_cdp':
				eachModule.name = LANG.UI_DB_RECOVERY_CONTENT_DB;
				break;
			case 'emergency_takeover':
				eachModule.name = LANG.UI_VOL_CDP_EMERGENCY_TAKEOVER_DESC;
				break;
		}
		return eachModule;
	}


	//得到授权功能名称
	const getEachFunName = (name)=>{
		switch(name){
			case 'archive':
				return LANG.UI_LICENSE_DATA_ARCHIVE;
			case 'backupStorageProtect':
				return LANG.UI_LICENSE_EXTORTION_PROTECT;
			case 'cdpAutoTakeover':
				return LANG.UI_VOL_CDP_JOB_DETAILS_AUTO_TAKEOVER;
			case 'cloudstorage':
				return LANG.UI_LICENSE_BACKUP_STORAGE_CLOUD;
			case 'copy':
				return LANG.UI_LICENSE_COPY_DISASTER;
			case 'dbAutoTakeover':
				return LANG.UI_LICENSE_DB_FAILOVER;
			case 'dedupication':
				return LANG.UI_LICENSE_STRATEGY_DEDUPULICATION;
			case 'emergencyDR':
				return LANG.UI_LICENSE_EMERGENCY_DISASTER_EMBED;
			case 'fileArchiveMode':
				return LANG.UI_LICENSE_FILE_ARCHIVE_MODE;
			case 'grain':
				return LANG.UI_VISUAL_RECOVERY_GRAIN;
			case 'instantRecovery':
				return LANG.UI_VISUAL_INSTANT_NAME;
			case 'integrity':
				return LANG.UI_VM_INTEGRITY_VERIFY;
			case 'lanfree':
				return LANG.UI_LICENSE_LAN_FREE_STORAGE;
			case 'multithread':
				return LANG.UI_LICENSE_MULTI_THREAD;
			case 'supportCluster':
				return LANG.UI_LICENSE_CLUSTER_MODE;
			case 'tapestorage':
				return LANG.UI_LICENSE_BACKUP_STORAGE_TAPE;
			case 'taskSchedule':
				return LANG.UI_LICENSE_JOB_ORCHESTRATION;
			case 'threepowers':
				return LANG.UI_LICENSE_SEPARA_POWER;
			case 'vcbt':
				return LANG.UI_LICENSE_GLOBAL_STRATEGY_PARSE_FS;				
			case 'virusKill':
				return LANG.UI_VM_VIRUS_SCANKILL;
			case 'visualization':
				return LANG.UI_LICENSE_SCREEN;
			case 'worm':
				return LANG.UI_LICENSE_WORM;	
			case 'dataVerificationDR':
				return LANG.UI_LICENSE_DATA_VERIFY_DISASTER;
			case 'dataVerificationVM':
				return LANG.UI_LICENSE_DATA_VERIFY_VM_PLATFORM;
			case 'engine':
				return LANG.UI_LICENSE_PLATFORM_BACKUP;
			case 'embed':
				return LANG.UI_LICENSE_EMERGENCY_DISASTER_EMBED
			case 'tenant':
				return LANG.UI_LICENSE_TENANT;
			case 'ddBoost':
				return "DD Boost";
		}

	}

	//隐藏电话 将邮箱改为售前邮箱
	const hiddlephone = function(){
		//海外版未授权需要屏蔽售后电话
		$('.teleli').hide();
		//未授权需要将邮箱改成售前邮箱
		$('#emailinfo').text("customer.service@vinchin.com");
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
	
	const initToolTip = function(){
		// 批量处理所有需要此行为的元素
		document.querySelectorAll('.ellipsis-tooltip').forEach(addTooltipIfOverflow);

		// 如果内容可能动态变化（如响应式 resize、数据更新），可以监听 resize
		window.addEventListener('resize', () => {
			document.querySelectorAll('.ellipsis-tooltip').forEach(addTooltipIfOverflow);
		});
	}

	function addTooltipIfOverflow(element) {
		let text = element.textContent || element.innerText;
		// 检查 scrollWidth > clientWidth 表示内容溢出
		if (element.scrollWidth > element.clientWidth) {
			element.title = text;
			element.setAttribute('data-toggle', "tooltip");
			element.setAttribute('data-placement', "top");
			element.setAttribute('data-original-title', text);
		} else {
			element.removeAttribute('title');
			element.removeAttribute('data-toggle');
			element.removeAttribute('data-placement');
			element.removeAttribute('data-original-title');
			
		}
		$('[data-toggle="tooltip"]').tooltip();
	}
	
	

    return {
        //main function to initiate the module
        init: function () {
            initListener(); 
			initBasicInfo();
			initAuthTips();
        }

    };

}();

jQuery(document).ready(function() {    
	AuthorizationModule.init();
});