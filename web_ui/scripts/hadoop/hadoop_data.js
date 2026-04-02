var hadoop_data = function () {
	//时间点树
	var zTree,pointList,agentList,taskuuidList;
	var _daterangepicker_starttime, _daterangepicker_endtime, _daterangepicker_range;
	var clusteruuid, taskuuid, storageuuid;
	var _UserPassword;
	var initErrorFlag = false;
	var gridInitFlag = false
	var gfsList = {};//主要用于判断是否修改了GFS,如果没修改则不提交后台
	var showNode;//搜索到的时间点
    var showNode;//搜索到的时间点
	//事件监听
	var initListener = function(){
    
        //选择的集群改变
        $('#storageselect').on('change', storageselectChange);
        //设置GFS标记
        $("#mark_submit").on('click',function(){
            markSubmit();
        });
        $('#allDelete').on('click', deleteSelectPoint);
        //弹出高级搜索模态框
		$('#searchAll').on('click', function(){
			$('#searchmodal').modal({'width':'800px', 'height':'300px'});
		});
        //高级搜索发送请求到服务端
		$('#serach_submit').on('click', function(){
            var p = {};
			p.start_time = _daterangepicker_starttime; //任务开始时间查询范围开头
			p.end_time = _daterangepicker_endtime;		//任务开始时间查询范围结尾
			p.timepoint_type = $('#searchmodal #timepointType').val();
			p.forever = boolToInt($("input[name=foreverCheck1]").get(0).checked);
            p.task = taskuuid;
            p.cluster = clusteruuid;
            p.storage_uuid = storageuuid;
			$('#timepoint_table').bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return $.extend(queryParams, p);
				}
			});
			//添加搜索条件显示
			addSearchContent(p);
			$('#searchmodal').modal('hide');
		});
		//搜索模态框
		$('#filterSearch').click(function(){
			$('#filterSearch').popModal({
				html : $('#filter-content'),
				placement : 'bottomLeft',
				showCloseBut : true,
				onDocumentClickClose : true,
				onOkBut : searchHadoop,
				onCancelBut : function(){},
				onLoad : function(){},
				onClose : function(){},
				maxWidth: 300,
				maxHeight: 'auto',
			});
		});

        $('#searchDiv .clearSearch').on('click',function(){
			$('#searchDiv .searchContent').text('');
            $('#searchDiv').removeClass('opacity-100');
			$('#searchDiv').addClass('opacity-0');
            // 弹窗回到初始化状态
            $('#searchmodal #timepointType').val(0);
            var checkbox = $("input[name=foreverCheck1]");
            checkbox.iCheck('uncheck');
            checkbox.prop("checked",false);
            $('#daterangepicker').val('');
            _daterangepicker_starttime = "";
            _daterangepicker_endtime = "";
            $('#timepoint_table').bootstrapTable('refreshOptions', {
				queryParams: function (queryParams) {
					return {
						offset: queryParams.offset,
						limit: queryParams.limit,
						sort: queryParams.sort,
						order: 'desc',
						task: taskuuid,
						cluster: clusteruuid,
						storage_uuid: storageuuid
					}
				}
			});
		});
        $("#marktips").on('click',function(){
            $('.tabledata-wrapper__content__table .table-container').css('height','calc(100% - 60px)');
        })
	}

     //集群选择改变
     var storageselectChange = function(){
        storageuuid = $('#storageselect').val();
        initTree();
    }

    //初始化时间点树
	var initTree = function() {
        var data = {
            "storage_uuid":$('#storageselect').val() ==  null ? "": $('#storageselect').val(),
            "data_flag":true
        }
        var requestPointTree =  function(d){
            //初始化树
            if(!d.success) return;
			setTree(d.data);
        }
        pAjaxRequest(data, "/api/v1/hadoop/recovery/cluster/ztree", "GET", requestPointTree ,true);
	};
    //设置时间点树
    var setTree  =  function(zNodes){
        $('#hadooptimepointtree').show();
		$("#nopointtips").hide();
		if(zNodes.length == 0){
			$('#hadooptimepointtree').hide();
			$("#nopointtips").show();
			return;
		}
		var setting = {
				check: {
					enable: true,
					nocheckInherit: false
				},
				view: {
					nameIsHTML: true
				},
				data: {
					simpleData: {
						enable: true
					},
					key: {
						title: "title"
					}
				},
				callback: {
					onCheck: pointOnCheck,
					beforeClick: nodeSelect,
					beforeExpand: nodeExpand
				}
			};
		zTree = $.fn.zTree.init($("#hadooptimepointtree"), setting, zNodes);
    }
    //选择时间点节点事件绑定
    var nodeSelect = function(treeId, treeNode, clickFlag){
        if(2 == treeNode.type) {//集群名
			$('#fsdataUrl').text('');
			var parent = treeNode.getParentNode();
			var info = parent.name + "---" + treeNode.name;
			$('#fsdataUrl').append(info);
            clusteruuid =  treeNode.clusteruuid;
            taskuuid = treeNode.taskuuid;
            storageuuid = $('#storageselect').val();
            initTable(treeNode.clusteruuid, treeNode.taskuuid, storageuuid);
		}
		//异步加载时间点
		nodeExpand(treeId, treeNode);
		zTree.expandNode(treeNode, true);
    }
    //节点勾选
    var pointOnCheck  = function(event, treeId, treeNode){
    }

    //节点展开
    var nodeExpand =  function(treeId,treeNode){
        if(treeNode.clickshow){
			if(treeNode.children) return true;
			getRecoveryTimepoint(treeId, treeNode, false, false);
		}else{
			return true;
		}
    }
    //异步获取备份的时间点  refreshFlag是否重新刷新
    var getRecoveryTimepoint = function(treeId, treeNode, refreshFlag, expendFlag){
        var checkeFlag = treeNode.checked;
        var data = {
            taskuuid:treeNode.taskuuid,
            id:treeNode.id,
            refreshFlag:refreshFlag,
            clusteruuid:treeNode.clusteruuid,
            storageuuid:storageuuid,
            recoverflag:false,
            dataflag: true
        }
        var div = ".tree-wrapper__ztree";
		Metronic.blockUI({target: div,animate: true});
        var requestTimeTree =  function(d){
            Metronic.unblockUI(div);
            if(!d.success) {
                UIToastr.showInfo(LANG.UI_HADOOP_GET_TIMEPOINT,d.message);
                return;
            }else{
                if(checkeFlag) {
                    for(var i=0;i<d.data.length;i++) {
                        d.data[i].checked = true;
                    }
                }
                //获取到了时间点
                $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode,d.data, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
                if(expendFlag == true){
                    $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true, true, true);
                }
            }
        }
        pAjaxRequest(data, "/api/v1/hadoop/recovery/timepoint/ztree", "GET", requestTimeTree ,true);
    }

	
    //初始化集群列表
    var initClusterList =  function(){
        var requestClusterList =  function(d){
            var clusterselect = $('#clusterselect');
            clusterselect.empty();
            var  clusterlist =  d.data;
            for(var i = 0; i < clusterlist.length; i++){
                var option = $("<option>").text(clusterlist[i].cluster_name).val(clusterlist[i].cluster_uuid);
                clusterselect.append(option);
            }
            
        }
        pAjaxRequest({}, "/api/v1/hadoop/recovery/cluster/list", "GET", requestClusterList ,true);
    }


    //初始化存储列表
	var initStorageList =  function(){
		$.post(CONF.AJAXPATH, {m:CONF.M.STORAGE,f:'getStorageType',p:JSON.stringify({backupDataFlag: true})}, function(d){
			var data = JSON.parse(d);
			var stroageselect = $('#storageselect')
			stroageselect.empty();
			for(var i  = 0;i<data.length;i++){
				var option =  $("<option>").text(data[i].text).val(data[i].storageid);
				stroageselect.append(option);
			}
    	});
	}
    

    //初始化日期选择插件
    var inintDatatimePicker = function(){
        //初始化日期时间选择控件
        $('#daterangepicker').daterangepicker({
            "autoUpdateInput": false,											//是否自动填充input
            "startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
            "endDate": moment({hour: 23, minute: 59}),	//默认结束时间
            "minDate": moment().subtract(1, 'month'), //最早可以选的日期  
            "maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
            "timePicker": true,													//是否显示时间,时分
            "timePicker24Hour": true,											//是否是24小时制
            "alwaysShowCalendars": true,										//是否总是显示日期选择
            "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
            "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
        }, function(start, end, label) {
        });
        
        //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
        $('#daterangepicker').on('apply.daterangepicker', function(ev, picker) {
            //给全局变量赋值,然后设置input
            _daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
            _daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
            _daterangepicker_range = picker.chosenLabel;
            $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss'));
        });

        $('#daterangepicker').on('cancel.daterangepicker', function(ev, picker) {
            //清除全局变量,然后设置input
            _daterangepicker_starttime = "";
            _daterangepicker_endtime = "";
            _daterangepicker_range = "";
            $(this).val('');
        });
        
        //input右侧的图标事件
        $('.daterangepickerdiv i').click(function() {
            $(this).parent().find('input').click();
        });
    }

    //初始化右表格
    var initTable = function (clusteruuid,taskuuid,storageuuid) {
        let params = {
			clusteruuid : clusteruuid,
			taskuuid: taskuuid,
			storageuuid: storageuuid
		};
        //提示信息隐藏
         $("#tabletips").hide();
        var operateEvents = {
            'click .remark': function (event, value, row, index) {
                remarkPoint(row);
            },
            'click .setmark': function (event, value, row, index) {
                setMark(row);
            },
            'click .delete': function (event, value, row, index) {
                deletePoint(row);
            },
        }
        if(!gridInitFlag){
            var options = {
                searchInput: false,
                toolbarId: '#vin_hadoopdata_toolbar',
                vin_toolbar: '.vin_hadoopdata_toolbar',
                vin_url:"/api/v1/hadoop/restore_data/",
                vin_method:"GET",
                pagination: true,
                pageList:[5,10,25,50],
                advanceSearch:{module:'hadoopdata'},
                changeHeightBtn: false,
                paginationLoop: false,
                resizable:true,
                sortName: 'timepoint',
                sortOrder: 'desc',
                queryParams: function(p){
                    return {
                        limit: p.limit,
                        offset: p.offset,
                        order: p.order,
                        sort: p.sort,
                        task: params.taskuuid,
                        cluster: params.clusteruuid,
                        storage_uuid: params.storageuuid
                    }
                },
                columns:[
                    {
                        field: 'timepoint',
                        title: LANG.UI_RECOVERY_TIMEPOINT,
                        sortable: true,
                        align: 'center',
                        formatter: function (timepoint, row) {
                            return '<span title="' + row.timepoint_uuid + '"> ' + timepoint + '</span>';
                        }
                    },
                    {
                        field: 'timpoint_type_desc',
                        title: LANG.UI_STORAGE_TYPE,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'total_size',
                        title: LANG.UI_COPY_DATA_SIZE,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'write_size',
                        title: LANG.UI_PUBLIC_REAL_SIZE,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'storage',
                        title: LANG.UI_BACKUP_FILE_STORAGE,
                        sortable: true,
                        align: 'center',
                    },
                    {
                        field: 'user_uuid',
                        title: LANG.UI_CLIENT_OWNER,
                        sortable: true,
                        align: 'center',
                        formatter: function(index, row) {
                            return `<span>${row.owner}</span>`;
                        }
                    },
                    {
                        field: 'operate',
                        title: LANG.UI_PUBLIC_OPERATION,
                        sortable: false,
                        clickToSelect: false, //不可通过点击行选中
                        opButton:true,
                        align: 'center',
                        // type: 'operation',
                        events: operateEvents,
                        formatter: function (value, row, index, field) {
                            var mode = row['backup_mode'];
                            var opCode =  row['op'];
                            var button  = '<div class="btn-group dropdown-wrapper">';
                            if (index > 4) {
                                button = '<div class="btn-group dropdown-wrapper dropup">';
                            }
                            button += '<button type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown" ' + 
                                'data-hover="dropdown" data-delay="1000" data-close-others="true" aria-expanded="false">' + 
                                LANG.UI_PUBLIC_OPERATION + ' <i class="fa fa-angle-down"></i>' + 
                                '</button>' + 
                                '<ul class="dropdown-menu" role="menu">';
                                if(mode ==1){
                                    $.each(opCode, function(i, d){
                                        switch(d){
                                            case 1:
                                                button += '<li class="remark"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_modify me-4"></i> ' + LANG.UI_PUBLIC_REMARK + '</button></li>';
                                                break;
                                            case 2:
                                                button += '<li class="delete"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_delete me-4"></i> ' + LANG.UI_PUBLIC_DELETE + '</button></li>';
                                                break;
                                            case 3:
                                                button += '<li class="setmark"><button class="btn dropdown-menu__item me-0" type="button"><i class="viconfont vicon-ge_sign me-4"></i> ' + LANG.UI_SETTING_VM_DATA_SET_MARK + '</button></li>';
                                                break;
                                        }
                                    });
                                }else{
                                    $.each(opCode, function(i, d){
                                        switch(d){
                                            case 1:
                                                button += '<li class="remark"><button class="btn dropdown-menu__item me-0" type="button" ><i class="viconfont vicon-ge_modify me-4"></i> ' + LANG.UI_PUBLIC_REMARK + '</button></li>';
                                                break;
                                        }
                                    });
                                }
                            button += '</ul></div>';
                            return button;
                        }
                    },
                ],
                onPostBody:function(){
                    $('.remarktips').popover();	
                    $('#timepoint_table th[data-field="timepoint"]').css('width', '25%');
                    $('#timepoint_table th[data-field="timpoint_type_desc"]').css('width', '10%');
                    $('#timepoint_table th[data-field="total_size"]').css('width', '10%');
                    $('#timepoint_table th[data-field="write_size"]').css('width', '10%');
                    $('#timepoint_table th[data-field="storage"]').css('width', '25%');
                    $('#timepoint_table th[data-field="user_uuid"]').css('width', '10%');
                    $('#timepoint_table th[data-field="operate"]').css('width', '10%');
                },
                onRefresh: function (params) {
                     $('#timepoint_table').bootstrapTable('hideLoading');
                },
    
    
            }
            $('#timepoint_table').baseTableConfig().init(options);
            gridInitFlag = true;
            $('#tabletips').hide();
            $('#hadooptable').show();
            $('#marktips').show();
        }else{
            //刷新表格
            $('#timepoint_table').bootstrapTable('refreshOptions', {
				queryParams: function (p) {
					return {
						limit: p.limit,
						offset: p.offset,
						order: p.order,
						sort: p.sort,
                        task: params.taskuuid,
                        cluster: params.clusteruuid,
                        storage_uuid: params.storageuuid
					}
				}
			});
        }

    }

    
    //添加备注
    var remarkPoint = function(row){
        var value = row.remark;
        bootbox.prompt({
            title: LANG.UI_DATA_ADD_REMARK,
            value:value,
            callback: debounce( function(result) {
                if(result == null || $.trim(result) == value) return;
                submitRemark($.trim(result), row);
            },300)
        });
    }
    var submitRemark = function(remark, row){
        var params = {
            remark:remark,
            timepoint_uuid:row.timepoint_uuid
        }
        Metronic.blockUI({target: '#timepoint_table',animate: true});
        pAjaxRequest(params, "/api/v1/hadoop/restore_data/remark/timepoint", "POST", function(d){
            Metronic.unblockUI('#timepoint_table');
            if(d.success){
                UIToastr.showSuccess(LANG.UI_HADOOP_TIMEPOINT_ADD_REMARKS,LANG.UI_HADOOP_TIMEPOINT_ADD_REMARKS_SUCCESS); 
                refreshTreeTable(remark,row);
            }else{
                UIToastr.showInfo(LANG.UI_HADOOP_TIMEPOINT_ADD_REMARKS, LANG.UI_HADOOP_TIMEPOINT_ADD_REMARKS_FAIL);
            }
        });
    }

    //点击设置标记
    var setMark  =  function(row){
         //得到GFS保留标记
         gfsList = {};
         gfsList.forever = row.importance_flag;//永久保留
        //初始化清空所有勾选项
        $("input[name=foreverCheck]").iCheck('uncheck');
        //设置为只有完全备份才可GFS
        if(row['backup_mode'] != 1){
            $("input[name=foreverCheck]").iCheck('disable');
        }else{
            $("input[name=foreverCheck]").iCheck('enable');
        }
        //设置值
        $("#Marktimepoint_uuid").val(row.timepoint_uuid);
        //设置勾选
        if(gfsList.forever){
            $("input[name=foreverCheck]").iCheck('check');
        }
        $('#setAllMark').modal('show');
    }

    //设置标记确认
	var markSubmit = function(){
		//先得到所有信息
		var forever = $("input[name=foreverCheck]").get(0).checked;
		//得到信息后先判断是否有做修改 如果没有做修改则不提交后台
		if(gfsList.forever == forever){
			$('#setAllMark').modal('hide');
			gfsList = {};
			return;
		}
		//时间点uuid
		var uuid = $("#Marktimepoint_uuid").val();
		//设置永久标记
		if(gfsList.forever != forever){
			if(forever){
				var params = {};
				params.uuid = uuid;
				starHandler(params,'addStar',forever)
			}else{
				var params = {};
				params.uuid = uuid;
				starHandler(params,'deleteStar',forever)
			}
		}
		$('#setAllMark').modal('hide');
		
	}

    //标星统一处理
    var starHandler = function(params, funName,forever){
        Metronic.blockUI({target: '#setAllMark',animate: true});
        // pAjaxRequest(params, "/api/v1/kubernetes/restore_data/remark/timepoint", "POST", refreshTreeTable(remark,row),true);
        //添加星标
        if(funName == "addStar"){
            pAjaxRequest(params, "/api/v1/hadoop/restore_data/addstar", "POST",  function(d){
                Metronic.unblockUI('#setAllMark');
                if(d.success){
                    UIToastr.showSuccess(LANG.UI_OS_DATA_SET_MARK,LANG.UI_NAS_DATA_SET_MARK_SUCCESS);
                    var row  ={};
                    row.importance_flag  = forever;
                    row.timepoint_uuid = params.uuid;
                    refreshTreeTable("",row)
                }else{
                    UIToastr.showInfo(LANG.UI_OS_DATA_SET_MARK, LANG.UI_SETTING_VM_MARK_POINT_FAIL);
                }
            });

        }
        if(funName == "deleteStar"){
            pAjaxRequest(params, "/api/v1/hadoop/restore_data/deletestar", "DELETE",  function(data){
                Metronic.unblockUI('#setAllMark');
                var row  ={};
                row.importance_flag  = forever;
                row.timepoint_uuid = params.uuid;
                refreshTreeTable("",row)
            });
        }
    }

    //删除时间点
    var deletePoint = function(row){
        if(row.storage_type == CONF.BD_STORAGE_TYPE.TAPE){
            UIToastr.showInfo(LANG.UI_DATA_DELETE_TIMEPOINT,LANG.UI_TAPE_DELETE_BACKUP_POINT_TIPS);
			return;
        }
        bootbox.confirm({
            title: LANG.UI_DATA_DELETE_TIMEPOINT,
            message: LANG.UI_DATA_DELETE_TIMEPOINT_TIPS,
            callback: debounce(function(r) {
                if(!r) return;
                initErrorFlag = false;
                bootbox.prompt({
                    title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES,
                    inputType: 'password',
                    callback: debounce(function (result) {
                        if(result == null) return;
                        if(hex_md5(result) == _UserPassword){
                            submitDelete(row);
                            return true;
                        }else{
                            $('.bootbox-input').css('border-color', "#a94442");
                            if(!initErrorFlag){
                                var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS+'</p>';
                                $('.bootbox-input').after(des);
                                initErrorFlag = true;
                            }
                            return false;
                        }
                    }, 300)
                });
            },300)
        });
    }

    //确认删除所选中的时间点
    var submitDelete = function(row){
        var params = {
            timepoint_uuid: row.timepoint_uuid,
            cluster_uuid: row.cluster_uuid,
            job_uuid: row.job_uuid
		};
		Metronic.blockUI({target: '#timepoint_table',animate: true});
    	pAjaxRequest(params, "/api/v1/hadoop/restore_data/timepoint", "DELETE", function (d) {
			Metronic.unblockUI('#timepoint_table');
			if (operateResponseList(d, LANG.UI_DATA_DELETE_TIMEPOINT)) {
                // getParams();
                $("#timepoint_table").bootstrapTable("refresh");
                var  idStr =  params.timepoint_uuid;
                //先刷新界面树
    			refreshTree(row.timepoint_uuid);
                var node = zTree.getNodesByParam("id", idStr, null);
                //如果还有时间节点执行刷新树操作
				if(node.length !=0){
					getSyncVcenterInfo("vcenter_tree", node[0], true, true);
				}
                
			}
           
		});
    }
    //如果把一个任务下所有时间点删除后,需要把这个树上的任务移除
	var refreshTree = function(nodeid){
		var nodes = zTree.getNodesByParam("id", nodeid, null);
		if(0 == nodes.length){
			return;
		}
		//要删除的节点
		var fsNode = nodes[0];
		//要删除节点的父节点,如果父节点只有1个孩子节点,把父节点也要删除,父节点是任务名,不再处理任务名的父节点
		var parent = fsNode.getParentNode();
		if(!parent) return;
		var childrenNum = parent.children.length;
		if(1 == childrenNum && fsNode.type == 3){
			zTree.removeNode(parent);
			removeParentNode(parent);
		}else{
			zTree.removeNode(fsNode);
		}
	}
	//删除父节点
	var removeParentNode = function(node){
		var parent = node.getParentNode();
		if(!parent) return;
		var childrenNum = parent.children.length;
		if(0 == childrenNum){
			zTree.removeNode(parent);
			removeParentNode(parent);
		}else{
			return;
		}
		
	}
    //删除时间点
	var deleteSelectPoint = function(){
		var node = zTree.getCheckedNodes(true);
		if(node.length == 0){
			UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_NOT_CHECK,LANG.UI_DATA_BATCH_DELETE_NOT_CHECK_TIPS);
			return;
		}
        var tapeflag =  false;
        //获取选中的时间点有没有在磁带上的
        for(var j = 0;j < node.length;j++){
            if(node[j].storagetype == CONF.BD_STORAGE_TYPE.TAPE){
                tapeflag = true;
                break;
            }
        }
        if(tapeflag){
            UIToastr.showInfo(LANG.UI_DATA_BATCH_DELETE_NOT_CHECK,LANG.UI_TAPE_DELETE_BACKUP_POINT_TIPS);
			return;
        }
		pointList = [];
		agentList = [];
		// taskuuidList = [];
		for(var i=0; i<node.length; i++){
			if(node[i].type == 2){//agent
				if(!node[i].children){
					var agent = {};
					agent.agentuuid = node[i].clusteruuid;
					agent.taskuuid = node[i].taskuuid;
					agent.nodeuuid = node[i].nodeuuid;
					agent.type = node[i].type;
					agentList.push(agent);
				}
			}else if(node[i].type == 3){//时间点
				var point = {};
			    point.timepointuuid = node[i].point_uuid;
			    point.nodeuuid = node[i].nodeuuid;
			    point.type = node[i].type;
			    pointList.push(point);
			    if(node[i].isParent){
			    	var children = node[i].children;
			    	for(var j=0;j<children.length;j++){
			    		var point = {};
			    		point.timepointuuid = children[j].point_uuid;
			    		point.nodeuuid = children[j].nodeuuid;
			    		point.type = children[j].type;
			    		pointList.push(point);
			    	}
			    }
			}
		}
		bootbox.confirm({
            title: LANG.UI_DATA_DELETE_TIMEPOINT,
            message: LANG.UI_DATA_DELETE_TIMEPOINT_TIPS,
            callback: debounce(function(r) {
                if(!r) return;
                initErrorFlag = false;
                bootbox.prompt({ 
				    title: LANG.UI_TENANT_DELETE_COFIRM_INPUT_YES, 
				    inputType: 'password',
				    callback: debounce(function (result) {
				    	if(result == null) return;
				        if(hex_md5(result) == _UserPassword){
							//如果是归档数据，三次弹出确认框再确认
                            submitSelectDelete();
                            return true;
				        }else{
				        	$('.bootbox-input').css('border-color', "#a94442");
				        	if(!initErrorFlag){
				        		var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_DB_DM_CORRECT_PASSWORD_INPUT_TIPS+'</p>';
					        	$('.bootbox-input').after(des);
				        		initErrorFlag = true;
				        	}
				        	return false;
				        }
				    },300)
				});
            },300)
        });
	}

    //提交删除备份时间点
	var submitSelectDelete = function(){
		var node = zTree.getCheckedNodes(true);
		var params = {pointList:pointList, agentList: agentList};
		Metronic.blockUI({target: '#timepointdiv',animate: true});
        pAjaxRequest(params, "/api/v1/hadoop/restore_data/timepoint", "DELETE", function (d) {
			Metronic.unblockUI('#timepointdiv');
			if (operateResponseList(d, LANG.UI_DATA_DELETE_TIMEPOINT)) {
                for(var i=0;i<node.length;i++){
					var halfCheck = node[i].getCheckStatus();
					if(!halfCheck.half){
						refreshAllTree(node[i].id);
					}else{
						zTree.checkNode(node[i],!node[i].checked,false,false);
					}
				}
				// getParams();
                $("#timepoint_table").bootstrapTable("refresh");
			}
           
		});
	}

    //一一对应timepointuuid进行清除树节点
	var refreshAllTree = function(nodeid){
		var nodes = zTree.getNodesByParam("id", nodeid, null);
		if(0 == nodes.length){
			return;
		}
		//要删除节点的父节点,如果父节点只有1个孩子节点,把父节点也要删除,父节点是任务名,不再处理任务名的父节点
		var parent = nodes[0].getParentNode();
		if(parent !=null){
			var childrenNum = parent.children.length;
		}
		if(1 == childrenNum){
			zTree.removeNode(parent);
		}else{
			zTree.removeNode(nodes[0]);
		}
	}

    //搜索hadoop集群
    var searchHadoop = function () {
        if ($.trim($('#searchfs').val()) == "" && !$("input[name=foreverFilter]").get(0).checked) {
            return;
        }
        var data = {
            "storage_uuid":$('#storageselect').val() ==  null ? "": $('#storageselect').val(),
            "data_flag":true
        }
        var requestPointTree =  function(d){
            //初始化树
            if(!d.success) return;
			setTree(d.data);
            nodeParamList = "";
			nodeParamList = zTree.getNodesByFilter(filterZtree)
            //异步搜索时间点
            var param = {}
			param.search = $('#searchfs').val();
			param.forever = $("input[name=foreverFilter]").get(0).checked;
            param.storage_uuid = $('#storageselect').val() ==  null ? "": $('#storageselect').val();
            param.data_flag = true;
            var  searchFsTimepoint =  function(d){
                Metronic.unblockUI('#hadooptimepointtree');
                if(!d.success) return;
                var pointNode =  d.data;
                showNode = [];
                if(pointNode != null) {
					pointNode.forEach(item => {
						//在当前树节点中搜索异步请求得到的时间点，如果当前树不存在，加入showNode数组
						var nodeExist = zTree.getNodesByParam("id", item.id, null)[0];
						if(nodeExist == null) {
							showNode.push(item);
						}
					});
					//把showNode中时间点加到对应父节点下
					if(showNode.length != 0) {
						showNode.forEach(item => {
							//先把完备点加进去，再放增量差异等，避免先搜增量差异等搜不到父节点
							if(item.type == 3) {
								var parentNode = zTree.getNodesByParam("id", item.pId, null)[0];
								zTree.addNodes(parentNode, item, true);
							}
						});
						showNode.forEach(item => {
							if(item.type == 4) {//增量差异等
								var parentNode = zTree.getNodesByParam("id", item.pId, null)[0];
								zTree.addNodes(parentNode, item, true);
							}
						});
					}
				}
                searchByTree();
            }
            Metronic.blockUI({target:'#hadooptimepointtree',animate: true});
            pAjaxRequest(param, "/api/v1/hadoop/restore/data/search", "GET", searchFsTimepoint ,true);
            
        }
        pAjaxRequest(data, "/api/v1/hadoop/recovery/cluster/ztree", "GET", requestPointTree ,true);
    
    }

    var searchByTree = function() {
		var keyword = $.trim($('#searchfs').val());
		var allNodes = zTree.transformToArray(zTree.getNodes());
		zTree.hideNodes(allNodes);    //当开始搜索时，先将所有节点隐藏
		var nodeList = [];
		if(keyword != '') {
			nodeList = zTree.getNodesByParamFuzzy('name', keyword, 0);    //通过关键字模糊搜索
		}
		//搜gfs标记点
		if($("input[name=foreverFilter]").get(0).checked) {
			nodeList =  zTree.getNodesByParamFuzzy('name', 'viconfont vicon-remark-forever', 0);
		}
 		if(nodeList.length == 0) {
			$('#nosearchtips').show();
			return;
		}
		var arr = new Array();
		for(var i=0; i<nodeList.length; i++){
			arr = $.merge(arr,nodeList[i].getPath());    //找出节点的所有父节点（包括自己）
			if(nodeList[i].children) {//展开过，有子节点，把子节点加进去
				arr = $.merge(arr,nodeList[i].children);
			}
		}
		var firstlevel = [];
		var otherlevel = [];
		arr.forEach(item=>{
			if(item.level != 0) {
				otherlevel.push(item);
			}else {
				firstlevel.push(item);
			}
		});
		var arr = zTree.transformToArray(otherlevel);//避免获取到第一层下的其他任务
		arr = arr.concat(firstlevel);
		zTree.showNodes($.unique(arr));    //显示所有要求的节点及其路径节点
		//展开显示的所有节点
		arr.forEach(item => {
			if(item.children) {
				zTree.expandNode(item, true);
			}
		});
	}
    //ztree的复杂过滤
	var filterZtree = function(node){
		var markStr = LANG.UI_SETTING_VM_DATA_SCREEN + ": ";
		var resultVal = false;
		var resultForever = false;
		
		//获得数据
		var value = $('#searchfs').val();
		var forever = $("input[name=foreverFilter]").get(0).checked;
		if(node.level==0 || node.level==1) {
			var ThisName = node.name;
		}else {
			var ThisName = node.oldname;
		}
		if(value == "" && !forever){
			$("#markStr").empty();
			return true;
		}
		if(forever){
			var foreverval = "viconfont vicon-remark-forever";
			if(node.gfsforever!=undefined&&node.gfsforever.indexOf(foreverval) > -1){
				resultForever = true;
			}
			markStr += '<i class="viconfont vicon-remark-forever"></i>'
		}
		//检测
		if(value != ""){
			if(ThisName.indexOf(value) > -1){
				resultVal = true;
			}
			markStr += value;
		}
		markStr += '<a id="clearGFS" style="text-decoration: underline;margin-left: 10px;">'+ LANG.UI_SETTING_VM_DATA_SCREEN_CLEAN +'</a>'
		$("#markStr").html(markStr);
		//添加清除点击事件
		$("#clearGFS").on('click',function(){
			//得到所有节点集合
			var Nownodes = zTree.getNodes();
			// 删除所有新搜索出来的时间点，避免异步加载不加载其他节点
			showNode.forEach(item => {
				var delnode = zTree.getNodesByParam("id", item.id, null)[0];
				if(delnode != null && delnode.getParentNode() != null) {
					zTree.removeChildNodes(delnode.getParentNode());
					delnode.getParentNode().isParent = true;
					zTree.updateNode(delnode.getParentNode()); 
				}
			});
			//得到所有隐藏的节点
			var nodes = zTree.getNodesByParam("isHidden", true);
			//显示所有被隐藏的节点
			zTree.showNodes(nodes);
			//显示div
			$("#filetimepointtree").show();
			$("#nopointtips").hide();
			$('#nosearchtips').hide();
			if(Nownodes.length ==0){
				$("#nopointtips").show();
			}
			$("#markStr").empty();
		})
		if(resultVal || resultForever){
			return true;
		}else{
			return false;
		}
	}


    //刷新树和表格
     var refreshTreeTable =  function(remark,row){
        //先刷新表
        // getParams();
        $("#timepoint_table").bootstrapTable("refresh");
        //再更新树节点
        editZtreeName(row.timepoint_uuid,row.weekly_flag,row.monthly_flag,row.yearly_flag,row.importance_flag,remark);
    }

    //静态修改Ztree的名称
    var editZtreeName = function(timepointUUID,$Wflag,$Mflag,$Yflag,$Fflag, remark){
        //根据timeUUID得到ztree的node数据，没有搜到返回null
        var treeObj = $.fn.zTree.getZTreeObj("hadooptimepointtree");
        var node = treeObj.getNodeByParam('timepointuuid', timepointUUID, null);
        if (!node) {
            return;
        }
        //获得原来的name
        var old_name = node.oldname;
        var markStr = '';
        if($Wflag){
            markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_WEEK_POINT+'" class="viconfont vicon-remark-week"></i>';
        }
        if($Mflag){
            markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_MONTH_POINT+'" class="viconfont vicon-remark-month"></i>';
        }
        if($Yflag){
            markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_YEAR_POINT+'" class="viconfont vicon-remark-year"></i>';
        }
        if($Fflag){
            markStr +='<i title="'+LANG.UI_SETTING_VM_GFS_FOREVER_POINT+'" class="viconfont vicon-remark-forever"></i>';
        }
        var new_name = old_name+" "+markStr;
        
        node.name = new_name;
        treeObj.updateNode(node);
        //清除已存在的备注
        $('#remark_'+timepointUUID).remove();
    }
    //初始化当前用户密码用于删除二次确认
	var initUserPassword = function(){
		$.post(CONF.AJAXPATH,{m:CONF.M.USER,f:"getUserPassword",p:{}},function(d){
			var data = JSON.parse(d);
			_UserPassword = data.password;
		});
	}
    //布尔类型转成int1和2
	var boolToInt = function(thisbool){
		if(thisbool){
			return 1;
		}else{
			return 2;
		}
		
	}

    //显示搜索内容
	var addSearchContent = function(p){
		var info = "";
		$('.searchContent').text('');
		if(p.start_time && p.end_time){
			info += '<span id="time" title="' + p.start_time + "~" + p.end_time + '"> '+ LANG.UI_SEARCH_TIME_RANGE +': <i>' + p.start_time + "~" + p.end_time + '</i><em>X</em></span>';
		}
		if(p.timepoint_type != "0"){
			info += '<span id="timepoint_type" title="' + $('#timepointType').find("option:selected").text() + '"> '+ LANG.UI_SEARCH_TYPE +': <i>' + $('#timepointType').find("option:selected").text() + '</i><em>X</em></span>';
		}
		if(p.forever != "2"){
			info += '<span id="forever" title="' + LANG.UI_SETTING_VM_GFS_FOREVER_POINT + '"> '+ LANG.UI_SEARCH_STAR +': <i>' + LANG.UI_SETTING_VM_GFS_FOREVER_POINT + '</i><em>X</em></span>';
		}
		
		$('.searchContent').append(info);
        $('#searchDiv').removeClass('opacity-0');
		$('#searchDiv').addClass('opacity-100');
		$('#searchDiv .searchContent em').on('click', function(){
			$(this).parent().remove();
			var searchContent = $('#searchDiv .searchContent');
			if(searchContent[0].children.length == 0){
                $('#searchDiv').removeClass('opacity-100');
				$('#searchDiv').addClass('opacity-0');
			}
			var parent  = $(this).parent();
			var id = parent[0].id;
            //清除搜索内容
			if(id == "time"){
				p.start_time = "";
				p.end_time = "";
                $('#daterangepicker').val('');
                _daterangepicker_starttime = "";
                _daterangepicker_endtime = "";
			}else if(id == "timepoint_type"){
				p.timepoint_type = 0;
                $('#searchmodal #timepointType').val(0);
			}else if(id == "forever"){
				p.forever = 2;
                var checkbox = $("input[name=foreverCheck1]");
                checkbox.iCheck('uncheck');
                checkbox.prop("checked",false);
			}
			$('#timepoint_table').bootstrapTable('refresh',{query:p});
		});
		//如果没搜索条件，先隐藏div
		if(!info){
            $('#searchDiv').removeClass('opacity-100');
			$('#searchDiv').addClass('opacity-0');
		}
	}
	
    return {
        //main function to initiate the module
        init: function () {
			inintDatatimePicker();
        	// initClusterList(); //初始化集群下拉列表
            initStorageList(); //初始化存储列表
            initTree();
            initListener();
            initUserPassword();
    
        }

    };

}();

jQuery(document).ready(function() {    
    hadoop_data.init();
});