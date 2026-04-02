var hadoop_cluster  = function(){
    //初始化间隔值
    var refreshTimeVal = 60;


    const HADOOP_STATUS_DESC = {
        'online': LANG.UI_CLOUD_PLATFORM_ONLINE,
        3: LANG.UI_CLOUD_PLATFORM_OFFLINE,
        4: LANG.UI_NODE_ABNORMAL,
    };
    var changeHeightFlag = false;
    var lastIndex = [-1, -1];
    var _dateRangePicker_startTime = '', _dateRangePicker_endTime = '', _dateRangePicker_range = '';
    var advancedSearch = {};
    var authInfo = {};
    var initDataTable =  function(){
        var options  = {
            vin_url:"/api/v1/hadoop/cluster",
            vin_method:"GET",
            searchInput:false,
            search:false,
            pagination:true,
            pageList:[10,20,50,100,150,200],
            changeHeightBtn:false,
            detailView:true,
            detailFormatter:cluster_detail,
            showJumpTo:true,
            fullPage:true, //全屏表格高度适配
            sortName: 'add_time',
            sortOrder: 'desc',
            onPostBody:function (){
                btnDisplayClass();
                $('[data-toggle="tooltip"]').tooltip();
                $('#cluster_table th[data-field="cluster_name"]').css('width', '9%');
                $('#cluster_table th[data-field="node_number"]').css('width', '6%');
                // $('#cluster_table th[data-field="auth_status"]').css('width', '7%');
                $('#cluster_table th[data-field="online_flag"]').css('width', '7%');
                $('#cluster_table th[data-field="add_time"]').css('width', '13%');
                $('#cluster_table th[data-field="refresh_time"]').css('width', '13%');
                $('#cluster_table th[data-field="creator"]').css('width', '6%');
                $('#cluster_table th[data-field="owner"]').css('width', '6%');
            },
            onCheckAll: function (res) {
				modifyDelStyle('cluster_table', 'deleteSelect');
			},
            onCheck: function (res) {
				modifyDelStyle('cluster_table', 'deleteSelect');
			},
			onUncheck: function () {
				modifyDelStyle('cluster_table', 'deleteSelect');
			},
            onUncheckAll: function () {
				modifyDelStyle('cluster_table', 'deleteSelect');
			},
            onPostBody: function () {
                let tableData = $('#cluster_table').bootstrapTable('getData');

                if (tableData.length === 0) { // 空data保证fixed-table-container高度100%，以消除 bs-table 中计算的乱七八糟的错误高度
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container .bootstrap-table .fixed-table-container').css('height', '100%');
                } else {
                    // 52px 是 分页 fixed-table-pagination 的高度
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container .bootstrap-table .fixed-table-container').css('height', 'calc(100% - 52px)');
                }
            },
            columns:[
                {
                    checkbox:true,
                    sortable:false,
                    formatter: function (value, row, index) {
                        if (row.op_flag === false) { // 创建者等不是当前用户，不能操作
                            return {
                                disabled: true
                            };
                        }
                    }
                },
                {
                    field:"cluster_name",
                    title:LANG.UI_HADOOP_CLUSTER_NAME,
                    sortable:true,
                    align:"left"
                },
                {
                    field:"node_number",
                    title:LANG.UI_HADOOP_NODE_NUM,
                    sortable:true,
                    align:"left"
                },
                {
                    field:"add_time",
                    title:LANG.UI_PUBLIC_ADD_TIME,
                    sortable:true,
                    align:"left"
                },
                {
                    field:"online_flag",
                    title:LANG.UI_HADOOP_CLUSTER_STATUS,
                    sortable:true,
                    align:"left",
                    formatter:function(value){
                        let labelHtml = "";
                        switch(value){
                            case 0:
                                labelHtml = '<span class="label label-sm label-danger status-icon">' + LANG.UI_PUBLIC_UNKNOWN + '</span>';
                                break;
                            case 1:
                            case 2:
                                labelHtml = '<span class="label label-sm label-success status-icon">' + LANG.UI_CLOUD_PLATFORM_ONLINE + '</span>';
                                break;
                            case 3:
                                labelHtml = '<span class="label label-sm label-danger status-icon">' + LANG.UI_CLOUD_PLATFORM_OFFLINE + '</span>';
                                break;
                            case 4:
                                labelHtml = '<span class="label label-sm label-danger status-icon">' + LANG.UI_NODE_ABNORMAL + '</span>';
                                break;
                            default:
                                labelHtml = '<span class="label label-sm label-info status-icon">' + LANG.UI_PUBLIC_UNKNOWN + '</span>';
                                break;
                        }
                        return labelHtml;
                    }
        
                },
                {
                    field:"refresh_time",
                    title:LANG.UI_CLOUD_PLATFORM_SYNC_TIME,
                    sortable:true,
                    align:"left"
                },
                // {
                //     field:"auth_status",
                //     title:LANG.UI_CLOUD_PLATFORM_AUTHORIZE_STATUS,
                //     sortable:true,
                //     align:"left",
                //     formatter:function(value){
                //         let labelHtml = "";
                //         switch(value){
                //             case 1:
                //                 labelHtml = '<span class="label label-sm label-success status-icon table-label_en width80_en">' +LANG.UI_CLOUD_PLATFORM_AUTHORIZED + '</span>';
                //                 break;
                //             case 2:
                //             case 0:
                //                 labelHtml = '<span class="label label-sm label-default status-icon table-label_en width80_en">' + LANG.UI_CLOUD_PLATFORM_UNAUTHORIZED + '</span>';
                //                 break;
                //         }
                //         return labelHtml;
                //     }
                // },
                {
                    field:"version",
                    title:LANG.UI_TASK_AWS_VERSION,
                    sortable:true,
                    align:"left"
                },
                {
					field: 'creator',
					title: LANG.UI_REPORT_BUILDER,
					sortable: false,
					align: 'center',
				},
				{
					field: 'owner',
					title: LANG.UI_CLIENT_OWNER,
					sortable: false,
					align: 'center',
				},
                {
                    title:LANG.UI_PUBLIC_OPERATION,
                    sortable:false,
                    events: operateEvents,
                    formatter: function (value, row, index, field) {
                        var button = '<div class="btn-group">';
                        if(row.op_flag){
                            button +='<div class="btn_operation_vicon">' ;
                             if ($.inArray('p_hadoopmanager_sync', CONF.PERMISSION_ARR) !== -1) {
                                button += '<a class="refreshCluster"><i class="viconfont vicon-biaogeshuaxin" title="'+ LANG.UI_CLIENT_REFRESH +'"></i></a>';
                             }
                             if ($.inArray('p_hadoopmanager_edit', CONF.PERMISSION_ARR) !== -1) {
                                button += '<a class="editCluster"><i class="viconfont vicon-a-Editbianji"  title="'+ LANG.UI_PUBLIC_EDIT +'"></i></a>';
                             }
                             if ($.inArray('p_hadoopmanager_delete', CONF.PERMISSION_ARR) !== -1) {
                                button += '<a class="deleteCluster"><i class="viconfont vicon-a-Deleteshanchu1" title="'+ LANG.UI_CLIENT_DELETE_NEW +'" ></i></a>';
                             }
                            button +='</div>';
                        }else{
                            if ($.inArray('p_hadoopmanager_edit', CONF.PERMISSION_ARR) !== -1) {
                                button +='<div class="btn_operation_vicon">' +
                                '<a class="editCluster"><i class="viconfont vicon-a-Editbianji"  title="'+ LANG.UI_PUBLIC_EDIT +'"></i></a>' +
                                '</div>';
                            }

                        }
                       
                        button += '</div>';
                        return button;
                    }
                }
            ]
        }
        $('#cluster_table').baseTableConfig().init(options);
    }
    //表格操作按钮监听事件
    var operateEvents  = {
        //修改事件
        'click .editCluster': function (e, value, row, index) {
            //获取uuid
            checkOperateAuth({
                type: 2,
                source_uuid: row.cluster_uuid,
                source_type: 60
            }, function(){
                var clusteruuid =  row.cluster_uuid;
                //跳转到集群修改页面
                LOCATION('./content/hadoop/edit_cluster.php?clusteruuid='+clusteruuid,'infrastructure');
            })
        },
        //删除事件
        'click .deleteCluster': function (e, value, row, index) {
            checkOperateAuth({
                type: 2,
                source_uuid: row.cluster_uuid,
                source_type: 60
            },function(){
                var data = {};
                var hadoop_uuid_list =  [row.cluster_uuid]
                data.hadoop_uuid_list =  JSON.stringify(hadoop_uuid_list);
                //发送给后端
                var des = LANG.UI_HADOOP_DELETE_CLUSTER_TIPS1 + '<br>'+ LANG.UI_HADOOP_CLUSTER_NAME +'：' + row.cluster_name ;
                bootbox.confirm({
                    title: LANG.UI_HADOOP_DELETE_CLUSTER_TITLE,
                    message: des,
                    callback: debounce(function (r) {
                        if (!r) {
                            return;
                        }
                        var requestDeleteCluster = function (d){
                            Metronic.unblockUI($(".hadoop-cluster-table-container"));
                            if(d.success){
                            //删除成功
                            UIToastr.showSuccess(LANG.UI_HADOOP_DELETE_CLUSTER,d.message);
                                //更新表格数据
                            $('#cluster_table').bootstrapTable('refresh'); 
                            }else{
                                UIToastr.showWarning(LANG.UI_HADOOP_DELETE_CLUSTER,d.message);
                            }
                        }
                        Metronic.blockUI({target: '.hadoop-cluster-table-container',animate: true,cenrerY: true,});
                        pAjaxRequest(data, "/api/v1/hadoop/cluster", "DELETE", requestDeleteCluster);
                    },300)
                });
            })
           
        },
        //刷新集群
        'click .refreshCluster': function (e, value, row, index) {
            checkOperateAuth({
                type: 2,
                source_uuid: row.cluster_uuid,
                source_type: 60
            },function(){
                var data = {};
                data.hadoop_cluster_uuid =  row.cluster_uuid
                var requestRefreshCluster = function (d){
                    Metronic.unblockUI($(".hadoop-cluster-table-container"));
                    if(d.success){
                        //刷新成功
                        UIToastr.showSuccess(LANG.UI_HADOOP_REFRESH_CLUSTER,d.message);
                        //更新表格数据
                        $('#cluster_table').bootstrapTable('refresh'); 
                    }else{
                        UIToastr.showWarning(LANG.UI_HADOOP_REFRESH_CLUSTER,d.message);
                    }
                }
                Metronic.blockUI({target: '.hadoop-cluster-table-container',animate: true,cenrerY: true,});
                pAjaxRequest(data, "/api/v1/hadoop/refresh/cluster", "POST", requestRefreshCluster);
            })
           
        }

    };
    //监听事件
    var initListeners =  function(){
        //搜索确认事件
        $("#searchSubmit").on('click',function (){
            advancedSearch = {
                start_time: _dateRangePicker_startTime,
                end_time: _dateRangePicker_endTime,
                status: $('#advanced_search_status').val(),
                keyword: $("#searchVal").val()
            };
            $("#cluster_table").bootstrapTable('refresh',{query:advancedSearch});
        })
        $('#searchVal').keypress(function (e) {
            if (e.which == 13) {
            	// searchVcenter();
                advancedSearch = {
                    start_time: _dateRangePicker_startTime,
                    end_time: _dateRangePicker_endTime,
                    status: $('#advanced_search_status').val(),
                    keyword: $("#searchVal").val()
                };
                $("#cluster_table").bootstrapTable('refresh',{query:advancedSearch});
            }
        });
		$('#searchVal').on('blur', function () {
			$(this).prop('placeholder',LANG.UI_HADOOP_SEARCH_BY_NAME);
		});

        $('#searchVal').on('focus', () => {
            $('#clearSearchBtn').removeClass('hide');
        })
		$('#clearSearchBtn').on('click', function () {
			$('#searchVal').val('');
            $('#clearSearchBtn').addClass('hide');
            advancedSearch = {
                start_time: _dateRangePicker_startTime,
                end_time: _dateRangePicker_endTime,
                status: $('#advanced_search_status').val(),
                keyword: $("#searchVal").val()
            };
            $("#cluster_table").bootstrapTable('refresh',{query:advancedSearch});
		});
        //聚焦输入搜索事件
        // 聚焦时添加红色背景
        $('.search_glass_input').focus(function() {
            $('.search_glass').addClass('search_glass_ing');
            $('.icon-search').addClass('icon-search_ing');
        });
        // 失焦时移除红色背景
        $('.search_glass_input').blur(function() {
            $('.search_glass').removeClass('search_glass_ing');
            $('.icon-search').removeClass('icon-search_ing');
        })
        //添加集群
        $('#addCluster').on('click',function(){
            LOCATION('./content/hadoop/add_cluster.php','infrastructure');
        })
        //授权集群
        // $('#authorizationCluster').on('click',function(){
        //     $('#hadoopAuthModal').modal('show');
        //     initHadoopAuthDes(authInfo);
        // })
        //改变表格高度
        $('#vin_hadoop_cluster_toolbar .change_height').on('click', change_height);
        //批量删除事件
        $("#deleteSelect").on('click',function(){
            deleteSelect();
        })
        //刷新事件,批量刷新
        $("#refreshSelect").on('click',function(){
            refreshSelect();
        })
        //弹出自动刷新配置模态框
		$('#refreshInterval').on('click',function(){
			$('#refreshModal').modal('show');
		})
        $('#refreshTime').on('click',refreshTime);
        
        //点击高级搜索打开弹窗
        $('#advanceSearchBtn').on('click', function () {
            $('#advanced_search_vendor').val('');
            $('#advanced_search_status').val('');
            $('#advanced_search_modal').modal('show');
        });
        
        // 高级搜索提交
        $('#advanceSearchSubmit').on('click', function () {
            advancedSearch = {
                start_time: _dateRangePicker_startTime,
                end_time: _dateRangePicker_endTime,
                status: $('#advanced_search_status').val(),
                keyword: $("#searchVal").val()
            };

            addSearchContent();  // 显示搜索项
            $("#cluster_table").bootstrapTable('refresh',{query:advancedSearch});
            $('#advanced_search_modal').modal('hide');
        });

        // 清除高级搜索内容
        $('#current_searchDiv .clearSearch').on('click', function () {
            $('#current_searchDiv .searchContent').text('');
            $('#current_searchDiv').hide();

            // 动态设置 table-container高度
            if ($('#hadoop_cluster_tip').length > 0) { // 提示存在
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.hadoop-cluster-table-container').css('height', 'calc(100% - 146px)');
            } else {
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.hadoop-cluster-table-container').css('height', 'calc(100% - 46px)');
            }

            for (const id of Object.keys(advancedSearch)) {
                clearAdvancedSearch(id);
            }
            let searchList = {};
            searchList.keyword = $("#searchVal").val();
            $("#cluster_table").bootstrapTable('refresh',{query:searchList});
        });

        $("#cancelRefreshTime").on('click',()=>{
            $('#refreshValue').val(refreshTimeVal);
        })

        //授权
        $('#authCluster').on('click', function(){;
            authCluster();
        });
        //取消授权
        $('#authClusterRemove').on('click', function(){
            cancelAuthCluster();
        });

        // hadoop_cluster_tip 关闭时动态设置表格高度
        $('#hadoop_cluster_tip_close').on('click', () => {
            $('.resource-manager-wrap .resource-manager-wrap__content').css('padding-bottom', 0);

            if ($('#searchDiv').is(':visible')) { // search-content 存在
                // 86px = table-toolbar-wrapper 高度 34px + marin-bottom 12px + search-content的高度 40px
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.hadoop-cluster-table-container').css('height', 'calc(100% - 86px)');
            } else {
                // 46px = table-toolbar-wrapper 高度 34px + marin-bottom 12px
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.hadoop-cluster-table-container').css('height', 'calc(100% - 46px)');
            }
        });
    }
    //生成高级搜索内容
    var addSearchContent = function () {
        var contents = [];
        $('#current_searchDiv .searchContent').text('');
        if (advancedSearch.start_time && advancedSearch.end_time) {  // 添加时间
            contents.push('<span id="label_time" title="' + advancedSearch.start_time + "~" + advancedSearch.end_time + '"> '
                + LANG.UI_PUBLIC_ADD_TIME + ': <i>' + advancedSearch.start_time + "~" + advancedSearch.end_time + '</i><em>X</em></span>');
        }
        if (advancedSearch.status) {  // 状态
            contents.push(`<span id="label_sattus" value="${advancedSearch.sattus}" title="${HADOOP_STATUS_DESC[advancedSearch.status]}">${LANG.UI_PUBLIC_STATUS}: `
                + `<i>${HADOOP_STATUS_DESC[advancedSearch.status]}</i><em>X</em></span>`);
        }
        $('#current_searchDiv .searchContent').append(contents.join(''));

        $('#current_searchDiv .searchContent em').on('click', function () {
            // 这里的监听只能放在这里, 因为em元素是动态生成的
            // 点击x
            $(this).parent().remove();
            let searchContent = $('#current_searchDiv .searchContent');

            if (!searchContent[0].children.length) {
                $('#current_searchDiv').hide();

                // 动态设置 table-container高度
                if ($('#hadoop_cluster_tip').length > 0) { // 提示存在
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container.hadoop-cluster-table-container').css('height', 'calc(100% - 146px)');
                } else {
                    $('.resource-manager-wrap .resource-manager-wrap__content .table-container.hadoop-cluster-table-container').css('height', 'calc(100% - 46px)');
                }
            }

            let parent = $(this).parent();
            let id = parent[0].id;
            clearAdvancedSearch(id);
            advancedSearch = {
                start_time: _dateRangePicker_startTime,
                end_time: _dateRangePicker_endTime,
                status: $('#advanced_search_status').val(),
                keyword: $("#searchVal").val()
            };
            $('#cluster_table').bootstrapTable('refresh',{query:advancedSearch});
        });
        if (contents.length) {
            $('#current_searchDiv').show();

            // 动态设置 table-container高度
            if ($('#hadoop_cluster_tip').length > 0) { // 提示存在
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.hadoop-cluster-table-container').css('height', 'calc(100% - 186px)');
            } else {
                $('.resource-manager-wrap .resource-manager-wrap__content .table-container.hadoop-cluster-table-container').css('height', 'calc(100% - 86px)');
            }
        }
    }
    // 清除高级搜索内容
    var clearAdvancedSearch = function (id) {
        switch(id) {
            case 'start_time':
            case 'end_time':
            case 'label_time':
                advancedSearch.start_time = '';
                advancedSearch.end_time = '';
                $('#advanced_search_daterangepicker').val('');
                _dateRangePicker_startTime = '';
                _dateRangePicker_endTime = '';
                break;
            case 'label_sattus':
                $('#advanced_search_status').val('');
                break;
            default:
                break;
        }
    }

    //初始化日期选择器组件
    var initDatatimePicker = function () {
       //初始化日期时间选择控件
       let dataRangePicker = $('#advanced_search_daterangepicker');
       dataRangePicker.daterangepicker({
           "autoUpdateInput": false,											//是否自动填充input
           "startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
           "endDate": moment({hour: 23, minute: 59}),												//默认结束时间
           "maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
           "timePicker": true,													//是否显示时间,时分
           "timePicker24Hour": true,											//是否是24小时制
           "alwaysShowCalendars": true,										//是否总是显示日期选择
           "ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
           "locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
       }, function (start, end, label) {
//			console.log('New date range selected: ' + start.format('YYYY-MM-DD HH:mm:ss') + ' to ' + end.format('YYYY-MM-DD HH:mm:ss') + ' predefined range: ' + label);
       });

       //如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
       dataRangePicker.on('apply.daterangepicker', function (ev, picker) {
           //给全局变量赋值,然后设置input
           _dateRangePicker_startTime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
           _dateRangePicker_endTime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
           _dateRangePicker_range = picker.chosenLabel;
           $(this).val(picker.startDate.format('YYYY-MM-DD HH:mm') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm'));
       });

       dataRangePicker.on('cancel.daterangepicker', function (ev, picker) {
           //清除全局变量,然后设置input
           _dateRangePicker_startTime = "";
           _dateRangePicker_endTime = "";
           _dateRangePicker_range = "";
           $(this).val('');
       });

       //input右侧的图标事件
       $('.daterangepickerdiv i').click(function () {
           $(this).parent().find('input').click();
       });
        
    }
    //刷新时间
    var refreshTime = function(){
    	var refresh = parseInt($('#refreshValue').val());
    	if(!refresh || refresh < 5){
    		UIToastr.showWarning(LANG.UI_HADOOP_RFRESH_TIME_FAILD,LANG.UI_HADOOP_RFRESH_TIME_FAILD_TIPS);
    		initrefreshTime();
    		return;
    	}
    	bootbox.confirm({
            title: LANG.UI_HADOOP_RFRESH_TIME_EDIT,
            message: LANG.UI_HADOOP_RFRESH_TIME_EDIT_TIPS,
            callback: debounce(function(r) {
                if(!r) return;
    	        updateRefreshtime();
            },300)
        });
    } 
    //读取hadoop集群自动刷新时间
    var initrefreshTime = function(){
        var requestGetTime = function(d){
            if(d.data){
                var refreshTime = parseInt(d.data) / 60;
                refreshTimeVal = refreshTime;
                $('#refreshValue').val(refreshTime);
                //初始化加减控件
                $('.input-group').spinner({value: refreshTime, step: 1, min: 1,max: 9999});
            }

        }
        //发送后端接口
        pAjaxRequest({}, "/api/v1/hadoop/get/time", "get", requestGetTime);
        
    }
    //修改hadoop集群自动刷新时间间隔
    var updateRefreshtime = function(){
        var refresh = parseInt($('#refreshValue').val());
    	var data = {
    			'refresh' : refresh
    	};
        var requestUpdateTime = function(d){
            // console.log("d-----",d);
            if (d.data) {
                refreshTimeVal = refresh;
                $('#refreshModal').modal('hide');
                UIToastr.showSuccess(LANG.UI_HADOOP_RFRESH_TIME_EDIT,LANG.UI_HADOOP_RFRESH_TIME_SUCCESS_TIPS);
            }
            Metronic.unblockUI('#refreshModal');
        }
        Metronic.blockUI({target: '#refreshModal',animate: true});
        //发送后端接口
        pAjaxRequest(data, "/api/v1/hadoop/update/time", "PUT", requestUpdateTime);
    }
    //集群展开详情
    var cluster_detail =  function(index, row, element){
        if (index != lastIndex[1]) {
            lastIndex.push(index);
            $('#cluster_table').bootstrapTable('collapseRow', lastIndex[lastIndex.length - 2]);
            lastIndex.splice(0, 1);
        }
        let appliancestr = '';
        //如有集群有传输代理，则显示传输代理
        if(row.appliance_uuid != null && row.appliance_uuid != ""){
            appliancestr = `<div style="margin-top:15px;margin-bottom:-5px;font-size:13px"><b>`+ LANG.UI_VISUAL_APPLIANCE+`</b>：` +  row.appliance_des + `</div>`
        }
        let thead = `<table><thead><tr><th style="font-weight:400;font-size:13px">` + LANG.UI_HADOOP_HOST_IP + `</th>
                        <th style="font-weight:400;font-size:13px">` + LANG.UI_HADOOP_REST_API + `</th>
                        <th style="font-weight:400;font-size:13px">` + LANG.UI_HADOOP_SSL + `</th>
                        <th style="font-weight:400;font-size:13px">` + LANG.UI_MICROSOFT365_USER + `</th>
                        <th style="font-weight:400;font-size:13px">` + LANG.UI_MICROSOFT365_VERIFY_WAY + `</th>
                        <th style="font-weight:400;font-size:13px">` + LANG.UI_TASK_AWS_VERSION + `</th>
                        <th style="font-weight:400;font-size:13px">` + LANG.UI_BLACK_WHITE_STATUS + `</th>
                        </tr></thead>`
        let tbody = `<tbody>`;
        row.node_info.map((node_item)=>{
            let verifystr =  node_item.verify_type == 1?"Simple":"kerberos";
            let statusstr = getNodeStatus(node_item.online_status);
            let ssl_verify_flag =  node_item.ssl_verify_flag == 1?LANG.UI_PUBLIC_YES:LANG.UI_PUBLIC_NO;
            tbody += `<tr>
            <td class="pd0">`+ node_item.namenode_ip + `</td>
            <td class="pd0">` + node_item.rest_api + `</td>
            <td class="pd0">` + ssl_verify_flag + `</td>
            <td class="pd0">` + node_item.username + `</td>
            <td class="pd0">` + verifystr + `</td>
            <td class="pd0">` + node_item.version + `</td>
            <td class="pd0">` + statusstr + `</td>
            </tr>
            `
        })
        tbody += '</tbody></table>';
        return appliancestr+thead+tbody;
        
    }
    //获取节点状态描述
    var getNodeStatus = function(status){
        var des = "";
        switch(status){
            case 0:
                des = LANG.UI_PUBLIC_UNKNOWN;
                break;
            case 1:
                des = LANG.UI_CLOUD_PLATFORM_ONLINE + '<span class="hadoopnode">(active)';
                break;
            case 2:
                des = LANG.UI_CLOUD_PLATFORM_ONLINE + '<span class="hadoopnode">(standby)';
                break;
            case 3:
                des = LANG.UI_CLOUD_PLATFORM_OFFLINE;
                break;
            case 4:
                des = LANG.UI_NODE_ABNORMAL;
                break;
            default:
                des = LANG.UI_PUBLIC_UNKNOWN;
                break;
        }
        return des;
    }

    //根据是否有勾选添加样式
    var btnDisplayClass = function(){
        $("input[name='btSelectItem'], input[name='btSelectAll']").on('change',function (){
            var selectedRow = $('#cluster_table').bootstrapTable("getSelections");
            if(selectedRow.length != 0){
                //如果有勾选 则改变图标颜色
                $(".grey_box_btn").addClass('grey_box_btn_hover');
            }else{
                $(".grey_box_btn").removeClass('grey_box_btn_hover');
            }
        })
    }
    //改变表格高度
    var change_height =  function(){
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#cluster_table>tbody>tr>td').css({
                'padding-top': '10.25px',
                'padding-bottom': '10.25px'
            })
            $('#vin_hadoop_cluster_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#cluster_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('#vin_hadoop_cluster_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }  
    
    //批量删除勾选的
    var deleteSelect = function (){
        //获取勾选
        var selectedRow = $('#cluster_table').bootstrapTable("getSelections");
        if (selectedRow.length == 0) {
            UIToastr.showWarning(LANG.UI_HADOOP_DELETE_CLUSTER_TITLE, LANG.UI_HADOOP_DELETEC_LUSTER_TIPS);
            return;
        }
        let data = {};
        var hadoop_uuid_list = [];
        var des = LANG.UI_HADOOP_DELETE_CLUSTER_TIPS1 + '<br>'+ LANG.UI_HADOOP_CLUSTER_NAME +'：'
        selectedRow.forEach(item => {
            des += item.cluster_name + ';&nbsp;';
            hadoop_uuid_list.push(item.cluster_uuid);
        });
        data.hadoop_uuid_list =  JSON.stringify(hadoop_uuid_list);
        bootbox.confirm({
            title: LANG.UI_HADOOP_DELETE_CLUSTER_TITLE,
            message: des,
            callback: debounce(function (r) {
                if (!r) {
                    return;
                }
                var requestDeleteCluster = function (d){
                    Metronic.unblockUI($(".hadoop-cluster-table-container"));
                    if(d.success){
                       //删除成功
                       UIToastr.showSuccess(LANG.UI_HADOOP_DELETE_CLUSTER,d.message);
                         //更新表格数据
                       $('#cluster_table').bootstrapTable('refresh'); 
                    }else{
                        UIToastr.showWarning(LANG.UI_HADOOP_DELETE_CLUSTER,d.message);
                    }
                }
                Metronic.blockUI({target: '.hadoop-cluster-table-container',animate: true,cenrerY: true,});
                pAjaxRequest(data, "/api/v1/hadoop/cluster", "DELETE", requestDeleteCluster);
            }, 300)
        });
    }
    //批量刷新
    var refreshSelect = function (){
        advancedSearch = {
            start_time: _dateRangePicker_startTime,
            end_time: _dateRangePicker_endTime,
            status: $('#advanced_search_status').val(),
            keyword: $("#searchVal").val()
        };
        $("#cluster_table").bootstrapTable('refresh',{query:advancedSearch});
    }
    //集群授权
    var authCluster = function(){
        //获取勾选
        var selectedRow = $('#auth_table').bootstrapTable("getSelections");
        if (selectedRow.length == 0) {
            UIToastr.showInfo(LANG.UI_HADOOP_AUTH_HADOOP_CLUSTER, LANG.UI_HADOOP_ADD_AUTH_HADOOP_CLUSTER_TIPS);
            return;
        }
        let data = {};
        var hadoop_uuid_list = [];
        selectedRow.forEach(item => {
            hadoop_uuid_list.push(item.cluster_uuid);
        });
        data.hadoop_uuid_list =  hadoop_uuid_list;
        data.authflag =  "1";
        var requestAuthCluster = function (d){
            Metronic.unblockUI($("#auth_table"));
            if(d.success){
                 //授权成功
                 UIToastr.showSuccess(LANG.UI_SETTING_ADD_AUTH,d.message);
                 //更新表格数据
                 $('#hadoopAuthModal').modal('hide');
                 $('#cluster_table').bootstrapTable('refresh'); 
                 $('#auth_table').bootstrapTable('refresh');
                 initAuthInfo();
            }else{
                 UIToastr.showWarning(LANG.UI_SETTING_ADD_AUTH,d.message);
            }
        }
        Metronic.blockUI({target: '#auth_table',animate: true,cenrerY: true,});
        pAjaxRequest(data, "/api/v1/hadoop/cluster/auth", "POST", requestAuthCluster);
    }
    //取消授权
    var  cancelAuthCluster  = function(){
          //获取勾选
          var selectedRow = $('#auth_table').bootstrapTable("getSelections");
          if (selectedRow.length == 0) {
              UIToastr.showInfo(LANG.UI_HADOOP_AUTH_HADOOP_CLUSTER, LANG.UI_HADOOP_CANCEL_AUTH_HADOOP_CLUSTER_TIPS);
              return;
          }
          let data = {};
          var hadoop_uuid_list = [];
          selectedRow.forEach(item => {
              hadoop_uuid_list.push(item.cluster_uuid);
          });
          data.hadoop_uuid_list =  hadoop_uuid_list;
          data.authflag =  "2";
          var requestAuthCluster = function (d){
            Metronic.unblockUI($("#auth_table"));
            if(d.success){
                //授权成功
                UIToastr.showSuccess(LANG.UI_SETTING_DELETE_AUTH,d.message);
                //更新表格数据
                $('#hadoopAuthModal').modal('hide');
                $('#cluster_table').bootstrapTable('refresh'); 
                $('#auth_table').bootstrapTable('refresh');
                initAuthInfo();
            }else{
                 UIToastr.showWarning(LANG.UI_SETTING_DELETE_AUTH,d.message);
            }
        }
        Metronic.blockUI({target: '#auth_table',animate: true,cenrerY: true,});
        pAjaxRequest(data, "/api/v1/hadoop/cluster/auth", "POST", requestAuthCluster);
    }

    //初始化集群授权表格
    var initAuthTable =  function(){
        var options  = {
            vin_url:"/api/v1/hadoop/cluster",
            vin_method:"GET",
            searchInput:false,
            search:false,
            pagination:true,
            pageList:[10,20,50,100,150,200],
            showJumpTo:true,
            fullPage:true, //全屏表格高度适配
            onPostBody:function (){
                btnDisplayClass();
                $('[data-toggle="tooltip"]').tooltip();
            },
            onCheckAll: function (res) {
				modifyDelStyle('cluster_table', 'deleteSelect');
			},
            onCheck: function (res) {
				modifyDelStyle('cluster_table', 'deleteSelect');
			},
			onUncheck: function () {
				modifyDelStyle('cluster_table', 'deleteSelect');
			},
            onUncheckAll: function () {
				modifyDelStyle('cluster_table', 'deleteSelect');
			},

            columns:[
                {
                    checkbox:true,
                    sortable:false,
                    formatter: function (value, row, index) {
                        if (row.op_flag === false) { // 创建者等不是当前用户，不能操作
                            return {
                                disabled: true
                            };
                        }
                    }
                },
                {
                    field:"cluster_name",
                    title:LANG.UI_HADOOP_CLUSTER_NAME,
                    sortable:true,
                    align:"left"
                },
                {
                    field:"auth_status",
                    title:LANG.UI_CLOUD_PLATFORM_AUTHORIZE_STATUS,
                    sortable:true,
                    align:"left",
                    formatter:function(value){
                        let labelHtml = "";
                        switch(value){
                            case 1:
                                labelHtml = '<span class="label label-sm label-success status-icon table-label_en width80_en">' +LANG.UI_CLOUD_PLATFORM_AUTHORIZED + '</span>';
                                break;
                            case 2:
                            case 0:
                                labelHtml = '<span class="label label-sm label-default status-icon table-label_en width80_en">' + LANG.UI_CLOUD_PLATFORM_UNAUTHORIZED + '</span>';
                                break;
                        }
                        return labelHtml;
                    }
                },
            ]
        }
        $('#auth_table').baseTableConfig().init(options);
    }
    //初始化hadoop授权信息
    // var initHadoopAuthDes = function(data){
    //     //如果是容量授权显示无限制
	// 	if(data.licensetype == 3){
	// 		var hadoopDes = LANG.UI_NAS_MANAGE_AUTH_DES + ": " + LANG.UI_SETTING_AUTH_UNLIMITED;
	// 	}else{
	// 		var hadoopDes = LANG.UI_CLIENT_AUTH_USED_TOTAL + ": " + (data.hadoop.valid < 0 ? '--' : data.hadoop.valid) + "/" + data.hadoop.used + "/" + data.hadoop.total;
	// 	}
	// 	$('#hadoopDes').html(hadoopDes);
    // }
    //获取hadoop授权信息
    // var initAuthInfo = function(){
    //     var requestAuthInfo = function(d){
    //         if(d.success){
    //             authInfo = d.data.authinfo;
    //             //如果不是按照容量进行授权，需要显示授权按钮
    //             if(d.data.authinfo.licensetype != 3){
    //                 $("#authorizationCluster").show();
    //             }
    //         }
    //     }
    //     pAjaxRequest({}, "/api/v1/hadoop/cluster", "GET", requestAuthInfo);
    // }


    return{
        init:function(){
            // initAuthInfo();
            initDataTable();
            initAuthTable();
            initListeners();
            initrefreshTime();
            initDatatimePicker();
        }
    }

}();
jQuery(document).ready(function(){
    hadoop_cluster.init();
})