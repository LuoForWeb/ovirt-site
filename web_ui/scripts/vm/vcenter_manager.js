var VcenterManager = function () {

	var grid = $("#datatable");
	var hostGrid = $('#hostdatatable');
	var authorizedFlag; //是否授权
	var queryParams = {accurate_flag:false};
	var _userUuid; // 当前用户uuid
	
	var tipSelect = function(){
		UIToastr.showInfo(LANG.UI_VCENTER_SELECT, LANG.UI_VCENTER_SELECT_TIPS);
	}
	
	var tipEdit = function(){
		UIToastr.showInfo(LANG.UI_VCENTER_EDIT_SELECT, LANG.UI_VCENTER_EDIT_SELECT_TIPS);
	}
	
	var tipDelete = function(){
		UIToastr.showInfo(LANG.UI_VCENTER_DELETE_SELECT, LANG.UI_VCENTER_DELETE_SELECT_TIPS);
	}

	//授权某个虚拟化中心
	var authHost = function(platform_uuid){
		$('#vcenteruuid').val(platform_uuid);
		handleHostRecords(platform_uuid);
		handleLisenceInfo();
		if(authorizedFlag == 1){
			$('#modalauthhost').modal({'width':'800px', 'height':'500px'});
		}else{
			return UIToastr.showWarning(LANG.UI_VCENTER_CHECK_SYSTEM_AUTH, LANG.UI_VCENTER_CHECK_SYSTEM_AUTH_TIPS);
		}
	}
	
	var handleLisenceInfo = function(){
		pAjaxRequest({}, "/api/v1/system/vm_license", "GET", function (data) {
			var d = data.data;
			var html = "";
			if(1 == d.type){
				//宿主机授权
				html = LANG.UI_SETTING_AUTH_HOST + ': ' + LANG.UI_VCENTER_AUTH_TOTAL + ' ' +
					d.total + ', ' + LANG.UI_SETTING_USED_NUM + ' ' + d.used + ', ' + LANG.UI_SETTING_VALID_NUM + ' ' + d.valid;
			}else if(2 == d.type){
				//CPU授权
				html = LANG.UI_SETTING_AUTH_CPU + ': ' + LANG.UI_VCENTER_AUTH_TOTAL + ' ' +
					d.total + ', ' + LANG.UI_SETTING_USED_NUM + ' ' + d.used + ', ' + LANG.UI_SETTING_VALID_NUM + ' ' + d.valid;
			}
			$('#vmlisenceinfo').html(html);
		}, false);
	}
	
	var handleHostRecords = function(vcenteruuid){
		let options = {
			vin_url: '/api/v1/vm/platforms/hosts',
			vin_method: 'GET',
			queryParamsType: 'limit',
			queryParams: function (params) {
				let queryParams = {};
				queryParams.offset = params.offset;
				queryParams.limit = params.limit;
				queryParams.sort = params.sort;
				queryParams.order = params.order;
				queryParams.platform_uuid = vcenteruuid;
				return queryParams;
			},
			pagination: true,
			sidePagination: 'server',
			pageNumber: 1,
			pageSize: 20,
			pageList: [10, 20, 50, 100],
			paginationLoop: false,
			uniqueId: 'host_uuid',
			changeHeightBtn: true, //改变高度按钮
			batchOperation: true, // 批量操作
			sortable: true,
			sortName: 'host_name',
			sortOrder: 'asc',
			resizable: true,
			onRefresh: function (params) {
				$("#hostdatatable").bootstrapTable('hideLoading');
			},
			onCheck: function (res) {
				var selectedRow = $('#hostdatatable').bootstrapTable("getSelections");
				$('#hostdatatable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checksome.svg')");
				if (selectedRow.length == 0) {
					$('#hostdatatable .fixed-table-pagination .pull-left .pagination-info span').html('');
				} else if (selectedRow.length > 0) {
					$('#hostdatatable .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_CLOUD_SELECTED + selectedRow.length + '');
				}
			},
			onUncheck: function (row, $element) {
				var selectedRow = $('#hostdatatable').bootstrapTable("getSelections");
				if (selectedRow.length == 0) {
					$('#hostdatatable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
					$('#hostdatatable .fixed-table-pagination .pull-left .pagination-info span').html('');
				} else if (selectedRow.length > 0) {
					$('#hostdatatable .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_CLOUD_SELECTED + selectedRow.length + '');
				}
			},
			onCheckAll: function () {
				var selectedRow = $('#hostdatatable').bootstrapTable("getSelections");
				$('#hostdatatable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checked.svg')");
				$('#hostdatatable .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_CLOUD_SELECTED + selectedRow.length + '');
			},
			onUncheckAll: function () {
				var selectedRow = $('#hostdatatable').bootstrapTable("getSelections");
				$('#hostdatatable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checkbox.svg')");
			},

			columns: [
				{
					field: 'checkbox',
					checkbox: true,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'host_name', //字段名
					title: LANG.UI_CLOUD_PLATFORM_HOST_NAME,
				},
				{
					field: 'host_ip',
					title: LANG.UI_CLOUD_PLATFORM_HOST_IP_ADDRESS,
				},
				{
					field: 'platform_info',
					title: LANG.UI_VCENTER_VCENTER,
				},
				{
					field: 'online_flag',
					title: LANG.UI_CLOUD_PLATFORM_HOST_STATUS,
					formatter: function (value) {
						let online_des = '';
						if (1 == value) {
							online_des = '<span class="label label-sm label-success">' + LANG.UI_CLOUD_PLATFORM_ONLINE + '</span>';
						} else if (2 == value) {
							online_des = '<span class="label label-sm label-default">' + LANG.UI_CLOUD_PLATFORM_OFFLINE + '</span>';
						} else {
							online_des = '<span class="label label-sm label-warning">' + LANG.UI_CLOUD_PLATFORM_UNKNOWN + '</span>';
						}
						return online_des;
					}
				},
				{
					field: 'cpu_count',
					title: LANG.UI_VM_SETTING_CPU_COUNT,
				},
				{
					field: 'authorization_flag',
					title: LANG.UI_VCENTER_AUTH_AUTH,
					formatter: function (value) {
						let auth_des = '';
						if (1 == value) {
							auth_des = '<span class="label label-sm label-success">' + LANG.UI_CLOUD_PLATFORM_AUTHORIZED + '</span>';
						} else {
							auth_des = '<span class="label label-sm label-warning">' + LANG.UI_CLOUD_PLATFORM_UNAUTHORIZED + '</span>';
						}
						return auth_des;
					}
				}
			]
		};
		$('#hostdatatable').bootstrapTable('destroy');
		$('#hostdatatable').baseTableConfig().init(options);
	}
	
	var doSyncVcenter = function(id){
		let p = {};
		p.platform_uuid = id;
		Metronic.blockUI({target: '#vcentercontent', animate: true});
		pAjaxRequest(p, "/api/v1/vm/platforms/synchronization", "POST", function (d) {
			Metronic.unblockUI('#vcentercontent');
			if (operateResponseList(d)) {
				grid.bootstrapTable("refresh");
			}
		}, true);
	}
	
	//顶部同步数据按钮事件
	var syncVcenterMore = function(){
		// var select = grid.getSelectedRows();
		// if(!select.length){
		// 	return tipSelect();
		// }
		// if(1 == select.length){
		// 	//一个,直接调用单个虚拟化中心刷新
		// 	doSyncVcenter(select[0]);
		// }
		// if(select.length > 1){
		// 	//多个,提示用户
		// 	bootbox.confirm({
	    //         title: LANG.UI_VCENTER_SYNC_MORE_TITLE,
	    //         message: LANG.UI_VCENTER_SYNC_MORE_CONTENT,
	    //         callback: function(r) {
	    //             if(!r) return;
	    //             var data = {};
	    //             data.ids = select;
	    //             data = JSON.stringify(data);
	    //     		Metronic.blockUI({target: '#vcentercontent',animate: true});
	    //     		$.post(CONF.AJAXPATH, {m:CONF.M.VCENTER,f:'syncVcenterMore',p:data}, function(d){
	    //     			Metronic.unblockUI('#vcentercontent');
	    //     			if(OPREL(d)){
	    //         			grid.getRefresh({});
	    //         		}
	    //         	});
	    //         }
	    //     });
		// }
	}
	
    var handleRecords = function () {
		tableInit();

		$("#edit").on('click', function(){
			let authParams = checkAuth('edit');
			if (authParams) {
				checkOperateAuth(authParams, editVcenter);
			}
		});
		$("#add").on('click', function(){
			checkOperateAuth(checkAuth('add'), addVcenter);
		});
		$("#delete").on('click', function(){
			let authParams = checkAuth('delete');
			if (authParams) {
				checkOperateAuth(authParams, deleteVcenter);
			}
		});
    	$("#syncmore").on('click', function(){
    		syncVcenterMore();
    	});
    	//初始化多选下拉框
		$(".selectpicker").selectpicker({
			noneSelectedText: LANG.BILLING_PLEASE_SELECT,
			deselectAllText: LANG.BILLING_DESELECT_ALL,
			selectAllText: LANG.BILLING_SELECT_ALL,
			liveSearchPlaceholder: LANG.BILLING_SEARCH,
			countSelectedText: function(){}
		});
    	return;
    }
    
    var addVcenter = function(){
    	LOCATION('./content/vm/add_vcenter.php', 'infrastructure');
    }
    
    var editVcenter = function(){
		var select = $('#datatable').bootstrapTable("getSelections");
		var url = './content/vm/modify_vcenter.php?uuid=' + select[0].platform_uuid;
    	LOCATION(url, 'infrastructure');
    }
    
    
    var deleteVcenter = function(){
    	var select = $('#datatable').bootstrapTable("getSelections");
		bootbox.confirm({
            title: LANG.UI_VCENTER_DELETE,
            message: LANG.UI_VCENTER_DELETE_TIPS2,
            callback: debounce(function(r) {
                if(!r) return;
                submitDelete(select);
            }, 300)
        });
    }
    
    var submitDelete = function(select){
		let data = {};
		data.platforms_uuids = [select[0].platform_uuid];
		Metronic.blockUI({target: '#vcentercontent',animate: true});
		pAjaxRequest(data, "/api/v1/vm/platforms", "DELETE", function (d) {
			Metronic.unblockUI('#vcentercontent');
			if (operateResponseList(d)) {
				grid.bootstrapTable("refresh");
			}
		}, true);
    }
    
    var tipSelectHost = function(){
		UIToastr.showInfo(LANG.UI_VCENTER_AUTH_SELECT_HOST, LANG.UI_VCENTER_AUTH_SELECT_HOST_TIPS);
	}
    
    //添加授权
    var addlisence = function(){
		authHandler(this, 'authorization');
    }
    
    //删除授权
    var deletelisence = function(){
		authHandler(this, 'unauthorization');
    }
    
	//授权统一处理
	var authHandler = function(button, funName){
		var select = hostGrid.bootstrapTable("getSelections");
		if(!select.length){
			return tipSelectHost();
		}
		var hostuuids = [];
		var vcenteruuid = $('#vcenteruuid').val();
		for (var i = 0; i < select.length; i++) {
			hostuuids[i] = select[i].host_uuid;
		}
		let p = {};
		p.host_uuids = hostuuids;
		p.platform_uuid = vcenteruuid;
		Metronic.blockUI({target: '#modalauthhost',animate: true});
		pAjaxRequest(p, "/api/v1/vm/platforms/" + funName, "POST", function (data) {
			Metronic.unblockUI('#modalauthhost');
			if (operateResponseList(data)) {
				handleLisenceInfo();
				hostGrid.bootstrapTable("refresh");
				grid.bootstrapTable("refresh");
			}
		}, true);
	}

	var refreshTime = function () {
		var refresh = parseInt($('#refreshValue').val());
		if (!refresh || refresh < 5) {
			UIToastr.showWarning(LANG.UI_VCENTER_RFRESH_TIME_FAILD, LANG.UI_VCENTER_RFRESH_TIME_FAILD_TIPS);
			initrefreshTime();
			return;
		}
		let message = LANG.UI_VCENTER_RFRESH_TIME_EDIT_TIPS;
		if (refresh < 30) {
			message += `<br>${LANG.UI_VCENTER_RFRESH_TIME_EDIT_TIPS2}`;
		}
		bootbox.confirm({
			title: LANG.UI_VCENTER_RFRESH_TIME_EDIT,
			message: message,
			callback: function (r) {
				if (!r) return;
				updateRefreshtime();
			}
		});
	}
    //读取虚拟化中心自动刷新间隔时间
    var initrefreshTime = function(){
		pAjaxRequest({}, "/api/v1/vm/platforms/autorefresh", "GET", function (d) {
			_userUuid = d.data.user_uuid;
			var refreshTime = parseInt(d.data.vcenter_refresh_interval) / 60;
			$('#refreshValue').val(refreshTime);
			//初始化加减控件
			$('.spinner-group').spinner({value: refreshTime, step: 1, min: 1,max: 9999});
			authorizedFlag = d.data.authorized_flag;
		}, false);
    }
    
    //修改自动刷新虚拟化中心间隔时间
    var updateRefreshtime = function(){
    	var refresh = parseInt($('#refreshValue').val());
    	var params = {
			'refresh' : refresh,
			'platform_type': 'vcenter'
    	};
		pAjaxRequest(params, "/api/v1/vm/platforms/autorefresh", "PUT", function (data) {
			Metronic.unblockUI('#refreshValue');
			if (data.success) {
				UIToastr.showSuccess(LANG.UI_VCENTER_RFRESH_TIME_SUCCESS, LANG.UI_VCENTER_RFRESH_TIME_SUCCESS_TIPS);
				$('#refreshModal').modal('hide');
			} else {
				UIToastr.showWarning(LANG.UI_VCENTER_RFRESH_TIME_FAILD, LANG.UI_VCENTER_RFRESH_TIME_FAILD_TIPS);
			}
		}, false);
    }    
    var addListeners = function(){
    	//切换页数保存到cookie
		$('select[name=datatable_length]').on('change', function(){
			pageLength.vcenter = this.value;
			var data = JSON.stringify(pageLength);
			$.cookie("pageLength", data);
		});
    	
    	$('#addlisence').on('click', addlisence);
    	$('#deletelisence').on('click', deletelisence);
		$('#refreshTime').on('click',refreshTime);
    	$('#spinnerNum').spinner({value: 5, step: 5, min: 5});
    	
    	//弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'800px', 'height':'260px'});
		});
		
		$('#searchbtn').on('click', searchVcenter);
		$('#searchInput').keypress(function (e) {
            if (e.which == 13) {
            	searchVcenter();
            }
        });
		$('#searchInput').on('blur', function () {
			$(this).prop('placeholder', LANG.UI_VCENTER_SEARCH_TIPS);
		});
		$('#clearSearchBtn').on('click', function () {
			$('#searchInput').val('');
			$("#datatable").bootstrapTable("refresh");
		});
		
		//弹出自动刷新模态框
		$('#refreshInterval').on('click',function(){
			checkOperateAuth(checkAuth('refresh'), () => {
				$('#refreshModal').modal('show');
			});
			
		});
		
		//高级搜索发送请求到服务端
		$('#serach_submit').on('click', function(){
			$('#searchInput').val('');
			var p = {};
			p.hypervisor = $('#searchmodal #vmtype').val();
			p.nickName = $('#searchmodal #nickName').val();
			p.userName = $('#searchmodal #userName').val();
			p.vcenterIp = $('#searchmodal #vcenterIp').val();
			queryParams.accurate_flag = true;
			//添加搜索条件显示
			addSearchContent(p);
			$('#searchmodal').modal('hide');
			$("#datatable").bootstrapTable("refresh");
		});
		
		//跳转平台数据管理
		$('#dataManager').on('click', toDataManger);
		
		//打开分配备份节点的模态框
		$('#nodeAllocation').on('click',function(){
			let authParams = checkAuth('nodeAllocation');
			if (authParams) {
				checkOperateAuth(authParams, () => {
					var select = grid.bootstrapTable("getSelections");
					$('#node_vcenteruuid').val(select[0].platform_uuid);
					initSelectNode();
					$('#nodeAllocationModal').modal({'width':'700px', 'height':'240px'});
				})
			}
		});
		
		//分配备份节点确认
		$('#allocationSubmit').on('click', allocationSubmit);

		// vcenter_manager_table_tip_close 关闭
		$('#vcenter_manager_table_tip_close').on('click', () => {
			$('.resource-manager-wrap .resource-manager-wrap__content').css('padding-bottom', 0);
			if ($('#searchDiv').is(':visible')) { // search-content 存在
				// 86px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + search-content的高度 40px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 86px)');
			} else {
				// 46px = table-toolbar-wrapper 高度 34px + marin-bottom 12px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 46px)');
			}
		});
    }
    
    var searchVcenter = function(){
		//清除高级筛选显示内容
		$('#searchDiv .searchContent').text('');
		$('#searchDiv').hide();

		// 动态设置 table-container高度
		if ($('#vcenter_manager_table_tip').length > 0) { // alert div存在 96px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + alert 高度50px
			$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 96px)');
		} else { // alert div不存在 46px = table-toolbar-wrapper 高度 34px + marin-bottom 12px
			$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 46px)');
		}

		queryParams.accurate_flag = false;
		$('#searchmodal #vmtype').val(0);
		$('#searchmodal #nickName').val('');
		$('#searchmodal #userName').val('');
		$('#searchmodal #vcenterIp').val('');
		$("#datatable").bootstrapTable("refresh");
	}
    
  //跳转平台数据管理
	var toDataManger = function(){
		LOCATION('./content/vm/engine_data_manager.php','vcenter_manager');
	}
    
    //显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('.searchContent').text('');
		var nickName = xssEncode($('#nickName').val());
		var userName = xssEncode($('#userName').val());
		var vcenterIp = xssEncode($('#vcenterIp').val());
		if(p.hypervisor != "0"){
			info += '<span id="hypervisor" title="' + $('#vmtype').find("option:selected").text() + '"> '+ LANG.UI_REPORT_PLATFORM +': <i>' + $('#vmtype').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.nickName){
			info += '<span id="nickName" title="' + nickName + '">'+ LANG.UI_SEARCH_NICKNAME +': <i>' + nickName + '</i><em>X</em></span>';
		}
		if(p.userName){
			info += '<span id="userName" title="' + userName + '">'+ LANG.UI_SEARCH_CREATE_USER +': <i>' + userName + '</i><em>X</em></span>';
		}
		if(p.vcenterIp){
			info += '<span id="vcenterIp" title="' + vcenterIp + '"> IP: <i>' + vcenterIp + '</i><em>X</em></span>';
		}
		$('.searchContent').append(info);
		$('#searchDiv').show();
		// 动态设置 table-container高度
		if ($('#vcenter_manager_table_tip').length > 0) { // alert div存在 136px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + search-content的高度 40px + alett 高度50px
			$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 136px)');
		} else { // alert div不存在 86px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + search-content的高度 40px
			$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 86px)');
		}

		$('#searchDiv .searchContent #nickName').mouseenter(function(e){
			$('#nickNameDetail').show()
		}).mouseleave(function(){
			$('#nickNameDetail').hide()
		});
		$('#searchDiv .searchContent #userName').mouseenter(function(e){
			$('#userNameDetail').show()
		}).mouseleave(function(){
			$('#userNameDetail').hide()
		});
		$('#searchDiv .searchContent #vcenterIp').mouseenter(function(e){
			$('#vcenterIpDetail').show()
		}).mouseleave(function(){
			$('#vcenterIpDetail').hide()
		});

		$('#searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();
			var searchContent = $('.searchContent');
			if(searchContent[0].children.length == 0){
				$('#searchDiv').hide();
				// 动态设置 table-container高度
				if ($('#vcenter_manager_table_tip').length > 0) { // alert div存在 96px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + alert 高度50px
					$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 96px)');
				} else { // alert div不存在 46px = table-toolbar-wrapper 高度 34px + marin-bottom 12px
					$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 46px)');
				}
			}
			var parent  = $(this).parent();
			var id = parent[0].id;
			if ('hypervisor' == id) {
				$('#searchmodal #vmtype').val(0);
			} else {
				$('#searchmodal #' + id).val('');
			}
			$("#datatable").bootstrapTable("refresh");
		});
		
		$('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
			$('#searchDiv').hide();
			// 动态设置 table-container高度
			if ($('#vcenter_manager_table_tip').length > 0) { // alert div存在 96px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + alert 高度50px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 96px)');
			} else { // alert div不存在 46px = table-toolbar-wrapper 高度 34px + marin-bottom 12px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 46px)');
			}

			queryParams.accurate_flag = false;
			$('#searchmodal #vmtype').val(0);
			$('#searchmodal #nickName').val('');
			$('#searchmodal #userName').val('');
			$('#searchmodal #vcenterIp').val('');
			$("#datatable").bootstrapTable("refresh");
		});
		//如果没搜索条件，先隐藏div
		if(!info){
			$('#searchDiv').hide();

			// 动态设置 table-container高度
			if ($('#vcenter_manager_table_tip').length > 0) { // alert div存在 96px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + alert 高度50px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 96px)');
			} else { // alert div不存在 46px = table-toolbar-wrapper 高度 34px + marin-bottom 12px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 46px)');
			}
		}
	}
    
  //初始化虚拟化类型
	var initVMType = function(){
		pAjaxRequest({}, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
			let hypervisors = d.data.hypervisors;
			var vmtypeselect = $('#vmtype');
			vmtypeselect.empty();
			var option = $("<option>").text(LANG.UI_SEARCH_ALL_HYPERVISOR).val('0');
			vmtypeselect.append(option);
			for (var i = 0; i < hypervisors.length; i++) {
				option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
				vmtypeselect.append(option);
			}
			vmtypeselect.val('0');
		}, false);
	}
	
	//初始化平台备份数据管理,检测是否有数据 没有数据不显示其按钮
	var initEngineData = function(){
		pAjaxRequest({}, "/api/v1/vm/platforms/engine", "GET", function (d) {
			if (d.data.total == 0) {
				$(".dataManagerDiv").addClass('display-none');
			} else {
				$(".dataManagerDiv").removeClass('display-none');
			}
		}, false);
	}
	
	//加载分配可选节点
	var initSelectNode = function(){
		var data = {};
		data.platform_uuid = $('#node_vcenteruuid').val();
		pAjaxRequest(data, '/api/v1/nodes/allocation', 'GET', function (d) {
			var jsondata = d.data;
			var nodes = jsondata.node_list;	//备份节点列表
			var allocationList = jsondata.allocation_list;	//已绑定的节点列表
			var nodeSelect = $('#nodeSelect');
			nodeSelect.empty();

			for(var i=0; i<nodes.length; i++){
				var option = $("<option>").text(nodes[i].text).val(nodes[i].uuid);
				nodeSelect.append(option);
			}
			nodeSelect.selectpicker('val', allocationList);
			nodeSelect.selectpicker('refresh');
		}, false);
	}
	
	//分配备份节点发送消息
	var allocationSubmit = function(){
		var data = {};
		data.platform_uuid = $('#node_vcenteruuid').val();
		data.node_uuids = $('#nodeSelect').selectpicker('val');
//		if(data.nodes.length < 1){
//			UIToastr.showInfo("选择备份节点", "请选择一个或多个备份节点分配到当前虚拟化平台");
//			return false;
//		}
		Metronic.blockUI({target: '#nodeAllocationModal',animate: true});
		pAjaxRequest(data, '/api/v1/nodes/allocation', 'POST', function (d) {
			Metronic.unblockUI('#nodeAllocationModal');
			if (operateResponseList(d)) {
				$('#nodeAllocationModal').modal('hide');
			}
		}, false);
	}

	var tableInit = function () {
		Metronic.blockUI({target: '.vcenter-manager-table-container',animate: true});
		var operates = {
			'click .sync': function (event, value, row, index) {
				checkOperateAuth(checkAuth('sync', row.user_uuid), () => {
					doSyncVcenter(row.platform_uuid);
				});
			},
			'click .authhost': function (event, value, row, index) {
				checkOperateAuth(checkAuth('authHost', row.user_uuid), () => {
					authHost(row.platform_uuid);
				});
			},
		}

		var options = {
			// toolbarId: '#vin_cpm_toolbar',
			// vin_toolbar: '.vin_toolbar',
			vin_url: '/api/v1/vm/platforms',
			vin_method: 'GET',
			queryParamsType: 'limit',
			queryParams: initParams,
			pagination: true,
			sidePagination: 'server',
			pageNumber: 1,
			pageSize: 20,
			pageList: [10, 20, 50, 100],
			paginationLoop: false,
			uniqueId: 'platform_uuid',
			changeHeightBtn: true, //改变高度按钮
			batchOperation: true, // 批量操作
			sortable: true,
			sortName: 'refresh_time',
			sortOrder: 'desc',
			resizable: false,
			singleSelect: true,
			onRefresh: function (params) {
				$("#datatable").bootstrapTable('hideLoading');
			},
			onSearch: function () {
				$("#searchInput").on('keyup', function (event) {
					$('#datatable').bootstrapTable(('refresh'));
				});
			},
			// onSort: function (name, order) {
			// 	$("#datatable").bootstrapTable('refreshOptions', {sortName: name, sortOrder: order});
			// },
			onCheck: function (res) {
				var selectedRow = $('#datatable').bootstrapTable("getSelections");
				$('#datatable thead .bs-checkbox input[type=checkbox]').css("background-image", "url('./assets/global/img/checksome.svg')");
				if (selectedRow.length == 0) {
					$('#datatable .fixed-table-pagination .pull-left .pagination-info span').html('');
				} else if (selectedRow.length > 0) {
					$('#datatable .fixed-table-pagination .pull-left .pagination-info span').html('<span class="m-lr2">|</span>' + LANG.UI_CLOUD_SELECTED + selectedRow.length + '');
				}
				modifyDelStyle('datatable', 'delete');
			},
			onUncheck: function () {
				modifyDelStyle('datatable', 'delete');
			},
			PostBody: function () { 
				$('#datatable th[data-field="operations"]').css('width', '14%');
			},
			columns: [ //列定义
				{
					field: 'checkbox',
					checkbox: true,
					sortable: false, //默认可排序，禁用排序才写此项
				},
				{
					field: 'no', //字段名
					title: LANG.UI_PUBLIC_TABLE_ID,
					sortable: false,
					width: "50px",
				},
				{
					field: 'ip',
					title: LANG.UI_CLOUD_PLATFORM_IP_ADDRESS,
					formatter: function (value, data) {
						return '<a href="/content/vm/vcenter_details.php?vcuuid=' + data.platform_uuid + '" class="ajaxify" name="infrastructure">' + value + '</a>';
					}
				},
				{
					field: 'nickname',
					title: LANG.UI_SEARCH_NICKNAME,
				},
				{
					field: 'hypervisor_des',
					title: LANG.UI_VM_SETTING_V2_HYPER_TYPE
				},
				{
					field: 'version',
					title: LANG.UI_CLOUD_PLATFORM_VERSION,
				},
				{
					field: 'username',
					title: LANG.UI_CLOUD_PLATFORM_LOGIN_USERNAME
				},
				{
					field: 'refresh_time',
					title: LANG.UI_CLOUD_PLATFORM_SYNC_TIME,
				},
				{
					field: 'user_name',
					title: LANG.UI_CLOUD_PLATFORM_CREATER,
				},
				{
					field: 'status',
					title: LANG.UI_CLOUD_PLATFORM_AUTHORIZE_STATUS,
					sortable: false,
					formatter: function (value, data) {
						let status_des = '';
						if (1 == data.license_info.status) {
							status_des = '<span class="label label-sm label-success">' + LANG.UI_CLOUD_PLATFORM_ALL_AUTHORIZE + '</span>';
						} else if (2 == data.license_info.status) {
							status_des = '<span class="label label-sm label-warning">' + LANG.UI_CLOUD_PLATFORM_PARTIAL_AUTHORIZE + '</span>';
						} else if (3 == data.license_info.status) {
							status_des = '<span class="label label-sm label-danger">' + LANG.UI_CLOUD_PLATFORM_UNAUTHORIZED + '</span>';
						} else {
							status_des = '<span class="label label-sm label-default">' + LANG.UI_PUBLIC_UNKNOWN + '</span>';
						}
						return status_des;
					}
				},
				{
					field: 'operations',
					title: LANG.UI_PUBLIC_OPERATION,
					sortable: false,
					events: operates,
					opButton: true,
					formatter: function (value, data) {
						let btn = '';
						if (CONF.PERMISSION_ARR.includes('p_vcenter_manager_sync')) {
							btn += '<button type="button" class="btn green-haze btn-sm sync" name="' + data.platform_uuid + '">' +
								'<i class="viconfont vicon-ge_refresh"></i> ' + LANG.UI_CLOUD_PLATFORM_SYNC + '</button>';
						}
						if (data.permission_flag && $.inArray('p_vcenter_manager_license', CONF.PERMISSION_ARR) !== -1) {
							//如果允许进行授权操作,才添加授权按钮
							btn += '<button type="button" class="btn green-haze btn-sm authhost" name="' + data.platform_uuid + '">' +
								'<i class="viconfont vicon-ge_authorization2"></i> ' + LANG.UI_CLOUD_PLATFORM_AUTH_AUTH + '</button>';
						}
						return btn;
					},
				}
			]
		}
		$('#datatable').baseTableConfig().init(options);

		Metronic.unblockUI('.vcenter-manager-table-container');
	}

	var initParams = function(params) {
		var _params = {};
		_params.offset = params.offset;
		_params.limit = params.limit;
		_params.sort = params.sort;
		_params.order = params.order;
		_params.accurate_flag = queryParams.accurate_flag;
		_params.search_nickname = $.trim($('#searchInput').val());
		_params.hypervisor_type = $('#searchmodal #vmtype').val();
		_params.nickname = $('#searchmodal #nickName').val();
		_params.username = $('#searchmodal #userName').val();
		_params.platform_ip = $('#searchmodal #vcenterIp').val();
		return _params;
	}

	/**
	 * 构造权限校验所需参数
	 * @param type		操作类型
	 * @param user_uuid 资源所属用户uuid
	 * @returns {{user_uuid, auth: (string), type: number}|void|boolean}
	 */
	const checkAuth = (type, user_uuid = '') => {
		let select = grid.bootstrapTable("getSelections");
		let authKey = 'vmprotect';
		if (type === 'edit') {
			if (!select.length) {
				tipEdit();
				return false;
			}
			if (select.length > 1) {
				UIToastr.showInfo(LANG.UI_VCENTER_MODIFY, LANG.UI_VCENTER_MODIFY_TIPS);
				return false;
			}
			return {type: 1, user_uuid: select[0].user_uuid, auth: authKey};
		} else if (type === 'delete') {
			if (!select.length) {
				tipDelete();
				return false;
			}
			if (select.length > 1) {
				UIToastr.showInfo(LANG.UI_VCENTER_DELETE, LANG.UI_VCENTER_DELETE_TIPS1);
				return false;
			}
			return {type: 1, user_uuid: select[0].user_uuid, auth: authKey};
		} else if (type === 'nodeAllocation') {
			if (select.length !== 1) {
				UIToastr.showInfo(LANG.UI_VCENTER_ALLOCATION_NODE, LANG.UI_VCENTER_ALLOCATION_NODE_TIPS);
				return false;
			}
			return {type: 1, user_uuid: select[0].user_uuid, auth: authKey};
		} else if (type === 'add' || type === 'refresh') {
			// 取当前用户uuid判断权限
			return {type: 1, user_uuid: _userUuid, auth: authKey};
		} else if (type === 'sync' || type === 'authHost') {
			// 取传进来的用户uuid判断权限
			return {type: 1, user_uuid: user_uuid, auth: authKey};
		} else {
			return false;
		}
	}

    return {
        //main function to initiate the module
        init: function () {
        	initVMType(); //初始化虚拟化类型
			handleRecords();
        	initEngineData();//初始化平台备份数据管理按钮
			initrefreshTime();
            addListeners();
        }

    };

}();

jQuery(document).ready(function() {    
    VcenterManager.init();
});