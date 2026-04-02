/*
 * @note: 
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-04-14 10:14:17
 * @LastEditTime: 2025-05-09 10:31:07
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */
//手动备份
var System_Br_Oncebak = function () {
	let zTree;
	const addListeners = function () {
		$("#oncebakcancel").click(function () {
			LOCATION('./content/platform/settings/settingstab/system_br.php', 'setting_manager');
		});

		$("#oncebaksubmit").on('click', submit);

	}
	/**
	 * 手动备份提交
	 */
	const submit = function () {
		let allNodes = zTree.getCheckedNodes(true);
		if (allNodes.length == 0) {
			return UIToastr.showWarning(LANG.UI_BR_ONCEBAK, LANG.UI_BR_ONCEBAK_SELECT_CONTENT);
		}
		let nodes = [];
		for (let i = 0; i < allNodes.length; i++) {
			nodes.push(allNodes[i].id);
		}
		UIToastr.showInfo(LANG.UI_BR_ONCEBAK, LANG.UI_BR_ONCEBAK_NO_LEAVE + '</br>' + LANG.UI_BR_ONCEBAK_AUTO_DOWNLOAD);
		Metronic.blockUI({target: '#oncebakform',animate: true,cenrerY: true,});
		// 获取下载链接
		pAjaxRequest({ "nodes": nodes }, "/api/v1/system/backup_manual", "POST", function (res) {
			Metronic.unblockUI('#oncebakform');
			if (operateResponseList(res)) {
				pAjaxRequest({ 'filepath': res.data.info.file_path }, '/api/v1/system/generate/download', 'GET', function (res) {
					if ("#" == res.data.url) {
						return;
					}
					window.location.href = res.data.url;
				});
			}
		}, true);
	}
	const nodeSelect = function (treeId, treeNode, clickFlag) {
		let treeObj = $.fn.zTree.getZTreeObj(treeId);
		treeObj.checkNode(treeNode, !treeNode.checked, true);
	}

	//初始化权限树
	const initBakTree = function (res) {
		if (!res.success) {
			return;
		}
		const setting = {
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
	/**
	 * 初始化树
	 */
	const initViews = function () {
		pAjaxRequest({}, "/api/v1/system/backup_tree", "GET", initBakTree, true);

	};

	return {
		init: function () {
			//添加事件
			addListeners();
			initViews();
		}
	};
}();

jQuery(document).ready(function () {
	System_Br_Oncebak.init();
});