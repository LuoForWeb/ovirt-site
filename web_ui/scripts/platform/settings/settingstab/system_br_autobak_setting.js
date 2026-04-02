/*
 * @note: 自动备份策略配置
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-04-14 10:14:17
 * @LastEditTime: 2025-06-23 19:49:44
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */
//自动备份配置
var System_Br_Autobak_Setting = function () {
	let zTree;
	let initOldFlag = false;
	// 初始化时间策略
	const loadTimepicker = function () {
		$('.backupTime').timepicker({
			autoclose: true,
			minuteStep: 5,
			showSeconds: true,
			showMeridian: false,
		});
		$('.backupTime').parent('.input-group').on('click', '.input-group-btn', function (e) {
			e.preventDefault();
			$(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
		});
	}
	/**
	 * 点击事件监听
	 */
	const addListeners = function () {
		// 返回上一级
		$("#autobakcancel").click(function () {
			LOCATION('./content/platform/settings/settingstab/system_br.php', 'setting_manager');
		});
		// 提交配置
		$("#autobaksubmit").on('click', submitCheck);
		// 自动备份配置开关
		$('#autoBakCheck').on('switchChange.bootstrapSwitch', function () {
			if (this.checked) {
				$('.autodiv').show();
			} else {
				$('.autodiv').hide();
			}
		});
		// 初始化树
		initTree();
		// 初始化保留个数
		$('#spinnerNum').spinner({ value: 30, step: 5, min: 1, max: 1000 });
	}

	// 统一提交配置
	const submitPost = function (data) {
		pAjaxRequest(data, "/api/v1/system/backup_auto", "POST", function (res) {
			operateResponseList(res);
		});
	}

	/**
	 * 获取配置内容
	 * @returns void
	 */
	const submitCheck = function () {
		let auto_flag = $('#autoBakCheck').get(0).checked;
		let data = { "auto_flag": auto_flag };
		if (!auto_flag) {
			//如果是关闭配置,直接提交,不检查其他内容
			submitPost(data);
			return true;
		}
		// 如果开启配置，必须勾选备份内容
		let allNodes = zTree.getCheckedNodes(true);
		if (allNodes.length == 0) {
			return UIToastr.showWarning(LANG.UI_BR_AUTOBAK_SETTING, LANG.UI_BR_ONCEBAK_SELECT_CONTENT);
		}

		let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
		let backup_time = $('.backupTime').val();
		let reserved_num = $('#spinnerNumInput').val();
		let node_uuid = backupTargetInfo.node_uuid;
		let storage_uuid = backupTargetInfo.storage_uuid;
		if (!backup_time) {
			return UIToastr.showWarning(LANG.UI_BR_AUTOBAK_SETTING, LANG.UI_BR_AUTOBAK_BACKUP_TIME);
		}
		if (!reserved_num || reserved_num == 0) {
			return UIToastr.showWarning(LANG.UI_BR_AUTOBAK_SETTING, LANG.UI_BR_AUTOBAK_RESERVE_NUM);
		}
		if (!node_uuid) {
			return UIToastr.showWarning(LANG.UI_BR_AUTOBAK_SETTING, LANG.UI_BR_AUTOBAK_BACKUP_NODE);
		}
		if (!storage_uuid) {
			return UIToastr.showWarning(LANG.UI_BR_AUTOBAK_SETTING, LANG.UI_BR_AUTOBAK_BACKUP_STORAGE);
		}
		data.backup_time = backup_time;
		data.reserved_num = reserved_num;
		data.node_uuid = node_uuid;
		data.storage_uuid = storage_uuid;

		let nodes = [];
		for (let i = 0; i < allNodes.length; i++) {
			nodes.push(allNodes[i].id);
		}
		data.nodes = nodes;
		submitPost(data);
	}
	/**
	 * 初始化树
	 */
	const initTree = function () {
		pAjaxRequest({}, "/api/v1/system/backup_tree", "GET", initBakTree, true);

	};

	const nodeSelect = function (treeId, treeNode, clickFlag) {
		let treeObj = $.fn.zTree.getZTreeObj(treeId);
		treeObj.checkNode(treeNode, !treeNode.checked, true);
	}

	// 初始化权限树
	const initBakTree = function (res) {
		let setting = {
			check: {
				enable: true,
				nocheckInherit: false,
				chkStyle: "checkbox"
			},
			data: {
				simpleData: {
					enable: true,
					idKey: "id",
					pIdKey: "pid",
					rootPId: 0
				},
				key: {
					title: "title"
				}
			},
			view: {
				showIcon: false,
				nameIsHTML: true
			},
			callback: {
				beforeClick: nodeSelect,
			}
		};
		zTree = $.fn.zTree.init($("#baktree"), setting, res.data);
	}

	//设置之前配置的每一项
	const setOldData = function (data) {
		$('.autodiv').show();
		$('#autoBakCheck').bootstrapSwitch('state', data.auto_flag);
		$('.backupTime').val(data.backup_time);
		$('#spinnerNumInput').val(data.reserved_num);
		//因为默认所有都选中了的,这里检查一下,只需要取消不在配置里面的就行
		let nodes = zTree.transformToArray(zTree.getNodes());
		for (let i = 0; i < nodes.length; i++) {
			if (-1 == $.inArray(nodes[i].id, data.nodes)) {
				zTree.checkNode(nodes[i], false, false);
			}
		}
		initOldFlag = true;
		initTargetConfig(data);
		loadTimepicker();
	}

	// 初始化配置
	const initOldInfo = function () {
		// 如果未配置，则默认初始化
		if (initOldFlag) {
			initTargetConfig();
			return;
		};
		Metronic.blockUI({ target: '#setautobakform .form-body-wrapper', animate: true });
		pAjaxRequest({}, "/api/v1/system/backup/auto_config", "GET", function(res){
			Metronic.unblockUI('#setautobakform .form-body-wrapper');
				if (res.data.auto_flag) {
					//如果是开启备份,才设置,因为关闭的时候不用设置
					setOldData(res.data);
				} else {
					initTargetConfig();
					loadTimepicker();
				}
		}, true);
	};
	/**
	 * 初始化目标配置
	 * @param {} data 
	 */
	const initTargetConfig = (data = null) => {
		let params = {
			exclude_storage_type_list: [CONF.BD_STORAGE_TYPE.TAPE, CONF.BD_STORAGE_TYPE.CLOUD, CONF.BD_STORAGE_TYPE.REMOTE],
			hide_node_pool_flag: true,
			hide_storage_pool_flag: true,
		};
		if(data){
			params.node_uuid = data.node_uuid;
			params.storage_uuid = data.storage_uuid;
		}
		$('#backupTarget').backupTarget(params);
	};

	return {
		init: function () {
			//添加事件
			addListeners();
			initOldInfo();
		}
	};
}();

jQuery(document).ready(function () {
	System_Br_Autobak_Setting.init();
});