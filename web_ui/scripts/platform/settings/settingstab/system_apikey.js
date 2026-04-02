var Settings_APIKEY = function () {
	let keyData = [];

	/**
	 * 初始化表格
	 */
	const initDataTable = () => {
		//初始化表格参数
		let options = {
			vin_url: "/api/v1/system/apikey",
			vin_method: "get",
			hangeHeightBtn: true, //改变高度按钮
			resizable: true, //可变宽度
			pagination:true,
			pageList: [5,10,25,50],
			searchInput: false, // 搜索框
			search: false,
			onCheck: function (row) {
				modifyDelStyle('system_apikey_table', 'delete');
				keyData.push(row.user_uuid);
			},
			onUncheck: function (row) {
				modifyDelStyle('system_apikey_table', 'delete');
				var index = keyData.indexOf(row.id); // 查找元素的索引
				if (index !== -1) {
					keyData.splice(index, 1); // 从数组中删除一个元素
				}
			},
			onCheckAll:function (row) {
				modifyDelStyle('system_apikey_table', 'delete');
				for (var i = 0; i < row.length; i++) {
					var index = keyData.indexOf(row[i].id); // 查找元素的索引
					if (index == -1) {
						keyData.push(row[i].id)
					}
				}
			},
			onUncheckAll: function (row) {
				modifyDelStyle('system_apikey_table', 'delete');
				for (var i = 0; i < row.length; i++) {
					var index = keyData.indexOf(row[i].id); // 查找元素的索引
					if (index != -1) {
						keyData.splice(index, 1); // 从数组中删除一个元素
					}
				}
			},
			columns: [
				{
					checkbox: true,
					sortable: false,
					formatter: function (value, row, index, field) {
						for(var i=0; i<keyData.length; i++) {

							if(row.id == keyData[i]){
								return true
							}
						}
					}
				},
				{
					field: 'apikey',
					title: 'Apikey',
					sortable: true,
					align: 'left'
				},
				{
					field: 'create_time',
					title: LANG.UI_TAPE_GENERATE_TIME,
					sortable: true,
					align: 'left'
				},
				{
					field: 'lock_flag',
					title: LANG.UI_PUBLIC_STATUS,
					sortable: true,
					align: 'left',
					formatter: function(value, row){
						return row['lock_flag_des'];
					}
				}

			]
		};

		$('#system_apikey_table').baseTableConfig().init(options);
	};

	const initListener = function(){
		//生成apikey
		$('#add').on('click', function(){
			bootbox.confirm({
				title: LANG.UI_SETTINGS_APIKEY_CREATE,
				message: LANG.UI_SETTINGS_APIKEY_CREATE_TIPS,
				callback: function (r) {
					if (!r) return;
					let data = {};
					Metronic.blockUI({
						target: '.page-content',
						animate: true
					});
					pAjaxRequest(data, '/api/v1/system/apikey', 'post', function(result) {
						Metronic.unblockUI('.page-content');
						//刷新表格
						let op = LANG.UI_SETTINGS_APIKEY_CREATE;
						if (operateResponseList(result, op)) {
							$('#system_apikey_table').bootstrapTable('refresh');
						}
					});
				}
			});
		});

		//删除apikey
		$('#delete').on('click', function(){
			let selectRows = $('#system_apikey_table').bootstrapTable('getSelections');
			if (!selectRows.length) {
				return UIToastr.showInfo(LANG.UI_SETTINGS_APIKEY_DELETE, LANG.UI_SETTINGS_APIKEY_DELETE_SELECT_TIPS);
			}
			bootbox.confirm({
				title: LANG.UI_SETTINGS_APIKEY_DELETE,
				message: LANG.UI_SETTINGS_APIKEY_DELETE_CONFIRM_TIPS,
				callback: function (r) {
					if (!r) return;
					let data = {};
					data['apikey_ids'] = [];
					for (let i=0;i<selectRows.length;i++){
						data['apikey_ids'].push(selectRows[i]['id']);
					}
					Metronic.blockUI({
						target: '.page-content',
						animate: true
					});
					pAjaxRequest(data, '/api/v1/system/apikey', 'delete', function(result) {
						Metronic.unblockUI('.page-content');
						//刷新表格
						let op = LANG.UI_SETTINGS_APIKEY_DELETE;
						if (operateResponseList(result, op)) {
							$('#system_apikey_table').bootstrapTable('refresh');
						}
					});
				}
			});
		});

		//禁用apikey
		$('#lock').on('click', function(){
			let selectRows = $('#system_apikey_table').bootstrapTable('getSelections');
			if (!selectRows.length) {
				return UIToastr.showInfo(LANG.UI_SETTINGS_APIKEY_DISABLE, LANG.UI_SETTINGS_APIKEY_DISABLE_SELECT_TIPS);
			}
			bootbox.confirm({
				title: LANG.UI_SETTINGS_APIKEY_DISABLE,
				message: LANG.UI_SETTINGS_APIKEY_DISABLE_CONFIRM_TIPS,
				callback: function (r) {
					if (!r) return;
					let data = {};
					data['apikey_ids'] = [];
					for (let i=0;i<selectRows.length;i++){
						data['apikey_ids'].push(selectRows[i]['id']);
					}
					Metronic.blockUI({
						target: '.page-content',
						animate: true
					});
					pAjaxRequest(data, '/api/v1/system/apikey_lock', 'put', function(result) {
						Metronic.unblockUI('.page-content');
						//刷新表格
						let op = LANG.UI_SETTINGS_APIKEY_DISABLE;
						if (operateResponseList(result, op)) {
							$('#system_apikey_table').bootstrapTable('refresh');
						}
					});
				}
			});
		});

		//启用apikey
		$('#unlock').on('click', function(){
			let selectRows = $('#system_apikey_table').bootstrapTable('getSelections');
			if (!selectRows.length) {
				return UIToastr.showInfo(LANG.UI_SETTINGS_APIKEY_ENABLE, LANG.UI_SETTINGS_APIKEY_ENABLE_SELECT_TIPS);
			}
			bootbox.confirm({
				title: LANG.UI_SETTINGS_APIKEY_ENABLE,
				message: LANG.UI_SETTINGS_APIKEY_ENABLE_CONFIRM_TIPS,
				callback: function (r) {
					if (!r) return;
					let data = {};
					data['apikey_ids'] = [];
					for (let i=0;i<selectRows.length;i++){
						data['apikey_ids'].push(selectRows[i]['id']);
					}
					Metronic.blockUI({
						target: '.page-content',
						animate: true
					});
					pAjaxRequest(data, '/api/v1/system/apikey_unlock', 'put', function(result) {
						Metronic.unblockUI('.page-content');
						//刷新表格
						let op = LANG.UI_SETTINGS_APIKEY_ENABLE;
						if (operateResponseList(result, op)) {
							$('#system_apikey_table').bootstrapTable('refresh');
						}
					});
				}
			});
		});

	}


	return {
		init: function(){
			initDataTable();	//初始化表格
			initListener();		//初始化事件函数
		}
	}

}();

jQuery(document).ready(function(){
	Settings_APIKEY.init();
});