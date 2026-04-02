var domian_server = function(){
	var grid;
	var tipEditDomainServer = function(){
		UIToastr.showInfo(LANG.UI_DOMAIN_SERVER_MODIFY, LANG.UI_DOMAIN_SERVER_MODIFY_NO_SELECT);
	}
	var tipDeleteDomainServer = function (){
		UIToastr.showInfo(LANG.UI_DOMAIN_SERVER_DELETE, LANG.UI_DOMAIN_SERVER_DELETE_NO_SELECT);
	}

	//添加监听事件
	var addListeners = function(){
		// 添加域服务器
		$('#newBut').on('click',function(){
			clickEffect(this);
			LOCATION('./content/platform/users/add_domain_server.php','safety');
		})

		// 修改域服务器
		$('#editBut').on('click', function (){
			if (checkAuth('edit')) {
				checkOperateAuth(checkAuth('edit'), editDomain)
			}
		});

		// 批量删除
		$('#vin_domain_toolbar #delete_domain').on('click', function (){
			if (checkAuth('delete')) {
				checkOperateAuth(checkAuth('delete'), deleteDomains)
			}
		});
	}

	// 操作权限校验
	var checkAuth = function(operationType) {
		var select = $('#domain_table').bootstrapTable('getSelections');
		if (!select.length) {
			switch (operationType){
				case 'edit':
					tipEditDomainServer();
					break;
				case 'delete':
					tipDeleteDomainServer();
					break;
			}
			return false;
		}

		return {
			type: 1,
			user_uuid: [...new Set(select.map(item => item.user_uuid))].join(','),
			auth: ''
		};
	}

	var handleRecords = function () {
		//表格初始化配置项
		let options = {
			toolbarId: '#vin_domain_toolbar',
			buttonsToolbar: '#vin_domain_toolbar .vin_btnToolbar',
			vin_url: '/api/v1/domains',
			vin_method: 'GET',
			showColumns: true,
			showExport: true,
			paginationLoop: false,
			onResetView: initTableHeight,
			onCheck: function () {
				modifyDelStyle('domain_table', 'delete_domain');
			},
			onUncheck: function (){
				modifyDelStyle('domain_table', 'delete_domain');
			},
			onCheckAll: function () {
				modifyDelStyle('domain_table', 'delete_domain');
			},
			onUncheckAll: function () {
				modifyDelStyle('domain_table', 'delete_domain');
			},
			fileName:LANG.UI_DOMAIN_SERVER_LIST,
			columns:[
				{
					checkbox: true,
					sortable: false, //默认可排序，禁用排序才写此项
					formatter: function (value, row, index, field) {
						if (row.checked === false) {
							return {
								disabled: true
							};
						}
					}
				},
				{
					field: 'domain_name',
					title: LANG.UI_DOMAIN_SERVER_NAME,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'domain_type',
					title: LANG.UI_VM_MACHINE_TYPE,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'domain_ip',
					title: LANG.UI_DRILLS_IP_ADDRESS,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'register_time',
					title: LANG.UI_DOMAIN_SERVER_REGISTER_TIME,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'create_user_name',
					title: LANG.UI_AGENT_POOL_TABLE_CREATOR,
					sortable: false, //默认可排序，禁用排序才写此项
				},
			],
		}
		sessionStorage.removeItem('domain_table_pageRecord');
		$('#domain_table').baseTableConfig().init(options);
	}

	var checkEvent = function (tableId, btnId) {
		let select = $('' + tableId + '').bootstrapTable('getSelections');
		if (select.length == 0) {
			$('' + btnId + '').addClass('icon-gray-delete');
			$('' + btnId + '').removeClass('icon-white-delete');
			$('' + btnId + '').removeClass('select-delete-btn');
			$('' + btnId + '').addClass('cancel-delete-btn');
		} else {
			$('' + btnId + '').removeClass('icon-gray-delete');
			$('' + btnId + '').addClass('icon-white-delete');
			$('' + btnId + '').removeClass('cancel-delete-btn');
			$('' + btnId + '').addClass('select-delete-btn');
		}
	}
	function initTableHeight() {
		//拿到父窗口的高度
		var height;
		var panelH = window.innerHeight;

		height = panelH - 381;

		$("#domainDiv .fixed-table-body").css({
			"height": height
		});
	}

	var editDomain = function(){
		clickEffect(this);
		var select = $('#domain_table').bootstrapTable('getSelections');
		if(select.length == 0){
			return UIToastr.showInfo(LANG.UI_DOMAIN_SERVER_MODIFY, LANG.UI_DOMAIN_SERVER_MODIFY_NO_SELECT);
		}

		if(select.length > 1){
			return UIToastr.showWarning(LANG.UI_DOMAIN_SERVER_MODIFY, LANG.UI_DOMAIN_SERVER_MODIFY_SELECT_ONE);

		}

		var url = './content/platform/users/edit_domain_server.php?domainuuid=' + select[0].domain_uuid;
		LOCATION(url);

	}

	var deleteDomains = function (){
		clickEffect(this);
		let select = $('#domain_table').bootstrapTable('getSelections');
		let ids = [];
		for (let i = 0; i < select.length; i++) {
			ids.push(select[i].domain_uuid);
		}
		if (!select.length) {
			return UIToastr.showInfo(LANG.UI_DOMAIN_SERVER_DELETE, LANG.UI_DOMAIN_SERVER_DELETE_NO_SELECT);
		}
		var data = {};
		data.domainuuid = ids;
		Metronic.blockUI({
			target: '#domainDiv',
			animate: true,
			cenrerY: true,
		});
		pAjaxRequest(data,'/api/v1/domains','DELETE',function (res){
			Metronic.unblockUI('#domainDiv');
			if (operateResponseList(res, LANG.UI_DOMAIN_SERVER_DELETE)) {
				$('#domain_table').bootstrapTable('refresh');
			}
		});
	}

	// 按钮点击效果
	var clickEffect = function (element) {
		$(element).addClass('btn-hover');
		setTimeout(function() {
			$(element).removeClass('btn-hover');
		}, 300); // 0.3秒后恢复原样
	}

	return{
		init: function(){
			handleRecords();
			addListeners();
		}
	};
}();
jQuery(document).ready(function() {
	domian_server.init();
});