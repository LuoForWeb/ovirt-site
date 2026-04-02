var archiveData = function () {
	let zTree, nodeParamList, time_point_list; //节点树，节点搜索列表，时间点存放列表, 虚拟机列表
	let _UserPassword;
	let initErrorFlag = false;
	let initTaskTableFlag = false;
	let deleteStorageUUid = '';
	let showNode;//搜索到的时间点

	//初始化虚拟机节点树
	let initTree = function () {
		let storage_uuid = $('#storageSelect').val();
		let module = $('#moduleType').val();
		Metronic.blockUI({ target: '#copyNodeTree', animate: true });
		pAjaxRequest(deepCloneObject({ data_type: 4, module: module, storage_uuid: storage_uuid, archive_data_flag: true }), "/api/v1/copy/resources", "GET", setTree, true);
	}

	let setTree = function (res) {
		zNodes = res.data;
		$("#noPointTips").hide();
		$("#copyNodeTree").show();
		Metronic.unblockUI('#copyNodeTree');
		if (zNodes.length == 0) {
			$("#copyNodeTree").hide();
			$("#noPointTips").show();
			$("#tableTips").show();
			$("#copyDataUrl").html('');
			return;
		}
		function showTitleForTree(treeId, treeNode) {
			return treeNode.type != 1;
		};
		let setting = {
			check: {
				enable: true,
				nocheckInherit: false,
				//					chkboxType: { "Y": "s", "N": "s" }
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
				beforeClick: nodeSelect,
				onCheck: nodeCheck,
				beforeExpand: nodeExpand
			},
			view: {
				showTitle: showTitleForTree,
				nameIsHTML: true
			}
		};
		zTree = $.fn.zTree.init($("#copyNodeTree"), setting, zNodes);
	};

	//点击节点，选中并展开
	let nodeSelect = function (treeId, treeNode, clickFlag) {
		if (treeNode.type == 2) {
			$('#copyDataUrl').text('');
			let parent = treeNode.getParentNode();
			let info = parent.name + "---" + treeNode.name;
			$('#copyDataUrl').append(info);
			archiveDataTable(treeNode); //录入备份数据列表
			$('#tableTips').hide();
			$('#copyTable').show();
			$('#markTips').show();
		}
		if (treeNode.type == 4) {
			UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_TIMEPOINT, LANG.UI_DATA_BATCH_DELETE_TIMEPOINT_TIPS);
		}
		nodeExpand(treeId, treeNode);
		zTree.expandNode(treeNode, true);
		if(treeNode.event_type === 'loadMore') {
			$.fn.zTree.getZTreeObj(treeId).removeNode(treeNode);
			getAsyncTimePoint(treeId, parentNode, true, false);
		}
	}

	//节点展开异步添加时间点
	let nodeExpand = function (treeId, treeNode) {
		if (treeNode.click_show) {
			if (treeNode.children) return true;
			treeNode.nocheck = false;
			getAsyncTimePoint(treeId, treeNode, false, false);
		} else {
			return true;
		}

	}

	//异步获取虚拟化中心/宿主机的信息  refreshFlag是否重新刷新
	let getAsyncTimePoint = function (treeId, treeNode, refreshFlag, expendFlag) {
		let storage_uuid = $('#storageSelect').val();
		let div = "#copyNodeTree";
		let params = {
			task_uuid: treeNode.task_uuid,
			db_uuid: treeNode.db_uuid ? treeNode.db_uuid : '',
			sub_type: treeNode.sub_type ? treeNode.sub_type : '',
			agent_uuid: treeNode.agent_uuid,
			data_type: treeNode.data_type,
			id: treeNode.id,
			module: treeNode.module,
			item_uuid: treeNode.item_uuid,
			archive_flag: true,
			checkNode: treeNode.checked,
			copy_data_flag: true,
			storage_uuid: storage_uuid ?? '',
		};
		Metronic.blockUI({ target: div, animate: true });
		let setTimePoint = function (res) {
			Metronic.unblockUI(div);
			let result = res.data;
			if (result.re) {
				$.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result.msg, true);
				$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
				if (expendFlag == true) {
					$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
				}
			} else {
				OPREL(data);
			}
		}

		pAjaxRequest(deepCloneObject(params), "/api/v1/copy/resources/data", "GET", setTimePoint, true);
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

	//选择备份节点
	let nodeCheck = function (e, id, node) {
		let allNodes = zTree.getCheckedNodes(true);
		//如果选中的是完备点,勾选和未勾选时同步处理其子节点
		if (node.type <= 3 && node.checked) {
			for (let j = 0; j < allNodes.length; j++) {
				if (allNodes[j].type == 3 && allNodes[j].isParent && allNodes[j].checked) {
					let children = allNodes[j].children;
					for (let i = 0; i < children.length; i++) {
						zTree.setChkDisabled(children[i], false);
						zTree.checkNode(children[i], true, true);
						zTree.setChkDisabled(children[i], true);
					}
				}
			}
		} else if (node.type <= 3) {
			let allChildren = getAllChildren(node, []);
			for (let j = 0; j < allChildren.length; j++) {
				if (allChildren[j].type == 4) {
					zTree.setChkDisabled(allChildren[j], false);
					zTree.checkNode(allChildren[j], false, true);
					zTree.setChkDisabled(allChildren[j], true);
				}
			}
		}
	}


	//初始化时间点展示方式和事件
	let initPointShowType = function () {
		let initStorage = function (res) {
			if (res.success) {
				let storageSelect = $('#storageSelect');
				storageSelect.empty();
				storageSelect.append('<option value="">' + LANG.UI_COPY_LOCAL_STORAGE_ALL + '</option>');
				for (let i = 0; i < res.data.length; i++) {
					let mode = res.data[i].storage_type;
					if (mode != CONF.BD_STORAGE_TYPE.REMOTE && mode != CONF.BD_STORAGE_TYPE.HUAWEI_CBR) {
						let option = '<option data-type="' + res.data[i].storage_type + '" data-name="' + res.data[i].name + '" value="' + res.data[i].storage_uuid + '">' + res.data[i].name + '</option>';
						storageSelect.append(option);
					}
				}
				initTree();
			}

		}
		pAjaxRequest({ copy_flag: true }, "/api/v1/storages/backup", "GET", initStorage, true);

		//绑定事件
		$('#storageSelect').on('change', storage_change);
	}

	//节点选择改变事件
	let storage_change = function () {
		$("#searchCopy").val("");
		$('#copyTable').hide();
		$('#markTips').hide();
		$('#noSearchTips').hide();
		$('#tableTips').show();
		$('#copyDataUrl').empty().hide();
		$('#copyDataUrl').show();
		let storage_type = parseInt($('#storageSelect option:selected').data('type'))
		if (storage_type == 8) {
			getRemoteTree();
		} else {
			// 从数据库获取
			initTree();
		}
	}

	let submitRemark = function (remark, uuid) {
		let remarkBack = function (res) {
			if (res.success) {
				$('#dataTable').bootstrapTable('refresh');
			};
		};
		pAjaxRequest({ remark: remark, time_point_uuid: uuid }, "/api/v1/copy/data/remark", "PUT", remarkBack, true);
	}

	//初始化监听事件
	let initListener = function () {
		// 选择模块类型
		$('#moduleType').on('change', copyTypeHandler);
		$('#allDelete').on('click', deleteSelectPoint);
		$('#searchvm').on('propertychange', searchVM).on('input', searchVM);

		//设置标记确认
		$("#mark_submit").on('click', function () {
			markSubmit();
		});

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
	// 选择模块类型
	let copyTypeHandler = function () {
		$('#markStr').empty();
		$('#copyTable').hide();
		let storage_type = parseInt($('#storageSelect option:selected').data('type'))
		if (storage_type == 8) {
			getRemoteTree();
		} else {
			initCopyData();
		}
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
	let getRemoteTree = function () {
		let storageUuid = $('#storageSelect').val();
		let moduleType = parseInt($('#moduleType').val());
		let remoteTaskBack = function (res) {
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
					beforeClick: remoteSelect,
					onCheck: remoteCheck,
					beforeExpand: remoteExpand
				},
				view: {
					nameIsHTML: true
				}
			};
			zTree = '';
			zTree = $.fn.zTree.init($("#copyNodeTree"), setting, zNodes);
		};
		pAjaxRequest({ storageUuid: storageUuid, module_type: moduleType }, "/api/v1/copy/resources/remote/task", "GET", remoteTaskBack, true);
	}
	let remoteSelect = function (treeId, treeNode) {
		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
		remoteExpand(treeId, treeNode);
		if (treeNode.event_type == 'host') {
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
				let params = deepCloneObject({ storageUuid: treeNode.storage_uuid, module_type: treeNode.module_type, item_uuid: treeNode.item_uuid, task_uuid: treeNode.task_uuid, data_table_flag: true, sub_module_type: treeNode.sub_module_type });
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
						time_point += '<span>' + row.timepoint + '</span><br>';
						if (row.mark) {
							time_point += row.mark;
						};
						if (row.remark) {
							time_point += row.remark;
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
					sortable: true,
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
			$('#dataTable').baseTableConfig().init(options);
		}
	}

	let remoteCheck = function (treeId, treeNode) {
		return true;
	}

	let remoteExpand = function (treeId, treeNode, expendFlag) {
		if (treeNode.click_show) {
			if (!treeNode.isParent) return true;
			if (treeNode.event_type == "task") {
				getAsyncHost(treeId, treeNode, expendFlag)
			}
			if (treeNode.event_type == "host") {
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
			pAjaxRequest(deepCloneObject({ storageUuid: treeNode.storage_uuid, module_type: treeNode.module_type, parent_tree_uuid: treeNode.task_uuid, task_name: treeNode.name, sub_module_type: treeNode.sub_module_type }), "/api/v1/copy/resources/remote/host", "get", remoteHostBack, true);
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
			pAjaxRequest(deepCloneObject({ storageUuid: treeNode.storage_uuid, module_type: treeNode.module_type, item_uuid: treeNode.item_uuid, task_uuid: treeNode.task_uuid, item_name: treeNode.name, checked: treeNode.checked, copy_data_flag: true, sub_module_type: treeNode.sub_module_type }), "/api/v1/copy/resources/remote/timepoint", "get", remoteHostBack, true);
		}
	}

	//异步搜索时间点
	let searchPoint = function () {
		if ($.trim($('#searchCopy').val()) == "" && !$("input[name=foreverFilter]").get(0).checked) {
			return;
		}
		filterZtree();
		// 任务和虚拟机中没搜索到 去搜时间点
		let storage_uuid = $('#storageSelect').val();
		let module = $('#moduleType').val();
		let search = $('#searchCopy').val();
		let forever = $("input[name=foreverFilter]").get(0).checked;
		let params = { storage_uuid: storage_uuid, module: module, search: search, forever: forever, archive_flag: true };
		Metronic.blockUI({ target: '#copyNodeTree', animate: true });
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
						if (item.type == 3) {
							let parentNode = zTree.getNodesByParam("id", item.pId, null)[0];
							zTree.addNodes(parentNode, item, true);
						}
					});
					showNode.forEach(item => {
						if (item.type == 4) {//增量差异等
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

		pAjaxRequest(deepCloneObject(params), "/api/v1/copy/data/search", "GET", searchBack, true);
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
		// $('#database_tree')[0].scrollTop = '0px'; //滚动条保持在顶部
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
			if (node.type == 4) {
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

	//批量删除所选时间点
	let deleteSelectPoint = function () {
		let node = zTree.getCheckedNodes(true);
		if (node.length == 0) {
			UIToastr.showInfo(LANG.UI_COPY_DATA_BATCH_DELELE, LANG.UI_COPY_DATA_BATCH_DELELE_TIPS);
			return false;
		}
		deleteCopyPoint(node);
	};

	//批量删除所选时间点
	let deleteCopyPoint = function (node) {
		time_point_list = [];  //每次删除先清空时间点列表
		deleteList = []; //待删除列表
		for (let i = 0; i < node.length; i++) {
			if (node[i].type == '2') {
				if (!node[i].children) {
					let copyData = {};
					copyData.node_uuid = node[i].node_uuid;
					copyData.task_uuid = node[i].task_uuid;
					copyData.item_uuid = node[i].item_uuid;
					copyData.storage_uuid = node[i].storage_uuid;
					deleteList.push(copyData);
				}
			}
			if (node[i].type == '3') {
				let data = {};
				data.time_point_uuid = node[i].time_point_uuid;
				data.node_uuid = node[i].node_uuid;
				data.task_uuid = node[i].task_uuid;
				time_point_list.push(data);
				data.storage_uuid = node[i].storage_uuid;
				deleteStorageUUid = node[i].storage_uuid;
				if (node[i].isParent) {
					let children = node[i].children;
					for (let j = 0; j < children.length; j++) {
						let data = {};
						data.time_point_uuid = children[j].time_point_uuid;
						data.node_uuid = children[j].node_uuid;
						data.storage_uuid = children[j].storage_uuid;
						data.task_uuid = children[j].task_uuid;
						time_point_list.push(data);
					}
				}
			}
		}
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

	//删除所选择的
	let submitSelectDelete = function () {
		let module = $('#moduleType').val();
		let params = { time_point_list: time_point_list, deleteList: deleteList, module: module, storage_uuid: deleteStorageUUid, archive_data: true };
		Metronic.blockUI({ target: '.timepoint-wrapper__content', animate: true });
		let deleteBack = function (res) {
			Metronic.unblockUI('.timepoint-wrapper__content');
			if (operateResponseList(res)) {
				initTree();
				$('#dataTable').bootstrapTable('destroy');
				$('#tableTips').show();
				$('#copyDataUrl').val('');
			};
		};
		pAjaxRequest(deepCloneObject(params), "/api/v1/copy", 'DELETE', deleteBack, true);
	}

	//定位搜索时间节点
	let searchVM = function () {
		let value = $('#searchvm').val();
		let checkNode = zTree.getCheckedNodes();
		let allNode = zTree.transformToArray(zTree.getNodes());
		nodeParamList = zTree.getNodesByParamFuzzy('name', value);
		if (nodeParamList.length != 0) {
			zTree.hideNodes(allNode);
			$('.copyDataTree').show();
			$('#noSearchTips').hide();
		} else {
			$('.copyDataTree').hide();
			$('#noSearchTips').show();
		}
		//连接搜索的和所勾选的
		nodeParamList = nodeParamList.concat(checkNode);
		let nodeParamList1 = zTree.transformToArray(nodeParamList);
		for (let n in nodeParamList1) {
			findParent(zTree, nodeParamList1[n]);
		}
		zTree.showNodes(nodeParamList);
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

	// 初始化数据表格
	let archiveDataTable = function (treeNode) {
		let storage_uuid = $('#storageSelect').val();
		let node_uuid = $('#copyModuleTypeSelect').attr('value');
		let options = {
			vin_url: "/api/v1/copy/resources/data",
			vin_method: "GET",
			changeHeightBtn: true, //改变高度按钮
			sortName: 'time_point',
			sortOrder: 'desc',
			vin_params: function () {
				let params = { task_uuid: treeNode.task_uuid, db_uuid: treeNode.db_uuid, sub_type: treeNode.sub_type, agent_uuid: treeNode.agent_uuid, data_type: treeNode.data_type, node_uuid: node_uuid, id: treeNode.id, module: treeNode.module, item_uuid: treeNode.item_uuid, data_table_flag: true, archive_flag: true, storage_uuid: storage_uuid ?? '' };
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
					field: 'time_point',
					title: LANG.UI_COPY_TIMEPOINT,
					sortable: true,
					align: 'center',
					formatter: function (value, row, index) {
						let time_point = '<div>';
						time_point += '<span>' + row.time_point + '</span><br>';
						if (row.mark) {
							time_point += row.mark;
						};
						if (row.remark) {
							time_point += row.remark;
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
						let button = '<div class="btn-group dropdown-wrapper">';
						if (index > 5) {
							button = '<div class="btn-group dropup dropdown-wrapper">';
						}

						button += '<button type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" ' +
							'data-hover="dropdown" data-delay="1000" data-close-others="true">' +
							'' + LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' +
							'</button>' +
							'<ul class="dropdown-menu" role="menu" id=' + uuid + '>';
						for (let i = 0; i < value.length; i++) {
							switch (value[i]) {
								case 1:
									button += '<li class="remark"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_modify me-4"></i> ' + LANG.UI_PUBLIC_REMARK + '</button></li>';
									break;
								case 3:
									button += '<li class="setMark"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_sign me-4"></i> ' + LANG.UI_SETTING_VM_DATA_SET_MARK + '</button></li>';
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
			if (forever) {
				starHandler(uuid, 'POST', forever);
			} else {
				starHandler(uuid, 'DELETE', forever);
			}
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
	};

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
	};


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
			initPointShowType();
			initListener();
			initUserPassword();
		}

	};

}();

jQuery(document).ready(function () {
	archiveData.init();
});