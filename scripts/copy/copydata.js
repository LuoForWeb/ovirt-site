var CopyData = function () {
	let nodeParamList, time_point_list, deleteList; //节点搜索列表，时间点存放列表, 虚拟机列表, 数据库列表
	let zTree;//当前树, 副本树
	let _UserPassword;//账户密码
	let initErrorFlag = false;
	let timePointFlag = false;//是否搜到过时间点
	let initTaskTableFlag = false; //任务表格初始化标志
	let module = 2; //模块类型
	let deleteStorageUUid = '';
	let showNode;//搜索到的时间点
	let op_id = ''; //exchange同步操作
	const COPY_DATA_TYPE = 4;
	const TREE_NODE_TYPE = {
		TASK: 'task',
		HOST: 'host',
		INSTANCE: 'instance',
		POINT: 'point',
		FULL: 3,
		UN_FULL: 4
	}

	// 初始化副本数据
	let initCopyData = function () {
		//清楚搜索框内容
		$("#searchCopy").val("");
		$('#copyTable').hide();
		$('#markTips').hide();
		$('#noSearchTips').hide();
		$('#tableTips').show();
		$('#copyDataUrl').empty().hide();
		initPointShowType();
		$('#copyDataUrl').show();
	}

	//获取副本数据节点树
	let initTree = function () {
		let storage_uuid = $('#storageSelect').val();
		module = parseInt($('#module').val());//模块类型
		Metronic.blockUI({ target: '#copyNodeTree', animate: true });
		pAjaxRequest(deepCloneObject({ storage_uuid: storage_uuid, data_type: COPY_DATA_TYPE, module: module, copy_data_flag: true, copy_flag: true }), "/api/v1/copy/resources", "GET", setTree, true);
	}

	// 初始化数据树
	let setTree = function (res) {
		iniTree(res);
	};

	//初始化监听事件
	let initListener = function () {
		// 批量删除
		$('#allDelete').on('click', deleteSelectPoint);
		//切换存储类型
		$('#storageSelect').on('change', nodeSelectChange);
		// 选择模块类型
		$('#module').on('change', copyTypeHandler);
		//设置标记确认
		$("#mark_submit").on('click', markSubmit);
		//搜索模态框
		$('#filterSearch').click(function () {
			$('#filterSearch').popModal({
				html: $('#filter-content'),
				placement: 'bottomLeft',
				showCloseBut: true,
				onDocumentClickClose: true,
				onOkBut: searchPoint,
				onCancelBut: function () { },
				onLoad: function () { },
				onClose: function () { },
				maxWidth: 300,
				maxHeight: 'auto',
			});
		});
		$('#markTips .close').on('click', () => {
			$('.tabledata-wrapper__content__table .table-container').css('height', 'calc(100% - 60px)');
		})
	}

	//点击节点，展开并展示时间点信息表
	let nodeSelect = function (treeId, treeNode) {
		if (treeNode.event_type == TREE_NODE_TYPE.HOST) {
			$('#copyDataUrl').text('');
			let parent = treeNode.getParentNode();
			let info = parent.name + "---" + treeNode.name;
			$('#copyDataUrl').append(info);
			$('#copyTable').show();
			$('#tableTips').hide();
			copyDataTable(treeNode); //录入备份数据列表
		}
		if (treeNode.type == TREE_NODE_TYPE.UN_FULL) {
			UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_TIMEPOINT, LANG.UI_DATA_BATCH_DELETE_TIMEPOINT_TIPS);
		}
		//展开取消选中
		nodeExpand(treeId, treeNode);
		zTree.expandNode(treeNode, true);
	}

	//选择时间节点
	let nodeCheck = function (e, id, node) {
		//如果选中的是完备点,勾选和未勾选时同步处理其子节点
		if (module == CONF.MODULE_TYPE.M365) {
			if (node.type == TREE_NODE_TYPE.FULL) {
				let nodeArr = node.getParentNode().children;
				let checkFlag = false;//是否选中增备
				for (var i = 0; i < nodeArr.length; i++) {
					if (nodeArr[i].type == TREE_NODE_TYPE.FULL && nodeArr[i].checked) {//完备是选中的
						checkFlag = true
					} else if (nodeArr[i].type == TREE_NODE_TYPE.UN_FULL && checkFlag) {//选中完备下的增备
						nodeArr[i].checked = true;
						zTree.updateNode(nodeArr[i]);
					} else if (nodeArr[i].type == TREE_NODE_TYPE.FULL && !nodeArr[i].checked) {//未选中
						checkFlag = false;
					} else if (nodeArr[i].type == TREE_NODE_TYPE.UN_FULL && !checkFlag) {//取消选中增备
						nodeArr[i].checked = false;
						zTree.updateNode(nodeArr[i]);
					}
				}
			} else if (node.event_type == TREE_NODE_TYPE.HOST && node.children) {//选中任务
				let nodeArr = node.children;
				for (let i = 0; i < nodeArr.length; i++) {
					if (node.checked) {//选中
						nodeArr[i].checked = true;
						zTree.updateNode(nodeArr[i]);
					} else if (!node.checked) {//取消选中
						nodeArr[i].checked = false;
						zTree.updateNode(nodeArr[i]);
					}
				}
			} else if (node.event_type == TREE_NODE_TYPE.TASK && node.children) {
				let nodeArr = node.children;
				for (var i = 0; i < nodeArr.length; i++) {
					nodeArr[i].checked = node.checked;
					zTree.updateNode(nodeArr[i]);
					let nodeChild = nodeArr[i].children;
					if (nodeChild) {
						for (var j = 0; j < nodeChild.length; j++) {
							nodeChild[j].checked = node.checked;
							zTree.updateNode(nodeChild[j]);
						}
					}
				}
			}
		} else {
			let allNodes = zTree.getCheckedNodes(true);
			//如果选中的是完备点,勾选和未勾选时同步处理其子节点
			if (node.type <= TREE_NODE_TYPE.FULL && node.checked) {
				allNodes
					.filter(n => n.type === TREE_NODE_TYPE.FULL && n.isParent && n.checked)
					.forEach(parentNode => {
						parentNode.children.forEach(child => {
							zTree.setChkDisabled(child, false);
							zTree.checkNode(child, true, true);
							zTree.setChkDisabled(child, true);
						});
					});
			} else if (node.type <= 3) {
				let allChildren = getAllChildren(node, []);
				allChildren
					.filter(child => child.type === TREE_NODE_TYPE.UN_FULL)
					.forEach(child => {
						zTree.setChkDisabled(child, false);
						zTree.checkNode(child, false, true);
						zTree.setChkDisabled(child, true);
					});
			}
		}
	}

	//节点展开异步添加时间点
	let nodeExpand = function (treeId, treeNode) {
		if (treeNode.click_show) {
			if (treeNode.children) return true;
			if (treeId == "copyNodeTree") {
				treeNode.nocheck = false;
			}
			//异步获取时间点信息
			getSyncDataInfo(treeId, treeNode, true, false);
		} else {
			return true;
		}
	}

	//异步获取时间信息
	let getSyncDataInfo = function (treeId, treeNode, refreshFlag, expendFlag) {
		let storage_uuid = $('#storageSelect').val();
		let div = "#copyNodeTree";
		let params = {
			task_uuid: treeNode.task_uuid,
			db_uuid: treeNode.db_uuid,
			sub_type: treeNode.sub_type ? treeNode.sub_type : '',
			agent_uuid: treeNode.agent_uuid,
			copy_flag: treeNode.copy_flag ? treeNode.copy_flag : '',
			data_type: treeNode.data_type,
			id: treeNode.id,
			module: treeNode.module,
			item_uuid: treeNode.item_uuid,
			storage_uuid: storage_uuid ?? '',
			checkNode: treeNode.checked,
			copy_data_flag: true
		};
		Metronic.blockUI({ target: div, animate: true });
		let setTimePoint = function (res) {
			Metronic.unblockUI(div);
			let result = res.data;
			if (result.re) {
				//success
				let treeObj = $.fn.zTree.getZTreeObj(treeId);
				treeObj.addNodes(treeNode, result.msg, true);
				treeObj.expandNode(treeNode, true);
				if (expendFlag == true) {
					treeObj.expandNode(treeNode, true, true, true);
				}
			} else {
				operateResponseList(res);
			}
		}
		pAjaxRequest(deepCloneObject(params), "/api/v1/copy/resources/data", "GET", setTimePoint, true);
	}

	//初始化时间点展示方式和事件
	let initPointShowType = function () {
		//清楚搜索框内容
		$("#searchCopy").val("");
		$('#copyTable').hide();
		$('#markTips').hide();
		$('#noSearchTips').hide();
		$('#tableTips').show();
		$('#copyDataUrl').empty().hide();
		let initStorage = function (res) {
			if (res.success) {
				let storageSelect = $('#storageSelect');
				storageSelect.empty();
				storageSelect.append('<option value="">' + LANG.UI_COPY_LOCAL_STORAGE_ALL + '</option>');
				for (let i = 0; i < res.data.length; i++) {
					let option = '<option data-type="' + res.data[i].storage_type + '" data-name="' + res.data[i].name + '" value="' + res.data[i].storage_uuid + '">' + res.data[i].name + '</option>';
					storageSelect.append(option);
				}
				let storage_type = parseInt($('#storageSelect option:selected').data('type'))
				if (storage_type == 8) {
					getRemoteTree();
				} else {
					initTree();
				}
			}
		}
		pAjaxRequest({ copy_flag: true }, "/api/v1/storages/backup", "GET", initStorage, true);
	}

	// 选择模块类型
	let copyTypeHandler = function () {
		$('#markStr').empty();
		$('#copyTable').hide();
		module = parseInt($('#module').val());
		let storage_type = parseInt($('#storageSelect option:selected').data('type'))
		if (storage_type == 8) {
			getRemoteTree();
		} else {
			initCopyData();
		}
	}

	let searchBack = function (res) {
		Metronic.unblockUI('#copyNodeTree');
		let pointNode = res.data;
		showNode = [];
		// 隐藏错误提示
		$('#noSearchTips').hide();
		$('#copyNodeTree').show();
		// 在搜索之前将所有时间点节点移除
		let allNodes = zTree.transformToArray(zTree.getNodes());
		for (const node of allNodes) {
			if (node.time_point_uuid !== undefined && node.time_point_uuid) {
				zTree.removeNode(node);
			}
		}
		if (pointNode != null) {
			timePointFlag = true;
			pointNode.forEach(item => {
				//在当前树节点中搜索异步请求得到的时间点，如果当前树不存在，加入showNode数组
				let nodeExist = zTree.getNodesByParam("id", item.id, null)[0];
				if (nodeExist == null) {
					showNode.push(item);
				}
			});
			//把showNode中时间点加到对应父节点下
			if (showNode.length != 0) {
				showNode.forEach(item => {
					//先把完备点加进去，再放增量差异等，避免先搜增量差异等搜不到父节点
					if (item.type == TREE_NODE_TYPE.FULL) {
						let parentNode = zTree.getNodesByParam("id", item.pId, null)[0];
						zTree.addNodes(parentNode, item, true);
					}
				});
				showNode.forEach(item => {
					if (item.type == TREE_NODE_TYPE.UN_FULL) {//增量差异等
						let parentNode = zTree.getNodesByParam("id", item.pId, null)[0];
						zTree.addNodes(parentNode, item, true);
					}
				});
			}
		}
		// 在搜索之前将非时间空节点移除
		allNodes = zTree.transformToArray(zTree.getNodes());
		for (const node of allNodes) {
			if (node.time_point_uuid !== undefined && node.time_point_uuid) {
				continue;
			}
			if (!node.children) {
				zTree.removeNode(node)
			}
		}
		searchByTree();
	};
	//异步搜索时间点
	let searchPoint = function () {
		// 输入验证
		const searchValue = $.trim($('#searchCopy').val());
		if (searchValue === "" && !$("input[name=foreverFilter]").get(0).checked) {
			return;
		}
		filterZtree();
		// 任务和虚拟机中没搜索到 去搜时间点
		let storage_uuid = $('#storageSelect').val();
		let module = parseInt($('#module').val());
		let forever = $("input[name=foreverFilter]").get(0).checked;
		let params = { storage_uuid: storage_uuid, module: module, search: searchValue, forever: forever };
		Metronic.blockUI({ target: '#copyNodeTree', animate: true });
		pAjaxRequest(deepCloneObject(params), "/api/v1/copy/data/search", "GET", searchBack, true);
	}

	//在当前树用ztree方法搜索
	let searchByTree = function () {
		let keyword = $.trim($('#searchCopy').val());
		let allNodes = zTree.transformToArray(zTree.getNodes());
		zTree.hideNodes(allNodes);    //当开始搜索时，先将所有节点隐藏
		let nodeList = fuzzySearch('name', keyword, zTree); // 模糊搜索
		//搜gfs标记点
		if ($("input[name=foreverFilter]").get(0).checked) {
			keyword = "viconfont vicon-remark-forever";
			let nodeList_mark = zTree.getNodesByParamFuzzy('name', keyword, 0);
			// 取两个数组的交集
			nodeList = nodeList_mark.filter(item => nodeList.includes(item));
		}
		if (nodeList.length == 0) {
			$('#noSearchTips').show();
			$('#copyNodeTree').hide();
			return;
		}
		let arr = [];
		for (let i = 0; i < nodeList.length; i++) {
			arr = $.merge(arr, nodeList[i].getPath());    //找出节点的所有父节点（包括自己）
			if (nodeList[i].children) {//展开过，有子节点，把子节点加进去
				arr = $.merge(arr, nodeList[i].children);
			}
		}
		zTree.showNodes($.unique(arr));    //显示所有要求的节点及其路径节点
		zTree.checkAllNodes(false);  // 取消选中
		//展开显示的所有节点
		arr.forEach(item => {
			if (item.children) {
				if (item.time_point_uuid !== undefined && item.time_point_uuid) {
					// 完备点的折叠和展开处理
					if (!keyword) {
						return;
					}
					item.children.map(child => {
						let match_value = child.name.replace(/<.*?>/ig, '').toLowerCase()
						if (match_value.indexOf(keyword.toLowerCase()) > -1) { // 如果搜索到完备点下面的节点, 需要展开完备点
							zTree.expandNode(item, true);
						}
					});
					return;
				}
				zTree.expandNode(item, true);
			}
		});
	}


	// 树模糊搜索, 去除html标签, 返回所有子树
	let fuzzySearch = function (key, value, tree) {
		value = value.toLowerCase();
		let allNodes = tree.transformToArray(tree.getNodes());
		let retNodes = [];
		let nodeIds = [];
		for (const node of allNodes) {
			if (nodeIds.includes(node.id)) {
				continue;
			}
			if (node.type == TREE_NODE_TYPE.UN_FULL) {
				continue;
			}
			let match_value = node[key].replace(/<.*?>/ig, '').toLowerCase();  // 去除html标签
			if (match_value.indexOf(value) > -1) {
				retNodes.push(node);
				nodeIds.push(node.id);
				// 这里需要将子树加入到列表里面, 因为永久备份点是在时间点节点上筛选的
				let children = tree.getNodesByFilter(() => {
					return true;
				}, false, node);
				for (const child of children) {
					if (nodeIds.includes(child.id)) {
						continue;
					}
					nodeIds.push(child.id);
					retNodes.push(child);
				}
			}
		}
		return retNodes;
	}

	//ztree的复杂过滤
	let filterZtree = function () {
		let markStr = LANG.UI_SETTING_VM_DATA_SCREEN + ": ";
		//获得数据
		let value = $('#searchCopy').val();
		let forever = $("input[name=foreverFilter]").get(0).checked;
		if (value == "" && !forever) {
			$("#markStr").empty();
			return true;
		}
		if (forever) {
			markStr += '<i class="viconfont vicon-remark-forever"></i>'
		}
		//检测
		if (value != "") {
			markStr += value;
		}
		markStr += '<a id="clearGFS" style="text-decoration: underline;margin-left: 10px;">' + LANG.UI_SEARCH_CLEAR + '</a>'
		$("#markStr").html(markStr);
		//添加清除点击事件
		$("#clearGFS").on('click', function () {
			$('#noSearchTips').hide();
			if (timePointFlag) {
				initTree();
			}
			$("#markStr").empty();
		})
	}


	// 初始化数据表格
	let copyDataTable = function (treeNode) {
		let storage_uuid = $('#storageSelect').val();
		let node_uuid = $('#copyModuleTypeSelect').attr('value');
		let options = {
			vin_url: "/api/v1/copy/resources/data",
			vin_method: "GET",
			changeHeightBtn: true, //改变高度按钮
			sortName: 'time_point',
			sortOrder: 'desc',
			vin_params: function () {
				let params = {
					task_uuid: treeNode.task_uuid,
					db_uuid: treeNode.db_uuid,
					sub_type: treeNode.sub_type,
					agent_uuid: treeNode.agent_uuid,
					copy_flag: treeNode.copy_flag,
					data_type: treeNode.data_type,
					node_uuid: node_uuid,
					id: treeNode.id,
					module: treeNode.module,
					item_uuid: treeNode.item_uuid,
					data_table_flag: true,
					storage_uuid: storage_uuid ?? ''
				};
				return params;
			},
			onPostBody: function () {
				$('.remarktips').popover();
				$('#markTips').show();
                $('#dataTable th[data-field="time_point"]').css('width', '25%');
                $('#dataTable th[data-field="time_type"]').css('width', '10%');
                $('#dataTable th[data-field="data_size"]').css('width', '10%');
                $('#dataTable th[data-field="write_size"]').css('width', '10%');
                $('#dataTable th[data-field="storage"]').css('width', '25%');
                $('#dataTable th[data-field="action"]').css('width', '10%');
			},

			columns: [
			{
				field: 'time_point',
				title: LANG.UI_COPY_TIMEPOINT,
				sortable: true,
				align: 'center',
				formatter: function (value, row, index) {
					let time_point = '<div>';
					time_point += '<span>' + row.time_point + '</span>';
					if (row.mark) {
						time_point +='<span>' + row.mark + '</span>';
					};
					if (row.remark) {
						time_point +='<span>' + row.remark + '</span>';
					};
					return time_point;
				}
			},
			{
				field: 'time_type',
				title: LANG.UI_COPY_TYPE,
				sortable: true,
				align: 'center',
			},
			{
				field: 'data_size',
				title: LANG.UI_COPY_DATA_SIZE,
				sortable: true,
				align: 'center',
			},
			{
				field: 'write_size',
				title: LANG.UI_PUBLIC_REAL_SIZE,
				sortable: true,
				align: 'center',
			},
			{
				field: 'storage',
				title: LANG.UI_COPY_DATA_STORAGE,
				sortable: false,
				align: 'center',
			},
			{
				field: 'action',
				title: LANG.UI_PUBLIC_OPERATION,
				sortable: false,
				align: 'center',
				events: operateEvents,
				opButton: true,
				formatter: function (value, row, index) {
					let uuid = row.uuid;
					let button = '<div class="btn-group">';
					if (index > 5) {
						button = '<div class="btn-group dropup">';
					}

					button += '<button style="line-height:16px" type="button" class="btn btn-success btn-sm dropdown-toggle" data-toggle="dropdown" ' +
						'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
						'' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
						'</button>' +
						'<ul class="dropdown-menu" role="menu" id=' + uuid + '>';
					for (let i = 0; i < value.length; i++) {
						switch (value[i]) {
							case 1:
								button += '<li class="remark"><a href="javascript:;" ><i class="viconfont vicon-ge_modify"></i> ' + LANG.UI_PUBLIC_REMARK + '</a></li>';
								break;
							case 3:
								button += '<li class="setMark"><a href="javascript:;"><i class="viconfont vicon-ge_sign"></i> ' + LANG.UI_SETTING_VM_DATA_SET_MARK + '</a></li>';
								break;
							case 4:
								button += '<li class="sync-data"><a href="javascript:;" ><i class="viconfont vicon-ge_refresh"></i> ' + LANG.UI_MICROSOFT365_SYNC + '</a></li>';
								break;
						}
					}
					button += '</ul></div>';
					return button;
				}
			},
			],
		}
		if (!initTaskTableFlag) {
			$('#dataTable').baseTableConfig().init(options);
			initTaskTableFlag = true;
		} else {
			// 如果已经初始化销毁表格再初始化新表
			// 销毁表格
			$('#dataTable').bootstrapTable('destroy');
			sessionStorage.removeItem('dataTable_pageRecord');
			$('#dataTable').baseTableConfig().init(options);
		}
	}

	let operateEvents = {
		'click .remark': function (e, value, row, index) {
			let remark_info = row.remark_info;
			let uuid = row.uuid;
			bootbox.prompt({
				title: LANG.UI_DATA_ADD_REMARK,
				value: remark_info,
				callback: function (result) {
					if (result == null || $.trim(result) == value) return;
					submitRemark($.trim(result), uuid);
				}
			});
		},
		'click .setMark': function (e, value, row, index) {
			//得到GFS保留标记
			gfsList = {};
			gfsList.forever = row.importance_flag;//永久保留
			//初始化清空所有勾选项
			$("input[name=foreverCheck]").iCheck('uncheck');
			//设置为只有完全备份才可GFS
			$("input[name=foreverCheck]").iCheck('enable');

			//设置值
			$("#Marktimepoint_uuid").val(row.uuid);
			//设置勾选
			if (gfsList.forever) {
				$("input[name=foreverCheck]").iCheck('check');
			}
			$('#setAllMark').modal('show');
		},
		'click .sync-data': function (event, value, row, index) {
			$('#syncPointData').modal({ 'width': '660px', 'height': '100%' });
			syncPointData(row);
		},
	}
	// 云存储上的完备点索引数据同步
	let syncPointData = function (row) {
		let nodeId = row.node_uuid;
		if(row.storage_type == CONF.BD_STORAGE_TYPE.CLOUD) {
			nodeId = row.real_node_uuid;
		}
		let p = { "timepoint_uuid": row.uuid, "op_id": op_id, "node_uuid": nodeId };
		pAjaxRequest(p, "/api/v1/exchange/manage_sync", "GET", function (result) {
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
	//批量删除所选时间点
	let deleteSelectPoint = function () {
		let node = zTree.getCheckedNodes(true);
		if (node.length == 0) {
			UIToastr.showInfo(LANG.UI_COPY_DATA_BATCH_DELELE, LANG.UI_COPY_DATA_BATCH_DELELE_TIPS);
			return false;
		}
		deleteCopyPoint(node);
	};

	let deleteCopyPoint = function (node) {
		time_point_list = [];  //每次删除先清空时间点列表
		deleteList = []; //待删除列表
		let tepList = [];  //每次删除先清空时间点列表
		let moduleDes = $('#module option:selected').text();
		for (var i = 0; i < node.length; i++) {
			if (node[i].type == '2') {
				if (!node[i].children) {
					var copyData = {};
					copyData.node_uuid = node[i].node_uuid;
					copyData.task_uuid = node[i].task_uuid;
					copyData.item_uuid = node[i].item_uuid;
					copyData.storage_uuid = node[i].storage_uuid;
					deleteList.push(copyData);
				}
			}
			if (node[i].type == '3' || node[i].type == '4') {
				let deleteDetails = `${LANG.UI_PUBLIC_TASK_NAME}：${node[i].task_name}，${LANG.UI_GLOBAL_STRATEGY_MODULE_TYPE}：${moduleDes}，${LANG.UI_RECOVERY_TIMEPOINT}：${node[i].timepoint}`;
				if (module != CONF.MODULE_TYPE.M365) {
					var data = {};
					data.time_point_uuid = node[i].time_point_uuid;
					data.node_uuid = node[i].node_uuid;
					tepList.push(data);
					data.storage_uuid = node[i].storage_uuid;
					data.task_uuid = node[i].task_uuid;
					deleteStorageUUid = node[i].storage_uuid;
					data.deleteDetails = deleteDetails;
					if (node[i].isParent) {
						var children = node[i].children;
						for (var j = 0; j < children.length; j++) {
							var data = {};
							data.time_point_uuid = children[j].time_point_uuid;
							data.node_uuid = children[j].node_uuid;
							data.storage_uuid = children[j].storage_uuid;
							data.task_uuid = children[j].task_uuid;
							data.deleteDetails = deleteDetails;
							tepList.push(data);
						}
					}

				} else {
					let child_nodes = node[i].getParentNode().children;
					for (var j = 0; j < child_nodes.length; j++) {
						if (child_nodes[j].checked) {//完备是选中的
							let data = {};
							data.time_point_uuid = child_nodes[j].time_point_uuid;
							data.node_uuid = child_nodes[j].node_uuid;
							data.storage_uuid = child_nodes[j].storage_uuid;
							data.task_uuid = child_nodes[j].task_uuid;
							data.deleteDetails = deleteDetails;
							tepList.push(data);
						}
					}
				}
			}
		}
		// 去掉重复的时间点
		time_point_list = [...new Set(tepList.map(JSON.stringify))].map(JSON.parse);
		bootbox.confirm({
			title: LANG.UI_DATA_DELETE_TIMEPOINT,
			message: LANG.UI_DATA_CONFIRM_DELETE_TIPS1 + LANG.UI_DATA_CONFIRM_DELETE_TIPS2,
			callback: function (r) {
				if (!r) return;
				initErrorFlag = false;
				bootbox.prompt({
					title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES,
					inputType: 'password',
					callback: function (result) {
						if (result == null) return;
						if (hex_md5(result) == _UserPassword) {
							submitSelectDelete();
							return true;
						} else {
							$('.bootbox-input').css('border-color', "#a94442");
							if (!initErrorFlag) {
								let des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS + '</p>';
								$('.bootbox-input').after(des);
								initErrorFlag = true;
							}
							return false;
						}
					}
				});
			}
		});
	}

	// 提交批量删除副本点
	let submitSelectDelete = function () {
		let node = zTree.getCheckedNodes(true);
		let params = { time_point_list: time_point_list, deleteList: deleteList, module: module, storage_uuid: deleteStorageUUid };
		Metronic.blockUI({ target: '.timepoint-wrapper__content', animate: true });
		let deleteBack = function (res) {
			Metronic.unblockUI('.timepoint-wrapper__content');
			if (operateResponseList(res)) {
				for(let i=0;i<node.length;i++){
					let halfCheck = node[i].getCheckStatus();
					if(!halfCheck.half){
						refreshAllTree(node[i].id);
					}else{
						zTree.checkNode(node[i],!node[i].checked,false,false);
					}
				}
				$('#dataTable').bootstrapTable('refresh');
			};
		};
		pAjaxRequest(deepCloneObject(params), "/api/v1/copy", 'DELETE', deleteBack, true);
	}
	//一一对应timepointuuid进行清除树节点
	let refreshAllTree = function(nodeid){
		let nodes = zTree.getNodesByParam("id", nodeid, null);
		let childrenNum = 0;
		if(0 == nodes.length){
			return;
		}
		//要删除节点的父节点,如果父节点只有1个孩子节点,把父节点也要删除,父节点是任务名,不再处理任务名的父节点
		let parent = nodes[0].getParentNode();
		if(parent !=null){
			childrenNum = parent.children.length;
		}
		if(1 == childrenNum){
			zTree.removeNode(parent);
		}else{
			zTree.removeNode(nodes[0]);
		}
	}

	//设置标记确认
	let markSubmit = function () {
		//先得到所有信息
		let forever = $("input[name=foreverCheck]").get(0).checked;
		//得到信息后先判断是否有做修改 如果没有做修改则不提交后台
		if (gfsList.forever == forever) {
			$('#setAllMark').modal('hide');
			gfsList = {};
			return;
		}
		//时间点uuid
		let uuid = $("#Marktimepoint_uuid").val();
		//GFS和F标记点是分开发信息的 所以要发送2个信息
		//设置永久标记
		if (gfsList.forever != forever) {
			let type = 'POST';
			if (forever) {
				type = 'POST';
			} else {
				type = 'DELETE';
			}
			starHandler(uuid, type, forever);
		}
	}

	//标星统一处理
	let starHandler = function (uuid, funName, flag) {
		Metronic.blockUI({ target: '#setAllMark', animate: true });
		let markBack = function (res) {
			Metronic.unblockUI('#setAllMark');
			if (res.success) {
				$('#setAllMark').modal('hide');
				$('#dataTable').bootstrapTable('refresh');
				editTreeName(uuid, flag);
			};
		};
		//发送到数据管理统一处理
		pAjaxRequest({ time_point_uuid: uuid }, "/api/v1/copy/data/mark", funName, markBack, true);
	}
	// 静态修改树节点名
	let editTreeName = function (uuid, flag) {
		//根据timeUUID得到ztree的node数据，没有搜到返回null
		let node = zTree.getNodeByParam('time_point_uuid', uuid, null);
		//获得原来的name
		let old_name = node.oldname;
		let markStr = '';
		if (flag) {
			markStr += '<span title="' + LANG.UI_SETTING_VM_GFS_FOREVER_POINT + '" class="viconfont vicon-remark-forever"></span>';
		}
		let new_name = old_name + " " + markStr;

		node.name = new_name;
		zTree.updateNode(node);
	}

	//得到当前节点的所有子节点
	let getAllChildren = function (node, allNode) {
		if (node.isParent) {
			let children = node.children;
			if (children) {
				for (let i = 0; i < children.length; i++) {
					allNode.push(children[i]);
					if (children[i].isParent) {
						getAllChildren(children[i], allNode);
					}
				}
			}
		} else {
			allNode.push(node);
		}
		return allNode;
	}

	//节点选择改变事件
	let nodeSelectChange = function () {
		$('#markStr').empty();
		$('#copyTable').hide();
		let storage_type = parseInt($('#storageSelect option:selected').data('type'))
		if (storage_type == CONF.BD_STORAGE_TYPE.REMOTE) {
			getRemoteTree();
			$('#searchBtn').hide();
		} else {
			// 从数据库获取
			initTree();
			$('#searchBtn').show();
		}
	}
	let iniTree = (res, remoteFlag = false) => {
		let zNodes = res.data;
			if (zNodes.length == 0) {
				$("#copyNodeTree").hide();
				$("#noPointTips").show();
				$("#tableTips").show();
				$("#copyDataUrl").html('');
				return;
			} else {
				$("#copyNodeTree").show();
				$('#noPointTips').hide();
			}
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
					beforeClick: remoteFlag ? remoteSelect : nodeSelect,
					onCheck: nodeCheck,
					beforeExpand: remoteFlag ? remoteExpand : nodeExpand,
				},
				view: {
					nameIsHTML: true
				}
			};
			zTree = $.fn.zTree.init($("#copyNodeTree"), setting, zNodes);
	}

	let getRemoteTree = function () {
		let storageUuid = $('#storageSelect').val();
		let moduleType = parseInt($('#module').val());
		let remoteTaskBack = function (res) {
			iniTree(res, true);
		};
		pAjaxRequest({ storageUuid: storageUuid, module_type: moduleType }, "/api/v1/copy/resources/remote/task", "GET", remoteTaskBack, true);
	}
	let remoteSelect = function (treeId, treeNode) {
		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
		remoteExpand(treeId, treeNode);
		if (treeNode.event_type == TREE_NODE_TYPE.HOST) {
			$('#copyDataUrl').text('');
			let parent = treeNode.getParentNode();
			let info = parent.name + "---" + treeNode.name;
			$('#copyDataUrl').append(info);
			$('#copyTable').show();
			$('#tableTips').hide();
			copyRemoteTable(treeNode); //录入备份数据列表
		}
	}

	let copyRemoteTable = function (treeNode) {
		let options = {
			vin_url: "/api/v1/copy/resources/remote/timepoint",
			vin_method: "GET",
			changeHeightBtn: true, //改变高度按钮
			sortName: 'timepoint',
			sortOrder: 'desc',
			vin_params: function () {
				let params = deepCloneObject({ storageUuid: treeNode.storage_uuid, module_type: treeNode.module_type, item_uuid: treeNode.item_uuid, task_uuid: treeNode.task_uuid, data_table_flag: true, sub_module_type: treeNode.sub_module_type,task_name: treeNode.task_name });
				return params;
			},
			onPostBody: function () {
				$('.remarktips').popover();
                $('#dataTable th[data-field="time_point"]').css('width', '25%');
                $('#dataTable th[data-field="time_type"]').css('width', '10%');
                $('#dataTable th[data-field="data_size"]').css('width', '10%');
                $('#dataTable th[data-field="write_size"]').css('width', '10%');
                $('#dataTable th[data-field="storage"]').css('width', '25%');
                $('#dataTable th[data-field="action"]').css('width', '10%');
			},

			columns: [
				{
					field: 'timepoint',
					title: LANG.UI_COPY_TIMEPOINT,
					sortable: true,
					align: 'center',
					formatter: function (value, row, index) {
						let time_point = '<div>';
						time_point += '<span>' + row.timepoint + '</span>';
						if (row.mark) {
							time_point +='<span>' + row.mark + '</span>';
						};
						if (row.remark) {
							time_point +='<span>' + row.remark + '</span>';
						};
						return time_point;
					}
				},
				{
					field: 'backup_mode',
					title: LANG.UI_COPY_TYPE,
					sortable: true,
					align: 'center',
				},
				{
					field: 'total_size',
					title: LANG.UI_COPY_DATA_SIZE,
					sortable: true,
					align: 'center',
				},
				{
					field: 'write_size',
					title: LANG.UI_PUBLIC_REAL_SIZE,
					sortable: true,
					align: 'center',
				},
				{
					field: 'storage',
					title: LANG.UI_COPY_DATA_STORAGE,
					sortable: false,
					align: 'center',
				},
			],
		}
		if (!initTaskTableFlag) {
			$('#dataTable').baseTableConfig().init(options);
			initTaskTableFlag = true;
		} else {
			// 如果已经初始化销毁表格再初始化新表
			// 销毁表格
			$('#dataTable').bootstrapTable('destroy');
			sessionStorage.removeItem('dataTable_pageRecord');
			$('#dataTable').baseTableConfig().init(options);
		}
	}

	let remoteExpand = function (treeId, treeNode, expendFlag) {
		if (treeNode.click_show) {
			if (!treeNode.isParent) return true;
			if (treeNode.event_type == TREE_NODE_TYPE.TASK) {
				getAsyncHost(treeId, treeNode, expendFlag)
			}
			if (treeNode.event_type == TREE_NODE_TYPE.HOST) {
				treeNode.nocheck = false;
				getAsyncPoint(treeId, treeNode, expendFlag)
			}
		} else {
			return true;
		}
	}

	// 获取异地树-主机
	let getAsyncHost = function (treeId, treeNode, expendFlag) {
		let remoteHostBack = function (res) {
			if (res.success) {
				$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
				$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, res.data, true);
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
				if (expendFlag == true) {
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
				}
			}
		};
		if (treeNode.click_show) {
			pAjaxRequest(deepCloneObject({ storageUuid: treeNode.storage_uuid, module_type: treeNode.module_type, parent_tree_uuid: treeNode.task_uuid, task_name: treeNode.task_name, sub_module_type: treeNode.sub_module_type }), "/api/v1/copy/resources/remote/host", "get", remoteHostBack, true);
		}
	}
	// 获取异地树-时间点
	let getAsyncPoint = function (treeId, treeNode, expendFlag) {
		let remoteHostBack = function (res) {
			if (res.success) {
				$.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
				$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, res.data, true);
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
				if (expendFlag == true) {
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
				}
			}
		};
		if (treeNode.click_show) {
			pAjaxRequest(deepCloneObject({ storageUuid: treeNode.storage_uuid, module_type: treeNode.module_type, item_uuid: treeNode.item_uuid, task_uuid: treeNode.task_uuid, item_name: treeNode.name, checked: treeNode.checked, copy_data_flag: true,task_name: treeNode.task_name }), "/api/v1/copy/resources/remote/timepoint", "get", remoteHostBack, true);
		}
	}

	//递归移除节点,如果是增量的话是相互依赖的(都是依赖于上一个备份点)
	let removeNode = function (nodes, treeNode) {
		for (let i = 0; i < nodes.length; i++) {
			if (nodes[i].type == 3) {
				if (nodes[i].depend_uuid == treeNode.timepointuuid) {
					zTree.removeNode(nodes[i]);
					removeNode(nodes, nodes[i]);
				}
			}
		}
	}

	//找到父节点
	let findParent = function (treeObj, node) {
		zTree.expandNode(node, true, false, false);
		if (!node.children) {
			zTree.expandNode(node, false, false, false);
		}
		if (!node.isParent) {
			nodeParamList.push(node);
		}
		let pNode = node.getParentNode();
		if (pNode != null) {
			nodeParamList.push(pNode);
			findParent(zTree, pNode);
		}
	}

	// 提交备注
	let submitRemark = function (remark, uuid) {
		let remarkBack = function (res) {
			if (res.success) {
				$('#dataTable').bootstrapTable('refresh');
			};
		};
		pAjaxRequest({ remark: remark, time_point_uuid: uuid }, "/api/v1/copy/data/remark", "PUT", remarkBack, true);
	}
	//删除父节点
	let removeParentNode = function (node) {
		let parent = node.getParentNode();
		let childrenNum = parent.children.length;
		if (0 == childrenNum) {
			zTree.removeNode(parent);
			removeParentNode(parent);
		} else {
			return;
		}

	}


	let inintDatatimePicker = function () {
		if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
			//英文独有的
			$(".form_datetime").datetimepicker({
				autoclose: true,
				isRTL: Metronic.isRTL(),
				format: "yyyy-mm-dd hh:ii:ss",
				pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
			});
		} else {
			$(".form_datetime").datetimepicker({
				language: 'zh-CN',
				autoclose: true,
				isRTL: Metronic.isRTL(),
				format: "yyyy-MM-dd hh:ii:ss",
				pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
			});
		}
	}

	//初始化当前用户密码用于删除二次确认
	let initUserPassword = function () {
		pAjaxRequest({}, "/api/v1/users/password", "GET", function (res) {
			_UserPassword = res.data.password;
		}, true);
	}

	return {
		//main function to initiate the module
		init: function () {
			inintDatatimePicker();    //初始化时间
			initCopyData();			//初始化副本数据
			initListener();
			initUserPassword();
		}

	};
}();

jQuery(document).ready(function () {
	CopyData.init();
});