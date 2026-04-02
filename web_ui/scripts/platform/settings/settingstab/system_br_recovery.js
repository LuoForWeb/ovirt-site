//系统恢复
var System_Br_Recovery = function () {
	let grid, zTree;
	let uploadFlag = false;
	/**
	 * 监听事件
	 */
	const addListeners = function () {

		$('#rctype').on('change', function () {
			$('.contentdiv').hide();
			let value = $('#rctype').val();
			if ("" == value) {

			} else if ("0" == value) {
				//自动备份数据源
				$('#tablediv').show();
			} else if ("1" == value) {
				//手动上传数据源
				$('#uploaddiv').show();
			}
		});
		// 开始上传
		$('#startupload').on('click', function () {
			event.preventDefault();
		});
		// 返回上一级
		$("#rccancel").click(function () {
			LOCATION('./content/platform/settings/settingstab/system_br.php', 'setting_manager');
		});
		// 恢复确认
		$("#rcsubmit").on('click', submit);
		// 关闭恢复页面，清除定时器
		$('#cancelRecBtn').on('click', function () {
			$('#recModal').modal('hide');
			clearTimeout(timerTask.SystemRecovery_RunningLog);
		})
	}
	// 确认恢复
	const submit = function () {
		let data = {};
		data.srctype = $('#rctype').val();
		// 必选选择恢复类型
		if ("" == data.srctype) {
			return UIToastr.showInfo(LANG.UI_BR_RECOVERY_SELECT_DR, LANG.UI_BR_RECOVERY_SELECT_TYPE);
		}
		if ("0" == data.srctype) {
			//自动备份恢复源
			let select = $('#system_backup_point').bootstrapTable('getSelections')
			if (0 == select.length) {
				return UIToastr.showWarning(LANG.UI_BR_RECOVERY_SELECT_RS, LANG.UI_BR_RECOVERY_SELECT_DATA);
			}
			if(1 > select.length) {
				return UIToastr.showWarning(LANG.UI_BR_RECOVERY_SELECT_RS, LANG.UI_BR_RECOVERY_SELECT_RECOVERY_FILE);
			}
			data.uuid = select[0].id;

		} else if ("1" == data.srctype) {
			//手动上传恢复源
			if (!uploadFlag) {
				return UIToastr.showWarning(LANG.UI_BR_RECOVERY_UPLOAD, LANG.UI_BR_RECOVERY_PLEASE_UPLOAD);
			}
		}
		// 获取恢复内容
		let allNodes = zTree.getCheckedNodes(true);
		if (allNodes.length == 0) {
			return UIToastr.showWarning(LANG.UI_BR_RECOVERY, LANG.UI_BR_RECOVERY_SELECT_PROJECT);
		}
		let nodes = [];
		for (let i = 0; i < allNodes.length; i++) {
			nodes.push(allNodes[i].id);
		}
		data.nodes = nodes;
		pAjaxRequest(data, '/api/v1/system/backup/recovery_check', 'POST', function (res) {
			if(res.success){
				// 检查成功
				bootbox.confirm({
					title: LANG.UI_BR_RECOVERY,
					message: LANG.UI_BR_RECOVERY_CONFIRM,
					callback: function (r) {
						if (!r) return;
						startRecoverySystem(data);
					}
				});
			} else {
				operateResponseList(res)
			}
		});
	}


	//开始系统恢复
	const startRecoverySystem = function (data) {
		$('#recModal').modal({ 'width': '630px', 'height': '500px' });
		$('.modalalert').hide();
		$('#recAlarm').show();
		pAjaxRequest(data, '/api/v1/system/backup/recovery', 'POST', function (res) {
			$('.modalalert').hide();
			if(res.success){
				//恢复成功
				$('#recSuccess').show();
			}else{
				//恢复失败
				$('#recError').show();
			}

		}, true);
		Metronic.blockUI({ target: '#recModal', animate: true, cenrerY: true, });
		// 更新恢复日志
		updateLog(data);
	}

	//更新恢复日志
	const updateLog = function (data) {
		let updateInterval = 2000;
		let init = function () {
			if (0 == $('#recModal').size()) {
				clearTimeout(timerTask.SystemRecovery_RunningLog);
				return;
			}
			pAjaxRequest(data, '/api/v1/system/backup/recovery_progress', 'POST', setRecoveryInfo, true);
			timerTask.SystemRecovery_RunningLog = setTimeout(init, updateInterval);
		}
		init();
	}

	const getIcon = function (level) {
		if (1 == level) {
			//文件
			icon = '<div class="label label-sm label-success"><i class="fa fa-check"></i></div>';
		} else if (0 == level) {
			icon = '<div class="label label-sm label-danger"><i class="fa fa-times"></i></div>';
		}
		return icon;
	}

	//设置恢复信息
	const setRecoveryInfo = function (res) {
		Metronic.unblockUI('#recModal');
		let data = res.data;
		let info = "";
		for (let i = 0; i < data.length; i++) {
			info += '<li><div class="col1"><div class="cont contdetail"><div class="cont-col1">' +
				getIcon(data[i][0]) + '</div><div class="cont-col2"><div class="desc">' + data[i][2] + '</div>' +
				'</div></div></div><div class="col2 logtimecol"><div class="date">' + data[i][1] + '</div></div></li>';
		}
		if (0 == data.length) {
			info = '<li><div class="col textalignc">' + LANG.UI_TOOLS_NO_DATA + '</div></li>';
		}
		$('#runninglog').html(info);
	}
	/**
	 * 初始化自动恢复源表格
	 */
	const initTable = function () {
		let options = {
			vin_url: "/api/v1/system/backup_list",
			vin_method: "GET",
			vin_params: function () {
				return {};
			},
			sortName: 'file_size',
			sortOrder: 'desc',
			changeHeightBtn: true, //改变高度按钮
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			checkboxHeader: false, // 隐藏全选复选框
			onCheck: (row) => {
				// 显示配置
				$('#configdiv').show();
				// 只能勾选一个恢复源
				let data = $('#system_backup_point').bootstrapTable('getData');
				for(let i = 0; i < data.length; i++){
					if (data[i].id !== row.id) { // 确保不是自己
						$('#system_backup_point').bootstrapTable('uncheckBy', {
							field: 'id',
							values: [data[i].id]
						});
					}
				}
				Metronic.blockUI({ target: '#rectree', animate: true, cenrerY: true, });
				pAjaxRequest({uuid: row.id}, "/api/v1/system/backup/recovery_auto_tree", "GET", initRecTree, true);
			},
			columns: [
				{
					field: 'checked',
					checkbox: true,
					sortable: false,
				},
				{
					field: 'file_name',
					title: LANG.UI_LOG_GET_DOWNLOAD_LOG_LIST_PACK_NAME,
				},
				{
					field: 'file_size',
					title: LANG.UI_LOG_GET_DOWNLOAD_LOG_LIST_PACK_SIZE,
				},
				{
					field: 'backup_time',
					title: LANG.UI_BR_RECOVERY_TABEL_LABEL_BACKUP_TIME,
				},
			],
		}
		$('#system_backup_point').baseTableConfig().init(options);
	}

	const nodeSelect = function (treeId, treeNode, clickFlag) {
		let treeObj = $.fn.zTree.getZTreeObj(treeId);
		treeObj.checkNode(treeNode, !treeNode.checked, true);
	}

	//初始化权限树
	const initRecTree = function (res) {
		Metronic.unblockUI('#rectree');
		if (!res.success) {
			return;
		}
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

		zTree = $.fn.zTree.init($("#rectree"), setting, res.data);
	}

	//初始化上传插件
	const initUploader = function () {
		let $list = $('#thelist');
		let uploader = WebUploader.create({
			//设置选完文件后是否自动上传
			auto: false,
			//swf文件路径
            swf: './assets/global/plugins/web-uploader/Uploader.swf',
			// 文件接收服务端。
            server: '/api/v1/system/backup_upload_file?x-api-version=1.0-rev0',
			// 选择文件的按钮。可选。
			// 内部根据当前运行是创建，可能是input元素，也可能是flash.
			pick: '#picker',
			accept: {
				title: 'system recovery package file',
				extensions: 'bak',
				mimeTypes: ''
			},
			chunked: true, //分片上传大文件
			chunkSize: 5 * 1024 * 1024,
			chunkRetry: 3,//如果某个分片由于网络问题出错，允许自动重传多少次
			resize: false//不压缩
		});
		//当文件被添加到队列之前
		uploader.on('beforeFileQueued', function (file) {
			// 上传之前先清理文件夹
			pAjaxRequest({}, "/api/v1/system/backup/clean_dir", "GET", function(res){
			}, true);
			let name = $.trim(file.name);
			let txt = name.substring(name.length - 3, name.length); //只允许.bak添加
			if (txt != "bak") {
				UIToastr.showWarning(LANG.UI_BR_RECOVERY_UPLOAD, LANG.UI_SETTING_CHECK_FILE_LEGAL);
				return false;
			}
		});

		// 当有文件被添加进队列的时候
		uploader.on('fileQueued', function (file) {
			$list.empty();
			$list.append('<div id="' + file.id + '" class="item">' +
				'<h4 class="info">' + file.name + '<a id="delete' + file.id + '" style="margin-left:20px;font-size:14px;">' + LANG.UI_PUBLIC_DELETE + '</a></h4>' +
				'<p class="state">' + LANG.UI_SETTINGS_UPDATE_WAIT_UPLOAD + '</p>' +
				'</div>');
			$('#delete' + file.id).on('click', function () {
				$('#' + file.id).remove();
				uploader.removeFile(uploader.getFile(file.id), true); //把对应文件从队列中删除
			});

		});
		$('#startupload').on('click', function () {
			uploader.upload();
		});

		// 文件上传过程中创建进度条实时显示。
		uploader.on('uploadProgress', function (file, percentage) {
			let $li = $('#' + file.id),
				$percent = $li.find('.progress .progress-bar');

			// 避免重复创建
			if (!$percent.length) {
				$percent = $('<div class="progress progress-striped active">' +
					'<div class="progress-bar" role="progressbar" style="width: 0%">' +
					'</div>' +
					'</div>').appendTo($li).find('.progress-bar');
			}

			$li.find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOADING);
			$percent.css('width', percentage * 100 + '%');
		});

		//文件上传成功回调
		uploader.on('uploadSuccess', function (file, res) {
			if (!res.success) {
				$('#' + file.id).find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOAD_ERROR + "(" + res.msg + ")");
				uploadFlag = false;
				return;
			}

			$('#' + file.id).find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOAD_SUCCESS);
			uploadFlag = true;

			$('#configdiv').show();
			//请求恢复项
			Metronic.blockUI({ target: '#rectree', animate: true, cenrerY: true, });
			pAjaxRequest({}, "/api/v1/system/backup/recovery_tree", "GET", initRecTree, true);

		});

		//文件上传错误回调
		uploader.on('uploadError', function (file, res) {
			$('#' + file.id).find('p.state').text(LANG.UI_SETTINGS_UPDATE_UPLOAD_ERROR + "(" + res.msg + ")");
			$('#' + file.id).find('.progress').remove();
			uploadFlag = false;
		});

		//文件上传完回调
		uploader.on('uploadComplete', function (file) {
			$('#' + file.id).find('.progress').fadeOut();
			$('.close').on('click', function () {
				uploader.reset();
				$('#thelist').empty();
			});
		});


	}

	return {
		init: function () {
			initUploader();
			//添加事件
			addListeners();
			initTable();
		}
	};
}();

jQuery(document).ready(function () {
	System_Br_Recovery.init();
});