var StorageManager = function () {
	var modeCheckFlag = false;
	var realsize = 0;
	var usesize = 0;
	var searchParams;
	var storageType;
	var vendor;
	var editData = {}; // 定义个修改的返回信息
	let btnOpen;
	var checkIndex;
	var interval = null;
	var expandIndex = null;
	var table = $('#storagetable');
	var showWormSize = true; // 是否显示worm配置的大小
	var nodeList = [];
	var tipEditStorage = function(){
		UIToastr.showInfo(LANG.UI_STORAGE_MODIFY, LANG.UI_STORAGE_MODIFY_TIPS);
	}

	var tipDeleteStorage = function(){
		UIToastr.showInfo(LANG.UI_STORAGE_DELETE, LANG.UI_STORAGE_DELETE_TIPS1);
	}
	var tipSyncStorage = function(){
		UIToastr.showInfo(LANG.UI_VCENTER_SYNC, LANG.UI_STORAGE_TARGET_STORAGE_TIPS2);
	}
	var tipImportSelect = function(){
		UIToastr.showInfo(LANG.UI_STORAGE_IMPORT_TIME_POINT, LANG.UI_STORAGE_IMPORT_TIME_POINT_TIPS);
	}

	//得到存储详情
	var getStorageDetails = function(conf, warning,refresh){
		var details = '';
		switch(conf.type){
			case 1:		//DISK
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_DISK_TYPE, conf.disk_type);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 2:		//LVM
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 3:		//PARTITION
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_PARTITION_TYPE, conf.partition_type);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 4:		//FC
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_FC_TYPE, conf.fc_type);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 5:		//ISCSI
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_ISCSI_TYPE, conf.iscsi_type);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_ISCSI_HOST, conf.iscsi_host);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 6:		//NFS
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SHARE_PATH, conf.dev_path);
				break;
			case 7:		//CIFS
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SHARE_PATH, conf.dev_path);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_USERNAME, conf.username);
				break;
			case 8:  //异地备份系统
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_ADDR, conf.dev_path);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_USERNAME, conf.username);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_NODE, conf.remote_node_ip);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_STORAGE_NAME, conf.remote_storage_nickname);
				switch(Number(conf.remote_storage_type)){
					case CONF.BD_STORAGE_TYPE.DISK:
						details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_STORAGE_TYPE, LANG.UI_STORAGE_TYPE_DISK);
						break;
					case CONF.BD_STORAGE_TYPE.LVM:
						details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_STORAGE_TYPE, LANG.UI_STORAGE_TYPE_LOGICAL_VOLUME_LVM);
						break;
					case CONF.BD_STORAGE_TYPE.PARTITION:
						details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_STORAGE_TYPE, LANG.UI_STORAGE_TYPE_LOCAL_PARTITION);
						break;
					case CONF.BD_STORAGE_TYPE.FC:
						details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_STORAGE_TYPE, LANG.UI_STORAGE_TYPE_DETAIL_FC);
						break;
					case CONF.BD_STORAGE_TYPE.ISCSI:
						details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_STORAGE_TYPE, LANG.UI_STORAGE_TYPE_ISCSI);
						break;
					case CONF.BD_STORAGE_TYPE.NFS:
						details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_STORAGE_TYPE, LANG.UI_STORAGE_TYPE_NFS);
						break;
					case CONF.BD_STORAGE_TYPE.CIFS:
						details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_STORAGE_TYPE, LANG.UI_STORAGE_TYPE_CIFS);
						break;
					case CONF.BD_STORAGE_TYPE.REMOTE:
						details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_STORAGE_TYPE, LANG.UI_STORAGE_TYPE_REMOTE);
						break;
					case CONF.BD_STORAGE_TYPE.CLOUD:
						details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_STORAGE_TYPE, LANG.UI_STORAGE_TYPE_COUND);
						break;
					case CONF.BD_STORAGE_TYPE.LOCALDIR:
						details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_REMOTE_STORAGE_TYPE, LANG.UI_STORAGE_TYPE_LOCAL_CATALOGUE);
						break;
				}
				break;
			case 9:  //云存储
				details += getEachRowHtml(LANG.UI_STORAGE_CLOUD_VENDOR, conf.vendor);
				//Azure没有地区
				if(conf.vendor_type != 2){
					// details += getEachRowHtml(LANG.UI_STORAGE_CLOUD_REGION, conf.region);
				}
				//AWS|Ceph S3|Huawei OceanStor Pacific才有服务终端节点
				if(conf.vendor_type == 1 || conf.vendor_type == 6 || conf.vendor_type == 9 || conf.vendor_type == 10){
					details += getEachRowHtml(LANG.UI_STORAGE_CLOUD_SERVER_END_POINT, conf.service_endpoint);
				}
				//Azure没有用户
				if(conf.vendor_type != 2){
					details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_USERNAME, conf.user_key);
				}
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 11: //本地目录
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_LOCAL_DIR, conf.dev_path);
				break;
			case 13:  //并行文件系统
				details += getEachRowHtml(LANG.UI_STORAGE_CLOUD_VENDOR, conf.vendor);

				//AWS|Ceph S3|Huawei OceanStor Pacific才有服务终端节点

				details += getEachRowHtml(LANG.UI_STORAGE_CLOUD_SERVER_END_POINT, conf.service_endpoint);

				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_USERNAME, conf.user_key);

				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 12: //华为CBR
				var refresh_des = "";
				if(refresh.refresh_flag){
					refresh_des=LANG.UI_PUBLIC_ON;
				}else{
					refresh_des=LANG.UI_PUBLIC_OFF;
				}
				details += getEachRowHtml(LANG.UI_STORAGE_DATA_SCAN_SWITCH,refresh_des);
				if( refresh.refresh_flag){
					details += getEachRowHtml(LANG.UI_STORAGE_SCAN_INTERVAL, refresh.refresh_time+LANG.UI_MICROSOFT365_MINUTE);
				}
				break;
			case 16:		//DDDB
				if (conf.auth_type == 'user') {
					// 用户
					details += getEachRowHtml(LANG.UI_STORAGE_DDDB_DOMAIN, conf.data_domain_system);
					details += getEachRowHtml(LANG.UI_STORAGE_DDDB_UNIT, conf.storage_unit);
					details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_USERNAME, conf.username);
				}
				break;
			default:
				break;
		}

		//添加存储告警配置信息 华为CBR 不需要添加告警配置
		if(conf.type != 12){
			details += getWarningSettingsStr(warning, conf.type);
		}

		return details;
	}

	/**
	 * 获取NAS存储详情
	 * @param {*} row
	 */
	const getNasDetails = row => {
		let usernameTh = ``;
		if (parseInt(row.storage_type) === CONF.BD_STORAGE_TYPE.CIFS) {
			usernameTh = `<th width="10%">${LANG.UI_STORAGE_DETAIL_USERNAME}</th>`;
		}
		let use = ``;
		if (parseInt(row.use_mode) == 5) {
			use = `<th width="5%">${LANG.UI_SEARCH_STORAGE_USEMODE}</th>`;
		}
		let thead = `<thead>
			<tr>
				<th width="20%">${LANG.UI_STORAGE_NODE_NAME}</th>
				<th width="15%">${LANG.UI_STORAGE_MOUNT_POSITION}</th>
				<th width="5%">${LANG.UI_STORAGE_MOUNT_STATUS}</th>
				<th width="10%">${LANG.UI_STORAGE_DETAIL_SHARE_PATH}</th>
				${usernameTh}
				${use}
				<th width="20%">${LANG.UI_STORAGE_WARNING}</th>
				<th width="15%">${LANG.UI_BACKUP_DATA_DETAIL_ACTION_WORM}</th>
			</tr>
		</thead>`;
		let tbody = `<tbody>`;
		for (const mountPointInfo of row.mount_point_list) {
			let warningDes = LANG.UI_PUBLIC_OFF;
			if (row.warning.flag) {
				warningDes = LANG.UI_PUBLIC_ON + ' ';
				if (parseInt(row.warning.type) === 1) {
					warningDes += LANG.UI_STORAGE_WARNING_PERCENT + ":" + row.warning.value;
				} else {
					warningDes += LANG.UI_STORAGE_WARNING_SIZE + ":" + row.warning.value;
				}
			}

			let wormDes = LANG.UI_PUBLIC_OFF;
			if (row.worm.flag) {
				wormDes = LANG.UI_PUBLIC_ON + ' ';
				if (parseInt(row.worm.type) === 1) {
					wormDes += LANG.UI_STORAGE_WARNING_PERCENT + ":" + row.worm.value;
				} else if (parseInt(row.worm.type) === 2){
					wormDes += LANG.UI_STORAGE_WARNING_SIZE + ":" + row.worm.value;
				} else {
					wormDes += row.worm.value;
				}
			}

			let mountStatus = `<span class="label label-sm label-success ">${LANG.UI_NODE_NORMAL}</span>`;
			if (parseInt(mountPointInfo.mount_status) !== 1) {
				mountStatus = '<span class="label label-sm label-danger ">'+ LANG.UI_NODE_ABNORMAL +'</span>';
			}
			tbody += `<tr>`;
			tbody += `<td>${mountPointInfo.node_name}(${mountPointInfo.node_ip})</td>`;
			tbody += `<td>${mountPointInfo.mount_point}</td>`;
			tbody += `<td>${mountStatus}</td>`;
			tbody += `<td>${row.config.dev_path}</td>`;
			if (parseInt(row.storage_type) === CONF.BD_STORAGE_TYPE.CIFS) {
				tbody += `<td>${row.config.username}</td>`;
			}
			if (parseInt(row.use_mode) == 5) {
				tbody += `<td>${row.use_mode_des}</td>`;
			}
			tbody += `<td>${warningDes}</td>`;
			tbody += `<td>${wormDes}</td>`;
			tbody += `</tr>`;
		}
		tbody += `</tbody>`;
		return thead + tbody;

	};


	/**
	 * 获取云存储详情
	 * @param {*} row
	 */
	const getCloudDetails = row => {
		var conf = row.config;

		let serverNode = ``;
		//AWS|Ceph S3|Huawei OceanStor Pacific才有服务终端节点
		if(conf.vendor_type == 1 || conf.vendor_type == 6 || conf.vendor_type == 9 || conf.vendor_type == 10){
			serverNode = `<th width="10%">${LANG.UI_STORAGE_CLOUD_SERVER_END_POINT}</th>`;
		}

		let usernameTh = ``;
		//Azure没有用户
		if(conf.vendor_type != 2){
			usernameTh = `<th width="10%">${LANG.UI_STORAGE_DETAIL_USERNAME}</th>`;
		}

		let thead = `<thead>
			<tr>
				<th width="20%">${LANG.UI_STORAGE_NODE_NAME}</th>
				<th width="5%">${LANG.UI_STORAGE_MOUNT_STATUS}</th>
				<th width="10%">${LANG.UI_STORAGE_CLOUD_VENDOR}</th>
				<th width="10%">${LANG.UI_STORAGE_DETAIL_SHARE_PATH}</th>
				${serverNode}
				${usernameTh}
				<th width="20%">${LANG.UI_STORAGE_WARNING}</th>
				<th width="15%">${LANG.UI_BACKUP_DATA_DETAIL_ACTION_WORM}</th>
			</tr>
		</thead>`;
		let tbody = `<tbody>`;
		for (const mountPointInfo of row.mount_point_list) {
			let warningDes = LANG.UI_PUBLIC_OFF;
			if (row.warning.flag) {
				warningDes = LANG.UI_PUBLIC_ON + ' ';
				warningDes += LANG.UI_STORAGE_CLOUD_WARNING_SIZE + ":" + row.warning.value;
			}

			let wormDes = LANG.UI_PUBLIC_OFF;
			if (row.worm.flag) {
				wormDes = LANG.UI_PUBLIC_ON + ' ';
				if (parseInt(row.worm.type) === 1) {
					wormDes += LANG.UI_STORAGE_WARNING_PERCENT + ":" + row.worm.value;
				} else if (parseInt(row.worm.type) === 2){
					wormDes += LANG.UI_STORAGE_WARNING_SIZE + ":" + row.worm.value;
				} else {
					wormDes += row.worm.value;
				}
			}

			let mountStatus = `<span class="label label-sm label-success ">${LANG.UI_NODE_NORMAL}</span>`;
			if (parseInt(mountPointInfo.mount_status) !== 1) {
				mountStatus = '<span class="label label-sm label-danger ">'+ LANG.UI_NODE_ABNORMAL +'</span>';
			}
			tbody += `<tr>`;
			tbody += `<td>${mountPointInfo.node_name}(${mountPointInfo.node_ip})</td>`;
			tbody += `<td>${mountStatus}</td>`;
			tbody += `<td>${row.config.vendor}</td>`;
			tbody += `<td>${row.config.dev_path}</td>`;
			if(conf.vendor_type == 1 || conf.vendor_type == 6 || conf.vendor_type == 9 || conf.vendor_type == 10){
				tbody += `<td>${conf.service_endpoint}</td>`;
			}
			if(conf.vendor_type != 2){
				tbody += `<td>${conf.user_key}</td>`;
			}

			tbody += `<td>${warningDes}</td>`;
			tbody += `<td>${wormDes}</td>`;
			tbody += `</tr>`;
		}
		tbody += `</tbody>`;
		return thead + tbody;

	};

	//存储告警配置信息
	var getWarningSettingsStr = function(warning, storageType){
		var powerStr = warning.flag ? LANG.UI_PUBLIC_ON : LANG.UI_PUBLIC_OFF;

		var html = "<tr><td>" + LANG.UI_STORAGE_WARNING + ":</td><td>" + powerStr + "</td>";
		if(warning.flag){
			//如果存储告警开启
			if("1" == warning.type){
				html += "<td>" + LANG.UI_STORAGE_WARNING_PERCENT + ":" + warning.value + "</td>";
			}else{
				if(storageType == 9 ||storageType == 13 ){
					html = "<tr><td>" + LANG.UI_STORAGE_CLOUD_WARNING_SIZE + ":</td><td>" + warning.value + "</td>";
				}else{
					html += "<td>" + LANG.UI_STORAGE_WARNING_SIZE + ":" + warning.value + "</td>";
				}
			}
		}
		html += "</tr>";
		return html;
	}

	//worm告警配置信息
	var getWormSettingsStr = function(warning, storageType){
		// 处理WORM的功能授权控制
		if (!CONF.FUNCTIONS.includes('worm')) {
			// 没得worm，那么去掉
			return '';
		}
		var powerStr = warning.flag ? LANG.UI_PUBLIC_ON : LANG.UI_PUBLIC_OFF;

		var html = "<tr><td>" + LANG.UI_BACKUP_DATA_DETAIL_ACTION_WORM + ":</td><td>" + powerStr + "</td>";
		if(warning.flag && storageType != 0){
			//如果worm告警开启 并且不是云存储
			if("1" == warning.type){
				html += "<td>" + LANG.UI_STORAGE_WARNING_PERCENT + ":" + warning.value + "</td>";
			}else if("2" == warning.type){
				html += "<td>" + LANG.UI_STORAGE_WARNING_SIZE + ":" + warning.value + "</td>";
			} else {
				html += "<td>" + warning.value + "</td>";
			}
		}
		html += "</tr>";
		return html;
	}

	//得到每行的HTML
	var getEachRowHtml = function(key, value){
		var html = "<tr><td>" + key + ":</td><td colspan='10'>";
		html += value;
		html += "</td></tr>";
		return html;
	}


	function storageFormatter(index, row) {
		// console.log(row);
		if (row.storage_type == 14) {
			return '<span>HUAWEI OceanStor</span>'
		}
		switch (row.storage_type) {
			case CONF.BD_STORAGE_TYPE.UNKNOWN:
				return '<span> UNKNOWN </span>'
				break;
			case CONF.BD_STORAGE_TYPE.DISK:
				return '<span> ' + LANG.UI_STORAGE_TYPE_DISK + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.LVM:
				return '<span> ' + LANG.UI_STORAGE_TYPE_LOGICAL_VOLUME_LVM + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.PARTITION:
				return '<span> ' + LANG.UI_STORAGE_TYPE_LOCAL_PARTITION + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.FC:
				return '<span> ' + LANG.UI_STORAGE_TYPE_DETAIL_FC + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.ISCSI:
				return '<span> ' + LANG.UI_STORAGE_TYPE_ISCSI + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.NFS:
				return '<span> ' + LANG.UI_STORAGE_TYPE_NFS + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.CIFS:
				return '<span> ' + LANG.UI_STORAGE_TYPE_CIFS + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.REMOTE:
				return '<span> ' + LANG.UI_STORAGE_TYPE_REMOTE + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.CLOUD:
				return '<span> ' + LANG.UI_STORAGE_TYPE_COUND + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.TAPE:
				return '<span> ' + LANG.UI_STORAGE_TYPE_TAPE + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.LOCALDIR:
				return '<span> ' + LANG.UI_STORAGE_TYPE_LOCAL_CATALOGUE + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.HUAWEICBR:
				return '<span> ' + LANG.UI_STORAGE_TYPE_HUAWEI_CBR + ' </span>'
				break;
			case CONF.BD_STORAGE_TYPE.DDDB:
				return '<span> ' + LANG.UI_STORAGE_TYPE_16 + ' </span>'
				break;
		}

	}

	//存储详情
	var lastIndex = [-1, -1];
	var current_detail = function (index, row, element) {
		if (index != lastIndex[1]) {
			lastIndex.push(index);
			table.bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
			lastIndex.splice(0, 1);
		}
		if (!row.config) {
			return;
		}

		let sOut;
		let storageType = parseInt(row.storage_type);

		if (storageType === CONF.BD_STORAGE_TYPE.NFS || storageType === CONF.BD_STORAGE_TYPE.CIFS) {
			// NFS和CIFS 的详情显示与其他存储不一致
			sOut = `<table>`;
			sOut += getNasDetails(row);
			sOut += '</table>';
		} else if (storageType === CONF.BD_STORAGE_TYPE.CLOUD) {
			// 云存储的详情显示与其他存储不一致
			sOut = `<table>`;
			sOut += getCloudDetails(row);
			sOut += '</table>';
		} else {
			sOut = '<tr class="details"><td class="details" colspan="12">';
			sOut += '<table>';

			//如果是华为CBR 需要使用refresh_flag这一项
			if(row.config.type != 12){
				sOut += getStorageDetails(row.config, row.warning);
			}else{
				sOut += getStorageDetails(row.config, row.warning, row.refresh);
			}
			//        sOut += '<tr id="detail'+data[8]['config'].storageuuid+'"><td></td><tr>';
			// 自动扫描 自动分配
			var autoscan_flag_des = "";
			if(row.autoscan_flag){
				autoscan_flag_des = LANG.UI_PUBLIC_ON;
			}else{
				autoscan_flag_des = LANG.UI_PUBLIC_OFF;
			}
			var allocate_flag_des = "";
			if(row.allocate_flag){
				allocate_flag_des = LANG.UI_PUBLIC_ON;
			}else{
				allocate_flag_des = LANG.UI_PUBLIC_OFF;
			}
			//sOut += getEachRowHtml(LANG.UI_STORAGE_AUTO_IMPORT_BACKUP_DATA, LANG.UI_STORAGE_AUTO_SCAN+'：' + autoscan_flag_des + ' '+ LANG.UI_STORAGE_AUTO_ASSIGN +'：' + allocate_flag_des);
			if (row.use_mode == 5) {
				// 只读用途显示
				sOut += getEachRowHtml(LANG.UI_SEARCH_STORAGE_USEMODE, row.use_mode_des);
			}
			// 这里进行worm配置显示
			sOut += getWormSettingsStr(row.worm, row.storage_type);

			sOut += '</table></td></tr>';
		}
		$(element).append(sOut);
	}

	var checkEvent = function (tableId, btnId) {
		var selectedRow = table.bootstrapTable('getSelections');
		checkIndex = selectedRow;
		let select = $('' + tableId + '').bootstrapTable('getSelections');
		if (select.length == 0) {
			$('' + btnId + ' i').addClass('icon-gray-delete');
			$('' + btnId + ' i').removeClass('icon-white-delete');
			$('' + btnId + '').removeClass('select-delete-btn');
			$('' + btnId + '').addClass('cancel-delete-btn');
			$('' + btnId).css('cursor', 'not-allowed');
		} else {
			$('' + btnId + ' i').removeClass('icon-gray-delete');
			$('' + btnId + ' i').addClass('icon-white-delete');
			$('' + btnId + '').removeClass('cancel-delete-btn');
			$('' + btnId + '').addClass('select-delete-btn');
			$('' + btnId).css('cursor', 'pointer');
		}
	}

	var handleRecords = function () {
		var options = {
			toolbarId: '#storage_manager_toolbar',
			buttonsToolbar: '#storage_manager_toolbar .vin_btnToolbar',
			vin_url: '/api/v1/storages',
			vin_method: 'GET',
			vin_params: function () {
				let params = {};
				params.source_type = 1; //备份存储1， 生产存储2
				if ($.trim($('#searchInputVal').val()) != '') {
					params.search = $.trim($('#searchInputVal').val());
				}
				if (searchParams) {
					params.accurate_flag = true;
					$.extend(params, searchParams);
				}
				return params;
			},
			singleSelect:true,
			detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
			detailFormatter: current_detail,
			customTool: {
				beforeInput: ``,
				afterInput: ``,
			},
			searchInput: false, //搜索框
			advanceSearch: {
				module: 'list'
			}, //高级搜索
			onCheck: function () {
				checkEvent('#storagetable', '#delete');
			},
			onUncheck: function () {
				checkEvent('#storagetable', '#delete');
			},
			onCheckAll: function () {
				checkEvent('#storagetable', '#delete');
			},
			onUncheckAll: function () {
				checkEvent('#storagetable', '#delete');
			},
			onPostBody: function () {
				$('#storagetable th[data-field="num"]').css('width','5%');
				$('#storagetable th[data-field="storage_type"]').css('width','8%');
				$('#storagetable th[data-field="status"]').css('width','8%');
				$('#storagetable th[data-field="total_size"]').css('width','8%');
				$('#storagetable th[data-field="free_size"]').css('width','8%');
				$('#storagetable th[data-field="flag"]').css('width','8%');
				$('#storagetable th[data-field="use_mode"]').css('width','8%');

				var tableData = table.bootstrapTable('getData');
				if (tableData.length == 0)return;
				checkRecord();
				initTimer();
				$('#' + btnOpen + '').parent('.btn-group').addClass('open');
				if (null !== expandIndex) {
					table.bootstrapTable('expandRow', expandIndex);
				}
				checkEvent('#storagetable', '#delete');

			},
			onExpandRow: (index) => {
				if (null === expandIndex) {
					expandIndex = index;
				} else if (index !== expandIndex) {
					table.bootstrapTable('collapseRow', expandIndex);
					expandIndex = index;
				}
			},
			onCollapseRow: () => {
				expandIndex = null;
			},
			onRefresh: function (params) {
				table.bootstrapTable('hideLoading');
			},
			// showExport: false, //是否开启导出按钮
			// showColumns: false, //是否开启列选择按钮
			// onResetView: initTableHeight,
			columns: [ //列定义
				{
					checkbox: true,
					sortable: false,
					formatter: function (value, row, index){
						if (row.storage_type == CONF.BD_STORAGE_TYPE.TAPE) {
							// 磁带的不能操作
							return {
								disabled: true,// 设置是否可用
								checked: false // 设置选中
							};
						}
						return {
							disabled: false,// 设置是否可用
							checked: false // 设置选中
						};
					}
				},
				{
					field: 'num', //字段名
					title: LANG.UI_PUBLIC_TABLE_ID,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'storage_nickname',
					title: LANG.UI_SEARCH_NICKNAME,
					formatter: (value, row) => {
						if (!row.mount_flag) {
							return `<span title="${value} ${LANG.UI_STORAGE_STATUS_MOUNT_WARNNING}">${value} <i style="color: rgb(234, 164, 14);" class="viconfont vicon-error-warning-fill"></i></span>`
						}
						return `<span title="${value}">${value}</span>`
					},
				},
				{
					field: 'storage_type',
					title: LANG.UI_SEARCH_STORAGE_TYPE,
					formatter: storageFormatter
				},
				{
					field: 'node',
					title: LANG.UI_PUBLIC_STORAGE_IN_NODE,
					formatter: (value, row) => {
						let storageType = parseInt(row.storage_type);
						if (storageType === CONF.BD_STORAGE_TYPE.NFS || storageType === CONF.BD_STORAGE_TYPE.CIFS) {
							return `--`;
						}
						return `<span title="${value}">${value}</span>`
					},
				},
				{
					title: LANG.UI_STORAGE_POOL,
					field: 'storage_pool_name',
					sortable: false,
					formatter: value => {
						if (!Array.isArray(value) || !value.length) {
							return '--';
						}
						let name = value.join('<br>');
						let title = value.join('\n');
						return `<span title="${title}">${name}</span>`;
					},
				},
				{
					field: 'status',
					title: LANG.UI_NODE_STATUS,
					formatter: function (index, row) {
						let storageType = parseInt(row.storage_type);
						if (storageType === CONF.BD_STORAGE_TYPE.NFS || storageType === CONF.BD_STORAGE_TYPE.CIFS) {
							return `--`;
						}
						if (row.status == '--') {
							return row.status;
						} else if (row.status == true) {
							return '<span class="label label-sm label-success "> ' + LANG.UI_NODE_NORMAL + ' </span>';
						} else {
							return '<span class="label label-sm label-danger "> ' + LANG.UI_NODE_ABNORMAL + ' </span>';
						}
					}
				},
				{
					field: 'total_size',
					title: LANG.UI_JOB_TOTAL_SIZE,
				},
				{
					field: 'free_size',
					title: LANG.UI_PUBLIC_FREE_STORAGE_SIZE,
				},
				{
					field: 'flag',
					title: LANG.UI_SEARCH_STORAGE_STATUS,
					formatter: function (index, row) {
						let flag = parseInt(row.flag);
						let des = row.desc;
						let storageType = parseInt(row.storage_type);
						if (storageType === CONF.BD_STORAGE_TYPE.NFS || storageType === CONF.BD_STORAGE_TYPE.CIFS) {
							flag = 2;
							des = LANG.UI_STORAGE_STATUS_OFFLINE;
							for (const mountPointInfo of row.mount_point_list) {
								if (parseInt(mountPointInfo.mount_status) === 1) {
									flag = 1;
									des = LANG.UI_STORAGE_STATUS_NORMAL;
									break;
								}
							}
						}
						var levelClass = 'label-warning';
						switch (flag) {
							case 1:
								levelClass = "label-success";
								break;
							case 2:
							case 6: // 同步中
								levelClass = "label-warning";
								break;
							case 3:
								levelClass = "label-default";
								break;
							default:
								levelClass = "label-warning";
								break;
						}
						return '<span class="label label-sm '+ levelClass +' "> ' + des + ' </span>';
					}
				},
				/*{
					field: 'use_mode',
					title: LANG.UI_SEARCH_STORAGE_USEMODE,
				},*/
			]
		}
		table.baseTableConfig().init(options);
	}

	//记录勾选
	var checkRecord = function () {
		var checkArr = [];
		$.each(checkIndex, function (index) {
			checkArr.push(checkIndex[index].storage_uuid);
		});
		table.bootstrapTable('checkBy', {
			field: 'storage_uuid',
			values: checkArr
		})
	}
	var initTimer = function () {
		if (interval != null) { //判断计时器是否为空
			clearTimeout(interval);
			// interval = null;
		}
		interval = setTimeout(update, 5000);
	}
	//更新表格数据
	var update = function () {
		table.bootstrapTable('refresh');
	}

	//初始化事件
	var addListeners = function(){
		//切换页数保存到cookie
		$('select[name=storagetable_length]').on('change', function(){
			pageLength.storage = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});

		$('#add').on('click', addStorage);
		$('#edit').on('click', function (){
			if (checkAuth(1)) {
				checkOperateAuth(checkAuth(1), editStorage)
			}
		});
		$('#delete').on('click', function (){
			if (checkAuth(2)) {
				checkOperateAuth(checkAuth(2), deleteStorageCheck)
			}
		});

		$('#sync').on('click', function (){
			if (checkAuth(3)) {
				checkOperateAuth(checkAuth(3), syncStorageCheck)
			}
		});

		$('#importdata').on('click', function (){
			importData();
		});
		$('#submit').on('click', deleteStorage);
		$('#editsubmit').on('click', function (){
			if (checkAuth(1)) {
				checkOperateAuth(checkAuth(1), preCondition)
			}
		});
		$('#importsubmit').on('click',importSubmit);
		//手动导入时间点
		$('#manualimporttimepoint').on('click',manualImportTime);
		//自动导入时间点配置
		$('#autoimporttimepointconf').on('click',autoImportTimeConf);

		$('#noticeswitch').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('.warnningdiv').show();	//开
				noticeTypeChange();
			}else{
				$('.warnningdiv').hide();	//关
			}
		});

		$('#wormswitch').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				if (showWormSize == true) {
					$('.wormdiv').show();	//开
					wormTypeChange();
				}
			}else{
				$('.wormdiv').hide();	//关
			}
		});

		$('#yidiswitch').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('.yidimessagediv').show();	//开
			}else{
				$('.yidimessagediv').hide();	//关
			}
		});
		$('#cifsswitch').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('.cifsmessagediv').show();	//开
			}else{
				$('.cifsmessagediv').hide();	//关
			}
		});
		$('#dddbswitch').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('.dddbmessagediv').show();	//开
			}else{
				$('.dddbmessagediv').hide();	//关
			}
		});
		$('#datascanswitch').bootstrapSwitch('onSwitchChange', function (e, data) {
			if(data){
				$('.datascandiv').show();	//开
			}else{
				$('.datascandiv').hide();	//关
			}
		});

		$('select[name=noticetype]').on('change', noticeTypeChange);
		$('select[name=wormtype]').on('change', wormTypeChange);
		initspinner();

		//弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'800px', 'height':'260px'});
		});

		//高级搜索发送请求到服务端
		$('#serach_submit').on('click', function(){
			$('#searchInputVal').val('');
			$('#searchDiv .searchContent').text('');
			var p = {};
			p.storage_type = $('#storageType').val();
			p.storage_status = $('#storageStatus').val();
			p.nick_name = $('#nickName').val();
			p.node_uuid = $('#nodeSelect').val();
			searchParams = p;
			//添加搜索条件显示
			addSearchContent(p);
			$('#searchmodal').modal('hide');
			expandIndex = null
			table.bootstrapTable('uncheckAll');
			table.bootstrapTable('refresh');
		});

		//定义iCheck样式
		$('#usemodeDiv').find('input').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});

		//按存储别名搜索
		$('#searchbtnclear').on('click', function (){
			$('#searchInputVal').val('');
			searchStorage();
		});
		$('#searchInputVal').keypress(function (e) {
			if (e.which == 13) {
				searchStorage();
			}
		});
		$('#searchbtn').on('click', function (){
			searchStorage();
		});

		// 处理云存储的功能授权控制
		if (!CONF.FUNCTIONS.includes('cloudstorage')) {
			// 没得云存储，那么去掉
			$('#storageType option[value="9"]').remove();
		}

		// 处理WORM的功能授权控制
		if (!CONF.FUNCTIONS.includes('worm')) {
			// 没得worm，那么去掉
			$('.wormCheckDiv').remove();
			$('.storagewormdiv').remove();
		}
		//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function () {}
		});

		limitMin('#wormgpercent', 20, 99);
		limitMin('#wormsize', 1, 9999999);
	}

	function limitMin(dom, minVal, maxValue = 99) {
		let $doms = $(dom);

		$doms.each(function() {
			let $this = $(this);

			// 输入时只过滤非数字字符
			$this.off('input.limitMin').on('input.limitMin', function() {
				let value = $(this).val();
				let filteredValue = value.replace(/\D/g, '');

				// 限制长度
				let maxLength = maxValue.toString().length;
				if (filteredValue.length > maxLength) {
					filteredValue = filteredValue.substring(0, maxLength);
				}

				if (value !== filteredValue) {
					$(this).val(filteredValue);
				}
			});

			// 失去焦点时进行范围验证
			$this.off('blur.limitMin').on('blur.limitMin', function() {
				let value = $(this).val();

				if (value === '') {
					$(this).val(minVal.toString());
					$(this).trigger('change');
					return;
				}

				let numValue = parseInt(value, 10);

				if (isNaN(numValue) || numValue < minVal) {
					$(this).val(minVal.toString());
				} else if (numValue > maxValue) {
					$(this).val(maxValue.toString());
				}

				$(this).trigger('change');
			});

			// 初始验证
			let currentValue = $this.val();
			if (currentValue === '' || parseInt(currentValue) < minVal) {
				$this.val(minVal.toString());
			}
		});

		return $doms;
	}

	var searchStorage = function(){
		//清除高级筛选显示内容
		$('#searchDiv .searchContent').text('');
		$('#searchDiv').hide();
		// 动态设置表格高度
		$('.resource-manager-wrap .resource-manager-wrap__content .table-container.storage-manager-table-container').css('height', 'calc(100% - 46px)');

		table.bootstrapTable('uncheckAll');
		table.bootstrapTable('refresh');
	}


	var useModeClick = function(event){
		// var mode = $(this).data('mode');
		// if(event.target.checked){
		// 	//如果是取消选中
		// 	$('#useMode').find('input').iCheck("uncheck");
		// }else{
		// 	$('#useMode').find('input').iCheck("uncheck");
		// 	$('#useMode').find('input[data-mode='+ mode +']').iCheck("check");
		// }
	}


	//显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('.searchContent').text('');
		var nickName = xssEncode($('#nickName').val());
		if(p.storage_type != "0"){
			info += '<span id="storageType" title="' + $('#storageType').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_STORAGE_TYPE +': <i>' + $('#storageType').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.storage_status != "0"){
			info += '<span id="storageStatus" title="'+$('#storageStatus').find('option:selected').text()+'"> '+ LANG.UI_SEARCH_STORAGE_STATUS +': <i>' + $('#storageStatus').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.nick_name){
			info += '<span id="nickName" style="position: relative"><span id="nickNameDetail" style="display: none;position: absolute;bottom: -40px;left: 0px;background: white;white-space: nowrap"> ' + nickName + '</span> '+ LANG.UI_SEARCH_NICKNAME +': <i>' + nickName + '</i><em>X</em></span>';		}
		if(p.node_uuid != "0"){
			info += '<span id="nodeValue" title="' + $('#nodeSelect').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_SELET_NODE +': <i>' + $('#nodeSelect').find("option:selected").text() + '</i><em>X</em></span>';
		}

		$('.searchContent').append(info);
		$('#searchDiv').show();
		$('.resource-manager-wrap .resource-manager-wrap__content .table-container.storage-manager-table-container').css('height', 'calc(100% - 86px)');
		$('#searchDiv .searchContent #nickName').mouseenter(function(e){
			$('#nickNameDetail').show()
		}).mouseleave(function(){
			$('#nickNameDetail').hide()
		});
		$('#searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();
			var searchContent = $('.searchContent');
			if(searchContent[0].children.length == 0){
				$('#searchDiv').hide();
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.storage-manager-table-container').css('height', 'calc(100% - 46px)');
			}
			var parent  = $(this).parent();
			var id = parent[0].id;

			if (id == 'storageType') {
				p['storage_type'] = 0;
				$('#storageType').val(0);
			} else if (id == 'storageStatus') {
				p['storage_status'] = 0;
				$('#storageStatus').val(0);
			} else if (id == 'nickName') {
				p['nick_name'] = '';
				$('#nickName').val('');
			} else if (id == 'nodeValue') {
				p['node_uuid'] = 0;
				$('#nodeSelect').val(0);
			}
			searchParams = p;
			table.bootstrapTable('uncheckAll');
			table.bootstrapTable('refresh');
		});
		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').hide();
			$('.resource-manager-wrap .resource-manager-wrap__content .table-container.storage-manager-table-container').css('height', 'calc(100% - 46px)');
			expandIndex = null
			searchParams = {};
			$('#storageType').val(0);
			$('#storageStatus').val(0);
			$('#nickName').val('');
			$('#nodeSelect').val(0);
			table.bootstrapTable('uncheckAll');
			table.bootstrapTable('refresh');
		});

		//如果没搜索条件，先隐藏div
		if(!info){
			$('#searchDiv').hide();
			$('.resource-manager-wrap .resource-manager-wrap__content .table-container.storage-manager-table-container').css('height', 'calc(100% - 46px)');
		}
	}

	var noticeTypeChange = function(){
		var noticeType = $('select[name=noticetype]').val();
		if('1' == noticeType){
			$('#percentdiv').show();
			$('#sizediv').hide();
		}else if('2' == noticeType){
			$('#percentdiv').hide();
			$('#sizediv').show();
		}
	}

	var wormTypeChange = function(){
		var wormType = $('select[name=wormtype]').val();
		if('1' == wormType){
			$('#worm_percentdiv').show();
			$('#worm_sizediv').hide();
		}else if('2' == wormType){
			$('#worm_percentdiv').hide();
			$('#worm_sizediv').show();
		} else {
			$('#worm_percentdiv').hide();
			$('#worm_sizediv').hide();
		}
	}

	var initspinner = function(){
		$('#spinnerpercent').spinner({value: 20, step: 5, min: 1,max: 99});
		$('#spinnersize').spinner({value: 10, step: 10, min: 1,max: 9999999});
		$('#cloudsize').spinner({value: 10, step: 10, min: 1,max: 9999999});
		$('#cbrscantime').spinner({value:15,step:5,min:1,max:9999999});
		$('#autoimport').spinner({value:15,step:5,min:1,max:9999999});
	}

	//导入数据管理
	var importData = function(){
		LOCATION('./content/platform/storage/storage_data.php','storage_manager');
	}

	//添加存储
	var addStorage = function(){
		LOCATION('./content/platform/storage/storage_add.php','storage_manager');
	}

	// 操作权限校验
	var checkAuth = function(type = 1) {
		var select = table.bootstrapTable('getSelections');
		if (!select.length) {
			if (type == 2) {
				tipDeleteStorage();
			} else if (type == 3) {
				tipSyncStorage();
			} else if (type == 4) {
				tipImportSelect();
			} else {
				tipEditStorage();
			}
			return false;
		}

		return {
			type: 2,
			source_uuid: select[0].storage_uuid,
			source_type: 8
		};
	}

	//修改存储
	var editStorage = function(){
		var select = table.bootstrapTable('getSelections');
		if(!select.length){
			return tipEditStorage();
		}
		$('#yidiswitch').bootstrapSwitch('state', false);
		$('#cifsswitch').bootstrapSwitch('state', false);
		$('#dddbswitch').bootstrapSwitch('state', false);

		$('#backupCheck').iCheck('enable');
		$('#copyCheck').iCheck('enable');
		$('#archiveCheck').iCheck('enable');
		$('#backupCheck').iCheck('uncheck');
		$('#copyCheck').iCheck('uncheck');
		$('#archiveCheck').iCheck('uncheck');
		$('#node2Div').hide();

		Metronic.blockUI({target: '#storagetable',animate: true,cenrerY: true});
		pAjaxRequest({}, "/api/v1/storages/"+select[0].storage_uuid, "GET", function (result) {
			Metronic.unblockUI('#storagetable');
			if (result.code == 0) {
				var d = result.data;
				editData = d;
				$('#storageuuid').val(d.storage_uuid);
				$('#storagename').val(d.storage_nickname);
				var sizeGB = d.total_size;
				realsize = parseInt(sizeGB);
				usesize = realsize - parseInt(d.free_size);
				storageType = d.storage_type;
				setScanDataSettings(d);
				setWarningSettings(d);
				setWormSettings(d);
				setAutoImportSettings(d);
				$('#modaldivedit').drawer('show');
				var mode = d.use_mode;
				switch(mode){
					case 1:
						$('#backupCheck').iCheck('check');
						break;
					case 2:
						$('#copyCheck').iCheck('check');
						break;
					case 3:
						$('#archiveCheck').iCheck('check');
						break;
				}
				if(d.storage_type == 9 || d.storage_type == 13){
					$('.noticeCheckDiv').hide();
					$('.storagewarningdiv').hide();
					$('.cloudwarningdiv').show();
					$('.hwdatascanDiv').hide();
				} else if(d.storage_type == 12){
					$('.noticeCheckDiv').hide();
					$('.storagewarningdiv').hide();
					$('.cloudwarningdiv').hide();
					$('.hwdatascanDiv').show();
				} else {
					$('.noticeCheckDiv').show();
					$('.cloudwarningdiv').hide();
					$('.storagewarningdiv').show();
					$('.hwdatascanDiv').hide();
				}
				if(d.storage_type == 8 || d.storage_type == 9  || d.storage_type == 13){
					$('#backupCheck').iCheck('disable');
					$('#copyCheck').iCheck('disable');
					$('#archiveCheck').iCheck('disable');
				}
				if(d.storage_type == 8 || d.storage_type == 12){
					$('#autoimportflagDiv').hide();
				}else{
					$('#autoimportflagDiv').show();
				}

				if(d.storage_type == 8) {
					// 异地备份系统展示修改账号密码按钮
					$('.yidiCheckDiv').show();
				} else {
					$('.yidiCheckDiv').hide();
				}

				if(d.storage_type == 7) {
					// cifs 展示修改账号密码按钮
					$('.cifsCheckDiv').show();
				} else {
					$('.cifsCheckDiv').hide();
					$('.cifsmessagediv').hide();
				}

				if(d.storage_type == 16) {
					// dddb 展示修改原来配置信息
					$('.dddbCheckDiv').show();
					$('#modaldivedit').find('input[name=username16]').val(d.config.username);
				} else {
					$('.dddbCheckDiv').hide();
					$('.dddbmessagediv').hide();
				}

				if ($.inArray(d.storage_type, [CONF.BD_STORAGE_TYPE.NFS, CONF.BD_STORAGE_TYPE.CIFS, CONF.BD_STORAGE_TYPE.CLOUD]) !== -1) {
					// cifs或nfs 云存储显示挂载节点
					$('#node2Div').show();
					var uuids = [];
					for (var k in select[0].mount_point_list) {
						uuids.push(select[0].mount_point_list[k].node_uuid);
					}
					var nodeselect2 = $('#nodeselect2');
					// 选中指定的值
					nodeselect2.val(uuids);

					// 不允许勾选离线节点
					// 1. 先清空所有 disabled 状态
					nodeselect2.find('option').prop('disabled', false);
					// 2. 遍历 nodeList，找出“未被选中 且 离线”的节点，禁用其 option
					for (var j = 0; j < nodeList.length; j++) {
						var node = nodeList[j];
						// 条件：不在 uuids 中（未选中） + 离线
						if (!uuids.includes(node.node_uuid) && node.online_flag === false) {
							// 👇 通过 value 找到对应的 option
							nodeselect2.find('option[value="' + node.node_uuid + '"]').prop('disabled', true);
						}
					}

					// 记录本次的所选节点
					editData.mount_uuid_list = uuids;

					//nodeselect2.val(uuids).prop('disabled', editData.in_task).selectpicker('refresh');
					nodeselect2.val(uuids).selectpicker('refresh');

					if (editData.in_task) {
						$('#share_amount_in_use').show();
					} else {
						$('#share_amount_in_use').hide();
					}
				}

				//选择存储用途复选框
				if(!modeCheckFlag){
					$('#useMode').find('.icheck').on('ifClicked', useModeClick);
					modeCheckFlag = true;
				}
			} else {
				UIToastr.showError(LANG.UI_STORAGE, result.message);
			}
		});
	}

	//设置告警配置信息
	var setWarningSettings = function(settings){
		$('#noticeswitch').bootstrapSwitch('state', settings.warning.flag);
		var warningType = settings.warning.type;
		var warningValue = settings.warning.value;
		if(warningType == 0){
			warningType = 1;
			warningValue = 20;
			if(settings.storage_type == 9 || settings.storage_type == 13){
				warningValue = 10;
			}
		}
		$('select[name=noticetype]').val(warningType);
		if(1 == warningType){
			$('#warningpercent').val(warningValue);
		}else{
			if(settings.storage_type == 9 || settings.storage_type == 13){
				$('#limitsize').val(warningValue);
			}else{
				$('#warningsize').val(warningValue);
			}
		}
		//控制初始显示
		if(1 == warningType){
			$('#percentdiv').show();
			$('#sizediv').hide();
		}else{
			$('#percentdiv').hide();
			$('#sizediv').show();
		}
		if(!settings.warning.flag){
			$('.warnningdiv').hide();
			$('.cloudwarningdiv').hide();
		}
	}

	//设置worm配置信息
	var setWormSettings = function(settings){
		// worm配置
		var worm = settings.worm;
		$('#wormswitch').bootstrapSwitch("disabled", false);
		$('select[name=wormtype]').attr("disabled", false);
		$('#wormgpercent').attr("disabled", false);
		$('#wormsize').attr("disabled", false);
		$('#worm_spinnersize .spinner-group-btn').show();
		$('#worm_spinnerpercent .spinner-group-btn').show();

		$('#wormswitch').bootstrapSwitch('state', worm.flag);
		if (worm.flag == true) {
			// 不允许关闭
			$('#wormswitch').bootstrapSwitch("disabled", true);
			// 不允许修改
			$('select[name=wormtype]').attr("disabled", true);
			$('#wormgpercent').attr("disabled", true);
			$('#wormsize').attr("disabled", true);
			$('#worm_spinnersize .spinner-group-btn').hide();
			$('#worm_spinnerpercent .spinner-group-btn').hide();
		}

		$('.wormCheckDiv').show();
		if(settings.storage_type == 9){
			// 云存储
			// 展示隐藏修改功能
			$('.wormCheckDiv').hide();
			showWormSize = false;
			$('.wormdiv').hide();
			return;
			vendor = settings.config.vendor_type;
			if ($.inArray(settings.config.vendor_type, [2, 3, 5]) !== -1) {
				// azure、阿里云和腾讯云不支持worm开关
				$('.wormCheckDiv').hide();
				$('.wormdiv').hide();
			}
			showWormSize = false;
			$('.wormdiv').hide();
		} else {
			// 其它存储
			vendor = 0;
			$('.wormdiv').show();
			showWormSize = true;
			$('select[name=wormtype]').val(worm.type);
			if (worm.type == 1) {
				// 百分百
				$('#worm_percentdiv').show();
				$('#worm_sizediv').hide();
				$('#wormgpercent').val(worm.value);
				$('#worm_spinnerpercent').spinner({value: settings.worm.value, step: 5, min: 20,max: 99});
				$('#worm_spinnersize').spinner({value: 10, step: 10, min: 1,max: 9999999});
			} else if (worm.type == 2) {
				// 大小
				$('#worm_percentdiv').hide();
				$('#worm_sizediv').show();
				$('#wormsize').val(worm.value);
				$('#worm_spinnersize').spinner({value: worm.value, step: 10, min: 1,max: 9999999});
				$('#worm_spinnerpercent').spinner({value: 20, step: 5, min: 20,max: 99});
			} else {
				$('#worm_percentdiv').hide();
				$('#worm_sizediv').hide();
			}
			if (worm.flag == false) {
				$('.wormdiv').hide();
			}
		}
	}
	//设置自动导入开关
	var setAutoImportSettings  =  function(settings){
		$('#timepointimportswitch').bootstrapSwitch('state',settings.autoscan_flag);
		$('#timepointallocateswitch').bootstrapSwitch('state',settings.allocate_flag);
	}

	var getWarningSettings = function(){
		var warningSettings = {};
		warningSettings.power = $('#noticeswitch').get(0).checked;
		warningSettings.type = $('select[name=noticetype]').val();
		if('1' == warningSettings.type){
			warningSettings.value = $('#warningpercent').val();
		}else{
			warningSettings.value = $('#warningsize').val();
		}
		if(storageType == 9 || storageType == 13){
			warningSettings.type = 2;
			warningSettings.value = $('#limitsize').val();
		}
		return warningSettings;
	}

	var getWormSettings = function(){
		if (!CONF.FUNCTIONS.includes('worm')) {
			// 没得worm，那么去掉
			return {
				power: false,
				type: 3,
				value: 0
			};
		}
		var wormSettings = {
			power: $('#wormswitch').get(0).checked,
			type: 3,
			value: 0
		};

		if (storageType == 9) {
			// 云存储
			// 判断下 azure、阿里云和腾讯云不支持worm开关
			if ($.inArray(vendor, [2, 3, 5]) !== -1) {
				wormSettings.power = false;
			}
		} else {
			// 其它存储
			wormSettings.type = $('select[name=wormtype]').val();
			if('1' == wormSettings.type){
				wormSettings.value = $('#wormgpercent').val();
			} else if('2' == wormSettings.type) {
				wormSettings.value = $('#wormsize').val();
			} else {
				wormSettings.value = 0;
			}
		}

		return wormSettings;
	}

	//设置自动数据扫描时间间隔
	var setScanDataSettings =  function(settings){
		$('#datascanswitch').bootstrapSwitch('state', settings.scandata_flag);
		var scandata_value = settings.scandata_value;
		$('#cbrscantimevalue').val(scandata_value);
		//控制初始显示
		if(!settings.scandata_flag){
			$('.datascandiv').hide();
			$('#cbrscantime').spinner("value", 15);
			// $('#cbrscantime').spinner({value:15,step:5,min:1,max:9999999});
		}else{
			$('.datascandiv').show();
			$('#cbrscantime').spinner("value", scandata_value);
		}
	}

	var getScandataSettings =  function(){
		var scandataSettings = {};
		scandataSettings.power =  $('#datascanswitch').get(0).checked;
		scandataSettings.value  = $('#cbrscantimevalue').val();
		if(!scandataSettings.power){
			scandataSettings.value  =0;
		}
		return scandataSettings;
	}

	// 扫描异地备份系统
	var scanRemote = function (params) {
		Metronic.blockUI({target: '#modaldivedit',animate: true});
		var data = {remoteip: editData.remote_ip, remoteport: editData.remote_port, username: params.username, password: params.password}
		pAjaxRequest(data, "/api/v1/storages/remote", "GET", function (result) {
			if (result.code == 0) {
				// 表示扫描出来有东西
				var storageuuid = $('#storageuuid').val();
				pAjaxRequest(params, "/api/v1/storages/"+storageuuid, "PATCH", function (result) {
					Metronic.unblockUI('#modaldivedit');
					if (result.code == 0) {
						$('#remote_username').val('');
						$('#remote_password').val('');
						$('#yidiswitch').bootstrapSwitch('state', false);
						$('#cifsswitch').bootstrapSwitch('state', false);
						$('#dddbswitch').bootstrapSwitch('state', false);
						$('#modaldivedit').drawer('hide');
						$('#storagetable').bootstrapTable('refresh');
						UIToastr.showSuccess(LANG.UI_STORAGE_MODIFY, result.message);
					} else {
						UIToastr.showError(LANG.UI_STORAGE_MODIFY, result.message);
					}
				});
				return;
			} else {
				Metronic.unblockUI('#modaldivedit');
				UIToastr.showWarning(LANG.UI_STORAGE_MODIFY, result.message);
				return false;
			}
		});
	}

	// 共享网络存储前置条件
	var preCondition = function (){
		if ($.inArray(storageType, [CONF.BD_STORAGE_TYPE.CIFS, CONF.BD_STORAGE_TYPE.NFS, CONF.BD_STORAGE_TYPE.CLOUD]) !== -1) {
			// CIFS 或nfs 云存储 需要判断挂载节点是否为空
			var node_list = $('#nodeselect2').selectpicker('val');
			if (node_list.length <= 0) {
				UIToastr.showInfo(LANG.UI_STORAGE_MODIFY, LANG.UI_NAS_MANAGE_AT_LEAST_ONE_NODE);
				return;
			}

			// 组装节点uuid对应在线的数组
			var nodeArr = [];
			for (var k in nodeList) {
				nodeArr[nodeList[k].node_uuid] = nodeList[k].online_flag;
			}

			var showTips = false;

			let old_uuid = editData.mount_uuid_list;
			for (var k in old_uuid) {
				if ($.inArray(old_uuid[k], node_list) === -1) {
					// 如果有任务关联中，那么只能增加节点，而不能更改节点
					if (editData.in_task) {
						// 旧的有不在当前选中的，那么阻止并提示
						UIToastr.showInfo(LANG.UI_STORAGE_MODIFY, LANG.UI_STORAGE_ADD_NODE_TIPS2);
						return;
					}
					// 如果解挂离线的节点有提示
					if (!nodeArr[old_uuid[k]]) {
						showTips = true;
					}
				}
			}

			if (showTips) {
				bootbox.confirm({
					title: LANG.UI_PUBLIC_TIPS,
					message: LANG.UI_STORAGE_SHARE_REMOVE_MOUNT_TIPS,
					callback: debounce(function (r) {
						if (!r) return;
						editStorageSubmit();
					},300)
				})
				return;
			}
		}

		editStorageSubmit();
	}

	//修改存储
	var editStorageSubmit = function(){
		var alarmType = $("select[name=noticetype]").val();
		var alarmSize = parseInt($("#warningsize").val());
		var params = {};
		var node_list = [];
		//如果不是华为CBR存储，则需要判断告警阈值
		if(storageType != 12){
			if ($.inArray(storageType, [CONF.BD_STORAGE_TYPE.CIFS, CONF.BD_STORAGE_TYPE.NFS, CONF.BD_STORAGE_TYPE.CLOUD]) !== -1) {
				// CIFS 或nfs 云存储 需要判断挂载节点是否为空
				node_list = $('#nodeselect2').selectpicker('val');
			}
			if(alarmType ==2 && realsize !=0 && storageType != 9 && alarmSize >= realsize){
				UIToastr.showInfo(LANG.UI_STORAGE_MODIFY,LANG.UI_STORAGE_MODIFY_TIPS3);
				return;
			}
			var limitSize = parseInt($("#limitsize").val());
			if(usesize != 0 && storageType == 9 && limitSize < usesize){
				// 云存储不限制大小,更改存储的时候，需要判断已经使用的存储，不能小于已使用的
				UIToastr.showInfo(LANG.UI_STORAGE_MODIFY,LANG.UI_STORAGR_MODIFY_TIPS5);
				return;
			}

			var usemode = editData.use_mode;
			/*if($('#backupCheck').is(':checked')){
				usemode = 1;
			}else if($('#copyCheck').is(':checked')){
				usemode = 2;
			}else if($('#archiveCheck').is(':checked')){
				usemode = 3;
			}else if(!$('#backupCheck').is(':checked') && !$('#copyCheck').is(':checked') && !$('#archiveCheck').is(':checked')){
				UIToastr.showWarning(LANG.UI_STORAGE_USE_MODE_SELECT, LANG.UI_STORAGE_USE_MODE_SELECT_TIPS);
				return false;
			}*/
			params = {storage_name:$('#storagename').val(),storageuuid:$('#storageuuid').val(), storagetype: storageType,
				warning_setting:getWarningSettings(), usemode: usemode,auto_scan_flag:false,auto_assign_flag:false, node_list:node_list
				/*auto_scan_flag:$('#timepointimportswitch').get(0).checked, auto_assign_flag:$('#timepointallocateswitch').get(0).checked*/};

			if (storageType == 8) {
				var username = $('#remote_username').val();
				var password = $('#remote_password').val();
				// 异地备份系统，判断是否更改了用户密码
				if ($('#yidiswitch').is(':checked') && (username == '' || password == '')) {
					var msg = '';
					if (username == '') {
						msg = LANG.UI_ISCSI_CHAT_USERNAME_TIPS
					} else {
						msg = LANG.UI_ISCSI_CHAT_PASSWORD_TIPS
					}
					UIToastr.showWarning(LANG.UI_STORAGE_MODIFY, msg);
					return false;
				}
				if ($('#yidiswitch').is(':checked')) {
					params.remote_flag = true;
					params.username = username;
					params.password = btoa(password);
					// 校验账号密码是否正确
					return scanRemote(params);
				}
			}

			if (storageType == 7) {
				var username = $('#cifs_username').val();
				var password = $('#cifs_password').val();
				// cifs系统，判断是否更改了用户密码
				if ($('#cifsswitch').is(':checked') && (username == '' || password == '')) {
					var msg = '';
					if (username == '') {
						msg = LANG.UI_ISCSI_CHAT_USERNAME_TIPS
					} else {
						msg = LANG.UI_ISCSI_CHAT_PASSWORD_TIPS
					}
					UIToastr.showWarning(LANG.UI_STORAGE_MODIFY, msg);
					return false;
				}
				if ($('#cifsswitch').is(':checked')) {
					params.username = username;
					params.password = btoa(password);
				}
				$('#cifs_username').val('');
				$('#cifs_password').val('');
			}

			if ($.inArray(storageType, [CONF.BD_STORAGE_TYPE.DDDB]) !== -1 && $('#dddbswitch').is(':checked')) {
				// dddb存储，开启了修改信息开关
				var username = $('#modaldivedit').find('input[name=username16]').val();
				var password = $('#modaldivedit').find('input[name=password16]').val();
				// 判断是否更改了用户密码
				if (username == '' || password == '') {
					var msg = '';
					if (username == '') {
						msg = LANG.UI_ISCSI_CHAT_USERNAME_TIPS
					} else {
						msg = LANG.UI_ISCSI_CHAT_PASSWORD_TIPS
					}
					UIToastr.showWarning(LANG.UI_STORAGE_MODIFY, msg);
					return false;
				}
				params.username = username;
				params.password = btoa(password);

				$('#modaldivedit').find('input[name=username16]').val('');
				$('#modaldivedit').find('input[name=password16]').val('');
			}
		}else{
			params = {storage_name:$('#storagename').val(),storageuuid:$('#storageuuid').val(), storagetype: storageType,
				scandata_settings:getScandataSettings(), auto_scan_flag:false,auto_assign_flag:false, node_list:node_list};
		}
		params.worm_setting = getWormSettings();

		if((params.storagetype ==9 || params.storagetype ==13) && (params.warning_setting.value == 0 || params.warning_setting.value =='')){
			UIToastr.showWarning(LANG.UI_STORAGE_MODIFY, LANG.UI_STORAGR_MODIFY_TIPS4);
			return false;
		}
		if(params.storagetype == 12 && params.scandata_settings.power && (params.scandata_settings.value == 0 || params.scandata_settings.value =='')){
			UIToastr.showWarning(LANG.UI_STORAGE_SCAN_DATA_INTERVAL,LANG.UI_STORAGE_SCAN_DATA_INTERVAL_TIPS);
			return false;
		}
		// 云存储最大上限是 8388608
		if (params.storagetype ==9 && params.warning_setting.value > 8388608) {
			UIToastr.showInfo(LANG.UI_STORAGE_ADD, LANG.UI_TENANT_CAPACITY_MAX_NUM);
			return false;
		}
		Metronic.blockUI({target: '#modaldivedit',animate: true});
		var storageuuid = $('#storageuuid').val();
		pAjaxRequest(params, "/api/v1/storages/"+storageuuid, "PATCH", function (result) {
			Metronic.unblockUI('#modaldivedit');
			if (result.code == 0) {
				$('#modaldivedit').drawer('hide');
				$('#storagetable').bootstrapTable('refresh');
				UIToastr.showSuccess(LANG.UI_STORAGE_MODIFY, result.message);
			} else {
				UIToastr.showError(LANG.UI_STORAGE_MODIFY, result.message);
			}
		});

	}

	// 手动同步数据
	var syncStorageCheck = function (){
		var select = table.bootstrapTable('getSelections');
		if(!select.length){
			return tipSyncStorage();
		}
		// 如有备份任务正在运行，弹框提示：有备份任务正在运行无法进行存储同步，请先停止或等待任务完成后，再同步存储
		// 如无备份任务运行，弹出提示框，存储同步期间无法运行备份任务
		bootbox.confirm({
			title: LANG.UI_VCENTER_SYNC,
			message: LANG.UI_STORAGE_SYNC_TIPS,
			callback: debounce(function(r) {
				if(!r) return;
				Metronic.blockUI({target: '#storage_list_div',animate: true});
				var data = {};
				data.storage_uuid = [select[0].storage_uuid];
				pAjaxRequest(data, "/api/v1/storages/hand_sync", "POST", function (result) {
					Metronic.unblockUI('#storage_list_div');
					if (result.code == 0 || result.code == 200) {
						UIToastr.showSuccess(LANG.UI_VCENTER_SYNC, result.message);
					} else {
						UIToastr.showWarning(LANG.UI_VCENTER_SYNC, result.message);
					}
				});
			}, 300)
		});
	}

	//删除存储检查
	var deleteStorageCheck = function(){
		var select = table.bootstrapTable('getSelections');
		if(!select.length){
			return tipDeleteStorage();
		}
		if (select[0].storage_type == 10) {
			return UIToastr.showError(LANG.UI_STORAGE_DELETE, LANG.UI_STORAGE_DELETE_TAPE_TIP);
		}
		bootbox.confirm({
			title: LANG.UI_STORAGE_DELETE,
			message: LANG.UI_STORAGE_DELETE_TIPS2,
			callback: debounce(function(r) {
				if(!r) return;
				var data = {};
				data.uuids = [select[0].storage_uuid];
				Metronic.blockUI({target: '#storagetable',animate: true});
				pAjaxRequest(data, "/api/v1/storages", "DELETE", function (result) {
					Metronic.unblockUI('#storagetable');
					if(result.data.hasOwnProperty('info') && result.data.info != ''){
						//如果有备份点信息或任务依赖
						initModal(result.data.info);
						return;
					}
					if (result.code == 0) {
						expandIndex = null
						table.bootstrapTable('uncheckAll');
						table.bootstrapTable('refresh');
						UIToastr.showSuccess(LANG.UI_STORAGE_DELETE, result.message);
					} else {
						UIToastr.showError(LANG.UI_STORAGE_DELETE, result.message);
					}
				});

			}, 300)
		});
	}

	//删除存储
	var deleteStorage = function(){
		var select = table.bootstrapTable('getSelections');
		var data = {};
		data.uuids = [select[0].storage_uuid];

		Metronic.blockUI({target: '#modaldiv',animate: true});
		pAjaxRequest(data, "/api/v1/storages/confirm", "DELETE", function (result) {
			Metronic.unblockUI('#modaldiv');
			if (result.code == 0) {
				$('#modaldiv').modal('hide');
				expandIndex = null
				table.bootstrapTable('uncheckAll');
				table.bootstrapTable('refresh');
				UIToastr.showSuccess(LANG.UI_STORAGE_DELETE, result.message);
			} else {
				UIToastr.showError(LANG.UI_STORAGE_DELETE, result.message);
			}
		});

	}

	//设置自动导入时间点配置
	var setImportTimeConf =  function(settings){
		var autoimportValue = settings;
		$('#autoimportValue').val(autoimportValue);
		$('#autoimport').spinner("value", autoimportValue);
		$('.importtimediv').show();
		$('#importModal').modal({'width':'800px', 'height':'150px'});
	}

	//点击手动导入时间点
	var manualImportTime  = function(){
		var select = table.bootstrapTable('getSelections');
		if(!select.length){
			return tipEditStorage();
		}

		var data = {};
		data.uuids = [select[0].storage_uuid];
		data = JSON.stringify(data);
		Metronic.blockUI({target: '#storagetable',animate: true});
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'manualImportTime',p:data}, function(d){
			Metronic.unblockUI('#storagetable');
			if(OPREL(d)){
				table.bootstrapTable('uncheckAll');
				table.bootstrapTable('refresh');
			}
		});
	}

	//点击自动导入时间点配置
	var autoImportTimeConf  = function(){
		pAjaxRequest({}, "/api/v1/storages/autotime", "GET", function (result) {
			if (result.code == 0) {
				setImportTimeConf(result.data.value);
			} else {
				UIToastr.showError(LANG.UI_PALTFORM_STORAGE_TIMEPOINT_CONFIG, result.message);
			}
		});
	}

	//手动导入时间点配置提交按钮
	var importSubmit =  function(){
		var value  = $('#autoimportValue').val();
		Metronic.blockUI({target: '#importModal',animate: true});
		pAjaxRequest({value:value}, "/api/v1/storages/autotime", "POST", function (result) {
			Metronic.unblockUI('#importModal');
			if (result.code == 0) {
				UIToastr.showSuccess(LANG.UI_STORAGE_CONFIG_AUTO_IMPORT_INTERVAL_SUCCESS,LANG.UI_STORAGE_CONFIG_AUTO_IMPORT_INTERVAL_SUCCESS_TIPS);
				$('#importModal').modal('hide');
			} else {
				UIToastr.showError(LANG.UI_STORAGE_CONFIG_AUTO_IMPORT_INTERVAL_SUCCESS, result.message);
			}
		});
	}

	//初始化MODAL
	var initModal = function(data){
		$('#timepointcount').html(data.timepoint_count);
		$('#timepointsize').html(data.timepoint_size);
		$('#taskcount').html(data.task_count);
		var html = '';
		if(data.storageType == CONF.BD_STORAGE_TYPE.HUAWEICBR){
			//如果是华为CBR
			if(data.tasks.length <= 10){ //
				for(var i=0; i<data.tasks.length; i++){
					html += data.tasks[i] + "<br>";
				}
			}else{
				for(var i=0; i<10; i++){
					html += data.tasks[i] + "<br>";
				}
				//后面的用...代替
				html += '...';
			}
		}else{
			for(var i=0; i<data.tasks.length; i++){
				html += data.tasks[i] + "<br>";
			}
		}

		if(0 == data.tasks.length){
			html = '--';
		}
		$('#taskname').html(html);
		$('#modaldiv').modal({
			'width': '600px'
		});
	}

	//初始化所有备份节点
	var initNodeSelect = function(){
		pAjaxRequest({offset:0,limit:100,node_function: 2}, "/api/v1/nodes/", "GET", function (result) {
			var data = result.data.rows;
			nodeList = data;
			var nodeSelect = $('#nodeSelect');
			nodeSelect.empty();
			var option = $("<option>").text(LANG.UI_SEARCH_ALL_NODE).val('0');
			nodeSelect.append(option);
			for(var i=0; i<data.length; i++){
				var name = getNodeName(data[i].ip, data[i].node_nickname, data[i].host_name, data[i].online_flag);
				option = $("<option>").text(name).val(data[i].node_uuid);
				nodeSelect.append(option);
			}
			nodeSelect.val('0');

			var nodeselect2 = $('#nodeselect2');
			nodeselect2.empty();
			for (var i = 0; i < data.length; i++) {
				var name = getNodeName(data[i].ip, data[i].node_nickname, data[i].host_name, data[i].online_flag);
				var option = $("<option>").text(name).val(data[i].node_uuid).data('online_flag', data[i].online_flag);
				nodeselect2.append(option);
			}
			nodeselect2.selectpicker('refresh');

		});
	}

	var getNodeName = function (ip, nickname = '', hostname = '', online = true){
		let name = '';
		if (ip == nickname || (nickname == '')) {
			name = hostname;
		} else {
			name = nickname;
		}
		if (!online) {
			name = '(' + LANG.UI_STORAGE_STATUS_OFFLINE + ')' + name;
		}
		return name + '(' + ip + ')';
	}

	function initTableHeight() {
		//拿到父窗口的高度
		var height;
		var panelH = window.innerHeight;

		height = panelH - 281;

		$("#storage_list_div .fixed-table-body").css({
			"height": height
		});
	}

	return {
		//main function to initiate the module
		init: function () {
			handleRecords();
			addListeners();
			initNodeSelect(); //初始化所有节点
			initTableHeight();
		}

	};

}();
jQuery(document).ready(function() {
	StorageManager.init();
});