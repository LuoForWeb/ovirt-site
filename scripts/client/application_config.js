var ApplicationConfig = function () {
	let zTreeAgent;
	let tidbAgentTree;
	let initInstanceFlag = false;
	let addNewFlag = false;
	let clusterUUID; //集群关联uuid
	let hideEye = true;
	let mongodbMongosTree;

	/**
	 * 加载TIDB旧数据
	 * @param agentUuid
	 */
	const loadTiDBOldData = (agentUuid) => {
		return new Promise(resolve => {
			let reqData = {
				offset: 0,
				limit: 100,
				app_type: CONF.DB_TYPE.TIDB,
			};
			Metronic.blockUI({target: '#appModal', animate: true});
			pAjaxRequest(reqData, `/api/v1/agents/${agentUuid}/applications`, 'GET', res => {
				Metronic.unblockUI('#appModal');
				if (!res.success || !res.data.rows.length) {
					resolve(null);
					return;
				}
				let oldAppInfo = null;
				for (const row of res.data.rows) {
					if (row.cluster_uuid.length) {  // 集群
						if (oldAppInfo === null || !oldAppInfo.cluster_flag) {
							oldAppInfo = {
								cluster_flag: true,
								cluster_uuid: '',
								agent_uuid: agentUuid,
								cluster_agent_uuid_list: row.cluster_app_list.map(item => item.agent_uuid),
							};
							break;
						}
					} else {  // 单机
						if (oldAppInfo !== null) {
							continue;
						}
						oldAppInfo = {
							cluster_flag: false,
							cluster_uuid: '',
							agent_uuid: agentUuid,
							cluster_agent_uuid_list: [],
						};
					}
				}
				resolve(oldAppInfo);
			});
		});
	};

	//添加事件
	const addListeners = function () {
		$('#appconfig').on('click', '#addApp', addApplication)
			.on('click', '#deleteApp', deleteApplication);

		//切换应用类型
		$('#appType').on('change', function () {
			//初始化实例列表
			let dbType = parseInt($(this).val());
			initInstanceFlag = false;
			addNewFlag = false;
			//重置数据
			clusterUUID = "";
			$('#clusterCheck').bootstrapSwitch('disabled', false).bootstrapSwitch('state', false);
			// TiDB有特殊逻辑, 需要指定用户扫描
			switch (dbType) {
				case CONF.DB_TYPE.TIDB:
					$('.tidbClusterDiv').show();
					$('#tidbClusterCheck').bootstrapSwitch('state', false);
					$('.instanceDiv').hide();
					$('#instanceTable').bootstrapTable('destroy');
					// 加载旧数据
					loadTiDBOldData($(`#clientUUID`).val()).then(oldAppInfo => {
						// 初始化TiDB集群选择表
						initTiDBClusterTree(oldAppInfo);
					});
					break;
				default:
					$('.tidbClusterDiv').hide();
					$('.tidbClusterTreeDiv').hide();
					initAuthInstance();
					break;
			}
		});

		//开启Oracle集群关联
		$('#clusterCheck').on('switchChange.bootstrapSwitch', clusterChange);

		//开启配置集群关联主机IP地址
		$('#listenIpCheck').on('switchChange.bootstrapSwitch', listenIpChange);

		//开启mysql配置监听ip
		$('#mysqlListenIpCheck').on('switchChange.bootstrapSwitch', mysqlListenIpChange);
		//开启maria配置监听ip
		$('#mariaListenIpCheck').on('switchChange.bootstrapSwitch', mariaListenIpChange);

		//切换认证方式
		$('#authtype').on('change', authHandler);
		$('#oracleAuthType').on('change', oracleAuthHandler);

		$('#btInstance').on('click', function () {
			//添加标记
			addNewFlag = true;
		});

		//mysql验证方式
		$('#mysqlAuthtype').on('change', mysqlAuthHandler);
		//mariadb验证方式
		$('#mariaAuthtype').on('change', mariaAuthHandler);
		// MySQL端口输入
		$('#port').on('change', function () {
			checkInputPort($(this), $('#mysqlPortDiv'));
		});
		// MariaDB端口输入
		$('#mariaport').on('change', function () {
			checkInputPort($(this), $('#mariadbPortDiv'));
		});
		// MySQL的IP地址验证
		$('#mysqlIp').on('change', function () {
			checkInputIp($(this), $('#mysqlIpDiv'));
		});
		$('#mysqlListenIp').on('change', function () {
			checkInputIp($(this), $('#mysqlListenIpDiv'));
		});
		// MariaDB的IP地址验证
		$('#mariaIp').on('change', function () {
			checkInputIp($(this), $('#mariaIpDiv'));
		});
		$('#mariaListenIp').on('change', function () {
			checkInputIp($(this), $('#mariaListenIpDiv'));
		});
		// TiDB集群关联
		$('#tidbClusterCheck').on('switchChange.bootstrapSwitch', tidbClusterCheckChange);
		// TiDB扫描数据库
		$('#scanTidbInstall').on('click', tidbScanInstance);
		// 集群别名禁止输入 (),/
		$('#clusterAlias').on('input change', function () {
			$(this).val($(this).val().replace(/[(),\/]/g, ''));
		});
	};

	/**
	 * 操作权限校验
	 * @returns {{type: number, source_uuid, source_type: number}|boolean}
	 */
	const checkAuth = function() {
		return {
			type: 2,
			source_uuid: $('#clientUUID').val().trim(),
			source_type: 10,
		};
	};

	//////////////////// 开始-TiDB集群树 ////////////////////

	/**
	 * tidb节点点击了
	 * @param treeId
	 * @param treeNode
	 */
	const tidbNodeSelect = (treeId, treeNode) => {
		if (treeNode.eventtype === 'group') {
			return;
		}
		tidbAgentTree.checkNode(treeNode, !treeNode.checked, true, true);
	};

	/**
	 * tidb节点选择了
	 * @param ev
	 * @param treeId
	 * @param treeNode
	 */
	const tidbNodeCheck = (ev, treeId, treeNode) => {
		$('.instanceDiv').hide();
		$('#instanceTable').bootstrapTable('destroy');
	};

	/**
	 * 节点点击了搜索
	 * @param treeId
	 * @param treeNode
	 * @param prefixId
	 */
	const commonClickInnerSelectBtn = (treeId, treeNode, prefixId) => {
		let searchInputTag = $(`#${prefixId}_searchInput_${treeNode.tId}`);
		let value = searchInputTag.val();
		if (!value) {
			UIToastr.showInfo(LANG.UI_CLIENT_APP_CONFIG_ADD, LANG.UI_PATH_TREE_SELECT_FILE_TIP2);
			return;
		}
		// 隐藏关闭和搜索按钮
		commonInInnerSearch(treeId, treeNode, prefixId);
		// 开始搜素
		let fuzzyNodeList = $.fn.zTree.getZTreeObj(treeId).getNodesByParamFuzzy('name', value, treeNode);
		let hideNodes = [];
		for (const childNode of treeNode.children) {
			if (prefixId === 'mongos_cluster') {  // MongoDB是搜素有多个
				let hideFlag = true;
				if (Array.isArray(childNode.children) && childNode.children.length) {
					for (const subChildNode of childNode.children) {
						if (subChildNode.checked) {
							hideFlag = false;
							break;
						}
					}
				}
				if (hideFlag) {
					hideNodes.push(childNode);
				}
			} else {
				if (!childNode.checked) {
					hideNodes.push(childNode);
				}
			}
		}
		let showNodes = [];
		if (prefixId === 'mongos_cluster') {
			for (const fuzzyNode of fuzzyNodeList) {
				showNodes.push(fuzzyNode);
				if (fuzzyNode.eventtype === 'instance') {
					showNodes.push(fuzzyNode.getParentNode());
				}
			}
		} else {
			showNodes = fuzzyNodeList;
		}
		$.fn.zTree.getZTreeObj(treeId).hideNodes(hideNodes);
		$.fn.zTree.getZTreeObj(treeId).showNodes(showNodes);
		treeNode.search_value = value;
		$.fn.zTree.getZTreeObj(treeId).updateNode(treeNode);
		$.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
		// 隐藏关闭和搜索按钮
		commonOutInnerSearch(treeId, treeNode, prefixId);
	};

	/**
	 * 搜索框开始了
	 * @param treeId
	 * @param treeNode
	 * @param prefixId
	 */
	const commonInInnerSearch = (treeId, treeNode, prefixId) => {
		$(`#${prefixId}_searchInputStopBtn_${treeNode.tId}`).show();
		$(`#${prefixId}_searchInputLoading_${treeNode.tId}`).show();
		$(`#${prefixId}_searchInputSearchBtn_${treeNode.tId}`).hide();
		$(`#${prefixId}_searchInputCloseBtn_${treeNode.tId}`).hide();
		$(`#${prefixId}_searchInput_${treeNode.tId}`).attr('readonly', 'readonly').attr('title', LANG.UI_PATH_TREE_SEARCH_RUNNING);
		$(`#${prefixId}_searchError_${treeNode.tId}`).html('');
	};

	/**
	 * 搜索框取消了
	 * @param treeId
	 * @param treeNode
	 * @param prefixId
	 */
	const commonOutInnerSearch = (treeId, treeNode, prefixId) => {
		$(`#${prefixId}_searchInputStopBtn_${treeNode.tId}`).hide();
		$(`#${prefixId}_searchInputLoading_${treeNode.tId}`).hide();
		$(`#${prefixId}_searchInputSearchBtn_${treeNode.tId}`).show();
		$(`#${prefixId}_searchInputCloseBtn_${treeNode.tId}`).show();
		$(`#${prefixId}_searchInput_${treeNode.tId}`).removeAttr('readonly').removeAttr('title');
	};

	/**
	 * 点击了鼠标悬停的搜索按钮
	 * @param treeId
	 * @param treeNode
	 * @param sObj
	 * @param prefixId
	 */
	const commonClickSearchHoverBtn = (treeId, treeNode, sObj, prefixId) => {
		// 移除悬停按钮
		commonRemoveHoverDom(treeId, treeNode, prefixId);
		if (treeNode.search_value === undefined) {
			treeNode.search_value = '';
			$.fn.zTree.getZTreeObj(treeId).updateNode(treeNode);
		}
		// 移除其他搜索框
		// $(`div.${prefixId}_searchInputDivCls, span.${prefixId}_searchErrorCls`).off().remove();
		let lastSearch = treeNode.search_value ? treeNode.search_value : '';
		let searchInput = `
            <div style="display: inline-block;" class="form-group ${prefixId}_searchInputDivCls" id="${prefixId}_searchInputDiv_${treeNode.tId}">
                <div style="display: flex; margin-top: 1px">
                    <input id="${prefixId}_searchInput_${treeNode.tId}" spellcheck="false" class="form-control" style="height: 21px; padding: 0 10px"
                        value="${lastSearch}" autocomplete="off" />
                    <span class="input-group-btn" style="width: 42px; margin-left: 4px; display: flex; margin-top: 2px">
                        <div class="btn btn-success" style="position: relative; top: -2px; width: 21px; height: 21px;"
                            id="${prefixId}_searchInputSearchBtn_${treeNode.tId}" title="${LANG.UI_PATH_TREE_SEARCH_START}">
                            <i class="fa fa-search" style="font-family: FontAwesome,serif; padding-top: 3px"></i>
                        </div>
                        <div class="btn btn-danger" style="position: relative; top: -2px; width: 21px; height: 21px;"
                            id="${prefixId}_searchInputCloseBtn_${treeNode.tId}" title="${LANG.UI_PATH_TREE_SEARCH_EXIT}">
                            <i class="fa fa-close" style="font-family: FontAwesome,serif; padding-top: 3px"></i>
                        </div>
                        <div class="ispinner display-none" id="${prefixId}_searchInputLoading_${treeNode.tId}"
                            title="${LANG.UI_PATH_TREE_SEARCH_RUNNING}">
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                            <div class="ispinner-blade"></div>
                        </div>
                        <div class="btn btn-danger display-none" style="position: relative; top: -2px; width: 21px; height: 21px;"
                            id="${prefixId}_searchInputStopBtn_${treeNode.tId}" title="${LANG.UI_PATH_TREE_SEARCH_STOP}">
                            <i class="fa fa-stop" style="font-family: FontAwesome,serif; padding-top: 3px"></i>
                        </div>
                    </span>
                </div>
                <span id="${prefixId}_searchInputWidth_${treeNode.tId}" class="display-none"></span>
            </div>`
		let searchError = `<span id="${prefixId}_searchError_${treeNode.tId}" class="${prefixId}_searchErrorCls" style="color: red"></span>`;
		sObj.after(searchError);
		sObj.after(searchInput);
		$(`#${prefixId}_searchInputDiv_${treeNode.tId}`).on('click', ev => {
			ev.preventDefault();
			return false;
		});
		$(`#${prefixId}_searchInput_${treeNode.tId}`).on('click', () => {
			$(`#${prefixId}_searchInput_${treeNode.tId}`).focus();
		}).on('input change', function () {
			// 获取输入框中文本内容的实际宽度
			let inputWidth = $(`#${prefixId}_searchInputWidth_${treeNode.tId}`).html($(this).val()).width();

			if (inputWidth > $(this).width()) {
				if (inputWidth > 300) {  // 最大值300px
					return;
				}
				$(this).css('width', inputWidth + 'px');
			} else if (inputWidth < $(this).width()) {
				$(this).css('width', ''); // 清除宽度样式，使其回到默认宽度

				if (inputWidth > $(this).width()) {
					$(this).css('width', inputWidth + 'px');
				}
			}
		}).focus().keydown(ev => {
			if (ev.key === 'Enter' || ev.keyCode === 13) {
				commonClickInnerSelectBtn(treeId, treeNode, prefixId);
			}
		});
		$(`#${prefixId}_searchInputSearchBtn_${treeNode.tId}`).on('click', () => {
			commonClickInnerSelectBtn(treeId, treeNode, prefixId);
		});
		$(`#${prefixId}_searchInputCloseBtn_${treeNode.tId}`).on('click', () => {
			$(`#${prefixId}_searchInputDiv_${treeNode.tId}`).off().remove();
			$(`#${prefixId}_searchError_${treeNode.tId}`).off().remove();
			$.fn.zTree.getZTreeObj(treeId).showNodes(treeNode.children);
		});
		$(`#${prefixId}_searchInputStopBtn_${treeNode.tId}`).on('click', () => {
			commonOutInnerSearch(treeId, treeNode, prefixId);
		});
	};

	/**
	 * 添加搜索按钮
	 * @param treeId
	 * @param treeNode
	 * @param prefixId
	 * */
	const commonAddHoverDom = (treeId, treeNode, prefixId) => {
		if (treeNode.eventtype !== 'group') {
			return false;
		}
		let searchInputDivTag = `#${prefixId}_searchInputDiv_${treeNode.tId}`;
		if ($(searchInputDivTag).length) {  // 处于搜索状态
			return false;
		}
		let searchStr = `
		<span id="${prefixId}_searchBtn_${treeNode.tId}"
			style="width: 18px;height: 18px; color: #8c8c8c; background-color: #f5f5f5;
				display: inline-block; margin-left: 2px; position: relative; top: 2px">
			<i class="fa fa-search" style="font-family: FontAwesome,serif; position: relative; top: -2px;"></i>
		</span>
		`;
		let sObj = $(`#${treeNode.tId}_span`);
		let searchBtnTag = `#${prefixId}_searchBtn_${treeNode.tId}`;
		if ($(searchBtnTag).length > 0) {
			return;
		}
		sObj.after(searchStr);
		$(searchBtnTag).on('click', () => {
			commonClickSearchHoverBtn(treeId, treeNode, sObj, prefixId);
		}).hover(() => {
			$(searchBtnTag).css({color: '#0fbf98', 'background-color': '#e7f7f3'});
		}, () => {
			$(searchBtnTag).css({color: '#8c8c8c', 'background-color': '#f5f5f5'});
		});
	};

	/**
	 * 移除搜索按钮
	 * @param treeId
	 * @param treeNode
	 * @param prefixId
	 */
	const commonRemoveHoverDom = (treeId, treeNode, prefixId) => {
		$(`#${prefixId}_searchBtn_${treeNode.tId}`).off().remove();
	};

	/**
	 * 获取TIDB集群树的配置
	 * @return {Object}
	 */
	const getTiDBClusterTreeSetting = () => {
		return {
			check: {
				enable: true,
				nocheckInherit: false
			},
			data: {
				simpleData: {
					enable: true,
					idKey: 'id',
					pIdKey: 'pId',
					rootPId: 0
				},
				key: {
					title: 'title',
				}
			},
			callback: {
				beforeClick: tidbNodeSelect,
				onCheck: tidbNodeCheck,
			},
			view: {
				showTitle: true,
				nameIsHTML: true,
				addHoverDom: (treeId, treeNode) => {
					commonAddHoverDom(treeId, treeNode, 'tidb_cluster');
				},
				removeHoverDom: (treeId, treeNode) => {
					commonRemoveHoverDom(treeId, treeNode, 'tidb_cluster');
				},
			}
		};
	};

	/**
	 * 初始化TiDB集群选择树
	 */
	const initTiDBClusterTree = (oldAppInfo) => {
		/**
		 * 1. 获取在线的客户端信息
		 */
		let reqData = {
			h_online_status: 1,
			offset: 0,
			limit: 100,
			offset_flag: false,
		};
		let agentUuid = $('#clientUUID').val();
		let tidbAgentTreeTag = $('#tidbAgentTree');
		let dbType = CONF.DB_TYPE.TIDB;
		Metronic.blockUI({target: '#tidbAgentTree', animate: true});
		pAjaxRequest(reqData, `/api/v1/agents`, 'GET', res => {
			Metronic.unblockUI('#tidbAgentTree');
			if (!res.success) {
				$('#tidbNoAgent').show();
				tidbAgentTreeTag.hide();
				return;
			}
			if (!res.data.rows.length) {
				$('#tidbNoAgent').show();
				tidbAgentTreeTag.hide();
				return;
			}
			let nodes = {};

			for (const row of res.data.rows) {
				if (typeof nodes[row.group_uuid] === 'undefined') {
					let nocheck = false;
					nodes[row.group_uuid] = {
						id: row.group_uuid,
						pId: '',
						name: row.group_name,
						title: row.group_name,
						group_name: row.group_name,
						group_uuid: row.group_uuid,
						isParent: true,
						open: true,
						nocheck,
						icon: './img/platform/flag.png',
						eventtype: 'group',
					};
				}

				if (agentUuid === row.agent_uuid) {
					continue;
				}
				let name = row.alias + '(' + row.agent_ip + ')';
				let checked = false;
				let nocheck = false;
				let isParent = false;
				let initLoadInstanceFlag = false;
				if (row.agent_ip !== row.alias) {
					name = row.hostname + '(' + row.alias + ')';
				}
				for (const appInfo of row.app_list) {
					if (dbType === appInfo.app_type && appInfo.cluster_flag && appInfo.cluster_uuid === clusterUUID) {
						checked = true;
						initLoadInstanceFlag = true;
					}
				}
				nodes[row.group_uuid + '-' + row.agent_uuid] = {
					id: row.group_uuid + '-' + row.agent_uuid,
					pId: row.group_uuid,
					name,
					title: row.agent_ip,
					group_name: row.group_name,
					group_uuid: row.group_uuid,
					agent_uuid: row.agent_uuid,
					agent_ip: row.agent_ip,
					listen_ip: row.agent_ip,
					hostname: row.hostname,
					alias: row.alias,
					isParent,
					open: false,
					checked,
					nocheck,
					chkDisabled: checked,
					icon: './img/vm/host.png',
					eventtype: 'agent',
					init_load_instance_flag: initLoadInstanceFlag,  // 初始化就展开实例，用于编辑已认证的实例
					load_instance_flag: false,
				};
				if (checked) {
					addListenIp(nodes[row.group_uuid + '-' + row.agent_uuid]);
				}
			}

			tidbAgentTree = $.fn.zTree.init(tidbAgentTreeTag, getTiDBClusterTreeSetting(), Object.values(nodes));
			if (oldAppInfo !== null) {
				if (!oldAppInfo.cluster_flag) {
					$('#scanTidbInstall').trigger('click');
				} else {
					$('#tidbClusterCheck').bootstrapSwitch('state', true).trigger('switchChange.bootstrapSwitch');
					for (const clusterAgentUuid of oldAppInfo.cluster_agent_uuid_list) {
						let treeNode = tidbAgentTree.getNodeByParam('agent_uuid', clusterAgentUuid);
						if (treeNode !== null) {
							tidbAgentTree.checkNode(treeNode, true, true, true);
						}
					}
					$('#scanTidbInstall').trigger('click');
				}
			}
		});
	};

	//////////////////// 结束-TiDB集群树 ////////////////////

	/**
	 * tidb扫描数据库
	 */
	const tidbScanInstance = () => {
		let tidbClusterCheck = $('#tidbClusterCheck').get(0).checked;
		if (tidbClusterCheck) {
			let treeNodes = tidbAgentTree.getCheckedNodes(true);
			let checkAgentFlag = false;
			for (const treeNode of treeNodes) {
				if (treeNode.eventtype === 'agent') {
					checkAgentFlag = true;
				}
			}
			if (!checkAgentFlag) {
				UIToastr.showWarning(LANG.UI_CLIENT_APP_TIDB_SCAN, LANG.UI_CLIENT_APP_TIDB_SCAN_CLUSTER_EMPTY);
				return;
			}
		}
		initAuthInstance();
	};

	/**
	 * TiDB集群选择按钮改变了
	 */
	const tidbClusterCheckChange = function () {
		$('.instanceDiv').hide();
		$('#instanceTable').bootstrapTable('destroy');
		if (this.checked) {
			$('.tidbClusterTreeDiv').show();
		} else {
			$('.tidbClusterTreeDiv').hide();
		}
	};

	//开启mysql配置监听ip
	const mysqlListenIpChange = function () {
		if (this.checked) {
			$('.mysqlIpListDiv').show();
		} else {
			$('.mysqlIpListDiv').hide();
		}
	};

	const mariaListenIpChange = function () {
		if (this.checked) {
			$('.mariaIpListDiv').show();
		} else {
			$('.mariaIpListDiv').hide();
		}
	};

	//开启配置集群关联IP
	const listenIpChange = function () {
		if (this.checked) {
			$('.ipListDiv').show();
		} else {
			$('.ipListDiv').hide();
		}
	};

	const authHandler = function () {
		$('.userDiv').show();
		if (parseInt(this.value) === 2) {
			$('.osAuthDiv').hide();
			$('.instanceAuthDiv').show();
		} else {
			$('.osAuthDiv').show();
			$('.instanceAuthDiv').hide();
		}
	};

	const oracleAuthHandler = function () {
		if (1 === parseInt(this.value)) {  // 操作系统认证
			$('.osAuthDiv').show();
			$('.instanceAuthDiv').hide();
			// 隐藏集群和监听IP
			$('.clusterDiv').hide();
			$('.clusterTreeDiv').hide();
			$('.listenIpDiv').hide();
			$('.clusterAliasDiv').hide();
		} else {
			$('.osAuthDiv').hide();
			$('.instanceAuthDiv').show();
			$('.clusterDiv').show();
			$('.listenIpDiv').show();
			if ($('#clusterCheck').get(0).checked) {
				$('.clusterTreeDiv').show();
				$('.clusterAliasDiv').show();
			}
		}
	};

	const mysqlAuthHandler = function () {
		if (parseInt(this.value) === 2) {
			$('.tcpipDiv').hide();
			$('.sockDiv').show();
		} else {
			$('.tcpipDiv').show();
			$('.sockDiv').hide();
		}
	};

	const mariaAuthHandler = function () {
		if (parseInt(this.value) === 2) {
			$('.mariaTcpipDiv').hide();
			$('.mariaSockDiv').show();
		} else {
			$('.mariaTcpipDiv').show();
			$('.mariaSockDiv').hide();
		}
	};

	const clusterChange = function(){
		let dbType = parseInt($('#appType').val());
		if(this.checked){
			$('.clusterAliasDiv label span.required').show();
			//sqlserver支持用于连接数据库的集群IP配置
			switch (dbType) {
				case CONF.DB_TYPE.SQLSERVER:
					$('.clusterAliasDiv').show();
					$('.clusterIpDiv').show();
					break;
				case CONF.DB_TYPE.ORACLE:
					$('.clusterAliasDiv').show();
					// $('.clusterAliasDiv label span.required').hide();
					// $('.clusterServiceIpDiv').show();
					break;
				case CONF.DB_TYPE.MONGODB:
					$('.mongodbMongosTreeDiv').show();
					$('.clusterAliasDiv').show();
					break;
				case CONF.DB_TYPE.SAPHANA:
					$('.clusterServiceIpDiv').show();
					$('.clusterAliasDiv').show();
					break;
				case CONF.DB_TYPE.POSTGRE:
				case CONF.DB_TYPE.HIGHGO:
				case CONF.DB_TYPE.OPENGAUSS:
				case CONF.DB_TYPE.VASTBASE:
				case CONF.DB_TYPE.ANTDB:
				case CONF.DB_TYPE.HIGHGO:
				case CONF.DB_TYPE.KINGBASE:
					$('.clusterAliasDiv').show();
					break;
				case CONF.DB_TYPE.DM:
					$('.clusterAliasDiv').show();
					break;
			}
			$('.clusterTreeDiv').show();
			if (zTreeAgent) {
				// 已勾选主机
				let checkNodes = zTreeAgent.getCheckedNodes(true);  // 勾选的节点
				for (const checkNode of checkNodes) {
					if (checkNode.eventtype === 'agent') {
						addListenIp(checkNode);
					} else if (checkNode.eventtype === 'instance') {
						if (
							dbType === CONF.DB_TYPE.DM ||
							dbType === CONF.DB_TYPE.KINGBASE ||
							dbType === CONF.DB_TYPE.ORACLE
						) {
							let parentNode = checkNode.getParentNode();
							addListenIp(parentNode);
						}
					}
				}
			}
		} else {
			$('.clusterIpDiv').hide();
			$('.clusterTreeDiv').hide();
			$('.clusterAliasDiv').hide();
			$('.clusterServiceIpDiv').hide();
			$('.mongodbMongosTreeDiv').hide();
			if ($('#listenIp').length) {  // 表示有监听IP
				let ip = $('#listenIp').val();
				let name = $('#clientName').html();
				$('#oracleIpList').html('');
				initClientListenIp(name, ip);
			}
		}
	};

	//添加客户端应用
	const addApplication = function () {
		checkOperateAuth(checkAuth(), () => {
			doAddApplication();
		});
	};

	/**
	 * 执行添加应用
	 */
	const doAddApplication = () => {
		$('#appType').val(0).prop("disabled", false).trigger('change');
		$('.instanceDiv').hide();
		$('.addAppDiv').show();
		$('.editAppDiv').hide();
		if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
			$('#appModal').modal({
				'width': '1000px',
				'height': '400px'
			}).bootstrapWizard('first');
		}
		else {
			$('#appModal').modal({
				'width': '1000px',
				'height': '400px'
			}).bootstrapWizard('first');
		}
		// if (!hideEye) {
		registerPasswordEye();
		// } else {
		// 	$('.sys-password-icon').hide();
		// }
	};

	/**
	 * 注册密码的眼睛事件
	 */
	const registerPasswordEye = () => {
		// MySQL密码的眼睛
		$('#mysqlpassword').attr('type', 'password');
		$('#mysqlPasswordEyeOn').off().on('click', () => {
			$('#mysqlPasswordEyeOn').hide();
			$('#mysqlPasswordEyeOff').show();
			$('#mysqlpassword').attr('type', 'password');
		}).hide();
		$('#mysqlPasswordEyeOff').off().on('click', () => {
			$('#mysqlPasswordEyeOn').show();
			$('#mysqlPasswordEyeOff').hide();
			$('#mysqlpassword').attr('type', 'text');
		}).show();

		// MariaDB密码的眼睛
		$('#mariapassword').attr('type', 'password');
		$('#mariaPasswordEyeOn').off().on('click', () => {
			$('#mariaPasswordEyeOn').hide();
			$('#mariaPasswordEyeOff').show();
			$('#mariapassword').attr('type', 'password');
		}).hide();
		$('#mariaPasswordEyeOff').off().on('click', () => {
			$('#mariaPasswordEyeOn').show();
			$('#mariaPasswordEyeOff').hide();
			$('#mariapassword').attr('type', 'text');
		}).show();

		// sapHana密码的眼睛
		$('#sapHanaPassword').attr('type', 'password');
		$('#sapHanaPasswordEyeOn').off().on('click', () => {
			$('#sapHanaPasswordEyeOn').hide();
			$('#sapHanaPasswordEyeOff').show();
			$('#sapHanaPassword').attr('type', 'password');
		}).hide();
		$('#sapHanaPasswordEyeOff').off().on('click', () => {
			$('#sapHanaPasswordEyeOn').show();
			$('#sapHanaPasswordEyeOff').hide();
			$('#sapHanaPassword').attr('type', 'text');
		}).show();

		// IRIS密码的眼睛
		$('#IRISPassword').attr('type', 'password');
		$('#IRISPasswordEyeOn').off().on('click', () => {
			$('#IRISPasswordEyeOn').hide();
			$('#IRISPasswordEyeOff').show();
			$('#IRISPassword').attr('type', 'password');
		}).hide();
		$('#IRISPasswordEyeOff').off().on('click', () => {
			$('#IRISPasswordEyeOn').show();
			$('#IRISPasswordEyeOff').hide();
			$('#IRISPassword').attr('type', 'text');
		}).show();

		// tidb密码的眼睛
		$('#tidbPassword').attr('type', 'password');
		$('#tidbPasswordEyeOn').off().on('click', () => {
			$('#tidbPasswordEyeOn').hide();
			$('#tidbPasswordEyeOff').show();
			$('#tidbPassword').attr('type', 'password');
		}).hide();
		$('#tidbPasswordEyeOff').off().on('click', () => {
			$('#tidbPasswordEyeOn').show();
			$('#tidbPasswordEyeOff').hide();
			$('#tidbPassword').attr('type', 'text');
		}).show();

		// mongodb密码的眼睛
		$('#mongodbPassword').attr('type', 'password');
		$('#mongodbPasswordEyeOn').off().on('click', () => {
			$('#mongodbPasswordEyeOn').hide();
			$('#mongodbPasswordEyeOff').show();
			$('#mongodbPassword').attr('type', 'password');
		}).hide();
		$('#mongodbPasswordEyeOff').off().on('click', () => {
			$('#mongodbPasswordEyeOn').show();
			$('#mongodbPasswordEyeOff').hide();
			$('#mongodbPassword').attr('type', 'text');
		}).show();

		// Oracle操作系统认证密码的眼睛
		$('#osAuthPassword').attr('type', 'password');
		$('#osAuthPasswordEyeOn').off().on('click', () => {
			$('#osAuthPasswordEyeOn').hide();
			$('#osAuthPasswordEyeOff').show();
			$('#osAuthPassword').attr('type', 'password');
		}).hide();
		$('#osAuthPasswordEyeOff').off().on('click', () => {
			$('#osAuthPasswordEyeOn').show();
			$('#osAuthPasswordEyeOff').hide();
			$('#osAuthPassword').attr('type', 'text');
		}).show();

		// 通用密码的眼睛
		$('#password').attr('type', 'password');
		$('#passwordEyeOn').off().on('click', () => {
			$('#passwordEyeOn').hide();
			$('#passwordEyeOff').show();
			$('#password').attr('type', 'password');
		}).hide();
		$('#passwordEyeOff').off().on('click', () => {
			$('#passwordEyeOn').show();
			$('#passwordEyeOff').hide();
			$('#password').attr('type', 'text');
		}).show();
	};

	//删除客户端应用
	const deleteApplication = () => {
		checkOperateAuth(checkAuth(), () => {
			doDeleteApplication();
		});
	};

	/**
	 * 执行删除客户端应用
	 */
	const doDeleteApplication = () => {
		let rows = $('#applicationTable').bootstrapTable('getSelections');
		if (!rows.length) {
			UIToastr.showInfo(LANG.UI_CLIENT_APP_CONFIG_DELETE, LANG.UI_CLIENT_APP_CONFIG_DELETE_SELECT_TIPS);
			return;
		}
		//初始化删除提示框
		bootbox.confirm({
			title: LANG.UI_CLIENT_APP_CONFIG_DELETE,
			message: LANG.UI_CLIENT_APP_CONFIG_DELETE_TIPS,
			callback: debounce(function (r) {
				if (!r) return;
				submitDeleteApp(rows.map(row => row.app_uuid));
			}, 300)
		});
	};

	const submitDeleteApp = (appUuidList) => {
		let reqData = {
			app_uuid_list: appUuidList
		};

		Metronic.blockUI({target: '#appconfig', animate: true});
		pAjaxRequest(reqData, `/api/v1/agents/${$('#clientUUID').val()}/applications`, 'DELETE', res => {
			Metronic.unblockUI('#appconfig');
			if (!res.success) {
				UIToastr.showWarning(LANG.UI_CLIENT_APP_CONFIG_DELETE, res.message);
				return;
			}
			UIToastr.showSuccess(LANG.UI_CLIENT_APP_CONFIG_DELETE, res.message);
			$('#applicationTable').bootstrapTable('refresh');
		});
	};

	//创建数据验证步骤
	const wizardInit = function () {
		if (!jQuery().bootstrapWizard) {
			return;
		}
		let appModalTag = $('#appModal');
		let form = $('#submit_form');
		let error = $('.alert-danger', form);
		let success = $('.alert-success', form);
		let handleTitle = function (tab, navigation, index) {
			let total = navigation.find('li').length; //总共的步骤数
			let current = index + 1; //当前步骤
			// set wizard title
			//            $('.step-title', $('#vmbackupcontent')).text('Step ' + (index + 1) + ' of ' + total);
			// set done steps
			jQuery('li', appModalTag).removeClass("done");
			let li_list = navigation.find('li');
			for (let i = 0; i < index; i++) {
				jQuery(li_list[i]).addClass("done");
			}

			//如果第一步 上一步按钮隐藏
			if (current === 1) {
				appModalTag.find('.button-previous').hide();
				appModalTag.find('.button-next').addClass('next-btn-margin-left');
			} else {
				appModalTag.find('.button-previous').show();
				appModalTag.find('.button-next').removeClass('next-btn-margin-left');
			}

			//如果是最后一步
			if (current >= total) {
				appModalTag.find('.button-next').hide();
				appModalTag.find('.button-submit').show();
			} else {
				appModalTag.find('.button-next').show();
				appModalTag.find('.button-submit').hide();
			}


			Metronic.scrollTo($('.page-title'));
		}

		// default form wizard
		appModalTag.bootstrapWizard({
			'nextSelector': '.button-next,#btInstance',
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
				}
				handleTitle(tab, navigation, index);
			},

			//上一步
			onPrevious: function (tab, navigation, index) {
				success.hide();
				error.hide();
				addNewFlag = false;
				handleTitle(tab, navigation, index);
			},

			//进度条显示
			onTabShow: function (tab, navigation, index) {
				let total = navigation.find('li').length;
				let current = index + 1;
				let $percent = (current / total) * 100;
				appModalTag.find('.progress-bar').css({
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

		appModalTag.find('.button-previous').hide();
		$('#appModal .button-submit').click(() => {
			showOracleOsAuthDialog().then(submit).catch(submit);
		}).hide();
	};

	/**
	 * 显示Oracle操作系统认证的提示弹窗
	 * @return {Promise<unknown>}
	 */
	const showOracleOsAuthDialog = function () {
		return new Promise(resolve => {
			let dbType = parseInt($('#appType').val());
			let authType = parseInt($('#oracleAuthType').val());
			if (dbType !== CONF.DB_TYPE.ORACLE || 1 !== authType) {
				resolve();
				return;
			}
			bootbox.dialog({
				title: LANG.UI_DB_INSTANCE_AUTH,
				message: LANG.UI_DB_INSTANCE_ORACLE_OS_AUTH_TIPS,
				buttons: {
					cancel: {
						label: LANG.UI_PUBLIC_CANCEL,
						className: 'btn-default',
						callback: function () {
						}
					},
					ok: {
						label: LANG.UI_PUBLIC_CONFIRM,
						className: 'btn-primary',
						callback: debounce(resolve, 300),
					}
				}
			});
		});
	};

	//第一步
	const step1Valid = function () {
		let dbType = parseInt($('#appType').val());
		if (!dbType) {
			UIToastr.showInfo(LANG.UI_CLIENT_APP_CONFIG_ADD, LANG.UI_CLIENT_APP_CONFIG_APP_TYPE);
			return false;
		}
		let rows = $('#instanceTable').bootstrapTable('getSelections');
		if (!rows.length && (dbType === CONF.DB_TYPE.MYSQL || dbType === CONF.DB_TYPE.MARIA || (dbType === CONF.DB_TYPE.ORACLE && addNewFlag))) {
			initOldAuthInfo();
			return true;
		}
		if (!rows.length) {
			UIToastr.showInfo(LANG.UI_CLIENT_APP_CONFIG_ADD, LANG.UI_CLIENT_APP_CONFIG_SELECT_INSTANCE);
			return false;
		}
		switch (dbType) {
			case CONF.DB_TYPE.TIDB:
				if (!$('#instanceTable').html().trim()) {
					UIToastr.showInfo(LANG.UI_CLIENT_APP_CONFIG_ADD, LANG.UI_CLIENT_APP_TIDB_SCAN_INSTANCE);
					return false;
				}
				break;
		}
		$('.otherDiv').show();
		initOldAuthInfo(rows[0]);
		return true;
	};

	/**
	 * 验证输入的数字是否有效，并给出提示
	 * @param {object} inputObj 	输入标签的jquery对象
	 * @param {object} formGroupObj 输入标签的含有form-group的父标签的jquery对象，用于添加has-error错误提示的样式
	 */
	const checkInputPort = function (inputObj, formGroupObj) {
		if (isPort(inputObj.val())) {
			unsetInputValidate(inputObj, formGroupObj);
		} else {
			setInputValidate(inputObj, formGroupObj, LANG.UI_DB_INSTANCE_PORT_INVALID);
		}
	};

	/**
	 * 验证端口合法性
	 * @param {string} value
	 * @returns {boolean}
	 */
	const isPort = function (value) {
		let port = parseInt(value);
		if (port.toString().length !== value.length) {
			// parseInt会将0xx转为0
			return false;
		}
		if (isNaN(port)) {
			return false;
		}

		if (port < 0 || port > 65535) {
			return false;
		}
		// 判断是否为-0. 在js里面 0 === -0, 但是1/0是Infinity, 1/-0是-Infinity. Infinity > 0, -Infinity < 0
		return 1 / port >= 0;

	};

	/**
	 * 验证输入的IP地址是否有效，并给出提示
	 * @param {object} inputObj 输入标签的jquery对象
	 * @param {object} formGroupObj 输入标签的含有form-group的父标签的jquery对象，用于添加has-error错误提示的样式
	 */
	const checkInputIp = function (inputObj, formGroupObj) {
		if (ipV4V6(inputObj.val())) {
			unsetInputValidate(inputObj, formGroupObj);
		} else {
			setInputValidate(inputObj, formGroupObj, LANG.UI_DB_INSTANCE_IP_INVALID);
		}
	};

	/**
	 * 设置输入框验证
	 * @param {object} inputObj
	 * @param {object} formGroupObj
	 * @param {string} message
	 */
	const setInputValidate = function (inputObj, formGroupObj, message) {
		formGroupObj.addClass('has-error');
		inputObj.parent().find('i.fa-warning').show();
		inputObj.parent().find('i.fa-warning').attr('data-original-title', message);
		inputObj.parent().find('i.fa-warning').tooltip({'container': 'body'});
	};

	/**
	 * 移除输入框验证
	 * @param {object} inputObj
	 * @param {object} formGroupObj
	 */
	const unsetInputValidate = function (inputObj, formGroupObj) {
		formGroupObj.removeClass('has-error');
		inputObj.parent().find('i.fa-warning').hide();
	};

	//////////////////// 开始-实例认证 ////////////////////

	/**
	 * 实例认证公共函数
	 * @param reqData
	 */
	const authInstance = (reqData) => {
		Metronic.blockUI({target: '#appModal', animate: true});
		pAjaxRequest(reqData, `/api/v1/agents/${$('#clientUUID').val()}/applications`, 'POST', res => {
			Metronic.unblockUI('#appModal');
			if (!res.success) {
				UIToastr.showWarning(LANG.UI_CLIENT_APP_ADD, res.message);
				return;
			}
			UIToastr.showSuccess(LANG.UI_CLIENT_APP_ADD, res.message);
			$('#appModal').modal('hide');
			$('#applicationTable').bootstrapTable('refresh');
		});
	};

	/**
	 * 认证SQL Server实例
	 * @param row
	 */
	const authSqlServerInstance = row => {
		let authType = parseInt($('#authtype').val());
		let clusterFlag = !!$('#clusterCheck').get(0).checked;
		let clusterIp = $('#clusterIp').val();
		let reqData = {
			db_type: CONF.DB_TYPE.SQLSERVER,
			instance_name: row.instance_name,
			listen_ip: clusterIp,
			sql_server: {
				auth_type: authType,
				user: {
					username: authType === 2 ? $('#authname').val() : $('#osAuthName').val(),
					password: authType === 2 ? btoa($('#password').val()) : btoa($('#osAuthPassword').val()),
				},
				is_cluster: clusterFlag,
				cluster_name: clusterFlag ? $('#clusterAlias').val() : '',
				cluster_ip: clusterFlag ? clusterIp : '',
				cluster_info: [],
			},
		};
		// 验证
		if (!reqData.sql_server.user.username || !reqData.sql_server.user.password) {
			return UIToastr.showInfo(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
		}
		if (clusterFlag) {
			// 集群别名
			if (!$.trim(reqData.sql_server.cluster_name)) {
				UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_VERIFY_NO_CLUSTER_ALIAS);
				return false;
			}
			//集群IP检查
			if (!reqData.sql_server.cluster_ip || !ipV4V6(reqData.sql_server.cluster_ip)) {
				UIToastr.showInfo(LANG.UI_VERIFY_SET_IP_ADDRESS, LANG.UI_VERIFY_INPUT_CORRECT_IP_ADDRESS);
				return false;
			}
			reqData.sql_server.cluster_info = getClusterInfo(reqData.db_type);
			if (reqData.sql_server.cluster_info === false) {
				return false;
			}
		}
		authInstance(reqData);
	};

	/**
	 * 认证Oracle实例
	 * @returns {boolean|void}
	 */
	const authOracleInstance = () => {
		let authType = parseInt($('#oracleAuthType').val());
		let clusterFlag = !!$('#clusterCheck').get(0).checked;
		let listenIpFlag = !!$('#listenIpCheck').get(0).checked;
		let reqData = {
			db_type: CONF.DB_TYPE.ORACLE,
			instance_name: $('#instancename').val(),
			listen_ip: listenIpFlag ? $('#listenIp').val() : $('#clientIp').html(),
			oracle: {
				auth_type: authType,
				username: authType === 2 ? $('#authname').val() : $('#osAuthName').val(),
				password: authType === 2 ? btoa($('#password').val()) : btoa($('#osAuthPassword').val()),
				install_db_username: $('#installname').val(),
				is_cluster: clusterFlag,
				cluster_name: clusterFlag && 2 === authType ? $('#clusterAlias').val() : '',
				cluster_service_ip: '',
				cluster_info: [],
			},
		};

		// 验证
		if (!reqData.instance_name) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_NAME);
		}
		if (!reqData.oracle.username || !reqData.oracle.password) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
		}
		//监听IP检查
		if (reqData.listen_ip && !ipV4V6(reqData.listen_ip)) {
			UIToastr.showInfo(LANG.UI_VERIFY_SET_IP_ADDRESS, LANG.UI_VERIFY_INPUT_CORRECT_IP_ADDRESS);
			return false;
		}

		// 操作系统认证不能认证集群
		if (clusterFlag && 1 !== authType) {
			// 集群别名
			if (!$.trim(reqData.oracle.cluster_name)) {
				UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_VERIFY_NO_CLUSTER_ALIAS);
				return false;
			}
			// 集群服务IP
			// if (!$.trim(reqData.oracle.cluster_service_ip) && !ipV4V6(reqData.oracle.cluster_service_ip)) {
			// 	UIToastr.showWarning(LANG.UI_VERIFY_SET_CLUSTER_SERVICE_IP, LANG.UI_VERIFY_INPUT_CORRECT_IP_ADDRESS);
			// 	return false;
			// }
			reqData.oracle.cluster_info = getClusterInfo(reqData.db_type);
			if (reqData.oracle.cluster_info === false) {
				return false;
			}
		}
		authInstance(reqData);
	};

	/**
	 * 认证MySQL实例
	 * @returns {boolean|void}
	 */
	const authMySQLInstance = () => {
		let authType = parseInt($('#mysqlAuthtype').val());
		let port = $('#port').val();
		let reqData = {
			db_type: CONF.DB_TYPE.MYSQL,
			instance_name: `127.0.0.1:${port}`,
			listen_ip: $('#mysqlListenIp').val(),
			mysql: {
				config_path: $.fn.PathTreeSelector.getCheckPath('cnfPath'),
				auth_type: authType,
				username: $('#mysqlname').val().replace(/\s/g, ''),
				password: btoa($('#mysqlpassword').val()),
				tcp: {
					ip: $('#mysqlIp').val(),
					port: port,
				},
				sock: {
					host: $('#mysqlHost').val(),
					sock_path: $.fn.PathTreeSelector.getCheckPath('sockPath'),
				},
			},
		};

		// 验证
		if (1 === authType) {
			// 验证ip地址
			if (!ipV4V6(reqData.mysql.tcp.ip)) {
				return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_IP_INVALID);
			}
			// 验证端口号
			if (!isPort(reqData.mysql.tcp.port)) {
				return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_PORT_INVALID);
			}
			reqData.mysql.tcp.port = parseInt(reqData.mysql.tcp.port);
		}
		// 用户名和密码
		if (!reqData.mysql.username || !reqData.mysql.password) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
		}
		if (
			!reqData.mysql.config_path ||
			(!reqData.mysql.tcp.port && authType === 1) || (!reqData.mysql.sock.sock_path && authType === 2)
		) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_INPUT_CONFIG_INFO);
		}
		// 实例监听IP验证
		if ($('#mysqlListenIpCheck').is(':checked') && !ipV4V6(reqData.listen_ip)) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_LISTEN_IP_INVALID);
		}
		authInstance(reqData);
	};

	/**
	 * 认证MariaDB实例
	 * @returns {boolean|void}
	 */
	const authMariaDBInstance = () => {
		let authType = parseInt($('#mariaAuthtype').val());
		let port = $('#mariaport').val();
		let reqData = {
			db_type: CONF.DB_TYPE.MARIA,
			instance_name: `127.0.0.1:${port}`,
			listen_ip: $('#mariaListenIp').val(),
			mysql: {
				config_path: $.fn.PathTreeSelector.getCheckPath('mariaCnfPath'),
				auth_type: authType,
				username: $('#marianame').val().replace(/\s/g, ''),
				password: btoa($('#mariapassword').val()),
				tcp: {
					ip: $('#mariaIp').val(),
					port: port,
				},
				sock: {
					host: $('#mariaHost').val(),
					sock_path: $.fn.PathTreeSelector.getCheckPath('mariaSockPath'),
				},
			},
		};

		// 验证
		if (1 === authType) {
			// 验证ip地址
			if (!ipV4V6(reqData.mysql.tcp.ip)) {
				return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_IP_INVALID);
			}
			// 验证端口号
			if (!isPort(reqData.mysql.tcp.port)) {
				return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_PORT_INVALID);
			}
			reqData.mysql.tcp.port = parseInt(reqData.mysql.tcp.port);
		}
		// 用户名和密码
		if (!reqData.mysql.username || !reqData.mysql.password) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
		}
		if (
			!reqData.mysql.config_path ||
			(!reqData.mysql.tcp.port && authType === 1) || (!reqData.mysql.sock.sock_path && authType === 2)
		) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_INPUT_CONFIG_INFO);
		}
		// 实例监听IP验证
		if ($('#mariaListenIpCheck').is(':checked') && !ipV4V6(reqData.listen_ip)) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_LISTEN_IP_INVALID);
		}
		authInstance(reqData);
	};

	/**
	 * 认证pg系实例，POSTGRES、ANTDB、KINGBASE、UXDB、OPENGAUSS、VASTBASE、HIGHGO DB
	 * @param row
	 * @param dbType
	 * @returns {boolean|void}
	 */
	const authPostgresInstance = (row, dbType) => {
		let clusterFlag = !!$('#clusterCheck').get(0).checked;
		let reqData = {
			db_type: dbType,
			instance_name: row.instance_name,
			listen_ip: $('#listenIp').val(),
			postgres: {
				db_name: $('#installname').val(),
				db_bin_path: $.fn.PathTreeSelector.getCheckPath('installPath'),
				username: $('#authname').val(),
				password: btoa($('#password').val()),
				is_cluster: clusterFlag,
				cluster_name: clusterFlag ? $('#clusterAlias').val() : '',
				cluster_info: [],
			},
		};

		// 验证
		if (!reqData.postgres.db_bin_path) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_INPUT_CONFIG_INFO);
		}
		if (!reqData.postgres.username || !reqData.postgres.password) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
		}
		if (clusterFlag) {
			// 集群别名
			if (!$.trim(reqData.postgres.cluster_name)) {
				UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_VERIFY_NO_CLUSTER_ALIAS);
				return false;
			}
			reqData.postgres.cluster_info = getClusterInfo(reqData.db_type);
			if (reqData.postgres.cluster_info === false) {
				return false;
			}
		}
		authInstance(reqData);
	};

	/**
	 * 认证DM实例
	 * @param row
	 * @returns {boolean|void}
	 */
	const authDMInstance = (row) => {
		let clusterFlag = !!$('#clusterCheck').get(0).checked;
		let reqData = {
			db_type: CONF.DB_TYPE.DM,
			instance_name: row.instance_name,
			listen_ip: !$('#listenIpCheck').get(0).checked ? '' : $('#listenIp').val(),
			dm: {
				install_db_username: $('#installname').val(),
				username: $('#authname').val(),
				password: btoa($('#password').val()),
				is_cluster: clusterFlag,
				cluster_name: clusterFlag ? $('#clusterAlias').val() : '',
				cluster_info: [],
			},
		};

		// 验证
		if (!reqData.dm.username || !reqData.dm.password) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
		}

		// 集群
		if (clusterFlag) {
			// 集群别名
			if (!$.trim(reqData.dm.cluster_name)) {
				UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_VERIFY_NO_CLUSTER_ALIAS);
				return false;
			}
			reqData.dm.cluster_info = getClusterInfo(reqData.db_type);
			if (reqData.dm.cluster_info === false) {
				return false;
			}
		}
		authInstance(reqData);
	};

	/**
	 * 认证MongoDB实例
	 * @param row
	 * @returns {boolean|void}
	 */
	const authMongoDBInstance = (row) => {
		let clusterFlag = !!$('#clusterCheck').get(0).checked;
		let reqData = {
			db_type: CONF.DB_TYPE.MONGODB,
			instance_name: row.instance_name,
			listen_ip: $('#listenIp').val(),
			mongodb: {
				db_bin_path: $.fn.PathTreeSelector.getCheckPath('mongodbBinPath'),
				username: $('#mongodbUsername').val(),
				password: btoa($('#mongodbPassword').val()),
				is_cluster: clusterFlag,
				cluster_name: clusterFlag ? $('#clusterAlias').val() : '',
				cluster_info: [],
			},
		};

		// 验证
		if (!reqData.mongodb.db_bin_path) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_INPUT_CONFIG_INFO);
		}
		if (!reqData.mongodb.username || !reqData.mongodb.password) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
		}
		if (clusterFlag) {
			// 集群别名
			if (!$.trim(reqData.mongodb.cluster_name)) {
				return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_CLUSTER_NAME);
			}
			reqData.mongodb.cluster_info = getClusterInfo(reqData.db_type);
			if (reqData.mongodb.cluster_info === false) {
				return false;
			}
		}
		authInstance(reqData);
	};

	/**
	 * 认证TiDB实例
	 * @param row
	 * @returns {boolean|void}
	 */
	const authTiDBInstance = (row) => {
		let tidbClusterFlag = !!$('#tidbClusterCheck').get(0).checked;
		let reqData = {
			db_type: CONF.DB_TYPE.TIDB,
			instance_name: row.instance_name,
			listen_ip: $('#listenIp').val(),
			tidb: {
				username: $('#tidbUsername').val(),
				password: btoa($('#tidbPassword').val()),
				install_db_username: '',
				is_cluster: tidbClusterFlag,
				cluster_name: tidbClusterFlag ? $('#clusterAlias').val() : '',
				cluster_info: [],
			},
		};

		// 验证
		if (!reqData.tidb.username || !reqData.tidb.password) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
		}
		if (tidbClusterFlag) {
			// 集群别名
			if (!$.trim(reqData.tidb.cluster_name)) {
				return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_CLUSTER_NAME);
			}
			let rows = $('#instanceTable').bootstrapTable('getSelections');
			let treeNodes = tidbAgentTree.getCheckedNodes(true);
			for (const treeNode of treeNodes) {
				if (treeNode.eventtype === 'agent') {
					reqData.tidb.cluster_info.push({
						agent_uuid: treeNode.agent_uuid,
						listen_ip: treeNode.agent_ip,
						instance_name: rows[0].instance_name,
					});
				}
			}
		}
		authInstance(reqData);
	};

	/**
	 * 认证SapHana实例
	 * @param row
	 * @returns {boolean|void}
	 */
	const authSapHanaInstance = (row) => {
		let clusterFlag = !!$('#clusterCheck').get(0).checked;
		let reqData = {
			db_type: CONF.DB_TYPE.SAPHANA,
			instance_name: row.instance_name,
			listen_ip: $('#listenIp').val(),
			sap_hana: {
				username: $('#sapHanaUsername').val(),
				password: btoa($('#sapHanaPassword').val()),
				is_cluster: clusterFlag,
				cluster_name: clusterFlag ? $('#clusterAlias').val() : '',
				cluster_service_ip: $('#clusterServiceIp').val(),
				cluster_info: [],
			},
		};

		// 验证
		if (!reqData.sap_hana.username || !reqData.sap_hana.password) {
			return UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_NO_USER_OR_PASSWORD);
		}
		if (clusterFlag) {
			// 集群别名
			if (!$.trim(reqData.sap_hana.cluster_name)) {
				UIToastr.showWarning(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_VERIFY_NO_CLUSTER_ALIAS);
				return false;
			}
			// 集群服务IP
			if (!$.trim(reqData.sap_hana.cluster_service_ip) && !ipV4V6(reqData.sap_hana.cluster_service_ip)) {
				UIToastr.showWarning(LANG.UI_VERIFY_SET_CLUSTER_SERVICE_IP, LANG.UI_VERIFY_INPUT_CORRECT_IP_ADDRESS);
				return false;
			}
			reqData.sap_hana.cluster_info = getClusterInfo(reqData.db_type);
			if (reqData.sap_hana.cluster_info === false) {
				return false;
			}
		}
		authInstance(reqData);
	};

	//提交
	const submit = function () {
		let rows = $('#instanceTable').bootstrapTable('getSelections');
		let dbType = parseInt($('#appType').val());

		switch (dbType) {
			case CONF.DB_TYPE.SQLSERVER:
				authSqlServerInstance(rows[0]);
				return;
			case CONF.DB_TYPE.ORACLE:
				authOracleInstance();
				return;
			case CONF.DB_TYPE.DM:
				authDMInstance(rows[0]);
				return;
			case CONF.DB_TYPE.MARIA:
				authMariaDBInstance();
				return;
			case CONF.DB_TYPE.MYSQL:
				authMySQLInstance();
				return;
			case CONF.DB_TYPE.POSTGRE:
			case CONF.DB_TYPE.ANTDB:
			case CONF.DB_TYPE.KINGBASE:
			case CONF.DB_TYPE.UXDB:
			case CONF.DB_TYPE.OPENGAUSS:
			case CONF.DB_TYPE.VASTBASE:
			case CONF.DB_TYPE.HIGHGO:
				authPostgresInstance(rows[0], dbType);
				return;
			case CONF.DB_TYPE.MONGODB:
				authMongoDBInstance(rows[0]);
				return;
			case CONF.DB_TYPE.TIDB:
				authTiDBInstance(rows[0]);
				return;
			case CONF.DB_TYPE.SAPHANA:
				authSapHanaInstance(rows[0]);
				return;
		}
	};

	/**
	 * 获取集群信息
	 * @param {number} dbType 数据库类别
	 * @return {array|boolean}
	 */
	const getClusterInfo = function (dbType) {
		let clusterInstanceName = '';
		let listenIpFlag = !!$('#listenIpCheck').get(0).checked;
		if (dbType === CONF.DB_TYPE.ORACLE) {
			clusterInstanceName = $('#instancename').val();
		} else {
			let rows = $('#instanceTable').bootstrapTable('getSelections');
			clusterInstanceName = rows[0].instance_name;
		}
		if (!zTreeAgent) {
			UIToastr.showInfo(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_AUTH_INSTANCE_CLUSTER_TIPS);
			return false;
		}
		let clusterInfo = [];
		let disableNodes = zTreeAgent.getNodesByParam('chkDisabled', true);  // 禁止选择的节点
		let checkNodes = zTreeAgent.getCheckedNodes(true);  // 勾选的节点
		if (!disableNodes.length && !checkNodes.length) {
			UIToastr.showInfo(LANG.UI_DB_INSTANCE_AUTH, LANG.UI_DB_INSTANCE_SELECT_CLUSTER_CONNECT)
			return false;
		}
		let mongodbMongosCheckNodes = [];
		let mongodbMongosDisableNodes = [];
		if (dbType === CONF.DB_TYPE.MONGODB) {  // MongoDB勾选路由节点
			mongodbMongosCheckNodes = mongodbMongosTree.getCheckedNodes(true);
			mongodbMongosDisableNodes = mongodbMongosTree.getNodesByParam('chkDisabled', true);
			for (const mongodbMongosDisableNode of mongodbMongosDisableNodes) {
				clusterInfo.push({
					'agent_uuid': mongodbMongosDisableNode.agent_uuid,
					'listen_ip': mongodbMongosDisableNode.agent_ip,
					'instance_name': mongodbMongosDisableNode.instance_name,
					'node_identity': 'mongos',
				});
			}
			for (const mongodbMongosCheckNode of mongodbMongosCheckNodes) {
				clusterInfo.push({
					'agent_uuid': mongodbMongosCheckNode.agent_uuid,
					'listen_ip': mongodbMongosCheckNode.agent_ip,
					'instance_name': mongodbMongosCheckNode.instance_name,
					'node_identity': 'mongos',
				});
			}
		}
		for (let i = 0; i < disableNodes.length; i++) {
			// Oracle、HighGO、DM使用监听IP
			let ip = disableNodes[i].agent_ip;
			switch (dbType) {
				case CONF.DB_TYPE.SQLSERVER:
					ip = $('#clusterIp').val();
					break;
				case CONF.DB_TYPE.ORACLE:
				case CONF.DB_TYPE.HIGHGO:
					if (listenIpFlag) {
						ip = $('#' + disableNodes[i].agent_uuid + ' .listenIp').val();
					}
					break;
				case CONF.DB_TYPE.DM:
					if (listenIpFlag) {
						ip = $('#' + disableNodes[i].agent_uuid + ' .listenIp').val();
					} else {
						ip = '';
					}
					break;
			}
			if (disableNodes[i].eventtype === 'agent') {
				if (ip && !ipV4V6(ip)) {
					UIToastr.showInfo(LANG.UI_VERIFY_SET_IP_ADDRESS, LANG.UI_VERIFY_INPUT_CORRECT_IP_ADDRESS);
					return false;
				}
				if (
					dbType === CONF.DB_TYPE.DM ||
					dbType === CONF.DB_TYPE.KINGBASE ||
					dbType === CONF.DB_TYPE.ORACLE
				) {
					continue;
				}
				clusterInfo.push({
					'agent_uuid': disableNodes[i].agent_uuid,
					'listen_ip': ip,
					'instance_name': clusterInstanceName,
					'node_identity': '',
				});
			} else if (disableNodes[i].eventtype === 'instance') {
				if (ip && !ipV4V6(ip)) {
					UIToastr.showInfo(LANG.UI_VERIFY_SET_IP_ADDRESS, LANG.UI_VERIFY_INPUT_CORRECT_IP_ADDRESS);
					return false;
				}
				switch (dbType) {
					case CONF.DB_TYPE.DM:
					case CONF.DB_TYPE.KINGBASE:
					case CONF.DB_TYPE.ORACLE:
						clusterInfo.push({
							'agent_uuid': disableNodes[i].agent_uuid,
							'listen_ip': ip,
							'instance_name': disableNodes[i].instance_name,
							'node_identity': '',
						});
						break;
				}
			}
		}
		for (let j = 0; j < checkNodes.length; j++) {
			// Oracle、HighGO、DM使用监听IP
			let ip = checkNodes[j].agent_ip;
			switch (dbType) {
				case CONF.DB_TYPE.SQLSERVER:
					ip = $('#clusterIp').val();
					break;
				case CONF.DB_TYPE.ORACLE:
				case CONF.DB_TYPE.HIGHGO:
					if (listenIpFlag) {
						ip = $('#' + checkNodes[j].agent_uuid + ' .listenIp').val();
					}
					break;
				case CONF.DB_TYPE.DM:
					if (listenIpFlag) {
						ip = $('#' + checkNodes[j].agent_uuid + ' .listenIp').val();
					} else {
						ip = '';
					}
					break;
			}
			if (checkNodes[j].eventtype === 'agent') {
				if (ip && !ipV4V6(ip)) {
					UIToastr.showInfo(LANG.UI_VERIFY_SET_IP_ADDRESS, LANG.UI_VERIFY_INPUT_CORRECT_IP_ADDRESS);
					return false;
				}
				if (
					dbType === CONF.DB_TYPE.DM ||
					dbType === CONF.DB_TYPE.KINGBASE ||
					dbType === CONF.DB_TYPE.ORACLE
				) {
					continue;
				}
				clusterInfo.push({
					'agent_uuid': checkNodes[j].agent_uuid,
					'listen_ip': ip,
					'instance_name': clusterInstanceName,
					'node_identity': '',
				});
			} else if (checkNodes[j].eventtype === 'instance') {
				if (ip && !ipV4V6(ip)) {
					UIToastr.showInfo(LANG.UI_VERIFY_SET_IP_ADDRESS, LANG.UI_VERIFY_INPUT_CORRECT_IP_ADDRESS);
					return false;
				}
				switch (dbType) {
					case CONF.DB_TYPE.DM:
					case CONF.DB_TYPE.KINGBASE:
					case CONF.DB_TYPE.ORACLE:
						clusterInfo.push({
							'agent_uuid': checkNodes[j].agent_uuid,
							'listen_ip': ip,
							'instance_name': checkNodes[j].instance_name,
							'node_identity': '',
						});
						break;
				}
			}
		}
		return clusterInfo;
	}

	//////////////////// 结束-实例认证 ////////////////////

	/**
	 * 初始化旧数据
	 * @param {Object|null} row
	 * @param {String} row.app_uuid
	 * @param {Boolean} row.auth_flag
	 * @param {Boolean} row.cluster_flag
	 * @param {String} row.cluster_name
	 * @param {String} row.cluster_service_ip
	 * @param {String} row.cluster_uuid
	 * @param {Number} row.db_type
	 * @param {String} row.db_type_des
	 * @param {String} row.dbname
	 * @param {Object} row.detail
	 * @param {String} row.instance_cluster_path
	 * @param {String} row.instance_name
	 * @param {String} row.listen_ip
	 * @param {String} row.username
	 * @param {String} row.password
	 * @param {Array<Object>} row.task_info
	 * @param {String} row.verify_time
	 * @param {String} row.verify_type
	 * @param {String} row.version
	 * @param {String} row.install_db_username
	 */
	const initOldAuthInfo = function (row = null) {
		let authFlag = false;
		let dbType = parseInt($('#appType').val());
		let clientName = $('#clientName').html();
		let clientIp = $('#clientIp').html();
		$('#listenIpCheck').bootstrapSwitch('state', false);
		$('#mysqlListenIpCheck').bootstrapSwitch('state', false);
		$('#mariaListenIpCheck').bootstrapSwitch('state', false);
		$('#clusterCheck').bootstrapSwitch('state', false);
		if (row !== null) {
			$('#uuid').val(row.app_uuid);
			authFlag = row.auth_flag;
			if (row.listen_ip) {
				if (dbType === CONF.DB_TYPE.ORACLE) {
					if (row.listen_ip !== clientIp) {
						clientIp = row.listen_ip;
						$('#listenIpCheck').bootstrapSwitch('state', true);
					}
				} else {
					$('#listenIpCheck').bootstrapSwitch('state', true);
					$('#mysqlListenIpCheck').bootstrapSwitch('state', true);  // MySQL逻辑同Oracle逻辑
					$('#mariaListenIpCheck').bootstrapSwitch('state', true);  // MariaDB逻辑同Oracle逻辑
					clientIp = row.listen_ip;
				}
			}
		}

		//实例数据库名描述修改
		$('.installLabel').html(LANG.UI_CLIENT_APP_CONFIG_INSTALL_NAME);
		$('.installTips').html(LANG.UI_CLIENT_APP_CONFIG_INSTALL_NAME_TIPS);
		$('.installpathDiv').hide();  // 隐藏数据库bin路径
		$('.authItem').hide();  // 隐藏所有配置
		$('.osAuthDiv').hide();  // 隐藏操作系统认证的输入框
		$('.instanceAuthDiv').show();  // 显示实例认证的输入框
		$('#clusterAlias').val('');
		$('#clusterServiceIp').val('');
		let agentUuid = $('#clientUUID').val();
		let pgInstallPathSelector = new $.fn.PathTreeSelector.cls(agentUuid, {operateFlag: false});
		switch (dbType) {
			case CONF.DB_TYPE.SQLSERVER:
				$('.authtypeDiv').show();
				$('.clusterDiv').show();
				if (authFlag) {
					$('#authtype').val(row.verify_type);
					if (parseInt(row.verify_type) === 1) {  // Windows身份认证
						$('#osAuthName').val(row.username);
						$('#osAuthPassword').val(atob(row.password));
						$('.osAuthDiv').show();
						$('.instanceAuthDiv').hide();
					} else {  // SQL Server身份认证
						$('#authname').val(row.username);
						$('#password').val(atob(row.password));
						$('.osAuthDiv').hide();
						$('.instanceAuthDiv').show();
					}
					$('#clusterCheck').bootstrapSwitch('state', row.cluster_flag);
					if (row.cluster_flag) {
						$('.clusterTreeDiv').show();
						$('.clusterIpDiv').show(); //集群IP
						$('.clusterAliasDiv').show(); //集群别名
						clusterUUID = row.cluster_uuid;
						$('#clusterCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
						$('#clusterIp').val(clientIp);
						$('#clusterAlias').val(row.cluster_name);
					} else {
						$('.clusterTreeDiv').hide();
						$('.clusterIpDiv').hide(); //集群IP
						$('.clusterAliasDiv').hide(); //集群别名
					}
				} else {
					$('.osAuthDiv').show();
					$('.instanceAuthDiv').hide();
				}
				$('.userDiv').show();
				initAgentTree();
				$('.installnameDiv').hide();
				break;
			case CONF.DB_TYPE.ORACLE:
				$('.userDiv').show();
				$('.clusterDiv').show();
				$('#installname').val("oracle");
				$('#oracleAuthType').val(2);  // 默认选中数据库身份认证
				$('.oracleAuthTypeDiv').show();
				$('.listenIpDiv').show();	//监听IP
				//添加实例显示实例名
				if (row !== null) {
					$('.oracleAddDiv').hide();
				} else {
					$('.oracleAddDiv').show();
				}
				$('#instancename').val(row === null ? '' : row.instance_name);
				if (authFlag) {
					$('#installname').val(row.install_db_username);
					if (parseInt(row.verify_type) !== 1) {
						$('#authname').val(row.username);
						$('#password').val(atob(row.password));
						$('#oracleAuthType').val(2);
					} else {
						$('#osAuthName').val(row.username);
						$('#osAuthPassword').val(atob(row.password));
						$('#oracleAuthType').val(1);
						$('.osAuthDiv').show();
						$('.instanceAuthDiv').hide();
						$('.clusterTreeDiv').hide();
						$('.clusterDiv').hide();
						$('.listenIpDiv').hide();
					}
					$('#clusterCheck').bootstrapSwitch('state', row.cluster_flag);
					if (row.cluster_flag) {
						$('.clusterTreeDiv').show();
						clusterUUID = row.cluster_uuid;
						$('#clusterCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
						$('#clusterAlias').val(row.cluster_name);
						$('#clusterServiceIp').val(row.cluster_service_ip);
					} else {
						$('.clusterTreeDiv').hide();
					}
				}
				initAgentTree();
				$('.installnameDiv').hide(); //去掉安装用户名
				break;
			case CONF.DB_TYPE.MYSQL:
				$('.mysqlDiv').show();
				let cnfPathSelector = new $.fn.PathTreeSelector.cls(agentUuid, {operateFlag: false});
				let sockPathSelector = new $.fn.PathTreeSelector.cls(agentUuid, {operateFlag: false});
				$('#mysqlAuthtype').removeAttr('disabled');
				$('#mysqlIp').removeAttr('disabled');
				$('#port').removeAttr('disabled');
				// $('.mysqlListenIpDiv').show(); //mysql监听IP
				$('#mysqlHost').attr('disabled', 'disabled');  // MySQL的socket认证不能修改host主机
				if (authFlag) {
					cnfPathSelector.init({
						target_id: 'cnfPath',
						select_mode: 1,
						selected_path: row.detail.cnf_path,
						select_only: false,
					});
					sockPathSelector.init({
						target_id: 'sockPath',
						select_mode: 1,
						selected_path: row.detail.sock_path,
						disabled: !!row.task_info.length,
						select_only: !!row.task_info.length,
					});
					$('#cnfPath').val(row.detail.cnf_path);
					$('#mysqlname').val(row.username);
					$('#mysqlpassword').val(atob(row.password));
					if (parseInt(row.verify_type) === 1) {  // tcp/ip认证
						$('#mysqlAuthtype').val(1);
						$('#mysqlHost').val('localhost');  // 设置默认值，下同
						$('#mysqlIp').val(row.detail.host);
						$('#port').val(row.detail.port);
					} else {  // sock认证
						$('#mysqlAuthtype').val(2);
						$('#mysqlHost').val(row.detail.host);
						$('#mysqlIp').val('127.0.0.1');
						$('#port').val(3306);
					}
					$('#mysqlAuthtype').trigger('change');  // 手动触发change事件
					if (row.task_info.length) {
						$('#mysqlAuthtype').attr('disabled', 'disabled');
						$('#mysqlIp').attr('disabled', 'disabled');
						$('#port').attr('disabled', 'disabled');
					}
				} else {
					cnfPathSelector.init({
						target_id: 'cnfPath',
						select_mode: 1,
						select_only: false,
					});
					sockPathSelector.init({
						target_id: 'sockPath',
						select_mode: 1,
						select_only: false,
					});
				}
				break;
			case CONF.DB_TYPE.MARIA:
				$('.mariaDiv').show();
				let mariaCnfPathSelector = new $.fn.PathTreeSelector.cls(agentUuid, {operateFlag: false});
				let mariaSockPathSelector = new $.fn.PathTreeSelector.cls(agentUuid, {operateFlag: false});
				$('#mariaAuthtype').removeAttr('disabled');
				$('#mariaIp').removeAttr('disabled');
				$('#mariaport').removeAttr('disabled');
				// $('.mariaListenIpDiv').show(); //mariadb监听IP
				$('#mariaHost').attr('disabled', 'disabled');  // MariaDB的socket认证不能修改host
				if (authFlag) {
					mariaCnfPathSelector.init({
						target_id: 'mariaCnfPath',
						select_mode: 1,
						selected_path: row.detail.cnf_path,
						select_only: false,
					});
					mariaSockPathSelector.init({
						target_id: 'mariaSockPath',
						select_mode: 1,
						selected_path: row.detail.sock_path,
						disabled: !!row.task_info.length,
						select_only: !!row.task_info.length,
					});
					//					$('#dataPath').val(mysqlInfo.data_dir);
					$('#marianame').val(row.username);
					$('#mariapassword').val(atob(row.password));
					if (parseInt(row.verify_type) === 1) {  // tcp/ip认证
						$('#mariaAuthtype').val(1);
						$('#mariaHost').val('localhost');  // 设置默认值，下同
						$('#mariaIp').val(row.detail.host);
						$('#mariaport').val(row.detail.port);
					} else {  // sock认证
						$('#mariaAuthtype').val(2);
						$('#mariaHost').val(row.detail.host);
						$('#mariaIp').val('127.0.0.1');
						$('#mariaport').val(3306);
					}
					$('#mariaAuthtype').trigger('change');  // 手动触发change事件
					if (row.task_info.length) {
						$('#mariaAuthtype').attr('disabled', 'disabled');
						$('#mariaIp').attr('disabled', 'disabled');
						$('#mariaport').attr('disabled', 'disabled');
					}
				} else {
					mariaCnfPathSelector.init({
						target_id: 'mariaCnfPath',
						select_mode: 1,
						select_only: false,
					});
					mariaSockPathSelector.init({
						target_id: 'mariaSockPath',
						select_mode: 1,
						select_only: false,
					});
				}
				break;
			case CONF.DB_TYPE.DM:
				$('.userDiv').show();
				$('#installname').val("dmdba");

				if (authFlag) {
					$('#installname').val(row.install_db_username);
					$('#authname').val(row.username);
					$('#password').val(atob(row.password));
					$('#clusterCheck').bootstrapSwitch('state', row.cluster_flag);
					if (row.cluster_flag) {
						$('.clusterTreeDiv').show();
						$('.clusterAliasDiv').show(); //集群别名
						clusterUUID = row.cluster_uuid;
						$('#clusterCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
						$('#clusterAlias').val(row.cluster_name);
					} else {
						$('.clusterTreeDiv').hide();
						$('.clusterAliasDiv').hide(); //集群别名
					}
				}
				$('.listenIpDiv').show(); //监听IP
				$('.clusterDiv').show();
				initAgentTree();
				break;
			case CONF.DB_TYPE.POSTGRE:
			case CONF.DB_TYPE.ANTDB:
			case CONF.DB_TYPE.OPENGAUSS:
			case CONF.DB_TYPE.VASTBASE:
				//实例数据库名描述修改
				$('.installLabel').html(LANG.UI_DB_NAME);
				$('.installTips').html(LANG.UI_DB_INSTANCE_EVERY_DATABASE);
				$('.userDiv').show();
				$('#installname').val("postgres");

				if (authFlag) {
					if (row.detail.database_name) {
						$('#installname').val(row.detail.database_name);
					}
					if (row.detail.install_db_path) {
						pgInstallPathSelector.init({
							target_id: 'installPath',
							select_mode: 2,
							selected_path: row.detail.install_db_path,
							select_only: false,
						});
					} else {
						pgInstallPathSelector.init({
							target_id: 'installPath',
							select_mode: 2,
							select_only: false,
						});
					}
					$('#authname').val(row.username);
					$('#password').val(atob(row.password));
					if (row.cluster_flag) {
						$('.clusterTreeDiv').show();
						$('.clusterAliasDiv').show(); //集群别名
						clusterUUID = row.cluster_uuid;
						$('#clusterCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
						$('#clusterAlias').val(row.cluster_name);
					} else {
						$('.clusterTreeDiv').hide();
						$('.clusterAliasDiv').hide(); //集群别名
					}
				} else {
					pgInstallPathSelector.init({
						target_id: 'installPath',
						select_mode: 2,
						select_only: false,
					});
				}
				if (
					dbType === CONF.DB_TYPE.OPENGAUSS ||
					dbType === CONF.DB_TYPE.VASTBASE ||
					dbType === CONF.DB_TYPE.POSTGRE
				) {
					$('.clusterDiv').show();
					initAgentTree();
				}
				$('.installpathDiv').show();
				break;
			case CONF.DB_TYPE.KINGBASE:
				//实例数据库名描述修改
				$('.installLabel').html(LANG.UI_DB_NAME);
				$('.installTips').html(LANG.UI_DB_INSTANCE_EVERY_DATABASE);
				$('.userDiv').show();
				$('#installname').val("test");

				if (authFlag) {
					if (row.detail.database_name) {
						$('#installname').val(row.detail.database_name);
					}
					if (row.detail.install_db_path) {
						pgInstallPathSelector.init({
							target_id: 'installPath',
							select_mode: 2,
							selected_path: row.detail.install_db_path,
							select_only: false,
						});
					} else {
						pgInstallPathSelector.init({
							target_id: 'installPath',
							select_mode: 2,
							select_only: false,
						});
					}
					$('#authname').val(row.username);
					$('#password').val(atob(row.password));
					$('#clusterCheck').bootstrapSwitch('state', row.cluster_flag);
					if (row.cluster_flag) {
						$('.clusterTreeDiv').show();
						$('.clusterAliasDiv').show(); //集群别名
						clusterUUID = row.cluster_uuid;
						$('#clusterCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
						$('#clusterAlias').val(row.cluster_name);
					} else {
						$('.clusterTreeDiv').hide();
						$('.clusterAliasDiv').hide(); //集群别名
					}
				} else {
					pgInstallPathSelector.init({
						target_id: 'installPath',
						select_mode: 2,
						select_only: false,
					});
				}
				$('.clusterDiv').show();
				initAgentTree();
				$('.installpathDiv').show();
				break;
			case CONF.DB_TYPE.UXDB:
				//实例数据库名描述修改
				$('.installLabel').html(LANG.UI_DB_NAME);
				$('.installTips').html(LANG.UI_DB_INSTANCE_EVERY_DATABASE);
				$('.userDiv').show();
				$('#installname').val("uxdb");

				if (authFlag) {
					if (row.detail.database_name) {
						$('#installname').val(row.detail.database_name);
					}
					if (row.detail.install_db_path) {
						pgInstallPathSelector.init({
							target_id: 'installPath',
							select_mode: 2,
							selected_path: row.detail.install_db_path,
							select_only: false,
						});
					} else {
						pgInstallPathSelector.init({
							target_id: 'installPath',
							select_mode: 2,
							select_only: false,
						});
					}
					$('#authname').val(row.username);
					$('#password').val(atob(row.password));
				} else {
					pgInstallPathSelector.init({
						target_id: 'installPath',
						select_mode: 2,
						select_only: false,
					});
				}
				$('.installpathDiv').show();
				break;
			case CONF.DB_TYPE.HIGHGO:
				//实例数据库名描述修改
				$('.installLabel').html(LANG.UI_DB_NAME);
				$('.installTips').html(LANG.UI_DB_INSTANCE_EVERY_DATABASE);
				$('.userDiv').show();
				$('#installname').val("highgo");

				if (authFlag) {
					if (row.detail.database_name) {
						$('#installname').val(row.detail.database_name);
					}
					if (row.detail.install_db_path) {
						pgInstallPathSelector.init({
							target_id: 'installPath',
							select_mode: 2,
							selected_path: row.detail.install_db_path,
							select_only: false,
						});
					} else {
						pgInstallPathSelector.init({
							target_id: 'installPath',
							select_mode: 2,
							select_only: false,
						});
					}
					$('#authname').val(row.username);
					$('#password').val(atob(row.password));
					if (row.cluster_flag) {
						$('.clusterTreeDiv').show();
						$('.clusterAliasDiv').show(); //集群别名
						clusterUUID = row.cluster_uuid;
						$('#clusterCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);  // 这里会触发switchChange.bootstrapSwitch事件
						$('#clusterAlias').val(row.cluster_name);
					} else {
						$('.clusterTreeDiv').hide();
						$('.clusterAliasDiv').hide(); //集群别名
					}
				} else {
					pgInstallPathSelector.init({
						target_id: 'installPath',
						select_mode: 2,
						select_only: false,
					});
				}
				$('.clusterDiv').show();
				initAgentTree();
				$('.installpathDiv').show();
				break;
			case CONF.DB_TYPE.MONGODB:
				$('.mongodbDiv').show();
				$('.clusterDiv').show();
				let mongodbBinPathSelector = new $.fn.PathTreeSelector.cls(agentUuid, {operateFlag: false});
				if (authFlag) {
					$('#mongodbUsername').val(row.username);
					$('#mongodbPassword').val(atob(row.password));
					if (row.detail.install_db_path) {
						mongodbBinPathSelector.init({
							target_id: 'mongodbBinPath',
							select_mode: 2,
							selected_path: row.detail.install_db_path,
							select_only: false,
						});
					} else {
						mongodbBinPathSelector.init({
							target_id: 'mongodbBinPath',
							select_mode: 2,
							select_only: false,
						});
					}
					if (row.cluster_flag) {
						$('.clusterTreeDiv').show();
						clusterUUID = row.cluster_uuid;
						$('#clusterCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
						$('#clusterAlias').val(row.cluster_name);
					} else {
						$('#clusterAlias').val('');
						$('.clusterTreeDiv').hide();
					}
				} else {
					mongodbBinPathSelector.init({
						target_id: 'mongodbBinPath',
						select_mode: 2,
						select_only: false,
					});
				}
				initMongoDBMongosTree();
				initAgentTree();
				break;
			case CONF.DB_TYPE.TIDB:
				$('.tidbDiv').show();
				let clusterFlag = !!$('#tidbClusterCheck').get(0).checked;
				if (clusterFlag) {
					$('.clusterAliasDiv').show();
					// $('#clusterAlias').val(row.instance_name);
				}
				if (authFlag) {
					$('#tidbUsername').val(row.username);
					$('#tidbPassword').val(atob(row.password));
					if (clusterFlag) {
						$('.clusterAliasDiv').show();
						$('#clusterAlias').val(row.cluster_name);
					}
				}
				break;
			case CONF.DB_TYPE.SAPHANA:
				$('.sapHanaDiv').show();
				$('.clusterDiv').show();
				if (authFlag) {
					$('#sapHanaUsername').val(row.username);
					$('#sapHanaPassword').val(atob(row.password));
					if (row.cluster_flag) {
						$('.clusterTreeDiv').show();
						$('.clusterAliasDiv').show(); //集群别名
						$('#clusterServiceIp').val(row.cluster_service_ip);
						clusterUUID = row.cluster_uuid;
						$('#clusterCheck').bootstrapSwitch('state', true).bootstrapSwitch('disabled', true);
						$('#clusterAlias').val(row.cluster_name);
					} else {
						$('.clusterTreeDiv').hide();
						$('.clusterAliasDiv').hide(); //集群别名
					}
				}
				initAgentTree();
				break;
		}
		$('#oracleIpList').empty();  // 清空已添加的监听IP
		initClientListenIp(clientName, clientIp);
		$('#mysqlListenIp').val(clientIp);
		//重置数据
		if (!authFlag) {
			$('#authtype').val('1');
			$("#authname").val("");
			$("#password").val("");
			$('#mysqlname').val("");
			$('#mysqlpassword').val("");
			//			$('#dataPath').val("");
			$('#port').val("3306");
			$('#mysqlIp').val("127.0.0.1");
			$('#mysqlHost').val("localhost");

			$('#mariaIp').val("127.0.0.1");
			$('#mariaHost').val("localhost");

			$('#marianame').val("");
			$('#mariapassword').val("");
			$('#mariaport').val("3306");

			$('#clusterCheck').bootstrapSwitch('state', false).bootstrapSwitch('disabled', false);
			$('#listenIpCheck').bootstrapSwitch('state', false);
			$('.ipListDiv').hide(); // 监听IP
			$('#mysqlListenIpCheck').bootstrapSwitch('state', false);
			$('#mariaListenIpCheck').bootstrapSwitch('state', false);
			$('.mysqlIpListDiv').hide(); // MySQL监听IP
			$('.mariaIpListDiv').hide(); // MariaDB监听IP
		}
		if (
			!authFlag ||
			!row.cluster_name
		) {
			// 初始化集群别名的默认值
			initClusterName();
		}
	};

	/**
	 * 初始化集群别名
	 */
	const initClusterName = () => {
		let dbType = parseInt($('#appType').val());
		Metronic.blockUI({target: '#appModal', animate: true});
		pAjaxRequest({db_type: dbType}, `/api/v1/agents/applications/cluster_name`, 'GET', res => {
			Metronic.unblockUI('#appModal');
			if (!res.success) {
				return UIToastr.showError(LANG.UI_DB_INSTANCE_AUTH, res.msg);
			}
			$('#clusterAlias').val(res.data.cluster_name);
		});
	};

	const initAuthInstance = function () {
		let dbType = parseInt($('#appType').val());
		let agentUuid = $('#clientUUID').val();
		let data = {
			dbtype: dbType,
			agentuuid: agentUuid,
		};
		if (data.dbtype !== 0) {
			$('.instanceDiv').show();
		} else {
			$('.instanceDiv').hide();
			return;
		}
		//切换数据库类型
		$('.authtypeDiv').hide();
		$('.userDiv').hide();
		$('.mysqlDiv').hide();
		$('.mariaDiv').hide();
		$('.clusterDiv').hide();
		$('.clusterTreeDiv').hide();
		$('.addInstance').hide();
		$('.oracleAddDiv').hide();
		let clusterCheck = $('#clusterCheck').get(0).checked;
		switch (dbType) {
			case CONF.DB_TYPE.SQLSERVER:
				$('.authtypeDiv').show();
				$('.clusterDiv').show();
				if (!clusterCheck) {
					$('.clusterTreeDiv').hide();
				}
				break;
			case CONF.DB_TYPE.ORACLE:
				$('.userDiv').show();
				$('.clusterDiv').show();
				$('.addInstance').show();
				$('#installname').val("oracle");
				$('.oracleAddDiv').show();
				if (!clusterCheck) {
					$('.clusterTreeDiv').hide();
				}
				break;
			case CONF.DB_TYPE.MYSQL:
				$('.authtypeDiv').hide();
				$('.userDiv').hide();
				$('.mysqlDiv').show();
				//				$('.addInstance').show();
				$('.instanceDiv').hide();
				break;
			case CONF.DB_TYPE.DM:
				$('.userDiv').show();
				$('#installname').val("dmdba");
				$('.clusterDiv').show();
				if (!clusterCheck) {
					$('.clusterTreeDiv').hide();
				}
				break;
			case CONF.DB_TYPE.POSTGRE:
				$('.userDiv').show();
				$('#installname').val("postgres");
				$('.clusterDiv').show();
				if (!clusterCheck) {
					$('.clusterTreeDiv').hide();
				}
				break;
			case CONF.DB_TYPE.ANTDB:
				$('.userDiv').show();
				$('#installname').val("antdb");
				break;
			case CONF.DB_TYPE.OPENGAUSS:
				$('.userDiv').show();
				$('#installname').val("opengauss");
				$('.clusterDiv').show();
				if (!clusterCheck) {
					$('.clusterTreeDiv').hide();
				}
				break;
			case CONF.DB_TYPE.VASTBASE:
				$('.userDiv').show();
				$('#installname').val("vastbase");
				$('.clusterDiv').show();
				if (!clusterCheck) {
					$('.clusterTreeDiv').hide();
				}
				break;
			case CONF.DB_TYPE.KINGBASE:
				$('.userDiv').show();
				$('#installname').val("test");
				$('.clusterDiv').show();
				if (!clusterCheck) {
					$('.clusterTreeDiv').hide();
				}
				break;
			case CONF.DB_TYPE.UXDB:
				$('.userDiv').show();
				$('#installname').val("uxdb");
				break;
			case CONF.DB_TYPE.HIGHGO:
				$('.clusterDiv').show();
				$('.userDiv').show();
				$('#installname').val("highgo");
				if (!clusterCheck) {
					$('.clusterTreeDiv').hide();
				}
				break;
			case CONF.DB_TYPE.MARIA:
				$('.authtypeDiv').hide();
				$('.userDiv').hide();
				$('.mariaDiv').show();
				//				$('.addInstance').show();
				$('.instanceDiv').hide();
				break;
			case CONF.DB_TYPE.SAPHANA:
			case CONF.DB_TYPE.TIDB:
			case CONF.DB_TYPE.MONGODB:
				break;
		}

		initInstanceTable(dbType);
	};

	const formatterAppAuthType = (value, row) => {
		value = parseInt(value);
		let dbType = parseInt(row.db_type);
		let text = '--';
		if (!!row.auth_flag) {
			switch (dbType) {
				case CONF.DB_TYPE.SQLSERVER:
					if (1 === value) {
						text = LANG.UI_CLIENT_APP_SQL_SERVER_AUTH_TYPE1;
					} else {
						text = LANG.UI_CLIENT_APP_SQL_SERVER_AUTH_TYPE2;
					}
					break;
			}
		}
		return `<span title="${text}">${text}</span>`;
	};

	/**
	 * 设置单元格样式
	 * @param value
	 * @param row
	 * @param index
	 */
	const instanceCellStyle = (value, row, index) => {
		return {
			css: {
				'word-break': 'break-all',
				'white-space': 'normal',
				'vertical-align': 'top',
			}
		}
	};

	const getInstanceTableColumns = dbType => {
		let showPort = false;
		let showClusterPath = false;
		let showAuthType = false;
		let instanceTitle = LANG.UI_DB_INSTANCE_NAME;
		let instanceWidth = '10';
		let versionWidth = '15';
		let authTimeWidth = '15';
		let verifyTypeWidth = '10';
		let instanceClusterWidth = '15';
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			authTimeWidth = '23';
			verifyTypeWidth = '18'
			instanceClusterWidth = '20'
		}
		switch  (dbType) {
			case CONF.DB_TYPE.SQLSERVER:
				showAuthType = true;
				break;
			case CONF.DB_TYPE.ORACLE:
				break;
			case CONF.DB_TYPE.MYSQL:
			case CONF.DB_TYPE.MARIA:
				versionWidth = '10';
				if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
					versionWidth = '15'
				}
				break;
			case CONF.DB_TYPE.DM:
				// instanceTitle = LANG.UI_DB_SERVER_NAME;  // 改为实例名
				break;
			case CONF.DB_TYPE.POSTGRE:
			case CONF.DB_TYPE.ANTDB:
			case CONF.DB_TYPE.KINGBASE:
			case CONF.DB_TYPE.UXDB:
			case CONF.DB_TYPE.HIGHGO:
			case CONF.DB_TYPE.OPENGAUSS:
			case CONF.DB_TYPE.VASTBASE:  // 实例簇路径
				showClusterPath = true;
				instanceTitle = LANG.UI_DB_INSTANCE_PORT;
				instanceWidth = '15';
				break;
			case CONF.DB_TYPE.SAPHANA:
			case CONF.DB_TYPE.MONGODB:
				break;
			case CONF.DB_TYPE.TIDB:
				instanceTitle = LANG.UI_DB_CLUSTER_NAME;
				break;
		}
		if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
			instanceWidth = '15'
		}
		return [{
			checkbox: true,
			width: '2',
			widthUnit: '%',
			sortable: false,
			cellStyle: instanceCellStyle,
		}, {
			title: LANG.UI_CLIENT_IP_ADDRESS,
			field: '',
			width: '10',
			widthUnit: '%',
			formatter: () => {
				return $('#clientIp').html().trim();
			},
			cellStyle: instanceCellStyle,
		}, {
			title: instanceTitle,
			field: 'instance_name',
			width: instanceWidth,
			widthUnit: '%',
			cellStyle: instanceCellStyle,
		}, {
			title: LANG.UI_CLIENT_APP_VERSION,
			field: 'version',
			width: versionWidth,
			widthUnit: '%',
			cellStyle: instanceCellStyle,
		}, {
			title: LANG.UI_CLIENT_APP_PORT,
			field: 'port',
			width: '10',
			widthUnit: '%',
			visible: showPort,
			cellStyle: instanceCellStyle,
		}, {
			title: LANG.UI_CLIENT_APP_LOGIN_NAME,
			field: 'username',
			width: '12',
			widthUnit: '%',
			cellStyle: instanceCellStyle,
		}, {
			title: LANG.UI_CLIENT_APP_AUTH_TYPE,
			field: 'verify_type',
			width: verifyTypeWidth,
			widthUnit: '%',
			visible: showAuthType,
			formatter: formatterAppAuthType,
			cellStyle: instanceCellStyle,
		}, {
			title: LANG.UI_CLIENT_APP_AUTH_TIME,
			field: 'verify_time',
			width: authTimeWidth,
			widthUnit: '%',
			cellStyle: instanceCellStyle,
		}, {
			title: LANG.UI_CLIENT_APP_POSTGRES_CLUSTER_PATH,
			field: 'instance_cluster_path',
			width: instanceClusterWidth,
			widthUnit: '%',
			sortable: false,
			visible: showClusterPath,
			cellStyle: instanceCellStyle,
		}];
	};

	const initInstanceTable = dbType => {
		$('#instanceTable').bootstrapTable('destroy').baseTableConfig().init({
			vin_url: `/api/v1/agents/${$('#clientUUID').val()}/instances`,
			vin_method: 'get',
			toolbarId: '#vin_instance_toolbar',
			vin_toolbar: '#vin_instance_toolbar',
			vin_params: () => {
				let params = {
					db_type: dbType,
					agent_uuid_list: [],
				};
				if (dbType === CONF.DB_TYPE.TIDB) {
					let treeNodes = tidbAgentTree.getCheckedNodes(true);
					for (const treeNode of treeNodes) {
						if (treeNode.eventtype === 'agent') {
							params.agent_uuid_list.push(treeNode.agent_uuid);
						}
					}
				}
				return params;
			},
			// 排序
			sortable: false,
			singleSelect: true,
			// 隐藏行
			hideColumns: '',
			showButtonText: false,
			clickToSelect: true,
			pagination: false,
			showRefresh: false,
			showExport: false,
			showColumns: false,
			onResetView: () => {
				$('#submit_form').css({
					'height': 'auto',
					'max-height': 200,
				})
			},
			onPostBody: (data) => {
				if (data.length) {
					$('.instanceDiv').show();
				}
				if (dbType === CONF.DB_TYPE.ORACLE) {
					$('#vin_instance_toolbar').show();
				}  else {
					$('#vin_instance_toolbar').hide();
				}
				return data;
			},
			columns: getInstanceTableColumns(dbType),
		});
	};

	/**
	 * MongoDB的Mosgos集群关联树点击
	 */
	const mongoDBMongosSelect = function (treeId, treeNode) {
		if (treeNode.eventtype === 'agent') {
			mongodbMongosTree.expandNode(treeNode, true, true, true, true);
		} else if (treeNode.eventtype === 'instance') {
			mongodbMongosTree.checkNode(treeNode, !treeNode.checked, true, true);
		}
	};

	/**
	 * MongoDB的Mosgos集群关联树点击
	 * @param e
	 * @param treeId
	 * @param treeNode
	 */
	const mongoDBMongosCheck = function (e, treeId, treeNode) {
		if (treeNode.eventtype === 'instance') {
		}
	};

	/**
	 * MongoDB的Mosgos集群关联树节点展开了
	 * @param treeId
	 * @param treeNode
	 */
	const mongoDBMongosExpand = (treeId, treeNode) => {
		if (treeNode.eventtype !== 'agent') {
			return true;
		}
		// 加载实例数据
		loadMongoDBMongosInstanceNodes(treeNode);
		return true;
	};

	/**
	 * 加载MongoDB的Mosgos集群关联树的实例节点
	 * @param treeNode
	 * @returns {boolean}
	 */
	const loadMongoDBMongosInstanceNodes = (treeNode) => {
		if (treeNode.eventtype !== 'agent' || treeNode.load_instance_flag) {
			return true;
		}

		// 加载代理实例
		let reqData = {
			db_type: parseInt($('#appType').val()),
		};
		Metronic.blockUI({target: '#' + treeNode.tId, animate: true});
		pAjaxRequest(reqData, `/api/v1/agents/${treeNode.agent_uuid}/instances`, 'GET', res => {
			Metronic.unblockUI('#' + treeNode.tId);
			if (!res.success) {
				UIToastr.showWarning(LANG.UI_CLIENT_APP_SCAN_TITLE, res.message);
				return;
			}
			treeNode.load_instance_flag = true;
			mongodbMongosTree.updateNode(treeNode);

			let nodes = [];
			for (const row of res.data.rows) {
				let checked = false;
				let chkDisabled = false;
				if (row.cluster_flag && row.cluster_uuid === clusterUUID) {
					checked = true;
					chkDisabled = true;
				}

				nodes.push({
					id: row.app_uuid,
					pId: treeNode.id,
					title: row.instance_name,
					name: row.instance_name,
					icon: './img/vm/host.png',
					isParent: false,
					nocheck: false,
					checked,
					chkDisabled,
					agent_uuid: treeNode.agent_uuid,
					agent_ip: treeNode.agent_ip,
					app_uuid: treeNode.app_uuid,
					instance_name: row.instance_name,
					eventtype: 'instance',
				});
			}
			mongodbMongosTree.addNodes(treeNode, nodes);
			mongodbMongosTree.expandNode(treeNode, true);
		});
	};

	/**
	 * 获取MongoDB的Mongos树的配置
	 */
	const getMongoDBMongosTreeSetting = () => {
		return {
			check: {
				enable: true,
				nocheckInherit: false
			},
			data: {
				simpleData: {
					enable: true,
					idKey: 'id',
					pIdKey: 'pId',
					rootPId: 0
				},
				key: {
					title: 'title',
				}
			},
			callback: {
				beforeClick: mongoDBMongosSelect,
				onCheck: mongoDBMongosCheck,
				beforeExpand: mongoDBMongosExpand,
			},
			view: {
				showTitle: true,
				nameIsHTML: true,
				addHoverDom: (treeId, treeNode) => {
					commonAddHoverDom(treeId, treeNode, 'mongos_cluster');
				},
				removeHoverDom: (treeId, treeNode) => {
					commonRemoveHoverDom(treeId, treeNode, 'mongos_cluster');
				},
			}
		};
	};

	/**
	 * 初始化MongoDB的Mongos树
	 */
	const initMongoDBMongosTree = function () {
		/**
		 * 1. 获取在线的客户端信息
		 */
		let reqData = {
			h_online_status: 1,
			offset: 0,
			limit: 100,
			offset_flag: false,
		};
		let agentUuid = $('#clientUUID').val();
		let mongodbMongosTreeTag = $('#mongodbMongosTree');
		let dbType = parseInt($('#appType').val());
		Metronic.blockUI({target: '#mongodbMongosTree', animate: true});
		pAjaxRequest(reqData, `/api/v1/agents`, 'GET', res => {
			Metronic.unblockUI('#mongodbMongosTree');
			if (!res.success) {
				$("#mongodbNoAgent").show();
				mongodbMongosTreeTag.hide();
				return;
			}
			if (!res.data.rows.length) {
				$("#mongodbNoAgent").show();
				mongodbMongosTreeTag.hide();
				return;
			}
			let nodes = {};

			for (const row of res.data.rows) {
				if (typeof nodes[row.group_uuid] === 'undefined') {
					nodes[row.group_uuid] = {
						id: row.group_uuid,
						pId: '',
						name: row.group_name,
						title: row.group_name,
						group_name: row.group_name,
						group_uuid: row.group_uuid,
						isParent: true,
						open: true,
						nocheck: true,
						icon: './img/platform/flag.png',
						eventtype: 'group',
					};
				}

				if (agentUuid === row.agent_uuid) {
					continue;
				}
				let name = row.agent_ip + '(' + row.hostname + ')';
				let initLoadInstanceFlag = false;
				if (row.hostname !== row.alias) {
					name = row.agent_ip + '(' + row.alias + ')';
				}
				for (const appInfo of row.app_list) {
					if (dbType === appInfo.app_type && appInfo.cluster_flag && appInfo.cluster_uuid === clusterUUID) {
						initLoadInstanceFlag = true;
					}
				}
				nodes[row.group_uuid + '-' + row.agent_uuid] = {
					id: row.group_uuid + '-' + row.agent_uuid,
					pId: row.group_uuid,
					name,
					title: row.agent_ip,
					group_name: row.group_name,
					group_uuid: row.group_uuid,
					agent_uuid: row.agent_uuid,
					agent_ip: row.agent_ip,
					hostname: row.hostname,
					isParent: true,
					open: false,
					checked: false,
					nocheck: true,
					chkDisabled: false,
					icon: './img/vm/host.png',
					eventtype: 'agent',
					init_load_instance_flag: initLoadInstanceFlag,  // 初始化就展开实例，用于编辑已认证的实例
					load_instance_flag: false,
				};
			}

			mongodbMongosTree = $.fn.zTree.init(mongodbMongosTreeTag, getMongoDBMongosTreeSetting(), Object.values(nodes));

			// 已认证集群实例加载关联的实例
			let expandNodeList = mongodbMongosTree.getNodesByParam('init_load_instance_flag', true);
			for (const expandNode of expandNodeList) {
				mongodbMongosTree.expandNode(expandNode, true, true, true, true);
			}
		});
	};

	/**
	 * 获取客户端树的配置
	 */
	const getAgentTreeSetting = () => {
		return {
			check: {
				enable: true,
				nocheckInherit: false
			},
			data: {
				simpleData: {
					enable: true,
					idKey: 'id',
					pIdKey: 'pId',
					rootPId: 0
				},
				key: {
					title: 'title',
				}
			},
			callback: {
				onCheck: agentCheck,
				beforeClick: nodeSelect,
				beforeExpand: agentExpand,
			},
			view: {
				showTitle: true,
				nameIsHTML: true,
				addHoverDom: (treeId, treeNode) => {
					commonAddHoverDom(treeId, treeNode, 'agent_cluster');
				},
				removeHoverDom: (treeId, treeNode) => {
					commonRemoveHoverDom(treeId, treeNode, 'agent_cluster');
				},
			}
		};
	};

	//初始化关联集群树
	const initAgentTree = function () {
		/**
		 * 1. 获取在线的客户端信息
		 */
		let reqData = {
			h_online_status: 1,
			offset: 0,
			limit: 100,
			offset_flag: false,
		};
		let agentUuid = $('#clientUUID').val();
		let agentTreeTag = $('#agent_tree');
		let dbType = parseInt($('#appType').val());
		Metronic.blockUI({target: '#agent_tree', animate: true});
		pAjaxRequest(reqData, `/api/v1/agents`, 'GET', res => {
			Metronic.unblockUI('#agent_tree');
			if (!res.success) {
				$("#noagent").show();
				agentTreeTag.hide();
				return;
			}
			if (!res.data.rows.length) {
				$("#noagent").show();
				agentTreeTag.hide();
				return;
			}
			let nodes = {};
			let rows = $('#instanceTable').bootstrapTable('getSelections');
			let selectRow = null;
			if (rows.length) {
				selectRow = rows[0];
			}

			for (const row of res.data.rows) {
				if (typeof nodes[row.group_uuid] === 'undefined') {
					let nocheck = false;
					if (
						dbType === CONF.DB_TYPE.DM ||
						dbType === CONF.DB_TYPE.KINGBASE ||
						dbType === CONF.DB_TYPE.ORACLE
					) {
						nocheck = true;
					}
					nodes[row.group_uuid] = {
						id: row.group_uuid,
						pId: '',
						name: row.group_name,
						title: row.group_name,
						group_name: row.group_name,
						group_uuid: row.group_uuid,
						isParent: true,
						open: true,
						nocheck,
						icon: './img/platform/flag.png',
						eventtype: 'group',
					};
				}

				if (agentUuid === row.agent_uuid) {
					continue;
				}
				let name = row.alias + '(' + row.agent_ip + ')';
				let checked = false;
				let nocheck = false;
				let isParent = false;
				let initLoadInstanceFlag = false;
				let listenIp = row.agent_ip;
				if (row.agent_ip !== row.alias) {
					name = row.hostname + '(' + row.alias + ')';
				}
				for (const appInfo of row.app_list) {
					if (dbType === appInfo.app_type && appInfo.cluster_flag && appInfo.cluster_uuid === clusterUUID) {
						checked = true;
						initLoadInstanceFlag = true;
					}
				}
				if (
					dbType === CONF.DB_TYPE.DM ||
					dbType === CONF.DB_TYPE.KINGBASE ||
					dbType === CONF.DB_TYPE.ORACLE
				) {
					nocheck = true;
					isParent = true;
				}
				if (selectRow && selectRow.auth_flag) {  // 已认证
					if (selectRow.cluster_flag && selectRow.cluster_uuid) {
						for (const clusterAppInfo of selectRow.cluster_app_info) {
							if (clusterAppInfo.agent_uuid === row.agent_uuid) {
								listenIp = clusterAppInfo.listen_ip;
							}
						}
					}
				}
				nodes[row.group_uuid + '-' + row.agent_uuid] = {
					id: row.group_uuid + '-' + row.agent_uuid,
					pId: row.group_uuid,
					name,
					title: row.agent_ip,
					group_name: row.group_name,
					group_uuid: row.group_uuid,
					agent_uuid: row.agent_uuid,
					agent_ip: row.agent_ip,
					listen_ip: listenIp,
					hostname: row.hostname,
					alias: row.alias,
					isParent,
					open: false,
					checked,
					nocheck,
					chkDisabled: checked,
					icon: './img/vm/host.png',
					eventtype: 'agent',
					init_load_instance_flag: initLoadInstanceFlag,  // 初始化就展开实例，用于编辑已认证的实例
					load_instance_flag: false,
				};
				if (checked) {
					addListenIp(nodes[row.group_uuid + '-' + row.agent_uuid]);
				}
			}

			zTreeAgent = $.fn.zTree.init(agentTreeTag, getAgentTreeSetting(), Object.values(nodes));


			if (
				dbType === CONF.DB_TYPE.DM ||
				dbType === CONF.DB_TYPE.KINGBASE ||
				dbType === CONF.DB_TYPE.ORACLE
			) {
				// 已认证集群实例加载关联的实例
				let expandNodeList = zTreeAgent.getNodesByParam('init_load_instance_flag', true);
				for (const expandNode of expandNodeList) {
					zTreeAgent.expandNode(expandNode, true, true, true, true);
				}
			}
		});
	};

	/**
	 * 加载客户端的实例节点
	 * @param treeNode
	 * @returns {boolean}
	 */
	const loadAgentInstanceNodes = (treeNode) => {
		let dbType = parseInt($('#appType').val());
		if (
			CONF.DB_TYPE.DM !== dbType &&
			CONF.DB_TYPE.KINGBASE !== dbType &&
			CONF.DB_TYPE.ORACLE !== dbType
		) {
			return true;
		}
		if (treeNode.eventtype !== 'agent' || treeNode.load_instance_flag) {
			return true;
		}

		// 加载代理实例
		let reqData = {
			db_type: dbType,
		};
		Metronic.blockUI({target: '#' + treeNode.tId, animate: true});
		pAjaxRequest(reqData, `/api/v1/agents/${treeNode.agent_uuid}/instances`, 'GET', res => {
			Metronic.unblockUI('#' + treeNode.tId);
			if (!res.success) {
				UIToastr.showWarning(LANG.UI_CLIENT_APP_SCAN_TITLE, res.message);
				return;
			}
			treeNode.load_instance_flag = true;
			zTreeAgent.updateNode(treeNode);

			let nodes = [];
			for (const row of res.data.rows) {
				let checked = false;
				let chkDisabled = false;
				if (row.cluster_flag && row.cluster_uuid === clusterUUID) {
					checked = true;
					chkDisabled = true;
				}

				nodes.push({
					id: row.app_uuid,
					pId: treeNode.id,
					title: row.instance_name,
					name: row.instance_name,
					icon: './img/vm/host.png',
					isParent: false,
					nocheck: false,
					checked,
					chkDisabled,
					agent_uuid: treeNode.agent_uuid,
					agent_ip: treeNode.agent_ip,
					app_uuid: treeNode.app_uuid,
					instance_name: row.instance_name,
					eventtype: 'instance',
				});
			}
			zTreeAgent.addNodes(treeNode, nodes);
			zTreeAgent.expandNode(treeNode, true);
		});
	};

	/**
	 * 集群关联树点击
	 * @param e
	 * @param treeId
	 * @param treeNode
	 */
	const agentCheck = function (e, treeId, treeNode) {
		let dbType = parseInt($('#appType').val());
		if (treeNode.eventtype === 'group') {
			//分组
			for (const childNode of treeNode.children) {
				if (childNode.eventtype === 'agent' && treeNode.checked) {
					addListenIp(childNode);
				} else if (childNode.eventtype === 'agent' && !treeNode.checked) {
					$('#' + childNode.agent_uuid).remove();
				}
			}
		} else if (treeNode.eventtype === 'agent') {
			//主机
			if (treeNode.checked) {
				addListenIp(treeNode);
			} else {
				$('#' + treeNode.agent_uuid).remove();
			}
		} else if (treeNode.eventtype === 'instance') {
			if (
				dbType === CONF.DB_TYPE.DM ||
				dbType === CONF.DB_TYPE.KINGBASE ||
				dbType === CONF.DB_TYPE.ORACLE
			) {
				let parentNode = treeNode.getParentNode();
				let anyCheck = false;
				for (const childNode of parentNode.children) {
					if (childNode.checked) {
						anyCheck = true;
						break;
					}
				}
				if (anyCheck) {
					addListenIp(parentNode);
				} else {
					$('#' + parentNode.agent_uuid).remove();
				}
			}
		}
	};

	//添加监听ip地址
	const addListenIp = function (row) {
		let tag = $(`#${row.agent_uuid}`);
		if (tag.length) {  // 存在了就不加入了
			return;
		}
		let agentName = `${row.alias}(${row.agent_ip})`;
		if (row.alias === row.agent_ip) {
			agentName = `${row.hostname}(${row.agent_ip})`;
		}
		let div = '<div class="mb15" id="' + row.agent_uuid + '"><span>' + agentName + '</span>' +
			'<input type="text" maxlength="128" class="form-control listenIp"  value="' + row.listen_ip + '"/></div>';
		$('#oracleIpList').append(div);
	}

	const nodeSelect = function (treeId, treeNode) {
		if (treeNode.eventtype === 'group') {
			return;
		}
		let dbType = parseInt($('#appType').val());
		if (
			dbType === CONF.DB_TYPE.DM ||
			dbType === CONF.DB_TYPE.KINGBASE ||
			dbType === CONF.DB_TYPE.ORACLE
		) {
			if (treeNode.eventtype === 'agent') {
				zTreeAgent.expandNode(treeNode, true, true, true, true);
			}
		} else {
			zTreeAgent.checkNode(treeNode, !treeNode.checked, true, true);
		}
	};

	/**
	 * 节点展开了
	 * @param treeId
	 * @param treeNode
	 */
	const agentExpand = (treeId, treeNode) => {
		let dbType = parseInt($('#appType').val());
		switch (dbType) {
			case CONF.DB_TYPE.DM:
			case CONF.DB_TYPE.KINGBASE:
			case CONF.DB_TYPE.ORACLE:
				break;
			default:
				return true;
		}
		if (treeNode.eventtype !== 'agent') {
			return true;
		}
		// 加载实例数据
		loadAgentInstanceNodes(treeNode);
		return true;
	};

	//添加当前主机IP展示
	const initClientListenIp = function (name, ip) {
		let agentUuid = $('#clientUUID').val();
		let div = '<div class="mb15" id="' + agentUuid + '"><span>' + name + '</span>' +
			'<input type="text" maxlength="128" class="form-control" id="listenIp" value="' + ip + '"/></div>';
		$('#oracleIpList').append(div);
	};

	const getQueryParams = () => {
		return {};
	};

	const initAppTableHeight = () => {
		let toolbarHeight = 57;
		let paginationHeight = 52;
		let tabTitleHeight = 48;
		let navTitleHeight = 46;
		// page-content有40px的内边距
		// portlet-body有10px的内边距
		// tab-content有20px的外边距离
		let otherHeight = 40 + 10 + 20;
		// 面包屑导航
		let breadHeight = 38;
		let height = window.innerHeight - toolbarHeight - paginationHeight - tabTitleHeight
			- navTitleHeight - otherHeight - breadHeight;

		$("#appconfig .fixed-table-body").css('height', height);
	};

	const formatterAuthType = (value, row) => {
		value = parseInt(value);
		let text = '--';
		switch (parseInt(row.app_type)) {
			case CONF.DB_TYPE.SQLSERVER:
				if (1 === value) {
					text = LANG.UI_CLIENT_APP_SQL_SERVER_AUTH_TYPE1;
				} else {
					text = LANG.UI_CLIENT_APP_SQL_SERVER_AUTH_TYPE2;
				}
				break;
			case CONF.DB_TYPE.ORACLE:
				if (1 === value) {
					text = LANG.UI_CLIENT_APP_ORACLE_AUTH_TYPE1;
				} else {
					text = LANG.UI_CLIENT_APP_ORACLE_AUTH_TYPE2;
				}
				break;
			case CONF.DB_TYPE.MYSQL:
			case CONF.DB_TYPE.MARIA:
				if (1 === value) {
					text = LANG.UI_CLIENT_APP_MYSQL_AUTH_TYPE1;
				} else {
					text = LANG.UI_CLIENT_APP_MYSQL_AUTH_TYPE2;
				}
				break;
		}
		return `<span title="${text}">${text}</span>`;
	};

	const formatterDetailConfig = (value, row) => {
		if (typeof value === 'string') {
			try {
				value = JSON.parse(value);
			} catch (e) {
				value = {};
			}
		}
		let text = '--';
		switch (parseInt(row.app_type)) {
			case CONF.DB_TYPE.SQLSERVER:
				break;
			case CONF.DB_TYPE.ORACLE:
				break;
			case CONF.DB_TYPE.MYSQL:
			case CONF.DB_TYPE.MARIA:
				text = LANG.UI_CLIENT_APP_MYSQL_CONFIG_PATH + ': ' + value.cnf_path;
				break;
			case CONF.DB_TYPE.POSTGRE:
			case CONF.DB_TYPE.ANTDB:
			case CONF.DB_TYPE.KINGBASE:
			case CONF.DB_TYPE.UXDB:
			case CONF.DB_TYPE.HIGHGO:
			case CONF.DB_TYPE.OPENGAUSS:
			case CONF.DB_TYPE.VASTBASE:  // 实例簇路径
				text = LANG.UI_CLIENT_APP_POSTGRES_CLUSTER_PATH + ': ' + value.cluster_path;
				break;
		}
		return `<span title="${text}">${text}</span>`;
	};

	const getAppTableColumns = () => {
		return [{
			checkbox: true,
			width: '2',
			widthUnit: '%',
			sortable: false,
		}, {
			title: LANG.UI_CLIENT_APP_TYPE,
			field: 'app_type',
			width: '8',
			widthUnit: '%',
			formatter: (value) => {
				let text = CONF.DB_DES[parseInt(value)];
				return `<span title="${text}">${text}</span>`;
			},
		}, {
			title: LANG.UI_CLIENT_APP_VERSION,
			field: 'app_version',
			width: '15',
			widthUnit: '%',
		}, {
			title: LANG.UI_DB_INSTANCE_NAME,
			field: 'app_name',
			width: '10',
			widthUnit: '%',
		}, {
			title: LANG.UI_CLIENT_APP_LOGIN_NAME,
			field: 'app_username',
			width: '8',
			widthUnit: '%',
		}, {
			title: LANG.UI_CLIENT_APP_AUTH_TYPE,
			field: 'app_auth_type',
			width: '10',
			widthUnit: '%',
			formatter: formatterAuthType,
		}, {
			title: LANG.UI_PUBLIC_ADD_TIME,
			field: 'register_time',
			width: '10',
			widthUnit: '%',
		}, {
			title: LANG.UI_CLIENT_APP_DETAIL_CONFIG,
			field: 'app_detail',
			width: '15',
			widthUnit: '%',
			sortable: false,
			formatter: formatterDetailConfig,
		}];
	};

	/**
	 * 设置选中事件
	 */
	const checkEvent = function (tableId, btnId) {
		let select = $('' + tableId + '').bootstrapTable('getSelections');
		if (select.length == 0) {
			$('' + btnId + ' i').addClass('icon-gray-delete');
			$('' + btnId + ' i').removeClass('icon-white-delete');
			$('' + btnId + '').removeClass('select-delete-btn');
			$('' + btnId + '').addClass('cancel-delete-btn');
		} else {
			$('' + btnId + ' i').removeClass('icon-gray-delete');
			$('' + btnId + ' i').addClass('icon-white-delete');
			$('' + btnId + '').removeClass('cancel-delete-btn');
			$('' + btnId + '').addClass('select-delete-btn');
		}
	};

	const appRowCheck = () => {
		checkEvent('#applicationTable', '#deleteApp');
	};

	/**
	 * 自定义按钮
	 */
	const customAppTool = () => {
		let beforeInput = ``;
		if (CONF.PERMISSION_ARR.includes('p_agent_manager_application_config')) {
			beforeInput += `
			<button type="button" id="deleteApp" class="b-btn brr2 mr12 table-toolbar-btn">
                <i class="icon-gray-delete"></i>
            </button>
			`;
		}
		let afterInput = ``;
		if (CONF.PERMISSION_ARR.includes('p_agent_manager_application_config')) {
			afterInput += `
			<button class="dropdown-toggle btn-font flex_center btn-title p-lr8 btn table-toolbar-btn" id="addApp"
				style="width: auto; height: 34px; border: 0">
				<i class="viconfont vicon-biaogetianjia"></i>
				<span class="pl2">${LANG.UI_CLIENT_APP_ADD_TITLE}</span>
			</button>
			`;
		}
		return {beforeInput, afterInput};
	};

	const getAppTableOption = () => {
		return {
			vin_url: `/api/v1/agents/${$('#clientUUID').val()}/applications`,
			vin_params: getQueryParams,
			vin_method: 'get',
			buttonsToolbar: '.vin_btnAppToolbar', // 自定义按钮工具栏class
			rightToolbarClass: 'rightTool',
			tableContentWrapper: '#vin_app_toolbar',
			toolbarId: '#vin_app_toolbar',
			vin_toolbar: '#vin_app_toolbar',
			// 排序
			sortName: 'register_time',
			sortOrder: 'desc',
			// 隐藏行
			hideColumns: '',
			showButtonText: false,
			clickToSelect: true,
			pagination: true,
			pageList: [10, 20, 50, 100, 150, 200],
			pageSize: 20,
			resizable: true,
			showRefresh: false,
			showExport: false,
			onResetView: initAppTableHeight,
			onRefresh: () => {
				$("#applicationTable").bootstrapTable('hideLoading');
			},
			onPostBody: () => {
				appRowCheck();
			},
			onCheck: appRowCheck,
			onUncheck: appRowCheck,
			onCheckAll: appRowCheck,
			onUncheckAll: appRowCheck,
			customTool: customAppTool(),
			columns: getAppTableColumns(),
		};
	};

	const initAppTable = () => {
		// 清除分页缓存
		window.sessionStorage.removeItem('applicationTable_pageRecord');
		$('#applicationTable').bootstrapTable('destroy').baseTableConfig().init(getAppTableOption())
	};

	/**
	 * 初始化应用类型
	 */
	const initAppType = () => {
		pAjaxRequest({}, `/api/v1/agents/app/type`, 'GET', res => {
			if (!res.success) {
				UIToastr.showWarning(LANG.UI_DB_AGENT_INSTANCE_AUTH, res.message);
				return;
			}
			let options = `<option value="0">${LANG.UI_CLIENT_APP_SELECT}</option>`;
			for (const appTypeInfo of res.data.rows) {
				options += `<option value="${appTypeInfo.app_type}">${appTypeInfo.app_type_name}</option>`;
			}
			$('#appType').html(options);
		});
	};

	return {
		//main function to initiate the module
		init: function () {
			console.log('application-config');  // 这里打印是为了方便debug
			addListeners();
			initAppTable();
			wizardInit(); //初始化执行步骤插件
			initAppType();
		}
	};
}();

jQuery(document).ready(function () {
	ApplicationConfig.init();
});
