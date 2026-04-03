var AddAppGroup = function(){
	let data = {};
	let ztree;
    let sortObj;	//排序对象
	let _VMNAMEREG = new RegExp("[ \n\r\t\f`~!@#$^&*()=|{}':;',\\[\\].<>/?~！@#￥……&*（）&;|{}【】‘；：”“'。，、？+-]");
	const netmaskVal=/^(254|252|248|240|224|192|128|0)\.0\.0\.0|255\.(254|252|248|240|224|192|128|0)\.0\.0|255\.255\.(254|252|248|240|224|192|128|0)\.0|255\.255\.255\.(254|252|248|240|224|192|128|0)$/;
	let networkList = [];
	let editFlag = false; //是否是修改
	let storageList = [];
	let source_list = [];
	let cpu_info_list = [], os_info_list = [];
	let nodeMap = {}; // 存放liID => node的键值对
	let dirverCheckFlag = true;
	let _pageSize = 40; //代理端文件列表每次显示条数;
	const TIMEPOINT_TYPE_ALL = 1; // 所有时间点
	const TIMEPOINT_TYPE_LAST = 2; // 最新时间点
	const TIMEPOINT_TYPE_SELECT = 3; // 指定时间点
	let selectModule = [];
	//获取时间点原配置信息
	let hostSettings = {};
	let threadShowFlag = false;
	let currentModule;
	let timepointParams;
	let pointList = [];
	let backupModeDes = [
		LANG.UI_PUBLIC_UNKNOWN,
		LANG.UI_DATA_TYPE_FULL,
		LANG.UI_DATA_TYPE_INCR,
		LANG.UI_DATA_TYPE_DIFF,

	];
	let zTreeFile = [];

	//模块类型初始化
	const moduleList = [
		{
			name: LANG.UI_PUBLIC_VM,
			module: 2,
			submodule: 1,
			product_type: 1
		},
		{
			name: LANG.UI_PUBLIC_PRIVATE_CLOUD,
			module: 2,
			submodule: 2,
			product_type: 1
		},
		{
			name: LANG.UI_PUBLIC_PUBLIC_CLOUD,
			module: 2,
			submodule: 3,
			product_type: 1
		},
		{
			name: LANG.UI_BACKUP_DATA_MODULE_OS,
			module: 5,
			submodule: 1,
			product_type: 1
		},
		{
			name: LANG.UI_VISUAL_FILE_FILE,
			module: 3,
			submodule: 1,
			product_type: 1
		},
		{
			name: LANG.UI_BACKUP_DATA_MODULE_NAS,
			module: 11,
			submodule: 2,
			product_type: 1
		},
		{
			name: LANG.UI_VISUAL_OBS,
			module: 3,
			submodule: 4,
			product_type: 1
		},
		{
			name: LANG.UI_VISUAL_HADOOP_BACKUP,
			module: 3,
			submodule: 3,
			product_type: 1
		},
		{
			name: LANG.UI_VISUAL_M365,
			module: 14,
			submodule: 0,
			product_type: 1
		},
		{
			name: LANG.UI_VISUAL_MODULE_DB,
			module: 4,
			submodule: 0,
			product_type: 1
		},
		{
			name: LANG.UI_BACKUP_DATA_MODULE_OS,
			module: 10,
			submodule: 0,
			product_type: 2
		},
	];

	let OS_TYPE = ['',
		'Mac OS',
		'Windows',
		'Linux'];
	let OS_TYPE_INDEX = {
		'Mac OS': 1,
		'Windows': 2,
		'Linux': 3
	};

	//加载回调事件函数
	let initListeners = function(){
		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});


		//取消
		$('#cancel').on('click', function(){
			let url = "./content/platform/dataverification/appgroup.php";
			LOCATION(url);
		});

		//添加应用组确认
		$('#addsubmit').on('click', function(){
			addSubmit();
		});

		//修改应用组确认
		$('#editsubmit').on('click', function(){
			editSubmit();
		});

		//选择时间点确认
		$('#select_point_submit').on('click', function(){
			selectPointSubmit();
		});


		//初始化排序
        sortObj = new Sortable($('#objectList').get(0), {
            animation: 150,
            swap: true, // Enable swap plugin
            swapClass: 'highlight', // The class applied to the hovered swap item
        });

		$('#pointType').on('change', function(){
			let value = $(this).val();
			switch (parseInt(value)){
				case 2:
					$('.pointTableDiv').hide();
					$('.cdptimeDiv').hide();
					break;
				case 3:
					//如果是整机实时
					if(currentModule == CONF.MODULE_TYPE.VOL_CDP){
						$('.cdptimeDiv').show();
						$('.pointTableDiv').hide();
					}else{
						initPointTable(timepointParams);
						$('.cdptimeDiv').hide();
						$('.pointTableDiv').show();
					}

					//GMP屏蔽最大验证数量显示，只支持选中一个点
					if($('#oem_version').val() == "vdms"){
						$('.pointNumDiv').hide();
					}
					break;
			}

		});

		// 监听过滤器组件派发的数据，以更新数据
		window.$on('verify_filter_btn-updateFilterEvent', (filterData) => {
			selectModule = [];
			for (var i=0;i<filterData.length;i++){
				selectModule = $.merge(selectModule, filterData[i].value);
			}
			getObjectInfo();
		});
	}

	//确认选择时间点
	let selectPointSubmit = function(){
		let pointType = parseInt($('#pointType').val());
		let liID = clearString("object_tree" + $('#object_uuid').val() + '_' +$('#src_task_uuid').val());
		$('.select_point_' +liID).html( $('#pointType').find('option:selected').text());
		$('.select_point_' +liID).attr('data-pointType', pointType);
		if(pointType != 3){
			//选择最新时间点 或全部时间点
			$('.select_point_' +liID).attr('data-type', 1);
			$('.select_point_' +liID).attr('data-uuid', "");
			initDriver(liID, nodeMap[liID], pointType);
		}else{
			//如果是整机实时
			if(currentModule == CONF.MODULE_TYPE.VOL_CDP){
				let timepoint = $('.selecttimepoint').val();
				let timepointuuid = $('#cdptimerange').find('option:selected').attr('time_point_uuid');
				$('.select_point_' +liID).attr('data-uuid', timepointuuid);
				$('.select_point_' +liID).attr('data-timepoint', timepoint);
				$('.select_point_' +liID).html(timepoint);
				initDriver(liID, nodeMap[liID], pointType);
			}else{
				//指定时间点
				let selectPoint = $('#point_table').bootstrapTable('getSelections');
				if (selectPoint.length == 0){
					UIToastr.showInfo(LANG.UI_VERIFY_ADD_APPGROUP,LANG.UI_VERIFY_SELECT_TIMEPOINT_TIPS);
					return false;
				}
				let list = [];
				for (let i=0; i<selectPoint.length;i++){
					let info = {
						timepoint_uuid: selectPoint[i].timepoint_uuid,
						timepoint: selectPoint[i].timepoint,
						backup_mode: selectPoint[i].backup_mode
					}
					list.push(info);
				}
				pointList[$('#object_uuid').val() + '_' +  $('#src_task_uuid').val()] = list;
				let des = "";
				for(let i=0;i<selectPoint.length;i++){
					des += selectPoint[i].timepoint + '('+ selectPoint[i].backup_mode_des +')' +'<br>';
				}
				$('.select_point_' +liID).attr('data-type', selectPoint[0].task_type);
				$('.select_point_' +liID).attr('data-uuid', selectPoint[0].timepoint_uuid);
				$('.select_point_' +liID).html(des);
				$('.select_point_' +liID).attr('data-timepoint', selectPoint[0].timepoint);
				$('.select_point_' +liID).attr('data-modeDes', selectPoint[0].backup_mode_des);
				initDriver(liID, nodeMap[liID], pointType, selectPoint);
			}
		}
		$('#select_point_drawer').drawer('hide');
	}

	let getObjectInfo = function(){
		let params = {};
		params.storage_uuid = $('#storagetypeselect').val();
		params.appgroup_uuid = $('#appgroup_uuid').val();
		params.edit_flag = editFlag;
		params.order = 'desc';
		params.sort = 'bt.create_time';
		params.module_list = selectModule;
		$('#objectList').empty();
		pAjaxRequest(params, "/api/v1/verification/select_object_list", "GET", function (result) {
			let data = result.data.rows;
			initObjectTree(data);
		});

	}

	//初始化对象树
	let initObjectTree = function(nodes){
		if(nodes.length == 0){
			$('.treeDiv').hide();
			$('#noModule').show();
			return;
		}else{
			$('.treeDiv').show();
			$('#noModule').hide();
		}
		let setting = {
			check: {
				enable: true,
				nocheckInherit: false
			},
			data: {
				simpleData: {
					enable: true
				},
				key: {
					title: "title"
				}
			},
			view: {
				fontCss: getFontCss,
			},
			callback: {
				onCheck: objectOnCheck,
			}
		};

		let zNodes = [];//拿到备份节点
		let taskList = [];
		let oldInfo = [];
		let oldTaskList = [];
		if(editFlag){
			for (let i =0;i<data.object_list.length;i++){
				oldInfo.push(data.object_list[i].general_config.task_uuid + data.object_list[i].general_config.item_uuid);
				if($.inArray(data.object_list[i].general_config.task_uuid, oldTaskList) == -1){
					oldTaskList.push(data.object_list[i].general_config.task_uuid);
				}
			}
		}
		for(let i=0; i<nodes.length; i++){
			//添加任务节点
			if($.inArray(nodes[i].task_uuid, taskList) == -1){
				let moduleDes = getModuleDes(nodes[i].module_type, nodes[i].sub_module_type);
				let taskDes = CONF.TASK_TYPE_DES[nodes[i].task_type]; //获取任务类型描述
				let info1 = {
					pId: 0,
					id: nodes[i].task_uuid,
					name: nodes[i].task_name + '(' + moduleDes + taskDes + ')',
					type: 1,
					module_type: nodes[i].module_type,
					sub_module_type: nodes[i].sub_module_type,
					chkDisabled: false,
					eventtype: "task",
					task_uuid: nodes[i].task_uuid,
					title: nodes[i].task_name + '(' + moduleDes + taskDes + ')',
					icon: "./img/platform/task.png"
				};
				let ID = nodes[i]['task_uuid'] + nodes[i]['object_uuid'];
				if($.inArray(nodes[i].task_uuid, oldTaskList) != -1){
					info1.checked = true;
					info1.open = true;
				}
				zNodes.push(info1);
				taskList.push(nodes[i].task_uuid);

			}

			//添加对象节点
			let info2 = {
				pId: nodes[i].task_uuid,
				id: nodes[i].object_uuid + '_' +nodes[i].task_uuid,
				name: nodes[i].object_name,
				type: 2,
				module_type: nodes[i].module_type,
				submodule_type: nodes[i].sub_module_type,
				chkDisabled: false,
				eventtype: "object",
				object_uuid: nodes[i].object_uuid,
				task_uuid: nodes[i].task_uuid,
				task_name: nodes[i].task_name,
				title: nodes[i].title,
				integrity_check_flag: nodes[i].integrity_check_flag,
				parent_uuid: nodes[i].parent_uuid,
				host_uuid: nodes[i].host_uuid,
				icon: "./img/platform/host.png"
			};
			let ID = nodes[i]['task_uuid'] + nodes[i]['object_uuid'];
			if($.inArray(ID, oldInfo) != -1){
				info2.checked = true;
			}

			zNodes.push(info2);
		}
		ztree = $.fn.zTree.init($("#object_tree"), setting, zNodes);//第一种
		for(let i=0;i<zNodes.length;i++){
			if(zNodes[i].checked && zNodes[i].eventtype == "object"){
				for (let j=0;j<data.object_list.length;j++){
					if(data.object_list[j].general_config.item_uuid == zNodes[i].object_uuid && data.object_list[j].general_config.task_uuid == zNodes[i].task_uuid){
						let checkNode = ztree.getNodesByParam("id", zNodes[i].id, null);
						initObjectConfig('object_tree', checkNode[0], true, data.object_list[j]);
					}
				}
			}
		}

	}

	//获取模块类型描述
	let getModuleDes = function(module, submodule){
		let des = "";
		for (let j=0;j<moduleList.length;j++){
			if(moduleList[j].module == module && moduleList[j].submodule == submodule){
				des += moduleList[j].name;
			}
		}
		return des;
	}

	//获取字体样式
	let getFontCss = function(treeId, treeNode) {
		let css = {color:"#333", "font-weight":"normal"};
		if(!!treeNode.highlight){
			//搜索使用的样式
			css = {color:"#A60000", "font-weight":"bold"};
		}
		return css;
	}

    //替换特殊字符
    let clearString = function (s){
        let rs = "";
        for (let i = 0; i < s.length; i++) {
            rs = rs+s.substr(i, 1).replace(_VMNAMEREG, '_');
        }
        return rs;
    }

    //转义字符串
    let escapeJquery = function(srcString){
        // 转义之后的结果
        let escapseResult = srcString.toString();
        // javascript正则表达式中的特殊字符
        let jsSpecialChars = ["\\", "^", "$", "*", "?", ".", "+", "(", ")", "[",
            "]", "|", "{", "}"];
        // jquery中的特殊字符,不是正则表达式中的特殊字符
        let jquerySpecialChars = ["~", "`", "@", "#", "%", "&", "=", "'", "\"",
            ":", ";", "<", ">", ",", "/"];
        for (let i = 0; i < jsSpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp("\\"
                + jsSpecialChars[i], "g"), "\\"
                + jsSpecialChars[i]);
        }
        for (let i = 0; i < jquerySpecialChars.length; i++) {
            escapseResult = escapseResult.replace(new RegExp(jquerySpecialChars[i],
                "g"), "\\" + jquerySpecialChars[i]);
        }
        return escapseResult;
    }

    //勾选节点
	let objectOnCheck = function(e, id, node){
		var checkNodes  = ztree.getCheckedNodes();
		if(checkNodes.length > 0){
			$('.objectDiv').show();
		}else{
			$('.objectDiv').hide();
			$('#driverCheck_vm').hide();
		}
		if(node.eventtype == "task"){
			let children = node.children;
			for(var i=0;i<children.length;i++){
				initObjectConfig(id,children[i],node.checked, {});
			}
		}else{
			initObjectConfig(id, node, node.checked, {});
		}

	}


	//获取唯一标识
	let getUuid = function() {
		let len = 36;//36长度
		let radix = 16;//16进制
		let chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
		let uuid = [], i;
		radix = radix || chars.length;
		if(len) {
			for(i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
		} else {
			let r;
			uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
			uuid[14] = '4';
			for(i = 0; i < 36; i++) {
				if(!uuid[i]) {
					r = 0 | Math.random() * 16;
					uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
				}
			}
		}
		return uuid.join('');
	}

	//初始化整机对象时间点配置信息
	var initObjectConfig = function(id,node,checkFlag, config){
		if (!checkFlag){
			addObjectList(id,node,checkFlag, {});
			return;
		};
		var p = {};
		p.object_uuid = node.object_uuid;
		p.task_uuid = node.task_uuid;
		p.module_type = node.module_type;
		p.sub_module_type = node.sub_module_type;
		pAjaxRequest(p, "/api/v1/verification/get_timepoint", "GET", function (d) {
			var timepointInfo = d.data;
			if (timepointInfo.length !=0){
				var p = {};
				p.timepoint_uuid = timepointInfo.timepoint_uuid
				if (node.module_type == 10){
					p.timepoint_uuid = timepointInfo.new_timepoint_uuid
				}
				p.agent_uuid = node.object_uuid;
				if(node.module_type != 5 &&node.module_type != 10){
					p.agent_uuid = "";
				}

				//非整机模块处理
				if($.inArray(node.module_type, [2,5,10]) == -1){
					if (editFlag && config.general_config){
						config.general_config.os_type = OS_TYPE_INDEX[config.general_config.os_type];
						addObjectList(id,node,checkFlag, config);
					}else {
						let info = {
							general_config: {},
							network_config: [],
							verify_config: {}
						}
						addObjectList(id,node,checkFlag, info);
					}
					return;
				}
				pAjaxRequest(p, "/api/v1/recovery/timepoint_config", "GET", function (d) {
					if (editFlag && config.general_config){
						config.general_config.os_type = OS_TYPE_INDEX[config.general_config.os_type];
						addObjectList(id,node,checkFlag, config);
					}else{
						if(!d.success) {
							UIToastr.showWarning(LANG.UI_VERIFY_GET_OBJECT_SETTINGS_TIPS,d.message);
							return;
						}
						//加载默认选项
						let settings = {};
						settings.general_config = {
							'cpu_arch' : d.data['cpu_arch'],
							'core_num' : d.data['cpu_core'],
							'cpu_num' : d.data['cpu_socket'],
							'memory_size' : d.data['memory_size'],
							'memory_size_int' : d.data['memory_size_int'],
							'memory_size_unit' : d.data['memory_size_unit'],
							'os_type' : d.data['os_type'],
							'os_version' : d.data['os_version']

						}
						node.timepointInfo = timepointInfo;
						hostSettings = settings.general_config;
						let networkList = []
						if(d.data.network_list){
							for (let i=0;i<d.data.network_list.length;i++){
								let info = {};
								info.network_name = d.data.network_list[i].network_name;
								let src_adapter_list = d.data.network_list[i].src_adapter_list;
								if(src_adapter_list){
									for (let j=0;j<src_adapter_list.length;j++){
										if (src_adapter_list[j].ip_protocol == 1){
											info.gateway = src_adapter_list[j].gateway;
											info.ip = src_adapter_list[j].ip_list[0].ip_addr;
											info.netmask = src_adapter_list[j].ip_list[0].netmask;
											info.mac_address = d.data.network_list[i].mac_address;
										}
									}
								}else{
									info.ip = "";
									info.netmask = "";
									info.gateway = "";
									info.mac_address = "";
								}
								networkList.push(info);
							}
						}
						settings.network_config = networkList;
						settings.verify_config = {};
						addObjectList(id,node,checkFlag, settings);
					}
				});
			}else{
				if (node.module_type == 5){
					//有代理整机调用接口获取原配置
					let p = {};
					p.agent_uuid = node.object_uuid;
					pAjaxRequest(p, "/api/v1/complete_machine_os/get_client_info", "GET", function (d) {
						//加载默认选项
						let settings = {};
						settings.general_config = {
							'cpu_arch' : d.data['cpu_arch'],
							'core_num' : d.data['cpu_core'],
							'cpu_num' : d.data['cpu_socket'],
							'memory_size' : d.data['mem_size'],
							'memory_size_int' : d.data['memory_size_int'],
							'memory_size_unit' : d.data['memory_size_unit'],
							'os_type' : d.data['os_type'],
							'os_version' : d.data['os_version']

						}
						hostSettings = settings.general_config;
						if (editFlag && config.general_config){
							config.general_config.os_type = OS_TYPE_INDEX[config.general_config.os_type];
							addObjectList(id,node,checkFlag, config);
						}else{
							let networkList = [];
							let network = d.data.nic_list;
							if(network){
								for (let i=0;i<network.length;i++){
									let info = {};
									info.network_name = network[i].name;
									info.gateway = network[i].gateway_address;
									info.ip = network[i].ip_set[0].ip_addr;
									info.netmask = network[i].ip_set[0].netmask;
									info.mac_address = network[i].mac_address;
									networkList.push(info);
								}
							}
							settings.network_config = networkList;
							settings.verify_config = {};
							settings.safe_config = {};
							settings.driver_config = {};
							settings.file_compare_config = {};
							addObjectList(id,node,checkFlag, settings);
						}
					});
					return;
				}else{
					let info = {
						general_config: {},
						network_config: [],
						verify_config: {},
						safe_config: {},
						driver_config: {},
						file_compare_config: {}
					}
					addObjectList(id,node,checkFlag, info);
					return;
				}
			}
		});
	}


	//勾选树节点添加右侧虚拟机列表
	let addObjectList = function(id,node,checkFlag, config){
		let liID = clearString(id + node.id);
		if(checkFlag){
			//初始化整机对象配置信息
			let info = "";
			let validate = "value=value.replace(/[^\d]/g,'')";
			let authType = $('#authselect').val();
			let vt_flag = $('#nodeselect').find('option:selected').attr('data-vt');
			let proper = LANG.UI_VM_MACHINE_VT_CPU_CUSTOM + '</br>';
			let displayHide = "";
			let hostflag = "";
			let gmpDisplay = "";
			let ping_test_flag = config.verify_config.ping_test_flag == false ? "" : "checked";
			let heartbeat_flag = config.verify_config.heartbeat_flag == false ? "" : "checked";
			let print_screen_flag = config.verify_config.print_screen_flag == false ? "" : "checked";
			let integrity_check_flag = config.verify_config.integrity_check_flag == false ?  "" : "checked";
			let max_boot_time = config.verify_config.max_boot_time ? config.verify_config.max_boot_time : "00:15:00";
			let max_ping_wait_time = config.verify_config.max_ping_wait_time ? config.verify_config.max_ping_wait_time : "00:01:00";
			//获取验证对象时间点信息
			let timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-type="1" data-pointType="2" data-uuid="">'+LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST+'</a>';
			if (node.timepointInfo){
				timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-type="1" data-pointType="2" data-uuid="'+node.timepointInfo.timepoint_uuid+'">'+LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST + node.timepointInfo.timepoint + '(' + node.timepointInfo.backup_mode_des  + ')'+'</a>';
				if(node.module_type == CONF.MODULE_TYPE.VOL_CDP){
					timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-system="'+node.timepointInfo.is_system_disk_flag+'" data-type="1" data-pointType="2" data-uuid="'+node.timepointInfo.new_timepoint_uuid+'" data-timepoint="'+node.timepointInfo.timepoint+'">'+LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST + node.timepointInfo.timepoint+'</a>';
				}
			}
			//修改任务获取时间点信息
			if (editFlag && config.verify_config.timepoint_uuid_list){
				let timepointInfo = config.verify_config.timepoint_uuid_list;
				if (timepointInfo.timepoint_range ==1){
					//选择所有验证点
					timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-type="1" data-pointType="1" data-uuid="" data-maxnum="'+timepointInfo.max_timepoint_verify+'">'+LANG.UI_VERIFY_SELECT_TIMEPOINT_ALL+'</a>';
				}else if (timepointInfo.timepoint_range ==3){
					let list = timepointInfo.timepoint_uuids;
					let des = "";
					for (var i=0;i<list.length;i++){
						des += list[i].timepoint + '('+ backupModeDes[list[i].backup_mode] +')' +'<br>';
					}
					timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-modeDes="'+backupModeDes[list[0].backup_mode]+'" data-pointType="3" data-timepoint="'+list[0].timepoint+'" data-type="'+list[0].backup_mode+'" data-uuid="'+list[0].timepoint_uuid+'" >'+des+'</a>';
					pointList[node.id] = list;
					if (node.module_type == CONF.MODULE_TYPE.VOL_CDP){
						timepointDes = '<a class="select_point_'+liID+'" href="javascript:;" data-pointType="3" data-timepoint="'+list[0].timepoint+'" data-type="1" data-uuid="'+list[0].timepoint_uuid+'" >'+list[0].timepoint+'</a>';
						$('.selecttimepoint').val(timepointInfo.vol_cdp_datetime);
					}
				}
			}

			networkList[liID] = [];
			if ($('#oem_version').val() == "vdms" ){
				gmpDisplay = "display-hide";
				displayHide = "";
			}

			//根据cpu模式显示描述
			if (vt_flag == 1) {
				// 开启 custom/ host_passthrough
				proper += LANG.UI_VM_MACHINE_VT_CPU_PASSTHROUGH;
			} else {
				proper += LANG.UI_VM_MACHINE_VT_CPU_MODEL;
			}
			proper += '</br>' + LANG.UI_VERIFY_CPU_MODE_TIPS;
			//head
			info +=
				'<li class="panel panel-default mb10" id="object'+liID+'">' +
				'<div class="panel-heading">' +
				'<h4 class="panel-title">' +
				'<a class="accordion-toggle accordion-toggle-styled collapsed popovers" data-container="body" data-trigger="hover" data-placement="top" data-toggle="collapse" href="#config'+liID+'" aria-expanded="true" data-parent="#objectList">'+
				'<span class="font-green-seagreen">'+node.name+'</span>' +
				'</a>' +
				'</h4>' +
				'</div>';
			//content
			info +=
				'<div class="panel-collapse collapse" id="config'+liID+'">' +
				'<div class="panel-body" style="padding: 16px"><div class="tabbable-custom ">' +
				'<ul class="nav nav-tabs ">';
			//整机相关模块支持基本信息修改
			info += '<li class="">' +
				'<a href="#common_tab'+liID+'" data-toggle="tab" aria-expanded="false">'+LANG.UI_VM_SETTING_V2_GENERAL+'</a>' +
				'</li>';
			info +=	'<li class="active">' +
				'<a href="#verify_tab'+liID+'" data-toggle="tab" aria-expanded="false">'+ LANG.UI_VERIFY_VERIFY_CONFIG +'</a>' +
				'</li>';
			info += '<li class="">' +
				'<a href="#network_tab'+liID+'" data-toggle="tab" aria-expanded="false">'+ LANG.UI_VERIFY_NETWORK_CONFIG +'</a>' +
				'</li>';

			info += '</ul><div class="tab-content" style="height:400px;overflow-y: auto;">';
			//通用配置
			info +=
				'<div class="tab-pane " id="common_tab'+liID+'"><div class="row">' +
				'<div class="form-group">' +
				'<label class="control-label col-md-4">'+LANG.UI_VERIFY_SELECT_TIMEPOINT+'</label>' +
				'<div class="col-md-8 pt7">' +
				timepointDes +
				'</div>' +
				'</div>' +
				'<div class="form-group display-hide"><label class="control-label col-md-4">'+ LANG.UI_VERIFY_CPU_MODE +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="cpumode">' + getCpuMode(config.cpu_mode) + '</select>' +

				'</div>' +
				'<a style="position:absolute;left:0;top:5px;" class="configTips ml10" data-toggle="tooltip" data-html="true" data-placement="right" title="'+proper+'">' +
				'<i class="viconfont vicon-tishi"></i>' +
				'</a>' +
				'</div>' +
				'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VM_SETTING_CPU_ARCH +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="cpuArch">' + getCpuArchOption(config.general_config.cpu_arch) + '</select></div></div>' +
				'<div class="form-group"><label class="control-label col-md-4">'+LANG.UI_VERIFY_CPU_NUM+'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="cpunum">' + getSocketAndCoresOption(config.general_config.cpu_num, hostSettings.cpu_num) + '</select></div></div>' +
				'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+LANG.UI_VERIFY_CPU_EVERY_CORE+'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="corenum">' + getSocketAndCoresOption(config.general_config.core_num, hostSettings.core_num) + '</select></div></div>' +
				'<div class="form-group"><label class="control-label col-md-4">'+LANG.UI_VERIFY_MEMORY_SIZE+'</label><div class="col-md-6"><div style="display:inline-flex;width:100%;"><select class="form-control select2me input-sm" name="memory">' + getSocketAndCoresOption(config.general_config.memory_size_int, hostSettings.memory_size_int, true) + '</select><select class="form-control select2me input-sm" name="unit" style="margin-left:5px;width:65px;">' + getMomoryUnit(config.general_config.memory_size_unit) + '</select></div></div></div>' +
				'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VM_OS_TYPE +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="ostype">' + getOsOption(config.general_config.os_type, config.general_config.cpu_arch) + '</select></div></div>' +
				'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VM_OS_VERSION +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="osversion">' + getOSVersionOption(config.general_config.os_version, config.general_config.os_type, config.general_config.cpu_arch) + '</select></div></div>' +
				'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VERIFY_OBJECT_DISK_TARGET_BUS +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="disk_target_bus">' + getDiskTypeOption(config.general_config.disk_target_bus) + '</select></div></div>' +
				'<div class="form-group"><label class="control-label col-md-4" style="word-break: normal;">'+ LANG.UI_VERIFY_OBJECT_NETCARD_TARGET_BUS +'</label><div class="col-md-6"><select class="form-control select2me input-sm" name="netcard_target_bus">' + getNetworkTypeOption(config.general_config.netcard_target_bus) + '</select></div></div>';


			info += '</div></div>';

			//验证配置
			info +=
				'<div class="tab-pane active" id="verify_tab'+liID+'">' +
				'<div class="row">' +
				'<div class="form-group '+displayHide+gmpDisplay+ '">' +
				'<label class="control-label col-md-4">'+LANG.UI_VERIFY_PING_TEST+'</label>' +
				'<div class="col-md-8 form-group-top4-label">' +
				'<input type="checkbox" class="make-switch configCheck pingCheck" '+ping_test_flag+' data-on-color="primary" data-off-color="info" data-size="small" data-on-text="" data-off-text="">' +
				'<a class="ml10 configTips" data-toggle="tooltip" data-placement="right" title="'+LANG.UI_VERIFY_PING_TIPS+'">' +
				'<i class="viconfont vicon-tishi"></i>' +
				'</a>' +
				'</div>' +
				'</div>' +
				'<div class="form-group '+ displayHide + gmpDisplay +'">' +
				'<label class="control-label col-md-4">'+LANG.UI_VERIFY_HEARTBEAT+'</label>' +
				'<div class="col-md-8 form-group-top4-label">' +
				'<input type="checkbox" class="make-switch configCheck heartCheck" '+heartbeat_flag+' data-on-color="primary" data-off-color="info" data-size="small"' + 'data-on-text="" data-off-text="">' +
				'<a class="ml10 configTips" data-toggle="tooltip" data-placement="right" title="'+LANG.UI_VERIFY_HEARTBEAT_TIPS+'">' +
				'<i class="viconfont vicon-tishi"></i>' +
				'</a>' +
				'</div>' +
				'</div>' +
				'<div class="form-group '+displayHide+'">' +
				'<label class="control-label col-md-4">'+LANG.UI_VERIFY_SCREEN+'</label>' +
				'<div class="col-md-8 form-group-top4-label">' +
				'<input type="checkbox" class="make-switch configCheck screenCheck" '+print_screen_flag+' data-on-color="primary" data-off-color="info" data-size="small" data-on-text="" data-off-text="">' +
				'<a class="ml10 configTips" data-toggle="tooltip" data-placement="right" title="'+LANG.UI_VERIFY_SCREEN_TIPS+'">' +
				'<i class="viconfont vicon-tishi"></i>' +
				'</a>' +
				'</div>' +
				'</div>' +
				'<div class="form-group '+ hostflag + gmpDisplay + '" id="virusConfig_'+ liID +'" >' +
				'</div>';
			if(node.integrity_check_flag == 1){
				info+='<div class="form-group '+ gmpDisplay + '">' +
					'<label class="control-label col-md-4">'+LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK+'</label>' +
					'<div class="col-md-8 form-group-top4-label">' +
					'<input type="checkbox" class="make-switch configCheck integrity_check_flag" '+integrity_check_flag+' data-on-color="primary" data-off-color="info" data-size="small"' + 'data-on-text="" data-off-text="">' +
					'<a class="ml10 configTips" data-toggle="tooltip" data-placement="right" title="'+LANG.UI_SAFE_STRATEGY_BACKUP_POINT_CHECK+'">' +
					'<i class="viconfont vicon-tishi"></i>' +
					'</a>' +
					'</div>' +
					'</div>';
			}

			//开机上线检测时间
			info += '<div class="form-group '+displayHide+'">' +
				'<label class="control-label col-md-4">'+LANG.UI_VERIFY_OBJECT_PING_WAIT_TIME+'</label>' +
				'<div class="col-md-6 form-group-content" style="height:revert;line-height: revert;">' +
				'<div class="input-group">' +
				'<input type="text" value="'+max_ping_wait_time+'" name="max_ping_wait_time"  class="form-control timepicker timepicker-24"><span class="input-group-btn">' +
				'<button class="btn default btn-time" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button></span>'+
				'</div>' +
				'<div>' +
				'<span class="help-block ">' + LANG.UI_VERIFY_OBJECT_PING_WAIT_TIME_TIPS + '</span>' +
				'</div></div></div>';
			//最大开机等待时间
			info += '<div class="form-group '+displayHide+'">' +
				'<label class="control-label col-md-4">'+LANG.UI_VERIFY_EFFECTIVE_TIME+'</label>' +
				'<div class="col-md-6 form-group-content" style="height:revert;line-height: revert;">' +
				'<div class="input-group">' +
				'<input type="text" value="'+max_boot_time+'" name="boottime"  class="form-control timepicker timepicker-24"><span class="input-group-btn">' +
				'<button class="btn default btn-time" type="button"><i class="viconfont vicon-beifenshijiandian"></i></button></span>'+
				'</div>' +
				'<div>' +
				'<span class="help-block ">' + LANG.UI_VERIFY_EFFECTIVE_TIME_TIPS + '</span>' +
				'</div></div></div>';
			// if ($('#oem_version').val() == "vdms" && node.module_type == 5 ){
			// 	//文件对比分析配置
			// 	info +=
			// 		'<div class="form-group">' +
			// 		'<label class="control-label col-md-4">'+LANG.UI_VERIFY_FILE_COMPARE_TYPE+'</label>' +
			// 		'<div class="col-md-4 form-group-top4-label">' +
			// 		'<select class="form-control select2me input-sm compare_mode" ><option value="1">MD5</option></select>' +
			// 		'</div>' +
			// 		'</div>' +
			// 		'<div class="form-group fileTreeDiv">' +
			// 		'<label class="control-label col-md-4">'+LANG.UI_FILE_SELECT_FILE_AND_DIR+'</label>' +
			// 		'<div class="col-md-8 form-group-top4-label">' +
			// 		'<ul class="ztree allFileTree" id="'+liID+'"></ul>' +
			// 		'</div>' +
			// 		'</div>';
			//
			// }

			info +=
				'</div>' +
				'</div>';
			//网络配置
			info +=
				'<div class="tab-pane pd10" id="network_tab'+liID+'">' +
				'<div class="row">' +
				'<button type="button"  class="btn btn-sm green-haze data-resource2 mb10 addNetwork"><i class="viconfont vicon-ge_add_task"></i>'+LANG.UI_VERIFY_ADD_NETWORK+'</button>' +
				'<ul class="feeds accordion addNetworkDiv"></ul>' +
				'</div></div>';




			//foot
			info += '</div></div></div></div></li>';

			$('#objectList').append(info);
			//初始化病毒扫描策略
			if (editFlag && config.verify_config.virus_scan_config_list){
				$('#virusConfig_'+liID).virusDetectionBackup('col-md-4', 1 == config.verify_config.vir_det_kill_flag, config.verify_config.virus_scan_config_list[0].interrupt_policy, config.verify_config.virus_scan_config_list[0].all_timepoints_flag, config.verify_config.virus_scan_config_list[0]);
			}else{
				$('#virusConfig_'+liID).virusDetectionBackup('col-md-4', false, true, false);
			}

			//屏蔽扫描所有未扫描备份点
			$('#virusConfig_'+liID + ' .virusBackupSelect .virus-item').eq(1).hide();

			for (var i=0;i<config.network_config.length;i++){
				initNetwork(config.network_config[i], liID);
			}

			//初始化文件树
			if ($('#oem_version').val() == "vdms" && node.module_type == 5){
				initFileTree(node, liID, config);

			}
			//初始化icheck
			$('.addIt-list .icheck').iCheck({
				checkboxClass: 'icheckbox_square-blue',
				radioClass: 'iradio_square-blue strategy-radio-custom',
				increaseArea: '20%' // optional
			});


			$('input[name=max_ping_wait_time]').timepicker({
				autoclose: true,
				minuteStep: 5,
				showSeconds: true,
				showMeridian: false,
			});

			$('input[name=max_ping_wait_time]').parent('.input-group').on('click', '.input-group-btn', function(e){
				e.preventDefault();
				$(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
			});

			$('input[name=boottime]').timepicker({
				autoclose: true,
				minuteStep: 5,
				showSeconds: true,
				showMeridian: false,
//              defaultTime:'00:00:00'
			});

			$('input[name=boottime]').parent('.input-group').on('click', '.input-group-btn', function(e){
				e.preventDefault();
				$(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
			});

			$('#object' + escapeJquery(liID) + ' .select_point_' + liID).on('click', function(){
				$('.pointTableDiv').hide();
				$('#object_name').html(node.name);
				$('#object_uuid').val(node.object_uuid);
				$('#src_task_uuid').val(node.task_uuid);
				currentModule = node.module_type;
				let name = LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST;
				if (node.timepointInfo){
					name = LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST+ node.timepointInfo.timepoint + '(' + node.timepointInfo.backup_mode_des  + ')';
					if(node.module_type == CONF.MODULE_TYPE.VOL_CDP){
						name = LANG.UI_VERIFY_SELECT_TIMEPOINT_NEWEST+ node.timepointInfo
							.timepoint;
					}
				}
				let option = ` <option value="2">`+name +`</option>
                                <option value="3">`+LANG.UI_VERIFY_SELECT_TIMEPOINT_SELECT+`</option>`;
				$('#pointType').empty().html(option);
				$('#select_point_drawer').drawer('show');
				let params = {};
				params.item_uuid = [node.object_uuid];
				params.task_uuid = node.task_uuid;
				params.taskGridFlag = true;
				params.module_type = node.module_type;
				params.sub_module_type = node.submodule_type;
				params.storage_uuid = [$('#storagetypeselect').val()];
				timepointParams = params;
				if(node.module_type == CONF.MODULE_TYPE.VOL_CDP){
					$('.selecttimepoint').val(node.timepointInfo.timepoint);
					cdpInfo = {
						'node_uuid': $('#storagetypeselect').find('option:selected').attr('nodeuuid'),
						'task_uuid': node.task_uuid,
						'host_uuid': node.object_uuid,
					}
					getBackupSetRange(cdpInfo);
				}
			});

			$('#object' + escapeJquery(liID) + ' .vir_det_kill_flag').on('switchChange.bootstrapSwitch', function(){
				if(this.checked){
					$('#object' + escapeJquery(liID) + " .virusSelect").show();
				}else{
					$('#object' + escapeJquery(liID) + " .virusSelect").hide();
				}
			});

			$('#object' + escapeJquery(liID) + " .configTips").tooltip();	   //初始化tips
			$('#object' + escapeJquery(liID) + " .configCheck").bootstrapSwitch();
			$('#object' + escapeJquery(liID) + " .keyupInput").off().keyup(function(){
				let value = $(this).val().replace(/[^\d]/g,'');
				$(this).val(value);
			});

			$('#object' + escapeJquery(liID) + " .pingCheck").on('switchChange.bootstrapSwitch', function(){
				if(this.checked){
					$('#object' + escapeJquery(liID) + " .ipDiv").show();
				}else{
					$('#object' + escapeJquery(liID) + " .ipDiv").hide();
				}
			});


			$('#object' + escapeJquery(liID) + " .addNetwork").on('click', function(){
				initNetwork({}, liID);
			});

			$('#object' + escapeJquery(liID) + " .heartCheck").on('switchChange.bootstrapSwitch', function(){
				if(this.checked){
					$('#object' + escapeJquery(liID) + " .pingCheck").bootstrapSwitch('disabled', false);
				}else{
					$('#object' + escapeJquery(liID) + " .pingCheck").bootstrapSwitch('state', true);
					$('#object' + escapeJquery(liID) + " .pingCheck").bootstrapSwitch('disabled', true);
				}
			});
			$('#object' + escapeJquery(liID) + " .pingCheck").on('switchChange.bootstrapSwitch', function(){
				if(this.checked){
					$('#object' + escapeJquery(liID) + " .heartCheck").bootstrapSwitch('disabled', false);
				}else{
					$('#object' + escapeJquery(liID) + " .heartCheck").bootstrapSwitch('state', true);
					$('#object' + escapeJquery(liID) + " .heartCheck").bootstrapSwitch('disabled', true);

				}
			});

			$(`#object` + escapeJquery(liID)).find(`select[name=ostype]`).on('change', function () {
				$(`#object` + escapeJquery(liID)).find(`select[name=osversion]`).empty().html(getOSVersionOption("",$(`#object` + escapeJquery(liID)).find(`select[name=ostype]`).val(), $(`#object` + escapeJquery(liID)).find(`select[name=cpuArch]`).val() ));
			});

			// 初始化驱动检测
			initDriver(liID, node, TIMEPOINT_TYPE_LAST);
		}else{
			//移除未选中的虚拟机选项
			$('#object' + escapeJquery(liID)).remove();
			let list = [];
			for (var i=0;i<source_list.length;i++){
				if(node.timepointInfo.timepoint_uuid != source_list[i].timepoint_uuid){
					list.push(source_list[i]);
				}
			}
			source_list = list;


		}
	}

	/**
	 * 获取备份集时间范围
	 */
	var getBackupSetRange = function(cdpInfo){
		var data = {};
		data.node_uuid = cdpInfo.node_uuid;
		data.task_uuid = cdpInfo.task_uuid;
		data.host_uuid = cdpInfo.host_uuid;

		Metronic.blockUI({target: '#appgroupContent',animate: true});
		pAjaxRequest(data, "/api/v1/complete_machine_volcdp/backup_set/time_range", "GET", function (result) {
			Metronic.unblockUI('#appgroupContent');
			var data = result.data;
			var volCdpBackupSetRange = $('#cdptimerange');
			volCdpBackupSetRange.empty();
			for(var i=0; i<data.length; i++){
				var timeRangeText = data[i].start_time + " - "+ data[i].end_time;
				var option = $("<option>").text(timeRangeText).val(data[i].backup_set_id)
					.attr('time_point_uuid', data[i].time_point_uuid)
					.attr('start_time',data[i].start_time)
					.attr('end_time',data[i].end_time)
					.attr('data-system',data[i].is_system_disk_flag);
				volCdpBackupSetRange.append(option);
			}
		});
		$('#cdptimerange').unbind('change').bind('change', function(){
			$('.selecttimepoint').val($(this).find('option:selected').start_time);
		});
	}

	//初始化网络
	let initNetwork = function(info, liID){
		let network = '';
		let collapsed = "collapsed";
		let collapsedIn = "";
		let uuid = getUuid();
		let network_name = LANG.UI_VERIFY_NEW_NETWORK;
		let ip = "", netmask="", gateway = "",macaddress = "";
		let disabled = "disabled";
		if (info && info.network_name){
			network_name = info.network_name;
		}
		if (info.ip){
			ip = info.ip;
		}

		if (info != {} && info.netmask){
			netmask = info.netmask;
		}

		if (info != {} && info.gateway){
			gateway = info.gateway;
		}


		//头部
		network += '<li  class="panel panel-default mb10" style="position: relative;width:90%;" id="network_'+uuid+'"><div class="panel-heading"><h4 class="panel-title"><a class="accordion-toggle accordion-toggle-styled popovers '+collapsed+'"'+
			'data-container="body" data-trigger="hover" data-parent=".addNetworkDiv" data-placement="top" data-toggle="collapse"  href="#network_content_'+uuid+'"  ><span class="font-green-seagreen">'+network_name+'</span></a></h4></div>' +
			'<div class="panel-collapse collapse '+collapsedIn+'" id="network_content_'+uuid+'"><div class="panel-body"><div class="form-group col-md-12 ">';

		//网络配置部分
		network += `<div class="form-group display-hide">
			<label class="control-label col-md-4 iplabel">`+LANG.UI_VIRTUAL_LAB_NETWORK_NAME+`</label>
			<div class="col-md-6">
				<div class="input-icon right">
					<i class="fa"></i>
					<input type="text" maxLength="128" class="form-control" name="networkname" value="`+network_name+`">
				</div>
			</div>
		</div>`;
		network += `<div class="form-group ">
					<label class="control-label col-md-4 iplabel"><span class="required">* </span>`+LANG.UI_PUBLIC_IP_ADDRESS+`</label>
					<div class="col-md-6">
						<div class="input-icon right">
							<i class="fa"></i>
							<input type="text" maxLength="128" class="form-control" name="ipaddress" value="`+ip+`" placeholder="192.168.1.110">
						</div>
					</div>
				</div>`;
		network += `<div class="form-group ">
					<label class="control-label col-md-4 iplabel"><span class="required">* </span>`+LANG.UI_PUBLIC_IP_NETMASK+`</label>
					<div class="col-md-6">
						<div class="input-icon right">
							<i class="fa"></i>
							<input type="text" maxLength="128" class="form-control" name="netmask" value="`+netmask+`" placeholder="255.255.255.0">
						</div>
					</div>
				</div>`;
		network += `<div class="form-group ">
					<label class="control-label col-md-4 iplabel">`+LANG.UI_PUBLIC_IP_GATEWAY+`</label>
					<div class="col-md-6">
						<div class="input-icon right">
							<i class="fa"></i>
							<input type="text" maxLength="128" class="form-control" name="gateway" value="`+gateway+`" placeholder="192.168.1.1">
						</div>
					</div>
				</div>`;
		if(info != {} && info.mac_address){
			disabled = "";
		}
		network += `<div class="form-group ">
				<label class="control-label col-md-4">`+LANG.UI_VM_SETTING_MAC+`</label>
				<div class="col-md-6">
					<select class="form-control select2me input-sm" name="mactype" `+disabled+`>
							<option value="2">`+LANG.UI_VM_SETTING_V2_AUTO_PRODUCT+`</option>
							<option value="1">`+LANG.UI_VM_SETTING_SAVE_MAC+`</option>
						
					</select>
				</div>
			</div>`;
		network += `<div class="form-group macdiv display-hide">
				<div class="col-md-offset-4 col-md-6">
					<div class="input-icon right">
						<i class="fa"></i>
						<input type="text" maxLength="128" disabled class="form-control" name="macaddress" value="`+info.mac_address+`">
					</div>
				</div>
			</div>`;


		//结尾拼接
		network += '</div></div></div>';
		//添加删除按钮
		network += '<button type="button" class="btn viconfont vicon-a-Deleteshanchu1 green-haze b-btn" id="delete_' + uuid + '" style="position: absolute;right: -45px;top:0;"></button>';
		network += '</li>';
		//添加到对应页面
		$('#object' + escapeJquery(liID) + " .addNetworkDiv").append(network);
		networkList[liID].push(uuid);

		$('#object' + escapeJquery(liID) + " .addNetworkDiv #network_"+uuid + " select[name=mactype]").on('change', function(){
			let type = $(this).val();
			if(type == 1){
				$('#object' + escapeJquery(liID) + " .addNetworkDiv #network_"+uuid + " .macdiv").show();
			}else{
				$('#object' + escapeJquery(liID) + " .addNetworkDiv #network_"+uuid + " .macdiv").hide();
			}
		});

		$('#delete_' + uuid).on('click', function(){
			$('#network_'+ uuid).remove();
			//检查已经取消的网络
			for(let i=0;i<networkList[liID].length;i++){
				//网络未选中时移除
				if(uuid == networkList[liID][i]){
					networkList[liID].splice(i,1);
				}
			}

		});
	}



	//通用配置
	let getGeneralConf = function(liID, node){
		let info = {};
		//cpu
		info.cpu_num =  $('#object'+liID + " select[name=cpunum]").val();
		//插槽数
 		info.core_num =  $('#object' +liID+" select[name=corenum]").val();
 		//内存大小
 		info.memory_size = $('#object' +liID+" select[name=memory]").val();
 		//操作系统类型
 		info.os_type = $('#object' +liID+" select[name=ostype]").val();
		//磁盘类型
		info.disk_target_bus = $('#object' +liID+" select[name=disk_target_bus]").val();
		//网卡适配器类型
		info.netcard_target_bus = $('#object' +liID+" select[name=netcard_target_bus]").val();
 		//自动开机标记默认关闭
		info.start_vm_flag = false;
 		//模块类型
		info.module_type = node.module_type;
		info.submodule_type = node.submodule_type;
		//对象uuid
		info.item_uuid = node.object_uuid;
		//对象名称
		info.item_name = node.name;
		info.backup_task_uuid = node.task_uuid;

		return info;
	}

	//验证配置
	let getVerifyConf = function(liID, node){
		let info = {};
		//时间点uuid
		info.timepoint_uuid_list = [$('#vm' + liID+ ' .select_point_' +liID).attr('data-uuid')];
		info.timepoint_uuid = "";
		info.timepoint_task_type = 1;
		//网络验证
		info.ping_test_flag = $('#object' +liID+" .pingCheck").get(0).checked;
		//心跳验证
		info.heartbeat_flag = $('#object' +liID+" .heartCheck").get(0).checked;
		//截屏验证
		info.print_screen_flag = $('#object' +liID+" .screenCheck").get(0).checked;
		//最大开机等待时间
		info.max_boot_time = $('#object' +liID+" input[name=boottime]").val();

		//开机检测等待时间
		info.max_ping_wait_time = $('#object' +liID+" input[name=max_ping_wait_time]").val();

		return info;
	}

	//网络配置
	let getNetworkConf = function(liID, node){
		let list = [];
		let network = networkList[liID];
		for(let i=0; i< network.length; i++){
			let info = {};
			info.network_name = $('#object' + liID + " #network_" + networkList[liID][i] + " input[name=networkname]").val();
			info.ip = $('#object' + liID + " #network_" + networkList[liID][i] + " input[name=ipaddress]").val();
			info.gateway = $('#object' + liID + " #network_" + networkList[liID][i] + " input[name=gateway]").val();
			info.netmask =$('#object' + liID + " #network_" + networkList[liID][i] + " input[name=netmask]").val();
			if($('#object' + liID + " #network_" + networkList[liID][i] + " select[name=mactype]").val() == 1){
				info.mac_address =$('#object' + liID + " #network_" + networkList[liID][i] + " input[name=macaddress]").val();
			}else{
				info.mac_address = "";
			}
			list.push(info);
		}

		return list;

	}

	//获取对象列表信息
	let getObjectListInfo = function (){
		let list = [];
		let driverList = [];
		// 驱动检测结果
		driverList = $.fn.driverCheck.getCheckResult($(`#driverCheck_vm`));
		if (!driverList) {
			UIToastr.showWarning(LANG.UI_VERIFY_ADD_JOB, LANG.UI_VERIFY_DRIVER_CHECK_TIPS);
			return false;
		}else{
			$.each(driverList, function(i,driver){
				if(driverList[i].driver_check_status == 8){
					driverList[i].driver_hw_id_map = "";
				}
			});
		}
		let nodes = ztree.getCheckedNodes(true);
		let networkFlag = true;
		for (let i=0; i< nodes.length; i++){
			//如果不是对象节点直接跳过
			if(nodes[i].eventtype != "object") continue;
			let networkFlag = false;

			//初始化验证对象配置空对象
			let general_config = {},verify_config = {},network_config = [], safe_config = {}, driver_config = {}, file_compare_config = {};

			//处理特殊字符转义
			let liID = clearString('object_tree' + nodes[i].id);

			//通用配置
			general_config.item_uuid = nodes[i].object_uuid;
			general_config.item_name= nodes[i].name;
			general_config.backup_task_uuid = nodes[i].task_uuid;
			general_config.cpu_mode = $('#object'+ liID + ' select[name=cpumode]').val();
			general_config.cpu_arch = $('#object'+ liID + ' select[name=cpuArch]').val();
			general_config.cpu_num = $('#object'+ liID + ' select[name=cpunum]').val();
			general_config.core_num = $('#object'+ liID + ' select[name=corenum]').val();
			general_config.memory_size = $('#object'+ liID + ' select[name=memory]').val();
			general_config.os_type = OS_TYPE[parseInt($('#object'+ liID + ' select[name=ostype]').val())];
			general_config.os_version = parseInt($('#object'+ liID + ' select[name=osversion]').val());
			general_config.os_version_name = $('#object'+ liID + ' select[name=osversion]').find('option:selected').data('name');
			general_config.disk_target_bus = parseInt($('#object'+ liID + ' select[name=disk_target_bus]').val());
			general_config.netcard_target_bus = parseInt($('#object'+ liID + ' select[name=netcard_target_bus]').val());
			general_config.start_vm_flag = false;
			general_config.module_type= nodes[i].module_type;
			general_config.submodule_type= nodes[i].submodule_type;
			general_config.role= 2;
			//检查CPU架构和操作系统类型和版本是否选择
			if(general_config.cpu_arch == "0" || general_config.os_type == "0" || general_config.os_version == "0"){
				UIToastr.showWarning(LANG.UI_VERIFY_ADD_APPGROUP, LANG.UI_VERIFY_OBJECT+"("+nodes[i].name + ")" +LANG.UI_VERIFY_OBJECT_HARDWARE_INFO_SET_TIPS);
				return false;
			}
			//验证配置
			verify_config.timepoint_uuid_list = {};
			verify_config.timepoint_uuid_list.timepoint_uuids = [];
			verify_config.timepoint_range = $('.select_point_' + liID).data('pointtype');
			verify_config.max_timepoint_verify = 1;
			if(verify_config.timepoint_range == 3 && nodes[i].module_type != CONF.MODULE_TYPE.VOL_CDP){
				//指定时间点
				verify_config.timepoint_uuid_list.timepoint_uuids = pointList[nodes[i].id];
			}

			//处理整机实时
			if(nodes[i].module_type == CONF.MODULE_TYPE.VOL_CDP){
				let timepoint = $('.select_point_' + liID).data('timepoint');
				let timepointuuid = $('.select_point_' + liID).data('uuid');
				let systemFlag = $('.select_point_' + liID).data('system');

				let info = {
					timepoint_uuid: timepointuuid,
					timepoint: timepoint,
					backup_mode: 0
				}
				verify_config.timepoint_uuid_list.timepoint_uuids = [info];
				verify_config.vol_cdp_datetime = timepoint;
			}
			verify_config.ping_test_flag = $('#object' +liID+" .pingCheck").get(0).checked;
			verify_config.heartbeat_flag = $('#object' +liID+" .heartCheck").get(0).checked;
			verify_config.print_screen_flag = $('#object' +liID+" .screenCheck").get(0).checked;
			verify_config.max_boot_time = $('#object' +liID+" input[name=boottime]").val();
			verify_config.max_ping_wait_time = $('#object' +liID+" input[name=max_ping_wait_time]").val();


			//网络配置
			network_config = getNetworkConf(liID, nodes[i]);

			//一个对象必须配置一张网卡
			if (network_config.length != 0){
				for (let j=0;j<network_config.length; j++){
					if(network_config[j].ip != "" && network_config[j].netmask != "" && customInputValidate('ipv4',$.trim(network_config[j].ip) ) && netmaskVal.test(network_config[j].netmask)){
						networkFlag = true;
					}
				}
			}

			//检查网卡信息
			if(!networkFlag){
				UIToastr.showWarning(LANG.UI_VERIFY_ADD_APPGROUP, LANG.UI_VERIFY_SET_NETWORK_INFO_TIPS);
				return false;
			}

			//获取病毒扫描参数
			let virusConfig = $('#virusConfig_'+liID).getVirusDetectionBackup();
			if (virusConfig === false) {  // 验证病毒扫描配置内容是否符合要求
				return false;
			}

			//安全配置
			safe_config.vir_det_kill_flag = virusConfig.virus_scan_flag;
			safe_config.virus_scan_config_list = JSON.stringify(virusConfig.virus_scan_config_list);
			if(nodes[i].integrity_check_flag == 1){
				safe_config.integrity_check_flag = $('#object' +liID+" .integrity_check_flag").get(0).checked;
			}else{
				safe_config.integrity_check_flag = false;
			}

			//文件对比验证配置
			if ($('#oem_version').val() == "vdms" && nodes[i].module_type == 5){
				file_compare_config.doc_consistency_flag = $('#object' +liID+" .compare_mode").val();
				let list = [];
				if(zTreeFile[liID]){
					let checkedNodes = zTreeFile[liID].getCheckedNodes();
					checkedNodes = filterFile(checkedNodes);
					for (let j=0;j<checkedNodes.length; j++){
						let fileInfo = {};
						fileInfo.doc_path = checkedNodes[j].filepath;
						fileInfo.encode = checkedNodes[j].code_type;
						fileInfo.path_name =  checkedNodes[j].filepath;
						fileInfo.path_type =  checkedNodes[j].type;
						list.push(fileInfo);
					}
				}
				file_compare_config.doc_list = list;
				threadShowFlag = true;
			}else{
				file_compare_config.doc_consistency_flag = 1;
				file_compare_config.doc_list = [];
			}

			//驱动检测
			for (let j=0; j<driverList.length; j++){
				if(nodes[i].timepointInfo) continue;
				if(driverList[j].timepoint_uuid == nodes[i].timepointInfo.timepoint_uuid){
					driver_config = driverList[j];
					if(driverList[j].driver_check_status == 8){
						dirverCheckFlag = false;
					}
				}
			}
			//整合参数
			let item = {
				general_config: general_config,
				verify_config: verify_config,
				network_config: network_config,
				safe_config: safe_config,
				driver_config: driver_config,
				file_compare_config: file_compare_config
			};

			//添加到列表里
			list.push(item);
		}


		return list;

	}

	//过滤文件
	var filterFile = function (allfileNodes) {
		var allCheckedNode = [];
		//得到所有勾选状态是全选中的节点
		for(var i=0; i<allfileNodes.length; i++){
			//check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
			if(allfileNodes[i].check_Child_State == 2 || allfileNodes[i].check_Child_State == -1) {
				allCheckedNode.push(allfileNodes[i]);
			}
		}
		//过滤掉重复的
		for(var m = 0;m<allCheckedNode.length;m++) {
			if(allCheckedNode[m].type != 1) {//磁盘或文件夹
				for(var n = 0;n<allCheckedNode.length;n++) {
					var str = allCheckedNode[n].pId==null ?'':allCheckedNode[n].pId;
					if(str.includes(allCheckedNode[m].filepath) && allCheckedNode.indexOf(allCheckedNode[n])!=-1) {//判断全选的文件夹下是否还有文件  有则从数组中删除
						allCheckedNode.splice(allCheckedNode.indexOf(allCheckedNode[n]),1);
						n--;
					}
				}
			}
		}
		return allCheckedNode;
	}


	//添加提交
	let addSubmit = function(){
		let data = {};
		data.appgroup_name = $('#appgroupname').val();
		data.node_uuid = $('#storagetypeselect').find('option:selected').attr('nodeuuid');
		data.storage_uuid = $('#storagetypeselect').val();
		data.description = $('#description').val();
		let objectList = getObjectListInfo();
		if(!objectList) return false;
		data.object_list = objectList;
		Metronic.blockUI({target: '#appgroupContent',animate: true,cenrerY: true,});
		pAjaxRequest(data, '/api/v1/verification/app_group', 'POST', (result) => {
			Metronic.unblockUI('#appgroupContent');
			if (result.success) {
				UIToastr.showSuccess(LANG.UI_VERIFY_ADD_APPGROUP, LANG.UI_VERIFY_ADD_APPGROUP+LANG.UI_PUBLIC_SUCCESS);
				let url = "/module/verification/html/appgroup.php";
				LOCATION(url);
			}else{
				UIToastr.showWarning(LANG.UI_VERIFY_ADD_APPGROUP, LANG.UI_VERIFY_ADD_APPGROUP_FAILD);
			}

		});

	}

	//修改提交
	let editSubmit = function(){
		let data = {};
		data.appgroup_uuid = $('#appgroup_uuid').val();
		data.appgroup_name = $('#appgroupname').val();
		data.node_uuid = $('#storagetypeselect').find('option:selected').attr('nodeuuid');
		data.storage_uuid = $('#storagetypeselect').val();
		data.description = $('#description').val();
		let objectList = getObjectListInfo();
		if(!objectList) return false;
		data.object_list = objectList;
		Metronic.blockUI({target: '#appgroupContent',animate: true,cenrerY: true,});
		pAjaxRequest(data, '/api/v1/verification/app_group', 'PUT', (result) => {
			Metronic.unblockUI('#appgroupContent');
			if (result.success) {
				UIToastr.showSuccess(LANG.UI_VERIFY_EDIT_APPGROUP, LANG.UI_VERIFY_EDIT_APPGROUP+LANG.UI_PUBLIC_SUCCESS);
				let url = "/module/verification/html/appgroup.php";
				LOCATION(url);
			}else{
				UIToastr.showWarning(LANG.UI_VERIFY_EDIT_APPGROUP, LANG.UI_VERIFY_EDIT_APPGROUP_FAILD);
			}

		});
	}

	//获取应用组名称
	let getName = function(){
		let params = {};
		pAjaxRequest(params, '/api/v1/verification/app_group/name', 'GET', (result) => {

			if (result.success) {
				$('#appgroupname').val(result.data.name);
			}
		});
	}

	//获取应用组修改信息
	let initOldSettings = function(settings){
		$('#appgroupname').val(settings.appgroup_name);
		$('#description').val(settings.description);
	}

	//初始化时间点表格
	let initPointTable = function(tableParams){
		//表格初始化配置项
		let options = {
			toolbarId: '#vin_point_toolbar',
			buttonsToolbar: '#vin_point_toolbar .vin_btnToolbar',
			vin_url: '/api/v1/backup_data/points',
			vin_method: 'POST',
			vin_params: function(){
				return tableParams;
			},
			placeholder: LANG.UI_VERIFY_SEARCH_BY_TIME, //搜索框的placeholder
			searchInput: true, //搜索框
			searchClass: 'pointSearch', //自定义的搜索框类名
			searchSelector: '.pointSearch', //选择使用自定义搜索框
			parentIdField: 'pid', // 确定字段作为父级字段
			idField: 'timepoint_uuid', // 确认字段作为id
			treeShowField: 'timepoint', // 确认显示展开图标的字段
			treegrid: true, // 使用树形格式
			singleSelect:true,
			showColumns: true,
			showExport: true,
			paginationLoop: false,
			fileName: LANG.UI_VERIFY_TIMEPOINT_LIST,
			PostBody: () => {
				// 渲染树形结构
				let columns = $('#point_table').bootstrapTable('getOptions').columns;
				if (columns && columns[0][1].visible) {
					// 二次渲染展开的列，增加展开图标
					$('#point_table').treegrid({
						treeColumn: 1,
						onChange: function () {
							$('#point_table').bootstrapTable('resetView')
						}
					});
				}
			},
			columns:[
				{
					checkbox: true,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter: function (value, row, index, field) {

					}
				},
				{
					field: 'timepoint',
					title: LANG.UI_RECOVERY_TIMEPOINT,
					events: expandEvents,
					formatter: function (value, row, index, field) {
						let html = '';
						if (row.hasChildren > 0) {
							html = `<div style="padding-left:12px !important;display: inline;"><span class="treegrid-expander"></span><span style="cursor:pointer;" class="treegrid-expander-collapsed root-collapsed get-child-node"></span><span title="${row.timepoint}">${row.timepoint}</span></div>`;
						} else {
							html = `<span title="${row.timepoint}">${row.timepoint}</span>`;
						}
						return html;
					}
				},
				{
					field: '',
					title: LANG.UI_SEARCH_TYPE,
					formatter: function (value, row, index, field) {
						return row.backup_mode_des;
					}
				},
				{
					field: '',
					title: LANG.UI_COPY_DATA_SIZE,
					formatter: function (value, row, index, field) {
						return row.total_size;
					}
				},
				{
					field: '',
					title: LANG.UI_PUBLIC_REAL_SIZE,
					formatter: function (value, row, index, field) {
						return row.write_size;
					}
				},
				{
					field: 'status',
					title: LANG.UI_PUBLIC_STATUS,
					formatter: function (value, row, index, field) {
						return row.status;
					}
				},
			],
		}
		$('#point_table').baseTableConfig().init(options);
	}

	let expandEvents = {
		'click .root-collapsed': function (e, value, row, index) {
			let checked = row.checked;
			let children = JSON.parse(row.children);
			let rows = $('#point_table').bootstrapTable("getData");
			if (children.length > 0) {
				if (checked) {
					for (let i = 0; i < children.length; i++) {
						children[i].checked = true;
					}
				}
				$('#point_table').bootstrapTable('append', children);
				// 获取依赖点后重新计算数量
				$('.pointTableDiv .pagination-info').html(LANG.UI_TOOLS_TOTAL + (rows.length + children.length) + LANG.UI_BACKUP_DATA_TABLE_LABEL_PAGE_NUM);
			}
		}
	}

	//初始化状态值显示
	let setStatusDes = function(status){
		let labelClass = "label-info";
		switch(status){
			case 0:	//未知
				labelClass = "label-info";
				break;
			case 1:	//部署中
				labelClass = "label-success";
				break;
			case 2:	//在线
				labelClass = "label-success";
				break;
			case 3:	//离线
				labelClass = "label-default";
				break;
			case 4:	//异常
				labelClass = "label-warning";
				break;
			case 5:	//错误
				labelClass = "label-danger";
				break;
			case 6:	//修改
				labelClass = "label-info";
				break;
			case 7:	//已部署
				labelClass = "label-success";
				break;
			default:
				labelClass ="label-success";
				break;
		}
		return labelClass;
	}

	let initNodeList = function(nodeuuid = ""){
		let params = {};
		pAjaxRequest(params, '/api/v1/nodes/select', 'GET', (result) => {
			if (result.success) {
				let nodeList = $('#nodeList');
				nodeList.empty();
				for (let i=0;i<result.data.length;i++){
					let option = '<option value="' + result.data[i].uuid + '">' + result.data[i].text + '</option>';
					nodeList.append(option);
				}
				nodeList.val(nodeuuid);
				nodeList.selectpicker('refresh');

				if(editFlag){
					getObjectInfo();
				}
			}

		});
	}

	//初始化模块类型
	let initModuleSelect = function(){
		let CURRENT_JOB_TABLE_FILTER_OPTIONS = [
			{
				label: LANG.UI_BACKUP_DATA_LABEL_SERVICE_BACKUP,
				field: 'timing_data_protect',
				value: [
					{
						id: 'vmprotect',
						value: '2_1',
						text: LANG.UI_BACKUP_DATA_MODULE_VM,
						hostflag: true,
					},
					{
						id: 'prcloud_protect',
						value: '2_2',
						text: LANG.UI_PUBLIC_PRIVATE_CLOUD,
						hostflag: true,
					},
					{
						id: 'awsprotect',
						value: '2_3',
						text: LANG.UI_PUBLIC_PUBLIC_CLOUD,
						hostflag: true,
					},
					{
						id: 'complete_machine',
						value: '5_1',
						text: LANG.UI_BACKUP_DATA_MODULE_OS,
						hostflag: true,
					},
				]
			},
			{
				label: LANG.UI_BACKUP_DATA_LABEL_SERVICE_CDP,
				field: 'real_time_data_protect',
				value: [
					{
						id: 'complete_cdp_backup',
						value: '10_0_32',
						text: LANG.UI_BACKUP_DATA_MODULE_OS,
						hostflag: true,
					}
				]
			},
			{
				label: LANG.UI_BACKUP_DATA_LABEL_SERVICE_REPLICATION,
				field: 'real_time_data_protect',
				value: [
					{
						id: 'machine_copy',
						value: '10_0_65',
						text: LANG.UI_BACKUP_DATA_MODULE_OS,
						hostflag: true,
					}
				]
			},

		];
		// 按授权显示对过滤器选项数组前三列，即定时备份、实时备份和数据复制列进行过滤
		const filteredOptions = CURRENT_JOB_TABLE_FILTER_OPTIONS.slice(0, 3).filter(item => {
			// 对每个 item 的 value 进行过滤
			const filteredValues = item.value.filter(val => CONF.PERMISSION.includes(val.id));

			// 如果过滤后的 value 数组不为空，则更新原对象的 value 并保留该对象
			if (filteredValues.length > 0) {
				item.value = filteredValues;
				return true;
			}
			return false; // 如果过滤后没有元素，则不保留该项
		});
		// 将前三项替换为过滤后的结果
		CURRENT_JOB_TABLE_FILTER_OPTIONS.splice(0, 3, ...filteredOptions);
		//完全可恢复性验证
		selectModule = ['2_1','2_2','2_3','5_1', '10_0_32', '10_0_65'];
		$('#verify_filter_wrapper').initFilter({
			filterSlotId: 'verify_filter_wrapper',
			filterBtnId: 'verify_filter_btn',
			filters: CURRENT_JOB_TABLE_FILTER_OPTIONS
		});
	}

	//初始化修改应用组信息
	let editAppGroup = function(){
		let appgroupuuid = $('#appgroup_uuid').val();
		if(appgroupuuid && appgroupuuid != ""){
			editFlag = true;
		}
		initModuleSelect();	//初始化模块类型
		//初始化修改信息
		if(editFlag){
			$('.objectDiv').show();
			pAjaxRequest({}, "/api/v1/verification/app_group/"+appgroupuuid, "GET", function (result) {
				initOldData(result.data);
				initOldSettings(result.data);
				_SETTINGS = result.data;
				initStorageShowType();
			});
		}else{
			getName();
			initStorageShowType();
		}
	}

	//初始化数据
	let initOldData = function(settings){

		//应用组uuid
		data.appgroup_uuid = settings.appgroup_uuid;

		//应用组名称
		data.appgroup_name = settings.appgroup_name;

		//节点uuid
		data.node_uuid = settings.node_uuid;

		//存储uuid
		data.storage_uuid = settings.storage_uuid;

		//描述
		data.description = settings.description;

		//对象列表
		data.object_list = settings.object_list;

	}

	//初始化存储类型展示方式和事件
	var initStorageShowType =  function(){
		pAjaxRequest({}, "/api/v1/storages/type", "GET", function (d) {
			var data = d.data;
			var storagetypeselect = $('#storagetypeselect');
			storagetypeselect.empty();
			for(var i=0; i<data.length; i++){
				//屏蔽云存储和异地存储及磁带
				if(data[i].storageid == "" || $.inArray(data[i].storagetype, [8,9,10]) != -1) continue;
				var option = $("<option>").text(data[i].text).val(data[i].storageid).attr("type",data[i].storagetype).attr("nodeuuid", data[i].node_uuid);
				storagetypeselect.append(option);
			}
			storageList = data;
			if(editFlag && _SETTINGS.storage_uuid){
				//存储
				$('#storagetypeselect').val(_SETTINGS.storage_uuid);
			}
			//初始化验证对象树
			getObjectInfo();
			//存储改变事件
			$('#storagetypeselect').on('change', function(){
				//初始化验证对象树
				getObjectInfo();
			});
		}, false);
		initOSSettings();//初始化操作系统类型描述
	}

	//初始化文件树
	var initFileTree = function(node, liID, config){
		var _path = node.path;
		var params = {agentuuid: node.object_uuid, start:0, limit:40, filename:'', dir:"",pid:0, groupuuid:""};
		if (editFlag){
			params = {agentuuid: node.object_uuid, start:0, limit:40, filename:'', dir:"",pid:0, groupuuid:"", editFlag: true,taskuuid: "",applyPathList: config.verify_config.doc_list};
		}
		var params = JSON.stringify(params);
		$.ajax({
			type: "post",
			url: CONF.AJAXPATH,
			async:true,
			data:{m:CONF.M.FILE,f:'getFileDirTree',p:params},
			success: function(data){
				result = JSON.parse(data);
				if(result.re){
					setFileTree(result, liID);
					$('#object' + escapeJquery(liID) + ' .fileTreeDiv').show();
				}else{
					$('#object' + escapeJquery(liID) + ' .fileTreeDiv').hide();
				}
			}
		});
	}
	var setFileTree = function(data, liID){
		var setting = {
			check: {
				enable: true,
				// nocheckInherit: false,
				// chkboxType: { "Y": "s", "N": "s" }
			},
			data: {
				simpleData: {
					enable: true,
				},
				key:{
					title: "title"
				}
			},
			callback: {
				beforeClick: fileNodeClick,
				// onCheck: fileNodeCheck,
				beforeExpand: fileNodeExpand
			},
			view: {
				dblClickExpand: false
			}
		};
		zTreeFile[liID] = $.fn.zTree.init($('#object' + escapeJquery(liID) + ' .allFileTree'), setting, data['fileNodes']);

	}

	var fileNodeClick = function(treeId, pNode, clickshow){
		//是否被禁用
		if(pNode.chkDisabled) {
			return;
		}
		//1文件 2 文件夹 3 磁盘
		if(pNode.type == 1){
			return;
		}else {
			//如果不是文件 加载文件/目录树
			if(pNode.more) {//加载更多
				getMoreTree(treeId, pNode);
				return;
			}
			_path = pNode.filepath;
			fileNodeExpand(treeId, pNode);
			$.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
		}


	}
	var fileNodeExpand = function (treeId,pNode) {
		if(pNode.children) return true;
		getFileSonTree(treeId,pNode,false);

	}
	// 获取文件子树
	var getFileSonTree = function (treeId,pNode,expendFlag) {
		var params = {agentuuid:pNode.uuid, start:0, limit:40, filename:'', dir:pNode.filepath,pid:pNode.filepath,groupuuid:pNode.groupuuid,code_type:pNode.code_type};
		var params = JSON.stringify(params);
		var div = "#" + pNode.groupuuid + "_" + pNode.uuid;
		Metronic.blockUI({target: div,animate: true});
		$.ajax({
			type: "post",
			url: CONF.AJAXPATH,
			async:true,
			data:{m:CONF.M.FILE,f:'getFileDirSonTree',p:params},
			success: function(data){
				Metronic.unblockUI(div);
				result = JSON.parse(data);
				if(result.re){
					//success
					$.fn.zTree.getZTreeObj(treeId).removeChildNodes(pNode);
					$.fn.zTree.getZTreeObj(treeId).addNodes(pNode, result['fileNodes'], true);
					$.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
					if(expendFlag == true){
						$.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true, true, true);
					}
					if(pNode.checked && pNode.children) {
						pNode.children.forEach(item=>{
							$.fn.zTree.getZTreeObj(treeId).checkNode(item,true);
						});
					}
				}else{
					OPREL(data);
				}
			}
		});
	}

	var getMoreTree = function(treeId, pNode){
		// 显示更多
		//判断父节点下的子节点是否全选
		var checkeFlag = pNode.getParentNode().check_Child_State==2 ? true : false;
		pNode.pid == undefined ? pNode.pid = 0 : pNode.pid;
		pNode.next_index == undefined ? pNode.next_index = 0 : pNode.next_index;
		pNode.search_file_name == undefined ? pNode.search_file_name = "" : pNode.search_file_name;
		pNode.dir_path == undefined ? _path = pNode.filepath : _path = pNode.dir_path;
		var params = {agentuuid:pNode.uuid, start:pNode.next_index, limit:_pageSize, filename:pNode.search_file_name, dir:_path,pid:pNode.pid,groupuuid:pNode.groupuuid};
		var params = JSON.stringify(params);
		var div = "#" + pNode.groupuuid + "_" + pNode.uuid;
		Metronic.blockUI({target: div,animate: true});
		$.ajax({
			type: "post",
			url: CONF.AJAXPATH,
			async:true,
			data:{m:CONF.M.FILE,f:'getFileDirSonTree',p:params},
			success: function(data){
				Metronic.unblockUI(div);
				result = JSON.parse(data);
				if(result.re){
					//success
					if(checkeFlag) {
						for(var i=0;i<result['fileNodes'].length;i++) {
							result['fileNodes'][i].checked = true;
						}
					}
					$.fn.zTree.getZTreeObj(treeId).removeNode(pNode);
					$.fn.zTree.getZTreeObj(treeId).addNodes(pNode.getParentNode(), result['fileNodes'], true);
					$.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
				}else{
					OPREL(data);
				}
			}
		});

	}

	var getCpuMode = function(oldnum){
		var div = '';
		var list = ["", LANG.UI_VERIFY_CPU_MODE_CUSTOM, "host-model", "host-passthrough"];
		var vt_flag = $('#nodeselect').find('option:selected').attr('data-vt');
		for(var i=1; i<=3; i++){
			if(vt_flag == 1 && i == 2){
				continue;
			}else if (vt_flag != 1 && i ==3){
				continue;
			}

			if(i == oldnum){
				//默认选中并标注原配置
				div += '<option value="' + i + '" selected="selected">'+ list[i] + '(' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE+ ')' +'</option>';
			}else{
				div += '<option value="' + i + '">' + list[i] + '</option>';
			}
		}
		return div;
	}

	//获取socket和Cores
	let getSocketAndCoresOption = function(oldnum, hostnum, memoryFlag = false){
		let div = '';
		let num = 40;
		//内存设置128最大值
		if (memoryFlag){
			num = 128;
		}
		for(let i=1; i<=num; i++){
			let oldDes = "";
			if(i == hostnum){
				oldDes = '(' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE+ ')';
			}
			if(i == oldnum){
				//默认选中并标注原配置
				div += '<option value="' + i + '" selected="selected">'+ i +oldDes+'</option>';
			}else{
				div += '<option value="' + i + '">' + i +oldDes+'</option>';
			}
		}
		return div;
	}

	//获取磁盘驱动器类型选项
	let getDiskTypeOption = function(oldNum){
		var div = '';
		var list = [LANG.UI_PUBLIC_DEFAULT, "IDE", "VIRTIO"+ '(' + LANG.UI_SETTING_DISK_TYPE_RECOMMEND_SOURCE+ ')', "SATA", 'SCSI'];
		if (!oldNum){
			oldNum = 2;//默认按virtio
		}
		for(var i=0; i<list.length; i++){
			if(i == 0) continue;
			if(i == oldNum){

				//默认选中并标注原配置
				div += '<option value="' + i + '" selected="selected">'+ list[i]  +'</option>';
			}else{
				div += '<option value="' + i + '">' + list[i] + '</option>';
			}
		}
		return div;
	}

	//获取网络适配器类型选项
	let getNetworkTypeOption = function(oldNum){
		var div = '';
		var list = [LANG.UI_PUBLIC_DEFAULT,  "VIRTIO"+ '(' + LANG.UI_SETTING_DISK_TYPE_RECOMMEND_SOURCE+ ')', "E1000", 'RTL8139'];
		for(var i=0; i<list.length; i++){
			if(i == 0) continue;
			if(i == oldNum){

				//默认选中并标注原配置
				div += '<option value="' + i + '" selected="selected">'+ list[i]  +'</option>';
			}else{
				div += '<option value="' + i + '">' + list[i] + '</option>';
			}
		}
		return div;
	}

	// 获取操作系统类型
	let getOsOption = function (oldnum, arch_type) {
		var div = '';
		var list = os_info_list[0].os_type_list;
		for (var j=0;j<os_info_list.length;j++){
			if(os_info_list[j].arch_type == arch_type){
				list = os_info_list[j].os_type_list;
			}
		}
		div += '<option value="0">' + LANG.UI_JOB_SELECT  + '</option>';
		for(var i=0; i<list.length; i++){
			let oldDes = "";
			if(list[i].os_value == hostSettings.os_type){
				oldDes = '(' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE+ ')';
			}
			if(list[i].os_value == oldnum){
				//默认选中并标注原配置
				div += '<option value="' + list[i].os_value + '" selected="selected">'+ list[i].os_name +oldDes+'</option>'
			}else{
				div += '<option value="' + list[i].os_value + '">' + list[i].os_name + oldDes +'</option>';
			}
		}
		return div;
	}

	//获取操作系统版本号
	let getOSVersionOption = function (oldnum, ostype, arch_type) {
		//默认widows;
		var list = os_info_list[0].os_list;
		for (var j=0;j<os_info_list.length;j++){
			if(os_info_list[j].arch_type == arch_type){
				list = os_info_list[j].os_list;
			}
		}
		var div = '';
		let oldDes = "";
		div += '<option value="0">' + LANG.UI_JOB_SELECT  + '</option>';
		for(var i=0; i<list.length; i++){
			let oldDes = "";
			if (list[i].os_type != ostype) continue;
			if(list[i].os_version == hostSettings.os_version){
				oldDes = '(' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE+ ')';
			}
			let osDescription = list[i].os_description ?? '';
			// 名称优先使用后台返回的os_description
			let osVersionName = '' !== osDescription ? osDescription : list[i].os_version_name;
			if(list[i].os_version == oldnum){
				//默认选中并标注原配置
				div += '<option data-name="'+osVersionName+'" value="' + list[i].os_version + '" selected="selected">'+ osVersionName + oldDes +'</option>';
			}else{
				div += '<option data-name="'+osVersionName+'" value="' + list[i].os_version + '" >'+ osVersionName + oldDes + '</option>';
			}
		}
		return div;
	}

	// 获取cpu架构
	let getCpuArchOption = function (oldnum) {
		var div = '';
		var list = cpu_info_list;
		div += '<option value="0">' + LANG.UI_JOB_SELECT  + '</option>';
		for(let i=0; i<list.length; i++){
			let oldDes = "";
			if(list[i].arch_type == hostSettings.cpu_arch){
				oldDes = '(' + LANG.UI_SETTING_DISK_TYPE_SAME_SOURCE+ ')';
			}
			if(list[i].arch_type == oldnum && oldnum != 0){
				//默认选中并标注原配置
				div += '<option value="' + list[i].arch_type + '" selected="selected">'+ list[i].arch_name + oldDes +'</option>';
			}else{
				div += '<option value="' + list[i].arch_type + '">' + list[i].arch_name + oldDes + '</option>';
			}
		}

		return div;
	}

	//获取内存的单位
	let getMomoryUnit = function(oldnum){
		var div = '';
		var units = ["MB", "GB", "TB"];
		for(let i=0; i<units.length; i++){
			if(units[i] == oldnum || (!oldnum && units[i] == "GB")){
				//默认选中原来的单位
				div += '<option value="' + units[i] + '" selected="selected">' + units[i] + '</option>';
			}else{
				div += '<option value="' + units[i] + '">' + units[i] + '</option>';
			}
		}

		return div;
	}

	//初始化操作系统配置项
	var initOSSettings = function(){
		var s = {};
		s.hypervisor_type = 108;
		s.platform_uuid = storageList[1].node_uuid;
		s.host_uuid = storageList[1].node_uuid;
		pAjaxRequest(s, "/api/v1/vm/platforms/support_info", "GET", function (d) {
			if (d.success){
				cpu_info_list = d.data.cpu_info_list;
				os_info_list = d.data.os_info_list;
			}
		});
	}

	// 初始化驱动检测插件，时间点类型修改后需重新初始化，指定时间点时需传入timepointList
	const initDriver = function (liID, node, pointType, timepointList = []) {
		$('#driverCheck_vm').show();
		if (!nodeMap[liID]) {
			nodeMap[liID] = node;
		}
		// let moduleType = node.module_type;
		let moduleType = CONF.MODULE_TYPE.VM;
		// 获取内嵌虚拟化的磁盘总线列表和网络类型列表
		const HYPERVISOR_EMD = 108;
		let emd_uuid = storageList[1].node_uuid;
		let p = {};
		let vm_info = {};
		p.hypervisor = HYPERVISOR_EMD;
		p.vcenter_uuid = emd_uuid;
		p.host_uuid = emd_uuid;
		Metronic.blockUI({target: '#object_tree',animate: true});
		pAjaxRequest(p, '/api/v1/recovery/vm/disk_network', 'GET', function (result) {
			Metronic.unblockUI('#object_tree');
			if (result.success) {
				// 驱动检测
				$('#driverCheck_vm').show();
				vm_info.vm_type = p.hypervisor;
				vm_info.vm_uuid = emd_uuid;
				vm_info.host_uuid = emd_uuid;
				vm_info.disk_bus_list = result.data.disk_bus;
				vm_info.net_bus_list = result.data.network_bus;
				vm_info.default_disk_bus = result.data.default_disk;
				vm_info.default_net_bus = result.data.default_network;
				vm_info.bus_changeable_flag = result.data.enable_change;
			} else {
				operateResponseList(result);
			}
		});
		// 目标配置
		let target_type;
		let target_info = {};
		if (CONF.MODULE_TYPE.VM === moduleType) {
			target_type = $.fn.driverCheck.DRIVER_TARGET_TYPE.VM;
			target_info.vm_info = vm_info;
		} else {
			target_type = $.fn.driverCheck.DRIVER_TARGET_TYPE.CLIENT;
			target_info.client_info = {
				agent_uuid: node.object_uuid
			}
		}
		target_info.target_type = target_type;
		// 实例化驱动检测组件
		$.fn.driverCheck.init($('#driverCheck_vm'), {
			width: {
				config_label: 'col-md-4',
				config_content: 'col-md-6',
				check_name: 'col-md-6',
				check_platform: 'col-md-3',
				check_driver: 'col-md-3',
			},
			getCheckObject: () => {
				return {
					source_list: getSourceList(),
					target_info: target_info
				};
			},
			show_check_btn: true,
		});
	}

	let getSourceList = function(){
		source_list = [];
		let nodes = ztree.getCheckedNodes();
		for (let i=0;i<nodes.length;i++) {

			//如果不是对象节点直接跳过
			if (nodes[i].eventtype != "object") continue;

			//处理特殊字符转义
			let liID = clearString('object_tree' + nodes[i].id);
			// 源配置
			let ostype = OS_TYPE[parseInt($(`#common_tab${liID}`).find(`select[name=ostype]`).val())];
			let osversion = $(`#common_tab${liID}`).find(`select[name=osversion]`).val();
			let cpuArch = $(`#common_tab${liID}`).find(`select[name=cpuArch]`).val();
			let moduleType = nodes[i].module_type;
			let name =  nodes[i].name;
			let timepointuuid = "";
			if (nodes[i].timepointInfo){
				name =  nodes[i].name + '/' + nodes[i].timepointInfo.timepoint + ' (' + nodes[i].timepointInfo.backup_mode_des + ')';
				if(moduleType == CONF.MODULE_TYPE.VOL_CDP){
					name =  nodes[i].name + '/' + nodes[i].timepointInfo.timepoint;
				}
				timepointuuid = nodes[i].timepointInfo.timepoint_uuid;
				if(moduleType == CONF.MODULE_TYPE.VOL_CDP){
					timepointuuid = nodes[i].timepointInfo.new_timepoint_uuid;
				}
			}

			if(cpuArch == "0" || ostype == "0" || osversion == "0"){
				UIToastr.showWarning(LANG.UI_VERIFY_ADD_APPGROUP, LANG.UI_VERIFY_OBJECT+"("+nodes[i].name + ")" +LANG.UI_VERIFY_OBJECT_HARDWARE_INFO_SET_TIPS);
				return false;
			}
			pointType = TIMEPOINT_TYPE_LAST;
			switch (pointType) {
				case TIMEPOINT_TYPE_LAST:
				case TIMEPOINT_TYPE_ALL:
					// 最新时间点和全部时间点都只传最新时间点
					if (nodes[i].timepointInfo) {
						source_list.push({
							name: name,
							timepoint_uuid: timepointuuid,
							module_type: moduleType,
							os_type: ostype,
							os_arch: cpuArch,
							os_version: osversion,
							target_hypervisor_disk_bus_list: [4], // 默认磁盘总线为VIRTO
							target_hypervisor_net_bus_list: [4], // 默认网卡总线为VMXNET3
						});
					} else {
						// 时间点不存在
						if(moduleType == CONF.MODULE_TYPE.OS || moduleType == CONF.MODULE_TYPE.VOL_CDP ){
							source_list.push({
								name: nodes[i].name,
								timepoint_uuid: '',
								module_type: moduleType,
								agent_uuid: nodes[i].object_uuid,
								os_type: ostype,
								os_arch: cpuArch,
								os_version: osversion,
								check_agent_flag: true,
								target_hypervisor_disk_bus_list: [4], // 默认磁盘总线为VIRTO
								target_hypervisor_net_bus_list: [4], // 默认网卡总线为VMXNET3
							});
						}else{
							source_list.push({
								name: nodes[i].name,
								timepoint_uuid: '',
								module_type: moduleType,
								os_type: ostype,
								os_arch: cpuArch,
								os_version: osversion,
								check_vm_flag: true,
								vm_uuid: nodes[i].object_uuid,
								vcenter_uuid: nodes[i].parent_uuid,
								target_hypervisor_disk_bus_list: [4], // 默认磁盘总线为VIRTO
								target_hypervisor_net_bus_list: [4], // 默认网卡总线为VMXNET3
							});
						}
					}
					break;
				case TIMEPOINT_TYPE_SELECT:
					// 指定时间点传选择的第一个
					for (let i in timepointList) {
						source_list.push({
							name: name,
							timepoint_uuid: timepointuuid,
							module_type: moduleType,
							os_type: ostype,
							os_version: osversion,
							os_arch: cpuArch,
							target_hypervisor_disk_bus_list: [4],
							target_hypervisor_net_bus_list: [4],
						});
					}
					break;
				default:
					break;
			}
		}
		return source_list;
	}


	return {
		init: function(){
			//修改信息初始化
			initListeners();
			editAppGroup();	//初始化修改应用组
		}
	}
}();

jQuery(document).ready(function(){
	AddAppGroup.init();
});