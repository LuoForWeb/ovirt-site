/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2024-10-08 15:36:43
 * @LastEditTime: 2026-03-27 16:57:51
 * @Version: 2.0
 * @copyright: Copyright 2024 vinchin.com
 */
var backupData = function () {
	// 全局变量
	let changeHeightFlag = { 'task': false, 'item': false, 'point': false, 'volPoint': false, 'tag': false }; // 初始化表格高度标志
	let storageFlag = true; // 存储模块显示标志
	// 不同表格需要的参数，任务表格，对象表格，异地表格，时间点表格，实时表格
	const defaultParam = { module_type: [], storage_uuid: [], task_type: [], search: '', operation_status: [], status: [], sub_module_type: [] };
	let [taskParam, itemParam, pointParam, volParams] = [{ ...defaultParam }, { ...defaultParam }, { ...defaultParam }, { ...defaultParam }, { ...defaultParam }];
	let remoteParam = {}, data_verify_uuid = '';
	let taskGridFlag = true; // 任务视图标志
	let zTree = null;
	let initPointListFlag = false, initTaskListFlag = false, initItemListFlag = false; // 时间点表格初始化标志
	let initRemoteFlag = false; // 异地数据表格初始化标志
	let initTagFlag = false;
	let initVolFlag = false;
	let markInfo = {}; // 标记信息
	let _UserPassword = null;//账户密码
	let TIME_CHECK = []; // 时间表格 用于存联动勾选的时间点
	let initErrorFlag = false;
	// 时间轴默认加载的间隔
	let op_id = ''; // exchange数据同步操作id
	const _MORE_SIZE = 20; // 树节点过多，按20分页
	let TREE_SORT = 'total_size';
	let TREE_ORDER = 'desc';
	let copy_task_type = [CONF.TASK_TYPE.BACKUP_COPY, CONF.TASK_TYPE.ARCHIVE, CONF.TASK_TYPE.BACKUP_COPY_FETCH, CONF.TASK_TYPE.ARCHIVE_FETCH]
	const REAL_MODULE_KEY = [CONF.MODULE_TYPE.VOL_CDP, CONF.MODULE_TYPE.DB_CDP];
	const STORAGE_TYPE = {
		1: LANG.UI_VIRTUAL_LOCAL_DISK, // 本地磁盘
		2: LANG.UI_VIRTUAL_LOGICAL_VOLUME_LVM, // 逻辑卷LVM
		3: LANG.UI_VIRTUAL_LOCAL_PARTITION,// 本地分区
		4: LANG.UI_STORAGE_TYPE_DETAIL_FC, // Fibre Channel
		5: LANG.UI_STORAGE_TYPE_ISCSI, //iSCSI
		6: LANG.UI_STORAGE_TYPE_NFS, // NFS
		7: LANG.UI_STORAGE_TYPE_CIFS, // CIFS
		8: LANG.UI_STORAGE_TYPE_REMOTE, // 异地存储
		9: LANG.UI_VIRTUAL_CLOUD_STORAGE, // 云存储
		10: LANG.UI_STORAGE_TYPE_TAPE, // 磁带
		11: LANG.UI_STORAGE_TYPE_LOCAL_CATALOGUE, // 本地目录
		16: LANG.UI_STORAGE_TYPE_16,
	}
	const STORAGE_WARN = { // 存储阈值告警类型
		PERCENT: 1, // 按百分比警告
		SIZE: 2 // 按容量警告
	}
	// 节点、表格类型
	const TREE_TYPE = {
		TASK: "task", // 任务
		ITEM: "item", // 备份对象
		BATCH: "batch", // 时间点批量
		POINT: "point", // 时间点批量
		VOL_POINT: "vol_point", // 时间点
	}
	// 时间点类型图标
	const BACKUP_MODE_ICON = {
		1: 'vicon-wanquanbeifen1 icon-full_point', // 完备
		2: 'vicon-zengliangbeifen1 icon-increment_point', // 增备
		3: 'vicon-chayibeifen icon-different_point', // 差备
		4: 'vicon-beifenshijiandian icon-log_point', // 差备
		9: 'vicon-a-Group255 icon-p_increment_point', // 日志
	}
	// GFS保留标记类型
	const MARK_LIST = {
		'week': LANG.UI_BACKUP_DATA_GFS_RETENTION_WEEKLY,
		'month': LANG.UI_BACKUP_DATA_GFS_RETENTION_MONTHLY,
		'year': LANG.UI_BACKUP_DATA_GFS_RETENTION_YEARLY,
	}
	// 操作列表
	const OPERATION_LIST = {
		1: LANG.UI_SYSTEM_MONITOR_DETAILSE, // 详情
		2: LANG.UI_PUBLIC_REMARK, // 备注
		3: LANG.UI_BACKUP_DATA_REMARK, // 标记
		4: LANG.UI_PUBLIC_DELETE, // 删除
		5: LANG.UI_BACKUP_DATA_DETAIL_ACTION_WORM, // worm保护
		6: LANG.UI_VISUAL_RECOVERY, // 恢复
		7: LANG.UI_VISUAL_INSTANT_NAME, // 瞬时恢复
		8: LANG.UI_VISUAL_RECOVERY_GRAIN, // 细粒度恢复
		9: LANG.UI_FILE_CROSS_RESTORE, // 跨平台恢复
		10: LANG.UI_MICROSOFT365_SYNC, // 同步
		11: LANG.UI_PUBLIC_TAKEOVER, // 接管
	}
	// 操作对应图标
	const OPERATION_ICON = {
		1: 'vicon-tenant-detail',
		2: 'vicon-a-Editbianji1',
		3: 'vicon-ge_sign',
		4: 'vicon-a-Deleteshanchu2',
		5: 'vicon-anquancelve',
		6: 'vicon-huifu1',
		7: 'vicon-vminstantrecover',
		8: 'viconfont vicon-a-xiliduhuifu1',
		9: 'vicon-kuapingtaihuifu',
		10: 'vicon-ge_refresh',
		11: 'vicon-vol_cdp_takeover',
	}
	// 虚拟机、整机支持病毒扫描
	const MODULE_SUPPORT_VIRUS = [CONF.MODULE_TYPE.VM, CONF.MODULE_TYPE.OS, CONF.MODULE_TYPE.VOL_CDP];
	// 实时模块不支持完整性校验
	const MODULE_NOT_SUPPORT_INTEGRITY = [CONF.MODULE_TYPE.VOL_CDP, CONF.MODULE_TYPE.DB_CDP];

	// --------------初始化页面 start ---------
	// 初始化存储tab
	let initStorageTab = () => {
		// 渲染存储列表
		let allStorage = (res) => {
			let storageList = res.data.rows;
			let storageTab = $('.storage-tab-list');
			let html = '';
			storageTab.empty();
			storageList.forEach(storage => {
				// 存储类型
				const { storage_type: storageType, storage_nickname, total_size, total_size_value, free_size, free_size_value
					, storage_uuid, warning, flag, desc, config } = storage;
				const { type: warnType, value: warnValueStr } = warning;
				const warnValue = parseInt(warnValueStr.match(/\d+/)[0], 10); // 警告阈值大小
				let remote_ip = '';
				let icon = "vicon-bendicunchu1"; // 存储图标
				let color = "green"; // 占用条颜色
				const usrPercent = ((total_size_value - free_size_value) / total_size_value * 100).toFixed(2); // 存储占用比例
				const freePercent = (free_size_value / total_size_value * 100).toFixed(2); // 可用存储比例
				// 判断存储状态，共享存储所有节点离线才显示离线
				let des = desc;
				let status_flag = flag;
				if (storageType === CONF.BD_STORAGE_TYPE.NFS || storageType === CONF.BD_STORAGE_TYPE.CIFS) {
					status_flag = 2;
					des = LANG.UI_STORAGE_STATUS_OFFLINE;
					for (const mountPointInfo of storage.mount_point_list) {
						if (parseInt(mountPointInfo.mount_status) === 1) {
							status_flag = 1;
							des = LANG.UI_STORAGE_STATUS_NORMAL;
							break;
						}
					}
				}
				let storage_name = status_flag != 1 ? storage_nickname + '(' + des + ')' : storage_nickname;
				if (storageType == CONF.BD_STORAGE_TYPE.CLOUD) { // 云存储图标
					icon = 'vicon-yuncunchu1';
				}
				// 异地数据删除时需要传remote_ip
				if (storageType == CONF.BD_STORAGE_TYPE.REMOTE) {
					remote_ip = config.remote_ip;
				}
				// 根据警告类型判断超过警告阈值，显示警告颜色
				if (((warnType == STORAGE_WARN.PERCENT && (freePercent < warnValue)) || warnType == STORAGE_WARN.SIZE && storage.free_size.match(/\d+/) < warnValue) && (storageType != CONF.BD_STORAGE_TYPE.CLOUD)) {
					color = "yellow";
				}
				html += `<div class="storage-tab-item ${status_flag != 1 && storageType == CONF.BD_STORAGE_TYPE.REMOTE ? 'disablebtn' : ''}" data-id="${storage_uuid}" data-type="${storageType}" data-name="${storage_name}" data-ip="${remote_ip ?? ''}">
							<div class="storage-tab-item-detail">
								<div class="storage-tab-item-icon">
									<i class="viconfont ${icon}"></i></span>
									<span class="item_detail-name">${storage_name}</span>
								</div>
								<span class="item_detail-type">${STORAGE_TYPE[storageType]}</span>
								<div class="item_detail-column">
									<div class="item_detail-column-${color}" style="width:${usrPercent}%;"></div>
								</div>
								<span class="item_detail-size">${free_size}${LANG.UI_OS_BACKUP_DISK_TIP}${total_size}</span>
							</div>
						</div>`
			});
			storageTab.append(html);
			// 初始化模块视图，选择存储下拉控件
			$('.module-tab_storage').mySelector({ storageList: storageList });
			// 选择存储刷新表格
			$('.mySelector-option_li').on('click', function (e) {
				let id = $(this).data('id');
				let array = id ? [id] : [];
				let type = $(this).data('type');
				// 初始化异地数据
				if (type == CONF.BD_STORAGE_TYPE.REMOTE) {
					remoteParam.storage_uuid = id;
					remoteParam.remote_ip = $(this).attr('data-ip');
					initRemoteTable(remoteParam);
					$('.local-tab').hide();
					$('.remote-tab').show();
				} else {
					// 刷新本地列表
					refreshTable({ storage_uuid: array });
					$('.local-tab').show();
					$('.remote-tab').hide();
				}
			});
		}
		// 获取所有存储
		pAjaxRequest({ offset: 0, limit: 100, source_type: 1 }, "/api/v1/storages", "GET", allStorage, true);
	};
	/**
	 * 根据授权生成模块列表
	 * @param {object} module 模块类型 定时模块，实时模块
	 * @param {object} permission 授权信息
	 * @returns 
	 */
	let getModuleItem = (group, permission, backupFlag = true) => {
		let html = '';
		for (const module of group) {
			let { id, type, name, icon, sub_type } = module;
			let type_info = '';
			// 非定时备份模块
			if (!backupFlag) {
				if (type == CONF.MODULE_TYPE.VOL_CDP) {
					let { storage_location, dev_type, task_type } = module;
					type_info = `data-storage_location="${storage_location}" data-dev_type="${dev_type}" data-task_type="${task_type}"`;
				} else {
					type_info = `data-task_type="${task_type}"`;
				}
			}
			// 判断权限
			if (permission.includes(id)) {
				html += `<div class="module-tab-item" data-id="${type}" data-sub_module="${sub_type}" ${type_info}>
						<div class="module-tab-item-icon">
							<i class="viconfont ${icon}"></i>
						</div>
						<div class="module-tab-item-detail">
							<span class="item_detail-name">${name}</span>
						</div>
					</div>`
			}
		}
		return html;
	}
	// 初始化模块tab
	let initModuleTab = () => {
		let PERMISSION = CONF.PERMISSION;
		// 全局观察者只有查看权限未获得模块授权，需要显示所有模块类型
		if (CONF.PERMISSION.includes('global_observer')) {
			PERMISSION = CONF.GLOBAL_OBSERVER_CONFIG;
		}
		let html = '';
		let moduleTab = $('.module-tab');
		// 判断是否有备份
		let showBackup = SERVICE_BACKUP.some(module => PERMISSION.includes(module.id));
		// 判断是否有连续数据保护
		let showCdp = SERVICE_CDP.some(module => PERMISSION.includes(module.id));
		// 判断是否有数据复制
		let showReplication = SERVICE_REPLICATION.some(module => PERMISSION.includes(module.id));
		// 渲染备份业务模块
		if (showBackup) {
			html += `<div class="module-tab-fixed">
						<div class="module-tab-label tab-fixed-label">${LANG.UI_BACKUP_DATA_LABEL_SERVICE_BACKUP}</div>
						${getModuleItem(SERVICE_BACKUP, PERMISSION)}
					</div>`
		}
		// 渲染数据复制业务模块
		if (showReplication) {
			let service_replication = SERVICE_REPLICATION.filter(item => item.id !== 'file_copy_protect');
			html += `<div class="module-tab-real">
						<div class="module-tab-label tab-fixed-label">${LANG.UI_BACKUP_DATA_LABEL_SERVICE_REPLICATION}</div>
						${getModuleItem(service_replication, PERMISSION, false)}
					</div>`
		}
		// 渲染连续数据保护业务模块
		if (showCdp) {
			html += `<div class="module-tab-real">
						<div class="module-tab-label tab-fixed-label">${LANG.UI_BACKUP_DATA_LABEL_SERVICE_CDP}</div>
						${getModuleItem(SERVICE_CDP, PERMISSION, false)}
					</div>`
		}
		moduleTab.append(html);
	};
	// 初始化页面
	let initPage = () => {
		// 初始化存储tab
		initStorageTab();
		// 初始化模块tab
		initModuleTab();
	};
	// --------------初始化页面 end ---------


	// --------------全局监听事件 start ---------
	// 切换模块<->存储tab
	let switchTab = () => {
		let storageTab = $('.storage-tab');
		let moduleTab = $('.module-tab');
		let tabTitle = $('.switch-tab-name');
		let taskTableFilter = $('#taskTableToolbar .dropdown-filter-wrapper');
		let itemTableFilter = $('#itemTableToolbar .dropdown-filter-wrapper');
		taskParam = {}; itemParam = {}; pointParam = {}; remoteParam = {};
		// 切换模块存储显示隐藏和文字
		if (storageTab.hasClass('display-none')) { // 显示存储视图 隐藏模块视图
			storageTab.removeClass('display-none');
			moduleTab.addClass('display-none');
			tabTitle.html(LANG.UI_BACKUP_DATA_SWITCH_MODULE);
			// 存储视图显示 表格模块过滤选项
			taskTableFilter.myFilter({ showAbnormal: true });
			itemTableFilter.myFilter({ showAbnormal: true });
			$('.module-tab_storage').hide();
			storageFlag = true;
		} else {
			moduleTab.removeClass('display-none');
			storageTab.addClass('display-none');
			tabTitle.html(LANG.UI_BACKUP_DATA_SWITCH_STORAGE);
			// 模块视图隐藏 表格模块过滤选项
			taskTableFilter.myFilter({ showModule: false, showAbnormal: true });
			itemTableFilter.myFilter({ showModule: false, showAbnormal: true });
			$('.module-tab_storage').show();
			storageFlag = false;
		}
		// 过滤器的点击事件在插件刷新之后 需要重新绑定
		filterListener("task", true);
		filterListener("item");
	}
	/**
	 * 刷新表格
	 */
	let refreshTable = (param) => {
		Metronic.blockUI({ target: '#backup_data-content', animate: true });
		// 根据任务、对象视图显示刷新列表
		let activeTab = $('.task-item-tab.active');
		taskParam = { ...taskParam, ...param };
		if (activeTab.hasClass('task-tab')) {
			if (!initTaskListFlag) {
				initTaskTable();
			} else {
				// 刷新任务列表
				$('#taskTable').bootstrapTable('refresh');
			}
			modifyDelStyle('taskTable', 'deleteTaskPoint');
		}
		itemParam = { ...itemParam, ...param };
		if (activeTab.hasClass('item-tab')) {
			if (!initItemListFlag) {
				initItemTable();
			} else {
				// 刷新对象列表
				$('#itemTable').bootstrapTable('refresh');
			}
			modifyDelStyle('itemTable', 'deleteItemPoint');
		}
	};
	// 切换存储
	let chooseStorage = function () {
		$('.storage-tab-item').removeClass('active');
		$(this).addClass('active');
		// 传递数组，便于多选扩展
		let id = $(this).attr('data-id');
		let array = id ? [id] : [];
		let type = $(this).attr('data-type');
		// 初始化异地数据
		if (type == CONF.BD_STORAGE_TYPE.REMOTE) {
			remoteParam.storage_uuid = id;
			remoteParam.remote_ip = $(this).attr('data-ip');
			initRemoteTable(remoteParam);
			$('.local-tab').hide();
			$('.remote-tab').show();
		} else {
			// 刷新本地列表
			refreshTable({ storage_uuid: array });
			$('.local-tab').show();
			$('.remote-tab').hide();
		}
		// 同步存储视图和模块视图的存储选择信息
		$('.module-tab_storage .selected-option').html($(this).data('name'));
		$('.module-tab_storage .selected-option').attr('data-type', $(this).attr('data-type'));
		$('.module-tab_storage .selected-option').attr('data-id', $(this).attr('data-id'));
		$('.module-tab_storage .selected-option').attr('data-ip', $(this).attr('data-ip'));
	}
	// 切换模块
	let chooseModule = function () {
		let storage_type = parseInt($('.module-tab_storage .selected-option').attr('data-type'));
		let storage_uuid = $('.module-tab_storage .selected-option').attr('data-id');
		$('.module-tab-item').removeClass('active');
		$(this).addClass('active');
		let id = parseInt($(this).attr('data-id'));
		let sub_module = parseInt($(this).attr('data-sub_module')) ?? '';
		if (storage_type == CONF.BD_STORAGE_TYPE.REMOTE) {
			remoteParam.storage_uuid = storage_uuid ?? '';
			remoteParam.remote_ip = $('.module-tab_storage .selected-option').attr('data-ip');
			remoteParam.module_type = id;
			remoteParam.sub_module_type = sub_module ?? '';
			$('.local-tab').hide();
			$('.remote-tab').show();
			initRemoteTable(remoteParam);
		} else {
			// 传递数组，便于多选扩展
			let module = id ? [id] : [];
			let sub_module_type = [sub_module];
			let param = { module_type: module, sub_module_type: sub_module_type, storage_uuid: storage_uuid ? [storage_uuid] : [] };
			if (module == CONF.MODULE_TYPE.VOL_CDP) {
				param.storage_location = $(this).attr('data-storage_location');
				param.dev_type = $(this).attr('data-dev_type');
				param.task_type = [$(this).attr('data-task_type')];
			} else {
				param.dev_type = '';
				param.storage_location = '';
				param.task_type = [];
			}
			refreshTable(param);
			$('.local-tab').show();
			$('.remote-tab').hide();
		}
	}
	// 切换视图
	let changeItemTask = function () {
		$('.task-item-tab').removeClass('active');
		$(this).addClass('active');
		// 切换显示表格
		if ($('.task-tab').hasClass('active')) { // 任务表格
			$('.task-grid-container').removeClass('display-none');
			$('.item-grid-container').addClass('display-none');
			taskGridFlag = true;
		} else { // 对象表格
			$('.task-grid-container').addClass('display-none');
			$('.item-grid-container').removeClass('display-none');
			taskGridFlag = false;
			$('.point_grid-container').hide();
		}
		// 判断筛选是否显示模块筛选
		if (storageFlag) {
			$('.task-filter-div #moduleTypeFixed').show();
			$('.task-filter-div #moduleTypeReal').show();
		} else {
			$('.task-filter-div #moduleTypeFixed').hide();
			$('.task-filter-div #moduleTypeReal').hide();
		}
		// 清空详情信息
		$('#drawerCard').empty();
		// 清空详情的树节点
		emptyTree();
		// 根据任务、对象视图显示刷新列表
		let activeTab = $('.task-item-tab.active');
		if (activeTab.hasClass('task-tab')) {
			if (!initTaskListFlag) {
				Metronic.blockUI({ target: '#backup_data-content', animate: true });
				initTaskTable();
			} else {
				// 刷新任务列表
				$('#taskTable').bootstrapTable('refresh');
			}
		}
		if (activeTab.hasClass('item-tab')) {
			if (!initItemListFlag) {
				Metronic.blockUI({ target: '#backup_data-content', animate: true });
				initItemTable();
			} else {
				// 刷新对象列表
				$('#itemTable').bootstrapTable('refresh');
			}
		}
	}
	// 配置worm
	let configWorm = function () {
		let spanStr = $(this).find('span');
		let inputNum = $(this).find('input');
		// 隐藏文本 显示可编辑输入框
		spanStr.hide();
		inputNum.show();
		addInputFlag = true;
	}
	// 提交worm配置
	let submitWorm = function () {
		let spanStr = $(this).find('span');
		let latest_uuid = $('#latest_uuid').val();
		let full_uuid = $('#full_uuid').val();
		let num = parseInt($('#wormTimeBtn input').val());
		let oldNum = parseInt($('#wormTimeBtn span').attr('data-value'));
		// 天数不能为0
		if (!isNaN(num) && num <= 0 || isNaN(num)) {
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_WORM_TIPS, LANG.UI_BACKUP_DATA_WORM_TIPS_TIME_EMPTY)
			return false;
		}
		// 期限只能延长
		if (isNaN(parseInt(spanStr.text())) && !isNaN(oldNum) && num <= oldNum) {
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_WORM_TIPS, LANG.UI_BACKUP_DATA_WORM_TIPS_TIME_DIABLE)
			return false;
		}
		let extend_num = isNaN(parseInt(num - oldNum)) ? 0 : parseInt(num - oldNum)
		pAjaxRequest({ latest_uuid: latest_uuid, full_uuid: full_uuid, worm_set_time: num ?? 0, extend_time: extend_num }, "/api/v1/backup_data/points/worm", "GET", function (res) {
			operateResponseList(res);
			$('#wormSettingModal').modal('hide');
			$('#pointTable').bootstrapTable('refresh');
		});
	}
	// 点击事件回调
	let initListeners = () => {
		// 模块存储视图切换
		$('.module-storage-switch').on('click', switchTab)
		// 存储tab点击事件
		$('.storage-tab').on('click', '.storage-tab-item', chooseStorage);
		// 模块tab点击事件
		$('.module-tab').on('click', '.module-tab-item', chooseModule);
		// 任务、对象视图切换
		$('.task-item-switch').on('click', '.task-item-tab', changeItemTask);
		// 备注修改提交
		$('#mark_submit').on('click', (e) => {
			markSubmit(markInfo);
		});
		// 初始化勾选空样式
		$('.icheck').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
		});
		//点击过滤菜单外关闭过滤菜单
		$(document).on('click', function (e) {
			if ($(e.target).closest('.filterDiv').length > 0) {
			} else {
				// 关闭所有过滤弹框
				$('.filter-menu').removeClass('show');
				$('.filterButton').removeClass('filter-hover');
				$('.filterButton').removeClass('filter-active');
			};
		});
		// worm配置   点击可编辑保护期限
		$('#wormTimeBtn').on('click', configWorm);
		// 提交worm配置信息
		$('#setWormConfirm').on('click', submitWorm);
		// 删除提示之后，调整表格高度
		$('.remove-point-tip').on('click', () => {
			$('.point_grid-container').css('height', '100%')
		});
		// 删除提示之后，调整表格高度
		$('.remove-real-tip').on('click', () => {
			$('.real_grid-container').css('height', '100%')
		});
		// 切换实时标签点
		$('a[data-toggle="tab"]').on('shown.bs.tab', function (e) {
			var tab = e.target;
			if (tab.hash == "#task_plot_li") {
				$('#tagTable').bootstrapTable('refresh');
			}
		});
		$('#wormNumInput').on('input', function () {
			let newVal = $(this).val();
			// 检查输入是否包含小数点
			if (/\. /.test(newVal)) {
				$(this).val(newVal.replace('.', ''));
			}
			if (!(/^\d+$/.test(newVal))) {
				// 使用正则表达式替换所有非数字字符
				$(this).val(newVal.replace(/[^0-9]/g, ''));
			}
		})
	};
	// --------------全局监听事件 end ---------

	// --------------动态生成表格元素 start ---------
	/**
	 * 生成批量删除按钮
	 * @param {string} id 删除按钮的id
	 * @returns 
	 */
	let getDeleteBtn = (id) => {
		if ($.inArray('p_backup_data_operate', CONF.PERMISSION_ARR) !== -1) {
			return `<div class="flex_center">
					<button class="btn b-btn viconfont vicon-a-Deleteshanchu1 brr2 mr12" id="${id}"></button>
				</div>`;

		} else {
			return '';
		}
	};
	/**
	 * 生成刷新按钮
	 * @param {string} id 刷新按钮id
	 * @returns 
	 */
	let getRefreshBtn = (id) => {
		return `<div class="btn-group">
					<button type="button"  id="${id}" class="dropdown-toggle btn-font btn-title btn-whitespace" style="width: auto;">
						<i class="viconfont vicon-biaogeshuaxin mr5"></i>${LANG.UI_CLIENT_REFRESH}
					</button>
				</div>`;
	};
	// --------------动态生成表格元素 end ---------


	// --------------表格初始化 start ------
	// 初始化任务表格
	let initTaskTable = () => {
		let options = {
			vin_url: "/api/v1/backup_data/jobs/list",
			vin_method: "POST",
			vin_params: function () {
				return taskParam;
			},
			parentIdField: 'pid', // 确定字段作为父级字段
			idField: 'task_uuid', // 确认字段作为id
			treeShowField: 'task_name', // 确认显示展开图标的字段
			showColumns: true,
			treegrid: true, // 使用树形格式
			tableArea: '.task-grid-container',
			showColumns: true,
			sortName: 'task_create_time',
			sortOrder: 'desc',
			toolbarId: '#taskTableToolbar',
			buttonsToolbar: '#taskTableToolbar .vin_taskTableToolbar',
			hideColumns: "sub_type_des,vcenter_name",
			changeHeightBtn: true, //改变高度按钮
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			searchInput: true, //搜索框
			dateRangePickerId: 'taskDateRange', // 日期选择器组件button id
			placeholder: LANG.UI_BACKUP_EXPORT_ENTER_TASKNAME,
			searchClass: 'searchTaskName', //搜索框类名
			searchSelector: '.searchTaskName', //表格选择使用该搜索框
			customTool: {
				beforeInput: getDeleteBtn('deleteTaskPoint'),
				afterInput: getRefreshBtn('refreshTask') + `<div class="btn-group dropdown-filter-wrapper"></div>
															<div class="btn-group">
																<div id="task_picker_wrapper" class="position-relative"></div>
															</div>`,
			},
			exportSettings: {
				showBuiltIn: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'], // 需要显示的默认导出项
				custom: [
					{
						label: LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL,
						class: 'export-all-excel'
					}
				]
			},
			// 勾选框联动效果
			onCheck: (row) => {
				modifyDelStyle('taskTable', 'deleteTaskPoint');
				checkSubRow(row, true);
			},
			onUncheck: (row) => {
				modifyDelStyle('taskTable', 'deleteTaskPoint');
				checkSubRow(row, false);
			},
			onUncheckAll: () => {
				modifyDelStyle('taskTable', 'deleteTaskPoint');
			},
			onCheckAll: () => {
				modifyDelStyle('taskTable', 'deleteTaskPoint');
			},
			PostBody: () => {
				// 渲染树形结构
				let columns = $('#taskTable').bootstrapTable('getOptions').columns;
				if (columns && columns[0][1].visible) {
					// 二次渲染展开的列，增加展开图标
					$('#taskTable').treegrid({
						treeColumn: 1,
						onChange: function () {
							$('#taskTable').bootstrapTable('resetView')
						}
					})
				}
				if (!initTaskListFlag) {
					$('#taskTable th[data-field="task_name"]').css('width', '15%');
					$('#taskTable th[data-field="module_type_des"]').css('width', '7.5%');
					$('#taskTable th[data-field="task_type_des"]').css('width', '7.5%');
					$('#taskTable th[data-field="total_size"]').css('width', '7.5%');
					$('#taskTable th[data-field="write_size"]').css('width', '7.5%');
					$('#taskTable th[data-field="point_num"]').css('width', '7.5%');
					$('#taskTable th[data-field="deleted_flag"]').css('width', '7.5%');
					$('#taskTable th[data-field="task_create_time"]').css('width', '7.5%');
					$('#taskTable th[data-field="sub_type_des"]').css('width', '7.5%');
					$('#taskTable th[data-field="vcenter_name"]').css('width', '7.5%');
					if (CONF.LANGUAGE !== "zh-cn" && CONF.LANGUAGE !== "zh-tw") {
						$('#taskTable th[data-field="point_num"]').css('width', '16.5%');
						$('#taskTable th[data-field="deleted_flag"]').css('width', '9%');
					}
					$('#taskTable .treegrid-expander').addClass('display-none-force');
					initTaskListFlag = true;
				}
				modifyDelStyle('taskTable', 'deleteTaskPoint');

				const exportOptions = {
					toolbarId: 'taskTableToolbar',
					url: '/api/v1/backup_data/jobs/list/export',
					fileName: LANG.UI_BACKUP_DATA_LABEL_TASK_LIST
				}

				// 监听导出全部数据
				exportAllTableData(exportOptions);
			},
			LoadSuccess: () => {
				Metronic.unblockUI('#backup_data-content');
			},
			LoadError: () => {
				Metronic.unblockUI('#backup_data-content');
			},
			columns: [
				{
					field: 'checked',
					checkbox: true,
					sortable: false,
					forceHide: true,
					width: 1,
					widthUnit: '%'
				},
				{
					field: 'task_name',
					title: LANG.UI_GLOBAL_STRATEGY_TASK_NAME,
					events: getSubTaskPoint,
					clickToSelect: false,
					formatter: function (value, row, index) {
						let html = '';
						if (row.hasChildren) {
							html = `<span class="treegrid-expander"></span><span class="treegrid-expander-collapsed root-collapsed get-child-node"></span><span title="${value}">${value}</span>`;
						} else {
							html = `<span title="${value}">${value}</span>`;
						}
						return html;
					}
				},
				{
					field: 'module_type_des',
					title: LANG.UI_GLOBAL_STRATEGY_MODULE_TYPE,
				},
				{
					field: 'task_type_des',
					title: LANG.UI_SEARCH_TASK_TYPE,
				},
				{
					field: 'total_size',
					title: LANG.UI_COPY_DATA_SIZE,
				},
				{
					field: 'write_size',
					title: LANG.UI_PUBLIC_REAL_SIZE,
				},
				{
					field: 'point_num',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_POINT_NUM,
				},
				{
					field: 'task_create_time',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_CREATE_TIME,
				},
				{
					field: 'sub_type_des',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_SUB_MODULE_TYPE,
					sortable: false,
				},
				{
					field: 'vcenter_name',
					title: LANG.UI_VCENTER_VCENTER,
					sortable: false,
				},
				{
					field: 'deleted_flag',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_DELETED,
					sortable: false,
					formatter: function (value, row, index) {
						let type = LANG.UI_PUBLIC_YES;
						if (!value) {
							type = LANG.UI_PUBLIC_NO;
						}
						return type;
					}
				},
				{
					field: 'detail',
					title: LANG.UI_ALARM_DETAILS,
					forceHide: true,
					width: 5,
					widthUnit: '%',
					sortable: false,
					events: operateEvents,
					clickToSelect: false,
					formatter: function (value, row, index) {
						if (value) {
							return '';
						} else {
							let html = `<span class="data-detail btn btn-light-primary">${LANG.UI_ALARM_DETAILS}</span>`
							return html;
						}
					}
				}
			],
		}
		$('#taskTable').baseTableConfig().init(options);
		// 初始化过滤器
		$('#taskTableToolbar .dropdown-filter-wrapper').myFilter({ showAbnormal: true });
		initTaskTablePicker();
		// 任务表格事件监听
		taskTableListener();

	};
	/**
	 * 任务表格时间选择器
	 */
	const initTaskTablePicker = () => {
		$(`#task_picker_wrapper`).initDateRangePicker({
			slotId: `task_picker_wrapper`, // 日期范围组件在父组件插槽位置的id
			dateRangePickerId: `taskDateRange`, // 选择器button id
			startTime: '', // 开始时间
			endTime: '', // 结束时间
			maxDate: 'now', // 最大可用时间
			timePicker: true, // 是否显示时间,时分
			timePickerSeconds: true, // 是否显示秒
			timePicker24Hour: true, // 是否是24小时制
			alwaysShowCalendars: true, // 是否总是显示日期选择
		});

		// 聚焦显示清空按钮
		$('.searchTaskName').on('focus', () => {
			$('#taskTableToolbar .clear').addClass('show');
			$('#taskTableToolbar .clear').removeClass('hide');
		});
		// 失去焦点隐藏清空按钮
		$('.searchTaskName').on('blur', () => {
			if ($('.searchTaskName').val() == '') {
				$('#taskTableToolbar .clear').removeClass('show');
				$('#taskTableToolbar .clear').addClass('hide');
			};
		});
	}
	/**
	 * 时间点表格时间选择器
	 */
	const initPointTablePicker = () => {
		$(`#point_picker_wrapper`).initDateRangePicker({
			slotId: `point_picker_wrapper`, // 日期范围组件在父组件插槽位置的id
			dateRangePickerId: `pointDateRange`, // 选择器button id
			startTime: '', // 开始时间
			endTime: '', // 结束时间
			maxDate: 'now', // 最大可用时间
			timePicker: true, // 是否显示时间,时分
			timePickerSeconds: true, // 是否显示秒
			timePicker24Hour: true, // 是否是24小时制
			alwaysShowCalendars: true, // 是否总是显示日期选择
		});
	}
	/**
	 * 对象表格时间选择器
	 */
	const initItemTablePicker = () => {
		$(`#item_picker_wrapper`).initDateRangePicker({
			slotId: `item_picker_wrapper`, // 日期范围组件在父组件插槽位置的id
			dateRangePickerId: `itemDateRange`, // 选择器button id
			startTime: '', // 开始时间
			endTime: '', // 结束时间
			maxDate: 'now', // 最大可用时间
			timePicker: true, // 是否显示时间,时分
			timePickerSeconds: true, // 是否显示秒
			timePicker24Hour: true, // 是否是24小时制
			alwaysShowCalendars: true, // 是否总是显示日期选择
		});
		// 聚焦显示清空按钮
		$('.searchItemName').on('focus', () => {
			$('#itemTableToolbar .clear').addClass('show');
			$('#itemTableToolbar .clear').removeClass('hide');
		});
		// 失去焦点隐藏清空按钮
		$('.searchItemName').on('blur', () => {
			if ($('.searchItemName').val() == '') {
				$('#itemTableToolbar .clear').removeClass('show');
				$('#itemTableToolbar .clear').addClass('hide');
			};
		});
	}

	let getSubTaskPoint = {
		'click .root-collapsed': function (e, value, row, index) {
			let checked = row.checked;
			let children = JSON.parse(row.children);
			let rows = $('#taskTable').bootstrapTable("getData");
			if (children.length > 0) {
				if (checked) {
					for (let i = 0; i < children.length; i++) {
						children[i].checked = true;
					}
				}
				$('#taskTable').bootstrapTable('append', children);
				// 获取依赖点后重新计算数量
				$('#taskTable .pagination-info').html(LANG.UI_TOOLS_TOTAL + (rows.length + children.length) + LANG.UI_BACKUP_DATA_TABLE_LABEL_PAGE_NUM);
			}
		}
	}

	// 初始化备份对象表格
	let initItemTable = () => {
		let options = {
			vin_url: "/api/v1/backup_data/items/list",
			vin_method: "POST",
			vin_params: function () {
				return itemParam;
			},
			tableArea: '.item-grid-container',
			showColumns: true,
			sortName: 'item_name',
			sortOrder: 'desc',
			toolbarId: '#itemTableToolbar',
			buttonsToolbar: '#itemTableToolbar .vin_itemTableToolbar',
			hideColumns: "sub_type_des,vcenter_name",
			changeHeightBtn: true, //改变高度按钮
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			searchInput: true, //搜索框
			dateRangePickerId: 'itemDateRange', // 日期选择器组件button id
			placeholder: LANG.UI_BACKUP_DATA_TABLE_SEARCH_BY_NAME,
			searchClass: 'searchItemName', //搜索框类名
			searchSelector: '.searchItemName', //表格选择使用该搜索框
			customTool: {
				beforeInput: getDeleteBtn('deleteItemPoint'),
				afterInput: getRefreshBtn('refreshItem') + `<div class="btn-group dropdown-filter-wrapper"></div>
															<div class="btn-group">
																<div id="item_picker_wrapper" class="position-relative"></div>
															</div>`,
			},
			exportSettings: {
				showBuiltIn: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'], // 需要显示的默认导出项
				custom: [
					{
						label: LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL,
						class: 'export-all-excel'
					}
				]
			},
			// 勾选框联动效果
			onCheck: () => {
				modifyDelStyle('itemTable', 'deleteItemPoint');
			},
			onUncheck: () => {
				modifyDelStyle('itemTable', 'deleteItemPoint');
			},
			onUncheckAll: () => {
				modifyDelStyle('itemTable', 'deleteItemPoint');
			},
			onCheckAll: () => {
				modifyDelStyle('itemTable', 'deleteItemPoint');
			},
			PostBody: () => {
				if (!initItemListFlag) {
					$('#itemTable th[data-field="item_name"]').css('width', '15%');
					$('#itemTable th[data-field="module_type_des"]').css('width', '7.5%');
					$('#itemTable th[data-field="task_type_des"]').css('width', '7.5%');
					$('#itemTable th[data-field="data_size"]').css('width', '7.5%');
					$('#itemTable th[data-field="write_size"]').css('width', '7.5%');
					$('#itemTable th[data-field="point_num"]').css('width', '7.5%');
					$('#itemTable th[data-field="deleted_flag"]').css('width', '7.5%');
					$('#itemTable th[data-field="last_time"]').css('width', '7.5%');
					$('#itemTable th[data-field="ip"]').css('width', '7.5%');
					$('#itemTable th[data-field="sub_type_des"]').css('width', '7.5%');
					$('#itemTable th[data-field="vcenter_name"]').css('width', '7.5%');
					if (CONF.LANGUAGE !== "zh-cn" && CONF.LANGUAGE !== "zh-tw") {
						//英文版宽度需要设置 长一点
						$('#itemTable th[data-field="point_num"]').css('width', '16%');
						$('#itemTable th[data-field="detail"]').css('width', '7%');
					}
					initItemListFlag = true;
				};
				modifyDelStyle('itemTable', 'deleteItemPoint');
			},
			LoadSuccess: () => {
				Metronic.unblockUI('#backup_data-content');
			},
			LoadError: () => {
				Metronic.unblockUI('#backup_data-content');
			},
			columns: [
				{
					field: 'checked',
					checkbox: true,
					sortable: false,
					forceHide: true,
					width: 1,
					widthUnit: '%'
				},
				{
					field: 'item_name',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_ITEM_NAME,
				},
				{
					field: 'module_type_des',
					title: LANG.UI_GLOBAL_STRATEGY_MODULE_TYPE,
					sortable: true,
				},
				{
					field: 'task_type_des',
					title: LANG.UI_SEARCH_TASK_TYPE,
				},
				{
					field: 'ip',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_CREATE_IP,
					sortable: false,
				},
				{
					field: 'data_size',
					title: LANG.UI_COPY_DATA_SIZE,
				},
				{
					field: 'write_size',
					title: LANG.UI_PUBLIC_REAL_SIZE,
				},
				{
					field: 'point_num',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_POINT_NUM,
				},
				{
					field: 'last_time',
					title: LANG.UI_REPORT_LAST_TIME,
					sortable: false,
				},
				{
					field: 'sub_type_des',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_SUB_MODULE_TYPE,
					sortable: false,
				},
				{
					field: 'vcenter_name',
					title: LANG.UI_VCENTER_VCENTER,
					sortable: false,
				},
				{
					field: 'detail',
					title: LANG.UI_ALARM_DETAILS,
					sortable: false,
					forceHide: true,
					width: 5,
					widthUnit: '%',
					events: operateEvents,
					clickToSelect: false,
					formatter: function (value, row, index) {
						let html = `<span class="data-detail btn btn-light-primary">${LANG.UI_ALARM_DETAILS}</span>`
						return html;
					}
				}
			],
		}
		// 初始化表格
		$('#itemTable').baseTableConfig().init(options);
		// 初始化过滤器
		$('#itemTableToolbar .dropdown-filter-wrapper').myFilter({ showAbnormal: true });
		initItemTablePicker();
		// 对象表格监听事件
		itemTableListener();
	};
	let initRemoteTable = (param) => {
		let options = {
			vin_url: "/api/v1/backup_data/remote/list",
			vin_method: "GET",
			vin_params: function () {
				return param;
			},
			tableArea: '.remote-grid-container',
			showColumns: true,
			sortName: 'write_size',
			sortOrder: 'desc',
			toolbarId: '#remoteTableToolbar',
			buttonsToolbar: '#remoteTableToolbar .vin_remoteTableToolbar',
			changeHeightBtn: true, //改变高度按钮
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			searchInput: true, //搜索框
			// dateTimePicker: {
			// 	id: 'dateRangeRemote'
			// }, //时间选择器// 勾选框联动效果
			placeholder: LANG.UI_BACKUP_DATA_TABLE_SEARCH_BY_NAME,
			searchClass: 'searchRemoteName', //搜索框类名
			searchSelector: '.searchRemoteName', //表格选择使用该搜索框
			customTool: {
				// beforeInput: getDeleteBtn('deleteRemote'),
				afterInput: getRefreshBtn('refreshRemote') + `<div class="btn-group dropdown-filter-wrapper"></div>`,
			},
			exportSettings: {
				showBuiltIn: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'], // 需要显示的默认导出项
				custom: [
					{
						label: LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL,
						class: 'export-all-excel'
					}
				]
			},
			PostBody: () => {
				$('#remoteTable th[data-field="checked"]').css('width', '2.5%');
				$('#remoteTable th[data-field="task_name"]').css('width', '15%');
				$('#remoteTable th[data-field="module_type_des"]').css('width', '7.5%');
				$('#remoteTable th[data-field="task_type_des"]').css('width', '7.5%');
				$('#remoteTable th[data-field="total_size"]').css('width', '7.5%');
				$('#remoteTable th[data-field="write_size"]').css('width', '7.5%');
				$('#remoteTable th[data-field="point_num"]').css('width', '7.5%');
				$('#remoteTable th[data-field="deleted_flag"]').css('width', '7.5%');
				$('#remoteTable th[data-field="task_create_time"]').css('width', '20%');
				$('#remoteTable th[data-field="detail"]').css('width', '5%');

				const exportOptions = {
					toolbarId: 'remoteTableToolbar',
					url: '/api/v1/backup_data/remote/list/export',
					fileName: LANG.UI_BACKUP_DATA_LABEL_TASK_LIST
				}
				$('#remoteTableToolbar .search').hide();
				// 监听导出全部数据
				exportAllTableData(exportOptions);
			},
			columns: [
				{
					field: 'checked',
					checkbox: true,
					sortable: false,
				},
				{
					field: 'task_name',
					title: LANG.UI_GLOBAL_STRATEGY_TASK_NAME,
				},
				{
					field: 'module_type_des',
					title: LANG.UI_GLOBAL_STRATEGY_MODULE_TYPE,
					sortable: true,
				},
				{
					field: 'task_type_des',
					title: LANG.UI_SEARCH_TASK_TYPE,
					sortable: false,
				},
				{
					field: 'total_size',
					title: LANG.UI_COPY_DATA_SIZE,
					sortable: true,
				},
				{
					field: 'write_size',
					title: LANG.UI_PUBLIC_REAL_SIZE,
					sortable: true,
				},
				{
					field: 'point_num',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_POINT_NUM,
					sortable: true,
				},
				{
					field: 'task_create_time',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_CREATE_TIME,
					sortable: true,
				},
				{
					field: 'deleted_flag',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_DELETED,
					sortable: false,
					formatter: function (value, row, index) {
						let type = LANG.UI_PUBLIC_YES;
						if (!value) {
							type = LANG.UI_PUBLIC_NO;
						}
						return type;
					}
				},
				{
					field: 'detail',
					title: LANG.UI_ALARM_DETAILS,
					sortable: false,
					events: operateEvents,
					clickToSelect: false,
					formatter: function (value, row, index) {
						let html = `<span class="data-detail btn btn-light-primary">${LANG.UI_ALARM_DETAILS}</span>`
						return html;
					}
				}
			],
		}
		// 初始化表格
		if (!initRemoteFlag) {
			$('#remoteTable').baseTableConfig().init(options);
			initRemoteFlag = true;
		} else {
			$('#remoteTable').bootstrapTable('refresh', { query: param });
			modifyDelStyle('remoteTable', 'deleteRemote');
		}
		// 对象表格监听事件
		remoteTableListener();
	}

	// -------------任务表格点击事件--start
	/**
	 * 过滤器提交事件监听
	 * @param {string} tableId 表格类型 Task、Item
	 * @param {object} param 表格参数 taskParam、itemParam
	 * @param {boolean} flag 却分是否是任务表格
	 */
	let filterListener = (tableId, flag) => {
		// 过滤确认
		$(`#${tableId}TableToolbar .filterSubmit`).on('click', (event) => {
			Metronic.blockUI({ target: '#backup_data-content', animate: true });
			const plugin = $(`#${tableId}TableToolbar .dropdown-filter-wrapper`).data('myFilter');
			let param = plugin.getFilter();
			updateParams(param, flag);
			$(`#${tableId}Table`).bootstrapTable('refresh');
			// 设置删除按钮可用 刷新状态
			modifyDelStyle(tableId, 'deleteItemPoint');
			modifyDelStyle(tableId, 'deleteTaskPoint');
			event.preventDefault();
		});
	}
	/**
	 * 更新任务和对象表格的参数
	 * @param {object} param 新参数
	 * @param {boolean} flag 任务表格标志
	 */
	let updateParams = (param, flag) => {
		if (flag) {
			taskParam = { ...taskParam, ...param };
		} else {
			itemParam = { ...itemParam, ...param };
		}
	}
	/**
	 * 任务、对象表格事件监听
	 * @param {string} id 表格类型 Task、Item
	 * @param {object} param 表格参数 taskParam、itemParam
	 * @param {boolean} flag 区分是否是任务表格
	 */
	let taskTableListener = () => {
		// 刷新表格
		$(`#refreshTask`).on('click', function () {
			Metronic.blockUI({ target: '#backup_data-content', animate: true });
			$(`#taskTable`).bootstrapTable('refresh');
			modifyDelStyle(`taskTable`, `deleteTaskPoint`);
		});
		// 改变表格高度
		$('.change_height').on('click', () => changeHeight('task'));
		$(`#taskTableToolbar .filterSubmit`).on('click', (event) => {
			Metronic.blockUI({ target: '#backup_data-content', animate: true });
			const plugin = $(`#taskTableToolbar .dropdown-filter-wrapper`).data('myFilter');
			let param = plugin.getFilter();
			taskParam = { ...taskParam, ...param };
			$(`#taskTable`).bootstrapTable('refresh');
			// 设置删除按钮可用 刷新状态
			modifyDelStyle('taskTable', 'deleteTaskPoint');
			event.preventDefault();
		});
		// 清空搜索字段
		$(`.taskTableclear`).on('click', () => {
			taskParam.search = '';
		})
		// 批量删除
		$(`#deleteTaskPoint`).on('click', () => {
			// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
			let user_uuid_arr = $.map($(`#taskTable`).bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: 1, user_uuid: user_uuid_arr.join(','), auth: 'data_manager' }, () => {
				deleteBatchPoint('task')
			});
		});
		// 搜索按钮点击搜索
		$(`#taskTableToolbar .search-btn`).on('click', () => {
			Metronic.blockUI({ target: '#backup_data-content', animate: true });
			taskParam.search = $(`.searchTaskName`).val();
			$(`#taskTable`).bootstrapTable('refresh');
			// 刷新按钮状态
			modifyDelStyle(`taskTable`, `deleteTaskPoint`);
		});
		// 回车搜索
		$(`.searchTaskName`).keypress(function (e) {
			if (e.which == 13) {
				// 空字段直接返回
				taskParam.search = $(this).val();
				// 刷新按钮状态
				modifyDelStyle(`taskTable`, `deleteTaskPoint`);
			}
		});

		// 监听表格 - 日期范围选择组件派发的数据，以更新表格
		window.$on(`taskDateRange-updateDateRangeEvent`, (data) => {
			// 记录选择的开始时间和结束时间，用于过滤搜索的联动
			// 更新参数
			taskParam.start_time = data.startTime;
			taskParam.end_time = data.endTime;
			$(`#taskTable`).bootstrapTable('refresh');
		});
	};
	let itemTableListener = () => {
		// 刷新表格
		$(`#refreshItem`).on('click', function () {
			Metronic.blockUI({ target: '#backup_data-content', animate: true });
			$(`#itemTable`).bootstrapTable('refresh', {
				query: itemParam
			});
			modifyDelStyle(`itemTable`, `deleteItemPoint`);
		});
		// 改变表格高度
		$('.change_height').on('click', () => changeHeight('item'));
		$(`#itemTableToolbar .filterSubmit`).on('click', (event) => {
			Metronic.blockUI({ target: '#backup_data-content', animate: true });
			const plugin = $(`#itemTableToolbar .dropdown-filter-wrapper`).data('myFilter');
			let param = plugin.getFilter();
			itemParam = { ...itemParam, ...param };
			$(`#itemTable`).bootstrapTable('refresh');
			// 设置删除按钮可用 刷新状态
			modifyDelStyle('itemTable', 'deleteItemPoint');
			event.preventDefault();
		});
		// 清空搜索字段
		$(`.itemTableclear`).on('click', () => {
			itemParam.search = '';
		})
		// 批量删除
		$(`#deleteItemPoint`).on('click', () => {
			// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
			let user_uuid_arr = $.map($(`#itemTable`).bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: 1, user_uuid: user_uuid_arr.join(','), auth: 'data_manager' }, () => {
				deleteBatchPoint('item')
			});
		});
		// 搜索按钮点击搜索
		$(`#itemTableToolbar .search-btn`).on('click', () => {
			Metronic.blockUI({ target: '#backup_data-content', animate: true });
			itemParam.search = $(`.searchItemName`).val();
			$(`#itemTable`).bootstrapTable('refresh');
			// 刷新按钮状态
			modifyDelStyle(`itemTable`, `deleteItemPoint`);
		});
		// 回车搜索
		$(`.searchItemName`).keypress(function (e) {
			if (e.which == 13) {
				// 空字段直接返回
				itemParam.search = $(this).val();
				// 刷新按钮状态
				modifyDelStyle(`itemTable`, `deleteItemPoint`);
			}
		});

		// 监听表格 - 日期范围选择组件派发的数据，以更新表格
		window.$on(`itemDateRange-updateDateRangeEvent`, (data) => {
			// 记录选择的开始时间和结束时间，用于过滤搜索的联动
			// 更新参数
			itemParam.start_time = data.startTime;
			itemParam.end_time = data.endTime;
			$(`#itemTable`).bootstrapTable('refresh');
		});
	};
	let remoteTableListener = () => {
		// 过滤确认
		$('#remoteTableToolbar .filterSubmit').on('click', (event) => {
			const plugin = $('#remoteTableToolbar .dropdown-filter-wrapper').data('myFilter');
			remoteParam = plugin.getFilter();
			$('#remoteTable').bootstrapTable('refresh');
			event.preventDefault();
		});
		// 刷新表格
		$('#refreshRemote').on('click', (event) => {
			$(`#remoteTable`).bootstrapTable('refresh');
		});
	};
	let operateEvents = {
		// 展开详情抽屉
		'click .data-detail': function (e, value, row, index) {
			TIME_CHECK = [];
			emptyTree();
			// 清空详情信息
			$('#drawerCard').empty();
			$('.point_grid-container').hide();
			// 异地和本地数据请求不同接口
			if (row.storage_type == CONF.BD_STORAGE_TYPE.REMOTE) {
				initRemoteDrawer(row);
				$('#pointListDrawer').drawer('toggle');
				Metronic.blockUI({ target: '.pointListBody', animate: true });
			} else {
				// 初始化抽屉页面
				initTaskDrawer(row.module_type, [row.task_type], row.task_uuid, row.item_uuid, row);
			}
		},
	}
	// -------------任务表格点击事件--end

	// -------------表格操作回调方法--start
	/**
	 * 改表表格高度
	 * @param {string} type 表格类型
	 */
	let changeHeight = (type) => {
		if (changeHeightFlag[type] == false) {
			changeHeightFlag[type] = true;
			$(`#${type}Table>tbody>tr>td`).css({
				'padding-top': '15.25px',
				'padding-bottom': '15.25px'
			})
			$(`#${type}TableToolbar .change_height i`).addClass('icon-auto-height2');
		} else if (changeHeightFlag[type] == true) {
			changeHeightFlag[type] = false
			$(`#${type}Table>tbody>tr>td`).css({
				'padding-top': '4.25px',
				'padding-bottom': '4.25px'
			})
			$(`#${type}TableToolbar .change_height i`).removeClass('icon-auto-height2');
		}
	}

	//初始化当前用户密码用于删除二次确认
	let initUserPassword = function () {
		pAjaxRequest({}, "/api/v1/users/password", "GET", function (res) {
			_UserPassword = res.data.password;
		}, true);
	}

	/**
	 * 获取选中项uuid
	 * @param {string} tableId 表格id
	 * @returns 
	 */
	let getTableTaskId = (tableId) => {//select = table id
		return $.map($(tableId).bootstrapTable('getSelections'), function (row) {
			return row.id;
		})
	}
	/**
	 * 删除行为：
	 * 1.删除某个任务：删除任务下的所有时间点，批量删除，js获取任务uuid，php向后端发送所有完备点
	 * 2.删除某个对象：删除当前对象下的所有时间点，批量删除，js获取对象uuid和任务uuid，向后端发送所有完备点
	 * 3.批量删除时间点：表格勾选限，勾选增备点会直接勾选整条链，删除整条链，向后端发送勾选链的完备点
	 * 3.单个删除：
	 * 	1.文件、虚拟机、操作系统模块：删除增备点、或者完备点，判断是否有依赖点、有则触发删除选择（1删除整条链，2.删除当前点并合并数据），无则删除整条链
	 *  2.其他模块，只能删除完备点，且不触发合并
	 * 删除单个时间点
	 * 如果是虚拟机和操作系统、文件，删除单个点可以选择删除类型，删除当前点合并，删除这个点后面的整条链
	 * @param {object} data 时间点信息
	 * @param {boolean} chain_delete_flag 差备链标志，默认删除整条链
	 */
	let deletePoint = (data, chain_delete_flag = false) => {
		// 需要显示删除选项的模块
		const SHOW_TYPE_MODULE = [CONF.MODULE_TYPE.VM, CONF.MODULE_TYPE.PRIVATE_CLOUD, CONF.MODULE_TYPE.PUBLIC_CLOUD, CONF.MODULE_TYPE.OS, CONF.MODULE_TYPE.FS, CONF.MODULE_TYPE.NAS];
		let selectFlag = false;
		// 虚拟机和操作系统]、文件拥有依赖点的完备点需要选择删除模式
		if ((SHOW_TYPE_MODULE.includes(data.module_type) && data.backup_mode == 1 && data.hasChildren) || chain_delete_flag) {
			selectFlag = true;
			tips = LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TIPS_POINT_FS;
		} else if (data.backup_mode == 1) {//完备点给出删除依赖提示
			tips = LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TIPS_DEPEND_POINT;
		} else {//非完备点给出删除点提示
			tips = LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TIPS_ONE_POINT;
		}
		// 二次确认
		deletePointConfirm(tips, data, false, selectFlag, chain_delete_flag);

	}
	/**
	 * 查询是否勾选差备链的完备点
	 * @returns { boolean }
	 */
	let hasDiffChainFullPoint = () => {
		let selectsRows = $('#pointTable').bootstrapTable('getSelections');
		for (let i = 0; i < selectsRows.length; i++) {
			if (selectsRows[i].backup_mode == 1 && selectsRows[i].diff_chain_flag) {
				return true;
			}
		}
		return false;
	}
	/**
	 * 批量删除时间点  默认是删除整条链的
	 * @param {string} type 表格类型
	 */
	let deleteBatchPoint = (type) => {
		let tips = LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TIPS_POINT;
		switch (type) {
			case TREE_TYPE.TASK:
				tips = LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TIPS_TASK;
				break;
			case TREE_TYPE.ITEM:
				tips = LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TIPS_ITEM;
				break;
			case TREE_TYPE.POINT:
				let delete_module = $('#point_table_module').val();
				// 文件模块提示差异点删除
				if ((delete_module == 3 || delete_module == 11) && hasDiffChainFullPoint()) {
					tips = LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TIPS_POINT_FS;
				} else {
					tips = LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TIPS_POINT;
				}
				break;
			case TREE_TYPE.VOL_POINT:
				tips = LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TIPS_POINT;
				break;
		}
		deletePointConfirm(tips, type, true, false);
	}
	/**
	 * 删除二次确认框
	 * @param {string} tips 提示信息
	 * @param {string} type 表格类型,删除时间点数据
	 * @param {boolean} batchFlag 批量删除标志
	 * @param {boolean} select 选择删除类型标志
	 * @param {boolean} chain_delete_flag 差备链标志，默认删除整条链
	 */
	let deletePointConfirm = (tips, type, batchFlag = false, select = false, chain_delete_flag = false) => {
		let deleteTypeSelect = '';
		let deleteTypeOptions = '';
		let deleteType = 2;
		// 虚拟机和操作系统拥有依赖点的完备点需要选择删除模式
		if (select) {
			deleteTypeSelect = `<span class="delete-tips-select display-none">${LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TIPS_EMPTY_MODE}</span>`;
			deleteTypeOptions = `<div class="delete-select-mode ${chain_delete_flag ? 'display-none' : ''}">
						<label>
							<input type="radio" name="selectType" class="icheck" value="1" checked>${LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TYPE_ALL}
						</label>
						<label>
							<input type="radio" name="selectType" class="icheck" value="2">${LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TYPE_MERGE}
						</label>
					</div>`;
		}
		// 二次删除确认框
		bootbox.dialog({
			title: `<div class="data_delete-title"><i class="viconfont vicon-a-Deleteshanchu2" style="margin-right:8px"></i>${LANG.UI_DATA_DELETE_TIMEPOINT}</div>`,
			message: `<div class="data_delete-body">
							<div class="delete-big-icon"><i class="viconfont vicon-a-Close-oneguanbi"></i></div>
							<div class="delete-tips">
								<span class="delete-tips-common">${tips}</span>
								${deleteTypeSelect}
							</div>
							${deleteTypeOptions}
						</div>`,
			buttons: {
				cancel: {
					label: LANG.UI_PUBLIC_CANCEL,
					className: 'btn btn-default',
					callback: function () {
						return;
					}
				},
				confirm: {
					label: LANG.UI_PUBLIC_CONFIRM,
					className: 'btn btn-primary',
					callback: function () {
						if (select) {
							deleteType = $('input[name=selectType]:checked').val();
						}
						// 密码匹配
						bootbox.dialog({
							title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES,
							// inputType: 'password',
							message: `<div class="bootbox-input-wrapper" style="position: relative;margin-bottom: 15px;">
										<input class="bootbox-input bootbox-input-password" type="password" autocomplete="off" style="border: 1px solid #E6E6E6;border-radius: 2px !important;height: 34px;width:100%;background-color: #FFFFFF">
										<button type="button" class="btn btn-link show-password-btn" style="position: absolute; right: 10px; top: 50%; transform: translateY(-50%); text-decoration: none;">
											<i class="viconfont vicon-a-lujing8232"></i>
										</button>
									</div>`,
							buttons: {
								cancel: {
									label: LANG.UI_PUBLIC_CANCEL,
									className: 'btn btn-default',
									callback: function () {
										return;
									}
								},
								confirm: {
									label: LANG.UI_PUBLIC_CONFIRM,
									className: 'btn btn-primary check-password-btn',
									callback: function () {
										let result = $('.bootbox-input-password').val();
										if (result == null) return;
										if (hex_md5(result) == _UserPassword) {
											// 批量删除和单点删除不同处理
											if (batchFlag) {
												submitBatchDelete(type);
											} else {
												submitDelete(type, deleteType);
											}
											return true;
										} else {
											$('.bootbox-input').css('border-color', "#a94442");
											$('.password-error').remove();
											let des = '<p class="password-error" style="margin-top:5px;color:#a94442;position:absolute">' + LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS + '</p>';
											$('.bootbox-input').after(des);
											return false;
										}
									}
								}
							},
						}).on('shown.bs.modal', function () {
							// 获取输入框和按钮
							let $input = $('.bootbox-input-password');
							let $btn = $('.show-password-btn');
							$input.focus();
							// 添加回车键事件监听器
							$input.on('keypress', function (e) {
								if (e.which == 13) { // 回车键的键码是13
									$('.check-password-btn').click(); // 触发确认按钮的点击事件
								}
							});
							// 添加点击事件监听器
							$btn.on('click', function () {
								let inputType = $input.attr('type');
								if (inputType === 'password') {
									$input.attr('type', 'text');
									$btn.find('i').removeClass('vicon-a-lujing8232').addClass('vicon-a-lianhe1');
								} else {
									$input.attr('type', 'password');
									$btn.find('i').removeClass('vicon-a-lianhe1').addClass('vicon-a-lujing8232');
								}
							});
						});
					}
				}
			},
		})

		$('.icheck').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
		});
	}
	// 提交批量删除
	let submitBatchDelete = (tableType) => {
		let data = { task_uuid: [], item: [], pointList: [], type: tableType, batchFlag: true };
		let src_data_deleted_flag = false;
		// 任务表格获取删除信息
		if (tableType === TREE_TYPE.TASK) {
			Metronic.blockUI({ target: '.task-grid-container', animate: true });
			data.task_uuid = getTableTaskId('#taskTable');
			data.abnormal_chain_flag = taskParam.abnormal_chain_flag;
			// 对象表格获取删除信息
		} else if (tableType === TREE_TYPE.ITEM) {
			Metronic.blockUI({ target: '.item-grid-container', animate: true });
			data.item = getSelectedItems('#itemTable');
			data.abnormal_chain_flag = itemParam.abnormal_chain_flag;
			// 时间嗲表格获取删除信息
		} else if (tableType === TREE_TYPE.POINT) {
			Metronic.blockUI({ target: '.point_grid-container', animate: true });
			let deleteList = getSelectedPoints('#pointTable');
			data.pointList = deleteList.list;
			src_data_deleted_flag = deleteList.src_data_deleted_flag;
			data.abnormal_chain_flag = pointParam.abnormal_chain_flag;
			// 整机实时获取删除信息
		} else if (tableType === TREE_TYPE.VOL_POINT) {
			Metronic.blockUI({ target: '.cdp_point_grid-content', animate: true });
			data.pointList = getSelectedPoints('#volPointTable', true);
			data.abnormal_chain_flag = pointParam.abnormal_chain_flag;
		}
		let deleteBack = (res) => {
			operateResponseList(res)
			// 解开所有的表格锁
			Metronic.unblockUI('.task-grid-container');
			Metronic.unblockUI('.item-grid-container');
			Metronic.unblockUI('.point_grid-container');
			Metronic.unblockUI('.cdp_point_grid-content');
			if (!res.success) {
				return false;
			}
			// 刷新表格
			$('#taskTable').bootstrapTable('refresh');
			$('#itemTable').bootstrapTable('refresh');
			// 刷新表格的删除按钮状态
			switch (tableType) {
				case TREE_TYPE.TASK:
					modifyDelStyle('taskTable', 'deleteTaskPoint');
					break;
				case TREE_TYPE.ITEM:
					modifyDelStyle('itemTable', 'deleteTaskPoint');
					break;
				case TREE_TYPE.POINT:
					$('#pointTable').bootstrapTable('refresh');
					modifyDelStyle('pointTable', 'deletePoint');
					break;
				case TREE_TYPE.VOL_POINT:
					$('#volPointTable').bootstrapTable('refresh');
					modifyDelStyle('volPointTable', 'deleteVolPoint');
					break;
			}
		}
		// 归档备份数据需要特殊提示
		if (src_data_deleted_flag) {
			bootbox.confirm({
				title: `<div class="data_delete-title"><i class="viconfont vicon-a-Deleteshanchu2" style="margin-right:8px"></i>${LANG.UI_DATA_DELETE_TIMEPOINT}</div>`,
				message: LANG.UI_BACKUP_DATA_ARCHIVE_DATA_TIPS2,
				callback: debounce(function (r) {
					if (!r) {
						// 解开所有的表格锁
						Metronic.unblockUI('.task-grid-container');
						Metronic.unblockUI('.item-grid-container');
						Metronic.unblockUI('.point_grid-container');
						Metronic.unblockUI('.cdp_point_grid-container');
						return;
					}
					pAjaxRequest(data, "/api/v1/backup_data/points", "DELETE", deleteBack, true);
				}, 300)
			});
		} else {
			pAjaxRequest(data, "/api/v1/backup_data/points", "DELETE", deleteBack, true);
		}
	}
	/**
	 * 获取删除对象信息
	 * @param {string} tableId 
	 * @returns 
	 */
	const getSelectedItems = (tableId) => {
		let selectsRows = $(tableId).bootstrapTable('getSelections');
		if (selectsRows.length === 0) return [];
		return selectsRows.map(element => ({
			item_uuid: element.item_uuid,
			module_type: element.module_type,
			task_type: element.task_type,
			sub_module_type: element.sub_module_type,
			db_name: element.db_name ?? '',
			instance_name: element.instance_name ?? '',
			db_cluster_uuid: element.db_cluster_uuid ?? '',
			db_type: element.db_type ?? '',
			db_agent_uuid: element.db_agent_uuid ?? '',
		}));
	};
	/**
	 * 获取删除时间点信息
	 * @param {string} tableId 
	 * @returns 
	 */
	const getSelectedPoints = (tableId, realFlag = false) => {
		let selectsRows = $(tableId).bootstrapTable('getSelections');
		if (selectsRows.length === 0) return [];
		let fullList = [], unFullList = [], excludeList = [], allList = [];
		let src_data_deleted_flag = false;
		if (realFlag) {
			selectsRows.map(element => {
				fullList.push({
					timepoint_uuid: element.timepoint_uuid,
					module_type: element.module_type,
					task_type: element.task_type,
					storage_uuid: element.storage_uuid,
					storage_type: element.storage_type,
					node_uuid: element.node_uuid,
					sub_type: element.sub_type,
					item_uuid: element.item_uuid,
					task_uuid: element.task_uuid,
					backup_mode: element.backup_mode,
					remote_ip: element.remote_ip ?? '',
					user_uuid: element.user_uuid,
				})
			});
			return { fullList, unFullList }
		}
		// 所有勾选的时间点uuid
		let selectedUUIDs = getTableTaskId(tableId);
		selectsRows.forEach(element => {
			// 完备点需要判断是否是整条链勾选
			if (element.backup_mode == 1) {
				// 获取所有的子节点
				let childRows = $(`.treegrid-parent-${element.id}`);
				let childUUIDs = [];
				// 获取所有子节点的uuid
				for (let i = 0; i < childRows.length; i++) {
					let IdClassStr = $(childRows[i]).attr('class').split(' ')[0];
					let uuid = IdClassStr.replace('treegrid-', '');
					childUUIDs.push(uuid);
				}
				// 判断勾选的时间点是否包含所有的子节点
				let allContained = childUUIDs.every(uuid => selectedUUIDs.includes(uuid));
				// 如果包含
				if (allContained || element.diff_chain_flag) {
					// 暂存所有子节点uuid
					excludeList.push(...childUUIDs);
					// 将完备点存入整链删除数组
					fullList.push({
						timepoint_uuid: element.timepoint_uuid,
						module_type: element.module_type,
						task_type: element.task_type,
						storage_uuid: element.storage_uuid,
						node_uuid: element.node_uuid,
						sub_type: element.sub_type,
						item_uuid: element.item_uuid,
						task_uuid: element.task_uuid,
						backup_mode: element.backup_mode,
						remote_ip: element.remote_ip ?? '',
						user_uuid: element.user_uuid,
						storage_type: element.storage_type,
					})
					// 不包含则将完备点存入删除合并的数组
				} else {
					allList.push({
						timepoint_uuid: element.timepoint_uuid,
						module_type: element.module_type,
						task_type: element.task_type,
						storage_uuid: element.storage_uuid,
						node_uuid: element.node_uuid,
						sub_type: element.sub_type,
						item_uuid: element.item_uuid,
						task_uuid: element.task_uuid,
						user_uuid: element.user_uuid,
						storage_type: element.storage_type,
					})
				}
				// 如果是非完备点则直接存入，删除合并的数组
			} else {
				allList.push({
					timepoint_uuid: element.timepoint_uuid,
					module_type: element.module_type,
					task_type: element.task_type,
					storage_uuid: element.storage_uuid,
					node_uuid: element.node_uuid,
					sub_type: element.sub_type,
					item_uuid: element.item_uuid,
					task_uuid: element.task_uuid,
					user_uuid: element.user_uuid,
					storage_type: element.storage_type,
				})
			}
			if (element.src_data_deleted_flag) {
				src_data_deleted_flag = true;
			}
			// 过滤整链删除的完备点的子节点，避免重复删除
			unFullList = allList.filter(el => !excludeList.includes(el.timepoint_uuid))
		});
		return {
			list: { fullList, unFullList },
			src_data_deleted_flag: src_data_deleted_flag
		};
	};
	/**
	 * 时间点单点删除操作
	 * @param {object} data 
	 * @param {int} type 删除类型 1批量删除 2.删除单个点并合并数据
	 */
	let submitDelete = (data, type) => {
		Metronic.blockUI({ target: '.point_grid-container', animate: true });
		let params = { pointList: {} };
		params.abnormal_chain_flag = pointParam.abnormal_chain_flag;
		let src_data_deleted_flag = false;
		// 删除单个点 #25782 数据库只能删链，前端op_code应为删链的op而非删点的op
		if (parseInt(type) == 1 || (parseInt(type) == 2 && ((data.module_type == CONF.MODULE_TYPE.DB && data.backup_mode == 1) || data.remote_flag))) {
			// 删除整条链
			let fullList = [{
				timepoint_uuid: data.timepoint_uuid,
				module_type: data.module_type,
				task_type: data.task_type,
				storage_uuid: data.storage_uuid,
				node_uuid: data.node_uuid,
				sub_type: data.sub_type,
				item_uuid: data.item_uuid,
				task_uuid: data.task_uuid,
				backup_mode: data.backup_mode,
				remote_ip: data.remote_ip ?? '',
			}];
			params.pointList.fullList = fullList;
			params.type = TREE_TYPE.POINT;
			params.batchFlag = true;
			params.task_uuid = data.task_uuid;
			src_data_deleted_flag = data.src_data_deleted_flag;
		} else {
			params.task_type = data.task_type;
			params.module_type = data.module_type;
			params.timepoint_uuid = data.timepoint_uuid;
			params.node_uuid = data.node_uuid;
			params.batchFlag = false;
			params.remote_ip = data.remote_ip ?? '';
			params.task_uuid = data.task_uuid;
			src_data_deleted_flag = data.src_data_deleted_flag;
		}
		let deleteBack = (res) => {
			Metronic.unblockUI('.point_grid-container');
			operateResponseList(res)
			if (!res.success) {
				return false;
			}
			// 刷新左侧树和时间点表格
			$('.name-asc').trigger('click');
			$('#pointTable').bootstrapTable('refresh');
			modifyDelStyle('pointTable', 'deletePoint');
		}
		// 归档备份数据需要特殊提示
		if (src_data_deleted_flag) {
			bootbox.confirm({
				title: `<div class="data_delete-title"><i class="viconfont vicon-a-Deleteshanchu2" style="margin-right:8px"></i>${LANG.UI_DATA_DELETE_TIMEPOINT}</div>`,
				message: LANG.UI_BACKUP_DATA_ARCHIVE_DATA_TIPS,
				callback: debounce(function (res) {
					if (!res) {
						Metronic.unblockUI('.point_grid-container');
						return
					};
					pAjaxRequest(params, "/api/v1/backup_data/points", "DELETE", deleteBack, true);
				}, 300)
			});
		} else {
			pAjaxRequest(params, "/api/v1/backup_data/points", "DELETE", deleteBack, true);
		}
	}
	// -------------备份对象表格操作回调方法--end

	// ----------------时间点列表抽屉--start
	/**
	 * 初始化任务卡片
	 * @param {object} data 任务信息 
	 * @param {boolean} realFlag 实时数据标志
	 */
	let initTaskCard = (data, realFlag = false) => {
		$('#point_table_module').val(data.module_type)
		let cardDiv = $('#drawerCard');
		$('#realDataTree').show();
		if (realFlag) {
			cardDiv = $('#realCard');
			if (data.module_type == CONF.MODULE_TYPE.DB_CDP && !taskGridFlag) {
				$('#realDataTree').hide();
			}
		}
		let subType = '';
		let html = '';
		let name = taskGridFlag ? data.task_name : ((data.db_cluster_uuid !== '' && data.db_path != '' && data.module_type == CONF.MODULE_TYPE.DB) ? data.db_path : data.item_name);
		let bgClass = taskGridFlag ? 'bd_task_green' : 'bd_item_green';
		if (data.abnormal_chain_flag) {
			bgClass = 'bd_abnormal';
		}
		if (!copy_task_type.includes(data.task_type)) {
			// 显示虚拟化类型
			if ([CONF.MODULE_TYPE.VM, CONF.MODULE_TYPE.PUBLIC_CLOUD, CONF.MODULE_TYPE.PRIVATE_CLOUD].includes(data.module_type)) {
				// #26208 虚拟机、数据库副本需要特殊处理对象的子类型显示
				subType = `<div class="card-sub_type">
							<span class="card-info-label">${LANG.UI_COPY_SOURCE_VM_TYPE}  </span>
							<span class="card-info-text">${data.sub_type_des}</span>
						</div>`;
			}
			// 显示数据库类型
			if (data.module_type == CONF.MODULE_TYPE.DB) {
				subType = `<div class="card-sub_type">
							<span class="card-info-label">${LANG.UI_COPY_SOURCE_DB_TYPE}  </span>
							<span class="card-info-text">${data.sub_type_des}</span>
						</div>`;
			}
		}
		html = `<div class="task_card-div ${bgClass}">
						<div class="task_card-info">
							<div class="card-task_name" title="${name}"><span>${name}</span></div>
							<div class="card-module_type">
								<span class="card-info-label">${LANG.UI_SEARCH_MODE_TYPE}  </span>
								<span class="card-info-text">${data.module_type_des}</span>
							</div>
							<div class="card-task_type">
								<span class="card-info-label">${LANG.UI_SEARCH_TASK_TYPE}  </span>
								<span class="card-info-text">${data.task_type_des}</span>
							</div>
							${subType}
						</div>
					</div>`;
		$('#moduleType').val(data.module_type);
		cardDiv.html(html);
	};
	/**
	 * 初始化搜索框和排序按钮
	 * @param {object} data 任务信息
	 * @param {object} params 表格参数
	 * @param {boolean} realFlag 实时数据标志
	 */
	let initSearchTask = (data, params, realFlag = false) => {
		let searchDiv = $('#drawerSort')
		if (realFlag) {
			searchDiv = $('#realSort');
		}
		let html = '';
		html = `<div class="cardSearch input-group mr12">
					<input class="searchTree customSearch" type="text" style="padding-right:32px" maxlength="64" placeholder="${LANG.UI_BACKUP_DATA_TABLE_SEARCH_BY_NAME}">
					<div class="position0" style="width:auto;height:34px">
						<button class="b-btn taskTableclear clear hide position0"><i class="icon-close-small"></i></button>
					</div>
					<div class="positionL0" style="width:auto;height:34px;">
						<button class="b-btn search-btn"><i class="icon-search"></i></button>
					</div>
				</div>
				<div class="card-sort">
					<button style="line-height:16px" type="button" class="dropdown-toggle btn-font btn-title btn-whitespace" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
					<i class="viconfont vicon-a-chuanshuchuanshudaili"></i>${LANG.UI_BACKUP_DATA_DETAIL_SORT_LABEL}
					</button>
					<ul class="dropdown-menu" role="menu">
						<li class="btn-whitespace data-asc">${LANG.UI_BACKUP_DATA_DETAIL_SORT_BY_DATA_ASC}</li>
						<li class="btn-whitespace data-desc">${LANG.UI_BACKUP_DATA_DETAIL_SORT_BY_DATA_DESC}</li>
						<li class="btn-whitespace name-asc">${LANG.UI_BACKUP_DATA_DETAIL_SORT_ASC}</li>
						<li class="btn-whitespace name-desc">${LANG.UI_BACKUP_DATA_DETAIL_SORT_DESC}</li>
					</ul>
				</div>`;
		searchDiv.html(html);
		// 回车搜索树节点
		$('.searchTree').on('keydown', function (event) {
			params.search = $('.searchTree').val();
			if (event.keyCode === 13) {
				Metronic.blockUI({ target: '.pointListBody', animate: true });
				let pointList = (res) => {
					initTree(res.data.tree, '#backupDataTree');
				}
				pAjaxRequest(params, "/api/v1/backup_data/task_tree", "POST", pointList, true);
			}
		});
		// 按钮搜索
		$('.cardSearch .search-btn').on('click', () => {
			Metronic.blockUI({ target: '.pointListBody', animate: true });
			params.search = $('.searchTree').val();
			let pointList = (res) => {
				initTree(res.data.tree, '#backupDataTree');
			}
			pAjaxRequest(params, "/api/v1/backup_data/task_tree", "POST", pointList, true);
		});
		// 按数据大小升序
		$('.data-asc').on('click', () => {
			Metronic.blockUI({ target: '.pointListBody', animate: true });
			params.sort = 'total_size';
			params.order = 'asc';
			TREE_SORT = 'total_size';
			TREE_ORDER = 'asc';
			refreshOrder(params);
		});
		// 按数据大小降序
		$('.data-desc').on('click', () => {
			Metronic.blockUI({ target: '.pointListBody', animate: true });
			params.sort = 'total_size';
			TREE_SORT = 'total_size';
			params.order = 'desc';
			TREE_ORDER = 'desc';
			refreshOrder(params);
		});
		// 按名称升序
		$('.name-asc').on('click', () => {
			Metronic.blockUI({ target: '.pointListBody', animate: true });
			params.sort = 'item_name';
			params.order = 'asc';
			TREE_SORT = 'item_name';
			TREE_ORDER = 'asc';
			refreshOrder(params);
		});
		// 按名称降序
		$('.name-desc').on('click', () => {
			Metronic.blockUI({ target: '.pointListBody', animate: true });
			params.sort = 'item_name';
			params.order = 'desc';
			TREE_SORT = 'item_name';
			TREE_ORDER = 'desc';
			refreshOrder(params);
		});
	}
	/**
	 * 获取操作列表
	 * @param {array} opList 操作类型数组
	 * @param {boolean} flag 分割线标志
	 * @returns 
	 */
	let getOpLi = (opList, data, flag = false) => {
		let html = '';
		opList.forEach((op, index) => {
			if (OPERATION_LIST.hasOwnProperty(op)) {
				let border = '';
				if (index == 0 && !flag) {
					border = `style="border-top: 1px solid #E6E6E6;"`;
				}
				html += `<li class="btn-whitespace point_action-${op}" ${border}><i class="viconfont ${OPERATION_ICON[op]}"></i>${OPERATION_LIST[op]}</li>`;
			}
		})
		return html;
	}
	/**
	 * 树节点排序
	 * @param {object} params 排序参数
	 */
	let refreshOrder = (params) => {
		let pointList = (res) => {
			initTree(res.data.tree, '#backupDataTree');
		}
		pAjaxRequest(params, "/api/v1/backup_data/task_tree", "POST", pointList, true);
	}
	/**
	 * 生成操作列表 不同模块具有不同操作
	 * 1.虚拟机、文件非完备点可以删除
	 * 2.扫描中、校验中的点不能进行删除和合并操作
	 * 3.删除中、合并中的时间点不能进行恢复
	 * 4.虚拟机、操作系统（支持永久增量的模块）非完备点可以设置永久标记，其他模块都不行
	 * @param {object} data 数据信息
	 * @returns 
	 */
	let getOperateList = (data) => {
		// 异常时间点只能删除
		if (data.abnormal_chain_flag) {
			return getOpLi([4], data, true);
		}
		// 异地数据只能查看详情
		if (data.remote_flag) {
			return getOpLi([1], data, true);
		}
		let html = '', baseOp = [], otherOp = [];
		const { module_type, sub_module_type, task_type, backup_mode, storage_type, sub_type, chain_latest_point_flag } = data;
		/**
		 * 根据模块类型不同，生成不同的操作列表
		 * baseOp：
		 * 2 备注
		 * 3 标记
		 * 4 删除
		 * otherOp：
		 * 5 worm
		 * 6 恢复
		 * 7 瞬时恢复
		 * 8 细腻度
		 * 9 跨平台
		 * 10 同步
		 * 
		 */
		switch (module_type) {
			case CONF.MODULE_TYPE.VM: // 虚拟机模块所有点都能删除，非完备点在未配置worm或者worm期限过期之后 可以显示worm配置按钮
			case CONF.MODULE_TYPE.PUBLIC_CLOUD:
			case CONF.MODULE_TYPE.PRIVATE_CLOUD:
				baseOp = [2, 3, 4];
				otherOp = [5, 6, 7, 8, 9];
				if (backup_mode == 1) {
				} else {
					otherOp = [6, 7, 8, 9];
					// 非完备在未配置worm或者worm期限过期之后 可以显示worm配置按钮
					if (data.details && data.details.worm_info && data.details.worm_info.show_worm_flag) {
						otherOp.unshift(5);
					}
				}
				// 私有云不支持瞬时恢复
				if (sub_module_type == 3) {
					otherOp = otherOp.filter(item => item !== 7);
				}
				// 云存储上的点不能单独删除
				if (storage_type == CONF.BD_STORAGE_TYPE.CLOUD) {
					baseOp = baseOp.filter(item => item != 4);
				}
				break;
			case CONF.MODULE_TYPE.FS: // 文件模块所有点都能删除，非完备点在未配置worm或者worm期限过期之后 可以显示worm配置按钮
			case CONF.MODULE_TYPE.NAS:
			case CONF.MODULE_TYPE.OBS:
			case CONF.MODULE_TYPE.HADOOP:
				baseOp = [2, 3, 4];
				if (backup_mode == 1) {
					otherOp = [5, 6];
				} else {
					otherOp = [6];
					// 非完备在未配置worm或者worm期限过期之后 可以显示worm配置按钮
					if (data.details && data.details.worm_info && data.details.worm_info.show_worm_flag) {
						otherOp.unshift(5);
					}
				}
				break;
			case CONF.MODULE_TYPE.DB: // MongoDB的增备点和差备点可以删除，其他非完备点不能删除
				if (backup_mode == 1) {
					baseOp = [2, 3, 4];
				} else {
					baseOp = [2];
					if (backup_mode != 4 && sub_type == CONF.DB_TYPE.MONGODB) {
						baseOp = [2, 3, 4]; // #27079 MongoDB增备点、差备点可以永久标记
					}
				}
				// 非完备在未配置worm或者worm期限过期之后 可以显示worm配置按钮
				if (data.details && data.details.worm_info && data.details.worm_info.show_worm_flag) {
					otherOp.unshift(5);
				}
				otherOp = [...otherOp, ...[6]];
				break;
			case CONF.MODULE_TYPE.KUBERNETES:
				if (backup_mode == 1) {
					baseOp = [2, 3, 4];
				} else {
					baseOp = [2];
				}
				// 非完备在未配置worm或者worm期限过期之后 可以显示worm配置按钮
				if (data.details && data.details.worm_info && data.details.worm_info.show_worm_flag) {
					otherOp.unshift(5);
				}
				break;
			case CONF.MODULE_TYPE.OS: // 整机数据在非云存储上的，可以删除单个点，增备链最后一个增备点标志，最后一个增备点不能删除
				baseOp = [2, 3, 4];
				if (backup_mode == 1) {
				} else {
					if (storage_type != CONF.BD_STORAGE_TYPE.CLOUD && !chain_latest_point_flag) {
						baseOp = [2, 3, 4];
					} else {
						baseOp = [2, 3];
					}
				}
				// 非完备在未配置worm或者worm期限过期之后 可以显示worm配置按钮
				if (data.details && data.details.worm_info && data.details.worm_info.show_worm_flag && sub_module_type == 1) {
					otherOp.unshift(5);
				}
				// 整机（卷）只有恢复操作
				if (sub_module_type == 1) {
					otherOp = [...otherOp, ...[6, 7, 8, 9]];
				} else {
					otherOp = [...otherOp, ...[6]];
				}
				break;
			case CONF.MODULE_TYPE.M365:
				baseOp = [2, 3, 4];
				if (backup_mode != 1) {
					baseOp = [2, 3];
				}
				// 非完备在未配置worm或者worm期限过期之后 可以显示worm配置按钮
				if (data.details && data.details.worm_info && data.details.worm_info.show_worm_flag) {
					otherOp = [5]
				}
				// 365副本数据和云存储、磁带的备份数据的完备点有同步功能
				if ((copy_task_type.includes(task_type) || [CONF.BD_STORAGE_TYPE.CLOUD, CONF.BD_STORAGE_TYPE.TAPE].includes(storage_type)) && backup_mode == 1) {
					otherOp = [...otherOp, ...[6, 10]];
				} else {
					otherOp = [...otherOp, ...[6]];
				}
				break;
			case CONF.MODULE_TYPE.VOL_CDP:
				baseOp = [2];
				// 非完备在未配置worm或者worm期限过期之后 可以显示worm配置按钮
				if (data.details && data.details.worm_info && data.details.worm_info.show_worm_flag) {
					otherOp = [5]
				}
				otherOp = [6, 11];
				break;
			case CONF.MODULE_TYPE.DB_CDP:
				baseOp = [2];
				break;
		}
		if (!CONF.FUNCTIONS.includes('worm')) {
			otherOp = otherOp.filter(item => ![5].includes(item));
		}
		// 磁带存储的数据不能删除，标记，worm保护, 瞬时恢复，细腻度恢复，跨平台恢复
		if (data.tape_storage_flag) {
			baseOp = baseOp.filter(item => ![3, 4].includes(item));
			otherOp = otherOp.filter(item => ![5, 7, 8, 9].includes(item));
		}
		html = getOpLi(baseOp, data, true) + getOpLi(otherOp, data);
		return html;
	}

	/**
	 * 设置按钮禁用状态
	 * 1.操作中的状态不能进行恢复
	 * 2.扫描中、校验中的点不能进行删除和合并操作
	 * @param {*} tableId 
	 */
	const setBtnStatus = (tableId) => {
		let data = $(`#${tableId}`).bootstrapTable("getData");
		for (let i = 0; i < data.length; i++) {
			if (data[i].remote_flag) {
				return true;
			}
			let operation_status = data[i].details.operation_status;
			let uuid = data[i].timepoint_uuid;
			// 1.操作中的时间点不能进行恢复
			if (operation_status != CONF.OPERATION_STATUS.NO_OPERATION) {
				addForbidButton(uuid, [6, 7, 8, 9]);
			}
			// 合并失败或依赖点合并失败的点，不能恢复和其他操作
			let merge_status = data[i].details.merge_status;
			if (merge_status == CONF.MERGE_STATUS.FAILED || merge_status == CONF.MERGE_STATUS.DEPEND_MERGE_FAILED) {
				addForbidButton(uuid, [6, 7, 8, 9]);
			}
		}
	}

	/**
	 * 添加禁止点击的按钮样式
	 * @param {string} uuid 时间点uuid
	 * @param {array} option 操作选项
	 * @param {bool} available 可用标志
	 */
	const addForbidButton = (uuid, option, available = false) => {
		option.forEach(item => {
			if (!available) {
				$(`#pointTable #${uuid} .point_action-${item}`).addClass('point-action-disabled');
			}
		})
	}
	// 无论点击哪个时间点都勾选整条链，原因如下：
	// 1.以前备份数据的逻辑，批量删除只能删除整条链，单点删除只能删除完备点（实质也是删除整条链），不允许对单独非完备点进行删除
	// 2.表格树不能实现单独disable某一行的复选框
	// 3.时间点表格只有批量删除一种批量操作
	// 4.表格树插件没有内置的子节点和父节点的联动操作
	// 5.表格树的checkBy和uncheckBy方法是通过模拟点击事件（调用trigger方法）来实现js勾选状态的，会出现子节点勾选联动父节点然后再联动子节点的情况，陷入死循环
	// 解决办法：只能通过数组暂存id的方式，排除已经勾选的点，避免死循环
	// 新增：文件模块可以批量删除非完备点，直接返回，不联动勾选
	/**
	 * 勾选整条链
	 * @param {object} row 行数据
	 * @param {boolean} checked 是否勾
	 */
	let checkDependRow = (row, checked) => {
		// 文件模块可以批量删除非完备点，直接返回，不联动勾选
		if ([CONF.MODULE_TYPE.FS, CONF.MODULE_TYPE.NAS].includes(row.module_type)) {
			return true;
		}
		// 勾选整条链
		if (checked) {
			// 如果是有子节点的完备点， 联动勾选所有子节点
			if (row.hasChildren && parseInt(row.backup_mode) == 1) {
				// 暂存完备点id,跳过已经勾选的点
				if (!TIME_CHECK.includes(row.id)) {
					TIME_CHECK.push(row.id);
					// 联动勾选子记录
					$('#pointTable').bootstrapTable('checkBy', {
						field: 'pid',
						values: [row.id]
					});
				}
			}
			// 如果是非完备点，联动勾选所有兄弟节点和父节点
			if (parseInt(row.backup_mode) !== 1) {
				// 已经勾选的点跳过
				if (!TIME_CHECK.includes(row.id)) {
					TIME_CHECK.push(row.id);
					// 联动勾选兄弟节点
					$('#pointTable').bootstrapTable('checkBy', {
						field: 'pid',
						values: [row.pid]
					});
					// 如果父节点未被勾选,勾选父节点
					if (!TIME_CHECK.includes(row.pid)) {
						TIME_CHECK.push(row.id);
						$('#pointTable').bootstrapTable('checkBy', {
							field: 'id',
							values: [row.pid]
						});
					}
				}
			}
			// 取消勾选整条链
		} else {
			// 如果是有子节点的完备点,联动取消所有非完备点
			if (row.hasChildren && row.backup_mode === 1) {
				// 取消勾选,未被取消的完备点
				if (TIME_CHECK.includes(row.id)) {
					// 清除暂存的id
					TIME_CHECK = TIME_CHECK.filter(item => item !== row.id);
					// 联动取消子节点
					$('#pointTable').bootstrapTable('uncheckBy', {
						field: 'pid',
						values: [row.id]
					});
				}
			}
			// 如果是非完备点,则取消所有兄弟节点和父节点
			if (row.backup_mode != 1) {
				// 只有勾选状态才取消
				if (TIME_CHECK.includes(row.id)) {
					// 清除暂存的id
					TIME_CHECK = TIME_CHECK.filter(item => item !== row.id);
					// 取消勾选兄弟节点
					$('#pointTable').bootstrapTable('uncheckBy', {
						field: 'pid',
						values: [row.pid]
					});
					// 取消父节点勾选，不在数组说明已经取消
					if (TIME_CHECK.includes(row.pid)) {
						TIME_CHECK = TIME_CHECK.filter(item => item !== row.pid);
						$('#pointTable').bootstrapTable('uncheckBy', {
							field: 'id',
							values: [row.pid]
						});
					}
				}
			}
		}
	}
	const checkSubRow = (row, checked) => {
		// 勾选整条链
		if (checked) {
			// 如果是有子节点的完备点， 联动勾选所有子节点
			if (row.hasChildren) {
				// 暂存完备点id,跳过已经勾选的点
				if (!TIME_CHECK.includes(row.id)) {
					TIME_CHECK.push(row.id);
					// 联动勾选子记录
					$('#taskTable').bootstrapTable('checkBy', {
						field: 'pid',
						values: [row.id]
					});
				}
			} else {
				// 已经勾选的点跳过
				if (!TIME_CHECK.includes(row.id)) {
					TIME_CHECK.push(row.id);
					// 联动勾选兄弟节点
					$('#taskTable').bootstrapTable('checkBy', {
						field: 'pid',
						values: [row.pid]
					});
					// 如果父节点未被勾选,勾选父节点
					if (!TIME_CHECK.includes(row.pid)) {
						TIME_CHECK.push(row.pid);
						$('#taskTable').bootstrapTable('checkBy', {
							field: 'id',
							values: [row.pid]
						});
					}
				}
			}
		} else {
			// 如果是有子节点的完备点,联动取消所有非完备点
			if (row.hasChildren) {
				// 取消勾选,未被取消的完备点
				if (TIME_CHECK.includes(row.id)) {
					// 清除暂存的id
					TIME_CHECK = TIME_CHECK.filter(item => item !== row.id);
					// 联动取消子节点
					$('#taskTable').bootstrapTable('uncheckBy', {
						field: 'pid',
						values: [row.id]
					});
				}
			} else {
				// 只有勾选状态才取消
				if (TIME_CHECK.includes(row.id)) {
					// 清除暂存的id
					TIME_CHECK = TIME_CHECK.filter(item => item !== row.id);
					// 取消勾选兄弟节点
					$('#taskTable').bootstrapTable('uncheckBy', {
						field: 'pid',
						values: [row.pid]
					});
					// 取消父节点勾选，不在数组说明已经取消
					if (TIME_CHECK.includes(row.pid)) {
						TIME_CHECK = TIME_CHECK.filter(item => item !== row.pid);
						$('#taskTable').bootstrapTable('uncheckBy', {
							field: 'id',
							values: [row.pid]
						});
					}
				}
			}
		}
	}
	/**
	 * 获取时间点表格列
	 */
	const getPointTableColumns = () => {
		if (pointParam.abnormal_chain_flag) {
			return [
				{
					field: 'checked',
					checkbox: true,
					sortable: false,
					clickToSelect: true,
				},
				{
					field: 'timepoint',
					title: LANG.UI_BACKUP_DATA_TIME_POINT,
					events: expandEvents,
					clickToSelect: false,
					formatter: function (value, row, index) {
						let html = '', point_name = value;
						// 加密的时间点加锁
						if (row.encrypted_flag == '1') {
							point_name = point_name + ' <i class="fa fa-lock" style="margin-left: 4px;"></i>';
						}
						if (row.hasChildren > 0) {
							html = `<span class="treegrid-expander"></span><span class="treegrid-expander-collapsed root-collapsed get-child-node"></span><a title="${value}" class="point_action-1">${point_name}</a>`;
						} else {
							html = `<a title="${value}" class="point_action-1">${point_name}</a>`;
						}
						return html;
					}
				},
				{
					field: 'backup_mode_des',
					title: LANG.UI_SEARCH_TYPE,
					sortable: false,
					formatter: function (value, row, index) {
						return `<div><i class="viconfont ${BACKUP_MODE_ICON[row.backup_mode]}"></i><span>${value}</span></div>`;
					}
				},
				{
					field: 'total_size',
					title: LANG.UI_COPY_DATA_SIZE,
					sortable: true,
				},
				{
					field: 'write_size',
					title: LANG.UI_PUBLIC_REAL_SIZE,
					sortable: true,
				},
				{
					field: 'storage',
					title: LANG.UI_COPY_DATA_STORAGE,
					sortable: true,
				},
				{
					field: 'task_name',
					title: LANG.UI_PUBLIC_TASK_NAME,
					sortable: false,
				},
				{
					field: 'remarks',
					title: LANG.UI_PUBLIC_REMARK,
					sortable: false,
				},
				{
					field: 'abnormal_chain_des',
					title: LANG.UI_BACKUP_DATA_LABEL_ABNORMAL_CHAIN_REASON,
					sortable: false,
					formatter: (value, row, index) => {
						return `<a title="${value}" class="abnormal-chain-color">${value}</a>`;
					}
				},
				{
					field: 'importance_flag',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_FOREVER,
					sortable: false,
					formatter: function (value, row, index) {
						let html = ``;
						if (value) {
							html = `<i class="viconfont mark-icon vicon-remark-forever"></i>`;
						}
						return html;
					}
				},
				{
					field: 'GFS_flag',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_GFS,
					sortable: false,
					formatter: function (value, row, index) {
						let gfsInfo = row.mark_info;
						let html = ``;
						if (gfsInfo.weekly_flag) {
							html += `<i class="viconfont mark-icon vicon-remark-week"></i>`;
						}
						if (gfsInfo.monthly_flag) {
							html += `<i class="viconfont mark-icon vicon-remark-month"></i>`;
						}
						if (gfsInfo.yearly_flag) {
							html += `<i class="viconfont mark-icon vicon-remark-year"></i>`;
						}
						return html;
					}
				},
				{
					field: 'detail',
					title: LANG.UI_PUBLIC_OPERATION,
					sortable: false,
					events: pointEvents,
					opButton: true,
					clickToSelect: false,
					formatter: function (value, row, index) {
						// 操作权限判断
						if ($.inArray('p_backup_data_operate', CONF.PERMISSION_ARR) !== -1) {
							let btn = getOperateList(row);
							// 异地副本数据只需要备注
							if (row.remote_flag) {
								btn = getOpLi([4], row);
							}
							let html = '<div class="btn-group dropdown-wrapper point-actions">';
							if (index > 5 && row.backup_mode == 1) {
								html = '<div class="btn-group dropup dropdown-wrapper point-actions">';
							}
							html += `<button style="display: flex;align-items: center;padding: 6px 8px;" type="button" class="btn btn-primary brr2 p-lr8 btn-sm dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
										${LANG.UI_PUBLIC_OPERATION}<i class="fa fa-angle-down" style="margin-left:4px"></i>
										</button>
										<ul class="dropdown-menu" role="menu" id="${row.timepoint_uuid}">
										${btn}
										</ul>
									</div>`
							return html;
						} else {
							return '';
						}
					}
				}
			];
		} else {
			return [
				{
					field: 'checked',
					checkbox: true,
					sortable: false,
					clickToSelect: true,
				},
				{
					field: 'timepoint',
					title: LANG.UI_BACKUP_DATA_TIME_POINT,
					events: expandEvents,
					clickToSelect: false,
					formatter: function (value, row, index) {
						let html = '', point_name = value;
						// 加密的时间点加锁
						if (row.encrypted_flag == '1') {
							point_name = point_name + ' <i class="fa fa-lock" style="margin-left: 4px;"></i>';
						}
						if (row.hasChildren > 0) {
							html = `<span class="treegrid-expander"></span><span class="treegrid-expander-collapsed root-collapsed get-child-node"></span><a title="${value}" class="point_action-1">${point_name}</a>`;
						} else {
							html = `<a title="${value}" class="point_action-1">${point_name}</a>`;
						}
						return html;
					}
				},
				{
					field: 'backup_mode_des',
					title: LANG.UI_SEARCH_TYPE,
					sortable: false,
					formatter: function (value, row, index) {
						return `<div><i class="viconfont ${BACKUP_MODE_ICON[row.backup_mode]}"></i><span>${value}</span></div>`;
					}
				},
				{
					field: 'total_size',
					title: LANG.UI_COPY_DATA_SIZE,
					sortable: true,
				},
				{
					field: 'write_size',
					title: LANG.UI_PUBLIC_REAL_SIZE,
					sortable: true,
				},
				{
					field: 'point_num',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_UN_FULL_NUM,
					sortable: false,
				},
				{
					field: 'storage',
					title: LANG.UI_COPY_DATA_STORAGE,
					sortable: true,
				},
				{
					field: 'status',
					title: LANG.UI_PUBLIC_STATUS,
					sortable: false,
					formatter: (value, row, index) => {
						return getStatusLabel(row.status_des, row.status_value)
					}
				},
				{
					field: 'task_name',
					title: LANG.UI_PUBLIC_TASK_NAME,
					sortable: false,
				},
				{
					field: 'remarks',
					title: LANG.UI_PUBLIC_REMARK,
					sortable: false,
				},
				{
					field: 'importance_flag',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_FOREVER,
					sortable: false,
					formatter: function (value, row, index) {
						let html = ``;
						if (value) {
							html = `<i class="viconfont mark-icon vicon-remark-forever"></i>`;
						}
						return html;
					}
				},
				{
					field: 'GFS_flag',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_GFS,
					sortable: false,
					formatter: function (value, row, index) {
						let gfsInfo = row.mark_info;
						let html = ``;
						if (gfsInfo.weekly_flag) {
							html += `<i class="viconfont mark-icon vicon-remark-week"></i>`;
						}
						if (gfsInfo.monthly_flag) {
							html += `<i class="viconfont mark-icon vicon-remark-month"></i>`;
						}
						if (gfsInfo.yearly_flag) {
							html += `<i class="viconfont mark-icon vicon-remark-year"></i>`;
						}
						return html;
					}
				},
				{
					field: 'detail',
					title: LANG.UI_PUBLIC_OPERATION,
					sortable: false,
					events: pointEvents,
					opButton: true,
					clickToSelect: false,
					formatter: function (value, row, index) {
						// 操作权限判断
						if ($.inArray('p_backup_data_operate', CONF.PERMISSION_ARR) !== -1) {
							let btn = getOperateList(row);
							// 异地副本数据只需要备注
							if (row.remote_flag) {
								btn = getOpLi([4], row);
							}
							let html = '<div class="btn-group dropdown-wrapper point-actions">';
							if (index > 5 && row.backup_mode == 1) {
								html = '<div class="btn-group dropup dropdown-wrapper point-actions">';
							}
							html += `<button style="display: flex;align-items: center;padding: 6px 8px;" type="button" class="btn btn-primary brr2 p-lr8 btn-sm dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
										${LANG.UI_PUBLIC_OPERATION}<i class="fa fa-angle-down" style="margin-left:4px"></i>
										</button>
										<ul class="dropdown-menu" role="menu" id="${row.timepoint_uuid}">
										${btn}
										</ul>
									</div>`
							return html;
						} else {
							return '';
						}
					}
				}
			];
		}
	}
	/**
	 * 初始化时间点表格
	 * @param {object} params 参数
	 * @param {boolean} remoteFlag 异地数据标志
	 */
	let initPointTable = () => {
		let options = {
			vin_url: "/api/v1/backup_data/points",
			vin_method: "POST",
			vin_params: function () {
				return { ...pointParam };
			},
			tableArea: '#pointListDrawer .point_grid-container',
			parentIdField: 'pid', // 确定字段作为父级字段
			idField: 'timepoint_uuid', // 确认字段作为id
			treeShowField: 'timepoint', // 确认显示展开图标的字段
			showColumns: true,
			treegrid: true, // 使用树形格式
			sortName: 'timepoint',
			sortOrder: 'desc',
			hideColumns: 'importance_flag,GFS_flag,remarks,task_name',
			toolbarId: '#pointTableToolbar',
			buttonsToolbar: '#pointTableToolbar .vin_pointTableToolbar',
			changeHeightBtn: true, //改变高度按钮
			pagination: true, //分页
			pageUnit: LANG.UI_TOOLS_TABLE_TOTAL_COUNT_CHAIN,
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			searchInput: true, //搜索框
			placeholder: LANG.UI_BACKUP_DATA_TABLE_SEARCH_BY_NAME,
			searchClass: 'searchPoint', //搜索框类名
			searchSelector: '.searchPoint', //表格选择使用该搜索框
			dateRangePickerId: 'pointDateRange', // 日期选择器组件button id
			customTool: {
				beforeInput: getDeleteBtn('deletePoint'),
				afterInput: getRefreshBtn('refreshPoint') + `<div class="btn-group dropdown-filter-wrapper"></div>
															<div class="btn-group">
																<div id="point_picker_wrapper" class="position-relative"></div>
															</div>`,
			},
			exportSettings: {
				showBuiltIn: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'], // 需要显示的默认导出项
				custom: [
					{
						label: LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL,
						class: 'export-all-excel'
					}
				]
			},
			// 勾选框联动效果
			onCheck: function (row) {
				modifyDelStyle('pointTable', 'deletePoint');
				checkDependRow(row, true);
			},
			onUncheck: (row) => {
				modifyDelStyle('pointTable', 'deletePoint');
				checkDependRow(row, false);
			},
			onUncheckAll: () => {
				modifyDelStyle('pointTable', 'deletePoint');
			},
			onCheckAll: () => {
				modifyDelStyle('pointTable', 'deletePoint');
			},
			onPostBody: function (data) {
				setBtnStatus('pointTable'); //根据状态设置操作按钮
				// 渲染树形结构
				let columns = $('#pointTable').bootstrapTable('getOptions').columns;
				if (columns && columns[0][1].visible) {
					// 二次渲染展开的列，增加展开图标
					$('#pointTable').treegrid({
						treeColumn: 1,
						onChange: function () {
							$('#pointTable').bootstrapTable('resetView')
						}
					})
				}
				$('#pointTable th[data-field="checked"]').css('width', '2.5%');
				$('#pointTable th[data-field="timepoint"]').css('width', '15%');
				$('#pointTable th[data-field="backup_mode_des"]').css('width', '7.5%');
				$('#pointTable th[data-field="total_size"]').css('width', '7.5%');
				$('#pointTable th[data-field="write_size"]').css('width', '7.5%');
				$('#pointTable th[data-field="task_name"]').css('width', '7.5%');
				$('#pointTable th[data-field="storage"]').css('width', '20%');
				$('#pointTable th[data-field="detail"]').css('width', '5%');
				if (CONF.LANGUAGE !== "zh-cn" && CONF.LANGUAGE !== "zh-tw") {
					$('#pointTable th[data-field="write_size"]').css('width', '11%');
				}
				if (!initPointListFlag) {
					$('#pointTable .treegrid-expander').addClass('display-none-force');
					initPointListFlag = true;
					// 操作权限判断
					if ($.inArray('p_backup_data_operate', CONF.PERMISSION_ARR) == -1) {
						$('#pointTable').bootstrapTable('hideColumn', 'detail');
					}
				};
				if (!pointParam.abnormal_chain_flag) {
					$('#pointTable th[data-field="point_num"]').css('width', '7.5%');
					$('#pointTable th[data-field="status"]').css('width', '5%');
					if (CONF.LANGUAGE !== "zh-cn" && CONF.LANGUAGE !== "zh-tw") {
						$('#pointTable th[data-field="write_size"]').css('width', '11%');
						$('#pointTable th[data-field="point_num"]').css('width', '16%');
						$('#pointTable th[data-field="status"]').css('width', '6.5%');
					}
				}
				modifyDelStyle('pointTable', 'deletePoint');

				const exportOptions = {
					toolbarId: 'pointTableToolbar',
					url: '/api/v1/backup_data/points/export',
					fileName: LANG.UI_BACKUP_DATA_LABEL_TIMEPOINT_LIST
				}

				// 监听导出全部数据
				exportAllTableData(exportOptions);
				
				$("#pointTable input[name='btSelectAll']").on('change',function (){
					let allData = $('#pointTable').bootstrapTable('getData');
					if(this.checked){
						// 获取所有的id
						TIME_CHECK = allData.map(item => item.timepoint_uuid);
					} else {
						TIME_CHECK = [];
					}
				})
			},
			LoadSuccess: () => {
				Metronic.unblockUI('.pointListBody');
			},
			LoadError: () => {
				Metronic.unblockUI('.pointListBody');
			},
			columns: getPointTableColumns(),
		}
		if (initPointListFlag) {
			// 2. 销毁现有表格
			$('#pointTable').bootstrapTable('destroy');

			// 3. 清空表格容器内容
			$('.vin_pointTableToolbar').empty();

			// 4. 重新初始化表格
			$('#pointTable').baseTableConfig().init(options);
		} else {
			$('#pointTable').baseTableConfig().init(options);
			initPointListener();
		}
		// 需要清楚上一次的保留状态
		modifyDelStyle('pointTable', 'deletePoint');
		$('#pointTableToolbar .dropdown-filter-wrapper').myFilter({ showModule: false, showTaskType: false, showTaskStatus: true });
		initPointTablePicker();
		// 过滤确认
		$('#pointTableToolbar .filterSubmit').on('click', (event) => {
			Metronic.blockUI({ target: '.pointListBody', animate: true });
			const plugin = $('#pointTableToolbar .dropdown-filter-wrapper').data('myFilter');
			let filterParam = plugin.getFilter();
			pointParam.status = filterParam.status;
			pointParam.operation_status = filterParam.operation_status;
			$('#pointTable').bootstrapTable('refresh', {
				query: pointParam
			});
			modifyDelStyle('pointTable', 'deletePoint');
			event.preventDefault();
		});
	}
	/**
	 * 初始化实时数据表格
	 * @param {object} params 参数
	 */
	let initVolPointTable = (params) => {
		let columns = [
			{
				checkbox: true,
				sortable: false,
				clickToSelect: true,
			},
			{
				field: 'start_timestamp',
				title: LANG.UI_BACKUP_DATA_TABLE_LABEL_START_TIME,
			},
			{
				field: 'end_timestamp',
				title: LANG.UI_BACKUP_DATA_TABLE_LABEL_END_TIME,
			},
			{
				field: 'encrypted_des',
				title: LANG.UI_BACKUP_DATA_ENCRYPT,
				sortable: false,
			},
			{
				field: 'backup_file_size',
				title: LANG.UI_BACKUP_DATA_TABLE_LABEL_VOL_DATA_SIZE,
			},
			{
				field: 'log_file_total_size',
				title: LANG.UI_BACKUP_DATA_TABLE_LABEL_VOL_LOG_SIZE,
			},
			{
				field: 'storage',
				title: LANG.UI_COPY_DATA_STORAGE,
			},
			{
				field: 'vol_storage_status',
				title: LANG.UI_PUBLIC_STATUS,
				sortable: false,
			},
			{
				field: 'remarks',
				title: LANG.UI_PUBLIC_REMARK,
				sortable: false,
			},
			{
				field: 'detail',
				title: LANG.UI_PUBLIC_OPERATION,
				sortable: false,
				events: pointEvents,
				opButton: true,
				clickToSelect: false,
				formatter: function (value, row, index) {
					// 操作权限判断
					if ($.inArray('p_backup_data_operate', CONF.PERMISSION_ARR) !== -1) {
						let html = `<div class="btn-group point-actions">
										<button style="line-height:16px" type="button" class="btn btn-primary brr2 p-lr8 btn-sm dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
										${LANG.UI_PUBLIC_OPERATION}<i class="fa fa-angle-down"></i>
										</button>
										<ul class="dropdown-menu" role="menu">
										${getOperateList(row)}
										</ul>
									</div>`

						return html;
					} else {
						return '';
					}
				}
			}
		]
		if (params.module_type == CONF.MODULE_TYPE.DB_CDP) {
			$('.os-cdp-div .vol-tips').hide();
			$('.os-cdp-div #task_plot_li').hide();
			columns = [
				{
					checkbox: true,
					sortable: false,
					clickToSelect: true,
				},
				{
					field: 'start_timestamp',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_START_TIME,
				},
				{
					field: 'end_timestamp',
					title: LANG.UI_BACKUP_DATA_TABLE_LABEL_END_TIME,
				},
				{
					field: 'source_agent',
					title: LANG.UI_VOL_CDP_JOB_DETAILS_RECOVER_CLIENT,
					sortable: false,
				},
				{
					field: 'target_agent',
					title: LANG.UI_VOL_CDP_JOB_DETAILS_STANDBY_MACHINE,
				},
				{
					field: 'db_cdp_status',
					title: LANG.UI_PUBLIC_STATUS,
				},
				{
					field: 'detail',
					title: LANG.UI_PUBLIC_OPERATION,
					sortable: false,
					events: pointEvents,
					opButton: true,
					clickToSelect: false,
					formatter: function (value, row, index) {
						// 操作权限判断
						if ($.inArray('p_backup_data_operate', CONF.PERMISSION_ARR) !== -1) {
							let html = `<div class="btn-group point-actions">
										<button style="line-height:16px" type="button" class="btn btn-primary brr2 p-lr8 btn-sm dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
										${LANG.UI_PUBLIC_OPERATION}<i class="fa fa-angle-down"></i>
										</button>
										<ul class="dropdown-menu" role="menu">
										${getOperateList(row)}
										</ul>
									</div>`
							return html;
						} else {
							return '';
						}
					}
				}
			]
		}
		let options = {
			vin_url: "/api/v1/backup_data/points",
			vin_method: "POST",
			vin_params: function () {
				return pointParam;
			},
			tableArea: '.cdp_point_grid-content',
			showColumns: true,
			sortName: 'end_timestamp',
			sortOrder: 'desc',
			toolbarId: '#volPointTableToolbar',
			buttonsToolbar: '#volPointTableToolbar .vin_volPointTableToolbar',
			hideColumns: "remarks",
			changeHeightBtn: true, //改变高度按钮
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			searchInput: true, //搜索框
			placeholder: LANG.UI_BACKUP_DATA_TABLE_SEARCH_BY_NAME,
			searchClass: 'searchVolPoint', //搜索框类名
			searchSelector: '.searchVolPoint', //表格选择使用该搜索框
			dateTimePicker: {
				id: 'dateRangePoint'
			}, //时间选择器// 勾选框联动效果
			customTool: {
				beforeInput: getDeleteBtn('deleteVolPoint'),
				afterInput: getRefreshBtn('refreshVolPoint') + `<div class="btn-group dropdown-filter-wrapper"></div>`,
			},
			exportSettings: {
				showBuiltIn: ['json', 'xml', 'csv', 'txt', 'sql', 'excel'], // 需要显示的默认导出项
				custom: [
					{
						label: LANG.UI_TOOLS_TABLE_EXPORT_ALL_EXCEL,
						class: 'export-all-excel'
					}
				]
			},
			// 勾选框联动效果
			onCheck: () => {
				modifyDelStyle('volPointTable', 'deleteVolPoint');
			},
			onUncheck: () => {
				modifyDelStyle('volPointTable', 'deleteVolPoint');
			},
			onUncheckAll: () => {
				modifyDelStyle('volPointTable', 'deleteVolPoint');
			},
			onCheckAll: () => {
				modifyDelStyle('volPointTable', 'deleteVolPoint');
			},
			onPostBody: function () {
				modifyDelStyle('volPointTable', 'deleteVolPoint');
				if ($.inArray('p_backup_data_operate', CONF.PERMISSION_ARR) == -1) {
					$('#volPointTable').bootstrapTable('hideColumn', 'detail');
				}

				const exportOptions = {
					toolbarId: 'volPointTableToolbar',
					url: '/api/v1/backup_data/points/export',
					fileName: LANG.UI_BACKUP_DATA_LABEL_TIMEPOINT_LIST
				}

				// 监听导出全部数据
				exportAllTableData(exportOptions);
			},
			LoadSuccess: () => {
				Metronic.unblockUI('.pointListBody');
			},
			LoadError: () => {
				Metronic.unblockUI('.pointListBody');
			},
			columns: columns,
		}

		$('#volPointTable').baseTableConfig().init(options);
		$('#volPointTable .dropdown-filter-wrapper').myFilter({ showModule: false, showTaskType: false, showTaskStatus: true });
		initVolListener();
	}
	let initVolListener = () => {
		// 删除备份集
		$('#deleteVolPoint').on('click', () => {
			// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
			let user_uuid_arr = $.map($('#volPointTable').bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: 1, user_uuid: user_uuid_arr.join(','), auth: 'data_manager' }, () => {
				$('#deleteType').val(TREE_TYPE.VOL_POINT);
				$('.delete-tips-common').html(LANG.UI_BACKUP_DATA_OPERATION_DELETE_BATCH_TIPS_POINT);
				deleteBatchPoint(TREE_TYPE.VOL_POINT);
			});
		});
		// 改变表格高度
		$('.change_height').on('click', () => changeHeight('volPoint'));
		// 过滤确认
		$('#volPointTableToolbar .filterSubmit').on('click', (event) => {
			Metronic.blockUI({ target: '.pointListBody', animate: true });
			const plugin = $('#volPointTableToolbar .dropdown-filter-wrapper').data('myFilter');
			volParams = plugin.getFilter();
			$('#volPointTable').bootstrapTable('refresh', {
				query: volParams
			});
			modifyDelStyle('volPointTable', 'deleteVolPoint');
			event.preventDefault();
		});
		// 刷新
		$('#refreshVolPoint').on('click', function () {
			Metronic.blockUI({ target: '.pointListBody', animate: true });
			$('#volPointTable').bootstrapTable('refresh', {
				query: volParams
			});
		});
	}
	/**
	 * 初始化标签点表格
	 * @param {object} params 参数
	 */
	let initVolTagTable = (params) => {
		let options = {
			vin_url: "/api/v1/backup_data/vol/list",
			vin_method: "GET",
			vin_params: function () {
				return params;
			},
			sortName: 'label_timestamp',
			sortOrder: 'desc',
			changeHeightBtn: false, //改变高度按钮
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: false, //可变宽度
			onPostBody: function () {
				$('#tagTable th[data-field="storage"]').css('width', '35%');
			},
			columns: [
				{
					field: 'label_timestamp',
					title: LANG.UI_BACKUP_DATA_DETAIL_LABEL_CREATE_TIME,
					clickToSelect: false,
				},
				{
					field: 'storage',
					title: LANG.UI_COPY_DATA_STORAGE,
					clickToSelect: false,
				},
				{
					field: 'remarks',
					title: LANG.UI_PUBLIC_REMARK,
					clickToSelect: false,
				},
				{
					field: 'detail',
					title: LANG.UI_PUBLIC_OPERATION,
					sortable: false,
					events: tagEvents,
					opButton: true,
					clickToSelect: false,
					formatter: function (value, row, index) {
						let html = `<div class="btn-group point-actions">
										<button style="line-height:16px" type="button" class="btn btn-primary brr2 p-lr8 btn-sm dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-delay="1000" data-close-others="true">
										${LANG.UI_PUBLIC_OPERATION}<i class="fa fa-angle-down"></i>
										</button>
										<ul class="dropdown-menu" role="menu">
										${getOpLi([2, 4], row)}
										</ul>
									</div>`
						return html;
					}
				}
			],
		}

		if (!initTagFlag) {
			$('#tagTable').baseTableConfig().init(options);
			initTagFlag = true;
		} else {
			$('#tagTable').bootstrapTable('destroy');
			$('#tagTable').baseTableConfig().init(options);
		}
		// 改变表格高度
		$('.change_height').on('click', () => changeHeight('tag'));
	}
	let tagEvents = {
		// 添加时间点备注
		'click .point_action-2': (e, value, row, index) => {
			// 操作权限判断，需要传归属用户的user_uuid
			checkOperateAuth({ type: 1, user_uuid: row.user_uuid, auth: 'data_manager' }, () => {
				bootbox.prompt({
					title: LANG.UI_DATA_ADD_REMARK,
					value: row.remarks,
					callback: function (result) {
						if (result == null || $.trim(result) == value) return;
						submitTagRemark($.trim(result), row);
					}
				});
			});
		},
		'click .point_action-4': (e, value, row, index) => {
			// 操作权限判断，需要传归属用户的user_uuid
			checkOperateAuth({ type: 1, user_uuid: row.user_uuid, auth: 'data_manager' }, () => {
				bootbox.confirm({
					title: LANG.UI_VOL_CDP_BACKUP_SET_LABEL_POINT_DELETE,
					message: LANG.UI_VOL_CDP_BACKUP_SET_LABEL_POINT_DELETE_MESSAGE,
					callback: debounce(function (r) {
						if (!r) return;
						initErrorFlag = false;
						bootbox.prompt({
							title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES,
							inputType: 'password',
							callback: function (result) {
								if (result == null) return;
								if (hex_md5(result) == _UserPassword) {
									_delete_level = delLevel.singleBackupSet;
									submitTagDelete(row);
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
							}
						});
					}, 300)
				});
			})
		}
	}
	/**
	 * 删除标签点
	 * @param {object} row 标签点信息
	 */
	let submitTagDelete = (row) => {
		Metronic.blockUI({ target: '#tab_backup_label_point', animate: true });
		let data = {
			backup_agent_id: row.backup_agent_id,
			label_timestamp: row.label_timestamp,
			node_uuid: row.node_uuid,
		}
		let deleteBack = (res) => {
			operateResponseList(res);
			Metronic.unblockUI('#tab_backup_label_point');
			$('#tagTable').bootstrapTable('refresh');
		}
		pAjaxRequest(data, "/api/v1/backup_data/vol/list", "DELETE", deleteBack, true);
	}
	/**
	 * 设置标签点备注
	 * @param {string} remarks 备注信息
	 * @param {object} row 标签点信息
	 * @returns 
	 */
	let submitTagRemark = (remarks, row) => {
		var byteLength = remarks.replace(/[^\u0000-\u00ff]/g, "aa").length;
		if (byteLength > 256) {
			UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABELNODE, LANG.UI_VOL_CDP_JOB_DETAILS_LABELNODE_MESSAGE);
			return false;
		}
		let data = {
			label_id: row.label_id,
			backup_agent_id: row.backup_agent_id,
			remark: remarks
		}
		let remarkBack = (res) => {
			operateResponseList(res);
			$('#tagTable').bootstrapTable('refresh');
		}
		pAjaxRequest(data, "/api/v1/backup_data/vol/remark", "GET", remarkBack, true);
	}

	let expandEvents = {
		'click .root-collapsed': function (e, value, row, index) {
			let checked = row.checked;
			let children = JSON.parse(row.children);
			if (children.length > 0) {
				if (checked) {
					for (let i = 0; i < children.length; i++) {
						children[i].checked = true;
					}
				}
				$('#pointTable').bootstrapTable('append', children);
			}
		},
		// 查看时间点详情
		'click .point_action-1': (e, value, row, index) => {
			let data = {};
			// 异地数据特殊处理，需要传入参数
			if (row.remote_flag) {
				data = {
					dir_path: row.dir_path,
					detail: {
						module_type: parseInt(row.module_type),
						sub_module_type: row.sub_module_type ? parseInt(row.sub_module_type) : 0,
						timepoint: row.timepoint,
						timepoint_uuid: row.timepoint_uuid,
						storage_uuid: row.storage_uuid
					}
				}
			}
			$('.page-content').initPointDetailDrawer({ timepoint_uuid: row.timepoint_uuid, remote_flag: row.remote_flag, data: data });
		},
	}
	/**
	 * 获取存储uuid
	 * @returns array
	 */
	const getStorage = () => {
		let $this = $('.storage-tab-item.active');
		let id = $this.data('id');
		let storageArray = id ? [id] : [];
		return storageArray;
	}
	/**
	 * 树节点勾选回调
	 * @param {object} e 树对象
	 * @param {string} id 树id
	 * @param {object} node 当前节点
	 * @returns 
	 */
	let nodeCheck = (e, id, node) => {
		Metronic.blockUI({ target: '.pointListBody', animate: true });
		let checked = node.checked;
		zTree.checkAllNodes(false);
		if (checked) {
			zTree.checkNode(node, true);
		}
		let data = {
			task_uuid: taskGridFlag ? node.task_uuid : '',
			module_type: parseInt(node.module_type),
			sub_module_type: parseInt(node.sub_module_type),
			task_type: [node.task_type],
			taskGridFlag: taskGridFlag,
			storage_type: node.storage_type,
			storage_uuid: getStorage(),
			remote_ip: node.remote_ip ?? '',
			sub_type: node.sub_type ?? 0,
			subTaskFlag: node.subTaskFlag ?? false,
			abnormal_chain_flag: node.abnormal_chain_flag,
			vcenter_uuid: node.vcenter_uuid ?? '',
		};
		let allCheckNodes = zTree.getCheckedNodes(true);
		if (allCheckNodes.length === 0) {
			Metronic.unblockUI('.pointListBody');
			return false;
		}
		// 返回item_uuid数组
		let item_uuid = allCheckNodes.filter(checkNode => checkNode.type === TREE_TYPE.ITEM)
			.map(checkNode => checkNode.item_uuid);
		data.item_uuid = item_uuid;
		pointParam = { ...data };
		if (node.module_type == CONF.MODULE_TYPE.DB) {
			data.db_name = node.db_name ?? '';
			data.db_cluster_uuid = node.db_cluster_uuid ?? '';
			data.instance_name = node.instance_name ?? '';
			data.db_type = node.db_type;
			data.db_agent_uuid = node.db_agent_uuid ?? '';
			pointParam = { ...pointParam, ...data };
		}
		$('.noSelectTips').hide();
		$('.timepoint-tips').show();
		// 非文件模块，云存储数据只能按链删除和删除链尾的点
		if (node.module_type == CONF.MODULE_TYPE.FS || node.module_type == CONF.MODULE_TYPE.NAS) {
			$('.cloud_storage_tips').hide();
		} else {
			$('.cloud_storage_tips').show()
		}
		// 异步获取备份对象下的时间点
		if (node.module_type == CONF.MODULE_TYPE.VOL_CDP || node.module_type == CONF.MODULE_TYPE.DB_CDP) {
			let vol_uuid = allCheckNodes.filter(checkNode => checkNode.type === 'vol')
				.map(checkNode => checkNode.vol_uuid);
			data.vol_uuid = vol_uuid;
			data.backup_set_id = node.backup_set_id;
			data_verify_uuid = node.timepoint_uuid
			data.task_uuid = node.task_uuid;
			pointParam = { ...pointParam, ...data };
			let tagData = {
				sortColumn: 'label.label_timestamp',
				sortType: 'asc',
				item_uuid: node.item_uuid,
			}
			initVolTagTable(tagData);
			if (!initVolFlag) {
				initVolFlag = true;
				if (node.module_type == CONF.MODULE_TYPE.VOL_CDP) {
					initDataVerifyHistoryTable();
					getSafepointRecords();
					$('#virus_history_li').show();
					$('#data_verify_li').show();
				} else {
					$('#virus_history_li').hide();
					$('#data_verify_li').hide();
				}
				initVolPointTable(data);
			} else {
				if (node.module_type == CONF.MODULE_TYPE.VOL_CDP) {
					$('#dataVerifyTable').bootstrapTable('refresh');
					$('#cmCdpDataSafePointTable').bootstrapTable('refresh');
				}
				$('#volPointTable').bootstrapTable('refresh');
			}
			$('.real_grid-container').show()
		} else {
			initPointTable();
			$('.point_grid-container').show();
		}
	}
	let nodeSelect = (treeId, treeNode, expandFlag) => {
		if (treeNode.more) {
			getMoreTree(treeId, treeNode);
		} else {
			zTree.checkNode(treeNode, true, true, true);
		}
	}
	let getMoreTree = (treeId, treeNode) => {
		//判断父节点下的子节点是否全选
		let addTreeNode = (res) => {
			if (res.success) {
				$.fn.zTree.getZTreeObj(treeId).removeNode(treeNode);
				$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode.getParentNode(), res.data.tree, true);
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
			}
		}
		pAjaxRequest({ task_uuid: treeNode.task_uuid, offset: treeNode.offset, limit: treeNode.limit, sort: TREE_SORT, order: TREE_ORDER, more_flag: true }, "/api/v1/backup_data/task_tree", "POST", addTreeNode, true);
	}
	/**
	 * 初始化树
	 * @param {object} node 节点信息
	 * @param {string} id 树id
	 */
	let initTree = (node, id) => {
		Metronic.unblockUI('.pointListBody');
		let setting = {
			check: {
				enable: true,
				nocheckInherit: false,
			},
			data: {
				simpleData: {
					enable: true,
					idKey: "id",
					pIdKey: "pId",
					rootPId: 0
				},
				key: {
					title: "title"
				}
			},
			callback: {
				beforeClick: nodeSelect,
				onCheck: nodeCheck,
			},
			view: {
				nameIsHTML: true,
				fontCss: function (treeId, treeNode) {
					let style = {};
					let width = $('.task_item-tree').width() - 100;
					if (treeNode.type == 'display_node') {
						style = { 'padding-left': '18px', 'max-width': width + 'px', 'white-space': 'nowrap', 'overflow': 'hidden', 'text-overflow': 'ellipsis', };
					} else {
						style = { 'max-width': width + 'px', 'white-space': 'nowrap', 'overflow': 'hidden', 'text-overflow': 'ellipsis', };
					}
					return style;
				}
			}
		};
		zTree = $.fn.zTree.init($(id), setting, node);
	};
	/**
	 * 初始化时间点列表抽屉
	 * @param {*} module_type 模块类型
	 * @param {*} treeId 树id
	 * @param {*} param 请求参数
	 * @param {*} realFlag 实时标志
	 */
	const initDrawerInfo = (module_type, treeId, param, realFlag) => {
		Metronic.blockUI({ target: '.pointListBody', animate: true });
		let data = { ...param };
		data.offset = 0;
		data.limit = _MORE_SIZE;
		data.sort = TREE_SORT;
		data.order = TREE_ORDER;
		$('.point_grid-container').hide()
		$('.real_grid-container').hide()
		let pointList = (res) => {
			Metronic.unblockUI('.pointListBody');
			if (res.success) {
				initTaskCard(res.data, realFlag);
				emptyTree();
				// 整机实时需要实现树结构
				if (module_type == CONF.MODULE_TYPE.VOL_CDP || module_type == CONF.MODULE_TYPE.DB_CDP) {
					initTree(res.data.tree, treeId);
					if (module_type == CONF.MODULE_TYPE.DB_CDP) {
						$('.os-cdp-div .vol-tips').hide();
						$('.os-cdp-div #task_plot_li').hide();
					}
				} else {
					if (taskGridFlag) {
						$('.search_sort-div').show();
						initSearchTask(res.data, data);
						initTree(res.data.tree, treeId);
					}
				}
			}
		};
		if (taskGridFlag || data.module_type == CONF.MODULE_TYPE.VOL_CDP) {
			pAjaxRequest(data, "/api/v1/backup_data/task_tree", "POST", pointList, true);
		} else {
			Metronic.unblockUI('.pointListBody');
			initTaskCard(data, realFlag);
		}
	}
	/**
	 * 初始化时间点列表抽屉
	 * @param {int} module_type 模块类型
	 * @param {int} task_type 任务类型
	 * @param {string} task_uuid 任务uuid
	 * @param {string} item_uuid 对象uuid
	 */
	let initTaskDrawer = (module_type, task_type, task_uuid = '', item_uuid = '', row) => {
		emptyTree();
		let treeId = '#backupDataTree';
		let realFlag = false;
		// 实时模块使用不同的抽屉 需要单独处理
		if (REAL_MODULE_KEY.includes(module_type)) {
			$('#realPointDrawer').drawer('toggle');
			treeId = '#realDataTree';
			realFlag = true;
			$('.real_grid-container').hide();
		} else {
			$('#pointListDrawer').drawer('toggle');
		}
		let storage_uuid = '';
		if (storageFlag) {
			storage_uuid = $('.storage-tab-list .active').data('id');
		}
		let data = {
			task_uuid: taskGridFlag ? (task_uuid || '') : '',
			module_type: module_type,
			sub_module_type: row.sub_module_type,
			item_uuid: item_uuid ? [item_uuid] : '',
			task_type: task_type || '',
			tree_flag: true,
			taskGridFlag: taskGridFlag,
			storage_uuid: storageFlag && storage_uuid ? [storage_uuid] : '',
			sort: 'timepoint',
			order: 'desc',
			subTaskFlag: row.subTaskFlag,
			db_cluster_uuid: row.db_cluster_uuid ?? '',
			db_name: row.db_name ?? '',
			db_type: row.db_type ?? '',
			instance_name: row.instance_name ?? '',
			db_agent_uuid: row.db_agent_uuid ?? '',
			hasChildren: row.hasChildren ?? false,
			task_name: row.task_name,
			sub_type_des: row.sub_type_des,
			item_name: row.item_name,
			task_type_des: row.task_type_des,
			module_type_des: row.module_type_des,
			abnormal_chain_flag: row.abnormal_chain_flag,
			db_path: row.db_path ?? '',
			vcenter_uuid: row.vcenter_uuid ?? '',
		}
		if (module_type == CONF.MODULE_TYPE.VOL_CDP) {
			data.sort = "vol_name";
			data.order = "asc";
		}
		$('#delModuleType').val(module_type);
		initDrawerInfo(module_type, treeId, data, realFlag);
		$('.noSelectTips').show();
		$('.timepoint-tips').hide();
		// 备份对象抽屉视图没有树，直接展示数据表格
		if (!taskGridFlag) {
			data.tree_flag = false;
			pointParam = { ...pointParam, ...data };
			delete pointParam.limit;
			delete pointParam.offset;
			$('.noSelectTips').hide();
			$('.timepoint-tips').show();
			// 非文件模块，云存储数据只能按链删除和删除链尾的点
			if (module_type == CONF.MODULE_TYPE.FS || module_type == CONF.MODULE_TYPE.NAS) {
				$('.cloud_storage_tips').hide();
			} else {
				$('.cloud_storage_tips').show()
			}
			// 整机实时需要单独初始化显示
			if (module_type == CONF.MODULE_TYPE.VOL_CDP) {
				let tagData = {
					sortColumn: 'label.label_timestamp',
					sortType: 'asc',
					item_uuid: item_uuid,
				}
				initVolTagTable(tagData);
				$('.real_grid-container').hide()
				$('.noSelectTips').show();
				$('.timepoint-tips').hide();
				$('#virus_history_li').show();
				$('#data_verify_li').show();
			} else if (module_type == CONF.MODULE_TYPE.DB_CDP) {
				$('.real_grid-container').show()
				$('#virus_history_li').hide();
				$('#data_verify_li').hide();
				if (!initVolFlag) {
					initVolFlag = true;
					data_verify_uuid = row.timepoint_uuid;
					initVolPointTable(data);
				} else {
					$('#volPointTable').bootstrapTable('refresh');
					$('#dataVerifyTable').bootstrapTable('refresh');
					$('#cmCdpDataSafePointTable').bootstrapTable('refresh');
				}
			} else {
				initPointTable();
				$('.point_grid-container').show()
			}
		}
	};
	/**
	 * 初始化异地数据抽屉
	 * @param {object} row 异地任务信息
	 */
	let initRemoteDrawer = (row) => {
		let data = {
			parent_tree_uuid: row.task_uuid,
			storage_uuid: row.storage_uuid,
			module_type: row.module_type,
			sub_module_type: row.sub_module_type,
			task_name: row.task_name,
			taskGridFlag: taskGridFlag,
			remote_ip: row.remote_ip,
		}
		Metronic.blockUI({ target: '.pointListBody', animate: true });
		let initRemoteTree = (res) => {
			Metronic.unblockUI('.pointListBody');
			if (res.success) {
				initTaskCard(res.data);
				initTree(res.data.tree, '#backupDataTree');
			}
		}
		pAjaxRequest(data, "/api/v1/backup_data/remote/tree", "GET", initRemoteTree, true);

	}
	// 清空树节点
	let emptyTree = () => {
		window.localStorage.removeItem('pointTable_BsTable');
		$('#backupDataTree').empty();
		$('#drawerSort').empty().hide();
	}
	// ----------------时间点列表抽屉--end
	// ----------------时间点表格事件--start
	// 时间点表格监听事件
	let initPointListener = () => {
		$('#refreshPoint').on('click', () => {
			Metronic.blockUI({ target: '.pointListBody', animate: true });
			pointParam.taskGridFlag = taskGridFlag;
			$('#pointTable').bootstrapTable('refresh', {
				query: pointParam
			});
			modifyDelStyle('pointTable', 'deletePoint');
		})
		// 删除时间点
		$('#deletePoint').on('click', () => {
			// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
			let user_uuid_arr = $.map($('#pointTable').bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: 1, user_uuid: user_uuid_arr.join(','), auth: 'data_manager' }, () => {
				deleteBatchPoint(TREE_TYPE.POINT);
			});
		});
		// 改变表格高度
		$('.change_height').on('click', () => changeHeight('point'));

		// 监听表格 - 日期范围选择组件派发的数据，以更新表格
		window.$on(`pointDateRange-updateDateRangeEvent`, (data) => {
			// 记录选择的开始时间和结束时间，用于过滤搜索的联动
			pointParam.start_time = data.startTime;
			pointParam.end_time = data.endTime;
			$(`#pointTable`).bootstrapTable('refresh');
		});
	};
	/**
	 * 初始化详情基本信息 时间点  uuid
	 * @param {object} data 时间点信息 
	 * @param {object} res 时间点相信信息
	 */
	let initBasicInfo = (data, res = null) => {
		let path = '';
		let pathLabel = '';
		let dirPath = '';
		let displayPath = 'flex';
		let timeNameLabel = LANG.UI_BACKUP_DATA_DETAIL_LABEL_TIMEPOINT;
		let timeName = data.timepoint;
		// 根据不同模块显示不同路径、列表
		if (res) {
			switch (data.module_type) {
				case CONF.MODULE_TYPE.VM:
				case CONF.MODULE_TYPE.PRIVATE_CLOUD:
					pathLabel = LANG.UI_CLOUD_PLATFORM_VM_PATH;
					path = `<label>${pathLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span title="${res.data.dir_path}">${res.data.dir_path}</span>`;
					break;
				case CONF.MODULE_TYPE.PUBLIC_CLOUD:
					pathLabel = LANG.UI_TASK_AWS_INSTANCE_PATH;
					path = `<label>${pathLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span title="${res.data.dir_path}">${res.data.dir_path}</span>`;
					break;
				case CONF.MODULE_TYPE.FS:
					pathLabel = LANG.UI_FILE_FILE_LIST;
					dirPath = JSON.parse(res.data.dir_path).join(',');
					path = `<label>${pathLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
					break;
				case CONF.MODULE_TYPE.DB:
					pathLabel = LANG.UI_DB_PATH;
					path = `<label>${pathLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span title="${res.data.dir_path}">${res.data.dir_path}</span>`;
					break;
				case CONF.MODULE_TYPE.NAS:
					pathLabel = LANG.UI_NAS_BACKUP_PATH;
					dirPath = JSON.parse(res.data.dir_path).join(',');
					path = `<label>${pathLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
					break;
				case CONF.MODULE_TYPE.M365:
					pathLabel = LANG.UI_MICROSOFT365_BACKUP_DATA_LIST;
					dirPath = JSON.parse(res.data.dir_path).join(',');
					path = `<label>${pathLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
					break;
				case CONF.MODULE_TYPE.HADOOP:
					pathLabel = LANG.UI_FILE_BAK_PATH;
					dirPath = JSON.parse(res.data.dir_path).join(',');
					path = `<label>${pathLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
					break;
				case CONF.MODULE_TYPE.OBS:
					pathLabel = LANG.UI_OBS_BACKUP_PATH;
					dirPath = JSON.parse(res.data.dir_path).join(',');
					path = `<label>${pathLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
					break;
				case CONF.MODULE_TYPE.OS:
					if (data.sub_module_type == 1) {
						pathLabel = LANG.UI_BACKUP_DATA_TABLE_LABEL_PARITION_INFO;
						$('.other-info').append(`
							<div class="three_tree osDiskTreeDiv" style="display: flex;padding: 12px 0;">
								<label>${pathLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>
								<ul id="diskTree" class="ztree"></ul>
							</div>`);
					} else {
						pathLabel = LANG.UI_BACKUP_DATA_TABLE_LABEL_VOLUME_INFO;
						dirPath = JSON.parse(res.data.dir_path).join(',');
						path = `<label>${pathLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><textarea readonly>${dirPath.replaceAll(',', '\n')}</textarea>`;
					}
					break;
				case CONF.MODULE_TYPE.VOL_CDP:
					timeNameLabel = LANG.UI_DATA_TYPE_BACKUPSET;
					timeName = data.start_timestamp + '  -  ' + data.end_timestamp;
					displayPath = 'none';
					break;
				case CONF.MODULE_TYPE.KUBERNETES:
					displayPath = 'none';
					break;
			}
		} else {
			displayPath = 'none';
		}
		let html = `<div class="info-timepoint">
						<label class="point-info-label">${timeNameLabel}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>${timeName}
					</div>
					<div class="info-timepoint_uuid">
						<label class="point-info-label">${LANG.UI_BACKUP_DATA_DETAIL_LABEL_TIMEPOINT_UUID}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span id="pointUUid">${data.timepoint_uuid}</span><span id="copyPointUUid" class="clip-icon"><i class="viconfont vicon-fuzhi"></i></span>
					</div>
					<div class="info-storage_uuid">
						<label class="point-info-label">${LANG.UI_BACKUP_DATA_DETAIL_LABEL_STORAGE_UUID}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span id="storageUUid">${data.storage_uuid}</span><span id="copyStorageUUid" class="clip-icon"><i class="viconfont vicon-fuzhi"></i></span>
					</div>`;
		$('.basic-info').append(html);
		if (data.module_type == CONF.MODULE_TYPE.OS && data.sub_module_type == 1) {
			let zNodes = JSON.parse(res.data.dir_path);
			let setting = {
				check: {
					enable: true,
					nocheckInherit: false,
				},
				data: {
					simpleData: {
						enable: true
					},
					key: {
						title: "title"
					}
				},
				callback: {
				},
			};
			//初始化树对象
			let zTree = $.fn.zTree.init($("#diskTree"), setting, zNodes);
		} else {
			$('.other-info').append(`<div class="info-dir_path" style="display:${displayPath}">${path}</div>`);
		}
		// 一键复制
		$('#copyPointUUid').myClipboard({ target: '#pointUUid', });
		$('#copyStorageUUid').myClipboard({ target: '#storageUUid', });
	}
	/**
	 * 初始化病毒信息 状态 扫描时间  感染列表
	 * @param {object} dom 容器
	 * @param {object} data 时间点信息
	 */
	let initVirusInfo = (data, timepoint_uuid) => {
		// 未授权不显示
		if (!CONF.FUNCTIONS.includes('virusKill')) {
			return;
		}
		const PERMISSION = CONF.PERMISSION;
		if (!MODULE_SUPPORT_VIRUS.includes(data.module_type) && PERMISSION.includes('virus')) {
			return true;
		}
		let { last_virus_scan_time, virus_scan_status, virus_scan_status_des } = data.details.virus_info
		let infectList = '';
		let scanTime = '';
		// 感染状态显示感染文件列表
		if (virus_scan_status == 3) {
			infectList = `<div class="virus-list  row-info">
							<label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_INFECT_FILE_LIST}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>
							<div id="spinnerNum" style="margin-right: 4px;">
								<div class="input-group spinner-group">
									<input type="number" id="spinnerNumInput" class="spinner-input form-control input-sm"  placeholder="${LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_LIST_NUM}" style="width: 128px;">
								</div>
							</div>
							<button type="button" class="btn btn-light-primary get-virus-list" style="min-width:auto">${LANG.UI_PUBLIC_GET}</button>
							</div>`;
		}
		// 未扫描不显示扫描时间
		if (virus_scan_status && virus_scan_status != CONF.VIRUS_STATUS.NO_SCAN && last_virus_scan_time) {
			scanTime = `<div class="virus-scan-time"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_SCAN_TIME}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span class="label-value">${last_virus_scan_time}</span></div>`
		}
		let value = CONF.POINT_STATUS.UNKNOWN;
		switch (virus_scan_status) {
			case CONF.VIRUS_STATUS.NO_SCAN:
				value = CONF.POINT_STATUS.UNKNOWN;
				break;
			case CONF.VIRUS_STATUS.SCANNING:
				value = CONF.POINT_STATUS.OPERATING;
				break;
			case CONF.VIRUS_STATUS.SAFE:
				value = CONF.POINT_STATUS.NORMAL;
				break;
			case CONF.VIRUS_STATUS.INFECTED:
			case CONF.VIRUS_STATUS.INFECTED_PARTLY_SCAN:
				value = CONF.POINT_STATUS.ABNORMAL;
				break;
		}
		let html = `<div class="virus-info">
						<div class="virus-status row-info"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_STATUS}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>${getStatusLabel(virus_scan_status_des, value)}</div>
						${scanTime}
						${infectList}
						<div class="table-container"><table id="virusListTable"></table></div>
					</div>`;
		$('.other-info').append(html);
		$('.get-virus-list').on('click', () => initVirusTable(data, timepoint_uuid));
	};
	/**
	 * 获取文件感染列表
	 * @param {*} data 时间点信息
	 * @param {*} timepoint_uuid  时间点uuid
	 * @returns 
	 */
	const initVirusTable = (data, timepoint_uuid) => {
		let max_num = parseInt($('#spinnerNumInput').val());
		let params = {
			max_num: max_num,
			result_file_path: data.virus_list,
			timepoint_uuid: timepoint_uuid
		}
		// 判断输入获取数量 必须是大于0 的数字
		if (isNaN(max_num) || max_num <= 0) {
			UIToastr.showWarning(LANG.UI_BACKUP_DATA_DETAIL_LABEL_GET_VIRUS_LIST, LANG.UI_BACKUP_DATA_DETAIL_LABEL_VIRUS_LIST_NUM_TIPS);
			return false;
		}
		let virusListBack = (res) => {
			Metronic.unblockUI('.virus-list');
			let dataList = res.data.result_list;
			// 没有感染列表直接返回
			if (!data || data.length == 0) {
				return false;
			}
			let table = $('#virusListTable');
			let html = `<thead style="position:sticky;top:0">
							<tr>
								<td style="width:30%">${LANG.UI_BACKUP_DATA_VRIUS_TABLE_HEAD_LABEL_PATH}</td>
								<td>${LANG.UI_BACKUP_DATA_VRIUS_TABLE_HEAD_LABEL_SIZE}</td>
								<td>${LANG.UI_BACKUP_DATA_VRIUS_TABLE_HEAD_LABEL_VIRUS}</td>
								<td>${LANG.UI_BACKUP_DATA_VRIUS_TABLE_HEAD_LABEL_STATUS}</td>
								<td>${LANG.UI_BACKUP_DATA_VRIUS_TABLE_HEAD_LABEL_MD5}</td>
							</tr>
						</thead>
						<tbody>`;
			dataList.forEach((list) => {
				html += `<tr style="width:30%">
							<td>${list.file_path}</td>
							<td>${list.file_size}</td>
							<td>${list.file_virus_name}</td>
							<td>${list.file_state}</td>
							<td>${list.file_md5}</td>
						</tr>`
			});
			html += '</tbody>';
			table.attr('style', 'height:100px;display:block;overflow:auto;position:relative')
			table.html(html);
		}
		Metronic.blockUI({ target: '.virus-list', animate: true });
		pAjaxRequest(params, `/api/v1/backup_data/points/virus_list`, "GET", virusListBack, true);
	};
	/**
	 * 初始化数据完整性信息
	 * @param {object} data 时间点信息
	 */
	let initIntegrityInfo = (data) => {
		// 未授权不显示
		if (!CONF.FUNCTIONS.includes('integrity')) {
			return;
		}
		if (MODULE_NOT_SUPPORT_INTEGRITY.includes(data.module_type)) {
			return true;
		}
		let { integrity_check_flag, integrity_check_status, integrity_check_status_des, last_integrity_check_time } = data;
		let value = CONF.POINT_STATUS.UNKNOWN;
		let showClass = '';
		// 校验过才显示时间
		if (integrity_check_flag == 1) {
			switch (integrity_check_status) {
				case CONF.INTEGRITY_STATUS.NORMAL:
					value = CONF.POINT_STATUS.NORMAL;
					break;
				case CONF.INTEGRITY_STATUS.BROKEN:
					value = CONF.POINT_STATUS.ABNORMAL;
					break;
				case CONF.INTEGRITY_STATUS.NO_CHECK:
					value = CONF.POINT_STATUS.UNKNOWN;
					// 未检测不显示上次检测时间
					showClass = 'display-none';
					break;
			}
			// 未校验过不显示时间
		} else {
			integrity_check_status_des = LANG.UI_BACKUP_DATA_POINT_DES_NOT_CONFIGED;
			showClass = 'display-none';
		}
		// 没有时间不显示
		if (!last_integrity_check_time) {
			showClass = 'display-none';
		}
		let html = `<div class="integrity-info">
						<div class="integrity-status row-info"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_INTEGRITY_STATUS}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>${getStatusLabel(integrity_check_status_des, value)}</div>
						<div class="integrity-check-time row-info ${showClass}"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_INTEGRITY_LAST_TIME}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span class="label-value">${last_integrity_check_time}</span></div>
					</div>`;
		$('.other-info').append(html);
	};
	/**
	 * 初始化worm信息
	 * @param {object} data 时间点信息
	 */
	let initWormInfo = (data) => {
		// 未授权不显示
		if (!CONF.FUNCTIONS.includes('worm')) {
			return;
		}
		const { worm_flag, worm_expire_left } = data;
		let wormValue = LANG.UI_BACKUP_DATA_POINT_DES_NOT_CONFIGED;
		let showClass = '';
		// 未配置显示未配置，已配置显示天数
		if (worm_flag == 1) {
			wormValue = LANG.UI_BACKUP_DATA_POINT_DES_CONFIGED;
		} else {
			showClass = 'display-none';
		}
		let html = `<div class="worm-info">
						<div class="worm-status row-info"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_WORM}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span class="label-value"><span class="label label-sm label-default">${wormValue}</span></span></div>
						<div class="worm-days row-info ${showClass}"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_WORM_DATA}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label><span class="label-value">${worm_expire_left}</span></div>
					</div>`;
		$('.other-info').append(html);
	};
	/**
	 * 初始化时间点合并状态  合并失败才显示 并显示失败原因
	 * @param {object} data 时间点信息
	 */
	let initMergeInfo = (data) => {
		let html = '';
		let status = data.details.merge_status;
		let value = CONF.POINT_STATUS.NORMAL;
		// 合并中、依赖点失败、回滚中都是异常
		if ([CONF.MERGE_STATUS.MERGING, CONF.MERGE_STATUS.DEPEND_MERGE_FAILED, CONF.MERGE_STATUS.ROLL_BACK].includes(status)) {
			value = CONF.POINT_STATUS.ABNORMAL;
		}
		// 合并失败为失败
		if (status == CONF.MERGE_STATUS.FAILED) {
			value = CONF.POINT_STATUS.ERROR;
		}
		// 只有异常和错误才显示
		if (value != CONF.POINT_STATUS.NORMAL) {
			html = `<div class="merge-info">
					<div class="merge-status row-info"><label>${LANG.UI_BACKUP_DATA_DETAIL_LABEL_MERGE_STATUS}${LANG.UI_BACKUP_DATA_DETAIL_LABEL_COLON}</label>${getStatusLabel(data.details.merge_status_des, value)}</div>
				</div>`;
		}
		// <div class="virus-scan-time"><label>${UI_BACKUP_DATA_DETAIL_LABEL_FAILED}</label><span class="label-value">${mergeInfo.fail_reason}</span></div>
		$('.other-info').append(html);
	}
	// 初始化时间点详细信息
	let initPointDetail = (row) => {
		let params = {
			timepoint_uuid: row.timepoint_uuid,
			module_type: row.module_type,
			item_uuid: row.item_uuid,
			task_uuid: row.task_uuid
		}
		let pointsBack = (res) => {
			$('.basic-info').empty();
			$('.other-info').empty();
			//分发选择任务模态框
			$('#pointDetail').modal({ "width": "600px" })
			// 基本信息
			initBasicInfo(row, res);
			if (!row.tape_storage_flag) {
				// 数据完整性信息
				initIntegrityInfo(row.details.integrity_info);
				// worm信息
				initWormInfo(row.details.worm_info);
				// 病毒查杀信息
				initVirusInfo(row, row.details.timepoint_uuid);
			}
			// 时间点合并信息
			initMergeInfo(row);
		}
		pAjaxRequest(params, `/api/v1/backup_data/points/path`, "GET", pointsBack, true); backup_data_points_details
	};
	/**
	 * 提交备注
	 * @param {string} remark 备注信息
	 * @param {string} uuid 时间点uuid
	 * @param {string} node_uuid 节点uuid
	 */
	let submitRemark = function (remark, uuid, node_uuid) {
		let remarkBack = function (res) {
			if (operateResponseList(res)) {
				$('#pointTable').bootstrapTable('refresh');
			};
			modifyDelStyle('pointTable', 'deletePoint');
		};
		pAjaxRequest({ remarks: remark, time_point_uuid: uuid, node_uuid: node_uuid }, "/api/v1/backup_data/points/remark", "PUT", remarkBack, true);
	}

	//布尔类型转成int1和2
	let boolToInt = function (bool) {
		if (bool) {
			return 1;
		} else {
			return 2;
		}
	}
	let pointEvents = {
		// 添加时间点备注
		'click .point_action-2': (e, value, row, index) => {
			// 操作权限判断，需要传归属用户的user_uuid
			checkOperateAuth({ type: 1, user_uuid: row.user_uuid, auth: 'data_manager' }, () => {
				let remarks = row.remarks;
				let uuid = row.timepoint_uuid;
				bootbox.prompt({
					title: LANG.UI_DATA_ADD_REMARK,
					value: remarks,
					callback: function (result) {
						if (result == null || $.trim(result) == value) return;
						submitRemark($.trim(result), uuid, row.node_uuid);
					}
				});
			});
		},
		// 设置时间点标记
		'click .point_action-3': (e, value, row, index) => {
			// 操作权限判断，需要传归属用户的user_uuid
			checkOperateAuth({ type: 1, user_uuid: row.user_uuid, auth: 'data_manager' }, () => {
				markInfo = row.mark_info || {};
				let markList = '';
				let list = {
					'forever': LANG.UI_BACKUP_DATA_GFS_RETENTION_FOREVER,
				};
				markInfo.importance_flag = row.importance_flag;
				// 初始化标记为未勾选状态
				$("input[name=weekCheck]").iCheck('uncheck');
				$("input[name=monthCheck]").iCheck('uncheck');
				$("input[name=yearCheck]").iCheck('uncheck');
				$("input[name=foreverCheck]").iCheck('uncheck');
				// 只有虚拟机模块有GFS保留标记  #26943 增量/差异点只能勾选 F 点
				if (row.module_type == CONF.MODULE_TYPE.VM && row.task_type != CONF.TASK_TYPE.BACKUP_COPY && row.backup_mode == 1) {
					Object.assign(list, MARK_LIST);
				}
				// 渲染标记列表
				for (const key in list) {
					if (list.hasOwnProperty(key)) {
						markList += `<div class="mark-item">
									<div class="row">
										<div class="col-md-12">
											<label class="vmbackdata-padding_cn vmbackdata-padding_en">
												<input type="checkbox" name="${key}Check" class="icheck">
												<i class="viconfont vicon-remark-${key}"></i>
												${list[key]}
											</label>
										</div>
									</div>
								</div>`;
					}
				}
				// 暂存时间点信息
				$('#mark_uuid').val(row.timepoint_uuid)
				$('#sub_type').val(row.sub_type)
				$('#node_uuid').val(row.node_uuid)
				$('#module_type').val(row.module_type)
				$('#task_type').val(row.task_type)
				$('.markList').html(markList)
				// 初始化勾选框样式
				$('.icheck').iCheck({ checkboxClass: 'icheckbox_square-blue' });
				//设置时间点已经标记的状态
				if (markInfo.weekly_flag) {
					$("input[name=weekCheck]").iCheck('check');
				}
				if (markInfo.monthly_flag) {
					$("input[name=monthCheck]").iCheck('check');
				}
				if (markInfo.yearly_flag) {
					$("input[name=yearCheck]").iCheck('check');
				}
				if (row.importance_flag) {
					$("input[name=foreverCheck]").iCheck('check');
					// 归档备份数据不能设置永久标记
					if (row.src_data_deleted_flag) {
						$("input[name=foreverCheck]").iCheck('disable');
					} else {
						$("input[name=foreverCheck]").iCheck('enable');
					}
				}
				$('#setBackupDataMark').modal('show')
			});
		},
		// 删除单个时间点
		'click .point_action-4': (e, value, row, index) => {
			if (row.remote_flag) {
				deletePoint(row, row.diff_chain_flag);
			} else {
				// 操作权限判断，需要传归属用户的user_uuid
				checkOperateAuth({ type: 1, user_uuid: row.user_uuid, auth: 'data_manager' }, () => {
					deletePoint(row, row.diff_chain_flag);
				});
			}
		},
		// worm保护配置
		'click .point_action-5': (e, value, row, index) => {
			// 操作权限判断，需要传归属用户的user_uuid
			checkOperateAuth({ type: 1, user_uuid: row.user_uuid, auth: 'data_manager' }, () => {
				const setChainModule = [CONF.MODULE_TYPE.FS, CONF.MODULE_TYPE.NAS, CONF.MODULE_TYPE.OBS, CONF.MODULE_TYPE.HADOOP, CONF.MODULE_TYPE.M365];
				// 完备点
				if (row.backup_mode == 1) {
					// 完备点本身
					$('#full_uuid').val(row.timepoint_uuid);
				} else {
					// 父节点是完备点
					$('#full_uuid').val(row.pid);
				}
				// 点击的节点本身
				$('#latest_uuid').val(row.timepoint_uuid);
				let spanStr = $('#wormTimeBtn span');
				let inputNum = $('#wormTimeBtn input');
				spanStr.show();
				inputNum.hide();
				// 显示未配置
				if ((typeof row.details.worm_info.worm_expire_days === 'number' && row.details.worm_info.worm_expire_days >= 0)) {
					$('.worm-set-tips span').html(LANG.UI_BACKUP_DATA_WORM_TIPS_SURE_DELAY);
				} else {
					$('.worm-set-tips span').html(LANG.UI_BACKUP_DATA_WORM_TIPS_SURE_SET);
				}
				if (setChainModule.includes(row.module_type)) {
					$('.worm-tips-content').html(LANG.UI_ABCKUP_DATA_WORM_TIPS_SET_CHAIN);
				} else {
					$('.worm-tips-content').html(LANG.UI_BACKUP_DATA_WORM_TIPS_NEW);
				}
				// 显示保护期限
				spanStr.html(row.details.worm_info.worm_expire_left);
				spanStr.attr('data-value', row.details.worm_info.worm_expire_days);
				$('#wormSettingModal').modal({ "width": "600px" });
			});
		},
		// 恢复跳转
		'click .point_action-6': (e, value, row, index) => {
			let url = "";
			// 按模块跳转恢复页面
			let sub_module_type = row.sub_module_type;
			switch (row.module_type) {
				case CONF.MODULE_TYPE.VM:
				case CONF.MODULE_TYPE.PRIVATE_CLOUD:
				case CONF.MODULE_TYPE.PUBLIC_CLOUD:
					if (CONF.VM_SUB_MODULE.VM == sub_module_type) {
						url = `./content/vm/vmrecover.php?sub_type=${row.sub_type}&`;
					}
					if (CONF.VM_SUB_MODULE.PRIVATE_CLOUD == sub_module_type) {
						url = `./content/vm/vmrecover.php?sub_module_type=2&sub_type=${row.sub_type}&`;
					}
					if (CONF.VM_SUB_MODULE.PUBLIC_CLOUD == sub_module_type) {
						url = `./content/aws/awsrecover.php?sub_type=${row.sub_type}&`;
					}
					break;
				case CONF.MODULE_TYPE.FS:
				case CONF.MODULE_TYPE.HADOOP:
				case CONF.MODULE_TYPE.OBS:
					if (CONF.SUBMODULE_TYPE.FS == sub_module_type) {
						url = "./content/fs/filerecover.php?";
					}
					if (CONF.SUBMODULE_TYPE.NAS == sub_module_type) {
						url = "./content/nas/nasrecover.php?";
					}
					if (CONF.SUBMODULE_TYPE.OBS == sub_module_type) {
						url = "./content/s3/obsrecover.php?";
					}
					if (CONF.SUBMODULE_TYPE.HADOOP == sub_module_type) {
						url = "./content/hadoop/hadoop_recovery.php?";
					}
					break;
				case CONF.MODULE_TYPE.NAS:
					url = "./content/nas/nasrecover.php?";
					break;
				case CONF.MODULE_TYPE.DB:
					url = `./content/dbprotect/dbrecover.php?sub_type=${row.sub_type}&agent_uuid=${row.item_uuid}&cluster_uuid=${row.db_cluster_uuid}&db_name=${row.item_name}&instance_name=${row.instance_name}&`;
					break;
				case CONF.MODULE_TYPE.M365:
					url = "./content/exchange/exchange_recover.php?";
					break;
				case CONF.MODULE_TYPE.OS:
					if (row.sub_module_type == 1) {
						url = "./content/complete_machine_os/machine_os_recover.php?";
					} else {
						url = "./content/os/osrecover.php?";
					}
					break;
				case CONF.MODULE_TYPE.VOL_CDP:
					// 卷
					if (row.dev_type == 1) {
						url = `./content/volcdp/vol_cdp_recover.php?backup_set_id=${row.backup_set_id}&`;
					}
					// 磁盘
					if (row.dev_type == 2) {
						url = `./content/complete_machine_volcdp/cm_volcdp_recovery.php?task_uuid=${row.task_uuid}&backup_set_id=${row.backup_set_id}&`;
					}
					break;
				case CONF.MODULE_TYPE.DB_CDP:
					url = "./content/db/dbrecovery.php?";
					break;
				case CONF.MODULE_TYPE.KUBERNETES:
					url = "./content/kubernetes/kubernetes_recovery.php?";
					break;
			}
			e.preventDefault();
			// 如果授权已过期，提示用户 且不能跳转
			if (!CONF.AUTH_NOT_EXPIRED) {
				UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
				return;
			}
			LOCATION(url + `point_uuid=${row.timepoint_uuid}&item_uuid=${row.item_uuid}&task_uuid=${row.task_uuid}`, 'recovery');
		},
		// 瞬时恢复
		'click .point_action-7': (e, value, row, index) => {
			let url = './content/platform/recovery/instantaneous.php';
			e.preventDefault();
			// 如果授权已过期，提示用户 且不能跳转
			if (!CONF.AUTH_NOT_EXPIRED) {
				UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
				return;
			}
			LOCATION(getOtherRecopverUrl(url, row), 'recovery');
		},
		// 细粒度恢复
		'click .point_action-8': (e, value, row, index) => {
			let url = `./content/platform/recovery/graininess.php`;
			e.preventDefault();
			// 如果授权已过期，提示用户 且不能跳转
			if (!CONF.AUTH_NOT_EXPIRED) {
				UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
				return;
			}
			LOCATION(getOtherRecopverUrl(url, row), 'recovery');
		},
		// 跨平台恢复
		'click .point_action-9': (e, value, row, index) => {
			let url = `./content/platform/recovery/platform.php`;
			e.preventDefault();
			// 如果授权已过期，提示用户 且不能跳转
			if (!CONF.AUTH_NOT_EXPIRED) {
				UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
				return;
			}
			LOCATION(getOtherRecopverUrl(url, row), 'recovery');
		},
		// exchange数据同步
		'click .point_action-10': (e, value, row, index) => {
			// 操作权限判断，需要传归属用户的user_uuid
			checkOperateAuth({ type: 1, user_uuid: row.user_uuid, auth: 'data_manager' }, () => {
				$('#syncPointData').modal({ 'width': '660px', 'height': '100%' });
				syncPointData(row);
			});
		},
		// 接管
		'click .point_action-11': (e, value, row, index) => {
			e.preventDefault();
			// 如果授权已过期，提示用户 且不能跳转
			if (!CONF.AUTH_NOT_EXPIRED) {
				UIToastr.showWarning(LANG.UI_LICENSE_AUTH_INFO_TITLE, LANG.UI_LICENSE_AUTH_INFO_EXPIRED);
				return;
			}
			// 卷
			if (row.dev_type == 1) {
				LOCATION('./content/volcdp/vol_cdp_takeover.php', 'vol_cdp_takeover');
			}
			// 磁盘./content/complete_machine_volcdp/cm_volcdp_recovery.php
			if (row.dev_type == 2) {
				LOCATION("./content/complete_machine_volcdp/cm_volcdp_takeover.php?task_uuid=" + row.task_uuid + "&backup_set_id=" + row.backup_set_id, 'vol_cdp_complete_takeover');
			}
		}
	}

	// 云存储上的完备点索引数据同步
	const syncPointData = (row) => {
		let params = { "timepoint_uuid": row.timepoint_uuid, "op_id": op_id, "node_uuid": row.node_uuid };
		pAjaxRequest(params, "/api/v1/exchange/manage_sync", "GET", function (result) {
			if (!result.success) {
				$('#syncPointData').modal('hide');
				op_id = '';
				UIToastr.showWarning(LANG.UI_MICROSOFT365_INDEX_DATA_SYNC, result.message);
			} else if (result.data.op_status == 1) {//请求中
				if (op_id == '') {
					op_id = result.data.op_id
				} else {
					$('.syncSize').html(result.data.detail.total_size);
					$('#total-progress').css({ width: result.data.detail.progress - 14 + '%' });
					$('#progressright').html(result.data.detail.progress.toFixed(2) + '%');
				}
				setTimeout(syncPointData(row), 1000);
			} else if (result.data.op_status == 2) {//请求完成
				$('.syncSize').html(result.data.detail.total_size);
				$('#total-progress').css({ width: result.data.detail.progress - 14 + '%' });
				$('#progressright').html(result.data.detail.progress.toFixed(2) + '%');
				$('#syncPointData').modal('hide');
				op_id = '';
				UIToastr.showSuccess(LANG.UI_MICROSOFT365_INDEX_DATA_SYNC, result.message);
			}
		});
	}
	/**
	 * 返回恢复跳转url
	 * @param {string} url 恢复页面地址
	 * @param {object} row 参数
	 * @returns 
	 */
	const getOtherRecopverUrl = (url, row) => {
		const { module_type, sub_module_type, timepoint_uuid, item_uuid, task_uuid, sub_type } = row;
		switch (module_type) {
			case CONF.MODULE_TYPE.VM:
			case CONF.MODULE_TYPE.PRIVATE_CLOUD:
			case CONF.MODULE_TYPE.PUBLIC_CLOUD:
				url += `?recovery_type=vm&subtype=${sub_module_type}`;
				break;
			case CONF.MODULE_TYPE.OS:
				// 整机磁盘（定时）
				url += `?recovery_type=os`;
				break;
		}
		return url + `&point_uuid=${timepoint_uuid}&item_uuid=${item_uuid}&task_uuid=${task_uuid}&sub_type=${sub_type}`;
	}

	/**
	 * 标星统一处理
	 * @param {string} uuid 时间点uuid
	 * @param {string} type 请求类型
	 * @param {string} node_uuid 节点uuid
	 */
	let starHandler = function (param) {
		Metronic.blockUI({ target: '#setBackupDataMark', animate: true });
		let markBack = function (res) {
			Metronic.unblockUI('#setBackupDataMark');
			if (operateResponseList(res)) {
				$('#setBackupDataMark').modal('hide');
				$('#pointTable').bootstrapTable('refresh');
				modifyDelStyle('pointTable', 'deletePoint');
			};
		};
		//发送到数据管理统一处理
		pAjaxRequest(param, "/api/v1/backup_data/points/mark", param.type, markBack, true);
	}
	//设置标记确认
	let markSubmit = function (markInfo) {
		//先得到所有信息
		let forever = $("input[name=foreverCheck]").get(0).checked;
		//得到信息后先判断是否有做修改 如果没有做修改则不提交后台
		//时间点uuid
		let uuid = $("#mark_uuid").val();
		let node_uuid = $("#node_uuid").val();
		let moduleType = $("#module_type").val();
		let taskType = $("#task_type").val();
		//GFS和F标记点是分开发信息的 所以要发送2个信息
		//设置永久标记
		if (markInfo.importance_flag != forever) {
			let type = 'GET';
			if (!forever) {
				type = 'DELETE';
			}
			starHandler({ timepoint_uuid: uuid, type: type, node_uuid: node_uuid, module_type: moduleType, task_type: taskType });
		} else if (moduleType != CONF.MODULE_TYPE.VM) {
			$('#setBackupDataMark').modal('hide');
		}
		// 虚拟机模块设置GFS保留标记
		if (moduleType == CONF.MODULE_TYPE.VM && taskType != 17 && taskType != 19) {
			// 获取勾选状态
			let week = $("input[name=weekCheck]").get(0).checked;
			let month = $("input[name=monthCheck]").get(0).checked;
			let year = $("input[name=yearCheck]").get(0).checked;
			// 未修改直接返回
			if (markInfo.weekly_flag == week && markInfo.monthly_flag == month && markInfo.yearly_flag == year) {
				$('#setBackupDataMark').modal('hide');
				markInfo = {};
				return;
			}
			let item_list = [];
			//周保留标记
			if (markInfo.weekly_flag != week) {
				let info = {
					'level1_type': 1,
					'flag': boolToInt(week)
				};
				item_list.push(info);
			}
			//月保留标记
			if (markInfo.monthly_flag != month) {
				let info = {
					'level1_type': 2,
					'flag': boolToInt(month)
				};
				item_list.push(info);
			}
			//年保留标记
			if (markInfo.yearly_flag != year) {
				let info = {
					'level1_type': 3,
					'flag': boolToInt(year)
				};
				item_list.push(info);
			}
			let paramsMark = {
				timepoint_uuid: uuid,
				item_list: item_list,
				node_uuid: node_uuid,
				hypervisor_type: $("#sub_type").val(),
			};
			//设置GFS保留标记
			//发送到数据管理统一处理
			Metronic.blockUI({ target: '#setBackupDataMark', animate: true });
			pAjaxRequest(paramsMark, "/api/v1/backup_data/points/gfs_mark", "POST", function (d) {
				Metronic.unblockUI('#setBackupDataMark');
				if (operateResponseList(d)) {
					$('#setBackupDataMark').modal('hide');
					$('#pointTable').bootstrapTable('refresh');
					modifyDelStyle('pointTable', 'deletePoint');
				}
			}, true);
		}
	}
	/**
	 * 获取状态标签
	 * @param {string} des 描述
	 * @param {number} value 状态值 用于判断这个状态应该使用什么颜色的背景
	 */
	let getStatusLabel = (des, value) => {
		if (!des) {
			return `<span class="label label-sm label-default">${LANG.UI_PUBLIC_UNKNOWN}</span>`
		}
		switch (value) {
			// 未知、无操作
			case CONF.POINT_STATUS.UNKNOWN:
				return `<span class="label label-sm label-default">${des}</span>`;
			// 操作中
			case CONF.POINT_STATUS.OPERATING:
				let html = '';
				let statusArr = des.split(',');
				statusArr.forEach(item => {
					html += `<span class="label label-sm label-primary">${item.trim()}</span>`;
				});
				return html;
			// 异常
			case CONF.POINT_STATUS.ABNORMAL:
				return `<span class="label label-sm label-danger">${des}</span>`;
			// 正常
			case CONF.POINT_STATUS.NORMAL:
				return `<span class="label label-sm label-success">${des}</span>`;
			case CONF.POINT_STATUS.ERROR:
				return `<span class="label label label-danger">${des}</span>`;
		}
	}
	// 获取验证点记录
	const initDataVerifyHistoryTable = () => {
		let options = {
			vin_url: "/api/v1/backup_data/verify_history",
			vin_method: "GET",
			vin_params: function () {
				return { timepoint_uuid: data_verify_uuid };
			},
			showColumns: true,
			sortName: 'start_time',
			sortOrder: 'desc',
			pagination: false, //分页
			pageList: [5, 10, 25, 50], //每页数量
			columns: [
				{
					field: 'timepoint',
					title: LANG.UI_RECOVERY_TIMEPOINT,
					sortable: false,
					align: 'center',
				},
				{
					field: 'start_time',
					title: LANG.UI_BACKUP_DATA_POINT_DETAIL_LABEL_SCAN_START_TIME,
					sortable: false,
					align: 'center',
				},
				{
					field: 'end_time',
					title: LANG.UI_BACKUP_DATA_POINT_DETAIL_LABEL_SCAN_END_TIME,
					sortable: false,
					align: 'center',
				},
				{
					field: 'ping_status_des',
					title: LANG.UI_VERIFY_PING_TEST,
					sortable: false,
					align: 'center',
				},
				{
					field: 'heartbeat_status_des',
					title: LANG.UI_VERIFY_HEARTBEAT,
					sortable: false,
					align: 'center',
				},
				{
					field: 'screen_status_des',
					title: LANG.UI_VERIFY_SCREEN,
					sortable: false,
					align: 'center',
				},

			]
		}
		$('#dataVerifyTable').baseTableConfig().init(options);
	}
	// ----------------时间点表格事件--end
	/**
	 * 获取任务对应客户端的病毒扫描数据信息
	 */
	let getSafepointRecords = function () {
		let option = {
			vin_url: '/api/v1/complete_machine_volcdp/backup_set/safe_point_info',
			vin_method: 'GET',
			height: '250px',
			vin_params: function () {
				let params = {
					backup_set_id: pointParam.backup_set_id
				};
				return params;
			},
			sortName: 'op_time',
			sortOrder: 'desc',
			pagination: true, //分页
			onRefresh: function (a, b, c, d) {
				$('#cmCdpDataSafePointTable').bootstrapTable('hideLoading');
			},
			columns: [
				{
					field: 'timepoint_datetime', //时间点
					title: LANG.UI_RECOVERY_TIMEPOINT,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter: function (index, row) {
						let icon = '<img class = "margintop-5" src ="./img/platform/timepoint.png"> ';
						let timeStr = icon + row.timepoint_datetime;
						return timeStr;
					}
				},
				{
					field: 'virus_scan_status',
					title: LANG.UI_CM_CDP_VIRUS_SCAN_STATUS,
					formatter: function (index, row) {
						let virusScanStatus = row.virus_scan_status;
						let virusStr = LANG.UI_BACKUP_DATA_POINT_VIRUS_STATUS_NOT_SCAN; //扫描中
						switch (virusScanStatus) {
							case 0:  //未扫描
								virusStr = LANG.UI_BACKUP_DATA_POINT_VIRUS_STATUS_NOT_SCAN;  //未扫描
								break;
							case 1:  //扫描中
								virusStr = LANG.UI_BACKUP_DATA_POINT_OPERATION_STATE_SCANNING;  //扫描中
								break;
							case 2:  //健康
								virusStr = LANG.UI_BACKUP_DATA_POINT_VIRUS_STATUS_NORMAL;  //健康
								break;
							case 3:  //感染
							case 4:  //感染
								virusStr = LANG.UI_BACKUP_DATA_POINT_VIRUS_STATUS_ABNORMAL;  //感染
								break;
						}
						return virusStr;
					}
				},
				{
					field: 'last_virus_scan_time',
					title: LANG.UI_CM_CDP_LAST_VIRUS_SCAN_TIME,
				},
				{
					field: 'operation',
					title: LANG.UI_PUBLIC_OPERATION,
					events: cdpSafeEvents,
					formatter: function (index, row) {
						let moreOperation = `<a class="timepoint-safe-detail">` + LANG.UI_PLATFORM_INDUSTRY_MORE + `</a>`;
						return moreOperation;
					}
				}
			]
		};
		$('#cmCdpDataSafePointTable').baseTableConfig().init(option);
	}
	/**
	 * 获取当前病毒点更多扫描信息
	 */
	let cdpSafeEvents = {
		'click .timepoint-safe-detail': (e, value, row, index) => {
			// 避免多模态框蒙层显示异常情况
			// 1.清除背景蒙层
			let backdrops = $('.drawer-backdrop[data-backdrop="dataSafeDetailContentDrawer"]');
			backdrops.remove();
			// 2.获取抽屉的z-index
			const zIndex = $('#dataSafeDetailContentDrawer').css('z-index');
			$('#dataSafeDetailContentDrawer').drawer('toggle');
			// 3.给新的蒙层添加z-index
			$('.drawer-backdrop[data-backdrop="dataSafeDetailContentDrawer"]').attr('style', `z-index: ${zIndex - 1}`);
			$('#cmCdpPointVirusListTable').initVirusHistoryTable({ timepoint_uuid: row.timepoint_uuid });
		}
	}
	return {
		init: function () {
			Metronic.blockUI({ target: '#backup_data-content', animate: true });
			// 初始化页面
			initPage();
			// 监听事件
			initListeners();
			// 初始化任务表格
			initTaskTable();
			// 初始化当前用户密码用于删除二次确认
			initUserPassword();
		}
	}
}();

jQuery(document).ready(function () {
	backupData.init();
});