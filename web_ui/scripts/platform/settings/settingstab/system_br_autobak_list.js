/*
 * @note: 自动备份数据列表
 * @author: chenyunfeng@vinchin.com
 * @Description: 
 * @Date: 2025-04-14 10:14:17
 * @LastEditTime: 2026-03-13 18:08:56
 * @Version: 2.0
 * @copyright: Copyright 2025 vinchin.com
 */
/**
 * 初始化备份点列表
 */
var System_Br_Autobak_List = function () {
	let changeHeightFlag = false;
	const handleRecords = function () {
		let options = {
			vin_url: "/api/v1/system/backup/list",
			vin_method: "get",
			vin_params: function () {
				let params = {};
				if ($('.searchFileName').val() !== '') {
					params.search = $('#autoBackTableToolbar .searchFileName').val();
				}
				return { ...params };
			},
			tableArea: '.data_list-container',
			showColumns: true,
			sortName: 'backup_time',
			sortOrder: 'desc',
			toolbarId: '#autoBackTableToolbar',
			buttonsToolbar: '#autoBackTableToolbar .vin_autoBackTableToolbar',
			changeHeightBtn: true, //改变高度按钮
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			searchInput: false, //搜索框
			// 勾选框联动效果，修改批量操作按钮状态
			onCheck: () => {
				modifyDelStyle('bakDataTable', 'deletePoint');
				modifyDelStyle('bakDataTable', 'downloadFile');
			},
			onUncheck: () => {
				modifyDelStyle('bakDataTable', 'deletePoint');
				modifyDelStyle('bakDataTable', 'downloadFile');
			},
			onUncheckAll: () => {
				modifyDelStyle('bakDataTable', 'deletePoint');
				modifyDelStyle('bakDataTable', 'downloadFile');
			},
			onCheckAll: () => {
				modifyDelStyle('bakDataTable', 'deletePoint');
				modifyDelStyle('bakDataTable', 'downloadFile');
			},
			PostBody: () => {
				modifyDelStyle('bakDataTable', 'deletePoint');
				modifyDelStyle('bakDataTable', 'downloadFile');
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
					title: LANG.UI_BACKUP_DATA_VRIUS_TABLE_HEAD_LABEL_SIZE,
				},
				{
					field: 'backup_time',
					title: LANG.UI_BR_RECOVERY_TABEL_LABEL_BACKUP_TIME,
				},
				{
					field: 'node_ip',
					title: LANG.UI_BACKUP_NODE,
					sortable: false,
				},
				{
					field: 'storage',
					title: LANG.UI_REPORT_STORAGE,
					sortable: false,
				},
			],
		}
		// 初始化表格
		$('#bakDataTable').baseTableConfig().init(options);
		initListener();
	}
	/**
	 * 表格操作监听
	 */
	const initListener = function () {
		// 删除
		$('#deletePoint').on('click', deletePointList);
		// 下载文件
		$('#downloadFile').on('click', backDataDownload);
		// 搜索
		$('.searchFileName').keypress(function (e) {
			if (e.which == 13) {
				$('#bakDataTable').bootstrapTable('refresh');
			}
		});
		$('.searchFileName').on('focus', function (e) {
			// 如果输入框有内容，或者获得了焦点（即使内容为空）
			if ($(this).val().trim() !== '' || $(this).is(':focus')) {
				$('.clear').removeClass('hide');
			} else {
				$('.clear').addClass('hide');
			}
		});
		// 当输入框失去焦点时，如果内容为空，可以隐藏 clear 按钮
		$('.searchFileName').on('blur', function () {
			if ($(this).val().trim() === '') {
				$('.clear').addClass('hide');
			}
		});
		// 刷新表格
		$('#refreshTable').on('click', function () {
			$('#bakDataTable').bootstrapTable('refresh');
		})
		// 搜索名称
		$('#search_auto_btn').on('click', function () {
			$('#bakDataTable').bootstrapTable('refresh');
		});
		// 清空搜索
		$('.auto_back_clear').on('click', function () {
			$('.searchFileName').val('');
			$('#bakDataTable').bootstrapTable('refresh');
		});
		// 改变表格高度
		$('.change_height').on('click', changeHeight);
	}

	// 改变表格高度
	let changeHeight = function () {
		if (changeHeightFlag == false) {
			changeHeightFlag = true;
			$('#bakDataTable>tbody>tr>td').css({
				'padding-top': '15.25px',
				'padding-bottom': '15.25px'
			})
			$('#autoBackTableToolbar .change_height i').addClass('icon-auto-height2');
		} else if (changeHeightFlag == true) {
			changeHeightFlag = false
			$('#bakDataTable>tbody>tr>td').css({
				'padding-top': '4.25px',
				'padding-bottom': '4.25px'
			})
			$('#autoBackTableToolbar .change_height i').removeClass('icon-auto-height2');
		}
	}	
	/**
	 * 删除
	 */
	const deletePointList = function () {
		// 获取删除数据的id
		let backupIdList = $.map($('#bakDataTable').bootstrapTable('getSelections'), function (row) {
			return row.id;
		})
		// 删除确认框
		bootbox.confirm({
			title: LANG.UI_BR_AUTOBAK_REMOVE_FILE,
			message: LANG.UI_BR_AUTOBAK_CONFIRM_REMOVE,
			callback: function (r) {
				if (!r) return;
				Metronic.blockUI({ target: '#autobaklist', animate: true });
				pAjaxRequest({ 'uuid': backupIdList }, '/api/v1/system/backup/list', 'DELETE', function (res) {
					Metronic.unblockUI('#autobaklist')
					if (operateResponseList(res)) {
						$('#bakDataTable').baseTableConfig('refresh');
					}
				});
			}
		});
	}
	//下载
	const backDataDownload = function () {
		// 获取勾选数据信息
		let select = $('#bakDataTable').bootstrapTable('getSelections');
		// 只能下载一个文件
		if (1 != select.length) {
			return UIToastr.showInfo(LANG.UI_BR_AUTOBAK_DOWNLOAD_FILE, LANG.UI_BR_AUTOBAK_SELECT_DOWNLOAD_FILE);
		}
		// 获取下载链接
		pAjaxRequest({ 'uuid': select[0].id }, '/api/v1/system/backup_list_download', 'GET', function (res) {
			if ("#" == res.data.data.url) {
				return;
			}
			window.location.href = res.data.data.url;
		});
	}

	return {
		init: function () {
			handleRecords();
		}
	};
}();

jQuery(document).ready(function () {
	System_Br_Autobak_List.init();
});