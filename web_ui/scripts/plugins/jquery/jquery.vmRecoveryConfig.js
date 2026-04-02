/**
 * 使用时需引入
 * <script type="text/javascript" src="./scripts/platform/component/network_config.js"></script>
 */
(function($){
	const BUS_TYPE_IDE = 1; // 磁盘总线类型 - IDE
	// 恢复方式
	const VM_RECOVERY_WAY_NEW = 1;
	const VM_RECOVERY_WAY_SPECIFIC = 2;
	// 置备模式的描述
	const diskTypeArr = ['', LANG.UI_SETTING_DISK_TYPE_THIN, LANG.UI_SETTING_DISK_TYPE_THICK_LAZY, LANG.UI_SETTING_DISK_TYPE_THICK];

	var showDriver = false; // 是否需要驱动检测
	var isDriverChecked = false; // 是否已完成驱动检测
	var driverCheckResult = true; // 驱动检测结果，默认通过
	var source_list_init = [];
	var specificFlag = false; // 是否指定实例恢复
	var initListenersFlag = false;

	/**
	 * 有代理时间点配置转vmRecoveryConfig需要的points和pointsDetail参数
	 * @param pointData 有代理时间点
	 */
	$.fn.agentDataConvertVmConfig = function (pointData) {
		let pointsDetail = [];
		$.each(pointData, function (i, v) {
			pointsDetail.push({
				hypervisor: 0,
				timepointuuid: '',
				vcenteruuid: '',
				vmuuid: '',
				vmname: 'Agents_' + new Date().getTime(),
				oldname: '',
			})
		});
		return pointsDetail;
	}

	$.fn.vmRecoveryConfig = function(options, pointsDetail = {}){
		var _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;\"',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“。，、？+-]");
		var defaults = {
			//多维数组,每一项表示每一个虚拟机的配置
			'config' : [],
  //		  'hypervisor': 1,
			'control' : '',
			pageAuth: [],
			recoveryWay: VM_RECOVERY_WAY_NEW,
		}
		// 获取授权信息
		pAjaxRequest({}, "/api/v1/system/auth_info", "get", function (result) {
			if (result.success) {
				defaults.pageAuth = result.data.extension.p;
			}
		}, false);
		var thisOption = $.extend(defaults,options);
		var parentID = '';
		var control = '';
		var interfacesbak = LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE;//初始化为原配置
		var crossPlatformFlag; // 跨平台恢复标记
		var cmCrossFlag = false; // 整机跨平台恢复标记，即原虚拟化类型为0
		specificFlag = VM_RECOVERY_WAY_SPECIFIC === thisOption.recoveryWay;
		
		//根据是否是跨平台恢复 显示是原配置还是推荐配置
		//如果非跨平台显示原配置,反之显示推荐配置
		var getDescriptionAboutInterfacesbak = function(hypervisor,old_hypervisor){
			if(hypervisor == old_hypervisor){
				interfacesbak = LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE;//显示原配置
				crossPlatformFlag = false;
			}else{
				interfacesbak = LANG.UI_SETTING_DISK_TYPE_RECOMMEND_SOURCE;//显示推荐配置
				crossPlatformFlag = true;
				if (0 == old_hypervisor) {
					cmCrossFlag = true;
				}
			}
		}
		getDescriptionAboutInterfacesbak(thisOption.hypervisor,thisOption.old_hypervisor);
		
		
		//得到是否展示类,bool转字符串
		var getDisplayStr = function(flag){
			return flag ? '' : 'displaynone';
		}
		
		//得到其他的左边tab是否展示和active
		var getActiveAndDisplayStr = function(flag){
			return flag ? ' active ' : 'displaynone';
		}

		// 是否显示为禁用
		var getDisabledStr = function (flag) {
			return flag ? 'disabled' : '';
		}
  
		var clearVmNameString = function (vmname) {
			if (CONF.VMTYPE_GROUP.XENSERVER.includes(thisOption.hypervisor) || CONF.VMTYPE_GROUP.HUAWEIKVM.includes(thisOption.hypervisor)) {
				//xenserver和华为kvm不转化虚拟机名
				return vmname;
			}
			return clearString(vmname);
		}
		
		//替换特殊字符为下划线
		  var clearString = function (s){ 
			  var rs = ""; 
			  for (var i = 0; i < s.length; i++) { 
				  rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_'); 
			  } 
			  return rs;  
		  }

		//得到上面横向导航
		var getCrosswiseNav = function(config, num){
			var flag = !crossPlatformFlag
				|| config.data_encrypt
				|| (crossPlatformFlag && (CONF.VM_TYPE.VMWARE == config.target_hypervisor_type || CONF.VMTYPE_GROUP.OPENSTACK.includes(config.target_hypervisor_type))); //跨平台到vmware/openstack需显示操作系统
			var div = '<li class="active">' + 
							'<a href="#tab_' + num + 'c1" data-toggle="tab" aria-expanded="true"><i class="viconfont vicon-tongyongpeizhi"></i> ' + LANG.UI_VM_SETTING_V2_GENERAL + ' </a>' +
						'</li>' + 
						'<li class="">' + 
							'<a href="#tab_' + num + 'c2" data-toggle="tab" aria-expanded="false"><i class="viconfont vicon-cipanpeizhi"></i> ' + LANG.UI_VM_SETTING_V2_DISK + ' </a>' +
						'</li>' + 
						'<li class="' + getDisplayStr(!specificFlag) + '">' +
							'<a href="#tab_' + num + 'c3" data-toggle="tab" aria-expanded="false"><i class="viconfont vicon-wangluopeizhi"></i> ' + LANG.UI_VM_SETTING_V2_NETWORK + ' </a>' +
						'</li>' + 
						'<li class="' + getDisplayStr(flag || crossPlatformFlag || thisOption.instantFlag) + '">' +
							'<a href="#tab_' + num + 'c4" data-toggle="tab" aria-expanded="false"><i class="viconfont vicon-gaojipeizhi"></i> ' + LANG.UI_VM_SETTING_V2_HIGH_SETTING + ' </a>' +
						'</li>';
			return div;
		}
		
		//获取socket和Cores
		var getSocketAndCoresOption = function(oldNum){
			var div = '';
			oldNum = parseInt(oldNum);
			for(var i=1; i<=40; i++){
				if(i == oldNum){
					//默认选中并标注原配置
					div += '<option value="' + i + '" selected="selected">' + i + ' (' + interfacesbak + ')</option>';
				}else{
					div += '<option value="' + i + '">' + i  + '</option>';
				}
			}
			return div;
		}

		// 获取CPU类型选项
		const getCpuTypeOption = (cpu_arch, cpu_info_list) => {
			let option = '';
			if (crossPlatformFlag) {
				option += `<option value="0"> ${LANG.UI_JOB_SELECT} </option>`
			}
			$.each(cpu_info_list, function (i, v) {
				if (v.arch_type == cpu_arch) {
					//默认选中
					option += '<option value="' + v.arch_type + '" selected="selected">' + v.arch_name + '(' + interfacesbak + ')' + '</option>';
				} else {
					option += '<option value="' + v.arch_type + '">' + v.arch_name + '</option>';
				}
			});
			return option;
		}

		// 获取CPU工作模式选项
		const getCpuModeOption = (cpu_arch, cpu_mode, cpu_info_list) => {
			if (specificFlag || !cpu_info_list.length) {
				return `<option value="1">${LANG.UI_PUBLIC_DEFAULT}</option>`;
			}
			if (parseInt(cpu_arch) === 0) {
				// 时间点没存cpu架构则取SupportInfo返回的第一个
				cpu_arch = cpu_info_list[0].arch_type;
			}
			let option = ``;
			let emdFlag = 108 == thisOption.hypervisor;
			let emd_cpu_mode_list = [];
			if (emdFlag) {
				emd_cpu_mode_list = [{value: 1, name: LANG.UI_PUBLIC_DEFAULT}];
				for (let key in CONF.CPU_MODE_EMD) {
					emd_cpu_mode_list.push({value: parseInt(key), name: CONF.CPU_MODE_EMD[key]});
				}
			}
			$.each(cpu_info_list, function (i, v) {
				if (v.arch_type == cpu_arch) {
					if (emdFlag) {
						v.cpu_mode_list = $.extend(v.cpu_mode_list, emd_cpu_mode_list);
					}
					// 跨平台恢复到pve匹配不到时默认值为kvm64（87）
					let pveDefaultFlag = false;
					let cpuModeValues = v.cpu_mode_list.map(item => item.value);
					if (crossPlatformFlag && CONF.VM_TYPE.PROXMOX == thisOption.hypervisor && !cpuModeValues.includes(cpu_mode)) {
						pveDefaultFlag = true;
					}

					$.each(v.cpu_mode_list, function (idx, item) {
						if (item.value == cpu_mode || pveDefaultFlag && item.value == 87) {
							//默认选中
							option += `<option value="${item.value}" selected="selected">${item.name}</option>`;
						} else {
							option += `<option value="${item.value}">${item.name}</option>`;
						}
					});
				}
			});
			return option;
		}

		var getMemoryInputAttrs = function (memoryUnit, targetHypervisor) {
			let min = '', max = '', step = '', maxlength = 4;
			if ([CONF.VM_TYPE.H3C, CONF.VM_TYPE.H3CCASCVD].includes(targetHypervisor)) {
				// H3C 限制单位为GB时不可输入小数,限制单位为MB时数字须为4的整倍数且不小于512
				if ('GB' == memoryUnit) {
					step = 1;
				} else {
					step = 4;
					min = 512;
				}
			}
			if (CONF.VM_TYPE.XHERE == targetHypervisor) {
				// Xhere 单位为GB时，不可输入小数且数值限定1-1024之间;单位为MB时，不可输入小数且限定数值在64-1048576之间
				if ('GB' == memoryUnit) {
					min = 1;
					max = 1024;
					maxlength = 4;
				} else {
					min = 64;
					max = 1048576;
					maxlength = 7
				}
				step = 1;
			}
			return {min: min, max: max, step: step, maxlength: maxlength};
		}

		var getMemoryValue = function (config) {
			if ([CONF.VM_TYPE.H3C, CONF.VM_TYPE.H3CCASCVD, CONF.VM_TYPE.XHERE].includes(config.target_hypervisor_type)) {
				let conf = getMemoryInputAttrs(config.memory_array.unit, config.target_hypervisor_type);
				let maxlengthStr = conf.maxlength ? `maxlength="${conf.maxlength}"` : '';
				let minStr = conf.min ? `min="${conf.min}"` : '';
				let maxStr = conf.max ? `max="${conf.max}"` : '';
				let stepStr = conf.step ? `step="${conf.step}"` : '';
				return `<input type="number" name="vm_memory" value="${config.memory_array.num}" style="text-align: center;" 
					class="spinner-input form-control input-sm" ${maxlengthStr} ${minStr} ${maxStr} ${stepStr}>`;
			} else {
				return `<input type="number" name="vm_memory" value="${config.memory_array.num}" style="text-align: center;"
					class="spinner-input form-control input-sm">`;
			}
		}

		//获取内存的单位
		var getMomoryUnit = function(unit, targetHypervisor){
			var div = '';
			var units = ["MB", "GB", "TB"];
			// h3c cvd/h3c uis/xhere 无TB选项
			if ([CONF.VM_TYPE.H3C, CONF.VM_TYPE.H3CCASCVD, CONF.VM_TYPE.XHERE].includes(targetHypervisor)) {
				units = ["MB", "GB"];
			}
			for(var i=0; i<units.length; i++){
				if(units[i] == unit){
					//默认选中原来的单位
					div += '<option value="' + units[i] + '" selected="selected">' + units[i] + '</option>';
				}else{
					div += '<option value="' + units[i] + '">' + units[i] + '</option>';
				}
			}

			return div;
		}
		
		//获取启动方式
		var getStartupMode = function(oldMode){
			var div = '';
			var list = ["",LANG.UI_VM_SETTING_START_DATA,LANG.UI_VM_SETTING_START_IMAGE];
			var oldDes = "(" + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE + ")";
			for(var i=1;i<list.length; i++){
				if(i == oldMode){
					//默认选中原来的单位
					div += '<option value="' + i + '" selected="selected">' + list[i] + oldDes + '</option>';
				}else{
					div += '<option value="' + i + '">' + list[i] + '</option>';
				}
			}
			return div;
		}
		
		//得到通用配置内容
		var getGeneralContent = function(config, num){
			//-----------------//
  			let display_new = '';
  			let display_specific = 'display:none;';
			var display_cpu = '';
			var display_ram = '';
			var display_openstack = 'display:none;';
			var display_openstack_start_mode = 'display:none;';
  
			//openstack显示实例类型和启动方式
			if(CONF.VMTYPE_GROUP.OPENSTACK.includes(thisOption.hypervisor)){
				// display_cpu = crossPlatformFlag ? '' : 'display:none;';
				display_ram = 'display:none;';
				display_openstack = 'display:block;';
				display_openstack_start_mode = 'display:block;';
				if (CONF.VM_TYPE.HUAWEICLOUDSTACK == thisOption.hypervisor) {
					display_openstack_start_mode = 'display:none;';
				}
			}
			if (specificFlag) {
				display_new = 'display:none;';
				display_specific = 'display:block;';
				display_ram = 'display:none;';
				display_openstack = 'display:none;';
				display_openstack_start_mode = 'display:none;';
			}

			//判断是否显示删除卷开关
			var showFlag = config.openstack_start_mode == 1? "": "display-none";
			// cpu模式是否显示
			let showCpuModeFlag = (CONF.VMTYPE_GROUP.OPENSTACK.includes(thisOption.hypervisor)
				|| CONF.VM_TYPE.XHERE == thisOption.hypervisor || CONF.VM_TYPE.ZSTACK == thisOption.hypervisor || CONF.VM_TYPE.NEXAVMNCSSV == thisOption.hypervisor
				|| CONF.VM_TYPE.H3C == thisOption.hypervisor || CONF.VM_TYPE.H3CCASCVD == thisOption.hypervisor
				|| CONF.VM_TYPE.INCLOUDKVM == thisOption.hypervisor || CONF.VM_TYPE.INSPURVVDK == thisOption.hypervisor || CONF.VM_TYPE.KSPHERE == thisOption.hypervisor
				|| CONF.VM_TYPE.LENOVOAIO == thisOption.hypervisor
				|| CONF.VM_TYPE.WINHONGKVM == thisOption.hypervisor
				|| CONF.VMTYPE_GROUP.REDHAT.includes(thisOption.hypervisor)
				|| CONF.VM_TYPE.PROXMOX == thisOption.hypervisor
				// || CONF.VM_TYPE.SANGFOR == thisOption.hypervisor
				|| CONF.VM_TYPE.SMARTX == thisOption.hypervisor
				|| 108 == thisOption.hypervisor) && !specificFlag;
			// 原平台恢复flag
			let oriPlatformFlag = !crossPlatformFlag && !thisOption.instantFlag && !thisOption.vmotionFlag;
			//-----------------//
			var div =
				'<div class="tab-pane active" id="tab_' + num + 'c1">' +
					'<div class="row" style="margin: 0 20px;">' +
						'<div class="col-md-3 col-sm-3 col-xs-3 nav-tab-radios">' +
							'<ul class="nav nav-tabs tabs-left">' +
								'<li class="active" style="' + display_specific + '"><a href="#tab_' + num + 'c1_0" data-toggle="tab" aria-expanded="true">' + LANG.UI_RECOVERY_GOAL + ' </a></li>' +
								'<li class="active" style="' + display_new + '"><a href="#tab_' + num + 'c1_1" data-toggle="tab" aria-expanded="true">' + LANG.UI_VM_MACHINE_NAME + ' </a></li>' +
								'<li class="" style="'+display_cpu+'"><a href="#tab_' + num + 'c1_2" data-toggle="tab" aria-expanded="false">' + LANG.UI_PUBLIC_CPU + ' </a></li>' +
								'<li class="" style="'+display_ram+'"><a href="#tab_' + num + 'c1_3" data-toggle="tab" aria-expanded="false">' + LANG.UI_PUBLIC_MEMORY + ' </a></li>' +
								'<li class="" style="'+display_openstack+'"><a href="#tab_' + num + 'c1_4" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_EXAMPLE_TYPE + ' </a></li>' +
								'<li class="" style="'+display_openstack_start_mode+'"><a href="#tab_' + num + 'c1_5" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_START_METHOD + ' </a></li>' +
								'<li class=""><a href="#tab_' + num + 'c1_6" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_OS + ' </a></li>' +
							'</ul>' +
						'</div>' +
						'<div class="col-md-9 col-sm-9 col-xs-9">' +
							'<div class="tab-content">' +
								'<div class="tab-pane ' + (specificFlag ? 'active' : 'display-none') + ' in" id="tab_' + num + 'c1_0">' +
									'<div class="form-group">' +
										'<label class="control-label mt2 migrated-vm-name col-md-3">'+ '<span class="required">* </span>' + LANG.UI_RECOVERY_GOAL +'</label>' +
										'<div class="col-md-6">' +
											'<input type="hidden" name="target_platform_uuid" value="">' +
											'<input type="hidden" name="target_host_uuid" value="">' +
											'<input type="hidden" name="target_host_name" value="">' +
											'<input type="hidden" name="target_region" value="">' +
											'<input type="hidden" name="target_vm_uuid" value="">' +
											'<input type="hidden" name="target_vm_name" value="">' +
											'<input type="hidden" name="target_username" value="">' +
											'<input type="hidden" name="target_password" value="">' +
											'<input type="text" class="form-control input-md target-path" placeholder="' + LANG.UI_RECOVERY_PLEASE_SELECT_GOAL + '" readonly>' +
											'</div>' +
										'<div class="col-md-3"><button class="btn btn-md btn-light-primary selectTargetBtn" type="button" data-toggle="drawer" data-target="#drawer-1">' + LANG.UI_RECOVERY_SELECT_GOAL + '</button></div>' +
									'</div>' +
								'</div>' +
								'<div class="tab-pane ' + (specificFlag ? 'display-none' : 'active') + ' in" id="tab_' + num + 'c1_1">' +
									'<div class="form-group  col-md-12">' +
										'<label class="control-label  mb5 migrated-vm-name">'+ '<span class="required">* </span>' + (CONF.VMTYPE_GROUP.OPENSTACK.includes(thisOption.hypervisor) ? LANG.UI_VM_SETTING_RECOVER_INSTANCE_NAME : LANG.UI_VM_SETTING_RECOVER_NAME) +'</label>' +
										'<div class="input-icon right"><i class="fa"></i><input type="text" class="form-control input-sm" value="' + clearVmNameString(config.new_vm_name) + '" name="vmname">' +
										'</div>' +
									'</div>' +
								'</div>' +
								'<div class="tab-pane fade" id="tab_' + num + 'c1_2">' +
									'<div class="form-group col-md-4 col-md-6_en ' + getDisplayStr(control.cpu_general) + '">' +
										'<label class="control-label mb5">' + '<span class="required">* </span>' + LANG.UI_VM_SETTING_V2_SOCKET + '</label>' +
										'<select class="form-control select2me input-sm" name="cpu_socket">' + getSocketAndCoresOption(config.sockets) + '</select>' +
									'</div>' +
									'<div class="col-md-12"></div>' +
									'<div class="form-group col-md-4 col-md-6_en ' + getDisplayStr(control.cpu_general) + '" style=" margin-left:0;">' +
										'<label class="control-label mb5">' + '<span class="required">* </span>' + LANG.UI_VM_SETTING_V2_SCCKET_CORE + '</label>' +
										'<select class="form-control select2me input-sm" name="cpu_core">' + getSocketAndCoresOption(config.cores) + '</select>' +
									'</div>' +
									'<div class="col-md-12"></div>' +
									'<div class="form-group col-md-4 col-md-6_en ' + getDisplayStr(false) + '">' +
										'<label class="control-label mb5">' + '<span class="required">* </span>' + LANG.UI_VM_SETTING_KERNEL + '</label>' +
										'<select class="form-control select2me input-sm" data-openstack="' + control.cpu_openstack + '" name="vcpu">' + getSocketAndCoresOption(parseInt(config.sockets) * parseInt(config.cores)) + '</select>' +
									'</div>' +
									'<div class="col-md-12"></div>' +
									'<div class="form-group col-md-4 col-md-6_en ' + getDisplayStr(control.cpu_xsky_zstack) + '">' +
										'<label class="control-label mb5">' + '<span class="required">* </span>' + LANG.UI_VM_SETTING_CPUNUM + '</label>' +
										'<select class="form-control select2me input-sm" data-xskyzstack="' + control.cpu_xsky_zstack + '" name="xzcpu">' + getSocketAndCoresOption(parseInt(config.sockets) * parseInt(config.cores)) + '</select>' +
									'</div>' +
									'<div class="col-md-12"></div>' +
									'<div class="form-group col-md-4 col-md-6_en ' + getDisplayStr(control.cpu_hyperv) + '" style=" margin-left:0;">' +
										'<label class="control-label mb5">' + '<span class="required">* </span>' + LANG.UI_VM_SETTING_CPU_COUNT + '</label>' +
										'<select class="form-control select2me input-sm" data-hyperv="' + control.cpu_hyperv + '" name="hypervcpu">' + getSocketAndCoresOption(config.sockets) + '</select>' +
									'</div>' +
									'<div class="col-md-12"></div>' +
									'<div class="form-group col-md-4 col-md-6_en ' + getDisplayStr(true) + '">' +
										'<label class="control-label mb5">' + '<span class="required">* </span>' + LANG.UI_VM_SETTING_CPU_ARCH + '</label>' +
										'<select class="form-control select2me input-sm" name="cpu_type">' + getCpuTypeOption(config.cpu_arch, thisOption.cpu_info_list) + '</select>' +
									'</div>' +
									'<div class="col-md-12"></div>' +
									'<div class="form-group col-md-4 col-md-6_en ' + getDisplayStr(showCpuModeFlag) + '">' +
										'<label class="control-label mb5">' + '<span class="required">* </span>' + LANG.UI_VM_SETTING_CPU_MODE + '</label>' +
										'<select class="form-control select2me input-sm" name="cpu_mode" data-cpu_mode="' + config.cpu_mode + '">' + getCpuModeOption(config.cpu_arch, config.cpu_mode, thisOption.cpu_info_list) + '</select>' +
									'</div>' +
								'</div>' +

								'<div class="tab-pane fade" id="tab_' + num + 'c1_3">' +
									'<div class="form-group col-md-6 col-md-11_en">' +
										'<label class="control-label mb5">' + '<span class="required">* </span>' + LANG.UI_PUBLIC_MEMORY_SIZE + '(' + interfacesbak + ' ' + config.memory_array.num + ' ' + config.memory_array.unit + ')</label>' +
										'<div class="right">' +
											'<div class="spinnerNum">' +
												'<div class="input-group" style="display: flex">' +
													'<div class="spinner-group" >' +
														getMemoryValue(config) +
														'<div class="spinner-buttons input-group-btn spinner-group-btn">' +
															'<button type="button" class="btn spinner-up default input-sm">' +
																'<i class="fa fa-angle-up"></i>' +
															'</button>' +
															'<button type="button" class="btn spinner-down default input-sm">' +
																'<i class="fa fa-angle-down"></i>' +
															'</button>' +
														'</div>' +
													'</div>' +
													'<select class="form-control input-sm select2me" name="vm_memory_unit" style="height: 34px;width: 100px;margin-left: 5px;">' +
														getMomoryUnit(config.memory_array.unit, config.target_hypervisor_type) +
													'</select>' +
												'</div>' +
											'</div>' +
										'</div>' +
									'</div>' +
								'</div>' +

								'<div class="tab-pane fade" id="tab_' + num + 'c1_4">' +
									'<div class="form-group col-md-4 col-md-11_en ' + display_openstack + '">' +
										'<label class="control-label mb5">' + LANG.UI_VM_EXAMPLE_TYPE + '：'+LANG.UI_VM_VCPU_MEMORY_DISK+'</label>' +
										'<select class="form-control select2me input-sm selectpicker show-tick ignore" name="instance_socket" data-live-search="true"></select>' +
										'<a style="position: absolute;right: -5px;top: 35px;" class="popovers" data-container="body" data-trigger="hover" data-placement="right" data-content="'+LANG.UI_VM_EXAMPLE_TYPE_TIPS+'"'+'><i class="viconfont vicon-tishi"></i></a>' +
									'</div>' +
								'</div>' +

								'<div class="tab-pane fade" id="tab_' + num + 'c1_5">' +
									'<div class="form-group col-md-4 ' + display_openstack_start_mode + '">' +
										'<label class="control-label mb5">' + LANG.UI_VM_START_METHOD + '</label>' +
										'<select class="form-control select2me input-sm" name="start_mode">' + getStartupMode(config.openstack_start_mode) + '</select>' +
									'</div>' +
									'<div class="form-group col-md-12 delDiskDiv ' + showFlag + '">' +
									  '<label class="control-label mb5 power-after-migrated">' + LANG.UI_VM_DELETE_EXAMPLE_DATA +
									  '</label>' +
									  '<div>' +
										  '<input type="checkbox" name="openstack_del_vm_del_disk_mode" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" ' +
										  'data-on-text="' + LANG.UI_PUBLIC_ON_ONE + '" ' +
										  'data-off-text="' + LANG.UI_PUBLIC_OFF_ONE + '">' +
									  '</div>' +
								  '</div>' +
								'</div>' +
								// 操作系统
								'<div class="tab-pane fade" id="tab_' + num + 'c1_6">' +
									'<div class="form-group ' + (oriPlatformFlag ? 'col-md-4' : 'col-md-6') + ' ">' +
										'<label class="control-label mb5">' + LANG.UI_VM_OS_TYPE + '</label>' +
										'<select class="form-control select2me col-md-4 os-type" name="os_type" data-os="' + config.os_type + '" data-os_version="' + config.os_version + '" data-match_os_version="' + config.match_os_version + '" data-num="' + num + '">' + (specificFlag ? `` : getOSTypeSelect(thisOption.os_info_list, config.cpu_arch, config.os_type)) + '</select>' +
									'</div>' +
									'<div class="col-md-12"></div>' +
									'<div class="form-group ' + (oriPlatformFlag ? 'col-md-8' : 'col-md-12') + ' ">' +
										'<label class="control-label mb5">' + LANG.UI_VM_OS_VERSION + '</label>' +
										'<select class="form-control select2me input-sm col-md-4 os-version selectpicker show-tick ignore" name="os_version" data-live-search="true">' + (specificFlag ? `` : getOSVersionSelect(thisOption.os_info_list, config.cpu_arch, config.os_type, config.os_version, config.match_os_version)) + '</select>' +
									'</div>' +
								'</div>' +
							'</div>' +
						'</div>' +
					'</div>' +
				'</div>';
			return div;
		}
		
		//得到磁盘名称内容,可以修改用input,无法修改用span
		var getDiskNameContent = function(diskinfo, source_hypervisor_type, target_hypervisor_type){
			//如果恢复目标虚拟化类型和原虚拟化一样,使用原来的磁盘名称
			var diskName = diskinfo['disk_name'];
			if(source_hypervisor_type == target_hypervisor_type){
				diskName = diskinfo['source_disk_name'];
			}
			var div = '';
			if(control.disk_name_modify_flag){
				//可以修改
				div = '<div class="form-group" style="margin: 0!important;">' +
						'<div class="input-icon right">' +
							'<i class="fa"></i>' +
							`<input type="text" class="form-control input-sm" name="diskname" maxlength="256" value="${diskName}" class="" data-src_disk_name="${diskinfo['source_disk_name']}">` +
						'</div>' +
					'</div>';
			}else{
				//无法修改
				div = `<span class="fs12" name="diskname" data-src_disk_name="${diskinfo['source_disk_name']}">${diskName}</span>`;
			}
			return div;
		}
		
		//得到置备模式
		var getSettingMode = function(disk){
			var option = "";
  		    var optionArrDefault = ['', LANG.UI_SETTING_DISK_TYPE_THIN, LANG.UI_SETTING_DISK_TYPE_THICK_LAZY, LANG.UI_SETTING_DISK_TYPE_THICK];
			var optionArr = thisOption.maintain_model ? thisOption.maintain_model : optionArrDefault;

			for(var i=1; i<optionArr.length; i++){
				if ('' === optionArr[i]) {
					continue;
				}
				if(i == parseInt(disk.disk_type)){
					//默认选中并标注原配置
					option += '<option value="' + i + '" selected="selected">' + optionArr[i] +  '(' + interfacesbak + ')' + '</option>';
				}else{
					option += '<option value="' + i + '">' + optionArr[i] + '</option>';
				}
				
			}
			return option;
		}
  
		//得到块存储策略
		var getBlockPolicy = function (disk) {
			  var option = "";
			  var optionArr = thisOption.volume_policy;
			$.each(optionArr, function (i, v) {
				if (disk.policy_id == v.uuid) {
					  //选中
					option += getBlockPolicyItem(v, true);
				} else {
					option += getBlockPolicyItem(v, false);
				}
			});
			return option;
		}
  
		//单个块存储策略
		var getBlockPolicyItem = function (policy, checked) {
			  var divCss = checked ? 'background: #E7F7F3;border: 1px solid #1BA39C' : 'background: #FFFFFF;border: 1px solid rgb(227, 230, 243)';
			  var crcCheck = policy.crc_check ? LANG.UI_VM_SETTING_V2_OPENED : LANG.UI_VM_SETTING_V2_UNOPENED; //是否开启数据校验
			var limitFlag = 0 != policy.max_total_bps || 0 != policy.burst_total_bps || 0 != policy.max_total_iops || 0 != policy.burst_total_iops;
			var limitDes = limitFlag ? LANG.UI_VM_SETTING_V2_LIMITED : LANG.UI_VM_SETTING_V2_UNLIMITED; //是否限制QoS
			let maxTotalBps = policy.max_total_bps > 0 ? (byteToMiB(policy.max_total_bps) + "MiB/s") : LANG.UI_SETTING_AUTH_UNLIMITED;
			let burstTotalBps = policy.burst_total_bps > 0 ? (byteToMiB(policy.burst_total_bps) + "MiB/s") : LANG.UI_SETTING_AUTH_UNLIMITED;
			var limitDetails = LANG.UI_VM_SETTING_V2_MAX_IOPS + ': ' + policy.max_total_iops + "\n" +
				LANG.UI_VM_SETTING_V2_BURST_IOPS + ': ' + policy.burst_total_iops + "\n" +
				LANG.UI_VM_SETTING_V2_MAX_BPS + ': ' + maxTotalBps + "\n" +
				LANG.UI_VM_SETTING_V2_BURST_BPS + ': ' + burstTotalBps;
			var limitDetailsShow = limitFlag ? '' : 'display: none';
			  var info = 
				'<div class="blockPolicy col-md-2" style="min-width: 180px;margin-right: 8px;padding: 8px;cursor: pointer;' + divCss + '" data-policyid="' + policy.uuid + '">' +
					'<div style="display: flex;justify-content: space-between;margin-bottom: 16px;">' +
						'<span class="blockPolicyName" style="font-weight: bolder;">' + policy.name + '</span>' +
					'</div>' +
					'<div style="display: flex;justify-content: space-between;margin-bottom: 8px;color: rgb(95, 100, 140)">' +
						'<span>' + LANG.UI_VM_SETTING_V2_CRC_CHECK + ': </span>' +
						'<span>' + crcCheck + '</span>' +
					'</div>' +
					'<div style="display: flex;justify-content: space-between;color: rgb(95, 100, 140)">' +
						'<span>' + LANG.UI_VM_SETTING_V2_BIZ_QOS + ': </span>' +
					  '<span>' +
							limitDes +
							'<a class="popovers ml5" data-container="body" data-trigger="hover" data-placement="right" ' +
								'data-content="' + limitDetails + '" style="' + limitDetailsShow + '">' +
								'<i class="fa fa-info-circle fa-lg"></i>' +
							'</a>' +
						'</span>' +
					'</div>' +
				'</div>';
			  return info;
		}

		// smartx磁盘存储策略
		const getSmartxDiskPolicy = function (src_policy_id) {
			let option = ``;
			let optionArr = thisOption.disk_storage_policy;
			$.each(optionArr, function (i, v) {
				if (v.storage_policy_id === src_policy_id) {
					option += `<option value="${v.storage_policy_id}" selected>${v.storage_policy_name}(${LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE})</option>`;
				} else {
					option += `<option value="${v.storage_policy_id}">${v.storage_policy_name}</option>`;
				}
			});

			return `<select class="form-control input-sm margin10 select2me" name="storage_policy_id">${option}</select>`;
		}
  
		//字节数转MB
		var byteToMiB = function (num) {
			  num = num / 1024 / 1024;
			  return parseInt(num);
		}
		
		//设置磁盘控制器类型
		var setDiskBusType = function(controller_type){
			var disk_bus = thisOption.disk_bus;
			if (CONF.VM_TYPE.HYPERV == thisOption.hypervisor || CONF.VM_TYPE.CITRIX == thisOption.hypervisor || CONF.VM_TYPE.XCPNG == thisOption.hypervisor) {
				// hyperv固定为hyperv scsi
				// xenserver/xcp-ng固定为xen
				return '<option value="' + disk_bus[0].key + '" selected="selected">' + disk_bus[0].value + '</option>';
			}
			var option = ``;
			var oldType = parseInt(controller_type);
			var oldDes = '(' + interfacesbak + ')';
			if(CONF.VMTYPE_GROUP.OPENSTACK.includes(thisOption.hypervisor)){
				//没有原来总线类型默认
  //			  if(oldType == 0){
					oldType = 4;	//没有原来的显示virtio
					oldDes = "";
  //			  }
			}
			if ((CONF.VMTYPE_GROUP.VMWARE.includes(thisOption.old_hypervisor) || CONF.VM_TYPE.HYPERV == thisOption.old_hypervisor) && 108 == thisOption.hypervisor) {
				// vmware/hyperv和kvm磁盘总线不对等，恢复到内嵌时设为0使其不再匹配
				oldType = 0;
			}
			if (0 == thisOption.old_hypervisor && 108 == thisOption.hypervisor) {
				// 整机恢复到内嵌时磁盘总线默认virtio
				oldType = 4;
			}
			if (!disk_bus) {
				return option;
			}
			for(var i=0; i<disk_bus.length; i++){
				if(disk_bus[i].key == oldType){
					option += '<option value="' + disk_bus[i].key + '" selected="selected">' + disk_bus[i].value + oldDes + '</option>';
				}else{
					option += '<option value="' + disk_bus[i].key + '">' + disk_bus[i].value + '</option>';
				}
			}
			return option;
		}
		
	  //设置磁盘类型，只适用于hyper-v,所有情况进来都选择第一种原配置 不关联数据
		var setDiskBusTypeHyperv = function(flag){
			if(!flag){
				return '';
			}
			var disk_bus = thisOption.disk_bus_hyperv;
			var option = '';
				option += '<option value="' + disk_bus[0].key + '" selected="selected">' + disk_bus[0].value + '(' + interfacesbak + ')' + '</option>';
			for(var i=1; i<disk_bus.length; i++){
				option += '<option value="' + disk_bus[i].key + '">' + disk_bus[i].value + '</option>';
			}
			return option;
		}
		
		
		//设置网卡控制器类型
		var setNetworkBusType = function(network_type, preinstall_driver_success = '0'){
			var network_bus = thisOption.network_bus;
			if (CONF.VM_TYPE.HYPERV == thisOption.hypervisor || CONF.VM_TYPE.CITRIX == thisOption.hypervisor || CONF.VM_TYPE.XCPNG == thisOption.hypervisor) {
				// hyperv固定为hyper-v network adapter
				// xenserver/xcp-ng固定为xen
				return '<option value="' + network_bus[0].key + '" selected="selected">' + network_bus[0].value + '</option>';
			}
			// 跨平台去掉【请选择】，若匹配不到原配置则按优先级默认选中第一个
			var option = crossPlatformFlag ? `` : `<option>${LANG.UI_JOB_SELECT}</option>`;
			var oldType = parseInt(network_type);
			var oldDes = '(' + interfacesbak + ')';
			if(CONF.VMTYPE_GROUP.OPENSTACK.includes(thisOption.hypervisor)){
				//没有原来总线类型默认
  //			  if(oldType == 0){
					oldType = 4;	//没有原来的显示virtio
					oldDes = "";
  //			  }
			}
			if (0 == thisOption.old_hypervisor && 108 == thisOption.hypervisor) {
				// 整机恢复到内嵌时网卡总线，preinstall_driver_success=1时为RTL8139(7)，0时为virtio(4)
				oldType = !!parseInt(preinstall_driver_success) ? 7 : 4;
			}
			if (!network_bus) {
				return option;
			}
			for(var i=0; i<network_bus.length; i++){
				if(network_bus[i].key == oldType){
					option += '<option value="' + network_bus[i].key + '" selected="selected">' + network_bus[i].value + oldDes + '</option>';
				}else{
					option += '<option value="' + network_bus[i].key + '">' + network_bus[i].value + '</option>';
				}
			}
			return option;
		}

		const getNetworkMacType = function (hypervisor) {
			if (CONF.VM_TYPE.XHERE == hypervisor || CONF.VM_TYPE.ZSTACK == hypervisor || CONF.VM_TYPE.NEXAVMNCSSV == hypervisor) {
				return `
					<select class="form-control input-sm margin10 mac_type" style="min-width: 138.33px;" name="mac_type" disabled>
						<option value="0">${LANG.UI_VM_SETTING_V2_AUTO_PRODUCT}</option>
					</select>
				`;
			}
			return '<select class="form-control input-sm margin10 mac_type" style="min-width: 138.33px;" name="mac_type">' +
				'<option value="0">' + LANG.UI_VM_SETTING_V2_AUTO_PRODUCT + '</option>' +
				'<option value="2">' + LANG.UI_VM_SETTING_DIY_MAC + '</option>' +
				'<option value="1">' + LANG.UI_VM_SETTING_SAVE_MAC + '</option>' +
				'</select>';
		}
		
		//得到每个磁盘内容
		var getEachDiskContent = function(config, sourceConfig){
			var div = '';
			for(var i=0; i<config.disk_list.length; i++){
				//如果是排除磁盘,不显示
				if(!config.disk_list[i].is_backup_or_recovery){
					continue;
				}
				//添加本行正表格
				div += '<tr class="disktr" id="' + config.disk_list[i].virtual_size + '" data-type="' + config.disk_list[i].is_bootable + '">' +
						  '<td class="hidden-xs" style="text-align: center!important;"><input type="checkbox" class="icheck" checked="checked" value="'+ config.disk_list[i].disk_name + '"></td>' +
						  '<td class="highlight" style="text-align: left!important;">' + getDiskNameContent(config.disk_list[i], config.source_hypervisor_type, config.target_hypervisor_type) + '</td>' +
						  '<td class="hidden-xs" style="text-align: left!important;"><input type="hidden" class="disk_size" value="' + config.disk_list[i].virtual_size + '">' + config.disk_list[i].virtual_size_unit + '</td>' +
						  '<td class="vmstorage storage-active ' + getDisplayStr(!specificFlag && (!thisOption.instantFlag || CONF.VM_TYPE.SANGFORVVDK == config.target_hypervisor_type)) + '" id="' + config.disk_list[i].disk_key_base64 + '" style="text-align: left!important;"><select class="form-control select2me input-sm selectpicker show-tick ignore" name="hoststorage" data-live-search="true"></select></td>' +
						  '<td class="' + getDisplayStr(specificFlag) + '" style="text-align: left!important;"><select class="form-control input-sm" name="target_vm_disk"><option value="">' + LANG.UI_JOB_SELECT + '</option></select></td>' +
						  '<td class="storage-high-config ' + getDisplayStr(!specificFlag) + '" style="text-align: left!important;"><a href="javascript:;" class="rowhighconfig">' + LANG.UI_VM_SETTING_V2_HIGH_SETTING + ' <span class="glyphicon glyphicon-plus"></span></a></td>' +
						  '<td class="target-disk-bus ' + getDisplayStr(specificFlag) + '" style="text-align: left!important;"> -- </td>' +
						  '<td class="target-disk-type ' + getDisplayStr(specificFlag) + '" style="text-align: left!important;"> -- </td>' +
						 '</tr>';
				//添加本行高级配置
				div += '<tr class="details displaynone">' + 
							'<td class="details" colspan="' + ((!thisOption.instantFlag || CONF.VM_TYPE.SANGFORVVDK == config.target_hypervisor_type) ? '10' : '4') + '">' +
								'<table>' + 
									'<tbody>' + 
										'<tr>' + 
											'<td>' + LANG.UI_VM_SETTING_V2_DISK_OLD_NAME + ': </td>' + 
											'<td style="padding: 18px 10px" colspan="10"><span class="fs12 ml10">' + config.disk_list[i].source_disk_name + '</span></td>' +
										'</tr>' + 
										'<tr class="' + getDisplayStr(!thisOption.instantFlag && control.disk_setting_mode) + '">' +
											'<td>' + LANG.UI_VM_SETTING_V2_DISK_TYPE + ': </td>' + 
											'<td>' + 
												'<select class="form-control input-sm margin10 select2me " name="setting_mode">' + 
													getSettingMode(config.disk_list[i]) + 
												'</select>' + 
											'</td>' +
										  '<td>' +
											  '<a class="popovers" style="margin-left: 20px" data-container="body" data-trigger="hover" ' +
											  'data-placement="right" data-content="' + LANG.UI_RECOVERY_DISK_SETTING_MODE_TIPS + '">' +
												  '<i class="viconfont vicon-tishi"></i>' +
											  '</a>' +
										  '</td>' +
										'</tr>' + 
										'<tr class="' + getDisplayStr(control.disk_bus_type) + '">' + 
											'<td>' + LANG.UI_VM_SETTING_V2_BUS_TYPE + ': </td>' + 
											'<td><select class="form-control input-sm margin10 select2me " name="disk_bus_type" data-timepointuuid="' + config.timepointuuid + '" data-old_type="' + ('undefined' === typeof sourceConfig.disk_list ? 0 : sourceConfig.disk_list[i].controller_type) + '">' +
												setDiskBusType(config.disk_list[i].controller_type) + 
											+ '</select></td>' + 
										'</tr>' + 
										'<tr class="' + getDisplayStr(control.disk_type_hyperv) + '">' + 
											'<td>' + LANG.UI_VM_SETTING_V2_MAGNETIC_DISK_TYPE + ': </td>' + 
											'<td><select class="form-control input-sm margin10 select2me "  data-disktypehyperv="' + control.disk_type_hyperv + '" name="disktypehyperv">' + 
												setDiskBusTypeHyperv(control.disk_type_hyperv) + 
											+ '</select></td>' +
										  '<td>' +
											  '<a class="popovers" style="margin-left: 20px" data-container="body" data-trigger="hover" ' +
											  'data-placement="right" data-content="' + LANG.UI_RECOVERY_DISK_TYPE_HYPERV_TIPS + '">' +
												  '<i class="viconfont vicon-tishi"></i>' +
											  '</a>' +
										  '</td>' +
										'</tr>' + 
										'<tr class="' + getDisplayStr(control.disk_cluster_size) + '">' + 
											'<td>' + LANG.UI_VM_SETTING_V2_BLOCK_SIZE + ': </td>' + 
											'<td>' + 
												'<select class="form-control input-sm margin10 select2me " name="cluster_type">' + 
													'<option value="64">64K</option>' + 
													'<option value="128">128K</option>' + 
													'<option value="256">256K</option>' + 
													'<option value="512">512K</option>' + 
													'<option value="1024">1M</option>' + 
												'</select>' + 
											'</td>' + 
										'</tr>' +
									  	'<tr class="' + getDisplayStr(control.disk_type_xhere) + '">' +
										  '<td>' + LANG.UI_VM_SETTING_V2_BLOCK_POLICY + ': </td>' +
											'<td style="padding: 18px;min-width: 600px!important;" colspan="2">' +
											  getBlockPolicy(config.disk_list[i]) +
												'<input type="hidden" name="volume_policy_id" value="' + config.disk_list[i].policy_id + '">' +
										  '</td>' +
									  	'</tr>' +
										'<tr class="' + getDisplayStr(control.disk_policy_smartx) + '">' +
											'<td>' + LANG.UI_GLOBAL_STRATEGY_STORE + ': </td>' +
											'<td>' +
												getSmartxDiskPolicy(config.disk_list[i].policy_id) +
											'</td>' +
										'</tr>' +
										`<tr style="visibility: collapse"><td></td><td></td><td></td></tr>` +
								  '</tbody>' +
								'</table>' + 
							'</td>' + 
						 '</tr>';
			}
			return div;
		}
		
		//得到磁盘配置内容
		var getDiskContent = function(config, num, sourceConfig){
			var div = '';
			// if (!config.source_hypervisor_type) {
			// 	// 源是有代理，嵌套磁盘插件
			// 	div =
			// 		'<div class="tab-pane" id="tab_' + num + 'c2">' +
			// 			'<div class="portlet-body">' +
			// 				'<div class="table-scrollable proxy-disk" id="proxy-disk_' + num + '">' +
			// 				'</div>'+
			// 			'</div>' +
			// 		'</div>';
			// } else {
				// 源是虚拟化
				div =
					'<div class="tab-pane" id="tab_' + num + 'c2">' +
						  '<div class="portlet-body" style="margin: 4px 20px;">' +
							  '<div class="table-scrollable" style="overflow: visible">' +
								  '<table id="vmstorage' + config.vm_uuid + '" class="table table-striped table-bordered table-advance table-hover table-tac getHoststorage" style="table-layout: fixed">' +
									  '<thead>' +
										  '<tr>' +
											  '<th width="10%">' + LANG.UI_VM_SETTING_DISK_CHECK + '</th>' +
											  '<th width="35%">' + (specificFlag ? LANG.UI_RECOVERY_ORIGINAL_DISK : LANG.UI_VM_SETTING_V2_RECOVERY_DISK_NAME) + '</th>' +
											  '<th class="hidden-xs" width="10%">' + LANG.UI_PUBLIC_TOTAL_SIZE + '</th>' +
											  '<th width="30%" class="' + getDisplayStr(!specificFlag && (!thisOption.instantFlag || CONF.VM_TYPE.SANGFORVVDK == config.target_hypervisor_type)) + '">' + LANG.UI_VM_SETTING_DIS_STORAGE + '</th>' +
											  '<th width="25%" class="' + getDisplayStr(specificFlag) + '">' + LANG.UI_RECOVERY_TARGET_DISK + '</th>' +
											  '<th width="15%" class="' + getDisplayStr(!specificFlag) + '">' + LANG.UI_VM_SETTING_V2_HIGH_SETTING + '</th>' +
											  '<th width="10%" class="' + getDisplayStr(specificFlag) + '">' + LANG.UI_VM_SETTING_V2_BUS_TYPE + '</th>' +
											  '<th width="10%" class="' + getDisplayStr(specificFlag) + '">' + LANG.UI_VM_SETTING_V2_DISK_TYPE + '</th>' +
										  '</tr>' +
									  '</thead>' +
									  '<tbody>' +
										  getEachDiskContent(config, sourceConfig) +
									  '</tbdoy>' +
								  '</table>' +
							  '</div>'+
						  '</div>' +
					'</div>';
			// }
			return div;
		}
		
		//得到每个网卡内容
		var getEachNetworkContent = function(config, num, sourceConfig){
			var div = '';
			for(var i=0; i<config.net_list.length; i++){
				//添加本行正表格
				div += '<tr class="vmnetwork networktr">' + 
						  '<td class="hidden-xs" style="text-align: center!important;">' + '<input class="src_network_uuid display-none" value="'+ config.net_list[i].network_uuid + '"><input type="checkbox" name="networkcheck" class="icheck" checked="checked" id="' + config.net_list[i].mac_address +'" value="'+ config.net_list[i].network_name + '"></td>' +
						  '<td class="highlight nwname" style="text-align: left!important;">' + config.net_list[i].network_name + '</td>' +
						  '<td style="' + ((thisOption.instantFlag || crossPlatformFlag) ? 'max-width: 168px' : '') + '"><select class="form-control select2me input-sm selectpicker show-tick ignore" name="hostnetwork" data-live-search="true" style="text-align: left!important;"></select></td>' +
						  '<td style="text-align: left!important;"><a href="javascript:;" class="rowhighconfig" style="text-align: left!important;">' + LANG.UI_VM_SETTING_V2_HIGH_SETTING + ' <span class="glyphicon glyphicon-plus"></span></a></td>' +
						 '</tr>';
				
				//添加本行高级配置
				div +=
					'<tr class="details displaynone">' +
						'<td class="details" colspan="10">' +
							'<table style="width: -webkit-fill-available">' +
								'<tbody>' +
									'<tr class="' + getDisplayStr(control.network_bus_type) + '">' +
										'<td>' + LANG.UI_SEARCH_TYPE + ': </td>' +
										'<td><select class="form-control input-sm margin10 "  name="network_bus_type" data-timepointuuid="' + config.timepointuuid + '" data-old_type="' + ('undefined' === typeof sourceConfig.net_list ? 0 : sourceConfig.net_list[i].controller_type) + '">' +
											setNetworkBusType(config.net_list[i].controller_type, config.preinstall_driver_success) + '</select></td>' +
									'</tr>' +
									'<tr class="">' +
										'<td>' + LANG.UI_VM_SETTING_MAC + ': </td>' +
										'<td >' + getNetworkMacType(config.target_hypervisor_type) + '</td>' +
										'<td >' +
											'<lable></lable>' +
											'<input type="text" class="form-control input-sm margin10 " data-old="' + config.net_list[i].mac_address + '" name="nwmac" value="' + config.net_list[i].mac_address + '" style="display:none;height: 28px">' +
										'</td>' +
										'<td>' +
											'<a class="popovers newmac_tips ' + showHypervMacTips(thisOption.hypervisor, thisOption.vcenter_detail) + '" style="margin-left: 20px" data-container="body" data-trigger="hover" ' +
											'data-placement="right" data-content="' + LANG.UI_RECOVERY_HYPERV_MAC_TIPS + '">' +
												'<i class="fa fa-info-circle fa-lg"></i>' +
											'</a>' +
										'</td>' +
									'</tr>' +
									// '<tr class="' + getDisplayStr(control.network_ip_setting) + '">' +
									// 	'<td>' + LANG.UI_VM_SETTING_V2_IPADDR + ': </td>' +
									// 	'<td><input type="text" placeholder="' + LANG.UI_VM_SETTING_V2_IPADDR_TIPS + '" name="ipaddr"  class="form-control input-sm margin10" value="" class="" style="height: 28px"></td>' +
									// '</tr>' +
									getIPConfigContent(num, i) +
								'</tbody>' +
							'</table>' +
						'</td>' +
					'</tr>';
			}
			return div;
		}

		const getIPConfigContent = (num, i) => {
			let str = '';
			if (thisOption.vmotionFlag) {
				// 迁移不支持修改网络配置
				return str;
			}
			// if (control.network_ip_setting) {
				str +=
					// 网络配置开关
					'<tr>' +
						'<td>' + LANG.UI_VM_SETTING_V2_IS_MODIFY_NETWORK + ': </td>' +
						'<td>' +
							'<div class="margin10">' +
								'<input type="checkbox" name="modify_network" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" ' +
								'data-on-text="' + LANG.UI_PUBLIC_ON_ONE + '" ' +
								'data-off-text="' + LANG.UI_PUBLIC_OFF_ONE + '">' +
							'</div>' +
						'</td>' +
					'</tr>' +
					// 网络配置插件
					'<tr class="display-none">' +
						'<td></td>' +
						'<td colspan="10" class="ipconfig" id="ipconfig_' + num + '_' + i + '">' +
						'</td>' +
					'</tr>';
			// }
			return str;
		}
  
		var showHypervMacTips = function (hypervisor, vcenter_detail) {
			var str = 'display-none';
			if (CONF.VM_TYPE.HYPERV == hypervisor && 1 == vcenter_detail.type) {
				//hyperv恢复到scvmm显示提示
				str = '';
			}
			return str;
		}
		
		//得到网络配置内容
		var getNetworkContent = function(config, num, sourceConfig){
			var div = '<div class="tab-pane" id="tab_' + num + 'c3">' + 
							'<div class="portlet-body" style="margin: 4px 20px;">' +
							  '<div class="table-scrollable" style="overflow: visible">' +
								  '<table class="table table-striped table-bordered table-advance table-hover table-tac getHostnetwork">' +
									  '<thead>' +
										  '<tr>' +
											  '<th width="7%">' + LANG.UI_VM_SETTING_V2_SELECT_NETWORK + '</th>' +
											  '<th>' + LANG.UI_VM_SETTING_CARD + '</th>' +
											  '<th width="40%">' + LANG.UI_VM_SETTING_DIS_NETWORK + '</th>' +
											  '<th width="13%" style="min-width: 100px">' + LANG.UI_VM_SETTING_V2_HIGH_SETTING + '</th>' +
										  '</tr>' +
									  '</thead>' +
									  '<tbody>' +
										  getEachNetworkContent(config, num, sourceConfig) +
									  '</tbdoy>' +
								  '</table>' +
							  '</div>' +
						  '</div>' +
						'</div>';
			return div;
		}
		
		//得到引导模式
		var getBootTypeSelect = function(config){
			let boot_mode = config.firmware_type ? config.firmware_type : config.boot_mode;
			var option = "";
			var optionArr = ['', "BIOS", "UEFI"];
			for(var i=1; i<optionArr.length; i++){
				if(i == parseInt(boot_mode)){
					//默认选中并标注原配置
					option += '<option value="' + i + '" selected="selected">' + optionArr[i] +  '(' + interfacesbak + ')' + '</option>';
				}else{
					option += '<option value="' + i + '">' + optionArr[i] + '</option>';
				}
			}
			return option;
		}
  
		//虚拟机版本是否禁用
		var getVmVersionDisabled = function (vm_version_enable) {
			  return vm_version_enable ? '' : 'disabled';
		}
		
		//得到虚拟机版本
		var getVmVersionSelect = function(vm_version, vm_new_version){
			var option = "";
			var optionArr = ['', "V1", "V2"];
			for(var i=1; i<optionArr.length; i++){
				if(optionArr[i] == vm_new_version){
					if (vm_version == vm_new_version) {
						//默认选中并标注原配置
						option += '<option value="' + optionArr[i] + '" selected="selected">' + optionArr[i] +  '(' + interfacesbak + ')' + '</option>';
					} else {
						option += '<option value="' + optionArr[i] + '" selected="selected">' + optionArr[i] + '</option>';
					}
				}else{
					option += '<option value="' + optionArr[i] + '">' + optionArr[i] + '</option>';
				}
			}
			return option;
		}
		
		//操作系统类型
		var getOSTypeSelect = function(os_info_list, cpu_arch, os_type){
			if (![1, 2, 3, 4].includes(cpu_arch) || typeof cpu_arch === 'undefined') {
				// 未知时先赋值为第一个
				cpu_arch = thisOption.cpu_info_list[0].arch_type;
			}
			let os_type_list = [];
			for (let i in os_info_list) {
				if (cpu_arch == os_info_list[i].arch_type || 0 == os_info_list[i].arch_type) {
					os_type_list = os_info_list[i].os_type_list;
				}
			}
			let option = `<option value="0"> ${LANG.UI_JOB_SELECT} </option>`;
			$.each(os_type_list, function (i, v) {
				if (0 == v.os_value) {
					return;
				}
				if (v.os_value == os_type) {
					//默认选中
					option += `<option value="${v.os_name}" data-os_value="${v.os_value}" selected="selected">${v.os_name}(${interfacesbak})</option>`;
				} else {
					option += `<option value="${v.os_name}" data-os_value="${v.os_value}">${v.os_name}</option>`;
				}
			});
			return option;
		}

		// 获取操作系统版本选项
		const getOSVersionSelect = (os_info_list, cpu_arch, osType, osVersion, matchOsVersion) => {
			if (![1, 2, 3, 4].includes(cpu_arch) || typeof cpu_arch === 'undefined') {
				// 未知时先赋值为第一个
				cpu_arch = thisOption.cpu_info_list[0].arch_type;
			}
			let os_version_list = [];
			for (let i in os_info_list) {
				if (cpu_arch == os_info_list[i].arch_type  || 0 == os_info_list[i].arch_type) {
					os_version_list = os_info_list[i].os_list;
				}
			}
			let option = `<option value="0"> ${LANG.UI_JOB_SELECT} </option>`;
			$.each(os_version_list, function (i, v) {
				if (0 == v.os_version) {
					return;
				}
				if (v.os_type == osType) {
					let osDescription = v.os_description ?? '';
					// 名称优先使用后台返回的os_description
					let osVersionName = '' !== osDescription ? osDescription : v.os_version_name;
					if (v.os_version == osVersion) {
						//默认选中
						option += `<option value="${v.os_version}" selected="selected" data-os_des="${osDescription}">${osVersionName}(${interfacesbak})</option>`;
					} else if (v.os_version == matchOsVersion) {
						// 列表中没有原配置则匹配推荐配置
						option += `<option value="${v.os_version}" selected="selected" data-os_des="${osDescription}">${osVersionName}(${LANG.UI_VM_RESTORE_OS_VERSION_RECOMMEND_TIPS})</option>`;
					} else {
						option += `<option value="${v.os_version}" data-os_des="${osDescription}">${osVersionName}</option>`;
					}
				}
			});
			return option;
		}

		// 重新加载操作系统版本
		const reloadOSVersionSelect = (os_info_list, _el, osVersion = 0, matchOsVersion = 0) => {
			let osType = $(_el).find('option:selected').data('os_value');
			let cpuArch = $(_el).closest(`.configsdiv`).find(`select[name=cpu_type]`).val();
			let option = getOSVersionSelect(os_info_list, cpuArch, osType, osVersion, matchOsVersion);
			$(_el).closest('.tab-pane').find(`select[name=os_version]`).empty().append(option).selectpicker('refresh');
		}
		
		//得到其他配置内容
		var getOtherContent = function(config, num){
			var flag = control.data_encrypt || config.data_encrypt;
			// //恢复操作系统类型同平台不支持
			// var osFlag = control.os_type;
			// if(!thisOption.vmotionFlag){
			// 	// 跨平台恢复到vmware/openstack时显示操作系统
			// 	osFlag = control.os_type && (config.target_hypervisor_type != config.source_hypervisor_type)
			// 		&& (CONF.VM_TYPE.VMWARE == config.target_hypervisor_type || CONF.VMTYPE_GROUP.OPENSTACK.includes(config.target_hypervisor_type));
			// }
			// if (thisOption.vmotionFlag && config.target_hypervisor_type == config.source_hypervisor_type) {
			// 	osFlag = false;
			// }
			// if(CONF.VM_TYPE.HUAWEICBR == thisOption.hypervisor){
			// 	osFlag =  true;
			// }
			var originalFlag = false;
			let icsTypes = [CONF.VM_TYPE.INCLOUDKVM, CONF.VM_TYPE.INSPURVVDK, CONF.VM_TYPE.KSPHERE];
			if (icsTypes.includes(config.source_hypervisor_type) && icsTypes.includes(config.target_hypervisor_type)) {
				// ics内相互恢复才显示
				originalFlag = true;
			}
			var div = '<div class="tab-pane vm-other-pane" id="tab_' + num + 'c4">' + 
				'<div class="row" style="margin: 0 20px;">' +
					'<div class="col-md-3 col-sm-3 col-xs-3 nav-tab-radios">' +
						'<ul class="nav nav-tabs tabs-left">' +
							'<li class="' + getActiveAndDisplayStr(control.boot_type) + '"><a href="#tab_' + num + 'c4_1" data-toggle="tab" aria-expanded="true">' + LANG.UI_VM_SETTING_V2_BOOT_TYPE + ' </a></li>' +
							'<li class="' + getActiveAndDisplayStr(control.available_domain) + '"><a href="#tab_' + num + 'c4_2" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_SETTING_V2_ZONE + ' </a></li>' +
							'<li class="' + getActiveAndDisplayStr(control.root_pass) + '"><a href="#tab_' + num + 'c4_3" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_SETTING_V2_ROOT_PASS + ' </a></li>' +
							'<li class="' + getActiveAndDisplayStr(control.des_dir) + '"><a href="#tab_' + num + 'c4_4" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_SETTING_V2_DES_DIR + ' </a></li>' +
							'<li class="' + getActiveAndDisplayStr(control.mirror_image) + '"><a href="#tab_' + num + 'c4_5" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_SETTING_V2_IMAGE + ' </a></li>' +
							'<li class="' + getActiveAndDisplayStr(control.HA) + '"><a href="#tab_' + num + 'c4_6" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_SETTING_V2_HA + ' </a></li>' +
							'<li class="' + getActiveAndDisplayStr(control.virtual_type) + '"><a href="#tab_' + num + 'c4_7" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_SETTING_V2_HYPER_TYPE + ' </a></li>' +
							'<li class="' + getActiveAndDisplayStr(control.vm_version) + '"><a href="#tab_' + num + 'c4_8" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_VERSION + ' </a></li>' +
							'<li class="' + getActiveAndDisplayStr(false) + '"><a href="#tab_' + num + 'c4_9" data-toggle="tab" aria-expanded="false">' + LANG.UI_BACKUP_DATA_ENCRYPT + ' </a></li>' +
							// '<li class="' + getActiveAndDisplayStr(osFlag) + '"><a href="#tab_' + num + 'c4_10" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_OS_TYPE + ' </a></li>' +
						    '<li class="' + getActiveAndDisplayStr(originalFlag) + '"><a href="#tab_' + num + 'c4_11" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_ORIGINAL_RECOVERY + ' </a></li>' +
						    '<li class="' + getActiveAndDisplayStr(control.image_metadata) + '"><a href="#tab_' + num + 'c4_12" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_IMAGE_METADATA + ' </a></li>' +
							'<li class="' + getActiveAndDisplayStr(false) + '"><a href="#tab_' + num + 'c4_13" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_BEFORE_RECOVERY_SCRIPT + ' </a></li>' +
							'<li class="' + getActiveAndDisplayStr((thisOption.pageAuth.includes('scripts_manager') || !thisOption.pageAuth.length) && !thisOption.vmotionFlag && (crossPlatformFlag || thisOption.instantFlag)) + '"><a href="#tab_' + num + 'c4_14" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_AFTER_RECOVERY_SCRIPT + ' </a></li>' +
							'<li class="' + getActiveAndDisplayStr(true) + '"><a href="#tab_' + num + 'c4_15" data-toggle="tab" aria-expanded="false">' + LANG.UI_VM_SYSTEM_CONFIG + ' </a></li>' +
						'</ul>' +
					'</div>' +
					'<div class="col-md-9 col-sm-9 col-xs-9">' +
						'<div class="tab-content">' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(control.boot_type) + '" id="tab_' + num + 'c4_1">' +
								// '<div class="form-group col-md-6">' +
								// 	'<label class="control-label mb5">' + LANG.UI_VM_SETTING_V2_BOOT_TYPE + '：</label>' +
								// 	'<select class="form-control input-sm" name="boot_type_select">' +
								// 		getBootTypeSelect(config.boot_mode) +
								// 	'</select>' +
								// '</div>' +
							'</div>' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(control.available_domain) + '" id="tab_' + num + 'c4_2">' +
								'<div class="form-group col-md-7 col-md-11_en">' +
				'<label class="control-label mb5">' + LANG.UI_INSTANCE_AVAILABLE_DOMAIN + '：</label>' +
									'<select class="form-control input-sm" name="available_domain_select"></select>' +
								'</div>' +
								'<div class="form-group col-md-7 col-md-11_en">' +
									'<label class="control-label mb5">' + LANG.UI_VM_AVAILABLE_DISK + '：</label>' +
									'<select class="form-control input-sm" name="disk_available_domain_select"></select>' +
									'<span class="class="help-block "">' + LANG.UI_VM_SELECT_AVAILABLE_DOMAIN_TIPS +'</span>' +
								'</div>' +
							'</div>' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(control.root_pass) + '" id="tab_' + num + 'c4_3">' +
								'<div class="form-group col-md-6">' +
									'<label class="control-label mb5">' + LANG.UI_VM_SETTING_V2_ROOT_PASS + '：</label>' +
									'<div class="input-group">' +
										'<input type="password" name="root_pass_input" class="form-control input-sm" placeholder="'+LANG.UI_VM_ROOT_PASSWORD+'" oninput="value=value.replace(/[\\u4E00-\\u9FA5]|[\\uFE30-\\uFFA0]|\\s+/g,\'\')">' +
										'<span class="input-group-btn">' +
											'<button class="btn btn-sm default" name="passcontrol" type="button" style="height:28px"><i class="fa fa-eye"></i></button>' +
										'</span>' +
									'</div>' +
								'</div>' +
							'</div>' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(control.des_dir) + '" id="tab_' + num + 'c4_4">' +
								'<div class="form-group col-md-6">' +
									'<label class="control-label mb5">' + LANG.UI_VM_SETTING_V2_DES_DIR + '：</label>' +
									'<select class="form-control input-sm" name="des_dir_select"></select>' +
								'</div>' +
							'</div>' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(control.mirror_image) + '" id="tab_' + num + 'c4_5">' +
								'<div class="form-group col-md-6">' +
									'<label class="control-label mb5">' + LANG.UI_VM_SETTING_V2_IMAGE + '：</label>' +
									'<select class="form-control input-sm" name="mirror_image_select"></select>' +
								'</div>' +
							'</div>' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(control.HA) + '" id="tab_' + num + 'c4_6">' +
								'<div class="form-group col-md-6">' +
									'<label class="control-label mb5">' + LANG.UI_VM_SETTING_V2_HA + '：</label>' +
									'<div>' +
										'<input type="checkbox" name="ha" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" ' +
									  'data-on-text="' + LANG.UI_PUBLIC_ON_ONE + '" ' +
									  'data-off-text="' + LANG.UI_PUBLIC_OFF_ONE + '">' +
									'</div>' +
								'</div>' +
							'</div>' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(control.virtual_type) + '" id="tab_' + num + 'c4_7">' +
								'<div class="form-group col-md-6">' +
									'<label class="control-label mb5">' + LANG.UI_VM_SETTING_V2_HYPER_TYPE + '：</label>' +
									'<select class="form-control input-sm" name="virtual_type_select">' +
										'<option value="HVM">HVM</option>' +
										'<option value="PV">PV</option>' +
									'</select>' +
								'</div>' +
							'</div>' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(control.vm_version) + '" id="tab_' + num + 'c4_8">' +
								'<div class="form-group col-md-6">' +
									'<label class="control-label mb5">' + LANG.UI_VM_VERSION + '：</label>' +
									'<select class="form-control input-sm" name="vm_version_select" ' + getVmVersionDisabled(config.vm_version_enable) + '>' +
										getVmVersionSelect(config.vm_version, config.vm_new_version) +
									'</select>' +
								'</div>' +
							'</div>' +

							'<div class="tab-pane ' + getActiveAndDisplayStr(flag) + '" id="tab_' + num + 'c4_9">' +
								'<div class="form-group col-md-6">' +
									'<label class="control-label mb5">' + LANG.UI_VM_ENCRYPT_PASSWORD + ': </label>' +
									'<div class="input-group">' +
										'<input type="password" name="encrypt_pass" class="form-control input-sm" maxlength="64" placeholder="" oninput="value=value.replace(/[\\u4E00-\\u9FA5]|[\\uFE30-\\uFFA0]|\\s+/g,\'\')">' +
										'<input type="text" name="encrypt_pass_flag" value="'+getActiveAndDisplayStr(flag)+'" class="form-control input-sm displaynone" >' +
										'<input type="text" name="timepointuuid" value="'+config.timepointuuid+'" class="form-control input-sm displaynone" >' +
									'</div>' +
								'</div>' +
							'</div>' +
							// '<div class="tab-pane ' + getActiveAndDisplayStr(osFlag) + '" id="tab_' + num + 'c4_10">' +
							// 	'<div class="form-group col-md-6">' +
							// 		'<label class="control-label mb5">' + LANG.UI_VM_SELECT_OS_TYPE + '：</label>' +
							// 		'<select class="form-control input-sm" name="os_type_select">'+
							// 		getOSTypeSelect(config.os_type) + '</select>' +
							// 	'</div>' +
							// '</div>' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(originalFlag) + '" id="tab_' + num + 'c4_11">' +
								'<div class="form-group col-md-6">' +
									'<label class="control-label mb5">' + LANG.UI_VM_ORIGINAL_RECOVERY + '：</label>' +
									'<div>' +
										'<input type="checkbox" name="original_recovery_input" class="make-switch original_recovery" data-size="small" data-on-color="primary" data-off-color="info" ' +
										'data-on-text="' + LANG.UI_PUBLIC_ON_ONE + '" ' +
										'data-off-text="' + LANG.UI_PUBLIC_OFF_ONE + '">' +
									'</div>' +
								'</div>' +
							'</div>' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(control.image_metadata) + '" id="tab_' + num + 'c4_12">' +
								'<div class="form-group col-md-12" style="height: 208px;overflow-y: auto">' +
									'<div class="col-md-12">' +
										'<label class="control-label mb5">' + LANG.UI_VM_IMAGE_METADATA_TIPS + '：</label>' +
									'</div>' +
									'<div class="col-md-6">' +
										getImageMetadataDiv(config.image_metadata) +
									'</div>' +
								'</div>' +
							'</div>' +
							// 嵌套执行脚本的组件，跨平台使用
							'<div class="tab-pane ' + getActiveAndDisplayStr(false) + ' scriptBeforeDiv scriptBeforeDiv_' + num + '" id="tab_' + num + 'c4_13" data-num="' + num + '">' +
							'</div>' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(!thisOption.vmotionFlag && (crossPlatformFlag || thisOption.instantFlag)) + ' scriptDiv scriptDiv_' + num + '" id="tab_' + num + 'c4_14" data-num="' + num + '">' +
							'</div>' +
							'<div class="tab-pane ' + getActiveAndDisplayStr(true) + '" id="tab_' + num + 'c4_15">' +
								// 引导模式
								'<div class="form-group col-md-4 ' + getDisplayStr(cmCrossFlag || !crossPlatformFlag && control.boot_mode) + '">' +
									'<label class="control-label mb5">' + '<span class="required">* </span>' + LANG.UI_VM_SETTING_V2_BOOT_TYPE + '：</label>' +
									'<select class="form-control input-sm" name="boot_type_select" data-hypervisor="' + config.target_hypervisor_type + '"' + getDisabledStr(cmCrossFlag) + '>' +
									getBootTypeSelect(config) + '</select>' +
								'</div>' +
								// 重置BIOS uuid，迁移不支持；整机恢复到vmware时忽略boot_mode需要显示
								'<div class="form-group col-md-4 reset_biosuuid_div ' +
									getDisplayStr(!thisOption.vmotionFlag && CONF.VM_TYPE.VMWARE === parseInt(config.target_hypervisor_type) && (1 === parseInt(config.boot_mode) || cmCrossFlag)) + '">' +
									'<label class="control-label mb10">' + '<span class="required">* </span>' + LANG.UI_VM_RESET_BIOS_UUID + '：</label>' +
									'<div>' +
									'<input type="checkbox" name="reset_biosuuid_input" class="make-switch form-control" data-size="small" data-on-color="primary" data-off-color="info" ' +
										'data-on-text="' + LANG.UI_PUBLIC_ON_ONE + '" ' +
										'data-off-text="' + LANG.UI_PUBLIC_OFF_ONE + '">' +
									'</div>' +
								'</div>' +

								// 恢复完成后启动
								'<div class="form-group col-md-12 ">' +
									'<label class="control-label mb5 power-after-migrated">' + LANG.UI_VM_SETTING_REC_POWER + '：</label>' +
									'<div>' +
										'<input type="checkbox" name="vmpower" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" ' +
										'data-on-text="' + LANG.UI_PUBLIC_ON_ONE + '" ' +
										'data-off-text="' + LANG.UI_PUBLIC_OFF_ONE + '">' +
									'</div>' +
								'</div>' +

								// 重置主机名开关，迁移不支持
								'<div class="form-group col-md-4 col-md-6_en reset_hostname_input_div ' + getActiveAndDisplayStr(!thisOption.vmotionFlag) + '">' +
									'<label class="control-label mb5">' + LANG.UI_VM_RESET_HOST_NAME + '：</label>' +
									'<div>' +
										'<input type="checkbox" name="reset_hostname_input" class="make-switch" data-size="small" data-on-color="primary" data-off-color="info" ' +
										'data-on-text="' + LANG.UI_PUBLIC_ON_ONE + '" ' +
										'data-off-text="' + LANG.UI_PUBLIC_OFF_ONE + '">' +
									'</div>' +
								'</div>' +
								// 重置主机名输入框
								'<div class="form-group col-md-4 col-md-6_en reset_hostname_div display-none">' +
									'<label class="control-label mb5">' + '<span class="required">* </span>' + LANG.UI_VM_NEW_HOST_NAME + '：</label>' +
									'<div>' +
										'<input type="text" name="reset_hostname" class="form-control input-sm" maxlength="128" style="height: 28px">' +
									'</div>' +
								'</div>' +
							'</div>' +
						'</div>' +
					'</div>' +
				'</div>' +
			'</div>';
			return div;
		}

		var getImageMetadataDiv = function (image_metadata) {
			let div = '<div class="image_metadata-items">'
			$.each (image_metadata, function (i, v) {
				div += '<div class="form-group display-inline-flex">' +
						'<input class="form-control input-sm image_metadata-keys" value="' + i + '" style="background-color: #eee">' +
						'<input class="form-control input-sm image_metadata-vals" value="' + v + '">' +
						'<a href="javascript:;" class="btn del-image_metadata">' +
							'<i class="viconfont vicon-guanbi" style="font-size: 18px"></i>' +
						'</a>' +
					'</div>';
			});
			div += '</div>';
			div += '<a href="javascript:;" class="btn btn-primary add-image_metadata">' +
					'<i class="viconfont vicon-danchuangtianjia1"></i> ' +
					LANG.UI_VM_IMAGE_METADATA_ADD +
				'</a>';
			return div;
		}
		
		//得到标题栏num(第几个虚拟机),name(虚拟机名字)
		var getVMDiv = function(config, num, sourceConfig){
			//总框架
			var div = '<div class="panel panel-default">' + 
						   '<div class="panel-heading">' + 
							  '<h4 class="panel-title">' + 
								'<a class="accordion-toggle accordion-toggle-styled collapsed" name="' + config.vm_uuid + '" ' + 
								 'data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" data-parent="#' + 
								 parentID + '" href="#vm' + num + '">' + 
								 '<i class="viconfont vicon-ge_vm font-green-seagreen"></i> <span class="font14">' + config.vm_name + '</span></a>' +
							  '</h4>' + 
						  '</div>' + 
						  '<div id="vm' + num + '" class="configsdiv panel-collapse collapse ' + clearString(config.vm_uuid) + '" data-timepointuuid="' + config.timepointuuid + '" data-num="' + num + '">' +
							  '<div class="tabbable-custom " style="overflow: visible">' +
								  '<ul class="nav nav-tabs ">' +
									  getCrosswiseNav(config, num) +
								  '</ul>' +
								  '<div class="tab-content">' +
									  getGeneralContent(config, num) +
									  getDiskContent(config, num, sourceConfig) +
									  getNetworkContent(config, num, sourceConfig) +
									  getOtherContent(config, num) +
								  '</div>' +
							  '</div>' +
						  '</div>' + 
					  '</div>';
			return div;
			
		}

		// 初始化dom
		const initDom = () => {
			//处理<其他>中如果是多个配置的时候出现多个active的情况
			var vmOtherPane = $('.vm-other-pane');	//所有虚拟机的其他,可能有多个虚拟机
			for(var i=0; i<vmOtherPane.length; i++){
				//分别获取其他中的tabs-left的li和展示的tab-pane
				var li = $(vmOtherPane[i]).find('.tabs-left').find('li');
				var tabContentPane = $(vmOtherPane[i]).find('.tab-content').find('.tab-pane');
				var activeNum = -1;
				for(var j=0; j<li.length; j++){
					if($(li[j]).hasClass("active")){
						activeNum = j;
						break;	//只找第一个,要设置显示的是第一个
					}
				}
				//清除所有的li和tab-pane的active,然后再给找到的第一个设置active
				if(activeNum >= 0){
					li.removeClass("active");
					tabContentPane.removeClass("active");

					$(li[activeNum]).addClass("active");
					$(tabContentPane[activeNum]).addClass("active");
				}
			}

			if (!specificFlag) {
				//设置目标目录
				var des_dir = thisOption.des_dir;
				var des_dir_select = $('select[name=des_dir_select]');
				for(var i=0; i<des_dir.length; i++){
					var option = $("<option>").text(des_dir[i].value).val(des_dir[i].key);
					des_dir_select.append(option);
				}

				//设置镜像
				var mirror_image = thisOption.mirror_image;
				var mirror_image_select = $('select[name=mirror_image_select]');
				for(var i=0; i<mirror_image.length; i++){
					var option = $("<option>").text(mirror_image[i].value).val(mirror_image[i].key);
					mirror_image_select.append(option);
				}
			}

			//设置特殊配置
			if(thisOption.hypervisor == thisOption.old_hypervisor){
				if (CONF.VM_TYPE.SANGFORVVDK == thisOption.hypervisor) {
					//scp同平台恢复时放开磁盘总线类型的选择
					$('select[name=disk_bus_type]').prop("disabled", false);
				}
			}else{
				if(CONF.VMTYPE_GROUP.OPENSTACK.includes(thisOption.hypervisor)){
					$('select[name=start_mode]').val(1).prop("disabled",true);
				}
			}

			if (CONF.VM_TYPE.HYPERV == thisOption.hypervisor || CONF.VM_TYPE.CITRIX == thisOption.hypervisor || CONF.VM_TYPE.XCPNG == thisOption.hypervisor) {
				$(`select[name="disk_bus_type"]`).prop('disabled', true);
				$(`select[name="network_bus_type"]`).prop('disabled', true);
			}

			//设置瞬时恢复配置
			if(thisOption.instantFlag){
				//如果是瞬时恢复,禁用目标存储选择（除scp）
				if (CONF.VM_TYPE.SANGFORVVDK != thisOption.hypervisor) {
					$('select[name=hoststorage]').prop("disabled",true);
				}
				//禁用磁盘的勾选框
				$('.disktr').find('.icheck').iCheck('disable');
				// 置备模式统一为精简置备
				$(`select[name=setting_mode]`).val(1);
			}

			// 设置迁移配置
			if (thisOption.vmotionFlag) {
				//禁用磁盘的勾选框
				$('.disktr').find('.icheck').iCheck('disable');
				// 磁盘和网卡总线不能改
				$('.disktr').next('.details').find(`select[name=disk_bus_type]`).prop('disabled', true);
				$('.networktr').next('.details').find(`select[name=network_bus_type]`).prop('disabled', true);
			}

			// 整机跨平台到vm时禁用系统盘的勾选
			if (cmCrossFlag) {
				$('.configsdiv').find('.getHoststorage .disktr[data-type="true"] .icheck').iCheck('disable');
			}

			// ----- 从磁带恢复时的限制 -----
			for (let i in thisOption.config) {
				if (CONF.BD_STORAGE_TYPE.TAPE === parseInt(thisOption.config[i].storage_type)) {
					let _vm = $(`#accordionvm`).find(`.panel-default`).eq(i);
					// 不支持切换磁盘总线类型
					_vm.find('.disktr').next('.details').find(`select[name=disk_bus_type]`).prop('disabled', true);
					// 不支持排除磁盘恢复
					_vm.find('.disktr').find('.icheck').iCheck('disable');
					// 不支持修改网络配置
					_vm.find('.networktr').find('.icheck').iCheck('disable');
					_vm.find('.networktr').find(`input[type=text]`).prop('disabled', true);
					_vm.find('.networktr').find(`select`).prop('disabled', true);
					_vm.find('.networktr').next('.details').find(`input`).prop('disabled', true);
					_vm.find('.networktr').next('.details').find(`select`).prop('disabled', true);
					_vm.find('.networktr').next('.details').find(`input[name=modify_network]`).bootstrapSwitch('disabled', true);
					// 不支持重置主机名
					_vm.find('.vm-other-pane').find('.reset_hostname_input_div').hide();
				}
			}

			//初始化spinner
			// $('.spinnerNum').spinner({step: 1, min: 1, max: 1023});

			//初始化tips
			$('.popovers').popover();

			// 初始化vmware磁盘总线类型的默认值
			if (CONF.VMTYPE_GROUP.VMWARE.includes(thisOption.hypervisor)) {
				$('.os-type').each(function () {
					orderVmwareDiskBusList(this, $(this).find(`option:selected`).data('os_value'));
				})
			}

			//初始化多选下拉框
			$(".selectpicker").selectpicker({
				noneSelectedText: LANG.BILLING_PLEASE_SELECT,
				deselectAllText: LANG.BILLING_DESELECT_ALL,
				selectAllText: LANG.BILLING_SELECT_ALL,
				liveSearchPlaceholder: LANG.BILLING_SEARCH,
				countSelectedText: function(){}
			});
		}

		// 获取cpu架构和操作系统
		const getCpuAndOS = function (i) {
			let p = {};
			p.hypervisor_type = thisOption.hypervisor;
			p.platform_uuid = thisOption.platform_uuid;
			p.host_uuid = thisOption.host_uuid;
			p.region = CONF.VMTYPE_GROUP.OPENSTACK.includes(thisOption.hypervisor) ? thisOption.region : '';
			p.os_version = thisOption.config[i].os_version;
			pAjaxRequest(p, "/api/v1/vm/platforms/support_info", "GET", function (d) {
				if (d.success) {
					thisOption.cpu_info_list = d.data.cpu_info_list;
					thisOption.os_info_list = d.data.os_info_list;
					thisOption.disk_storage_policy = d.data.disk_info ?? [];
					thisOption.config[i].match_os_version = d.data.match_os_version ?? ''; // 每个vm推荐的操作系统版本
				} else {
					operateResponseList(d);
				}
			}, false);
		}

		// 初始化网络配置插件，每个虚拟机的每块网卡
		const initNetworkPlugin = (vm_num, network_num) => {
			// 转换接口返回的src_adapter_list为网络配置插件要的ipv4_set和ipv6_set
			let ipv4_set = {}, ipv6_set = {};
			if (thisOption.source_config && thisOption.source_config[vm_num].net_list && typeof thisOption.source_config[vm_num].net_list[network_num].src_adapter_list !== 'undefined'
				|| thisOption.config && thisOption.config[vm_num].net_list && typeof thisOption.config[vm_num].net_list[network_num].src_adapter_list !== 'undefined'
			) {
				let src_adapter_list = thisOption.source_config && thisOption.source_config[vm_num].net_list
					? thisOption.source_config[vm_num].net_list[network_num].src_adapter_list
					: thisOption.config[vm_num].net_list[network_num].src_adapter_list;
				for (let i in src_adapter_list) {
					let set = {
						config_type: src_adapter_list[i].method != 0 ? src_adapter_list[i].method : 2,
						ip_set: typeof src_adapter_list[i].ip_list !== 'undefined' ? src_adapter_list[i].ip_list.map(row => {
							return {ip: row.ip_addr, netmask: row.netmask};
						}) : [],
						gateway: src_adapter_list[i].gateway,
						dns1: 'undefined' === typeof src_adapter_list[i].dns_list ? '' : (src_adapter_list[i].dns_list[0] ?? ''),
						dns2: 'undefined' === typeof src_adapter_list[i].dns_list ? '' : (src_adapter_list[i].dns_list[1] ?? ''),
					};
					if ('ipv4' === src_adapter_list[i].ip_protocol || '1' === src_adapter_list[i].ip_protocol) {
						ipv4_set = set;
					} else if ('ipv6' === src_adapter_list[i].ip_protocol || '2' === src_adapter_list[i].ip_protocol) {
						ipv6_set = set;
					}
				}
			}

			$.fn.NetworkConfig.init($(`#ipconfig_${vm_num}_${network_num}`), {
				label_width: 'col-md-3',
				input_width: 'col-md-9',
				ipv4_set: ipv4_set,
				ipv6_set: ipv6_set
			}, $.fn.NetworkConfig.USAGE_TYPE_ENUM.DEFAULT);
		}

		// 初始化脚本插件
		const initScriptPlugin = (vm_num) => {
			$(`.scriptBeforeDiv_${vm_num}`).initVinScript({class: `scriptBeforeDiv_${vm_num}`});
			$(`.scriptDiv_${vm_num}`).initVinScript({class: `scriptDiv_${vm_num}`});
		}

		// vmware磁盘总线类型匹配不到就选对应操作系统优先级最高的
		const orderVmwareDiskBusList = (_el, os_type) => {
			$(_el).closest('.configsdiv').find('select[name=disk_bus_type]').each(function () {
				let optionSelected = $(this).find('option[selected="selected"]');
				if (2 == os_type) {
					if (!optionSelected.length) {
						$(this).val(9);
					}
				} else {
					// 其他优先准虚拟SCSI
					if (!optionSelected.length) {
						$(this).val(11);
					}
				}
			});

		}

		// 初始化事件
		const initListener = () => {
			//高级配置事件
			$('.rowhighconfig').on('click', function(){
				//显示/隐藏高级配置行
				$(this).closest("tr").next().toggleClass('displaynone');
				//设置展开收起图标
				$(this).find('span').toggleClass('glyphicon-plus');
				$(this).find('span').toggleClass('glyphicon-minus');
			});

			//设置MAC地址
			$('.mac_type').on('change', function(){
				var macType = parseInt(this.value);
				var inputTd = $(this).closest('td').next();
				var input = inputTd.find('input');
				if(0 == macType){
					input.hide();
				}else if(1 == macType){
					input.show();
					$(input).prop("disabled",true);
					$(input).val($(input).data("old"));
				}else if(2 == macType){
					input.show();
					$(input).prop("disabled",false);
				}
			});

			//添加密码查看隐藏控制
			$('button[name=passcontrol]').on('click', function(){
				var passInput = $(this).closest('div').find('input[name=root_pass_input]');
				if("password" == passInput[0].type){
					passInput[0].type = "text";
				}else{
					passInput[0].type = "password";
				}
				$(this).find('i').toggleClass('fa-eye');
				$(this).find('i').toggleClass('fa-eye-slash');
			});

			//VMware恢复到oVirt4.4以上版本时磁盘接口不能同时为IDE和SATA类型
			$('select[name=disk_bus_type]').on('change', function () {
				var thisVal = this.value;
				if (CONF.VM_TYPE.VMWARE == thisOption.old_hypervisor && CONF.VM_TYPE.RHV == thisOption.hypervisor) {
					var all_bus_type = $(this).parents('.getHoststorage').find('select[name=disk_bus_type]');
					all_bus_type.each(function (i, e) {
						if (1 == thisVal && 5 == this.value) {
							$(this).val(1);
						}
						if (5 == thisVal && 1 == this.value) {
							$(this).val(5);
						}
					})
				}

				// v2v恢复时不是原配置也不是IDE要显示驱动检测
				let oriValue = $(this).find(`option[selected=selected]`).val();
				if (!cmCrossFlag) {
					initDriver(thisOption);
					if (thisVal != oriValue && thisVal != BUS_TYPE_IDE) {
						crossPlatformFlag = true;
					}

					// 私有云需显示cpu的tab和cpu架构
					if (CONF.VMTYPE_GROUP.OPENSTACK.includes(thisOption.hypervisor)) {
						$(this).closest(`.tab-content`).find(`.tabs-left`).find(`li`).eq(1).show();
						$(this).closest(`.tab-content`).find(`select[name=cpu_type]`).closest(`.form-group`).removeClass('displaynone');
					}
				}
			});

			$('select[name=network_bus_type]').on('change', function () {
				let thisVal = this.value;
				// v2v恢复时不是原配置也不是IDE要显示驱动检测
				let oriValue = $(this).find(`option[selected=selected]`).val();
				if (!cmCrossFlag) {
					initDriver(thisOption);
					if (thisVal != oriValue && thisVal != BUS_TYPE_IDE) {
						crossPlatformFlag = true;
					}

					// 私有云需显示cpu的tab和cpu架构
					if (CONF.VMTYPE_GROUP.OPENSTACK.includes(thisOption.hypervisor)) {
						$(this).closest(`.tab-content`).find(`.tabs-left`).find(`li`).eq(1).show();
						$(this).closest(`.tab-content`).find(`select[name=cpu_type]`).closest(`.form-group`).removeClass('displaynone');
					}
				}
			});

			//启动方式切换
			$('select[name=start_mode]').on('change', function(){
				if(this.value == 1){
					$(this).closest('.tab-pane').find('.delDiskDiv').show();
				}else{
					$(this).closest('.tab-pane').find('.delDiskDiv').hide();
				}
			});

			//xhere块存储策略
			$('.blockPolicy').on('click', function () {
				$(this).parent('td').find('.blockPolicy').css({"background": "#FFFFFF", "border": "1px solid rgb(227, 230, 243)"});
				$(this).css({"background": "#E7F7F3", "border": "1px solid #1BA39C"});
				var policy_id = $(this).data('policyid');
				$(this).parent('td').find('[name=volume_policy_id]').val(policy_id);
			});

			//修改目标存储
			$('select[name=hoststorage]').on('change', function () {
				if (CONF.VM_TYPE.SANGFOR == thisOption.hypervisor) {
					//hci恢复磁盘绑定同一个存储
					$(this).closest('tbody').find('select[name=hoststorage]').val(this.value);
				}
				if (CONF.VM_TYPE.SANGFORVVDK == thisOption.hypervisor) {
					//scp恢复/瞬时恢复磁盘绑定同一个存储
					$(this).closest('tbody').find('select[name=hoststorage]').val(this.value);
				}
				if (CONF.VMTYPE_GROUP.HUAWEIKVM.includes(thisOption.hypervisor)) {
					// 恢复到华为kvm，目标存储为nfs时置备模式锁定为【精简】
					if ($(this).find(`option:selected`).data('type') === 'netfs') {
						$(this).closest('tbody').find('select[name=setting_mode]').val(1).prop('disabled', true);
					} else {
						$(this).closest('tbody').find('select[name=setting_mode]').prop('disabled', false);
					}
				}
			});

			//切换删除实例时删除卷开关
			$('input[name=openstack_del_vm_del_disk_mode]').on('switchChange.bootstrapSwitch', function () {
				if (this.checked) {
					$(this).closest('.configsdiv').find('.getHoststorage .disktr[data-type="true"] .icheck').iCheck('disable');
				} else {
					$(this).closest('.configsdiv').find('.getHoststorage .disktr[data-type="true"] .icheck').iCheck('enable');
				}
			})

			// 删除镜像元数据
			$('.del-image_metadata').unbind().on('click', function () {
				$(this).closest('.form-group').remove();
			});

			// 添加镜像元数据
			$('.add-image_metadata').unbind().on('click', function () {
				let div = '<div class="form-group display-inline-flex">' +
					'<input class="form-control input-sm image_metadata-keys" style="background-color: #eee">' +
					'<input class="form-control input-sm image_metadata-vals">' +
					'<a href="javascript:;" class="btn del-image_metadata">' +
					'<i class="viconfont vicon-guanbi" style="font-size: 18px"></i>' +
					'</a>' +
					'</div>';
				$('.image_metadata-items').append(div);
				// 绑定删除
				$('.del-image_metadata').unbind().on('click', function () {
					$(this).closest('.form-group').remove();
				});
			});

			// 切换cpu类型
			$(`select[name=cpu_type]`).on('change', function () {
				initDriver(thisOption);
				let os_type = $(this).closest('.tab-content').find(`select[name=os_type]`).data('os');
				let os_version = $(this).closest('.tab-content').find(`select[name=os_type]`).data('os_version');
				let match_os_version = $(this).closest('.tab-content').find(`select[name=os_type]`).data('match_os_version');
				let osHtml = getOSTypeSelect(thisOption.os_info_list, this.value, os_type);
				$(this).closest('.tab-content').find(`select[name=os_type]`).html(osHtml);
				let osVersionHtml = getOSVersionSelect(thisOption.os_info_list, this.value, os_type, os_version, match_os_version);
				$(this).closest('.tab-content').find(`select[name=os_version]`).empty().append(osVersionHtml).selectpicker('refresh');
				// cpu模式
				let src_cpu_mode = $(this).closest('.tab-content').find(`select[name=cpu_mode]`).data('cpu_mode');
				let cpuModeHtml = getCpuModeOption(this.value, src_cpu_mode, thisOption.cpu_info_list);
				$(this).closest('.tab-content').find(`select[name=cpu_mode]`).html(cpuModeHtml);
			});

			// 切换操作系统类型
			$('.os-type').on('change', function () {
				reloadOSVersionSelect(thisOption.os_info_list, this, $(this).data('os_version'), $(this).data('match_os_version'));
				// 跨平台恢复才需要驱动检测
				if (crossPlatformFlag) {
					initDriver(thisOption);
				}
				// vmware调整磁盘总线类型顺序
				if (CONF.VMTYPE_GROUP.VMWARE.includes(thisOption.hypervisor)) {
					orderVmwareDiskBusList(this, $(this).find(`option:selected`).data('os_value'));
				}

				let i = $(this).closest(`.configsdiv`).data('num');
				scpDiskBusHandler(thisOption.hypervisor, $(this), thisOption.config[i].is_installed_tools);
			});

			// 切换操作系统版本
			$('.os-version').on('change', function () {
				// 跨平台恢复才需要驱动检测
				if (crossPlatformFlag) {
					initDriver(thisOption);
				}
			});

			// 切换引导模式
			$('[name=boot_type_select]').on('change', function () {
				if (CONF.VM_TYPE.VMWARE === parseInt($(this).data('hypervisor')) && 1 === parseInt(this.value)) {
					$(this).closest('.tab-pane').find('.reset_biosuuid_div').show();
				} else {
					$(this).closest('.tab-pane').find('.reset_biosuuid_div').hide();
				}
			});

			// 重置主机名开关
			$('[name=reset_hostname_input]').on('switchChange.bootstrapSwitch', function () {
				if (this.checked) {
					$(this).closest('.tab-pane').find('.reset_hostname_div').show();
				} else {
					$(this).closest('.tab-pane').find('.reset_hostname_div').hide();
				}
			});

			// 修改网络配置开关
			$('[name=modify_network]').on('switchChange.bootstrapSwitch', function () {
				if (this.checked) {
					$(this).closest('tr').next('tr').show();
				} else {
					$(this).closest('tr').next('tr').hide();
				}
			});

			// 切换内存单位
			$('select[name=vm_memory_unit]').on('change', function () {
				if ([CONF.VM_TYPE.H3C, CONF.VM_TYPE.H3CCASCVD, CONF.VM_TYPE.XHERE].includes(thisOption.hypervisor)) {
					let conf = getMemoryInputAttrs(this.value, thisOption.hypervisor);
					let _input = $(this).parent().find('input[name=vm_memory]');
					conf.min ? _input.attr('min', conf.min) : _input.removeAttr('min');
					conf.max ? _input.attr('max', conf.max) : _input.removeAttr('max');
					conf.maxlength ? _input.attr('maxlength', conf.maxlength) : _input.removeAttr('maxlength');
					conf.step ? _input.attr('step', conf.step) : _input.removeAttr('step');
				}
			});

			// 内存大小步进
			$(`.spinnerNum .spinner-up`).on('click', function () {
				let _input = $(this).closest(`.spinner-group`).find(`input`);
				_input.val((parseFloat(_input.val()) + 1));
			});
			$(`.spinnerNum .spinner-down`).on('click', function () {
				let _input = $(this).closest(`.spinner-group`).find(`input`);
				if (parseInt(_input.val()) >= 1) {
					_input.val((parseFloat(_input.val()) - 1));
				}
			});

			// 取消勾选磁盘时存储置灰不可选
			$(`.disktr`).find('.icheck').on('ifUnchecked', function () {
				$(this).closest(`tr`).find(`input[type=text]`).prop('disabled', true);
				$(this).closest(`tr`).find(`select[name=hoststorage]`).prop('disabled', true);
				$(this).closest(`tr`).next().find(`input`).prop('disabled', true);
				$(this).closest(`tr`).next().find(`select`).prop('disabled', true);
			});
			$(`.disktr`).find('.icheck').on('ifChecked', function () {
				$(this).closest(`tr`).find(`input[type=text]`).prop('disabled', false);
				$(this).closest(`tr`).find(`select`).prop('disabled', false);
				$(this).closest(`tr`).next().find(`input`).prop('disabled', false);
				$(this).closest(`tr`).next().find(`select`).prop('disabled', false);
			});

			// 瞬时恢复不能输入重复的磁盘名
			$(`input[name=diskname]`).on('input', function () {
				if (!thisOption.instantFlag) {
					return;
				}
				$(this).closest(`.getHoststorage`).find(`input[name="${this.name}"]`).valid('uniquename');
			});

			// 弹出恢复目标抽屉时的处理
			$(`.selectTargetBtn`).on('click', function () {
				// 初始化目标平台树的加载
				let timepointUuid = $(this).closest(`.configsdiv`).data('timepointuuid');
				$.fn.specificVmRecovery.initTarget({hypervisorType: thisOption.hypervisor, timepointUuid: timepointUuid, pointsDetail: pointsDetail});
			});

			// 确认恢复目标的处理
			$(`#recover-target-submit`).unbind().on('click', selectTargetVm);

			// 切换目标磁盘时刷新显示
			$(`select[name=target_vm_disk]`).on('change', function () {
				let val = $(this).val();
				let lastValue = $(this).attr('data-last_value') ?? '';
				if (!val) {
					$(this).closest(`tr`).find(`.target-disk-bus`).text('--');
					$(this).closest(`tr`).find(`.target-disk-type`).text('--');
				} else {
					$(this).closest(`tr`).siblings(`.disktr`).find(`select[name=target_vm_disk]`).find(`option[value=${val}]`).prop('disabled', true);
				}
				if (lastValue != '' && val !== lastValue) {
					// 修改为其他选项则释放之前选中的
					$(this).closest(`tr`).siblings(`.disktr`).find(`select[name=target_vm_disk]`).find(`option[value="${lastValue}"]`).prop('disabled', false);
				}
				// 更新last_value
				$(this).attr('data-last_value', val ?? '');
				let src_disk_size = parseInt($(this).closest(`tr`).find(`.disk_size`).val());
				let disk_size = parseInt($(this).find(`option[value=${val}]`).data('disk_size'));
				if (disk_size < src_disk_size) {
					// 目标磁盘容量小于源磁盘时:1.释放改选项给其他磁盘；2.改为请选择；3.提示
					$(this).closest(`tr`).siblings(`.disktr`).find(`select[name=target_vm_disk]`).find(`option[value=${val}]`).prop('disabled', false);
					$(this).val('');
					UIToastr.showWarning(LANG.UI_RECOVERY_GOAL, LANG.UI_COMPONENT_TARGET_CAPACITY_ERROR);
					return;
				}
				let bus_type = $(this).find(`option[value=${val}]`).data('bus_type_des');
				let disk_type = $(this).find(`option[value=${val}]`).data('disk_type');
				$(this).closest(`tr`).find(`.target-disk-bus`).text(bus_type);
				$(this).closest(`tr`).find(`.target-disk-type`).text(diskTypeArr[disk_type]);
			});
			initListenersFlag = true;
		}

		// scp磁盘总线屏蔽：windows不支持virtio，linux不支持ide，枚举值VmMiddleControllerType；如果安装了tools则只能选virtio
		const scpDiskBusHandler = (hypervisor, _e, isInstalledTools) => {
			if (CONF.VM_TYPE.SANGFORVVDK !== parseInt(hypervisor)) {
				return;
			}
			let _diskBus = _e.closest(`.configsdiv`).find(`select[name=disk_bus_type]`);
			if (CONF.OS_TYPE.WINDOWS === _e.val()) {
				_diskBus.find(`option[value=1]`).show();
				_diskBus.find(`option[value=4]`).hide();
				_diskBus.val(1);
			}
			if (CONF.OS_TYPE.LINUX === _e.val() || isInstalledTools) {
				_diskBus.find(`option[value=1]`).hide();
				_diskBus.find(`option[value=4]`).show();
				_diskBus.val(4);
			}
		}

		const selectTargetVm = () => {
			let targetData = $.fn.specificVmRecovery.getTargetData();
			if ('' === targetData.vmUuid) {
				UIToastr.showWarning(LANG.UI_RECOVERY_GOAL, LANG.UI_RECOVERY_PLEASE_SELECT_GOAL);
				return false;
			}
			// 先验证密码
			let passwordInput = $.trim($(`#pwd`).val());
			if (!passwordInput) {
				UIToastr.showWarning(LANG.UI_RECOVERY_GOAL, LANG.UI_RECOVERY_TARGET_PLEASE_INPUT_PASSWORD_TIPS);
				return false;
			}
			if (btoa(passwordInput) !== targetData.passwordDe) {
				UIToastr.showWarning(LANG.UI_RECOVERY_GOAL, LANG.UI_RECOVERY_TARGET_VERIFY_PASSWORD_ERROR);
				return false;
			}
			$(`verify_pwd`).hide();
			// 获取配置
			let div = $(`.configsdiv[data-timepointuuid=${targetData.timepointUuid}]`);
			div.find(`.target-path`).val(targetData.vmPath);
			div.find(`input[name=target_platform_uuid]`).val(targetData.vcUuid);
			div.find(`input[name=target_host_uuid]`).val(targetData.hostUuid);
			div.find(`input[name=target_host_name]`).val(targetData.hostName);
			div.find(`input[name=target_region]`).val(targetData.region);
			div.find(`input[name=target_vm_uuid]`).val(targetData.vmUuid);
			div.find(`input[name=target_vm_name]`).val(targetData.vmName);
			div.find(`input[name=target_username]`).val(targetData.username);
			div.find(`input[name=target_password]`).val(targetData.password);
			let p = {
				hypervisor_type: thisOption.hypervisor,
				platform_uuid: targetData.vcUuid,
				host_uuid: targetData.hostUuid,
				region: targetData.region,
				vm_uuid: targetData.vmUuid,
				points_detail: pointsDetail ?? []
			}
			Metronic.blockUI({target: '#vmrecovercontent', animate: true});
			pAjaxRequest(p, "/api/v1/vm/target_vm_config", "GET", function (d) {
				Metronic.unblockUI('#vmrecovercontent');
				let data = d.data;
				data.platform_uuid = p.platform_uuid;
				data.host_uuid = p.host_uuid;
				thisOption.platform_uuid = p.platform_uuid;
				thisOption.host_uuid = p.host_uuid;
				thisOption.region = p.region;

				// ----- 填充配置项的列表和默认值
				let i = div.data('num');
				// 获取SupportInfo
				getCpuAndOS(i);
				// cpu架构
				let cpuArchOption = getCpuTypeOption(thisOption.config[i].cpu_arch, thisOption.cpu_info_list);
				div.find(`select[name=cpu_type]`).empty().html(cpuArchOption);
				// 操作系统和版本
				let osOption = getOSTypeSelect(thisOption.os_info_list, thisOption.config[i].cpu_arch, thisOption.config[i].os_type);
				div.find(`select[name=os_type]`).empty().html(osOption);
				let osVerOption = getOSVersionSelect(thisOption.os_info_list, thisOption.config[i].cpu_arch, thisOption.config[i].os_type, thisOption.config[i].os_version, thisOption.config[i].match_os_version);
				div.find(`select[name=os_version]`).empty().html(osVerOption);
				// 目标磁盘列表
				div.find(`select[name=target_vm_disk]`).empty();
				$.each(thisOption.config[i].disk_list, (idx, val) => {
					let option = `<option value="">${LANG.UI_JOB_SELECT}</option>`;
					$.each(data.disk_list, (n, v) => {
						option += `<option value="${v.disk_uuid}" data-disk_size="${v.virtual_size}" data-storage_uuid="${v.src_storage_uuid}" data-bus_type_des="${v.controller_type_des}" data-bus_type="${v.controller_type}" data-disk_type="${v.disk_type}">${v.disk_name} (` + storageCalculateSize(v.virtual_size) + `)</option>`;
					});
					div.find(`.disktr`).eq(idx).find(`select[name=target_vm_disk]`).append(option);
					let firstOption = div.find(`.disktr`).eq(idx).find(`select[name=target_vm_disk] option`).eq(0);
					div.find(`.disktr`).eq(idx).find(`.target-disk-bus`).text(firstOption.data('bus_type_des'));
					div.find(`.disktr`).eq(idx).find(`.target-disk-type`).text(diskTypeArr[firstOption.data('disk_type')]);
				});
				if (data.disk_list.length < thisOption.config[i].disk_list.length) {
					// 目标vm磁盘个数小于时间点则全部不选中
					// div.find(`.disktr`).find(`.icheck`).iCheck('uncheck');
				}
			});
		}

		return this.each(function(){
			var _this = $(this);
			_this.empty();
			parentID = _this.get(0).id;
			control = thisOption.control;

			var vmInfo = '';
			for(var i=0; i<thisOption.config.length; i++){
				if (thisOption.source_config) {
					thisOption.config[i].cpu_arch = thisOption.source_config[i].cpu_arch;
					thisOption.config[i].os_type = thisOption.source_config[i].os_type;
					thisOption.config[i].os_version = thisOption.source_config[i].os_version;
				}
				if (!specificFlag) {
					getCpuAndOS(i);
				}
				vmInfo += getVMDiv(thisOption.config[i], i, thisOption.source_config ? thisOption.source_config[i] : thisOption.config[i]);
			}
			_this.empty().html(vmInfo);

			//初始化bootstrap-switch
			_this.find('input[name=modify_network]').bootstrapSwitch();
			_this.find('input[name=vmpower]').bootstrapSwitch('state', thisOption.instantFlag && 108 == thisOption.hypervisor); // 瞬时恢复到内嵌默认打开
			_this.find('input[name=reset_biosuuid_input]').bootstrapSwitch('state', true);
			_this.find('input[name=reset_hostname_input]').bootstrapSwitch();
			for (var i = 0; i < thisOption.config.length; i++) {
				var vmuuid = thisOption.config[i].vm_uuid.split('-').join('_').split(':').join('_').split('=').join('_');
				_this.find('.' + vmuuid).find('input[name=openstack_del_vm_del_disk_mode]').bootstrapSwitch('state', thisOption.config[i].delete_on_termination);
				if (specificFlag) {
					_this.find('.' + vmuuid).find('.getHoststorage .disktr[data-type="true"] .icheck').iCheck('check');
				} else {
					// 开启删除实例时删除卷时，无法排除根磁盘
					if (thisOption.config[i].delete_on_termination) {
						_this.find('.' + vmuuid).find('.getHoststorage .disktr[data-type="true"] .icheck').iCheck('disable');
					} else {
						_this.find('.' + vmuuid).find('.getHoststorage .disktr[data-type="true"] .icheck').iCheck('enable');
					}
                }
				_this.find('.' + vmuuid).find('input[name=ha]').bootstrapSwitch('state', thisOption.config[i].is_ha);
			}
			// _this.find('input[name=ha]').bootstrapSwitch();
			_this.find('input[name=original_recovery_input]').bootstrapSwitch();
			//初始化icheck
			_this.find('.icheck').iCheck({
				checkboxClass: 'icheckbox_square-blue',
				radioClass: 'iradio_square-blue',
				//	    	    increaseArea: '20%' // optional
			});

			// 统一初始化
			initDom();
			let vmNum = $(this).find('.panel-default').length;
			for (let i = 0; i < vmNum; i++) {
				// 磁盘总线
				scpDiskBusHandler(thisOption.hypervisor, $(`#tab_${i}c1`).find(`.os-type`), thisOption.config[i].is_installed_tools);
				
				// 网卡
				if (!thisOption.vmotionFlag && !specificFlag) {
					let networkNum = $(`#tab_${i}c3`).find('.networktr').length;
					for (let j = 0; j < networkNum; j++) {
						initNetworkPlugin(i, j);
					}
				}

				if (crossPlatformFlag || thisOption.instantFlag) {
					// 跨平台、瞬时恢复、指定实例恢复需要配置脚本
					initScriptPlugin(i);
				}
			}
			initListener();
		});
	}
	
	$.fn.getvmRecoveryConfig = function(options){
		var motionflag = options.vmotionFlag;
		var cbrflag  = options.cbrflag;
		//获取最终配置信息
		var getResultInfo = function(_this){
			var configsdiv = _this.find('.configsdiv');
			var info = [];
			for(var i=0; i<configsdiv.length;i++){
				let eachConfig = getEachVMConfig(configsdiv[i],options.vmNameLimit,options.hypervisor, i, motionflag, options.instantFlag)
				if(!eachConfig){
					return false;
				}
				info.push(eachConfig);
			}
			info = null2str(info);
			return info;
		}
		
		//得到每台虚拟机配置
		var getEachVMConfig = function(configsdiv,vmNameLimit,hypervisor, i, motionflag, instantFlag){
			var div = $(configsdiv);
			var vm = {};

			if (specificFlag) {
				// 指定虚拟机恢复的参数
				vm.vmname = $.trim(div.find(`input[name=target_vm_name]`).val());
				vm.target_platform_uuid = div.find(`input[name=target_platform_uuid]`).val();
				vm.target_host_uuid = div.find(`input[name=target_host_uuid]`).val();
				vm.target_host_name = div.find(`input[name=target_host_name]`).val();
				vm.target_region = div.find(`input[name=target_region]`).val();
				vm.target_vm_uuid = div.find(`input[name=target_vm_uuid]`).val();
				vm.target_vm_path = div.find(`.target-path`).val();
				vm.timepoint_des = div.prev().find(`a`).text();
				vm.target_username = div.find(`input[name=target_username]`).val();
				vm.target_password = div.find(`input[name=target_password]`).val();
				if (!vm.target_vm_uuid) {
					UIToastr.showWarning(LANG.UI_RECOVERY_PLEASE_SELECT_GOAL, vm.timepoint_des);
					return false;
				}
			} else {
				vm.vmname = $.trim(div.find('input[name=vmname]').val());
				if(!vmNameCheck(vm.vmname,vmNameLimit,hypervisor)){
					return false;
				}
			}

			vm.power = div.find('input[name=vmpower]').bootstrapSwitch('state');
			vm.openstack_del_vm_del_disk_mode = div.find('input[name=openstack_del_vm_del_disk_mode]').bootstrapSwitch('state');
			vm.cpu_socket = div.find('select[name=cpu_socket]').val();
			vm.cpu_core = div.find('select[name=cpu_core]').val();
			vm.cpu_type = div.find('select[name=cpu_type]').val();
			vm.cpu_mode = div.find('select[name=cpu_mode]').val();
			//得到通用配置-内存大小配置
			vm.vm_memory = div.find('input[name=vm_memory]').val();
			vm.vm_memory_unit = div.find('select[name=vm_memory_unit]').val();
			// 操作系统
			vm.os_type = div.find('select[name=os_type]').val();
			vm.os_version = div.find('select[name=os_version]').val();
			vm.os_description = div.find('select[name=os_version]').find(`option:selected`).data('os_des');
			if (hypervisor == options.old_hypervisor && !instantFlag && !motionflag) {
				// 普通恢复操作系统类型和版本必选
				if (vm.os_type == 0) {
					UIToastr.showWarning(LANG.UI_VM_CONFIG_OS_TYPE, LANG.UI_VM_SELECT_OS_TYPE_TIPS);
					return false;
				}
				if (vm.os_version == 0) {
					UIToastr.showWarning(LANG.UI_VM_CONFIG_OS_VERSION, LANG.UI_VM_SELECT_OS_VERSION_TIPS);
					return false;
				}
			}
			//判断非hyper-v的虚拟化检测其内存大小是否符合4M的倍数
			if(!VmMemoryCheck(vm.vm_memory,vm.vm_memory_unit) && hypervisor !=2 && ![CONF.VM_TYPE.H3C, CONF.VM_TYPE.H3CCASCVD, CONF.VM_TYPE.XHERE].includes(hypervisor)){
				UIToastr.showWarning(LANG.UI_VM_MEMORY_SIZE_TITLE, LANG.UI_VM_MEMORY_SIZE_TIPS);
				return false;
			}
			//hyper-v的虚拟化检测其内存大小是否符合2M的倍数
			if(2 == hypervisor && !VmMemoryCheck(vm.vm_memory,vm.vm_memory_unit,2)){
				UIToastr.showWarning(LANG.UI_VM_MEMORY_SIZE_TITLE, LANG.UI_VM_MEMORY_SIZE_HYPERV_TIPS);
				return false;
			}
			// h3c/xhere内存大小限制
			let memValidFlag = true;
			if ([CONF.VM_TYPE.H3C, CONF.VM_TYPE.H3CCASCVD].includes(hypervisor)) {
				if (vm.vm_memory.indexOf('.') !== -1) {
					memValidFlag = false;
				}
				if ('MB' == vm.vm_memory_unit && (parseInt(vm.vm_memory) < 512 || parseInt(vm.vm_memory) % 4 != 0)) {
					memValidFlag = false;
				}
				if (!memValidFlag) {
					UIToastr.showWarning(LANG.UI_VM_MEMORY_SIZE_TITLE, LANG.UI_VM_MEMORY_SIZE_H3C_TIPS);
					return false;
				}
			}
			if (CONF.VM_TYPE.XHERE == hypervisor) {
				if (vm.vm_memory.indexOf('.') !== -1) {
					memValidFlag = false;
				}
				if ('GB' == vm.vm_memory_unit && (parseInt(vm.vm_memory) < 1 || parseInt(vm.vm_memory) > 1024)) {
					memValidFlag = false;
				}
				if ('MB' == vm.vm_memory_unit && (parseInt(vm.vm_memory) < 64 || parseInt(vm.vm_memory) > 1048576)) {
					memValidFlag = false;
				}
				if (!memValidFlag) {
					UIToastr.showWarning(LANG.UI_VM_MEMORY_SIZE_TITLE, LANG.UI_VM_MEMORY_SIZE_XHERE_TIPS);
					return false;
				}
			}
			if (vm.vm_memory == 0) {
				UIToastr.showWarning(LANG.UI_VM_MEMORY_SIZE_TITLE, LANG.UI_VM_MEMORY_SIZE_TIPS2);
				return false;
			}
			vm.storage = getStorageConfig(div);
			if (!vm.storage) {
				return false;
			}
			if (!vm.storage.length) {
				UIToastr.showWarning(LANG.UI_VM_RESTORE_DISK_TITLE, LANG.UI_VM_RESTORE_DISK_EMPTY_TIPS);
				return false;
			}
			vm.network = getNetworkConfig(div, i, motionflag);
			if (!vm.network) {
				return false;
			}
			let crossFlag = options.hypervisor != options.old_hypervisor;
			vm.other = getOtherConfig(div, hypervisor, crossFlag, instantFlag);
			vm.encrypt_pass = btoa(div.find('input[name=encrypt_pass]').val());
			vm.passFlag = $.trim(div.find('input[name=encrypt_pass_flag]').val()) == "active";
			vm.timepointuuid = div.find('input[name=timepointuuid]').val();
			vm.openstack_start_mode = 1;	//openstack启动方式
			vm.root_disk_size = 0; //根磁盘大小初始化
			vm.flavor_id = "";
			if (!vm.network.length) {
				// 恢复的时候，必须至少保留一个网卡
				if (CONF.VMTYPE_GROUP.PRIVATECLOUD.includes(hypervisor)) {
					UIToastr.showWarning(LANG.UI_INSTANCE_SETTING_NETWORK_CONFIG, LANG.UI_VM_RESTORE_NETWORK_EMPTY_TIPS);
					return false;
				} else if (CONF.VM_TYPE.H3C == hypervisor
					|| CONF.VM_TYPE.H3CCASCVD == hypervisor
					|| CONF.VM_TYPE.XHERE == hypervisor
					|| CONF.VM_TYPE.SMARTX == hypervisor
				) {
					UIToastr.showWarning(LANG.UI_VM_SETTING_NETWORK_CONFIG, LANG.UI_VM_RESTORE_NETWORK_EMPTY_TIPS);
					return false;
				}
			}
			if(!vm.storage || !vm.network || !vm.other){//检测是否输入合规
				return false;
			}
			//---------虚拟化个性化显示------------
			//如果是类OpenStack,设置cpu_scoket和cpu_core,通过后台过来的标志判断
			var openstackFlag = div.find('select[name=vcpu]').data("openstack");
			if(openstackFlag && !specificFlag){
				var instance = div.find('select[name=instance_socket]').val();
				//如果是原配置
				if(instance == 0){
					UIToastr.showWarning(LANG.UI_VM_CONFIG_EXAMPLE_TYPE,LANG.UI_VM_SELECT_EXAMPLE_TYPE_TIPS);
					return false;
				}else{
					var info = instance.split('_'); 
					vm.cpu_socket = info[0];
					vm.cpu_core = "1";
					vm.vm_memory = info[1];
					vm.vm_memory_unit = "GB";
					vm.root_disk_size = info[2];
					vm.flavor_id = info[3];
  
					//处理实例id带下划线的情况
					var len = info.length;
					for (i = 4; i < len; i++) {
						  vm.flavor_id += '_' + info[i];
					}
				}
				vm.openstack_start_mode = div.find('select[name=start_mode]').val();
			}
			var xskyzstack = div.find('select[name=xzcpu]').data("xskyzstack");
			if(xskyzstack){
				vm.cpu_socket = div.find('select[name=xzcpu]').val();
				vm.cpu_core = "1";
			}
  
			//hyperv
			var hyperv = div.find('select[name=hypervcpu]').data("hyperv");
			if (hypervisor == CONF.VM_TYPE.HYPERV && hyperv) {
				vm.cpu_socket = div.find('select[name=hypervcpu]').val();
				vm.cpu_core = '1';
			}
			return vm;
		}
		
		//得到其他配置
		var getOtherConfig = function(div, hypervisor, crossFlag, instantFlag){
			var data = {};
			data.boot_type_select = div.find('select[name=boot_type_select]').val();
			data.reset_biosuuid = div.find('input[name=reset_biosuuid_input]').bootstrapSwitch('state');
			data.available_domain_select = div.find('select[name=available_domain_select]').val();
			data.disk_domain_select = div.find('select[name=disk_domain_select]').val();
			data.root_pass_input = div.find('input[name=root_pass_input]').val();
			data.des_dir_select = div.find('select[name=des_dir_select]').val();
			data.mirror_image_select = div.find('select[name=mirror_image_select]').val();
			data.ha = div.find('input[name=ha]').bootstrapSwitch('state');
			data.virtual_type_select = div.find('select[name=virtual_type_select]').val();
			data.vm_version = div.find('select[name=vm_version_select]').val();
			data.os_type = div.find('select[name=os_type_select]').val();
			data.original_recovery = div.find('input[name=original_recovery_input]').bootstrapSwitch('state');
			data.power = div.find('input[name=vmpower]').bootstrapSwitch('state');
			data.reset_hostname = div.find('input[name=reset_hostname_input]').bootstrapSwitch('state');
			data.new_hostname = div.find('input[name=reset_hostname]').val();

			// 获取脚本配置
			if (crossFlag || instantFlag) {
				let num = div.find('.scriptDiv').data('num');
				data.script_before_data = $('.scriptBeforeDiv_' + num).getVinScript('scriptBeforeDiv_' + num);
				data.script_data = $('.scriptDiv_' + num).getVinScript('scriptDiv_' + num);
				if (false === data.script_data) {
					return false;
				}
				let scriptFlag = true;
				$.each(data.script_data, function (i, v) {
					if ('' === $.trim(v.script_name)) {
						UIToastr.showWarning(LANG.UI_SCRIPT_GET_SCRIPT, LANG.UI_SCRIPT_ENTER_SCRIPT_NAME);
						scriptFlag = false;
						return false;
					}
				});
				if (!scriptFlag) {
					return false;
				}
			}

			//openstack镜像元数据
			data.image_metadata = {};
			if (CONF.VMTYPE_GROUP.OPENSTACK.includes(hypervisor)) {
				let _key = $('.image_metadata-keys');
				let _val = $('.image_metadata-vals');
				for (let i = 0; i < _key.length; i++) {
					if ('' === _key[i].value) {
						// key不能为空
						UIToastr.showWarning(LANG.UI_VM_IMAGE_METADATA, LANG.UI_VM_IMAGE_METADATA_KEY_EMPTY_TIPS);
						return false;
					}
					// if ('' === _val[i].value) {
					// 	// value不能为空
					// 	UIToastr.showWarning(LANG.UI_VM_IMAGE_METADATA, LANG.UI_VM_IMAGE_METADATA_VALUE_EMPTY_TIPS);
					// 	return false;
					// }
					if (data.image_metadata[_key[i].value]) {
						// key不能重复
						UIToastr.showWarning(LANG.UI_VM_IMAGE_METADATA, LANG.UI_VM_IMAGE_METADATA_KEY_REPEAT_TIPS);
						return false;
					}
					data.image_metadata[_key[i].value] = _val[i].value;
				}
			}
			
			//迁移除了CBR支持选择操作系统  其他无需提示
			if (CONF.VM_TYPE.HUAWEICBR == hypervisor){
				if(options.control.os_type && data.os_type == 0 && options.hypervisor != options.old_hypervisor ){
					UIToastr.showWarning(LANG.UI_VM_CONFIG_OS_TYPE,LANG.UI_VM_SELECT_OS_TYPE_TIPS);
					return false;
				}
			}else{
				if(motionflag){
					//如果是迁移 同步下来的时间点进行恢复之后再进行迁移也需要进行提示
					if(CONF.VM_TYPE.HUAWEICBR == options.original_hypervisor){
						if(options.control.os_type && data.os_type == 0 && options.hypervisor != options.old_hypervisor ){
							UIToastr.showWarning(LANG.UI_VM_CONFIG_OS_TYPE,LANG.UI_VM_SELECT_OS_TYPE_TIPS);
							return false;
						}
					}
				}
				if (!specificFlag) {
					if(options.control.os_type && data.os_type == 0 && options.hypervisor != options.old_hypervisor
						&& (CONF.VM_TYPE.VMWARE == options.hypervisor || CONF.VMTYPE_GROUP.OPENSTACK.includes(options.hypervisor)) && !motionflag){
						UIToastr.showWarning(LANG.UI_VM_CONFIG_OS_TYPE,LANG.UI_VM_SELECT_OS_TYPE_TIPS);
						return false;
					}
				}
			}

			return data;
		}
		
		//得到存储配置
		var getStorageConfig = function(div){
			var storage = [];
			var vmstorage = div.find('.vmstorage');
			var vmdisktype = div.find('.vmdisktype');
			let targetDiskUuidArr = [];
			for(var i=0; i<vmstorage.length; i++){
				var data = {};
				var tr = $(vmstorage[i]).parent().get(0);
				if(!$(tr).find('.icheck').is(":checked")){
					continue;
				}
				//磁盘uuid,磁盘大小,选择存储uuid,是否自动选择存储
				data.src_disk_uuid = vmstorage[i].id;
				data.size = Number(tr.id);
				
				data.disk_name = $(tr).find('span[name=diskname]').html();		//不可修改磁盘名字
				data.src_disk_name = $(tr).find('span[name=diskname]').data('src_disk_name');
				if(data.disk_name == undefined){
					data.disk_name = $(tr).find('input[name=diskname]').val();	//可以修改磁盘名字
					data.src_disk_name = $(tr).find('input[name=diskname]').data('src_disk_name');
				}
				var storageuuid = $(vmstorage[i]).find('select[name=hoststorage]').val();
				if("0" == storageuuid){
					//自动选择存储
					data.auto_conf_flag = 1;
					data.target_storage_uuid = '';
				}else{
					data.auto_conf_flag = 2;
					data.target_storage_uuid = storageuuid;
				}
				var detailTr = $(tr).next();
				if (specificFlag) {
					// 指定虚拟机恢复的目标磁盘
					data.target_disk_uuid = $(tr).find(`select[name=target_vm_disk]`).val();
					if ($.inArray(data.target_disk_uuid, targetDiskUuidArr) > -1) {
						// 禁止多个磁盘恢复到同一目标磁盘
						UIToastr.showWarning(LANG.UI_VM_RESTORE_DISK_TITLE, LANG.UI_RECOVERY_TARGET_DISK_SAME_TIPS);
						return false;
					}
					targetDiskUuidArr.push(data.target_disk_uuid);
					data.target_storage_uuid = $(tr).find(`select[name=target_vm_disk] option:selected`).data('storage_uuid');
					data.disk_type = parseInt($(tr).find(`select[name=target_vm_disk] option:selected`).data('disk_type'));
					data.bus_type = parseInt($(tr).find(`select[name=target_vm_disk] option:selected`).data('bus_type'));
					if (!data.target_disk_uuid) {
						UIToastr.showWarning(LANG.UI_VM_RESTORE_DISK_TITLE, LANG.UI_RECOVERY_TARGET_DISK_SELECT_TIPS);
						return false;
					}
				} else {
					//磁盘类型,总线类型,分块大小
					data.disk_type = parseInt(detailTr.find('select[name=setting_mode]').val());
					data.bus_type = parseInt(detailTr.find('select[name=disk_bus_type]').val());
					//针对hyper-v特殊处理
					var disktypehyperv = div.find('select[name=disktypehyperv]').data("disktypehyperv");
					if(disktypehyperv){
						data.disk_type = parseInt(div.find('select[name=disktypehyperv]').val());
					}
				}
				data.cluster_size = parseInt(detailTr.find('select[name=cluster_type]').val());
				data.policy_id = parseInt(detailTr.find('input[name=volume_policy_id]').val());
				data.storage_policy_id = detailTr.find('select[name=storage_policy_id]').val();

				storage.push(data);
			}
			return storage;
		}
		
		//转换keep_mac_flag,和后台对应起来
		var parseKeepMacFlag = function(keep_mac_flag){
			var endFlag = 1;
			if(0 == keep_mac_flag){
				endFlag = 2;	//自动生成是2,其他都是1
			}
			return endFlag;
		}
		
		//得到网络配置
		var getNetworkConfig = function(div, vm_num, motionflag){
			var network = [];
			let ipV4List = [];
			let ipV6List = [];
			var vmnetwork = div.find('.vmnetwork');
			let vmOriName = div.prev().find('span').text();
			for(var i=0; i<vmnetwork.length; i++){
				var data = {};
				var vnw = $(vmnetwork[i]);
				var vnwd = vnw.next('.details');
				if(!vnw.find('input[name=networkcheck]').get(0).checked){
					continue;
				}
				//网卡,mac,保留mac标志,选择网卡uuid,是否自动选择
				data.src_network_uuid = vnw.find('.src_network_uuid').val();
				data.src_network_name = vnw.find('.nwname').html();
				data.keep_mac_flag = parseKeepMacFlag(parseInt(vnw.next().find('select[name=mac_type]').val()));
				data.bus_type = parseInt(vnw.next().find('select[name=network_bus_type]').val());
				data.mac_addr = vnw.next().find('input[name=nwmac]').val();
				data.modify_network_flag = vnwd.find('input[name=modify_network]').length ? vnwd.find('input[name=modify_network]').bootstrapSwitch('state') : false;

				if (!motionflag && data.modify_network_flag) {
					let appendTips = ` (${LANG.UI_VCENTER_VM}: ${vmOriName}, ${LANG.UI_PUBLIC_NET_CARD}: ${data.src_network_name})`;
					// 从插件内获取网络配置
					let netData = $.fn.NetworkConfig.getData($(`#ipconfig_${vm_num}_${i}`));
					if (!netData) {
						let title = CONF.VMTYPE_GROUP.PRIVATECLOUD.includes(options.hypervisor) ? LANG.UI_INSTANCE_SETTING_NETWORK_CONFIG : LANG.UI_VM_SETTING_NETWORK_CONFIG;
						UIToastr.showWarning(title, LANG.UI_VM_SETTING_NETWORK_CONFIG_ERROR_TIPS + appendTips);
						return false;
					}
					data.ipv4_set = netData.ipv4_set;
					data.ipv6_set = netData.ipv6_set;
					// 检测手动配置的ipv4
					if (2 === data.ipv4_set.config_type) {
						for (let i in data.ipv4_set.ip_set) {
							// 检测ip
							if ('' === data.ipv4_set.ip_set[i].ip || !ipV4V6(data.ipv4_set.ip_set[i].ip)) {
								UIToastr.showWarning(LANG.UI_VM_SETTING_NETWORK_CONFIG_IPV4, LANG.UI_SETTING_INPUT_IP + appendTips);
								return false;
							}
							if (ipV4List.includes(data.ipv4_set.ip_set[i].ip)) {
								UIToastr.showWarning(LANG.UI_VM_SETTING_NETWORK_CONFIG_IPV4, LANG.UI_SETTING_INPUT_IP_REPEAT + appendTips);
								return false;
							}
							// 记录已有ip防止重复
							ipV4List.push(data.ipv4_set.ip_set[i].ip);

							// 检测子网掩码
							if (!data.ipv4_set.ip_set[i].netmask) {
								UIToastr.showWarning(LANG.UI_VM_SETTING_NETWORK_CONFIG_IPV4, LANG.UI_SETTING_INPUT_NETMASK + appendTips);
								return false;
							}
						}
						// 检测网关
						if ('' !== data.ipv4_set.gateway && !ipV4V6(data.ipv4_set.gateway)) {
							UIToastr.showWarning(LANG.UI_VM_SETTING_NETWORK_CONFIG_IPV4, LANG.UI_SETTING_INPUT_GATEWAY + appendTips);
							return false;
						}
						// 检测DNS
						if (data.ipv4_set.dns1 && !ipV4V6(data.ipv4_set.dns1) || data.ipv4_set.dns2 && !ipV4V6(data.ipv4_set.dns2)) {
							UIToastr.showWarning(LANG.UI_VM_SETTING_NETWORK_CONFIG_IPV4, LANG.UI_SETTING_INPUT_DNS + appendTips);
							return false;
						}
					}
					// 检测手动配置的ipv6
					if (2 === data.ipv6_set.config_type) {
						for (let i in data.ipv6_set.ip_set) {
							// 检测ip
							if ('' === data.ipv6_set.ip_set[i].ip || !ipV4V6(data.ipv6_set.ip_set[i].ip)) {
								UIToastr.showWarning(LANG.UI_VM_SETTING_NETWORK_CONFIG_IPV6, LANG.UI_SETTING_INPUT_IP + appendTips);
								return false;
							}
							if (ipV6List.includes(data.ipv6_set.ip_set[i].ip)) {
								UIToastr.showWarning(LANG.UI_VM_SETTING_NETWORK_CONFIG_IPV6, LANG.UI_SETTING_INPUT_IP_REPEAT + appendTips);
								return false;
							}
							// 记录已有ip防止重复
							ipV6List.push(data.ipv6_set.ip_set[i].ip);

							// 检测子网掩码
							if (!data.ipv6_set.ip_set[i].netmask) {
								UIToastr.showWarning(LANG.UI_VM_SETTING_NETWORK_CONFIG_IPV6, LANG.UI_SETTING_INPUT_PREFIX + appendTips);
								return false;
							}
						}
						// 检测网关
						if ('' !== data.ipv6_set.gateway && !ipV4V6(data.ipv6_set.gateway)) {
							UIToastr.showWarning(LANG.UI_VM_SETTING_NETWORK_CONFIG_IPV6, LANG.UI_SETTING_INPUT_GATEWAY + appendTips);
							return false;
						}
						// 检测DNS
						if (data.ipv6_set.dns1 && !ipV4V6(data.ipv6_set.dns1) || data.ipv6_set.dns2 && !ipV4V6(data.ipv6_set.dns2)) {
							UIToastr.showWarning(LANG.UI_VM_SETTING_NETWORK_CONFIG_IPV6, LANG.UI_SETTING_INPUT_DNS + appendTips);
							return false;
						}
					}
				}

				//mac地址正则检测
				var mac_rul = new RegExp(/^([0-9a-fA-F]{2})(([/\s:][0-9a-fA-F]{2}){5})$/);//匹配有冒号的
				var mac_rul_no = new RegExp(/^([0-9a-fA-F]{2})(([0-9a-fA-F]{2}){5})$/);//匹配没有冒号的
				if(1 == data.keep_mac_flag && !(data.mac_addr == "" || data.mac_addr == null || data.mac_addr == undefined) && !mac_rul.test(data.mac_addr) && !mac_rul_no.test(data.mac_addr)){
					UIToastr.showInfo(LANG.UI_VM_SETTING_MAC_STEP2, LANG.UI_VM_SETTING_HITE);
					return false;
				}
				if (1 == data.keep_mac_flag && "" == data.mac_addr) {
					UIToastr.showWarning(LANG.UI_VM_SETTING_MAC_STEP2, LANG.UI_VM_SETTING_MAC_EMPTY_TIPS);
					return false;
				}
				
				data.target_network_uuid = vnw.find('select[name=hostnetwork]').val();
				if("0" == data.target_network_uuid){
					//自动选择存储
					data.auto_conf_flag = 1;
					data.target_network_uuid = '';
				}else{
					data.auto_conf_flag = 2;
				}
				network.push(data);
			}
			return network;
		}
		
		
		
	   
		
		/**
		 * null => ''
		 * @param {*} data 要处理的数据
		 */
		var null2str = function(data) {
		  for (var x in data) {
			if (data[x] === null || data[x] === undefined) { // 如果是null 把直接内容转为 ''
			  data[x] = '';
			} else {
			  if (Array.isArray(data[x])) { // 是数组遍历数组 递归继续处理
				data[x] = null2str(data[x]);
			  }
			  if(typeof(data[x]) === 'object'){ // 是json 递归继续处理
				data[x] = null2str(data[x]);
			  }
			}
		  }
		  return data;
		}
		
		/**
		 * 检查虚拟机名称是否符合规则
		 * vm_name 输入的虚拟机名称
		 * vm_limit 名称的限制
		 * hypervisor 虚拟化类型
		 * return bool  
		 */
		var vmNameCheck = function(vm_name,vm_limit,hypervisor){
			var vm_num = vm_limit.len;//虚拟机限制字符长度
			var vm_reg = vm_limit.limit; //虚拟机名称限制输入
			var vm_msg = vm_limit.msg; //虚拟机提示
			var vm_reg_msg = LANG.UI_TOOLS_VMNAME_TIPS; //虚拟机限制字符类型提示
			if(vm_num != ''){
				  if (CONF.VM_TYPE.VMWARE == hypervisor) {
						//vmware平台%/\算3个字符，其他算一个
						num = vm_name.replace(/[\\%/]/g, 'xxx').length;
				} else if (CONF.VM_TYPE.INCLOUDKVM == hypervisor || CONF.VM_TYPE.INSPURVVDK == hypervisor || CONF.VM_TYPE.KSPHERE == hypervisor) {
						//ics,ics-vvdk所有字符都算一个
					num = vm_name.length;
					vm_reg_msg = LANG.UI_TOOLS_VMNAME_ICS_TIPS;
				} else if (CONF.VM_TYPE.SANGFOR == hypervisor) {
					num = vm_name.replace(/[^\x00-\xff]/g, 'xxx').length;
				} else {
					num = vm_name.replace(/[^\x00-\xff]/g, 'xx').length;
				}
				if(num > vm_num){
					UIToastr.showWarning(LANG.UI_VM_RESTORE_NAME_TITLE, vm_msg);
					return false;
				}
			}
			if(vm_reg != ''){
				var name_rule = new RegExp(vm_reg);
				var name_rule_result = name_rule.test(vm_name);
				if(!name_rule_result){
					UIToastr.showWarning(LANG.UI_VM_RESTORE_NAME_TITLE, vm_reg_msg);
					return false;
				}
			}
			return true;
		} 
		
		
		/**
		 * 统一检查所有虚拟机内存大小，必须为4MB的倍数
		 */
		var VmMemoryCheck = function(vm_memory,vm_unit, check_size = 4){
			$result = false;
			var data_MB = 0;
			if(vm_unit == "MB"){
				data_MB = vm_memory;
			}else if(vm_unit == "GB"){
				data_MB = vm_memory*1024;
			}else if(vm_unit == "TB"){
				data_MB = vm_memory*1024*1024;
			}
			if(data_MB>0 && data_MB% check_size ==0){
				return true;//不为0且为4MB的倍数
			}
			return $result;
		}
		
		
		
		
		
		return getResultInfo($(this));
	}

	// 获取单个虚拟机源磁盘总线
	$.fn.getVmRecoverySourceDiskBus = function (timepoint_uuid) {
		return getVmSourceBusList(timepoint_uuid, 'disk_bus_type');
	}
	// 获取单个虚拟机源网卡总线
	$.fn.getVmRecoverySourceNetBus = function (timepoint_uuid) {
		return getVmSourceBusList(timepoint_uuid, 'network_bus_type');
	}
	// 获取单个虚拟机所选的磁盘总线
	$.fn.getVmRecoveryDiskBus = function (timepoint_uuid) {
		return getVmBusList(timepoint_uuid, 'disk_bus_type');
	}
	// 获取单个虚拟机所选的网卡类型总线
	$.fn.getVmRecoveryNetBus = function (timepoint_uuid) {
		return getVmBusList(timepoint_uuid, 'network_bus_type');
	}

	// 获取驱动检测信息
	$.fn.getVmRecoveryDriverCheckInfo = function () {
		return {
			showDriver: showDriver,
			isDriverChecked: isDriverChecked,
			driverCheckResult: driverCheckResult
		};
	}

	// 获取虚拟机的一些配置信息
	const getVmConfig = function (timepoint_uuid, field) {
		return $(`.configsdiv[data-timepointuuid=${timepoint_uuid}]`).find(`select[name=${field}]`).val();
	}

	const getVmSourceBusList = function (timepoint_uuid, field) {
		let list = [];
		let _bus = $(`select[name=${field}]`).data('timepointuuid', timepoint_uuid);
		let defaultBus = '';
		$.each(_bus, function (i, v) {
			let checked = $(this).closest(`.details`).parent(`tr`).prev().find('input[type=checkbox]').prop('checked');
			if (checked) {
				list.push($(this).data('old_type').toString());
			}
			if (i == 0) {
				defaultBus = this.value;
			}
		});
		// 如果没勾选就取第一个的
		if (!list.length) {
			list.push(defaultBus);
		}
		return list;
	}

	const getVmBusList = function (timepoint_uuid, field) {
		let list = [];
		let _bus = $(`select[name=${field}]`).data('timepointuuid', timepoint_uuid);
		// console.log(_bus);
		let defaultBus = '';
		$.each(_bus, function (i, v) {
			let checked = $(this).closest(`.details`).parent(`tr`).prev().find('input[type=checkbox]').prop('checked');
			if (checked) {
				list.push(this.value);
			}
			if (i == 0) {
				defaultBus = this.value;
			}
		});
		// 如果没勾选就取第一个的
		if (!list.length) {
			list.push(defaultBus);
		}
		return list;
	}

	// 开始驱动检测
	const initDriver = function (thisOption) {
		// 这里的驱动检测只处理原平台恢复的
		if (thisOption.hypervisor != thisOption.old_hypervisor) return;
		// 获取的操作系统或版本为空则禁用驱动检测按钮
		let checkDisabled = false;
		// 隐藏是否继续恢复
		$('#driverCheckFailContinue').hide();
		$('#driverCheck_vm_button').show();
		
		showDriver = true;
		isDriverChecked = false;
		driverCheckResult = false;
		$('#driverCheck').show();
		let source_list = [];
		$.each(thisOption.config, function (i, v) {
			let os_type = getVmConfig(v.timepointuuid,'os_type');
			let os_version = getVmConfig(v.timepointuuid,'os_version');
			let source_hypervisor_disk_bus_list = getVmSourceBusList(v.timepoint_uuid, 'disk_bus_type');
			let source_hypervisor_net_bus_list = getVmSourceBusList(v.timepoint_uuid, 'network_bus_type');
			let target_hypervisor_disk_bus_list = getVmBusList(v.timepoint_uuid, 'disk_bus_type');
			let target_hypervisor_net_bus_list = getVmBusList(v.timepoint_uuid, 'network_bus_type');
			source_list.push({
				name: v.vm_name,
				timepoint_uuid: v.timepointuuid,
				module_type: CONF.MODULE_TYPE.VM,
				source_hypervisor_disk_bus_list: source_hypervisor_disk_bus_list,
				source_hypervisor_net_bus_list: source_hypervisor_net_bus_list,
				target_hypervisor_disk_bus_list: target_hypervisor_disk_bus_list,
				target_hypervisor_net_bus_list: target_hypervisor_net_bus_list,
				os_type: os_type,
				os_version: os_version,
				os_arch: getVmConfig(v.timepointuuid,'cpu_type')
			});
			if (0 == os_type) {
				checkDisabled = true;
			}
		});
		// if (arraysDeepEqual(source_list, source_list_init)) {
		// 	// 和原配置一样则不进行驱动检测
		// 	showDriver = false;
		// 	isDriverChecked = false;
		// 	driverCheckResult = false;
		// 	$('#driverCheck').hide();
		// 	return;
		// }

		let p = {};
		p.hypervisor = thisOption.hypervisor;
		p.vcenter_uuid = thisOption.platform_uuid;
		p.host_uuid = thisOption.host_uuid;
		// 需要先获取这个虚拟化类型的磁盘总线列表和网络类型列表
		Metronic.blockUI({target: '#host_tree',animate: true});
		pAjaxRequest(p, '/api/v1/recovery/vm/disk_network', 'GET', function (result) {
			Metronic.unblockUI('#host_tree');
			if (result.success) {
				// 驱动检测
				$('#driverCheck').show();
				var vm_info = {};
				vm_info.vm_type = p.hypervisor;
				vm_info.vm_uuid = p.vcenter_uuid;
				vm_info.host_uuid = p.host_uuid;
				vm_info.disk_bus_list = result.data.disk_bus;
				vm_info.net_bus_list = result.data.network_bus;
				vm_info.default_disk_bus = result.data.default_disk;
				vm_info.default_net_bus = result.data.default_network;
				vm_info.bus_changeable_flag = result.data.enable_change;
				getDriver(source_list, vm_info, checkDisabled);
			} else {
				operateResponseList(result);
			}
		}, false)
	}

	const getDriver = function (source_list, vm_info, checkDisabled) {
		// 实例化驱动检测组件
		$.fn.driverCheck.init($('#driverCheck'), {
			width: {
				config_label: 'col-md-3',
				config_content: 'col-md-9',
			},
			getCheckObject: () => {
				return {
					source_list: source_list,
					target_info: {
						target_type: $.fn.driverCheck.DRIVER_TARGET_TYPE.VM,
						vm_info: vm_info
					}
				};
			},
			show_check_btn: false,
			afterCheck:function (result, data){
				isDriverChecked = true; // 已完成检测
				driverCheckResult = result;
				// 隐藏是否继续恢复
				$('#driverCheckFailContinue').hide();
				if (!result) {
					// 检测失败显示是否继续恢复
					$('#driverCheckFailContinue').show();
				}
			}
		});
		if (checkDisabled) {
			UIToastr.showWarning(LANG.UI_VM_SETTING_OS_TITLE, LANG.UI_VM_SETTING_OS_EMPTY_CANNOT_DRIVER_CHECK_TIPS);
			$(`#driverCheck_checkDriver`).prop('disabled', true);
			isDriverChecked = true; // 设置为通过
			driverCheckResult = true;
		}
	}

	// 递归的去比较数组元素
	function arraysDeepEqual(arr1, arr2) {
		if (!Array.isArray(arr1) || !Array.isArray(arr2) || arr1.length !== arr2.length) {
			return false;
		}
		for (let i = 0; i < arr1.length; i++) {
			if (!deepEqual(arr1[i], arr2[i])) {
				return false;
			}
		}
		return true;
	}
	// 比较数组
	function deepEqual(obj1, obj2) {
		if (obj1 === obj2) return true;

		if (typeof obj1 !== 'object' || obj1 === null || typeof obj2 !== 'object' || obj2 === null) {
			return false;
		}

		const keys1 = Object.keys(obj1);
		const keys2 = Object.keys(obj2);

		if (keys1.length !== keys2.length) {
			return false;
		}

		for (let key of keys1) {
			if (!keys2.includes(key) || !deepEqual(obj1[key], obj2[key])) {
				return false;
			}
		}

		return true;
	}
  })(jQuery)