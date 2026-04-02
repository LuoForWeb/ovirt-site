var StorageData = function () {
	let changeHeightFlag = false;
	var table = $('#backupdatatable')
	var initListener = function () {
		var des = '';
		if($.inArray('p_storage_manager_data', CONF.PERMISSION_ARR) !== -1){
			des += '<div class="btn-group">\n' +
				'      <button type="button" id="distribute" class="btn table-toolbar-btn" style="width: 100%;">\n' +
				'<i class="viconfont vicon-a-Group1000003036"></i>' + LANG.UI_USER_LAST_ALLOCATION_ADMIN_USER +
				'</button>\n' +
				'</div>';
		}

		$('.leftTool_vm').html(des);
		//绑定事件
		toBindEvent();
	}

	// 高度改变
	var change_height = function () {
		if (changeHeightFlag == false) {
			changeHeightFlag = true;
			$('#backupdatatable>tbody>tr').css(
				'cssText', 'height: 60px!important',
			)
			$('#vin_current_toolbar .change_height i').addClass('icon-auto-height2');
		} else if (changeHeightFlag == true) {
			changeHeightFlag = false
			$('#backupdatatable>tbody>tr').css(
				'cssText', 'height: 40px!important',
			)
			$('#vin_current_toolbar .change_height i').removeClass('icon-auto-height2');
		}
	}
	// 初始化表格
	var initDataTable = function () {
		const options = {
			toolbarId: '#vin_current_toolbar',
			buttonsToolbar: '#vin_current_toolbar .vin_btnToolbar',
			vin_url: "/api/v1/storages/data",
			vin_method: "GET",
			detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
			sortName: 'create_time',
			sortOrder: 'desc',
			detailFormatter: current_detail, //详情展开
			changeHeightBtn: true, //改变高度按钮
			pagination: true, //分页
			pageList: [5, 10, 25, 50], //每页数量
			resizable: true, //可变宽度
			searchInput: false, //搜索框
			// showRefresh:true,// 显示刷新按钮
			onResetView: initTableHeight,
			columns: [
				{
					checkbox: true,
					sortable: false,
					formatter: checkFormatter
				},
				{
					field: 'num',
					title: LANG.UI_REPORT_NUMBER,
					sortable: false,
					align: 'center',
				},
				{
					field: 'task_name',
					title: LANG.UI_STORAGE_OLD_TASK_NAME,
					align: 'center',
				},
				{
					field: 'module_type_value',
					title: LANG.UI_SEARCH_MODE_TYPE,
					align: 'center',
				},
				{
					field: 'create_time',
					title: LANG.UI_STORAGE_OLD_TASK_CREATE_TIME,
					align: 'center',
				},
				{
					field: 'point_num',
					title: LANG.UI_STORAGE_TIMEPOINT_NUM,
					align: 'center',
				},
				{
					field: 'size',
					title: LANG.UI_STORAGE_TIMEPOINT_SIZE,
					align: 'center'
				},
			],
		};
		function checkFormatter(value, row, index) {
			return {
				disabled: false,// 设置是否可用
				checked: false // 设置选中
			};
		}

		table.baseTableConfig().init(options);
		initListener();
	}

	// 管理详情显示
	var lastIndex = [-1, -1];
	var current_detail = function (index, row, element) {
		// 控制只显示一个
		if (index != lastIndex[1]) {
			lastIndex.push(index);
			$('#backupdatatable').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
			lastIndex.splice(0, 1);
		}
		Metronic.blockUI({target: '#backupdatatable',animate: true,cenrerY: true,});
		pAjaxRequest({}, "/api/v1/storages/data/" + row.task_uuid, "GET", function (result) {
			Metronic.unblockUI('#backupdatatable');
			if (result.code == 0) {
				// 具体的内容
				var content = '<table><tbody>';
				var details = result.data.details
				var detail = LANG.UI_PUBLIC_NOTHING;
				if (details.length > 0) {
					detail = '';
					for (var k in details) {
						detail +=  details[k] + '</br>';
					}
				}
				content += '<tr><td style="width:200px;">'+result.data.details_des+':</td>';
				if (result.data.other_info != undefined) {
					content += 	'<td>' + result.data.other_info + '</td>';
				}
				content += 	'<td>' + detail + '</td>' +
					'</tr>';
				content += '</tbody></table>';
				$(element).append(content);

			} else {
				UIToastr.showError(LANG.UI_VOL_CDP_RECOVER_BACKUP_DATA, result.message);
			}
		});
	}

	var toBindEvent = function () {
		// 改变表格高度
		$('#vin_current_toolbar .change_height').on('click', change_height);
		$('#distribute').on('click', initModal);
		$('#submit').on('click', distribute);
	}

	//提交分配
	var distribute = function(){
		var select = table.bootstrapTable('getSelections');
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_STORAGE_DATA_SELECT_TASK, LANG.UI_STORAGE_DATA_TASK_TIPS);
		}
		var useruuid = $('select[name=userselect]').val();
		var username = $('select[name=userselect] option:selected').text();
		if(!useruuid){
			return UIToastr.showInfo(LANG.UI_STORAGE_DATA_SELECT_USER, LANG.UI_STORAGE_DATA_USER_TIPS);
		}

		var uuid = [];
		for (var j in select) {
			uuid.push(select[j]['task_uuid']);
		}
		var data = {};
		data.uuids = uuid;
		data.user_uuid = useruuid;
		data.username = username;

		Metronic.blockUI({target: '#modaldiv',animate: true});
		pAjaxRequest(data, "/api/v1/storages/distribute", "POST", function (result) {
			Metronic.unblockUI('#modaldiv');
			if (result.code == 0) {
				$('#modaldiv').modal('hide');
				table.bootstrapTable('refresh');
				UIToastr.showSuccess(LANG.UI_LUN_STORAGE_IMPORT, result.message);
			} else {
				UIToastr.showError(LANG.UI_LUN_STORAGE_IMPORT, result.message);
			}
		});
	}

	//初始化MODAL
	var initModal = function(){
		var select = table.bootstrapTable('getSelections');
		if(!select.length){
			return UIToastr.showInfo(LANG.UI_STORAGE_DATA_SELECT_TASK, LANG.UI_STORAGE_DATA_TASK_TIPS);
		}
		$.post(CONF.AJAXPATH, {m:CONF.M.USER,f:'getOperatorUsers',p:{}}, function(d){
			var data = JSON.parse(d);
			var userselect = $('select[name=userselect]');
			userselect.empty();
			for(var i=0; i<data.length; i++){
				var option = $("<option>").text(data[i].name).val(data[i].uuid);
				userselect.append(option);
			}
			$('#taskcount').html(select.length);
			$('#modaldiv').modal();
		});
	}

	function initTableHeight() {
		//拿到父窗口的高度
		var height;
		var panelH = window.innerHeight;

		height = panelH - 300;

		$("#div_backupdatatable .fixed-table-body").css({
			"height": height
		});
	}

	return {
		//main function to initiate the module
		init: function () {
			initDataTable();
			initTableHeight();
		}
	};

}();

jQuery(document).ready(function () {
	StorageData.init();
});
