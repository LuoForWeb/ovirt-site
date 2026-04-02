var OrchestrationManage = function () {
	let changeHeightFlag = false; // 改变高度标志
	let initGridFlag = false; // 初始化表格标志
	let queryParams = {}; // 参数
	let expandIndex = null; // 用于记录展开的详情行，刷新表格展开详情不刷新关闭
	let paginationOpenFlag = false; // 记录分页展开状态
	let checkIndex; // 记录勾选行
	let btnOpen; // 记录按钮展开状态
	let interval = null;

	// 初始化列表
	let initDataTable = () => {
		// 判断分页展开状态
		if ($('#jobOrchestrationDiv .page-list .dropdown').hasClass('open')) {
			paginationOpenFlag = true;
		} else {
			paginationOpenFlag = false;
		}
		let options = {
			vin_url: "/api/v1/orchestration",
			vin_method: "GET",
			vin_params: function () {
				return queryParams;
			},
			toolbarId: '#vinOrchestrationToolbar',
			buttonsToolbar: '#vinOrchestrationToolbar .vin_orchestrationToolbar',
			detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
			detailFormatter: orchestrationDetail, //详情展开
			changeHeightBtn: true, //改变高度按钮
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			sortName: 'create_time', // 默认排序
			sortOrder: 'desc',
			onRefresh: () => {
				// 取消刷新动画
				$("#jobOrchestrationTable").bootstrapTable('hideLoading');
			},
			// 勾选框联动效果
			onCheck: () => {
				const selectedRows = $('#jobOrchestrationTable').bootstrapTable("getSelections");
				checkIndex = selectedRows;
				if (selectedRows.length > 0) {
					const { allStopped, canStartStrategy } = checkSelectedRowsForStatus(selectedRows);
					updateBatchStatus(allStopped, canStartStrategy);
				} else {
					clearBatchStatus();
				}
			},
			onUncheck: () => {
				const selectedRows = $('#jobOrchestrationTable').bootstrapTable("getSelections");
				checkIndex = selectedRows;
				if (selectedRows.length > 0) {
					const { allStopped, canStartStrategy } = checkSelectedRowsForStatus(selectedRows);
					updateBatchStatus(allStopped, canStartStrategy);
				} else {
					clearBatchStatus();
				}
			},
			onUncheckAll: () => {
				var selectedRows = $('#jobOrchestrationTable').bootstrapTable("getSelections");
				checkIndex = selectedRows;
				clearBatchStatus();
			},
			onCheckAll: () => {
				const selectedRows = $('#jobOrchestrationTable').bootstrapTable("getSelections");
				checkIndex = selectedRows;
				if (selectedRows.length > 0) {
					const { allStopped, canStartStrategy } = checkSelectedRowsForStatus(selectedRows);
					updateBatchStatus(allStopped, canStartStrategy);
				} else {
					clearBatchStatus();
				}
			},
			PostBody: (data) => {
				setBtnStatus(); //根据状态设置操作按钮
				checkRecord();
				if (null !== expandIndex) {
					$("#jobOrchestrationTable").bootstrapTable('expandRow', expandIndex);
				}
				// 展开分页选择
				if (paginationOpenFlag) {
					$('#jobOrchestrationDiv .page-list .dropdown').addClass('open');
				}
				$('#' + btnOpen + '').parent('.btn-group').addClass('open');
				// 记录行高改变
				if (changeHeightFlag == false) {
					$('#jobOrchestrationTable>tbody>tr>td').css({
						'padding-top': '4.25px',
						'padding-bottom': '4.25px'
					})
					$('#vinOrchestrationToolbar .change_height i').removeClass('icon-auto-height2');
				} else if (changeHeightFlag == true) {
					$('#jobOrchestrationTable>tbody>tr>td').css({
						'padding-top': '10.25px',
						'padding-bottom': '10.25px'
					})
					$('#vinOrchestrationToolbar .change_height i').addClass('icon-auto-height2');
				}
				initOrchestrationTimer();
				// 操作权限判断
				if ($.inArray('p_task_orchestration_manager', CONF.PERMISSION_ARR) == -1) {
					$('#jobOrchestrationTable').bootstrapTable('hideColumn', 'plan_actions');
				}
			},
			onExpandRow: (index) => {
				// 展开详情并记录展开的index
				if (null === expandIndex) {
					expandIndex = index;
					// 切换展开行，更新index
				} else if (index !== expandIndex) {
					$("#jobOrchestrationTable").bootstrapTable('collapseRow', expandIndex);
					expandIndex = index;
				}
			},
			onCollapseRow: () => {
				expandIndex = null;
			},
			columns: [{
				checkbox: true,
				sortable: false,
				forceHide: true, //隐藏掉导出该列数据
			}, {
				field: 'plan_nickname',
				title: LANG.UI_JOB_ORCHESTRATION_PLAN_NAME,
				sortable: true,
				align: 'center',
				formatter: (value, row, index) => {
					return `<a href="./content/platform/orchestration/orchestration_details.php?uuid=${row.plan_uuid}" class="ajaxify" name="task">${row.plan_nickname}</a>`;
				}
			}, {
				field: 'tasks_number',
				title: LANG.UI_JOB_ORCHESTRATION_TASK_NUM,
				sortable: true,
				align: 'center',
			}, {
				field: 'create_time',
				title: LANG.UI_GLOBAL_STRATEGY_EDIT_DATE,
				sortable: true,
				align: 'center',
			}, {
				field: 'current_running_section',
				title: LANG.UI_JOB_ORCHESTRATION_EXECUTING_SECTION,
				sortable: false,
				align: 'center',
			}, {
				field: 'execute_count',
				title: LANG.UI_JOB_ORCHESTRATION_EXECUTE_NUM,
				sortable: false,
				align: 'center',
			}, {
				field: 'user_name',
				title: LANG.UI_GLOBAL_STRATEGY_UPDATE_USER,
				sortable: false,
				align: 'center',
			}, {
				field: 'plan_state',
				title: LANG.UI_PUBLIC_STATUS,
				sortable: true,
				align: 'center',
				formatter: (value, row, index) => {
					let state = row.plan_state;
					let stateName = '';
					switch (state) {
						case CONF.ORCHESTRATION_PLAN_STATUS.WAITING: // 等待
							stateName = '<span class="label label-sm label-info label-info_en">' + LANG.UI_VISUAL_WAIT + '</span>';
							break;
						case CONF.ORCHESTRATION_PLAN_STATUS.RUNNING: // 运行中
							stateName = '<span class="label label-sm label-success label-success_en">' + LANG.UI_VISUAL_RUNNING + '</span>';
							break;
						case CONF.ORCHESTRATION_PLAN_STATUS.PAUSED: // 暂停
							stateName = '<span class="label label-sm label-default label-default_en">' + LANG.UI_VISUAL_PAUSE + '</span>';
							break;
						case CONF.ORCHESTRATION_PLAN_STATUS.STOPPED: // 停止
							stateName = '<span class="label label-sm label-default label-default_en">' + LANG.UI_VISUAL_STOP + '</span>';
							break;
						case CONF.ORCHESTRATION_PLAN_STATUS.ERROR: // 错误
							stateName = '<span class="label label-sm label-danger label-danger_en">' + LANG.UI_PUBLIC_ERROR + '</span>';
							break;
					};
					return stateName
				}
			}, {
				field: 'plan_actions',
				title: LANG.UI_PUBLIC_OPERATION,
				sortable: false,
				align: 'center',
				events: operateEvents,
				forceHide: true, //隐藏掉导出该列数据
				opButton: true,
				// 生成修改和删除按钮
				formatter: function (value, row, index) {
					// 操作权限判断
					if ($.inArray('p_task_orchestration_manager', CONF.PERMISSION_ARR) !== -1) {
						var uuid = row.plan_uuid;
						var button = '<div class="btn-group dropdown-wrapper">';
						if (index > 5) {
							button = '<div class="btn-group dropdown-wrapper dropup">';
						}

						button += '<button type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" ' +
							'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
							'' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
							'</button>' +
							'<ul class="dropdown-menu" role="menu" id=' + uuid + '>'
							+ '<li class="startStrategy"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_time_point me-4"></i> ' + LANG.UI_JOB_START_STRATEGY + '</button></li>'
							+ '<li class="start"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_play me-4"></i> ' + LANG.UI_SETTINGS_SERVICE_START + '</button></li>'
							+ '<li class="edit"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-a-Editbianji me-4"></i> ' + LANG.UI_JOB_MODIFY + '</button></li>'
							+ '<li class="stop"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_suspend-copy me-4"></i> ' + LANG.UI_JOB_STOP + '</button></li>'
							+ '<li class="delete"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-a-Deleteshanchu1 me-4"></i> ' + LANG.UI_PUBLIC_DELETE + '</button></li>'
							+ '</ul></div>';
						return button;
					} else {
						return '';
					}
				}
			}]
		}
		// 第一次进页面创建表格，后面直接刷新
		$('#jobOrchestrationTable').baseTableConfig().init(options);
		initTableListener();
	}

	let orchestrationDetail = function (index, row, element) {
		// 策略信息
		var content = '<table><tbody>';
		// 时间策略
		let time = row.time_strategy;
		if (time.length > 0) {
			let timeDes = getTimeStrategy(time);
			content += '<tr><td>' + LANG.UI_GLOBAL_STRATEGY_TIME + '：</td><td>' + timeDes + '</td></tr>';
		}
		// 上次执行时间
		let lastTime = row.last_time;
		if (lastTime) {
			content += '<tr><td>' + LANG.UI_JOB_ORCHESTRATION_LAST_TIME + '：</td><td>' + lastTime + '</td></tr>';
		}
		// 下次执行时间
		let nextTime = row.next_time;
		if (nextTime) {
			content += '<tr><td>' + LANG.UI_JOB_ORCHESTRATION_NEXT_TIME + '：</td><td>' + nextTime + '</td></tr>';
		} else {
			content += '<tr><td>' + LANG.UI_JOB_ORCHESTRATION_NEXT_TIME + '：</td><td> --- </td></tr>';
		}
		content += '</tbody></table>';
		$(element).append(content);
	}

	//得到时间策略描述信息
	var getTimeStrategy = function (msg) {
		var timeInfo = LANG.UI_PUBLIC_NOTHING;
		if (!msg) {
			return timeInfo;
		}
		for (var i = 0; i < msg.length; i++) {
			var strategy = msg[i];
			var des = "";
			if (CONF.STRATEGY_TYPE.DAY == strategy.type) { // 每天
				des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy);
			} else if (CONF.STRATEGY_TYPE.WEEK == strategy.type) { // 每周
				des += getStrategyFrequency(strategy);
				if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
					des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
				} else {
					des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
				}
			} else if (CONF.STRATEGY_TYPE.MONTH == strategy.type) { // 每月
				des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
			} else if (CONF.STRATEGY_TYPE.GLOBAL == strategy.type) { // 永久限速
				des += strategy.startTime;
			}
			timeInfo = des
		}
		return timeInfo;
	}
	//得到备份间隔描述
	var getStrategyFrequency = function (strategy) {
		var frequency = "";
		var frequencyLang = LANG.UI_STRATEGY_WEEK_FREQUENCY_TIPS;
		for (var i = 1; i <= 20; i++) {
			if (strategy.frequency == "s" + i) {
				if (i == 1) {
					frequency = LANG.UI_STRATEGY_OTHER_WEEK + ",";
				}
				else {
					frequency = frequencyLang.replace('x', i) + ",";
				}
			}
		}
		return frequency;
	}

	//获取每周显示日期
	var getStrategyWeek = function (days) {
		var desDays = '';
		$.each(days, function (i, d) {
			if (1 == d) {
				desDays += CONF.WEEK[i] + ", ";
			}
		});
		return desDays;
	}

	var getStrategyDays = function (days) {
		var desDays = '';
		$.each(days, function (i, d) {
			if (1 == d) {
				var day = i + 1;
				desDays += day + ", ";
			}
		});
		return desDays;
	}

	var getEachStrategy = function (strategy) {
		var desEach = '';
		desEach += strategy.startTime;
		//如果是英文版 需要加空格
		if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
			desEach += " "; //策略开始时间
		}
		desEach += LANG.UI_STRATEGY_START + ", ";
		if (strategy.rollFlag) {
			if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
				desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
			} else {
				desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + strategy.endTime + LANG.UI_STRATEGY_END;
			}

		} else {
			desEach += LANG.UI_STRATEGY_ROLL_NO;
		}
		desEach += "<br>";
		return desEach;
	}

	let changeHeight = function () {
		if (changeHeightFlag == false) {
			changeHeightFlag = true;
			$('#jobOrchestrationTable>tbody>tr>td').css({
				'padding-top': '10.25px',
				'padding-bottom': '10.25px'
			})
			$('#vinOrchestrationToolbar .change_height i').addClass('icon-auto-height2');
		} else if (changeHeightFlag == true) {
			changeHeightFlag = false
			$('#jobOrchestrationTable>tbody>tr>td').css({
				'padding-top': '4.25px',
				'padding-bottom': '4.25px'
			})
			$('#vinOrchestrationToolbar .change_height i').removeClass('icon-auto-height2');
		}
	}
	let initTableListener = function () {
		// 改变表格高度
		$('#vinOrchestrationToolbar .change_height').on('click', changeHeight);
		// 新建编排计划
		$('#addOrchestration').on('click', function () {
			LOCATION('./content/platform/orchestration/orchestration.php', 'task');
		});
		// 删除
		$('#batchDelete').on('click', deletePlanBatch);
		$('#batchStartStrategy').on('click', startPlanBatch);
		$('#batchStop').on('click', stopPlanBatch);
		// 搜索功能
		$('#orchestration_job_seach_ipt').on('keyup', function (event) {
			// 回车搜索
			if (event.keyCode === 13) {
				queryParams = { search: this.value };
				$("#jobOrchestrationTable").bootstrapTable('refresh', { query: queryParams });
			}
		});
		$('#orchestration_job_search_btn').on('click', function (event) {
			let value = $('#orchestration_job_seach_ipt').val();
			queryParams = { search: value };
			$("#jobOrchestrationTable").bootstrapTable('refresh', { query: queryParams });
		});

		// 聚焦显示清空按钮
		$('#orchestration_job_seach_ipt').on('focus', () => {
			if ($('#orchestration_job_seach_ipt').val()) {
				$('#orchestration_job_clear_search').removeClass('hide');
			}
		});

		$('#orchestration_job_seach_ipt').on('input', () => {
			if ($('#orchestration_job_seach_ipt').val()) {
				$('#orchestration_job_clear_search').removeClass('hide');
			} else {
				$('#orchestration_job_clear_search').addClass('hide');
			}
		});

		// 失去焦点隐藏清空按钮
		$('#orchestration_job_seach_ipt').on('blur', () => {
			if ($('#orchestration_job_seach_ipt').val() == '') {
				$('#orchestration_job_clear_search').removeClass('show');
				$('#orchestration_job_clear_search').addClass('hide');
			};
		});
		// 清空搜索框
		$('#orchestration_job_clear_search').on('click', () => {
			$('#orchestration_job_clear_search').removeClass('show');
			$('#orchestration_job_clear_search').addClass('hide');
			$('#orchestration_job_seach_ipt').val('');
			queryParams = {};
			$("#jobOrchestrationTable").bootstrapTable('refresh');
		});
		$(document).on('click', function (e) {
			btnOpen = ''; //点击页面将展开按钮置为空
		});

		// 提示信息关闭
		$('#close_orchestration_tips').on('click', () => {
			// 关闭任务编排页面提示tips时，动态调整表格高度
			$('.jobs-wrapper .table-container.orchestration-job-table-container').css('height', 'calc(100% - 76px)')
		});

	}

	let listeners = function () {
		window.$off('updateOrchestrationJobPage');

		window.$on('updateOrchestrationJobPage', () => {
			if ($('#jobOrchestrationTable').children().length === 0) {
				initDataTable();
			}

			// 重置定时器
			initOrchestrationTimer();
		});
	};

	// 批量删除
	let deletePlanBatch = () => {
		// 操作权限判断，需要传归属用户的user_uuid，多个user_uuid用逗号隔开
		let user_uuid_arr = $.map($(`#jobOrchestrationTable`).bootstrapTable('getSelections'), function (row) {
			return row.user_uuid;
		});
		checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: user_uuid_arr.join(','), auth: 'current_job' }, () => {
			// 得到选中行的id
			let ids = getIdSelectedId('#jobOrchestrationTable');
			bootbox.confirm({
				title: LANG.UI_JOB_TASK_ORCHESTRATION_DELETE_BATCH,
				message: LANG.UI_JOB_TASK_ORCHESTRATION_DELETE_BATCH_TIPS,
				callback: debounce(function (r) {
					if (!r) return;
					Metronic.blockUI({ target: '#jobOrchestrationTable', animate: true });
					pAjaxRequest(deepCloneObject({ plan_uuid_list: ids }), `/api/v1/orchestration`, "DELETE", operateBack, true);
				}, 300)
			});
		});
	};
	// 批量启用
	let startPlanBatch = () => {
		let user_uuid_arr = $.map($(`#jobOrchestrationTable`).bootstrapTable('getSelections'), function (row) {
			return row.user_uuid;
		});
		checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: user_uuid_arr.join(','), auth: 'current_job' }, () => {
			// 得到选中行的id
			let ids = getIdSelectedId('#jobOrchestrationTable');
			Metronic.blockUI({ target: '#jobOrchestrationTable', animate: true });
			pAjaxRequest(deepCloneObject({ plan_uuid: ids, op_mode: 1, batchFlag: true }), `/api/v1/orchestration/operate`, "GET", operateBack, true);
		});
	};
	// 批量停止
	let stopPlanBatch = () => {
		let user_uuid_arr = $.map($(`#jobOrchestrationTable`).bootstrapTable('getSelections'), function (row) {
			return row.user_uuid;
		});
		checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: user_uuid_arr.join(','), auth: 'current_job' }, () => {
			// 得到选中行的id
			let ids = getIdSelectedId('#jobOrchestrationTable');
			Metronic.blockUI({ target: '#jobOrchestrationTable', animate: true });
			pAjaxRequest(deepCloneObject({ plan_uuid: ids, op_mode: 3, batchFlag: true }), `/api/v1/orchestration/operate`, "GET", operateBack, true);
		});
	};

	//获取选中项uuid
	let getIdSelectedId = (select) => {//select = table id
		return $.map($(select).bootstrapTable('getSelections'), function (row) {
			return row.plan_uuid;
		})
	}
	// 操作
	let operateEvents = {
		// 启动策略
		'click .startStrategy': (e, value, row, index) => {
			let user_uuid_arr = $.map($(`#jobOrchestrationTable`).bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: user_uuid_arr.join(','), auth: 'current_job' }, () => {
				Metronic.blockUI({ target: '#jobOrchestrationTable', animate: true });
				pAjaxRequest(deepCloneObject({ plan_uuid: [row.plan_uuid], op_mode: 1 }), `/api/v1/orchestration/operate`, "GET", operateBack, true);
			})
		},
		// 启动
		'click .start': (e, value, row, index) => {
			let user_uuid_arr = $.map($(`#jobOrchestrationTable`).bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: user_uuid_arr.join(','), auth: 'current_job' }, () => {
				//列表添加锁动画
				Metronic.blockUI({ target: '#jobOrchestrationTable', animate: true });
				pAjaxRequest(deepCloneObject({ plan_uuid: row.plan_uuid, op_mode: 2 }), `/api/v1/orchestration/operate`, "GET", operateBack, true);
			});
		},
		// 编辑
		'click .edit': (e, value, row, index) => {
			let user_uuid_arr = $.map($(`#jobOrchestrationTable`).bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: user_uuid_arr.join(','), auth: 'current_job' }, () => {
				if (row.plan_uuid) {
					//跳转页面
					LOCATION(`./content/platform/orchestration/orchestration.php?uuid=${row.plan_uuid}`, 'orchestration');
				}
			});
		},
		// 停止
		'click .stop': (e, value, row, index) => {
			let user_uuid_arr = $.map($(`#jobOrchestrationTable`).bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: user_uuid_arr.join(','), auth: 'current_job' }, () => {
				Metronic.blockUI({ target: '#jobOrchestrationTable', animate: true });
				let uuid = []
				uuid.push(row.plan_uuid);
				//列表添加锁动画
				Metronic.blockUI({ target: '#datatable', animate: true });
				pAjaxRequest(deepCloneObject({ plan_uuid: uuid, op_mode: 3 }), `/api/v1/orchestration/operate`, "GET", operateBack, true);
			});
		},
		// 删除
		'click .delete': (e, value, row, index) => {
			let user_uuid_arr = $.map($(`#jobOrchestrationTable`).bootstrapTable('getSelections'), function (row) {
				return row.user_uuid;
			});
			checkOperateAuth({ type: CONF.GLOBAL_OBSERVER_AUTH_TYPE.NON_ASSIGN_PERMISSION, user_uuid: user_uuid_arr.join(','), auth: 'current_job' }, () => {
				let uuid = []
				uuid.push(row.plan_uuid);
				//删除策略二次确认框
				bootbox.confirm({
					title: LANG.UI_JOB_ORCHESTRATION_DELETE,
					message: LANG.UI_JOB_ORCHESTRATION_CONFIRM,
					callback: debounce((r) => {
						if (!r) return;
						//列表添加锁动画
						Metronic.blockUI({ target: '#jobOrchestrationTable', animate: true });
						pAjaxRequest(deepCloneObject({ plan_uuid_list: uuid }), `/api/v1/orchestration`, "DELETE", operateBack, true);
					}, 300)
				});
			});
		},
		'click .btn': function (event, value, row, index) {
			btnRecord(event.target);
		},
	}

	// 操作统一响应事件，刷新表格
	let operateBack = (res) => {
		Metronic.unblockUI('#jobOrchestrationTable');
		if (operateResponseList(res)) {
			$("#jobOrchestrationTable").bootstrapTable('refresh');
			$("#jobOrchestrationTable").bootstrapTable('hideLoading'); // 隐藏刷新动画
		}
	};

	//根据任务状态设置按钮权限
	let setBtnStatus = () => {
		let data = $("#jobOrchestrationTable").bootstrapTable("getData");
		for (let i = 0; i < data.length; i++) {
			let planState = data[i].plan_state;
			let planId = data[i].plan_uuid;
			switch (planState) {
				case CONF.ORCHESTRATION_PLAN_STATUS.WAITING: // 等待状态 禁用启动策略，修改，删除
					addForbidButton(planId, 'startStrategy', false);
					addForbidButton(planId, 'edit', false);
					addForbidButton(planId, 'delete', false);
					break;
				case CONF.ORCHESTRATION_PLAN_STATUS.RUNNING: // 运行状态 禁用启动策略，启动，修改，删除
					addForbidButton(planId, 'startStrategy', false);
					addForbidButton(planId, 'start', false);
					addForbidButton(planId, 'edit', false);
					addForbidButton(planId, 'delete', false);
					break;
				case CONF.ORCHESTRATION_PLAN_STATUS.STOPPED: // 停止状态 禁用停止
					addForbidButton(planId, 'stop', false);
					break;
				case CONF.ORCHESTRATION_PLAN_STATUS.ERROR: // 错误状态 禁用启动策略、修改、删除、停止
					addForbidButton(planId, 'startStrategy', false);
					addForbidButton(planId, 'edit', false);
					addForbidButton(planId, 'delete', false);
					break;
			}
		}
	}

	//添加禁止点击的按钮样式
	let addForbidButton = (uuid, option, available) => {
		if (!available) {
			$('#jobOrchestrationTable #' + uuid + ' .' + option + ' .btn').prop('disabled', true);
		}
	}

	//记录勾选
	let checkRecord = function () {
		let checkArr = [];
		$.each(checkIndex, function (index) {
			checkArr.push(checkIndex[index].plan_uuid);
		});
		$('#jobOrchestrationTable').bootstrapTable('checkBy', {
			field: 'plan_uuid',
			values: checkArr
		})
	}

	//记录刷新按钮展开
	var btnRecord = function (target) {
		btnOpen = $(target).next('.dropdown-menu').prop('id');
	}
	let updateBatchStatus = (stopped, canStop) => {
		if (stopped) {
			$('#batchDelete i').addClass('icon-delete');
			$('#batchDelete').addClass('batch-active');
			$('#batchStartStrategy i').addClass('icon-start');
			$('#batchStartStrategy').addClass('batch-active');
		} else {
			$('#batchDelete i').removeClass('icon-delete');
			$('#batchDelete').removeClass('batch-active');
			$('#batchStartStrategy i').removeClass('icon-start');
			$('#batchStartStrategy').removeClass('batch-active');
		}

		if (canStop) {
			$('#batchStop i').addClass('icon-stop');
			$('#batchStop').addClass('batch-active');
		} else {
			$('#batchStop i').removeClass('icon-stop');
			$('#batchStop').removeClass('batch-active');
		}
	}

	let clearBatchStatus = () => {
		$('#jobOrchestrationTable thead .bs-checkbox input[type=checkbox]').removeClass("bootstrap-table-half-checked bootstrap-table-checked");
		$('#batchDelete i').removeClass('icon-delete');
		$('#batchDelete').removeClass('batch-active');
		$('#batchStartStrategy i').removeClass('icon-start');
		$('#batchStartStrategy').removeClass('batch-active');
		$('#batchStop i').removeClass('icon-stop');
		$('#batchStop').removeClass('batch-active');
	}

	let checkSelectedRowsForStatus = (selectedRows) => {
		let allStopped = true;
		let canStartStrategy = true;
		selectedRows.forEach(row => {
			if (row.plan_state !== CONF.ORCHESTRATION_PLAN_STATUS.STOPPED) {
				allStopped = false;
			}
			if (row.plan_state === CONF.ORCHESTRATION_PLAN_STATUS.STOPPED) {
				canStartStrategy = false;
			}
		});
		return { allStopped, canStartStrategy };
	}
	//更新表格数据
	let update = function () {
		$('#jobOrchestrationTable').bootstrapTable('refresh', {
			query: queryParams
		});
	}

	let initOrchestrationTimer = function () {
		if (timerTask.ORCHESTRATION_JOB_TIMER) {
			clearTimeout(timerTask.ORCHESTRATION_JOB_TIMER);
		}

		timerTask.ORCHESTRATION_JOB_TIMER = setTimeout(update, 5000);
	}

	return {
		init: () => {
			listeners();
		}
	}
}();
jQuery(document).ready(() => {
	OrchestrationManage.init();
});