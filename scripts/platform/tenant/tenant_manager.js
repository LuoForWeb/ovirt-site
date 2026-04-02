var tenantManager = function(){
	var deleteSuccessFlag =false, hideModalFlag = false, initErrorFlag = false;
	var _UserPassword;
	var editFlag = false;//是否是修改租户信息
	var tenantFormStep1 = $('#submit_form');//要校验的表单
	var firstFlag = true;//提示只显示一次
	var moduleShowList = [];//已授权的模块
	var license_type = '';//系统授权类型  6种
	let FIRST_TYPE = '';//系统一级授权
	let SECOND_TYPE = '';//系统二级授权
	
	//添加监听事件
	var addListeners = function(){
		//定义iCheck样式
		$('#tenantDrawer input[type=radio]').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});
		
		//跳转到添加虚拟化中心
		$('#toAddVcenter').on('click',function(){
			$('#tenantDrawer').drawer("hide");
			LOCATION('./content/vm/add_vcenter.php', 'vcenter_manager');
		});
		initSpinner();
		//初始化BS select控件
		$('#selectStorage').selectpicker({
			iconBase: 'fa',
			tickIcon: 'fa-check',
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
		});
		initAuthWay();
	}
	var initAuthWay = function () {
		let des = ``;
		//获取系统授权类型
		pAjaxRequest({}, '/api/v1/system/auth/basic/info', 'get', (res) => {
			if(res.success){
				let data =  res.data;
				FIRST_TYPE = data.first_type;
				SECOND_TYPE = data.second_type;
				switch (FIRST_TYPE) {//一级授权
					case 1://复制模块为数量授权，其他模块均为容量授权
						$('.copy-mudule').show();
						$('.normal-quota-div').show();
						$('.normal-capacity-mode').val('0');
						$('.normal-capacity-mode').on('change',function () {
							capacityModeChange('normal-');
						});
						des += `<span class="help_link">`+ LANG.UI_TENANT_COPY_MODULE+`</span>`+ LANG.UI_TENANT_AUTH_BY_NUM+`
								<span class="help_link">`+ LANG.UI_TENANT_OTHRE_MODULE+`</span>`+ LANG.UI_TENANT_ALL_AUTH_BY_CAPACITY;
						license_type = 1;		
						break;
					case 2://复制模块为数量授权，备份和实时保护模块按容量分别授权 
						$('.copy-mudule').show();
						$('.time-quota-div').show();
						// $('.real-time-quota-div').show();
						$('.time-capacity-mode').val('0');
						// $('.real-time-capacity-mode').val('0');
						$('.time-capacity-mode').on('change',function () {
							capacityModeChange('time-');
						});
						// $('.real-time-capacity-mode').on('change',function () {
						// 	capacityModeChange('real-time-');
						// });
						des += `<span class="help_link">`+ LANG.UI_TENANT_COPY_MODULE+`</span>`+ LANG.UI_TENANT_AUTH_BY_NUM+`
								<span class="help_link">`+ LANG.UI_TENANT_REAL_TIME_MODULE+`</span>`+ LANG.UI_TENANT_AUTH_BY_CAPACITY;
								// <span class="help_link">定时和实时模块</span>按容量分别授权`;
						license_type = 2;
						break;
					case 3:
						let vmDes = '';
						//第三大类才有单独的虚拟机授权方式
						switch(parseInt(data.module_info.vm.license_type)){
							case 1: //按宿主机(HOST)个数授权
								$('.vm-label').html(LANG.UI_TENANT_HOST_NUM);
								$('.detail-vm-label').html(LANG.UI_TENANT_HOST_NUM);
								vmDes = `<span class="help_link">`+ LANG.UI_PUBLIC_VM+`</span>`+ LANG.UI_TENANT_AUTH_BY_HOST + `,`;
								break;
							case 2: //按处理器(CPU)个数授权
								$('.vm-label').html(LANG.UI_TENANT_CPU_NUM);
								$('.detail-vm-label').html(LANG.UI_TENANT_CPU_NUM);
								vmDes = `<span class="help_link">`+ LANG.UI_PUBLIC_VM+`</span>`+ LANG.UI_TENANT_AUTH_BY_CPU + `,`;
								break;
							case 4://按虚拟机(VM)个数授权
								$('.vm-label').html(LANG.UI_TENANT_AUTH_BY_VM_NUM);
								$('.detail-vm-label').html(LANG.UI_TENANT_AUTH_BY_VM_NUM);
								vmDes = `<span class="help_link">`+ LANG.UI_PUBLIC_VM+`</span>`+ LANG.UI_TENANT_AUTH_BY_VM + `,`;
								break;
						}
						switch (SECOND_TYPE) {//二级授权
							case 1://虚拟机单独授权，其他模块均为数量授权
								$('.quota-content').hide();
								$('.num-div').show();
								$('.agent-mudule').hide();
								des += vmDes + `<span class="help_link">`+ LANG.UI_TENANT_OTHRE_MODULE+`</span>` +LANG.UI_TENANT_ALL_AUTH_BY_NUM;
								license_type = 3;
								break;
							case 2://虚拟机单独授权，文件、数据库、整机为客户端数量授权，其他模块均为数量授权 
								$('.quota-content').hide();
								$('.num-div').show();
								$('.machine-div').hide();
								$('.db-div').hide();
								$('.file-div').hide();
								des += vmDes + `<span class="help_link">`+ UI_TENANT_FS_DB_MACHINE +`</span>`+ LANG.UI_TENANT_AUTH_BY_AGENT+`
										<span class="help_link">`+ LANG.UI_TENANT_OTHRE_MODULE+`</span>`+ LANG.UI_TENANT_ALL_AUTH_BY_NUM;
								license_type = 4;
								break;
							case 3://虚拟机单独授权，文件系统为容量授权，其他模块均为数量授权 
								$('.num-div').show();
								$('.agent-mudule').hide();
								$('.file-mudule').hide();
								$('.file-quota-div').show();
								
								$('.file-capacity-mode').val('0');
								$('.file-capacity-mode').on('change',function () {
									capacityModeChange('file-');
								});
								des += `<span class="help_link">`+ LANG.UI_TENANT_FILE_SYSTEM +`</span>`+ LANG.UI_TENANT_AUTH_BY_CAPACITY2 +`
										`+ vmDes + `
										<span class="help_link">`+ LANG.UI_TENANT_OTHRE_MODULE+`</span>`+ LANG.UI_TENANT_ALL_AUTH_BY_NUM;
								license_type = 5;
								break;
							case 4://虚拟机单独授权，文件系统为容量授权；数据库、整机为客户端数量授权；其他模块均为数量授权 
								$('.num-div').show();
								$('.machine-div').hide();
								$('.db-div').hide();
								$('.file-mudule').hide();
								$('.file-quota-div').show();
								$('.file-capacity-mode').val('0');
								$('.file-capacity-mode').on('change',function () {
									capacityModeChange('file-');
								});
								des += `<span class="help_link">`+ LANG.UI_TENANT_FILE_SYSTEM +`</span>`+ LANG.UI_TENANT_AUTH_BY_CAPACITY2 +`
										`+ vmDes + `
										<span class="help_link">`+ LANG.UI_TENANT_DB_MACHINE +`</span>`+ LANG.UI_TENANT_AUTH_BY_AGENT+`
										<span class="help_link">`+ LANG.UI_TENANT_OTHRE_MODULE+`</span>`+ LANG.UI_TENANT_ALL_AUTH_BY_NUM;
								license_type = 6;
								break;
						}
						break;
				}
				$('#authMode').html(des);
			}
		});
	}
	var initAddModal = function () {
		editFlag = false;
		$('.title-des').html('<i class="viconfont vicon-danchuangtianjia1 mr8"></i>' + LANG.UI_TENANT_ADD);
		//初始化显示第一步
		$('#tenantDrawer').bootstrapWizard('first');
		// 清除上一次表单验证结果
		initFormStyle();
		$('.normal-quota-input-div').spinner("value", 0);
		$('#normal-quota-unit').val('GB');
		$('.time-quota-input-div').spinner("value", 0);
		$('#time-quota-unit').val('GB');
		// $('.real-time-quota-input-div').spinner("value", 0);
		// $('#real-time-quota-unit').val('GB');
		$('.file-quota-input-div').spinner("value", 0);
		$('#file-quota-unit').val('GB');
		$('.vmNumDiv').spinner("value", 0);
		$('.agentNumDiv').spinner("value", 0);
		$('.awsNumDiv').spinner("value", 0);
		$('.opsNumDiv').spinner("value", 0);
		$('.fileNumDiv').spinner("value", 0);
		$('.dbNumDiv').spinner("value", 0);
		$('.osNumDiv').spinner("value", 0);
		$('.nasNumDiv').spinner("value", 0);
		$('.m365NumDiv').spinner("value", 0);
		$('.m365OnlineNumDiv').spinner("value", 0);
		$('.obsNumDiv').spinner("value", 0);
		$('.hadoopNumDiv').spinner("value", 0);
		$('.k8sNumDiv').spinner("value", 0);
		$('.machinecopyNumDiv').spinner("value", 0);
		$('.filecopyNumDiv').spinner("value", 0);
		$('.nascopyNumDiv').spinner("value", 0);
		$('.dbcdpcopyNumDiv').spinner("value", 0);
		$('#remarks').val('');
		$('#admin_password').val('').attr('placeholder', '');
		$('#confirm_password').val('').attr('placeholder', '');
		$('#admin_email').val('');
		$('#tenant_name').attr('disabled',false).val('');
		$('#user_name').attr('disabled',false).val('');
		initTenantHomepageData();//获取首页数据
	}
	var capacityModeChange = function (className) {
		if ($('.'+ className +'capacity-mode').val() == 0) {
			$('.'+ className +'diy-capacity').hide();
		} else {
			$('.'+ className +'diy-capacity').show();
		}
	}
	var initSpinner = function () {
		$('.normal-quota-input-div').spinner({value:20, step: 1, min: 1, max: 1000000000});
		$('.time-quota-input-div').spinner({value:20, step: 1, min: 1, max: 1000000000});
		// $('.real-time-quota-input-div').spinner({value:20, step: 1, min: 1, max: 1000000000});
		$('.file-quota-input-div').spinner({value:20, step: 1, min: 1, max: 1000000000});
		$('.vmNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.agentNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.awsNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.opsNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.fileNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.dbNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.osNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.nasNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.m365NumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.m365OnlineNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.obsNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.hadoopNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.k8sNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.machinecopyNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.filecopyNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.nascopyNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
		$('.dbcdpcopyNumDiv').spinner({value:0, step: 5, min: 0, max: 10000000});
	}

	//删除租户
	var deleteTenant = function(grid){
		var select = $('#tenant_table').bootstrapTable('getSelections');
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_TENANT_DELETE, LANG.UI_TENANT_DELETE_NO_SELECT);
		}
		if(select.length >1){
			return UIToastr.showInfo(LANG.UI_TENANT_DELETE, LANG.UI_TENANT_DELETE_SELECT_ONE);
		}
		bootbox.confirm({
			title: LANG.UI_TENANT_DELETE,
			message: LANG.UI_TENANT_DELETE_COFIRM_TITLE + "：<br>" +
				LANG.UI_TENANT_DELETE_COFIRM_TIPS1 + "<br>" +
				LANG.UI_TENANT_DELETE_COFIRM_TIPS2 + "<br>" +
				LANG.UI_TENANT_DELETE_COFIRM_TIPS3 + "<br>" +
				LANG.UI_TENANT_DELETE_COFIRM_TIPS4 + "<br>" ,
			callback: debounce(function(r){
				if(!r) return;
				initErrorFlag = false;
				deleteSuccessFlag =false;	//开始删除时初始化加载日志标志
				bootbox.prompt({
					title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES,
					inputType: 'password',
					callback: debounce(function (result) {
						if(result == null) return;
						if(hex_md5(result) == _UserPassword){
							Metronic.blockUI({target:".tenant-table",animate: true});
							pAjaxRequest({"tenant_uuid": select[0].tenant_uuid}, "/api/v1/tenant", "delete", function (result) {
								Metronic.unblockUI('.tenant-table');
								if(operateResponseList(result,LANG.UI_TENANT_DELETE)){
									$('#tenant_table').bootstrapTable('refresh');
								}else{
									hideModalFlag = true;
								}
								//延迟2s返回成功,多获取一次日志信息
								setTimeout(function(){deleteSuccessFlag = true;}, 2000);
							});
							return true;
						}else{
							$('.bootbox-input').css('border-color', "#a94442");
							if(!initErrorFlag){
								var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS+'</p>';
								$('.bootbox-input').after(des);
								initErrorFlag = true;
							}
							return false;
						}
					}, 300),
				});
			}, 300),
		});
	}


	//获取删除日志
	var getDeleteInfo = function(){
		var updateInterval = 2000;
		var setTimeoutId = null;

		var getInfo = function(){
			if(0 == $('#info').size()){
				clearTimeout(setTimeoutId);
				return;
			}

			$.post(CONF.AJAXPATH, {m:CONF.M.TENANT, f:'readTenantLog', p:{}}, function(d){
				$('#info').html(d);
				var scrollHeight = $("#info").get(0).scrollHeight;
				$('#info').scrollTop(scrollHeight);
				if(!deleteSuccessFlag){
					setTimeoutId = setTimeout(getInfo, updateInterval);
				}
			});
		}
		getInfo();
	}
	var toSearchTenant = function () {
		$('#tenant_table').bootstrapTable('refresh', {
			query: {
				"search":$.trim($('#tenant_manager_toolbar .tenantSearch').val())
			}
		});
	}

	var enableTenant = function () {
		var uuids = [];
		var select = $('#tenant_table').bootstrapTable('getSelections');
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_TENANT_ENABLE, LANG.UI_TENANT_ENABLE_NO_SELECT);
		}
		select.forEach(item => {
			uuids.push(item.tenant_uuid);
		});
		bootbox.confirm({
			title: LANG.UI_TENANT_ENABLE,
			message: LANG.UI_PLATFORM_TENANT_CONFIRM_SELEC,
			callback: debounce(function(r) {
				if(!r) return;
				Metronic.blockUI({target:".tenant-table",animate: true});
				pAjaxRequest({'uuids':uuids}, "/api/v1/tenant/enable", "post", function (result) {
					if(operateResponseList(result, result.message)) {
						$('#tenant_table').bootstrapTable('refresh');
					}
					Metronic.unblockUI('.tenant-table');
				});
			}, 300),
		});
	}

	var disableTenant = function () {
		var tenant_uuid = '';
		var select = $('#tenant_table').bootstrapTable('getSelections');
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_TENANT_DISABLE, LANG.UI_TENANT_DISABLE_NO_SELECT);
		}
		if(select.length > 1){
			return UIToastr.showInfo(LANG.UI_TENANT_DISABLE, LANG.UI_TENANT_DISABLE_SELECT_ONE);
		}
		select.forEach(item => {
			tenant_uuid = item.tenant_uuid;
		});
		bootbox.confirm({
			title:  LANG.UI_TENANT_DISABLE,
			message: LANG.UI_TENANT_DISABLE_CONFIRM + "：<br>" +
					 LANG.UI_PLATFORM_TENANT_DISABLE_LOGIN + "<br>" +
					 LANG.UI_PLATFORM_TENANT_STOP_TASK,
			callback: debounce(function(r) {
				if(!r) return;
				initErrorFlag = false;
                bootbox.prompt({
                    title: LANG.UI_PLATFORM_TENANT_INPUT_PSW,
                    inputType: 'password',
                    callback: debounce(function (result) {
                        if (result == null) return;
                        if (hex_md5(result) == _UserPassword) {
							Metronic.blockUI({target:".tenant-table",animate: true});
							pAjaxRequest({'tenant_uuid':tenant_uuid}, "/api/v1/tenant/disable", "post", function (result) {
								if(operateResponseList(result, result.message)) {
									$('#tenant_table').bootstrapTable('refresh');
								}
								Metronic.unblockUI('.tenant-table');
							});
                            return true;
                        } else {
                            $('.bootbox-input').css('border-color', "#a94442");
                            if (!initErrorFlag) {
                                var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS + '</p>';
                                $('.bootbox-input').after(des);
                                initErrorFlag = true;
                            }
                            return false;
                        }
                    }, 300),
                });
			}, 300),
		});
	}

	var initTenantHomepageData = function (row) {
		let tenant_uuid = row ? row.tenant_uuid : '';
		let user_uuid = row ? row.user_uuid : '';
		let backup_data = row ? row.backup_data : '';
		//详情数据展示
		pAjaxRequest({"tenant_uuid":tenant_uuid,"user_uuid":user_uuid,"backup_days":30}, "/api/v1/tenant/homepage_info", "get", function (result) {
			if(result.success) {
				//详情
				var info = result.data.module_info;
				switch (license_type) {
					case 1: //除了复制，其余模块按容量
						$('.card-parent-storage').show();
						$('.detail-storage').html(backup_data);
						break;
					case 2: //除了复制，其余模块按容量，实时、定时分开，实时暂不支持
						$('.card-parent-storage').show();
						$('.detail-storage').html(backup_data);
						break;
					case 3: //全按数量
						$('.detail-vm').html(info.vm.auth_used + '/' + info.vm.auth_total);
						$('.detail-aws').html(info.public_cloud.auth_used + '/' + info.public_cloud.auth_total);
						$('.detail-ops').html(info.private_cloud.auth_used + '/' + info.private_cloud.auth_total);
						$('.detail-db').html(info.db.auth_used + '/' + info.db.auth_total);
						$('.detail-os').html(info.os.auth_used + '/' + info.os.auth_total);
						$('.detail-nas').html(info.nas.auth_used + '/' + info.nas.auth_total);
						$('.detail-m365').html(info.m365.auth_used + '/' + info.m365.auth_total);
						$('.detail-m365-online').html(info.exchange_online.auth_used + '/' + info.exchange_online.auth_total);
						$('.detail-file').html(info.fs.auth_used + '/' + info.fs.auth_total);
						$('.detail-hadoop').html(info.hadoop.auth_used + '/' + info.hadoop.auth_total);
						$('.detail-obs').html(info.obs.auth_used + '/' + info.obs.auth_total);
						$('.detail-k8s').html(info.kubernetes.auth_used + '/' + info.kubernetes.auth_total);
						break;
					case 4: //文件、数据库、整机按客户端数量，其余模块按数量
						$('.detail-vm').html(info.vm.auth_used + '/' + info.vm.auth_total);
						$('.detail-agent').html(info.db.auth_used + '/' + info.db.auth_total);
						$('.detail-aws').html(info.public_cloud.auth_used + '/' + info.public_cloud.auth_total);
						$('.detail-ops').html(info.private_cloud.auth_used + '/' + info.private_cloud.auth_total);
						$('.detail-nas').html(info.nas.auth_used + '/' + info.nas.auth_total);
						$('.detail-m365').html(info.m365.auth_used + '/' + info.m365.auth_total);
						$('.detail-m365-online').html(info.exchange_online.auth_used + '/' + info.exchange_online.auth_total);
						$('.detail-hadoop').html(info.hadoop.auth_used + '/' + info.hadoop.auth_total);
						$('.detail-obs').html(info.obs.auth_used + '/' + info.obs.auth_total);
						$('.detail-k8s').html(info.kubernetes.auth_used + '/' + info.kubernetes.auth_total);
						break;
					case 5: //文件系列按容量，其余模块按数量
						$('.card-parent-storage').show();
						$('.detail-storage').html(backup_data);
						$('.detail-vm').html(info.vm.auth_used + '/' + info.vm.auth_total);
						$('.detail-aws').html(info.public_cloud.auth_used + '/' + info.public_cloud.auth_total);
						$('.detail-ops').html(info.private_cloud.auth_used + '/' + info.private_cloud.auth_total);
						$('.detail-db').html(info.db.auth_used + '/' + info.db.auth_total);
						$('.detail-os').html(info.os.auth_used + '/' + info.os.auth_total);
						$('.detail-m365').html(info.m365.auth_used + '/' + info.m365.auth_total);
						$('.detail-m365-online').html(info.exchange_online.auth_used + '/' + info.exchange_online.auth_total);
						$('.detail-k8s').html(info.kubernetes.auth_used + '/' + info.kubernetes.auth_total);
						break;
					case 6: //文件系列按容量，数据库、整机按客户端数量，其余模块按数量
						$('.card-parent-storage').show();
						$('.detail-storage').html(backup_data);
						$('.detail-vm').html(info.vm.auth_used + '/' + info.vm.auth_total);
						$('.detail-agent').html(info.db.auth_used + '/' + info.db.auth_total);
						$('.detail-aws').html(info.public_cloud.auth_used + '/' + info.public_cloud.auth_total);
						$('.detail-ops').html(info.private_cloud.auth_used + '/' + info.private_cloud.auth_total);
						$('.detail-m365').html(info.m365.auth_used + '/' + info.m365.auth_total);
						$('.detail-m365-online').html(info.exchange_online.auth_used + '/' + info.exchange_online.auth_total);
						$('.detail-k8s').html(info.kubernetes.auth_used + '/' + info.kubernetes.auth_total);
						break;
				}
				$('.detail-machinecopy').html(info.os_copy.auth_used + '/' + info.os_copy.auth_total);
				$('.detail-filecopy').html(info.fs_copy.auth_used + '/' + info.fs_copy.auth_total);
				$('.detail-nascopy').html(info.nas_copy.auth_used + '/' + info.nas_copy.auth_total);
				$('.detail-dbcdpcopy').html(info.db_copy.auth_used + '/' + info.db_copy.auth_total);
			}
		});
		// 添加/修改(获取所有模块剩余的授权数量)
		pAjaxRequest({"tenant_uuid":tenant_uuid}, "/api/v1/tenant/auth_num", "get", function (result) {
			// auth_type为2是按容量
			if(result.success) {
				var data = result.data;
				switch (license_type) {
					case 3: //全按数量
						$('.vm-free-num').html(data.vm.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.vm.valid);
						$('.aws-free-num').html(data.cloud.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.cloud.valid);
						$('.ops-free-num').html(data.private_cloud.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.private_cloud.valid);
						$('.db-free-num').html(data.oracle.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.oracle.valid);
						$('.os-free-num').html(data.os.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.os.valid);
						$('.nas-free-num').html(data.nas.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.nas.valid);
						$('.m365-free-num').html(data.exchange.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.exchange.valid);
						$('.m365Online-free-num').html(data.exchange_online.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.exchange_online.valid);
						$('.file-free-num').html(data.file.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.file.valid);
						$('.hadoop-free-num').html(data.hadoop.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.hadoop.valid);
						$('.obs-free-num').html(data.obs.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.obs.valid);
						$('.k8s-free-num').html(data.k8s.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.k8s.valid);
						data.show_flag.vm && moduleShowList.push('vm');
						data.show_flag.cloud && moduleShowList.push('aws');
						data.show_flag.private_cloud && moduleShowList.push('ops');
						data.show_flag.oracle && moduleShowList.push('db');
						(data.show_flag.machine_os || data.show_flag.os) && moduleShowList.push('os');
						data.show_flag.nas && moduleShowList.push('nas');
						data.show_flag.exchange && moduleShowList.push('m365');
						data.show_flag.exchange_online && moduleShowList.push('m365Online');
						data.show_flag.file && moduleShowList.push('file');
						data.show_flag.hadoop && moduleShowList.push('hadoop');
						data.show_flag.obs && moduleShowList.push('obs');
						data.show_flag.k8s && moduleShowList.push('k8s');
						break;
					case 4: //文件、数据库、整机按客户端数量，其余模块按数量
						$('.vm-free-num').html(data.vm.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.vm.valid);
						$('.agent-free-num').html(data.agent.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.agent.valid);
						$('.aws-free-num').html(data.cloud.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.cloud.valid);
						$('.ops-free-num').html(data.private_cloud.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.private_cloud.valid);
						$('.nas-free-num').html(data.nas.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.nas.valid);
						$('.m365-free-num').html(data.exchange.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.exchange.valid);
						$('.m365Online-free-num').html(data.exchange_online.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.exchange_online.valid);
						$('.hadoop-free-num').html(data.hadoop.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.hadoop.valid);
						$('.obs-free-num').html(data.obs.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.obs.valid);
						$('.k8s-free-num').html(data.k8s.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.k8s.valid);
						data.show_flag.vm && moduleShowList.push('vm');
						(data.show_flag.file || data.show_flag.machine_os || data.show_flag.os || data.show_flag.oracle) && moduleShowList.push('agent');
						data.show_flag.cloud && moduleShowList.push('aws');
						data.show_flag.private_cloud && moduleShowList.push('ops');
						data.show_flag.nas && moduleShowList.push('nas');
						data.show_flag.exchange && moduleShowList.push('m365');
						data.show_flag.exchange_online && moduleShowList.push('m365Online');
						data.show_flag.hadoop && moduleShowList.push('hadoop');
						data.show_flag.obs && moduleShowList.push('obs');
						data.show_flag.k8s && moduleShowList.push('k8s');
						break;
					case 5: //文件系列按容量，其余模块按数量
						$('.vm-free-num').html(data.vm.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.vm.valid);
						$('.aws-free-num').html(data.cloud.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.cloud.valid);
						$('.ops-free-num').html(data.private_cloud.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.private_cloud.valid);
						$('.db-free-num').html(data.oracle.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.oracle.valid);
						$('.os-free-num').html(data.os.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.os.valid);
						$('.m365-free-num').html(data.exchange.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.exchange.valid);
						$('.m365Online-free-num').html(data.exchange_online.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.exchange_online.valid);
						$('.k8s-free-num').html(data.k8s.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.k8s.valid);
						data.show_flag.vm && moduleShowList.push('vm');
						data.show_flag.cloud && moduleShowList.push('aws');
						data.show_flag.private_cloud && moduleShowList.push('ops');
						data.show_flag.oracle && moduleShowList.push('db');
						(data.show_flag.machine_os || data.show_flag.os) && moduleShowList.push('os');
						data.show_flag.exchange && moduleShowList.push('m365');
						data.show_flag.exchange_online && moduleShowList.push('m365Online');
						data.show_flag.k8s && moduleShowList.push('k8s');
						break;
					case 6: //文件系列按容量，数据库、整机按客户端数量，其余模块按数量
						$('.vm-free-num').html(data.vm.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.vm.valid);
						$('.agent-free-num').html(data.agent.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.agent.valid);
						$('.aws-free-num').html(data.cloud.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.cloud.valid);
						$('.ops-free-num').html(data.private_cloud.auth_type == 2 ? LANG.UI_SETTING_AUTH_UNLIMITED : data.private_cloud.valid);
						$('.m365-free-num').html(data.exchange.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.exchange.valid);
						$('.m365Online-free-num').html(data.exchange_online.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.exchange_online.valid);
						$('.k8s-free-num').html(data.k8s.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.k8s.valid);
						data.show_flag.vm && moduleShowList.push('vm');
						(data.show_flag.machine_os || data.show_flag.os || data.show_flag.oracle) && moduleShowList.push('agent');
						data.show_flag.cloud && moduleShowList.push('aws');
						data.show_flag.private_cloud && moduleShowList.push('ops');
						data.show_flag.exchange && moduleShowList.push('m365');
						data.show_flag.exchange_online && moduleShowList.push('m365Online');
						data.show_flag.k8s && moduleShowList.push('k8s');
						break;
				}
				$('.machinecopy-free-num').html(data.machinecopy.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.machinecopy.valid);
				$('.filecopy-free-num').html(data.filecopy.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.filecopy.valid);
				$('.nascopy-free-num').html(data.nascopy.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.nascopy.valid);
				$('.dbcdpcopy-free-num').html(data.dbcdpcopy.des == LANG.UI_SETTING_AUTH_UNLIMITED ? LANG.UI_SETTING_AUTH_UNLIMITED : data.dbcdpcopy.valid);
				(data.show_flag.copy_os || data.show_flag.copy_machine) && moduleShowList.push('machinecopy');
				data.show_flag.copy_fs && moduleShowList.push('filecopy');
				data.show_flag.copy_nas && moduleShowList.push('nascopy');
				data.show_flag.copy_db && moduleShowList.push('dbcdpcopy');
			}
		});
		//获取可以给租户分配的剩余容量
		pAjaxRequest({}, "/api/v1/tenant/free_size", "get", function (result) {
			let des = '0B';
			let size = 0;
			if(result.success) {
				switch (license_type) {
					case 1://普通容量模式
						des = result.data.avail < 0 ? '0B' : result.data.avail_des;
						size = result.data.avail < 0 ? 0 : result.data.avail;
						$('.normal-free-size').html(des);
						$('.normal-free-size').attr("freeSize", size);
						break;
					case 2://实时容量和定时容量分开
						des = result.data.time.avail < 0 ? '0B' : result.data.time.avail_des;
						size = result.data.time.avail < 0 ? 0 : result.data.time.avail;
						$('.time-free-size').html(des);
						$('.time-free-size').attr("freeSize", size);
						// $('.real-time-free-size').html(LANG.UI_SETTING_AUTH_UNLIMITED);
						break;
					case 5:
					case 6://文件系列按容量
						des = result.data.avail < 0 ? '0B' : result.data.avail_des;
						size = result.data.avail < 0 ? 0 : result.data.avail;
						$('.file-free-size').html(des);
						$('.file-free-size').attr("freeSize", size);
						break;
					case 3:
					case 4://按数量，容量无限制
						break;
				}
			}
		});
		clearInputStyle();
	}


	var initDataTable = function(){
		var afterDiv = '';
		var beforeDiv = '';
		if ($.inArray('p_tenant_manager_delete', CONF.PERMISSION_ARR) !== -1 ) {
			beforeDiv = '<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="deleteTenant"></button></div>';
		}
		if ($.inArray('p_tenant_manager_add', CONF.PERMISSION_ARR) !== -1 ){
			afterDiv += `<button class="btn table-toolbar-btn" id="addTenant" data-toggle="drawer" data-target="#tenantDrawer">
                                <i class="viconfont vicon-biaogetianjia mr4"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + `</span>
                            </button>`;
		}
		if ($.inArray('p_tenant_manager_enable', CONF.PERMISSION_ARR) !== -1 ) {
			afterDiv += `<button class="btn table-toolbar-btn" id="enableTenant">
                                <i class="viconfont vicon-a-Unlockjiesuo-0111 mr4"></i>
                                <span>` + LANG.UI_RECOVERY_AWS_ENABLE + `</span>
                            </button>`;
		}
		if ($.inArray('p_tenant_manager_disable', CONF.PERMISSION_ARR) !== -1 ) {
			afterDiv += `<button class="btn table-toolbar-btn" id="disableTenant">
                                <i class="viconfont vicon-a-Unlockjiesuo-011 mr4"></i>
                                <span>` + LANG.UI_RECOVERY_AWS_DISABLE + `</span>
                            </button>`;
		}
		afterDiv = afterDiv != '' ? '<div>' + afterDiv + '</div>' : '';
		var options = {
			toolbarId: '#tenant_manager_toolbar',
			buttonsToolbar: '#tenant_manager_toolbar .vin_btnToolbar',
			placeholder: LANG.BILLING_SEARCH, //搜索框的placeholder
			searchInput: true, //搜索框
			searchClass: 'tenantSearch', //自定义的搜索框类名
			searchSelector: '.tenantSearch', //选择使用自定义搜索框
			customTool: {
				beforeInput: beforeDiv,
				afterInput: afterDiv,
			},
			pagination:true,
			pageList: [5,10,25,50],
			// detailView:true,
			// pa:{},
			vin_url:"/api/v1/tenant",
			vin_method:"GET",
			fileName:LANG.UI_TENANT_LIST,
			columns:[{
				checkbox:true,
				sortable: false,
			},
				{
					field: 'tenant_name',
					title: LANG.UI_TENANT_NAME_DES,
					sortable: true,
					align: 'center',
				},
				{
					field: 'nickname',
					title: LANG.UI_TENANT_DESCRIPTION,
					sortable: true,
					align: 'center',
				},
				{
					field: 'user_name',
					title: LANG.UI_TENANT_ADMIN,
					sortable: true,
					align: 'center',
				},
				{
					field: 'backup_data',
					title: LANG.UI_HOMEPAGEPRO_BACKUP_DATA,
					sortable: false,
					align: 'center',
				},
				{
					field: 'lock_flag',
					title: LANG.UI_PUBLIC_STATUS,
					sortable: true,
					align: 'center',
					formatter: function (value,data,row) {
						var thisClass = "";
						var des = '';
						if(1 == value){
							thisClass = "label-success";
							des = LANG.UI_RECOVERY_AWS_ENABLE;
						}else if(2 == value){
							thisClass = "label-danger";
							des = LANG.UI_RECOVERY_AWS_DISABLE;
						}
						return '<span class="label label-sm ' + thisClass + '">' + des + '</span>';
					}
				},
				{
					field: 'create_time',
					title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME,
					sortable: true,
					align: 'center',
				},
				{
					field: 'operate',
					title: LANG.UI_PUBLIC_OPERATION,
					sortable: false,
					align: 'center',
					events: opEvent,
					clickToSelect: false, //不可通过点击行选中
					// type: "operation",
					formatter: function (value,data,row) {
						if (!($.inArray('p_tenant_manager_edit', CONF.PERMISSION_ARR) !== -1)) {
							// 都没得 那么就不显示操作按钮了
							return '-';
						}
						//1 挂载  2 解挂
						var button = '';
						button += `<div class="btn-group"><button style="line-height: 16px;" type="button" class="btn btn-primary brr2 p-lr8 btn-sm dropdown-toggle" data-toggle="dropdown"
						data-hover="dropdown" aria-haspopup="true" data-delay="1000">
						${LANG.UI_PUBLIC_OPERATION}<i class="fa fa-angle-down ml10"></i></button><ul class="dropdown-menu min-width100">`;
						if ($.inArray('p_tenant_manager_edit', CONF.PERMISSION_ARR) !== -1) {
							button += '<li class="modify" data-toggle="drawer" data-target="#tenantDrawer"><a href="javascript:;" ><i class="viconfont vicon-a-Editbianji1"></i> ' + LANG.UI_JOB_MODIFY + '</a></li>';
						}
						if ($.inArray('p_tenant_manager_edit', CONF.PERMISSION_ARR) !== -1) {
							button += '<li class="detail" data-toggle="drawer" data-target="#tenantDetail"><a href="javascript:;"><i class="viconfont vicon-xiangqing"></i> ' + LANG.UI_MICROSOFT365_VIEW_DETAILS + '</a></li>';
						}
						button += '</ul></div>';
						$('[data-hover="dropdown"]').dropdownHover();
						return button;
					}
				},
			],
			onPostBody: function () {
				//加载完毕
				$('#tenant_table tr td:last-child').each(function() {
					// 移除overflow属性
					$(this).css('overflow', '');
				});
				modifyDelStyle('tenant_table', 'deleteTenant');
				//旧版本数据修改提示
				var row = $('#tenant_table').bootstrapTable('getData');
				if (row.length != 0 && row[0].old_version && firstFlag) {
					UIToastr.showWarning(LANG.UI_TENANT_RECONFIGURE_DATA,LANG.UI_TENANT_NAME+ row[0].tenant_name +LANG.UI_TENANT_RECONFIGURE_DATA_TIPS);
					firstFlag = false;
					return;
				}
			},
			onCheck: function () {
				modifyDelStyle('tenant_table', 'deleteTenant');
			},
			onUncheck: function () {
				modifyDelStyle('tenant_table', 'deleteTenant');
			},
			onCheckAll: function () {
				modifyDelStyle('tenant_table', 'deleteTenant');
			},
			onUncheckAll: function () {
				modifyDelStyle('tenant_table', 'deleteTenant');
			},
		}
		sessionStorage.removeItem("table_pageRecord");
		$('#tenant_table').baseTableConfig().init(options);
		modifyDelStyle('tenant_table', 'deleteTenant');
		//绑定事件
		toBindEvent();
	}

	var toBindEvent = function () {
		$('#addTenant').on('click',function () {
			initAddModal();
		});
		//删除租户
		$('#deleteTenant').on('click', function () {
			deleteTenant();
		});
		$('#disableTenant').on('click', function () {//禁用租户
			disableTenant();
		});
		$('#enableTenant').on('click',function () {//启用租户
			enableTenant();
		});
		//搜索
		$('#tenant_manager_toolbar').on('click', ' .b-btn.search-btn', function () {
			toSearchTenant()
		});
		$('#tenant_manager_toolbar .clear').on('click',function(){
			$('#tenant_manager_toolbar .tenantSearch').val('');
			$('#tenant_table').bootstrapTable('refresh', {
				query: {
					"search": ''
				}
			});
		});
	}

	var opEvent = {
		'click .detail': function (event, value, row, index) {
			initUserTable(row);
			initTenantHomepageData(row);
			$('.detail-tenant-name').html('(' + row.tenant_name + ')');
			// var recoveryDiv = ''
			// row.config.host_list_des.forEach(item => {
			// 	recoveryDiv += '<div><span  class="img" style="background-position: '+ getHypervisorImg(item.type) +'"></span>'+ item.name +'</div>'
			// })
			// $('.recovery-display').html(recoveryDiv);
			var storageDiv = ''
			row.config.storage_list_des.forEach(item => {
				storageDiv += '<div><i class="levelchild viconfont vicon-cunchu"></i>'+ item +'</div>'
			})
			$('.storage-display').html(storageDiv);
		},
		'click .modify': function (event, value, row, index) {
			$('.title-des').html('<i class="viconfont vicon-xiugai mr8"></i>' + LANG.UI_TENANT_EDIT);
			initFormStyle();
			$('#tenant_uuid').val(row.tenant_uuid);
			$('#user_uuid').val(row.user_uuid);
			editFlag = true;
			checked_nodes = row.config.host_list;
			initTenantHomepageData(row);//获取首页数据
			$('#tenantDrawer').bootstrapWizard('first');
			//清除上一次表单验证结果
			$('#selectStorage').selectpicker('val',row.config.storage_list);
			switch (license_type) {
				case 1: //普通容量 + 复制按数量
					$('.normal-capacity-mode').val(row.config.normal_capacity_mode ?? 0);
					if (row.config.normal_capacity_mode == 1) {
						$('.normal-quota-input-div').spinner("value", row.config.normal_quota_show_size);
						$('#normal-quota-unit').val(row.config.normal_quota_show_unit);
					}
					capacityModeChange('normal-');
					$('.machinecopyNumDiv').spinner("value", row.config.machinecopy_num);
					$('.filecopyNumDiv').spinner("value", row.config.filecopy_num);
					$('.nascopyNumDiv').spinner("value", row.config.nascopy_num);
					$('.dbcdpcopyNumDiv').spinner("value", row.config.dbcdpcopy_num);
					break;
				case 2: //实时和定时容量分开（不支持实时，暂时只有定时） + 复制按数量
					$('.time-capacity-mode').val(row.config.time_capacity_mode ?? 0);
					if (row.config.time_capacity_mode == 1) {
						$('.time-quota-input-div').spinner("value", row.config.time_quota_show_size);
						$('#time-quota-unit').val(row.config.time_quota_show_unit);
					}
					capacityModeChange('time-');
					$('.machinecopyNumDiv').spinner("value", row.config.machinecopy_num);
					$('.filecopyNumDiv').spinner("value", row.config.filecopy_num);
					$('.nascopyNumDiv').spinner("value", row.config.nascopy_num);
					$('.dbcdpcopyNumDiv').spinner("value", row.config.dbcdpcopy_num);
					break;
				case 3: //全按数量
					$('.vmNumDiv').spinner("value", row.config.vm_num);
					// $('.agentNumDiv').spinner("value", row.config.agent_num);
					$('.awsNumDiv').spinner("value", row.config.aws_num);
					$('.opsNumDiv').spinner("value", row.config.ops_num);
					$('.fileNumDiv').spinner("value", row.config.file_num);
					$('.dbNumDiv').spinner("value", row.config.db_num);
					$('.osNumDiv').spinner("value", row.config.os_num);
					$('.nasNumDiv').spinner("value", row.config.nas_num);
					$('.m365NumDiv').spinner("value", row.config.m365_num);
					$('.m365OnlineNumDiv').spinner("value", row.config.m365_online_num);
					$('.obsNumDiv').spinner("value", row.config.obs_num);
					$('.hadoopNumDiv').spinner("value", row.config.hadoop_num);
					$('.k8sNumDiv').spinner("value", row.config.k8s_num);
					$('.machinecopyNumDiv').spinner("value", row.config.machinecopy_num);
					$('.filecopyNumDiv').spinner("value", row.config.filecopy_num);
					$('.nascopyNumDiv').spinner("value", row.config.nascopy_num);
					$('.dbcdpcopyNumDiv').spinner("value", row.config.dbcdpcopy_num);
					break;
				case 4: //文件、数据库、整机按客户端数量，其余模块按数量
					$('.vmNumDiv').spinner("value", row.config.vm_num);
					$('.agentNumDiv').spinner("value", row.config.agent_num);
					$('.awsNumDiv').spinner("value", row.config.aws_num);
					$('.opsNumDiv').spinner("value", row.config.ops_num);
					// $('.fileNumDiv').spinner("value", row.config.file_num);
					// $('.dbNumDiv').spinner("value", row.config.db_num);
					// $('.osNumDiv').spinner("value", row.config.os_num);
					$('.nasNumDiv').spinner("value", row.config.nas_num);
					$('.m365NumDiv').spinner("value", row.config.m365_num);
					$('.m365OnlineNumDiv').spinner("value", row.config.m365_online_num)
					$('.obsNumDiv').spinner("value", row.config.obs_num);
					$('.hadoopNumDiv').spinner("value", row.config.hadoop_num);
					$('.k8sNumDiv').spinner("value", row.config.k8s_num);
					$('.machinecopyNumDiv').spinner("value", row.config.machinecopy_num);
					$('.filecopyNumDiv').spinner("value", row.config.filecopy_num);
					$('.nascopyNumDiv').spinner("value", row.config.nascopy_num);
					$('.dbcdpcopyNumDiv').spinner("value", row.config.dbcdpcopy_num);
					break;
				case 5: //文件系列按容量，其余模块按数量
					$('.file-capacity-mode').val(row.config.file_capacity_mode ?? 0);
					if (row.config.file_capacity_mode == 1) {
						$('.file-quota-input-div').spinner("value", row.config.file_quota_show_size);
						$('#file-quota-unit').val(row.config.file_quota_show_unit);
					}
					capacityModeChange('file-');
					$('.vmNumDiv').spinner("value", row.config.vm_num);
					// $('.agentNumDiv').spinner("value", row.config.agent_num);
					$('.awsNumDiv').spinner("value", row.config.aws_num);
					$('.opsNumDiv').spinner("value", row.config.ops_num);
					// $('.fileNumDiv').spinner("value", row.config.file_num);
					$('.dbNumDiv').spinner("value", row.config.db_num);
					$('.osNumDiv').spinner("value", row.config.os_num);
					// $('.nasNumDiv').spinner("value", row.config.nas_num);
					$('.m365NumDiv').spinner("value", row.config.m365_num);
					$('.m365OnlineNumDiv').spinner("value", row.config.m365_online_num)
					// $('.obsNumDiv').spinner("value", row.config.obs_num);
					// $('.hadoopNumDiv').spinner("value", row.config.hadoop_num);
					$('.k8sNumDiv').spinner("value", row.config.k8s_num);
					$('.machinecopyNumDiv').spinner("value", row.config.machinecopy_num);
					$('.filecopyNumDiv').spinner("value", row.config.filecopy_num);
					$('.nascopyNumDiv').spinner("value", row.config.nascopy_num);
					$('.dbcdpcopyNumDiv').spinner("value", row.config.dbcdpcopy_num);
					break;
				case 6: //文件系列按容量，数据库、整机按客户端数量，其余模块按数量
					$('.file-capacity-mode').val(row.config.file_capacity_mode ?? 0);
					if (row.config.file_capacity_mode == 1) {
						$('.file-quota-input-div').spinner("value", row.config.file_quota_show_size);
						$('#file-quota-unit').val(row.config.file_quota_show_unit);
					}
					capacityModeChange('file-');
					$('.vmNumDiv').spinner("value", row.config.vm_num);
					$('.agentNumDiv').spinner("value", row.config.agent_num);
					$('.awsNumDiv').spinner("value", row.config.aws_num);
					$('.opsNumDiv').spinner("value", row.config.ops_num);
					// $('.fileNumDiv').spinner("value", row.config.file_num);
					// $('.dbNumDiv').spinner("value", row.config.db_num);
					// $('.osNumDiv').spinner("value", row.config.os_num);
					// $('.nasNumDiv').spinner("value", row.config.nas_num);
					$('.m365NumDiv').spinner("value", row.config.m365_num);
					$('.m365OnlineNumDiv').spinner("value", row.config.m365_online_num)
					// $('.obsNumDiv').spinner("value", row.config.obs_num);
					// $('.hadoopNumDiv').spinner("value", row.config.hadoop_num);
					$('.k8sNumDiv').spinner("value", row.config.k8s_num);
					$('.machinecopyNumDiv').spinner("value", row.config.machinecopy_num);
					$('.filecopyNumDiv').spinner("value", row.config.filecopy_num);
					$('.nascopyNumDiv').spinner("value", row.config.nascopy_num);
					$('.dbcdpcopyNumDiv').spinner("value", row.config.dbcdpcopy_num);
					break;
			}
			pAjaxRequest({'tenant_uuid':row.tenant_uuid}, "/api/v1/tenant/edit", "get", function (result) {
				if(result.success) {
					var admin_uuid = result.data.admin_uuid;
					var config = result.data.config;
					$('#tenant_name').val(result.data.tenant_name);
					$('#remarks').val(result.data.remarks);
					$('#user_name').val(result.data.admin_username);
					$('#admin_password').val('').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
					$('#confirm_password').val('').attr('placeholder', LANG.UI_PUBLIC_MODIFY_PASSWORD_TIPS);
					$('#admin_email').val(result.data.admin_email);
					$('#tenant_name').attr('disabled',true);//修改租户不允许修改租户名
					$('#user_name').attr('disabled',true);//修改租户不允许修改管理员账户
				}
			});
		},
	}

	var initFormStyle = function () {
		$('#remarks').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
		$('#admin_password').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
		$('#confirm_password').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
		$('#admin_email').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
		$('#tenant_name').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
		$('#user_name').siblings("i").removeClass().addClass("fa").parents(".form-group").removeClass().addClass("form-group");
	}
	
	//初始化当前用户密码用于删除二次确认
	var initUserPassword = function(){
		$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
			var data = JSON.parse(d);
			_UserPassword = data.password;
		});
	}
	var initUsername = function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getUserExtendInfo',p:{}}, function(d){
			var data = JSON.parse(d);
			_Username = data.username;
		});
	}

	var getHypervisorImg = function (type) {
		var style = '';
		switch (parseInt(type)) {
			case 1:
			case 21:
				style = '-347px -286px';
				break;
			case 2:
				style = '-286px -164px';
				break;
			case 3:
				style = '-164px -42px';
				break;
			case 4:
				style = '-103px -164px';
				break;
			case 5:
				style = '';//没有这个虚拟化
				break;
			case 6:
			case 29:
				style = '-406px -164px';
				break;
			case 7:
			case 34:
				style = '-162px -286px';
				break;
			case 8:
				style = '-162px -164px';
				break;
			case 9:
				style = '-284px -103px';
				break;
			case 10:
				style = '-343px -164px';
				break;
			case 11:
			case 51:
				style = '-223px -102px';
				break;
			case 12:
				style = '-40px -284px';
				break;
			case 13:
				style = '';//没有这个虚拟化
				break;
			case 14:
			case 22:
				style = '-162px -103px';
				break;
			case 15:
				style = '-40px -225px';
				break;
			case 16:
				style = '-40px -164px';
				break;
			case 17:
				style = '-40px -164px';
				break;
			case 18:
			case 23:
			case 25:
			case 32:
				style = '-284px -286px';
				break;
			case 19:
				style = '-223px -225px';
				break;
			case 20:
				style = '-345px -42px';
				break;
			case 24:
			case 31:
			case 39:
				style = '-101px -164px';
				break;
			case 26:
			case 30:
				style = '-162px -347px';
				break;
			case 27:
				style = '-406px -42px';
				break;
			case 28:
				style = '-162px -42px';
				break;
			case 33:
				style = '-102px -286px';
				break;
			case 35:
				style = '-223px -164px';
				break;
			case 36:
				style = '-40px -103px ';
				break;
			case 37:
				style = '-101px -103px';
				break;
			case 38:
				style = '-284px -42px';
				break;
			case 39:
				style = '-40px -42px';
				break;
			case 41:
				style = '-223px -347px';
				break;
			case 42:
				style = '-162px -225px';
				break;
			case 43:
				style = '-40px -347px';
				break;
			case 44:
				style = '-101px -347px';
				break;
			case 45:
				style = '-345px -103px';
				break;
			case 46:
			case 101:
				style = '-406px -103px';
				break;
			case 47:
				style = '-40px -286px';
				break;
			case 48:
				style = '-223px -42px';
				break;
			case 49:
				style = '-284px -225px';
				break;
			case 50:
				style = '345px 225px';
				break;
			case 100:
				style = '-101px -42px';
				break;
		}
		return style;
	}

	//添加租户的步骤
	var wizardInit = function () {
		if (!jQuery().bootstrapWizard) {
			return;
		}
		let tenantDrawer = $('#tenantDrawer');
		let form = $('#submit_form');
		let error = $('.alert-danger', form);
		let success = $('.alert-success', form);
		let handleTitle = function (tab, navigation, index) {
			let total = navigation.find('li').length; //总共的步骤数
			let current = index + 1; //当前步骤
			jQuery('li', tenantDrawer).removeClass("done");
			let li_list = navigation.find('li');
			for (let i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}
			//如果第一步 上一步按钮隐藏
			if (current === 1) {
				tenantDrawer.find('.button-previous').hide();
			} else {
				tenantDrawer.find('.button-previous').show();
			}
			//如果是最后一步
			if (current >= total) {
				tenantDrawer.find('.button-next').hide();
				if (editFlag) {
					$('#tenantDrawer').find('#edit_submit').show();
					$('#tenantDrawer').find('#add_submit').hide();
				} else {
					$('#tenantDrawer').find('#add_submit').show();
					$('#tenantDrawer').find('#edit_submit').hide();
				}
			} else {
				tenantDrawer.find('.button-next').show();
				tenantDrawer.find('.button-submit').hide();
			}
			Metronic.scrollTo($('.page-title'));
		}

		// default form wizard
		tenantDrawer.bootstrapWizard({
			'nextSelector': '.button-next',
			'previousSelector': '.button-previous',
			onTabClick: function (tab, navigation, index, clickedIndex) {
				return false;
			},

			//下一步
			onNext: function (tab, navigation, index) {
				success.hide();
				error.hide();
				switch (index) {
					case 1:
						if (!step1Valid()) {
							return false;
						}
						break;
					case 2:
						if (step2Valid() == false) {
							return false;
						}
						$('#tenantDrawer #add_submit').removeClass('disabled');
						$('#tenantDrawer #edit_submit').removeClass('disabled');
						break;
					case 3:
						if (step3Valid() == false) {
							return false;
						}
						break;				}
				handleTitle(tab, navigation, index);
			},

			//上一步
			onPrevious: function (tab, navigation, index) {
				success.hide();
				error.hide();
				handleTitle(tab, navigation, index);
			},

			//进度条显示
			onTabShow: function (tab, navigation, index) {
				let total = navigation.find('li').length;
				let current = index + 1;
				let $percent = (current / total) * 100;
				tenantDrawer.find('.progress-bar').css({
					width: $percent + '%'
				});
			},
			//回退到第一步
			onFirst: function (tab, navigation, index) {
				success.hide();
				error.hide();
				handleTitle(tab, navigation, index);
			},
		});

		tenantDrawer.find('.button-previous').hide();

		$('#tenantDrawer #add_submit').click(submit);
		$('#tenantDrawer #edit_submit').click(editSubmit);
	};

	//第一步
	var step1Valid = function () {
		// 表单检测
		if (!tenantFormStep1.validate().form()) {
			return false;
		}
		return true;
	}

	//第二步
	var step2Valid = function () {
		//是否选了备份存储
		if ($('#selectStorage').selectpicker('val').length == 0) {
			UIToastr.showWarning(LANG.UI_TENANT_ADD,LANG.UI_TENANT_SELECT_STORAGE);
			return false;
		}
		return true;
	}

	//第三步
	var step3Valid = function () {
		var result = 1;
		var flag = true;
		//检测授权输入
		result = getAuthInputValid()
		switch (result) {
			case 0:
				UIToastr.showWarning(LANG.UI_TENANT_ADD,LANG.UI_TENANT_AUTH_OVER);
				flag = false;
				break;
			case 1://验证通过
				break;
		}
		return flag;
	}

	//添加租户提交
	var submit = function () {
		if (step3Valid() == false) {
			return false;
		}
		var params = {};
		var storage_list_des = [];
		params.tenant_name = $('#tenant_name').val().trim();
		params.remarks = $('#remarks').val().trim();
		params.user_name = $('#user_name').val().trim();
		params.admin_password = hex_md5($('#admin_password').val().trim());
		params.admin_email = $('#admin_email').val().trim();
		params.storage_list = $('#selectStorage').selectpicker('val');
		var selectNames = $('#selectStorage').find("option:selected");
		for(var i = 0; i < selectNames.length; i++) {
			storage_list_des.push($(selectNames[i]).text());
		}
		params.storage_list_des = storage_list_des;
		params.auth_info = {recover:{}};
		params.auth_info.auth_way = license_type;//授权方式
		switch (license_type) {
			case 1: //普通容量 + 复制按数量
				switch (parseInt($('.normal-capacity-mode').val())) {
					case 0://无限制
						params.auth_info.normal_capacity_mode = 0;
						params.auth_info.normal_quota_size = -1;
						params.auth_info.normal_quota_show_size = 0;
						params.auth_info.normal_quota_show_unit = 'GB';
						break;
					case 1://指定配额
						params.auth_info.normal_capacity_mode = 1;
						params.auth_info.normal_quota_size = calSize($('#normal-quota-input').val(),$('#normal-quota-unit').val());
						params.auth_info.normal_quota_show_size = $('#normal-quota-input').val();
						params.auth_info.normal_quota_show_unit = $('#normal-quota-unit').val();
						let freeSize = $('.normal-free-size').attr("freeSize");//系统剩余容量
						if (parseInt(freeSize) < params.auth_info.normal_capacity_mode) {
							UIToastr.showWarning(LANG.UI_TENANT_EDIT,LANG.UI_TENANT_AUTH_OVER_CAPACITY);
							return false;
						}
						break;
				}
				break;
			case 2: //实时和定时容量分开（不支持实时，暂时只有定时） + 复制按数量
				switch (parseInt($('.time-capacity-mode').val())) {
					case 0://无限制
						params.auth_info.time_capacity_mode = 0;
						params.auth_info.time_quota_size = -1;
						params.auth_info.time_quota_show_size = 0;
						params.auth_info.time_quota_show_unit = 'GB';
						break;
					case 1://指定配额
						params.auth_info.time_capacity_mode = 1;
						params.auth_info.time_quota_size = calSize($('#time-quota-input').val(),$('#time-quota-unit').val());
						params.auth_info.time_quota_show_size = $('#time-quota-input').val();
						params.auth_info.time_quota_show_unit = $('#time-quota-unit').val();
						let freeSize = $('.time-free-size').attr("freeSize");//系统剩余容量
						if (parseInt(freeSize) < params.auth_info.time_quota_size) {
							UIToastr.showWarning(LANG.UI_TENANT_EDIT,LANG.UI_TENANT_AUTH_OVER_CAPACITY);
							return false;
						}
						break;
				}
				break;
			case 3: //全按数量
				// params.auth_info.capacity_mode = 0;
				// params.auth_info.quota_size = -1;
				// params.auth_info.quota_show_size = 0;
				// params.auth_info.quota_show_unit = 'MB';
				params.auth_info.vm_num = $('#vmNum').val();
				// params.auth_info.agent_num = $('#agentNum').val();
				params.auth_info.aws_num = $('#awsNum').val();
				params.auth_info.ops_num = $('#opsNum').val();
				params.auth_info.file_num = $('#fileNum').val();
				params.auth_info.db_num = $('#dbNum').val();
				params.auth_info.os_num = $('#osNum').val();
				params.auth_info.nas_num = $('#nasNum').val();
				params.auth_info.m365_num = $('#m365Num').val();
				params.auth_info.m365_online_num = $('#m365OnlineNum').val();
				params.auth_info.obs_num = $('#obsNum').val();
				params.auth_info.hadoop_num = $('#hadoopNum').val();
				params.auth_info.k8s_num = $('#k8sNum').val();
				break;
			case 4: //文件、数据库、整机按客户端数量，其余模块按数量
				params.auth_info.vm_num = $('#vmNum').val();
				params.auth_info.agent_num = $('#agentNum').val();
				params.auth_info.aws_num = $('#awsNum').val();
				params.auth_info.ops_num = $('#opsNum').val();
				// params.auth_info.file_num = $('#fileNum').val();
				// params.auth_info.db_num = $('#dbNum').val();
				// params.auth_info.os_num = $('#osNum').val();
				params.auth_info.nas_num = $('#nasNum').val();
				params.auth_info.m365_num = $('#m365Num').val();
				params.auth_info.m365_online_num = $('#m365OnlineNum').val();
				params.auth_info.obs_num = $('#obsNum').val();
				params.auth_info.hadoop_num = $('#hadoopNum').val();
				params.auth_info.k8s_num = $('#k8sNum').val();
				break;
			case 5: //文件系列按容量，其余模块按数量
				switch (parseInt($('.file-capacity-mode').val())) {
					case 0://无限制
						params.auth_info.file_capacity_mode = 0;
						params.auth_info.file_quota_size = -1;
						params.auth_info.file_quota_show_size = 0;
						params.auth_info.file_quota_show_unit = 'GB';
						break;
					case 1://指定配额
						params.auth_info.file_capacity_mode = 1;
						params.auth_info.file_quota_size = calSize($('#file-quota-input').val(),$('#file-quota-unit').val());
						params.auth_info.file_quota_show_size = $('#file-quota-input').val();
						params.auth_info.file_quota_show_unit = $('#file-quota-unit').val();
						let freeSize = $('.file-free-size').attr("freeSize");//系统剩余容量
						if (parseInt(freeSize) < params.auth_info.file_quota_size) {
							UIToastr.showWarning(LANG.UI_TENANT_EDIT,LANG.UI_TENANT_AUTH_OVER_CAPACITY);
							return false;
						}
						break;
				}
				params.auth_info.vm_num = $('#vmNum').val();
				// params.auth_info.agent_num = $('#agentNum').val();
				params.auth_info.aws_num = $('#awsNum').val();
				params.auth_info.ops_num = $('#opsNum').val();
				// params.auth_info.file_num = $('#fileNum').val();
				params.auth_info.db_num = $('#dbNum').val();
				params.auth_info.os_num = $('#osNum').val();
				// params.auth_info.nas_num = $('#nasNum').val();
				params.auth_info.m365_num = $('#m365Num').val();
				params.auth_info.m365_online_num = $('#m365OnlineNum').val();
				// params.auth_info.obs_num = $('#obsNum').val();
				// params.auth_info.hadoop_num = $('#hadoopNum').val();
				params.auth_info.k8s_num = $('#k8sNum').val();
				break;
			case 6: //文件系列按容量，数据库、整机按客户端数量，其余模块按数量
				switch (parseInt($('.file-capacity-mode').val())) {
					case 0://无限制
						params.auth_info.file_capacity_mode = 0;
						params.auth_info.file_quota_size = -1;
						params.auth_info.file_quota_show_size = 0;
						params.auth_info.file_quota_show_unit = 'GB';
						break;
					case 1://指定配额
						params.auth_info.file_capacity_mode = 1;
						params.auth_info.file_quota_size = calSize($('#file-quota-input').val(),$('#file-quota-unit').val());
						params.auth_info.file_quota_show_size = $('#file-quota-input').val();
						params.auth_info.file_quota_show_unit = $('#file-quota-unit').val();
						let freeSize = $('.file-free-size').attr("freeSize");//系统剩余容量
						if (parseInt(freeSize) < params.auth_info.file_quota_size) {
							UIToastr.showWarning(LANG.UI_TENANT_EDIT,LANG.UI_TENANT_AUTH_OVER_CAPACITY);
							return false;
						}
						break;
				}
				params.auth_info.vm_num = $('#vmNum').val();
				params.auth_info.agent_num = $('#agentNum').val();
				params.auth_info.aws_num = $('#awsNum').val();
				params.auth_info.ops_num = $('#opsNum').val();
				// params.auth_info.file_num = $('#fileNum').val();
				// params.auth_info.db_num = $('#dbNum').val();
				// params.auth_info.os_num = $('#osNum').val();
				// params.auth_info.nas_num = $('#nasNum').val();
				params.auth_info.m365_num = $('#m365Num').val();
				params.auth_info.m365_online_num = $('#m365OnlineNum').val();
				// params.auth_info.obs_num = $('#obsNum').val();
				// params.auth_info.hadoop_num = $('#hadoopNum').val();
				params.auth_info.k8s_num = $('#k8sNum').val();
				break;
		}
		params.auth_info.machinecopy_num = $('#machinecopyNum').val();
		params.auth_info.filecopy_num = $('#filecopyNum').val();
		params.auth_info.nascopy_num = $('#nascopyNum').val();
		params.auth_info.dbcdpcopy_num = $('#dbcdpcopyNum').val();
		Metronic.blockUI({target:"#tenantDrawer",animate: true});
		pAjaxRequest(params, "/api/v1/tenant", "post", function (result) {
			if (operateResponseList(result,LANG.UI_TENANT_ADD)) {
				$('#tenant_table').bootstrapTable('refresh');
				$('#tenantDrawer').drawer('hide');
			} else {
				$('#tenantDrawer').drawer('show');
			}
			Metronic.unblockUI('#tenantDrawer');
		});
	}

	//修改租户提交
	var editSubmit = function () {
		if (step3Valid() == false) {
			return false;
		}
		var params = {};
		var storage_list_des = [];
		params.tenant_uuid = $('#tenant_uuid').val();
		params.user_uuid = $('#user_uuid').val();
		params.tenant_name = $('#tenant_name').val().trim();
		params.remarks = $('#remarks').val().trim();
		params.user_name = $('#user_name').val().trim();
		params.admin_password = $('#admin_password').val().trim() == '' ? '' : hex_md5($('#admin_password').val().trim());
		params.admin_email = $('#admin_email').val().trim();
		params.storage_list = $('#selectStorage').selectpicker('val');
		var selectNames = $('#selectStorage').find("option:selected");
		for(var i = 0; i < selectNames.length; i++) {
			storage_list_des.push($(selectNames[i]).text());
		}
		params.storage_list_des = storage_list_des;
		params.auth_info = {recover:{}};
		params.auth_info.auth_way = license_type;//授权方式
		switch (license_type) {
			case 1: //普通容量 + 复制按数量
				switch (parseInt($('.normal-capacity-mode').val())) {
					case 0://无限制
						params.auth_info.normal_capacity_mode = 0;
						params.auth_info.normal_quota_size = -1;
						params.auth_info.normal_quota_show_size = 0;
						params.auth_info.normal_quota_show_unit = 'GB';
						break;
					case 1://指定配额
						params.auth_info.normal_capacity_mode = 1;
						params.auth_info.normal_quota_size = calSize($('#normal-quota-input').val(),$('#normal-quota-unit').val());
						params.auth_info.normal_quota_show_size = $('#normal-quota-input').val();
						params.auth_info.normal_quota_show_unit = $('#normal-quota-unit').val();
						let freeSize = $('.normal-free-size').attr("freeSize");//系统剩余容量
						if (parseInt(freeSize) < params.auth_info.normal_capacity_mode) {
							UIToastr.showWarning(LANG.UI_TENANT_EDIT,LANG.UI_TENANT_AUTH_OVER_CAPACITY);
							return false;
						}
						break;
				}
				params.auth_info.machinecopy_num = $('#machinecopyNum').val();
				params.auth_info.filecopy_num = $('#filecopyNum').val();
				params.auth_info.nascopy_num = $('#nascopyNum').val();
				params.auth_info.dbcdpcopy_num = $('#dbcdpcopyNum').val();
				break;
			case 2: //实时和定时容量分开（不支持实时，暂时只有定时） + 复制按数量
				switch (parseInt($('.time-capacity-mode').val())) {
					case 0://无限制
						params.auth_info.time_capacity_mode = 0;
						params.auth_info.time_quota_size = -1;
						params.auth_info.time_quota_show_size = 0;
						params.auth_info.time_quota_show_unit = 'GB';
						break;
					case 1://指定配额
						params.auth_info.time_capacity_mode = 1;
						params.auth_info.time_quota_size = calSize($('#time-quota-input').val(),$('#time-quota-unit').val());
						params.auth_info.time_quota_show_size = $('#time-quota-input').val();
						params.auth_info.time_quota_show_unit = $('#time-quota-unit').val();
						let freeSize = $('.time-free-size').attr("freeSize");//系统剩余容量
						if (parseInt(freeSize) < params.auth_info.time_quota_size) {
							UIToastr.showWarning(LANG.UI_TENANT_EDIT,LANG.UI_TENANT_AUTH_OVER_CAPACITY);
							return false;
						}
						break;
				}
				params.auth_info.machinecopy_num = $('#machinecopyNum').val();
				params.auth_info.filecopy_num = $('#filecopyNum').val();
				params.auth_info.nascopy_num = $('#nascopyNum').val();
				params.auth_info.dbcdpcopy_num = $('#dbcdpcopyNum').val();
				break;
			case 3: //全按数量
				// params.auth_info.capacity_mode = 0;
				// params.auth_info.quota_size = -1;
				// params.auth_info.quota_show_size = 0;
				// params.auth_info.quota_show_unit = 'MB';
				params.auth_info.vm_num = $('#vmNum').val();
				// params.auth_info.agent_num = $('#agentNum').val();
				params.auth_info.aws_num = $('#awsNum').val();
				params.auth_info.ops_num = $('#opsNum').val();
				params.auth_info.file_num = $('#fileNum').val();
				params.auth_info.db_num = $('#dbNum').val();
				params.auth_info.os_num = $('#osNum').val();
				params.auth_info.nas_num = $('#nasNum').val();
				params.auth_info.m365_num = $('#m365Num').val();
				params.auth_info.m365_online_num = $('#m365OnlineNum').val();
				params.auth_info.obs_num = $('#obsNum').val();
				params.auth_info.hadoop_num = $('#hadoopNum').val();
				params.auth_info.k8s_num = $('#k8sNum').val();
				params.auth_info.machinecopy_num = $('#machinecopyNum').val();
				params.auth_info.filecopy_num = $('#filecopyNum').val();
				params.auth_info.nascopy_num = $('#nascopyNum').val();
				params.auth_info.dbcdpcopy_num = $('#dbcdpcopyNum').val();
				break;
			case 4: //文件、数据库、整机按客户端数量，其余模块按数量
				params.auth_info.vm_num = $('#vmNum').val();
				params.auth_info.agent_num = $('#agentNum').val();
				params.auth_info.aws_num = $('#awsNum').val();
				params.auth_info.ops_num = $('#opsNum').val();
				// params.auth_info.file_num = $('#fileNum').val();
				// params.auth_info.db_num = $('#dbNum').val();
				// params.auth_info.os_num = $('#osNum').val();
				params.auth_info.nas_num = $('#nasNum').val();
				params.auth_info.m365_num = $('#m365Num').val();
				params.auth_info.m365_online_num = $('#m365OnlineNum').val();
				params.auth_info.obs_num = $('#obsNum').val();
				params.auth_info.hadoop_num = $('#hadoopNum').val();
				params.auth_info.k8s_num = $('#k8sNum').val();
				params.auth_info.machinecopy_num = $('#machinecopyNum').val();
				params.auth_info.filecopy_num = $('#filecopyNum').val();
				params.auth_info.nascopy_num = $('#nascopyNum').val();
				params.auth_info.dbcdpcopy_num = $('#dbcdpcopyNum').val();
				break;
			case 5: //文件系列按容量，其余模块按数量
				switch (parseInt($('.file-capacity-mode').val())) {
					case 0://无限制
						params.auth_info.file_capacity_mode = 0;
						params.auth_info.file_quota_size = -1;
						params.auth_info.file_quota_show_size = 0;
						params.auth_info.file_quota_show_unit = 'GB';
						break;
					case 1://指定配额
						params.auth_info.file_capacity_mode = 1;
						params.auth_info.file_quota_size = calSize($('#file-quota-input').val(),$('#file-quota-unit').val());
						params.auth_info.file_quota_show_size = $('#file-quota-input').val();
						params.auth_info.file_quota_show_unit = $('#file-quota-unit').val();
						let freeSize = $('.file-free-size').attr("freeSize");//系统剩余容量
						if (parseInt(freeSize) < params.auth_info.file_quota_size) {
							UIToastr.showWarning(LANG.UI_TENANT_EDIT,LANG.UI_TENANT_AUTH_OVER_CAPACITY);
							return false;
						}
						break;
				}
				params.auth_info.vm_num = $('#vmNum').val();
				// params.auth_info.agent_num = $('#agentNum').val();
				params.auth_info.aws_num = $('#awsNum').val();
				params.auth_info.ops_num = $('#opsNum').val();
				// params.auth_info.file_num = $('#fileNum').val();
				params.auth_info.db_num = $('#dbNum').val();
				params.auth_info.os_num = $('#osNum').val();
				// params.auth_info.nas_num = $('#nasNum').val();
				params.auth_info.m365_num = $('#m365Num').val();
				// params.auth_info.obs_num = $('#obsNum').val();
				// params.auth_info.hadoop_num = $('#hadoopNum').val();
				params.auth_info.k8s_num = $('#k8sNum').val();
				params.auth_info.machinecopy_num = $('#machinecopyNum').val();
				params.auth_info.filecopy_num = $('#filecopyNum').val();
				params.auth_info.nascopy_num = $('#nascopyNum').val();
				params.auth_info.dbcdpcopy_num = $('#dbcdpcopyNum').val();
				break;
			case 6: //文件系列按容量，数据库、整机按客户端数量，其余模块按数量
				switch (parseInt($('.file-capacity-mode').val())) {
					case 0://无限制
						params.auth_info.file_capacity_mode = 0;
						params.auth_info.file_quota_size = -1;
						params.auth_info.file_quota_show_size = 0;
						params.auth_info.file_quota_show_unit = 'GB';
						break;
					case 1://指定配额
						params.auth_info.file_capacity_mode = 1;
						params.auth_info.file_quota_size = calSize($('#file-quota-input').val(),$('#file-quota-unit').val());
						params.auth_info.file_quota_show_size = $('#file-quota-input').val();
						params.auth_info.file_quota_show_unit = $('#file-quota-unit').val();
						let freeSize = $('.file-free-size').attr("freeSize");//系统剩余容量
						if (parseInt(freeSize) < params.auth_info.file_quota_size) {
							UIToastr.showWarning(LANG.UI_TENANT_EDIT,LANG.UI_TENANT_AUTH_OVER_CAPACITY);
							return false;
						}
						break;
				}
				params.auth_info.vm_num = $('#vmNum').val();
				params.auth_info.agent_num = $('#agentNum').val();
				params.auth_info.aws_num = $('#awsNum').val();
				params.auth_info.ops_num = $('#opsNum').val();
				// params.auth_info.file_num = $('#fileNum').val();
				// params.auth_info.db_num = $('#dbNum').val();
				// params.auth_info.os_num = $('#osNum').val();
				// params.auth_info.nas_num = $('#nasNum').val();
				params.auth_info.m365_num = $('#m365Num').val();
				// params.auth_info.obs_num = $('#obsNum').val();
				// params.auth_info.hadoop_num = $('#hadoopNum').val();
				params.auth_info.k8s_num = $('#k8sNum').val();
				params.auth_info.machinecopy_num = $('#machinecopyNum').val();
				params.auth_info.filecopy_num = $('#filecopyNum').val();
				params.auth_info.nascopy_num = $('#nascopyNum').val();
				params.auth_info.dbcdpcopy_num = $('#dbcdpcopyNum').val();
				break;
		}
		Metronic.blockUI({target:".tenant-table",animate: true});
		pAjaxRequest(params, "/api/v1/tenant", "put", function (result) {
			Metronic.unblockUI('.tenant-table');
			if (operateResponseList(result,LANG.UI_TENANT_EDIT)) {
				$('#tenant_table').bootstrapTable('refresh');
				$('#tenantDrawer').drawer('hide');
			} else {
				$('#tenantDrawer').drawer('show');
			}
		});
	}
	var initUserTable = function(rowData){
		var options = {
			pagination:true,
			pageList: [5,10,25,50],
			vin_params: function () {
				return {"tenant_uuid":rowData.tenant_uuid};
			},
			vin_url:"/api/v1/tenant/user_list",
			vin_method:"GET",
			columns:[
				{
					field: 'user_name',
					title: LANG.UI_CLOUD_PLATFORM_USERNAME,
				},
				{
					field: 'backup_data',
					title: LANG.UI_HOMEPAGEPRO_BACKUP_DATA,
					sortable: false,
				},
				{
					field: 'lock_flag',
					title: LANG.UI_PUBLIC_STATUS,
					formatter: function (value, row, index, field) {
						if (value == 1) {
							return '<span class="label label-sm label-success status-icon">' + LANG.BILLING_ON_LOCK + '</span>'
						} else {
							return '<span class="label label-sm label-danger status-icon"  style="width:auto; min-width:40px">' + LANG.BILLING_OFF_LOCK + '</span>';
						}
					}
				},
				{
					field: 'create_time',
					title: LANG.UI_JOB_CREATE_OR_MODIFI_TIME,
				},
			],
		}
		sessionStorage.removeItem("table_pageRecord");
		$('#user_table').bootstrapTable('destroy');
		$('#user_table').baseTableConfig().init(options);
	}

	//添加/修改nas设备数据格式校验
	var tenantValid = function() {
		tenantFormStep1.validate({
			errorElement: 'span', //default input error message container
			errorClass: 'help-block help-block-error', // default input error message class
			focusInvalid: false, // do not focus the last invalid input
			ignore: "",  // validate all fields including form hidden input
			rules: {
				tenant_name: {
					required: true,
					tenant_name: true,
					minlength: 4,
					maxlength: 24,
					tenantNameAvailable: true,//检测重复没
				},
				user_name: {
					minlength: 4,
					system_name: true,
					required: true,
				},
				admin_password: {
					system_password: true,
					// minlength: CONF.PASS_LENGTH,
					required_diy: true,
				},
				confirm_password: {
					confirm_password:true,
					required_diy: true,
				},
				email: {
					email: true
				},
			},
			errorPlacement: function (error, element) { // render error placement for each input type
				var icon = $(element).parent('.input-icon').children('i');
				icon.removeClass('fa-check').addClass("fa-warning");
				icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
			},
			highlight: function (element) { // hightlight error inputs
				$(element).closest('.form-group').removeClass("has-success").addClass('has-error'); // set error class to the control group
			},
			success: function (label, element) {
				if (element.id == 'normal-quota-input') {
					return;
				}
				var icon = $(element).parent('.input-icon').children('i');
				$(element).closest('.form-group').removeClass('has-error').addClass('has-success'); // set success class to the control group
				icon.removeClass("fa-warning").addClass("fa-check");
			},
		});
		//租户名支持英文数字和下划线组合
		$.validator.addMethod("tenant_name", function(value, element) {
			return this.optional(element) || /^[a-zA-Z0-9_]+$/.test(value);
		}, LANG.UI_TENANT_INPUT_NAME);
		//密码确认
		$.validator.addMethod("confirm_password", function(value, element) {
			return $('#admin_password').val().trim() == value;
		}, LANG.UI_TENANT_PASSWORD_NOT_MATCH);
		//密码是否与系统密码一致
		switch(CONF.PASS_COMPLEXITY){
			case 1: //弱(包含字母(不区分大小写),数字,特殊字符(不是必须))
				var pattern = "^[A-Za-z0-9!@#$%^&*,.]{" + CONF.PASS_LENGTH + ",}$";
				var tips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK
				break;
			case 2:	//中(必须包含字母(不区分大小写),数字,特称字符)
				var pattern = "^(?=.*[0-9])(?=.*[A-Za-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]{"+ CONF.PASS_LENGTH + ",}$";
				var tips = LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM
				break;
			case 3: //强(必须包含大小写字母,数字,特称字符)
				var pattern = "^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*,\\.])[0-9a-zA-Z!@#$%^&*,\\\\.]{"+ CONF.PASS_LENGTH + ",}$"
				var tips = LANG.UI_USER_PASSWORD_STRENGTH_STRONG
				break;
		}
		$.validator.addMethod("system_password", function(value, element) {
			if (editFlag) {
				return '' == value || new RegExp(pattern).test(value);
			}
			return this.optional(element) || new RegExp(pattern).test(value);
		}, LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + tips);
		$.validator.addMethod("system_name", function(value, element) {
			return this.optional( element ) || /^[a-zA-z][a-zA-Z0-9_@.-\\]{2,64}$/i.test( value );
		}, LANG.UI_USER_NAME_FORMART);
		$.validator.addMethod("required_diy", function(value, element) {
			if (editFlag) {
				return '' == value || '' != value;
			}
			return '' != value;
		}, LANG.UI_PUBLIC_EMPTY_TIPS);
		$.validator.messages.required = LANG.UI_PUBLIC_EMPTY_TIPS;
		$.validator.addMethod("tenantNameAvailable", function(value, element, param) {
				var flag = false;
				pAjaxRequest({'tenant_name': value}, "/api/v1/tenant/check_name", "get", function (result) {
					if(result.success) {
						flag = true;
					}
				},false);
				return flag;
			}, LANG.UI_TENANT_EXIST)
	};

	//初始化备份存储
	var initBackupStorage = function () {
		pAjaxRequest({"tenantFlag": true}, "/api/v1/tenant/storage", "get", function (result) {
			$('#selectStorage').empty();
			if(result.success) {
				for (var i = 0; i < result.data.length; i++) {
					$('#selectStorage').append('<option value="'+ result.data[i].storage_uuid +'">'+ result.data[i].text +'</option>');
				}
				//默认选中第一个
				document.getElementById("selectStorage").options.selectedIndex = 0;
			} else {
				$('.selectpicker').selectpicker({
					noneSelectedText: LANG.UI_TENANT_NO_STORAGE
				});
			}
			$('#selectStorage').selectpicker('refresh');//清空下拉框上一次选中的值
		});
	}
	//单位换算 (参数1：值；参数2：单位)
	var calSize = function(value, unit) {  
		var unitObj = {  
			'B': 1,  
			'KB': 1024,  
			'MB': 1024 * 1024,  
			'GB': 1024 * 1024 * 1024,  
			'TB': 1024 * 1024 * 1024 * 1024,  
			'PB': 1024 * 1024 * 1024 * 1024 * 1024  
		};  
		// 确保单位是大写的，与单位转换对象中的键匹配  
		unit = unit.toUpperCase();  
		// 执行转换  
		var result = value * unitObj[unit];  
		// 使用Intl.NumberFormat来格式化数字  
		var formatter = new Intl.NumberFormat('en-US', {  
			minimumFractionDigits: 0, // 最小小数位数  
			maximumFractionDigits: 0  // 最大小数位数，如果你想显示小数，可以调整这两个值  
		}); 
		// 返回格式化的字符串  
		return formatter.format(result).replace(/,/g, '');  
	}  
	var getAuthInputValid = function () {
		var module = moduleShowList;
		var flag = 1;
		for (var i = 0; i < module.length; i++) {
			if ($('.'+ module[i] +'-free-num').text() == LANG.UI_SETTING_AUTH_UNLIMITED) {
				flag = 1;
			} else if(parseInt($('.'+ module[i] +'-free-num').text()) >= parseInt($('#'+ module[i] +'Num').val())) {
				flag = 1;
			} else {
				$('#'+ module[i] +'Num').closest('.num-div').removeClass('has-success').addClass('has-error');
				flag = 0;
				break;
			}
		}
		return flag;
	}

	var clearInputStyle = function() {
		var module = moduleShowList;
		for (var i = 0; i < module.length; i++) {
			$('#'+ module[i] +'Num').closest('.num-div').removeClass('has-error').addClass('has-success');
		}
	}

	return{
		init: function(){
			wizardInit();
			addListeners();
			initBackupStorage();
			initDataTable();
			initUserPassword();
			initUsername();
			tenantValid();	//添加租户表单数据格式验证
		}
	}; 
}();
jQuery(document).ready(function() {    
	tenantManager.init();
});