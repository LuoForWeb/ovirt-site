var globalStrategy = function () {
	let distributeData = {}; //分发策略组数据
	let initTaskTableFlag = false; //任务表格初始化标志
	let deleteData = {};
	let changeHeightFlag = false;
	let lastIndex = [-1, -1]; // 表格展开记录
	const MODULE_NAME = {
		2: LANG.UI_BACKUP_DATA_MODULE_VM, // 虚拟机备份
		3: LANG.UI_BACKUP_DATA_MODULE_FS, // 文件
		4: LANG.UI_BACKUP_DATA_MODULE_DB, // 数据库
		5: LANG.UI_BACKUP_DATA_MODULE_OS, // 整机
		6: LANG.UI_COPY_MODULE_LABEL_REEL_OS, // 卷
		11: LANG.UI_BACKUP_DATA_MODULE_NAS, // nas
		14: LANG.UI_BACKUP_DATA_MODULE_M365, // exchange
		17: LANG.UI_BACKUP_DATA_MODULE_PUBLIC_CLOUD, // 公有云
		22: LANG.UI_BACKUP_DATA_MODULE_PRIVATE_CLOUD, // 私有云
		28: LANG.UI_BACKUP_DATA_MODULE_K8S, // kubernetes
		998: LANG.UI_BACKUP_DATA_MODULE_HADOOP, // hadoop
		999: LANG.UI_BACKUP_DATA_MODULE_OBS, // 对象存储
	}

	// ---------初始化页面-------
	let afterInputHtml = () => {
		let html = '';
		if ($.inArray('p_global_strategy_add', CONF.PERMISSION_ARR) !== -1) {
			html += `<div class="btn-group">
						<button type="button" id="add" class="dropdown-toggle btn-font btn-title btn-whitespace">
							<i class="viconfont vicon-biaogetianjia mr5"></i>${LANG.UI_JOB_NEW_TASK}
						</button>
					</div>`;
		}
		if ($.inArray('p_global_strategy_edit', CONF.PERMISSION_ARR) !== -1) {
			html += `<div class="btn-group">
					<button type="button" id="distribute" class="dropdown-toggle btn-font btn-title btn-whitespace">
						<i class="viconfont vicon-a-Efferent-threechuanchu3 mr5"></i>${LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEND_TITLE}
					</button>
				</div>`
		}
		return html;
	}
	let beforeInputHtml = function () {
		if ($.inArray('p_scripts_manager_delete', CONF.PERMISSION_ARR) !== -1) {
			return `<div class="flex_center">
						<button class="btn b-btn viconfont vicon-a-Deleteshanchu1 brr2 mr12" id="deleteStrategyGroup"></button>
					</div>`;
		} else {
			return '';
		}
	}
	// 初始化策略表格
	let initDataTable = function () {
		let options = {
			vin_url: "/api/v1/strategies",
			vin_method: "GET",
			sortName: 'update_time',
			sortOrder: 'desc',
			detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
			detailFormatter: current_detail, //详情展开
			changeHeightBtn: true, //改变高度按钮
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			searchInput: true, //搜索框
			placeholder: LANG.UI_BACKUP_DATA_TABLE_SEARCH_BY_NAME,
			searchClass: 'searchPoint', //搜索框类名
			searchSelector: '.searchPoint', //表格选择使用该搜索框
			customTool: {
				beforeInput: beforeInputHtml(),
				afterInput: afterInputHtml(),
			},
			onCheck: function () {
				modifyDelStyle('strategy_table', 'deleteStrategyGroup');
			},
			onUncheck: function () {
				modifyDelStyle('strategy_table', 'deleteStrategyGroup');
			},
			onUncheckAll: function () {
				modifyDelStyle('strategy_table', 'deleteStrategyGroup');
			},
			onCheckAll: function () {
				modifyDelStyle('strategy_table', 'deleteStrategyGroup');
			},
			onPostBody: function () {
				modifyDelStyle('strategy_table', 'deleteStrategyGroup');
				// 操作权限判断
				if ($.inArray('p_global_strategy_delete', CONF.PERMISSION_ARR) == -1 && $.inArray('p_global_strategy_edit', CONF.PERMISSION_ARR) == -1) {
					$('#strategy_table').bootstrapTable('hideColumn', 'strategy_actions');
				}
			},

			columns: [
				{
					checkbox: true,
					sortable: false,
					forceHide: true, //隐藏掉导出该列数据
				},
				{
					field: 'strategy_name',
					title: LANG.UI_GLOBAL_STRATEGY_NICK_NAME,
					sortable: true,
					align: 'center',
				},
				{
					field: 'strategy_type',
					title: LANG.UI_GLOBAL_STRATEGY_TYPE,
					sortable: true,
					align: 'center',
					formatter: function (value) {
						return `<span title="${MODULE_NAME[value]}">${MODULE_NAME[value]}</span>`;
					}
				},
				{
					field: 'update_time',
					title: LANG.UI_GLOBAL_STRATEGY_UPDATE_TIME,
					sortable: true,
					align: 'center',
				},
				{
					field: 'update_user',
					title: LANG.UI_GLOBAL_STRATEGY_UPDATE_USER,
					sortable: true,
					align: 'center',
				},
				{
					field: 'strategy_marks',
					title: LANG.UI_PUBLIC_REMARK,
					sortable: false,
					align: 'center',
				},
				{
					field: 'strategy_actions',
					title: LANG.UI_PUBLIC_OPERATION,
					sortable: false,
					align: 'center',
					events: operateEvents,
					forceHide: true, //隐藏掉导出该列数据
					// 生成修改和删除按钮
					formatter: function () {
						// 操作权限判断
						if ($.inArray('p_global_strategy_delete', CONF.PERMISSION_ARR) !== -1 || $.inArray('p_global_strategy_edit', CONF.PERMISSION_ARR) !== -1) {
							let button = '<div class="btn-group">';
							button += '<div class="btn_operation_vicon">';
							if ($.inArray('p_global_strategy_edit', CONF.PERMISSION_ARR) !== -1) {
								button += '<a class="editStrategy"><i class="viconfont vicon-a-Editbianji"></i></a>'
							}
							if ($.inArray('p_global_strategy_delete', CONF.PERMISSION_ARR) !== -1) {
								button += '<a class="deleteStrategy"><i class="viconfont vicon-a-Deleteshanchu1"></i></a>'
							};
							button += '</div>';
							return button;
						} else {
							return '';
						}
					}
				},
			],
		}
		$('#strategy_table').baseTableConfig().init(options);
	};
	// 策略管理详情显示
	let current_detail = function (index, row, element) {
		if (index != lastIndex[1]) {//只展开一行
			lastIndex.push(index);
			$('#copyTable').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
			lastIndex.splice(0, 1);
		}
		// 策略信息
		let details = row.strategy_details;
		let content = '<table><tbody>';
		// 时间策略
		let timeDes = '---';
		if (details.time && details.time.des) {
			timeDes = details.time.des;
		}
		content += '<tr><td>' + LANG.UI_GLOBAL_STRATEGY_TIME + '：</td><td>' + timeDes + '</td></tr>';
		// 限速策略
		let speedDes = '---';
		let limitDes = '';
		// 开启限速策略但是没有限速
		if (details.speedlimit && details.speedlimit.des) {
			let des = '';
			limitDes = details.speedlimit.des;
			if (details.speedlimit.type == 1) {
				limitDes = LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SETTING + ':<br>'
				des += LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_NAME + ': ' + details.speedlimit.name + '<br>';
				switch (details.speedlimit.level) {
					case '1':
						des += LANG.UI_JOB_TASK_PRIORITY + ': ' + LANG.UI_JOB_TASK_PRIORITY_PRIMARY + '<br>';
						break;
					case '2':
						des += LANG.UI_JOB_TASK_PRIORITY + ': ' + LANG.UI_JOB_TASK_PRIORITY_HIGH + '<br>';
						break;
					case '3':
						des += LANG.UI_JOB_TASK_PRIORITY + ': ' + LANG.UI_JOB_TASK_PRIORITY_HIGHEST + '<br>';
				}
				// 使用正则表达式匹配每个限速策略
				let rateLimitPattern = /(.+?)\/s/g;
				let matches = details.speedlimit.des.match(rateLimitPattern);
				// 输出结果
				if (matches) {
					matches.forEach((match, index) => {
						limitDes += `${match}<br>`;
					});
				}
			}
			speedDes = des + limitDes;
		}
		content += '<tr><td>' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT + '：</td><td>' + speedDes + '</td></tr>';
		// 存储策略
		let storeDes = '---';
		if (details.store && details.store.des) {
			storeDes = details.store.des;
		}
		content += '<tr><td>' + LANG.UI_GLOBAL_STRATEGY_STORE + '：</td><td>' + storeDes + '</td></tr>';
		// 保留策略
		let reserveDes = '---';
		if (details.reserve && details.reserve.des) {
			reserveDes = details.reserve.des;
		}
		content += '<tr><td>' + LANG.UI_STRATEGY_RESERVE + '：</td><td>' + reserveDes + '</td></tr>';
		// 关联任务
		if (row.related_task) {
			content += '<tr><td>' + LANG.UI_PLATFORM_ASSOCIA_TASK + '：</td><td>' + row.related_task + '</td></tr>';
		}
		content += '</tbody></table>';
		$(element).append(content);
	}

	// 初始化分发表格
	let initDistributeTable = function () {
		let select = getIdSelectedRow('#strategy_table');
		// 只能一个策略进行分发 大于一个或没选提示
		if (!select.length || select.length > 1) {
			UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_DISTRIBUTE_STRATEGY, LANG.UI_GLOBAL_STRATEGY_DISTRIBUTE_STRATEGY_TIPS);
			return;
		}

		// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
		let user_uuid_arr = $.map($('#strategy_table').bootstrapTable('getSelections'), function (row) {
			return row.user_uuid;
		});
		checkOperateAuth({ type: 1, user_uuid: user_uuid_arr.join(','), auth: 'resmanagement' }, () => {
			// 策略信息
			distributeData.strategyInfo = select[0].strategy_details;
			// 策略uuid
			distributeData.strategyuuid = select[0].uuid;
			// 策略类型
			distributeData.strategyType = select[0].strategy_type;
			//分发选择任务模态框
			$('#distributeModalDiv').modal({ "width": "800px" });
			// 初始化任务表格
			initTaskTable();
		});
	}

	// 初始化分发策略任务选择表格
	let initTaskTable = function () {
		let ids = getIdSelectedId('#strategy_table');
		let row = getIdSelectedRow('#strategy_table');
		let options = {
			vin_url: "/api/v1/strategies/jobs",
			vin_method: "GET",
			vin_params: function () {
				let params = {};
				params.distribute_flag = true;
				params.strategy_uuid = ids[0];
				params.module_type = row[0].strategy_type;
				return params;
			},
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: false, //可变宽度

			columns: [
				{
					checkbox: true,
					sortable: false,
				},
				{
					field: 'task_name',
					title: LANG.UI_GLOBAL_STRATEGY_TASK_NAME,
					sortable: false,
					align: 'center',
				},
				{
					field: 'module_type_des',
					title: LANG.UI_GLOBAL_STRATEGY_MODULE_TYPE,
					sortable: false,
					align: 'center',
				},
				{
					field: 'create_time',
					title: LANG.UI_GLOBAL_STRATEGY_EDIT_DATE,
					sortable: false,
					align: 'center',
				},
				{
					field: 'task_status',
					title: LANG.UI_PUBLIC_STATUS,
					sortable: false,
					align: 'center',
					formatter: (value) => {
						return `<span class="label label-sm label-default label-default_en">${value}</span>`
					}
				},
			],
		}
		// 未初始化过则初始一个新表
		if (!initTaskTableFlag) {
			$('#task_table').baseTableConfig().init(options);
			initTaskTableFlag = true;
		} else {
			// 如果已经初始化销毁表格再初始化新表
			// 销毁表格
			$('#task_table').bootstrapTable('destroy');
			$('#task_table').baseTableConfig().init(options);
		}
	};

	//操作策略表格监听事件
	let operateEvents = {
		//修改事件
		'click .editStrategy': function (e, value, row, index) {
			// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
			checkOperateAuth({ type: 1, user_uuid: row.user_uuid, auth: 'resmanagement' }, () => {
				let params = row.uuid;
				if (params) {
					let url = './content/platform/strategy/add_strategy.php?uuid=' + params;
					//跳转页面
					LOCATION(url, 'backup_manager');
				}
			})
		},
		//删除事件
		'click .deleteStrategy': function (e, value, row, index) {
			// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
			checkOperateAuth({ type: 1, user_uuid: row.user_uuid, auth: 'resmanagement' }, () => {
				//获取uuid
				let params = row.uuid;
				deleteData = {};
				deleteData.uuid_list = [];
				deleteData.uuid_list.push(params);	//策略uuid集合
				//删除策略二次确认框
				bootbox.confirm({
					title: LANG.UI_GLOBAL_STRATEGY_DELETE,
					message: LANG.UI_GLOBAL_STRATEGY_DELETE_CONFIRM,
					callback: debounce(function (r) {
						if (!r) return;
						//列表添加锁动画
						Metronic.blockUI({ target: '#datatable', animate: true });
						pAjaxRequest(deepCloneObject(deleteData), `/api/v1/strategies/jobs/occupation`, "GET", deleteBack, true);
					}, 300)
				});
				let deleteBack = function (res) {
					Metronic.unblockUI('#datatable');
					//如果有备份点信息或任务依赖加载模块框
					if (res.success && res.data.length) {
						initModal(res.data);
						return;
					} else {
						// 没有依赖任务直接刷新表格
						$('#strategy_table').bootstrapTable('refresh');
					}
				}

			});
		},

	};

	// ----------页面交互-----------	
	// 初始化监听时间
	let initListeners = function () {
		// 新建策略
		$('#add').on('click', addStrategy);
		// 分发
		$('#distribute').on('click', initDistributeTable);
		// 批量删除
		$('#deleteStrategyGroup').on('click', deleteStrategyBatchSubmit);
		// 分发确认
		$('#dispense_submit').on('click', distributeStrategy);
		// 删除确认
		$('#delete_submit').on('click', deleteSubmit);
		// 改变表格高度
		$('.change_height').on('click', changeHeight);
	}

	// 改变表格高度
	let changeHeight = function () {
		if (changeHeightFlag == false) {
			changeHeightFlag = true;
			$('#strategy_table>tbody>tr>td').css({
				'padding-top': '15.25px',
				'padding-bottom': '15.25px'
			})
			$('#vin_strategy_toolbar .change_height i').addClass('icon-auto-height2');
		} else if (changeHeightFlag == true) {
			changeHeightFlag = false
			$('#strategy_table>tbody>tr>td').css({
				'padding-top': '4.25px',
				'padding-bottom': '4.25px'
			})
			$('#vin_strategy_toolbar .change_height i').removeClass('icon-auto-height2');
		}
	}

	//跳转添加添加全局策略页面
	let addStrategy = function () {
		LOCATION('./content/platform/strategy/add_strategy.php', 'backup_manager');
	}

	// 分发策略
	let distributeStrategy = function () {
		if (distributeData.strategyInfo.time && distributeData.strategyInfo.time.timeInfo && distributeData.strategyInfo.time.type == 'oncetime') {
			let systemTime = $('#systemTimeTop').text();
			let onceTimeSize = new Date(distributeData.strategyInfo.time.datetime).getTime();
			let systemTimeSize = new Date(systemTime).getTime();
			if (onceTimeSize <= systemTimeSize) {
				UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_DISTRIBUTE_STRATEGY, LANG.UI_GLOBAL_STRATEGY_DISTRIBUTE_STRATEGY_FAILED);
				return false;
			}
		}
		// 获取勾选分发的任务uuid
		let taskIds = getIdSelectedId('#task_table');
		// 任务可多选 必选
		if (taskIds.length == 0) {
			UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_DISTRIBUTE_STRATEGY, LANG.UI_GLOBAL_STRATEGY_DISTRIBUTE_TASK_TIPS);
			return;
		}
		distributeData.taskuuids = taskIds;
		let distributeBack = function (res) {
			Metronic.unblockUI('#distributeModalDiv');
			// 分发成功刷新列表
			if (res.success) {
				$('#strategy_table').bootstrapTable('refresh');
				$('#distributeModalDiv').modal('hide');
				UIToastr.showSuccess(LANG.UI_GLOBAL_STRATEGY_DISTRIBUTE_STRATEGY, LANG.UI_GLOBAL_STRATEGY_DISTRIBUTE_STRATEGY_SUCCESS);
			} else {
				UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_DISTRIBUTE_STRATEGY, res.message);
			}
		};
		Metronic.blockUI({ target: '#distributeModalDiv', animate: true, cenrerY: true, });
		pAjaxRequest(deepCloneObject(distributeData), '/api/v1/strategies/distribution', "PUT", distributeBack, true);
	}

	//批量删除策略确认
	let deleteStrategyBatchSubmit = function (grid) {
		let ids = getIdSelectedId('#strategy_table');
		//策略必选
		if (ids.length == 0) {
			UIToastr.showInfo(LANG.UI_GLOBAL_STRATEGY_DELETE, LANG.UI_GLOBAL_STRATEGY_DELETE_TIPS);
			return false;
		}
		// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
		let user_uuid_arr = $.map($('#strategy_table').bootstrapTable('getSelections'), function (row) {
			return row.user_uuid;
		});
		checkOperateAuth({ type: 1, user_uuid: user_uuid_arr.join(','), auth: 'resmanagement' }, () => {
			//删除策略二次确认框
			bootbox.confirm({
				title: LANG.UI_GLOBAL_STRATEGY_DELETE,
				message: LANG.UI_GLOBAL_STRATEGY_DELETE_CONFIRM,
				callback: debounce(function (r) {
					if (!r) return;
					let data = {};
					data.uuid_list = ids;	//策略uuid集合
					deleteData = data;
					//列表添加锁动画
					Metronic.blockUI({ target: '#datatable', animate: true });
					let deleteBack = function (res) {
						Metronic.unblockUI('#datatable');
						//如果有备份点信息或任务依赖加载模块框
						if (res.success && res.data.length) {
							initModal(res.data);
							return;
						} else {
							$('#strategy_table').bootstrapTable('refresh');
						}
					}
					pAjaxRequest(data, `/api/v1/strategies/jobs/occupation`, "GET", deleteBack, true);
				}, 300)
			});
		});
	}

	//初始策略组任务关联树
	let setTree = function (zNodes) {
		if (zNodes == "[]") {
			return;
		}
		let setting = {
			check: {
				enable: true,
				nocheckInherit: false
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
			}
		};
		strategyTree = $.fn.zTree.init($("#strategy_tree"), setting, zNodes);
	};

	// 任务关联模态框
	let initModal = function (data) {
		setTree(data);
		$('#deletemodaldiv').modal();
	}

	// 删除确认
	let deleteSubmit = function () {
		let deleteBack = function (res) {
			// 删除成功刷新表格
			if (res.success) {
				$('#deletemodaldiv').modal('hide');
				$('#strategy_table').bootstrapTable('refresh');
			} else {
				UIToastr.showInfo(res.message);
			}
		};
		pAjaxRequest(deepCloneObject(deleteData), `/api/v1/strategies`, "DELETE", deleteBack, true);
	}

	//获取选中项uuid
	function getIdSelectedId(select) {//select = table id
		return $.map($(select).bootstrapTable('getSelections'), function (row) {
			return row.uuid;
		})
	}

	//获取选中项
	function getIdSelectedRow(select) {//select = table id
		return $.map($(select).bootstrapTable('getSelections'), function (row) {
			return row;
		})
	}

	return {
		init: function () {
			initDataTable();
			initListeners();
		}
	}
}();
$(document).ready(function () {
	globalStrategy.init();
});