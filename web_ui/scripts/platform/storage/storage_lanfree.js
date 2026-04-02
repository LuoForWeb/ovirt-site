var StorageLanfree = function () {
	var grid;
	var table = $('#lanfreetable');
	var searchParams;
	//控制详情刷新的全局变量 插入到第几条后、插入信息、详情的页码
	var detailsIndex = 0,detailsInfo = null, pageIndex = 1;
	
	var tipEditStorage = function(){
		UIToastr.showInfo(LANG.UI_STORAGE_LANFREE_EDIT, LANG.UI_STORAGE_MODIFY_TIPS);
	}
	
	var tipDeleteStorage = function(){
		UIToastr.showInfo(LANG.UI_STORAGE_LANFREE_DELETE, LANG.UI_STORAGE_DELETE_TIPS1);
	}
	
	//得到存储详情
	var getStorageDetails = function(conf){
		var details = '';
		switch(conf.type){
			case 1:		//DISK
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_DISK_TYPE, conf.disk_type);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 2:		//LVM
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 3:		//PARTITION
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_PARTITION_TYPE, conf.partition_type);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 4:		//FC
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_FC_TYPE, conf.fc_type);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 5:		//ISCSI
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_ISCSI_TYPE, conf.iscsi_type);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_ISCSI_HOST, conf.iscsi_host);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SCR_PATH, conf.dev_path);
				break;
			case 6:		//NFS
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SHARE_PATH, conf.dev_path);
				break;
			case 7:		//CIFS
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_SHARE_PATH, conf.dev_path);
				details += getEachRowHtml(LANG.UI_STORAGE_DETAIL_USERNAME, conf.username);
				break;
			default:
				break;
		}
		
		
		return details;
	}
	
	//得到每行的HTML
	var getEachRowHtml = function(key, value){
		var html = "<tr><td>" + key + ":</td><td>";
		html += value;
		html += "</td></tr>";
		return html;
	}

	//lan_free存储详情
    var lastIndex = [-1, -1];
    var current_detail = function (index, row, element) {
        if (index != lastIndex[1]) {
            lastIndex.push(index);
            table.bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
            lastIndex.splice(0, 1);
        }
		if (!row.config) {
			return;
		}
		var sOut = '<tr class="details"><td class="details" colspan="9">';
    	sOut += '<table>';
        sOut += getStorageDetails(row.config);
        sOut += '</table></td></tr>';
		$(element).append(sOut);
	}
	
	var handleRecords = function () {
		var options = {
			toolbarId: '#lan_free_toolbar',
			buttonsToolbar: '#lan_free_toolbar .vin_btnToolbar',
			vin_url: '/api/v1/storages',
			vin_method: 'GET',
			vin_params: function () {
				let params = {};
				params.source_type = 2; //备份存储1， 生产存储2;lanfree属于生产存储 Bug #29485
				params.lan_free_flag = true;
				if ($.trim($('#searchInput').val()) != '') {
					params.search = $.trim($('#searchInput').val());
				}
				if (searchParams) {
					$.extend(params, searchParams);
				}
				return params;
			},
			onCheck: function () {
                checkEvent('#lanfreetable', '#delete');
            },
            onUncheck: function () {
                checkEvent('#lanfreetable', '#delete');
            },
            onCheckAll: function () {
                checkEvent('#lanfreetable', '#delete');
            },
            onUncheckAll: function () {
                checkEvent('#lanfreetable', '#delete');
            },
			onPostBody: function () {
				checkEvent('#lanfreetable', '#delete');
			},
			onPostBody: function () {
                let tableData = $('#lanfreetable').bootstrapTable('getData');

                if (tableData.length === 0) { // 空data保证fixed-table-container高度100%
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container .bootstrap-table .fixed-table-container').css('height', '100%');
                } else {
                    // 52px 是 分页 fixed-table-pagination 的高度
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container .bootstrap-table .fixed-table-container').css('height', 'calc(100% - 52px)');
                }
            },
			singleSelect:true,
            detailView: true, //需要更新的表格配置项,此项为是否开启展开详情视图
			detailFormatter: current_detail,
			customTool: {
				beforeInput: `<button class="icon-gray-delete b-btn brr2 mr12" style="background-color:#F4F4F5" id="delete"></button>`,
				afterInput: `<div style="display:flex">
							<button class="dropdown-toggle btn-font flex_center btn-title p-lr8" id="add" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-biaogetianjia"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + `</span>
                            </button>
							<button class="dropdown-toggle btn-font flex_center btn-title p-lr8" id="importdata" style="width:auto;height:34px;border:0px">
                                <i class="viconfont vicon-shuju"></i>
                                <span>` + LANG.UI_LUN_STORAGE_IMPORT + `</span>
                            </button></div>
							`,
			},
			placeholder: LANG.UI_STORAGE_NAME_SEARCH, //搜索框的placeholder
			// searchInput: true, //搜索框
			// searchClass: 'storageSearch', //自定义的搜索框类名
			// searchSelector: '.storageSearch', //选择使用自定义搜索框
			// showExport: false, //是否开启导出按钮
			// showColumns: false, //是否开启列选择按钮
			// onResetView: initTableHeight,
			columns: [ //列定义
				{
					checkbox: true,
					sortable: false
				},
				{
					field: 'num', //字段名
					title: LANG.UI_PUBLIC_TABLE_ID,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'storage_nickname',
					title: LANG.UI_SEARCH_NICKNAME,
				},
				{
					field: 'storage_type',
					title: LANG.UI_SEARCH_STORAGE_TYPE,
					formatter: storageFormatter
				},
				{
					field: 'node',
					title: LANG.UI_PUBLIC_STORAGE_IN_NODE,
				},
				{
					field: 'status',
					title: LANG.UI_NODE_STATUS,
					formatter: function (index, row) {
						if (row.status == true) {
							return '<span class="label label-sm label-success "> ' + LANG.UI_NODE_NORMAL + ' </span>';
						} else {
							return '<span class="label label-sm label-danger "> ' + LANG.UI_NODE_ABNORMAL + ' </span>';
						}
					}
				},
				{
					field: 'total_size',
					title: LANG.UI_JOB_TOTAL_SIZE,
				},
				{
					field: 'flag',
					title: LANG.UI_SEARCH_STORAGE_STATUS,
					formatter: function (index, row) {
						var levelClass = 'label-warning';
						switch (row.flag) {
							case 1:
								levelClass = "label-success";
								break;
							case 2:
								levelClass = "label-warning";
								break;
							case 3:
								levelClass = "label-default";
								break;
							default:
								levelClass = "label-warning";
								break;
						}
						return '<span class="label label-sm '+ levelClass +' "> ' + row.desc + ' </span>';
					}
				}
			]
		}
		table.baseTableConfig().init(options);
    }
	var checkEvent = function (tableId, btnId) {
		let select = $('' + tableId + '').bootstrapTable('getSelections');
		if (select.length == 0) {
			$('' + btnId + ' i').addClass('icon-gray-delete');
			$('' + btnId + ' i').removeClass('icon-white-delete');
			$('' + btnId + '').removeClass('select-delete-btn');
			$('' + btnId + '').addClass('cancel-delete-btn');
			$('' + btnId).css('cursor', 'not-allowed');
		} else {
			$('' + btnId + ' i').removeClass('icon-gray-delete');
			$('' + btnId + ' i').addClass('icon-white-delete');
			$('' + btnId + '').removeClass('cancel-delete-btn');
			$('' + btnId + '').addClass('select-delete-btn');
			$('' + btnId).css('cursor', 'pointer');
		}
	}
	function storageFormatter(index, row) {
		// console.log(row);
		if (row.storage_type == 14) {
			return '<span>HUAWEI OceanStor</span>'
		}
		switch (row.storage_type) {
			case CONF.BD_STORAGE_TYPE.UNKNOWN:
				return '<span> UNKNOWN </span>'
			case CONF.BD_STORAGE_TYPE.DISK:
				return '<span> ' + LANG.UI_STORAGE_TYPE_DISK + ' </span>'
			case CONF.BD_STORAGE_TYPE.LVM:
				return '<span> ' + LANG.UI_STORAGE_TYPE_LOGICAL_VOLUME_LVM + ' </span>'
			case CONF.BD_STORAGE_TYPE.PARTITION:
				return '<span> ' + LANG.UI_STORAGE_TYPE_LOCAL_PARTITION + ' </span>'
			case CONF.BD_STORAGE_TYPE.FC:
				return '<span> ' + LANG.UI_STORAGE_TYPE_DETAIL_FC + ' </span>'
			case CONF.BD_STORAGE_TYPE.ISCSI:
				return '<span> ' + LANG.UI_STORAGE_TYPE_ISCSI + ' </span>'
			case CONF.BD_STORAGE_TYPE.NFS:
				return '<span> ' + LANG.UI_STORAGE_TYPE_NFS + ' </span>'
			case CONF.BD_STORAGE_TYPE.CIFS:
				return '<span> ' + LANG.UI_STORAGE_TYPE_CIFS + ' </span>'
			case CONF.BD_STORAGE_TYPE.REMOTE:
				return '<span> ' + LANG.UI_STORAGE_TYPE_REMOTE + ' </span>'
			case CONF.BD_STORAGE_TYPE.CLOUD:
				return '<span> ' + LANG.UI_STORAGE_TYPE_COUND + ' </span>'
			case CONF.BD_STORAGE_TYPE.TAPE:
				return '<span> ' + LANG.UI_STORAGE_TYPE_TAPE + ' </span>'
			case CONF.BD_STORAGE_TYPE.LOCALDIR:
				return '<span> ' + LANG.UI_STORAGE_TYPE_LOCAL_CATALOGUE + ' </span>'
			case CONF.BD_STORAGE_TYPE.HUAWEICBR:
				return '<span> ' + LANG.UI_STORAGE_TYPE_CBR + ' </span>'
		}

	}
	
	//初始化事件
	var addListeners = function(){
		//切换页数保存到cookie
		$('select[name=lanfreetable_length]').on('change', function(){
			pageLength.lanfree = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
		$('#add').on('click', addStorage);
		$('#edit').on('click', function (){
			if (checkAuth(1)) {
				checkOperateAuth(checkAuth(1), editStorage)
			}
		});
		$('#delete').on('click', function (){
			if (checkAuth(2)) {
				checkOperateAuth(checkAuth(2), deleteStorageCheck)
			}
		});
		$('#editsubmit').on('click', editStorageSubmit);
		
		//弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'700px', 'height':'260px'});
		});
		
		//高级搜索发送请求到服务端
		$('#serach_submit').on('click', function(){
			$('#searchInput').val('');
			var p = {};
			p.storage_type = $('#storageType').val();
			p.storage_status = $('#storageStatus').val();
			p.nick_name = $('#nickName').val();
			p.node_uuid = $('#nodeSelect').val();
			p.accurate_flag = true;
			searchParams = p;
			//添加搜索条件显示
			addSearchContent(p);	
			$('#searchmodal').modal('hide');
			table.bootstrapTable('refresh');
		});
		
		//按存储别名搜索
    	$('#searchbtn').on('click', searchStorage);
		$('#searchInput').keypress(function (e) {
            if (e.which == 13) {
            	searchStorage();
            }
        });
		$('#searchbtn i').on('click', function (){
			$('#searchInput').val('');
			$(this).remove();
			searchStorage();
		});

		// lan_free_tip 关闭时动态设置表格高度
		$('#lan_free_tip_close').on('click', () => {
			$('.resource-manager-wrap .resource-manager-wrap__content').css('padding-bottom', 0);

            if ($('#searchDiv').is(':visible')) { // search-content 存在
                // 86px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + search-content的高度 40px
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 86px)');
            } else {
                // 46px = table-toolbar-wrapper 高度 34px + marin-bottom 12px
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 46px)');
            }
		});
	}
	
	var searchStorage = function(){
		//清除高级筛选显示内容
		$('#searchDiv .searchContent').text('');
		$('#searchDiv').hide();

		// 动态设置 table-container高度
		if ($('#lan_free_tip').length > 0) { // 提示存在
			// 176px = table-toolbar-wrapper高度 46px + alert-info高度130px
			$('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 176px)');
		} else {
			// 46px = table-toolbar-wrapper高度 46px
			$('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 46px)');
		}

		table.bootstrapTable('refresh');
	}
    
	
	//显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('.searchContent').text('');
		var nickName = xssEncode($('#nickName').val());
		if(p.storage_type != "0"){
			info += '<span id="storageType" title="' + $('#storageType').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_STORAGE_TYPE +': <i>' + $('#storageType').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.storage_status != "0"){
			info += '<span id="storageStatus" title="'+$('#storageStatus').find('option:selected').text()+'"> '+ LANG.UI_SEARCH_STORAGE_STATUS +': <i>' + $('#storageStatus').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.nick_name){
			info += '<span id="nickName" style="position: relative"><span id="nickNameDetail" style="display: none;position: absolute;bottom: -40px;left: 0px;background: white"> ' + nickName + '</span> '+ LANG.UI_SEARCH_NICKNAME +': <i>' + nickName + '</i><em>X</em></span>';		}
		if(p.node_uuid != "0"){
			info += '<span id="nodeValue" title="' + $('#nodeSelect').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_SELET_NODE +': <i>' + $('#nodeSelect').find("option:selected").text() + '</i><em>X</em></span>';
		}
		
		$('.searchContent').append(info);
		$('#searchDiv').show();

		// 动态设置 table-container高度
		if ($('#lan_free_tip').length > 0) { // 提示存在
			// 216px = table-toolbar-wrapper高度 46px + search-content 40px + alert-info高度130px
			$('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 216px)');
		} else {
			// 86px = table-toolbar-wrapper高度 46px + search-content 40px
			$('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 86px)');
		}


		$('#searchDiv .searchContent #nickName').mouseenter(function(e){
			$('#nickNameDetail').show()
		}).mouseleave(function(){
			$('#nickNameDetail').hide()
		});
		$('#searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();
			var searchContent = $('.searchContent');
			if(searchContent[0].children.length == 0){
				$('#searchDiv').hide();

				// 动态设置 table-container高度
				if ($('#lan_free_tip').length > 0) { // 提示存在
					// 176px = table-toolbar-wrapper高度 46px + alert-info高度130px
					$('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 176px)');
				} else {
					// 46px = table-toolbar-wrapper高度 46px
					$('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 46px)');
				}
			}
			var parent  = $(this).parent();
			var id = parent[0].id;
			p[id] = undefined;
			searchParams = p;
			if (p == {}) {
				searchParams.accurate_flag = undefined;
			} else {
				searchParams.accurate_flag = true;
			}
			table.bootstrapTable('refresh');
		});
		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').hide();

			// 动态设置 table-container高度
			if ($('#lan_free_tip').length > 0) { // 提示存在
				// 176px = table-toolbar-wrapper高度 46px + alert-info高度130px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 176px)');
			} else {
				// 46px = table-toolbar-wrapper高度 46px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 46px)');
			}

			searchParams = {};
			table.bootstrapTable('refresh');
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#searchDiv').hide();

			// 动态设置 table-container高度
			if ($('#lan_free_tip').length > 0) { // 提示存在
				// 176px = table-toolbar-wrapper高度 46px + alert-info高度130px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 176px)');
			} else {
				// 46px = table-toolbar-wrapper高度 46px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.lan-free-table-container').css('height', 'calc(100% - 46px)');
			}
		}
	}
	
	
	//添加存储
	var addStorage = function(){
    	LOCATION('./content/platform/storage/storage_lanfree_add.php', 'infrastructure');
	}
	
	//修改存储
	var editStorage = function(){
		var select = table.bootstrapTable('getSelections');
		if(!select.length){
			return tipEditStorage();
		}
		if(select.length > 1){
			UIToastr.showInfo(LANG.UI_STORAGE_LANFREE_EDIT, LANG.UI_STORAGE_MODIFY_TIPS2);
			return;
		}
		Metronic.blockUI({target: '.lan-free-table-container',animate: true,cenrerY: true});
		pAjaxRequest({}, "/api/v1/storages/"+select[0].storage_uuid, "GET", function (result) {
			Metronic.unblockUI('.lan-free-table-container');
			if (result.code == 0) {
				$('#storageuuid').val(result.data.storage_uuid);
				$('#storagename').val(result.data.storage_nickname);
				$('#modaldivedit').modal();
			}
		});
	}

	// 操作权限校验
	var checkAuth = function(type = 1) {
		var select = table.bootstrapTable('getSelections');
		if (!select.length) {
			if (type == 2) {
				tipDeleteStorage();
			} else {
				tipEditStorage();
			}
			return false;
		}

		return {
			type: 1,
			user_uuid: select[0].user_uuid,
			auth: 'resmanagement'
		};
	}
	
	//修改存储
	var editStorageSubmit = function(){
		var params = {storage_name:$('#storagename').val()};
		Metronic.blockUI({target: '#modaldivedit',animate: true});
		var storageuuid = $('#storageuuid').val();
		pAjaxRequest(params, "/api/v1/storages/"+storageuuid, "PATCH", function (result) {
			Metronic.unblockUI('#modaldivedit');
			if (result.code == 0) {
				$('#modaldivedit').modal('hide');
				table.bootstrapTable('refresh');
				UIToastr.showSuccess(LANG.UI_STORAGE_MODIFY, result.message);
			} else {
				UIToastr.showError(LANG.UI_STORAGE_MODIFY, result.message);
			}
		});
	}
	
	//删除存储
	var deleteStorageCheck = function(){
		var select = table.bootstrapTable('getSelections');
		var data = table.bootstrapTable('getData');

		if(!select.length){
			return tipDeleteStorage();
		}
		var nodeuuid = "";
		for(var i=0;i<data.length;i++){
			if($.inArray(data[i].storage_uuid, select) != -1){
				if(nodeuuid == ""){
					nodeuuid = data[i].config.node_uuid;
				}
				if(nodeuuid != data[i].config.node_uuid){
					UIToastr.showWarning(LANG.UI_STORAGE_LANFREE_DELETE, LANG.UI_STORAGE_DELETE_TIPS3);
					return;
				}
			}
		}
		bootbox.confirm({
            title: LANG.UI_STORAGE_LANFREE_DELETE,
            message: LANG.UI_STORAGE_LANFREE_DELETE_TIPS,
            callback: debounce(function(r) {
                if(!r) return;
				var data = {};
				data.uuids = [select[0].storage_uuid];

				Metronic.blockUI({target: '#lanfreetable',animate: true});
				pAjaxRequest(data, "/api/v1/storages/confirm", "DELETE", function (result) {
					Metronic.unblockUI('#lanfreetable');
					if (result.code == 0) {
						$('#modaldiv').modal('hide');
						expandIndex = null
						table.bootstrapTable('refresh');
						UIToastr.showSuccess(LANG.UI_STORAGE_LANFREE_DELETE, result.message);
					} else {
						UIToastr.showError(LANG.UI_STORAGE_LANFREE_DELETE, result.message);
					}
				});
            }, 300)
        });
	}

	var initNodeSelect = function(){
		pAjaxRequest({offset:0,limit:100}, "/api/v1/nodes/", "GET", function (result) {
			var data = result.data.rows;
			var nodeSelect = $('#nodeSelect');
			nodeSelect.empty();
			var option = $("<option>").text(LANG.UI_SEARCH_ALL_NODE).val('0');
			nodeSelect.append(option);
			for(var i=0; i<data.length; i++){
				var name = data[i].host_name + '('+ data[i].ip +')';
				option = $("<option>").text(name).val(data[i].node_uuid);
				nodeSelect.append(option);
			}
			nodeSelect.val('0');
		});
	}

    return {
        //main function to initiate the module
        init: function () {
        	initNodeSelect(); //初始化所有节点
        	handleRecords();
        	addListeners();
        }
    };
}();

jQuery(document).ready(function() {    
	StorageLanfree.init();
});