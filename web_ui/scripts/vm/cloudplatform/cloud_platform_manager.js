var VcenterManager = function () {
	const CLOUD_TYPE_PRIVATE = 'private';
	const CLOUD_TYPE_PUBLIC = 'public';
	var checkIndex;
	var grid = $("#datatable");
	var gridInitFlag = false;
	var hostGrid = $('#hostdatatable');
	var authorizedFlag; //是否授权
	var queryParams = {accurate_flag:false};
	var cloudType;
	var _userUuid; // 当前用户uuid
	
	var tipSelect = function(){
		UIToastr.showInfo(LANG.UI_CLOUD_PLATFORM_SELECT, LANG.UI_CLOUD_PLATFORM_SELECT_TIPS);
	}
	
	var tipEdit = function(){
		UIToastr.showInfo(LANG.UI_CLOUD_PLATFORM_EDIT_SELECT, LANG.UI_CLOUD_PLATFORM_EDIT_SELECT_TIPS);
	}
	
	var tipDelete = function(){
		UIToastr.showInfo(LANG.UI_CLOUD_PLATFORM_DELETE_SELECT, LANG.UI_CLOUD_PLATFORM_DELETE_SELECT_TIPS);
	}

	//授权某个虚拟化中心
	var authHost = function (platform_uuid) {
		$('#vcenteruuid').val(platform_uuid);
		handleHostRecords(platform_uuid);
		handleLisenceInfo();
		if (authorizedFlag == 1) {
			$('#modalauthhost').modal({'width': '800px', 'height': '500px'});
		} else {
			return UIToastr.showWarning(LANG.UI_CLOUD_PLATFORM_CHECK_SYSTEM_AUTH, LANG.UI_CLOUD_PLATFORM_CHECK_SYSTEM_AUTH_TIPS);
		}
	}

	var syncImage = function (platform_uuid) {
		bootbox.confirm({
			title: LANG.UI_CLOUD_PLATFORM_SYNC_PROXY_IMAGE,
			message: LANG.UI_CLOUD_PLATFORM_SYNC_PROXY_IMAGE_TIPS,
			callback: debounce(function(r) {
				if(!r) return;
				Metronic.blockUI({target: '#cloud-platform-content', animate: true});
				pAjaxRequest({platform_uuid: platform_uuid}, "/api/v1/cloud/platform/sync_image", "POST", function (d) {
					Metronic.unblockUI('#cloud-platform-content');
					operateResponseList(d);
				}, true);
				grid.bootstrapTable('refresh');
			}, 300)
		});
	}
	
	var handleLisenceInfo = function(){
		pAjaxRequest({}, "/api/v1/system/vm_license", "GET", function (data) {
			var d = data.data;
			var html = "";
			if(1 == d.type){
				//宿主机授权
				html = LANG.UI_SETTING_AUTH_HOST + ': ' + LANG.UI_CLOUD_PLATFORM_AUTH_TOTAL + ' ' +
					d.total + ', ' + LANG.UI_SETTING_USED_NUM + ' ' + d.used + ', ' + LANG.UI_SETTING_VALID_NUM + ' ' + d.valid;
			}else if(2 == d.type){
				//CPU授权
				html = LANG.UI_SETTING_AUTH_CPU + ': ' + LANG.UI_CLOUD_PLATFORM_AUTH_TOTAL + ' ' +
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
					title: LANG.UI_CLOUD_PLATFORM_VCENTER,
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
					title: LANG.UI_CLOUD_PLATFORM_AUTH_AUTH,
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
		// 公有云平台屏蔽宿主机ip、CPU个数
		if (CLOUD_TYPE_PUBLIC == cloudType) {
			options.columns.splice(2, 1);
			options.columns.splice(4, 1);
		}
		$('#hostdatatable').bootstrapTable('destroy');
		$('#hostdatatable').baseTableConfig().init(options);
	}
	
	//同步单个虚拟化中心
	var doSyncVcenter = function (platform_uuid) {
		let p = {};
		p.platform_uuid = platform_uuid;
		Metronic.blockUI({target: '#cloud-platform-content', animate: true});
		pAjaxRequest(p, "/api/v1/vm/platforms/synchronization", "POST", function (data) {
			Metronic.unblockUI('#cloud-platform-content');
			if (data.success) {
				UIToastr.showSuccess(LANG.UI_CLOUD_PLATFORM_SYNC_TIPS, LANG.UI_CLOUD_PLATFORM_SYNC_SUCCESS_TIPS);
				grid.bootstrapTable("refresh");
			} else {
				if (5268 == data.code) {
					// 系统时间与网络时间时差超过15分钟
					UIToastr.showWarning(LANG.UI_CLOUD_PLATFORM_SYNC_TIPS, LANG.UI_PUBLIC_SYSTEM_TIME_ERROR_TIPS);
					return;
				}
				UIToastr.showWarning(LANG.UI_CLOUD_PLATFORM_SYNC_TIPS, LANG.UI_CLOUD_PLATFORM_SYNC_FAILED_TIPS);
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
	    //         title: LANG.UI_CLOUD_PLATFORM_SYNC_MORE_TITLE,
	    //         message: LANG.UI_CLOUD_PLATFORM_SYNC_MORE_CONTENT,
	    //         callback: function(r) {
	    //             if(!r) return;
	    //             var data = {};
	    //             data.ids = select;
	    //             data = JSON.stringify(data);
	    //     		Metronic.blockUI({target: '#cloud-platform-content',animate: true});
	    //     		$.post(CONF.AJAXPATH, {m:CONF.M.VCENTER,f:'syncVcenterMore',p:data}, function(d){
	    //     			Metronic.unblockUI('#cloud-platform-content');
	    //     			if(OPREL(d)){
	    //         			grid.getRefresh({});
	    //         		}
	    //         	});
	    //         }
	    //     });
		// }
	}
	
    var handleRecords = function () {
		var init = function () {
			tableInit(timerTask.CloudPlatformManager_platformList);
			timerTask.CloudPlatformManager_platformList = setTimeout(init, 10000);
		}
		init();
    	
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
    	return;
    }
    
    var addVcenter = function(){
		var navigation = 'infrastructure';
		
		LOCATION('./content/vm/cloudplatform/add_cloud_platform.php?cloudType=' + cloudType,navigation);
    }
    
    var editVcenter = function(){
    	var select = $('#datatable').bootstrapTable("getSelections");
		var url = './content/vm/cloudplatform/modify_cloud_platform.php?uuid=' + select[0].platform_uuid + '&cloudType=' + cloudType;
    	LOCATION(url, 'infrastructure');
    }
    
    
    var deleteVcenter = function(){
    	var select = $('#datatable').bootstrapTable("getSelections");
		bootbox.confirm({
            title: LANG.UI_CLOUD_PLATFORM_DELETE,
            message: LANG.UI_CLOUD_PLATFORM_DELETE_TIPS2,
            callback: debounce(function(r) {
                if(!r) return;
                submitDelete(select);
            }, 300)
        });
    }
    
    var submitDelete = function(select){
    	let data = {};
		data.platforms_uuids = [select[0].platform_uuid];
		Metronic.blockUI({target: '#cloud-platform-content',animate: true});
		pAjaxRequest(data, "/api/v1/vm/platforms", "DELETE", function (data) {
			Metronic.unblockUI('#cloud-platform-content');
			if (data.success) {
				UIToastr.showSuccess(LANG.UI_CLOUD_PLATFORM_DELETE, LANG.UI_CLOUD_PLATFORM_DELETE_SUCCESS_TIPS);
				grid.bootstrapTable("refresh");
			} else {
				UIToastr.showWarning(LANG.UI_CLOUD_PLATFORM_DELETE, data.message);
			}
		}, true);
    }
    
    var tipSelectHost = function(){
		UIToastr.showInfo(LANG.UI_CLOUD_PLATFORM_AUTH_SELECT_HOST, LANG.UI_CLOUD_PLATFORM_AUTH_SELECT_HOST_TIPS);
	}
    
    //添加授权
    var addlisence = function(){
		//authHandler(this, 'addHostAuth');
		authHandler(this, 'authorization');
    }
    
    //删除授权
    var deletelisence = function(){
		//authHandler(this, 'deleteHostAuth');
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
		}, false);
	}

	var refreshTime = function(){
    	var refresh = parseInt($('#refreshValue').val());
    	if(!refresh || refresh < 5){
    		UIToastr.showWarning(LANG.UI_CLOUD_PLATFORM_RFRESH_TIME_FAILD,LANG.UI_CLOUD_PLATFORM_RFRESH_TIME_FAILD_TIPS);
    		initrefreshTime();
    		return;
    	}
		let message = LANG.UI_CLOUD_PLATFORM_RFRESH_TIME_EDIT_TIPS;
		if (refresh < 30) {
			message += `<br>${LANG.UI_CLOUD_PLATFORM_RFRESH_TIME_EDIT_TIPS2}`;
		}
		bootbox.confirm({
			title: LANG.UI_CLOUD_PLATFORM_RFRESH_TIME_EDIT,
			message: message,
			callback: debounce(function(r) {
				if(!r) return;
				updateRefreshtime();
			}, 300)
		});
    } 
    //读取虚拟化中心自动刷新间隔时间
    var initrefreshTime = function(){
		pAjaxRequest({}, "/api/v1/vm/platforms/autorefresh", "GET", function (d) {
			_userUuid = d.data.user_uuid;
			var refresh_interval = d.data.public_cloud_platform_refresh_interval;
			if (CLOUD_TYPE_PRIVATE == cloudType) {
				refresh_interval = d.data.private_cloud_platform_refresh_interval;
			}
			var refreshTime = parseInt(refresh_interval) / 60;
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
			'refresh': refresh,
			'platform_type': cloudType
		};
		Metronic.blockUI({target: '#refreshValue', animate: true});
		pAjaxRequest(params, "/api/v1/vm/platforms/autorefresh", "PUT", function (data) {
			Metronic.unblockUI('#refreshValue');
			if (data.success) {
				UIToastr.showSuccess(LANG.UI_CLOUD_PLATFORM_RFRESH_TIME_SUCCESS, LANG.UI_CLOUD_PLATFORM_RFRESH_TIME_SUCCESS_TIPS);
				$('#refreshModal').modal('hide');
			} else {
				UIToastr.showWarning(LANG.UI_CLOUD_PLATFORM_RFRESH_TIME_FAILD, LANG.UI_CLOUD_PLATFORM_RFRESH_TIME_FAILD_TIPS);
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
			$(this).prop('placeholder', LANG.UI_CLOUD_PLATFORM_SEARCH_TIPS);
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

		// cloud_platform_table_tip_close 关闭
		$('#cloud_platform_table_tip_close').on('click', () => {
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
		if ($('#cloud_platform_table_tip').length > 0) { // alert div存在 96px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + alert 高度50px
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
    
    //显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('.searchContent').text('');
		var nickName = xssEncode($('#nickName').val());
		var userName = xssEncode($('#userName').val());
		var vcenterIp = xssEncode($('#vcenterIp').val());
		if (p.hypervisor != "0") {
			info += '<span id="hypervisor" title="' + $('#vmtype').find("option:selected").text() + '"> ' + LANG.UI_REPORT_CLOUD_PLATFORM + ': <i>' + $('#vmtype').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if (p.nickName) {
			info += '<span id="nickName" title="' + nickName + '">' + LANG.UI_SEARCH_NICKNAME + ': <i>' + nickName + '</i><em>X</em></span>';
		}
		if (p.userName) {
			info += '<span id="userName" title="' + userName + '">' + LANG.UI_SEARCH_CREATE_USER + ': <i>' + userName + '</i><em>X</em></span>';
		}
		if (p.vcenterIp) {
			let ipTitle = CLOUD_TYPE_PRIVATE == cloudType ? LANG.UI_CLOUD_PLATFORM_IP_ADDRESS : LANG.UI_CLOUD_PLATFORM_ACCOUNT_NUM;
			info += '<span id="vcenterIp" title="' + vcenterIp + '">' + ipTitle + ': <i>' + vcenterIp + '</i><em>X</em></span>';
		}
		$('.searchContent').append(info);
		$('#searchDiv').show();
		// 动态设置 table-container高度
		if ($('#cloud_platform_table_tip').length > 0) { // alert div存在 136px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + search-content的高度 40px + alett 高度50px
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
				if ($('#cloud_platform_table_tip').length > 0) { // alert div存在 96px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + alert 高度50px
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
			if ($('#cloud_platform_table_tip').length > 0) { // alert div存在 96px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + alert 高度50px
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
			if ($('#cloud_platform_table_tip').length > 0) { // alert div存在 96px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + alert 高度50px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 96px)');
			} else { // alert div不存在 46px = table-toolbar-wrapper 高度 34px + marin-bottom 12px
				$('.resource-manager-wrap .resource-manager-wrap__content .table-container.vcenter-manager-table-container').css('height', 'calc(100% - 46px)');
			}
		}
	}
    
  //初始化虚拟化类型
	var initVMType = function () {
		cloudType = $('#cloudType').val();
		var data = {};
		data.cloud_flag = true;
		data.cloud_type = cloudType;
		pAjaxRequest(data, "/api/v1/vm/platforms/hypervisors", "GET", function (d) {
			let hypervisors = d.data.hypervisors;
			var vmtypeselect = $('#vmtype');
			vmtypeselect.empty();
			var option = $("<option>").text(LANG.UI_SEARCH_ALL_CLOUD_PLATFORM).val('0');
			vmtypeselect.append(option);
			for (var i = 0; i < hypervisors.length; i++) {
				option = $("<option>").text(hypervisors[i].text).val(hypervisors[i].value);
				vmtypeselect.append(option);
			}
			vmtypeselect.val('0');
		}, false);
	}
	
	var tableInit = function (timeoutID) {
		if (!gridInitFlag) {
			clearTimeout(timeoutID);
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
				'click .syncImage': function (event, value, row, index) {
					checkOperateAuth(checkAuth('syncImage', row.user_uuid), () => {
						syncImage(row.platform_uuid);
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
				resizable: true,
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
						title: CLOUD_TYPE_PRIVATE == cloudType ? LANG.UI_CLOUD_PLATFORM_IP_ADDRESS : LANG.UI_CLOUD_PLATFORM_ACCOUNT_NUM,
						formatter: function (value, data) {
							if (CLOUD_TYPE_PRIVATE == cloudType) {
								return '<a href="/content/vm/cloudplatform/cloud_platform_details.php?cloudType=private&vcuuid=' + data.platform_uuid + '" class="ajaxify" name="infrastructure">' + value + '</a>';
							} else {
								return '<a href="/content/vm/cloudplatform/cloud_platform_details.php?cloudType=public&vcuuid=' + data.platform_uuid + '" class="ajaxify" name="infrastructure">' + value + '</a>';
							}
						}
					},
					{
						field: 'nickname',
						title: LANG.UI_SEARCH_NICKNAME,
					},
					{
						field: 'hypervisor_des',
						title: LANG.UI_REPORT_CLOUD_PLATFORM
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
						width: '6',
						widthUnit: '%',
					},
					{
						// 代理镜像同步状态，华为云使用
						field: 'image',
						title: LANG.UI_CLOUD_PLATFORM_PROXY_IMAGE,
						sortable: false,
						width: '8',
						widthUnit: '%',
						formatter: function (value, data) {
							if (CONF.VM_TYPE.HUAWEICLOUD != data.hypervisor_type) {
								return '--';
							}
							// 有跳过区域则显示
							let skipRegionHtml = '';
							if (data.detail.skip_region && data.detail.skip_region.length > 0) {
								let title = `${LANG.UI_CLOUD_PLATFORM_SKIP_REGION}:\n`;
								$.each(data.detail.skip_region, function (i, v) {
									title += `${v}\n`;
								})
								skipRegionHtml = `
									<a class="popovers" data-container="body" data-trigger="hover" data-html="true" 
										data-placement="right" data-content="" data-original-title="" title="${title}" aria-describedby="">
										<i class="viconfont vicon-tishi"></i>
									</a>
								`;
							}
							if (3 == data.detail.image_sync_status) {
								return '<span class="label label-sm label-danger">' + LANG.UI_CLOUD_PLATFORM_SYNC_FAILED + '</span>' + skipRegionHtml;
							} else if (2 == data.detail.image_sync_status) {
								return '<span class="label label-sm label-success">' + LANG.UI_CLOUD_PLATFORM_SYNCED + '</span>' + skipRegionHtml;
							} else if (1 == data.detail.image_sync_status) {
								return '<span class="label label-sm label-info">' + LANG.UI_CLOUD_PLATFORM_SYNCING + '</span>';
							} else {
								return '<span class="label label-sm label-warning">' + LANG.UI_CLOUD_PLATFORM_NOT_SYNC + '</span>';
							}
						}
					},
					{
						field: 'status',
						title: LANG.UI_CLOUD_PLATFORM_AUTHORIZE_STATUS,
						sortable: false,
						visible: cloudType == CLOUD_TYPE_PRIVATE,
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
						width: '20',
						widthUnit: '%',
						events: operates,
						formatter: function (value, data) {
							let btn = '';
							if (CLOUD_TYPE_PRIVATE == cloudType && CONF.PERMISSION_ARR.includes('p_cloud_platform_private_manager_sync')
								|| CLOUD_TYPE_PUBLIC == cloudType && CONF.PERMISSION_ARR.includes('p_cloud_platform_manager_sync')) {
								btn += '<button type="button" class="btn green-haze btn-sm sync" name="' + data.platform_uuid + '">' +
									'<i class="viconfont vicon-ge_refresh"></i> ' + LANG.UI_CLOUD_PLATFORM_SYNC + '</button>';
							}
							if (data.permission_flag && $.inArray('p_cloud_platform_private_manager_license', CONF.PERMISSION_ARR) !== -1
								&& [CONF.VM_TYPE.ZSTACK, CONF.VM_TYPE.NEXAVMNCSSV].includes(data.hypervisor_type) && ['account', 'tenant_platform'].includes(data.detail.account_type)) {
								// Zstack Cloud如果允许进行授权操作,且登录类型为账户（cpu授权）或租户-平台用户（宿主机授权），才添加授权按钮
								btn += '<button type="button" class="btn green-haze btn-sm authhost" name="' + data.platform_uuid + '">' +
									'<i class="viconfont vicon-ge_authorization2"></i> ' + LANG.UI_CLOUD_PLATFORM_AUTH_AUTH + '</button>';
							}

							if (CONF.VM_TYPE.HUAWEICLOUD == data.hypervisor_type && CONF.PERMISSION_ARR.includes('p_cloud_platform_manager_sync')) {
								// 华为云显示同步代理镜像按钮
								btn += '<button type="button" class="btn green-haze btn-sm syncImage" name="' + data.platform_uuid + '">' +
									'<i class="viconfont vicon-ge_refresh"></i> ' + LANG.UI_CLOUD_PLATFORM_SYNC_PROXY_IMAGE + '</button>';
							}
							return btn;
						},
					}
				]
			}
			// 公有云平台屏蔽版本号
			if (CLOUD_TYPE_PUBLIC == cloudType) {
				options.columns.splice(5, 1);
			} else {
				// 私有云平台屏蔽代理镜像状态
				options.columns.splice(9, 1);
			}
			gridInitFlag = true;
			grid.bootstrapTable('destroy');
			grid.baseTableConfig().init(options);
		} else {
			grid.bootstrapTable('refresh');
		}
	}

	var initParams = function(params) {
		var _params = {};
		_params.offset = params.offset;
		_params.limit = params.limit;
		_params.sort = params.sort;
		_params.order = params.order;
		_params.cloud_flag = true;
		_params.cloud_type = cloudType;
		_params.accurate_flag = queryParams.accurate_flag;
		_params.search_nickname = $.trim($('#searchInput').val());
		_params.hypervisor_type = $('#searchmodal #vmtype').val();
		_params.nickname = $('#searchmodal #nickName').val();
		_params.username = $('#searchmodal #userName').val();
		_params.platform_ip = $('#searchmodal #vcenterIp').val();
		return _params;
	}

	//记录勾选
	var checkRecord = function () {
		var checkArr = [];
		$.each(checkIndex, function (index) {
			checkArr.push(checkIndex[index].platform_uuid);
		});
		$('#datatable').bootstrapTable('checkBy', {
			field: 'platform_uuid',
			values: checkArr
		})
	}

	/**
	 * 构造权限校验所需参数
	 * @param type		操作类型
	 * @param user_uuid 资源所属用户uuid
	 * @returns {{user_uuid, auth: (string), type: number}|void|boolean}
	 */
	const checkAuth = (type, user_uuid = '') => {
		let select = grid.bootstrapTable("getSelections");
		let authKey = CLOUD_TYPE_PRIVATE === cloudType ? 'prcloud_protect' : 'awsprotect';
		if (type === 'edit') {
			if (!select.length) {
				tipEdit();
				return false;
			}
			if (select.length > 1) {
				UIToastr.showInfo(LANG.UI_CLOUD_PLATFORM_MODIFY, LANG.UI_CLOUD_PLATFORM_MODIFY_TIPS);
				return false;
			}
			return {type: 1, user_uuid: select[0].user_uuid, auth: authKey};
		} else if (type === 'delete') {
			if (!select.length) {
				tipDelete();
				return false;
			}
			if (select.length > 1) {
				UIToastr.showInfo(LANG.UI_CLOUD_PLATFORM_DELETE, LANG.UI_CLOUD_PLATFORM_DELETE_TIPS1);
				return false;
			}
			return {type: 1, user_uuid: select[0].user_uuid, auth: authKey};
		} else if (type === 'add' || type === 'refresh') {
			// 取当前用户uuid判断权限
			return {type: 1, user_uuid: _userUuid, auth: authKey};
		} else if (type === 'sync' || type === 'authHost' || type === 'syncImage') {
			// 取传进来的用户uuid判断权限
			return {type: 1, user_uuid: user_uuid, auth: authKey};
		}else {
			return false;
		}
	}

    return {
        //main function to initiate the module
        init: function () {
        	initVMType(); //初始化虚拟化类型
            handleRecords();
			initrefreshTime();
            addListeners();
        }

    };

}();

jQuery(document).ready(function() {    
    VcenterManager.init();
});