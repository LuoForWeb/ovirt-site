var kubernetes_cluster = function () {
    var changeHeightFlag = false;
    // 保存计时器 ID
    var hostChecked = [];
    var initFlag = false;
    var edit_cluster_uuid = "";

    //接收表格数据生成二级表格样式
    var initSecondTable = function(datas){
        if(datas['data'] == "" || datas['data'] == undefined || datas['data'] == null){
            return LANG.UI_KUBE_NO_DATA;
        }
        //获取表格数据
        var tableList = datas['data']['rows'];
        if(tableList == "" || tableList == undefined || tableList == null){
            return LANG.UI_KUBE_NO_DATA;
        }

        var tableContent = "";
        $.each(tableList, function (index, value) {
            let hostname = value.hostname;
            //判断节点是否在master节点上
            if(value.in_master == 1){
                hostname += "("+LANG.UI_KUBE_MASTER_NODE+")";
            }
            let status = "";
            if(value.online_flag == 1){
                status = '<span class="label label-sm label-success status-icon">' + LANG.UI_KUBE_ONLINE + '</span>';
            }else{
                status = '<span class="label label-sm label-default status-icon">' + LANG.UI_KUBE_OFFLINE + '</span>';
            }
            tableContent += "<tr>" +
            "<td>"+hostname+"</td>" +
            "<td>"+value.ip+"</td>" +
            "<td>"+value.cpu+"</td>" +
            "<td>"+value.memory+"</td>" +
            "<td>"+status+"</td>" +
            "</tr>";
        });
        //开始封装表格
        var des = "<table>" +
            "<tr>" +
            "<th>"+LANG.UI_KUBE_NODE_HOSTNAME+"</th>" +
            "<th>"+LANG.UI_KUBE_NODE_IP+"</th>" +
            "<th>"+LANG.UI_KUBE_CPU_CORES+"</th>" +
            "<th>"+LANG.UI_KUBE_MEMORY+"</th>" +
            "<th>"+LANG.UI_KUBE_STATUS+"</th>" +
            "</tr>" +tableContent+
            "</table>";
        return des;
    }


    var checkEvent = function (tableId, btnId) {
        let select = $('' + tableId + '').bootstrapTable('getSelections');
        if (select.length == 0) {
            modifyDelStyle('cluster_table', 'deleteSelect');
            $('' + btnId + ' i').addClass('icon-gray-delete');
            $('' + btnId + ' i').removeClass('icon-white-delete');
            $('' + btnId + '').removeClass('select-delete-btn');
			$('' + btnId + '').addClass('cancel-delete-btn');
        } else {
            modifyDelStyle('cluster_table', 'deleteSelect');
            $('' + btnId + ' i').removeClass('icon-gray-delete');
            $('' + btnId + ' i').addClass('icon-white-delete');
            $('' + btnId + '').removeClass('cancel-delete-btn');
			$('' + btnId + '').addClass('select-delete-btn');
        }
    }
    //初始化数据
    var initDataTable = function () {
        var beforeInput = '';
        if ($.inArray('p_k8s_cluster_delete', CONF.PERMISSION_ARR) !== -1) {
            // 删除
            beforeInput = `<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="deleteSelect"></button></div>`
        }
        var afterInput = '';
        if ($.inArray('p_k8s_cluster_add', CONF.PERMISSION_ARR) !== -1) {
            // 新建
            afterInput += `<div><button class="btn table-toolbar-btn" id="addCluster" data-toggle="drawer" data-target="#" aria-haspopup="true" aria-expanded="false" >
                                <i class="viconfont vicon-biaogetianjia mr4"></i>
                                <span>` + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD + `</span>
                            </button>`;
        }

        
    var operationFormatter = function(value, row, index, field){
        let button = '<div class="btn-group dropdown-wrapper">';
        button += `<button type="button" class="btn btn-dropdown-operate dropdown-toggle" data-toggle="dropdown"
						data-hover="dropdown" aria-haspopup="true" data-delay="1000" data-close-others="true" aria-expanded="false">
						${LANG.UI_PUBLIC_OPERATION}
						<i class="fa fa-angle-down"></i>
					</button>
					<ul class="dropdown-menu">`;
        //修改
        if($.inArray('p_k8s_cluster_modify', CONF.PERMISSION_ARR) !== -1){
            button += '<li class="editThis"><button class="btn dropdown-menu__item me-0" type="button"" ><i class="viconfont vicon-a-Editbianji me-4"></i> ' + LANG.UI_PUBLIC_EDIT + '</a></li>';
        }
        //删除
        if($.inArray('p_k8s_cluster_delete', CONF.PERMISSION_ARR) !== -1){
            button += '<li class="deleteThis"><button class="btn dropdown-menu__item me-0" type="button"" ><i class="viconfont vicon-a-Deleteshanchu1 me-4"></i> ' + LANG.UI_PUBLIC_DELETE + '</a></li>';
        }
        
        
        

       
        button += '</ul></div>';
        // 开启以下会导致页面卡顿
        return button;

    }




        var options = {
            vin_url:"/api/v1/kubernetes/cluster",
            vin_method:"GET",
            // searchInput: false,
            // search:false,
            pagination: true,
            pageList:[5,10,25,50],
            vin_params: function () {
                let params = {};
                params.search_val = $('#kubernetes_table_toolbar .searchVal').val();
                return params;
            },
            placeholder: LANG.UI_KUBE_SEARCH_BY_CLUSTER_NAME_OR_IP, //搜索框的placeholder
            searchInput: true, //搜索框
            searchClass: 'searchVal', //自定义的搜索框类名
            searchSelector: '.searchVal', //选择使用自定义搜索框
            customTool: {
                beforeInput: beforeInput,
                afterInput: afterInput,
            },
            uniqueId: 'id',
            fullPage: true,
            clickToSelect: false,
            changeHeightBtn: true,
            detailView:true,
            showJumpTo: true,
            onRefresh: function (params) {
                $('#cluster_table').bootstrapTable('hideLoading');
            },
            onCheck: function(){
                btnDisplayClass();
                hostChecked = $("#cluster_table").bootstrapTable('getSelections');
            },
            onUncheck: function(){
                btnDisplayClass();
                hostChecked = $("#cluster_table").bootstrapTable('getSelections');
            },
            onUncheckAll: function(){
                btnDisplayClass();
                hostChecked = $("#cluster_table").bootstrapTable('getSelections');
            },
            onCheckAll: function(){
                btnDisplayClass();
                hostChecked = $("#cluster_table").bootstrapTable('getSelections');
            },
            PostBody:function (){
                // debugger;
                // $('[data-toggle="tooltip"]').tooltip();
                // $('#kubernetes_table_toolbar .search-btn').off().on('click', function () {
                //     $('#cluster_table').bootstrapTable('refresh');
                // })
            },
            onPostBody: (data) => {
                 // 保持表格高度逻辑
                 if (changeHeightFlag == false) {
                    $('#cluster_table>tbody>tr>td').css({
                        'padding-top': '10.25px',
                        'padding-bottom': '10.25px'
                    })
                    $('#kubernetes_table_toolbar .change_height i').addClass('icon-auto-height2');
                } else if (changeHeightFlag == true) {
                    $('#cluster_table>tbody>tr>td').css({
                        'padding-top': '4.25px',
                        'padding-bottom': '4.25px'
                    })
                    $('#kubernetes_table_toolbar .change_height i').removeClass('icon-auto-height2');
                }
                checkRecord();
            },
            detailFormatter:function (row,data,div) {
                var secondTable;
                var initSecondTableInside = function(d){
                    secondTable =  initSecondTable(d);
                    return secondTable;
                }
                var dataList = {};
                dataList.cluster_uuid = data.cluster_uuid;
                pAjaxRequest(dataList, "/api/v1/kubernetes/cluster", "GET", initSecondTableInside,false);
                return secondTable;

            },
            columns:[
                {
                    checkbox:true,
                    sortable: false,
                },
                {
                    field: 'cluster_name',
                    title: LANG.UI_KUBE_CLUSTER_NAME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'host',
                    title: LANG.UI_KUBE_IP_DOMAIN,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'hostname',
                    title: LANG.UI_KUBE_MASTER_HOSTNAME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'nodes',
                    title: LANG.UI_KUBE_NODE_COUNT,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'cpu',
                    title: LANG.UI_KUBE_CPU_CORES_COUNT,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'memory',
                    title: LANG.UI_KUBE_MEMORY_SIZE,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'create_time',
                    title: LANG.UI_KUBE_ADD_TIME,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'online_flag',
                    title: LANG.UI_KUBE_DEPLOYMENT_STATUS,
                    sortable: true,
                    align: 'center',
                    formatter: function (value, row, index, field) {
                        let labelHtml = "";
                        // 'UNKNOWN' => 0,
                        // 'ONLINE' => 1,// 集群在线
                        // 'OFFLINE' => 2,// 集群离线
                        // 'DEPLOYING' => 3,// 远程部署开始部署，设置值为部署中
                        // 'DEPLOYED' => 4,// 远程部署完成，设置值为已部署完成
                        // 'DEPLOY_FAILED' => 5,// 远程部署失败，设置值为部署失败
                        switch (value.online_flag){
                            case 1:
                                labelHtml = '<span class="label label-sm label-success status-icon">' + LANG.UI_TAPE_STATUS_ONLINE + '</span>';
                                break;
                            case 2:
                                labelHtml = '<span class="label label-sm label-default status-icon">' + LANG.UI_TAPE_STATUS_OFFLINE + '</span>';
                                break;
                            case 3:
                                labelHtml = '<button class="label label-sm label-info status-icon custom-tooltip" data-toggle="tooltip" data-placement="bottom" title="'+LANG.UI_KUBE_DEPLOYMENT_WAITING+'">' + LANG.UI_KUBE_DEPLOYING + '</button>'
                                break;
                            case 4:
                                labelHtml = '<span class="label label-sm label-info status-icon">' + LANG.UI_KUBE_DEPLOYMENT_COMPLETED + '</span>';
                                break;
                            case 5:
                                labelHtml = '<button class="label label-sm label-danger status-icon custom-tooltip" data-toggle="tooltip" data-placement="bottom" title="#'+value.error_code+': '+value.error_message+'">' + LANG.UI_VISUAL_FAIL + '</button>';
                                break;
                            default:
                                labelHtml = '<button class="label label-sm label-danger status-icon custom-tooltip" data-toggle="tooltip" data-placement="bottom"' + LANG.UI_PUBLIC_UNKNOWN + '</button>';
                                break;
                        }

                        return labelHtml;

                    }
                },
                {
                    field: 'owner_name',
                    title: LANG.UI_CLIENT_OWNER,
                    sortable: true,
                    align: 'center',
                },

                {
                    title: LANG.UI_VM_SOURCE_OPERATION_NAME,
                    sortable: false,
                    align: 'center',
                    visible: $.inArray('p_k8s_cluster_modify', CONF.PERMISSION_ARR) !== -1 || $.inArray('p_k8s_cluster_delete', CONF.PERMISSION_ARR) !== -1,
                    opButton: true,
                    events: operateEvents,
                    clickToSelect: false,
                    opButton: true,
                    formatter: operationFormatter
                },
            ],
        }
        $('#cluster_table').baseTableConfig().init(options);

        // 每5秒刷新一次数据
        timerTask.cluster_k8s = setInterval(function () {
            if(0 == $("#ClusterManagerDiv").size()){
                clearInterval(timerTask.cluster_k8s);
            }
            $('#cluster_table').bootstrapTable('refresh');
        }, 5000);

       






    }
     //记录勾选
     var checkRecord = function () {
        var checkArr = [];

        $.each( hostChecked, function (index) {
            checkArr.push(hostChecked[index].cluster_uuid);
        });
        $('#cluster_table').bootstrapTable('checkBy', {
            field: 'cluster_uuid',
            values: checkArr
        })
    }

    
    // 在页面卸载时清除计时器
    $(window).on('beforeunload', function() {
        clearInterval(timerTask.cluster_k8s);
    });

    //根据是否有勾选添加样式
    var btnDisplayClass = function(){
        $("input[name='btSelectItem'], input[name='btSelectAll']").on('change',function (){
            checkEvent('#cluster_table', '#deleteSelect');
        })
       
    }

    //改变表格高度
    var change_height = function () {
        if (changeHeightFlag == false) {
            changeHeightFlag = true;
            $('#cluster_table>tbody>tr>td').css({
                'padding-top': '10.25px',
                'padding-bottom': '10.25px'
            })
            $('#kubernetes_table_toolbar .change_height i').addClass('icon-auto-height2');
        } else if (changeHeightFlag == true) {
            changeHeightFlag = false
            $('#cluster_table>tbody>tr>td').css({
                'padding-top': '4.25px',
                'padding-bottom': '4.25px'
            })
            $('#kubernetes_table_toolbar .change_height i').removeClass('icon-auto-height2');
        }
    }
    //监听事件
    var initListeners = function(){
         // 改变表格高度
         $('#kubernetes_table_toolbar .change_height').on('click', change_height);


        //添加集群
        $('#addCluster').on('click',function (){
            //获取主节点所有地址
            getNodeport();
            $('#addClustermodal').modal({'width':'693px'});
        })
        //添加授权
        $('#authorizationCluster').on('click',function (){

        })



        //添加方式改变
        $('#add_style').on('change',function (){
            addStyle();

        })
        $("#getFile").on('change', function () {
            if (this.files.length === 0) {
                console.error('No file selected');
                return;
            }
        
            const reader = new FileReader();
            reader.readAsText(this.files[0]);
            reader.onload = function () {
                $('#config_content').val(reader.result);
            };
            reader.onerror = function (error) {
                console.error('Error reading file:', error);
            };
        });
        
        $("#getFileBut").on('click', function () {
             // 重置文件输入元素的值, 不然会第二次上传失败
            $("#getFile").val('');
            $("#getFile").trigger('click');
        });
        //确认添加集群按钮
        // $("#current_cluster_submit").on('click',function (){
        //     submitAddCluster();
        // })

        //高级配置ssh改变事件
        $('#ssh_high').on('switchChange.bootstrapSwitch', function(){
            if(this.checked){
                $(".resourcessh_div").show();
            }else{
                $(".resourcessh_div").hide();
            }
        });
        //高级配置config改变事件
        $('#config_high').on('switchChange.bootstrapSwitch', function(){
            if(this.checked){
                $(".resourceConfig_div").show();
            }else{
                $(".resourceConfig_div").hide();
            }
        });
        //网络模式ssh改变事件
        $("#ssh_network").on('change',function (){
            var hh = $("#ssh_network").val();
            console.log(hh);
            if( $("#ssh_network").val() == 1){
                //如果是服务端到客户端
                $(".ssh_address_div").hide();
            }else{
                //如果是客户端到服务端
                $(".ssh_address_div").show();
            }
        })
        //网络模式config改变事件
        $("#config_network").on('change',function (){
            if($("#config_network").val() == 1){
                //如果是服务端到客户端
                $(".config_address_div").hide();
            }else{
                //如果是客户端到服务端
                $(".config_address_div").show();
            }
        })
        //批量删除事件
        $("#deleteSelect").on('click',function(){
             //获取勾选
            var selectedRow = $('#cluster_table').bootstrapTable("getSelections");
            checkOperateAuth({type: 2,
                source_uuid: selectedRow.map(row=>row.cluster_uuid).join(","),
                source_type: 62,
            },function(){
                bootbox.confirm({
                    title: LANG.UI_KUBE_DELETE_KUBERNETES_CLUSTER,
                    message: LANG.UI_KUBE_CONFIRM_DELETE_KUBERNETES_CLUSTER,
                    buttons: {
                        confirm: {
                            label: LANG.UI_PUBLIC_CONFIRM,
                            className: 'btn-primary'
                        },
                        cancel: {
                            label: LANG.UI_PUBLIC_CANCEL,
                            className: 'btn-default'
                        }
                    },
                    callback: function (result) {
                        if (result) {
                            deleteSelect();
                        } else {
                            // 用户点击了“取消”按钮
                            // 这里可以添加取消后的处理逻辑，如果不需要处理，可以留空
                        }
                    }
                });
            })
           
        })

        //刷新事件,批量刷新
        $("#refreshSelect").on('click',function(){
            refreshSelect();
        })
        //搜索确认事件
        $("#searchSubmit").on('click',function (){
            let searchList = {};
            searchList.search_val = $("#searchVal").val();
            $("#cluster_table").bootstrapTable('refresh',{query:searchList});
        })
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
        });

        $("#edit_cluster_submit").on('click',function(){
            var dataList = {};
            dataList.cluster_uuid = edit_cluster_uuid;
            dataList.cluster_name = $("#editClusterName").val();
            var requestRefreshCluster = function (d){
                if(d.success){
                    UIToastr.showSuccess(LANG.UI_KUBE_EDIT_CLUSTER, d.message);
                    $("#editClustermodal").modal("hide");
                }else{
                    UIToastr.showWarning(LANG.UI_KUBE_EDIT_CLUSTER, d.message);
                }
            }
            //发送后端接口
            pAjaxRequest(dataList, "/api/v1/kubernetes/cluster", "patch", requestRefreshCluster,true);

        })

        // kubernetes_cluster_tip 关闭时动态设置表格高度
        $('#kubernetes_cluster_tip_close').on('click', () => {
            $('.resource-manager-wrap .resource-manager-wrap__content').css('padding-bottom', 0);
            $('.resource-manager-wrap .resource-manager-wrap__content .table-container.kubernetes-table-container').css('height', 'calc(100% - 46px)');
        });

        //初始化验证
      
        handleValidation("BY_MANUAL");
        

    }

    //批量刷新
    var refreshSelect = function (){
        Metronic.blockUI({target: $("#ClusterManagerDiv"),animate: true});
        var requestRefreshCluster = function (d){
            Metronic.unblockUI($("#ClusterManagerDiv"));
            if(d.success){
                UIToastr.showSuccess(LANG.UI_KUBE_REFRESH_KUBERNETES_CLUSTER, d.message);
            }else{
                UIToastr.showWarning(LANG.UI_KUBE_REFRESH_KUBERNETES_CLUSTER, d.message);
            }
        }
        //发送后端接口
        pAjaxRequest({}, "/api/v1/kubernetes/cluster/refresh", "GET", requestRefreshCluster,true);



    }



    //批量删除勾选的
    var deleteSelect = function (){
        Metronic.blockUI({target: $("#ClusterManagerDiv"),animate: true});
        //获取勾选
        var selectedRow = $('#cluster_table').bootstrapTable("getSelections");
        let dataList = {};
        dataList.cluster_uuid_list = [];
        for(let i=0;i<selectedRow.length;i++){
            dataList.cluster_uuid_list.push(selectedRow[i].cluster_uuid);
        }
        var requestDeleteCluster = function (d){
            Metronic.unblockUI($("#ClusterManagerDiv"));
            if(d.success){
                 checkEvent('#cluster_table', '#deleteSelect');
                UIToastr.showSuccess(LANG.UI_KUBE_DELETE_KUBERNETES_CLUSTER, d.message);
            }else{
                UIToastr.showWarning(LANG.UI_KUBE_DELETE_KUBERNETES_CLUSTER, d.message);
            }
        }
        //发送后端接口
        pAjaxRequest(dataList, "/api/v1/kubernetes/cluster", "DELETE", requestDeleteCluster,true);



    }


    //得到主节点所有端口地址
    var getNodeport = function (){
        var requestAddCluster = function (d){
            var data = d.data;
            $("#ssh_address").empty();
            $("#config_address").empty();
            for(let i=0; i<data.length;i++){
                let stringOption = '<option value="'+data[i].ip_port+'">'+data[i].ip_port+' </option>';
                $("#ssh_address").append(stringOption);
                $("#config_address").append(stringOption);
            }
        }
        //发送后端接口
        pAjaxRequest({}, "/api/v1/kubernetes/cluster/nodeport", "GET", requestAddCluster,true);
    }



    //初始化数字加减插件
    var initSpinner = function(){
        $('#initSpinnersshcpu').spinner({value:4, step: 1, min: 2, max: 8});
        $('#initSpinnerconfigcpu').spinner({value:4, step: 1, min: 2, max: 8});

        $('#initSpinnersshram').spinner({value:16, step: 1, min: 4, max: 32});
        $('#initSpinnerconfigram').spinner({value:16, step: 1, min: 4, max: 32});

       $("#ssh_cpu").on('change',function (){
			if(this.value > 8 || this.value == "" || this.value == 0 || this.value < 2){
				$('#initSpinnersshcpu').spinner('value',4);
				$('#ssh_cpu').val(4);
			}
		})

        $("#config_cpu").on('change',function (){
			if(this.value > 8 || this.value == "" || this.value == 0 || this.value < 2){
				$('#initSpinnerconfigcpu').spinner('value',4);
				$('#config_cpu').val(4);
			}
		})

        $("#ssh_ram").on('change',function (){
			if(this.value > 32 || this.value == "" || this.value == 0 || this.value < 4){
				$('#initSpinnersshram').spinner('value',16);
				$('#ssh_ram').val(16);
			}
		})

        $("#config_ram").on('change',function (){
			if(this.value > 32 || this.value == "" || this.value == 0 || this.value < 4){
				$('#initSpinnerconfigram').spinner('value',16);
				$('#config_ram').val(16);
			}
		})

        




    }


    //添加集群
    var submitAddCluster = function(){
        
        Metronic.blockUI({
            target: $("#addClustermodal"),
            animate: true,
            // message: '加载时间可能长,请耐心等待'
        });
        //根据不同方式获取数据
        //得到选择的方式
        var add_style = $('#add_style').val();
        var dataList = {};
        switch (add_style){
            case "BY_MANUAL":
                dataList.type = "BY_MANUAL";
                dataList.host = $("#manual_ip").val();
                dataList.port = $("#manual_port").val();
                dataList.name = $("#manual_nickname").val();
                break;
            case "BY_SSH":
                dataList.type = "BY_SSH";
                //ssh配置
                dataList.ssh_host = $("#ssh_ip").val();
                dataList.ssh_port = $("#ssh_port").val();
                dataList.ssh_user = $("#ssh_name").val();
                dataList.ssh_password = $("#ssh_password").val();
                //config配置
                dataList.kube_config = "";
                dataList.limit_cpu  = $("#ssh_cpu").val();
                dataList.limit_memory  = $("#ssh_ram").val();

                dataList.net_mode  = $("#ssh_network").val();
                //1为服务端连接客户端 2为客户端连接服务端
                if($("#ssh_network").val() == 1){
                    //如果为服务器连接客户端 则不需要输入IP地址
                    dataList.net_mode_host  = "";
                    dataList.net_mode_port  = 0;
                }else{
                    // if($("#ssh_address").val() == "" || $("#ssh_address").val() == null){
                    //     //如果为空则给出提示
                    // }
                    var ip_port = validateIPPort($("#ssh_address").val());
                    if(!ip_port){
                        //如果正则匹配不通过则给出提示
                        UIToastr.showWarning(LANG.UI_KUBE_ADD_KUBERNETES_CLUSTER, LANG.UI_KUBE_INVALID_IP_PORT_FORMAT);
                    }
                    dataList.net_mode_host  = ip_port['ip'];
                    dataList.net_mode_port  = ip_port['port'];
                }


                break;
            case "BY_CONFIG":
                dataList.type = "BY_KUBE_CONFIG";
                //ssh配置
                dataList.ssh_host = "";
                dataList.ssh_port = 0;
                dataList.ssh_user = "";
                dataList.ssh_password = "";
                //config配置
                dataList.kube_config = $("#config_content").val();
                dataList.limit_cpu  = $("#config_cpu").val();
                dataList.limit_memory  = $("#config_ram").val();

                dataList.net_mode  = $("#config_network").val();
                //1为服务端连接客户端 2为客户端连接服务端
                if($("#config_network").val() == 1){
                    //如果为服务器连接客户端 则不需要输入IP地址
                    dataList.net_mode_host  = "";
                    dataList.net_mode_port  = 0;
                }else{
                    if($("#config_address").val() == "" || $("#config_address").val() == null){
                        //如果为空则给出提示
                        UIToastr.showWarning(LANG.UI_KUBE_ADD_KUBERNETES_CLUSTER, LANG.UI_KUBE_INVALID_IP_PORT_FORMAT);
                    }
                    var ip_port = validateIPPort($("#config_address").val());
                    if(!ip_port){
                        //如果正则匹配不通过则给出提示
                    }
                    dataList.net_mode_host  = ip_port['ip'];
                    dataList.net_mode_port  = ip_port['port'];
                }
                break;
        }
        var requestAddCluster = function(d){
            Metronic.unblockUI($("#addClustermodal"));
            if(d.success){
                $('#addClustermodal').modal('hide');
                UIToastr.showSuccess(LANG.UI_KUBE_ADD_KUBERNETES_CLUSTER, d.message);
                //刷新集群列表
                $('#cluster_table').bootstrapTable('refresh');

            }else{
                UIToastr.showWarning(LANG.UI_KUBE_ADD_KUBERNETES_CLUSTER, d.message);
            }
        }
        //发送后端接口
        pAjaxRequest(dataList, "/api/v1/kubernetes/cluster", "POST", requestAddCluster,true);

    }

    //端口号正则匹配/类似于192.168.26.10:8088
    var validateIPPort = function(str) {
        // IP地址和端口号的正则表达式
        const regex = /^(\d{1,3}\.\d{1,3}\.\d{1,3}\.\d{1,3}):(\d+)$/;

        // 使用正则表达式进行匹配
        const match = str.match(regex);

        if (match) {
            const ip = match[1]; // 提取IP地址
            const port = match[2]; // 提取端口号
            return {"ip":ip,"port":port};
        } else {
            return false; // 不符合IP加端口形式的字符串
        }
    }



    //添加集群方式改变不同显示
    var addStyle = function (){
        //得到选择的方式
        var add_style = $('#add_style').val();
        switch (add_style){
            case "BY_MANUAL":
                $(".manual_div").show();
                $(".SSH_div").hide();
                $(".Config_div").hide();
                  //初始化输入框验证
                handleValidation("BY_MANUAL");
                break;
            case "BY_SSH":
                $(".manual_div").hide();
                $(".SSH_div").show();
                $(".Config_div").hide();
                  //初始化输入框验证
                handleValidation("BY_SSH");
                break;
            case "BY_CONFIG":
                $(".manual_div").hide();
                $(".SSH_div").hide();
                $(".Config_div").show();
                  //初始化输入框验证
                handleValidation("");
                break;
        }
        //初始化模态框位置
        var modalHeight = $("#addClustermodal").height();
        $('#addClustermodal').css('margin-top',"-"+modalHeight/2+"px");


    }
    //操作监听事件
    var operateEvents = {
        //修改事件
        'click .editThis': function (e, value, row, index) {
            checkOperateAuth({type: 2,
                source_uuid:row.cluster_uuid,
                source_type: 62,
            },function(){
                edit_cluster_uuid = row.cluster_uuid;
                $("#editClusterName").val(row.cluster_name);
                $("#editClustermodal").modal("show");
            })
           
        },
        // 删除事件
        'click .deleteThis': function (e, value, row, index) {
            checkOperateAuth({type: 2,
                source_uuid:row.cluster_uuid,
                source_type: 62,
            },function(){
                bootbox.confirm({
                    title: LANG.UI_KUBE_DELETE_KUBERNETES_CLUSTER,
                    message: LANG.UI_KUBE_CONFIRM_DELETE_KUBERNETES_CLUSTER,
                    buttons: {
                        confirm: {
                            label: LANG.UI_PUBLIC_CONFIRM,
                            className: 'btn-primary'
                        },
                        cancel: {
                            label: LANG.UI_PUBLIC_CANCEL,
                            className: 'btn-default'
                        }
                    },
                    callback: debounce(function (result) {
                        if (result) {
                            // 用户点击了“删除”按钮
                            var dataList = {};
                            dataList.cluster_uuid_list = [];
                            dataList.cluster_uuid_list.push(row.cluster_uuid);
                            // 发送后端
                            var requestDeleteScript = function(d){
                                if(d.success){
                                    // 删除成功
                                     checkEvent('#cluster_table', '#deleteSelect');
                                    UIToastr.showSuccess(LANG.UI_KUBE_DELETE_KUBERNETES_CLUSTER, d.message);
                                    $('#cluster_table').bootstrapTable('refresh');
                                }else{
                                    UIToastr.showWarning(LANG.UI_KUBE_DELETE_KUBERNETES_CLUSTER, d.message);
                                }
                            }
                            pAjaxRequest(dataList, "/api/v1/kubernetes/cluster", "DELETE", requestDeleteScript, false);
                        } else {
                            // 用户点击了“取消”按钮
                            // 这里可以添加取消后的处理逻辑，如果不需要处理，可以留空
                        }
                    },300)
                });
            })
           
        },

    };


   //验证ip/域名 
	$.validator.addMethod("ipv4Ordomain", function(value, element) {
		var domain = this.optional( element ) || /^(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
		var ipv4 = this.optional(element) || ipV4V6(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
		//http|https
		var domainHTTP = this.optional( element ) || /^(http|https):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test( value );
		var ipv4HTTP = this.optional(element) || /^(http|https):\/\/(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)\.(25[0-5]|2[0-4]\d|[01]?\d\d?)$/i.test(value) || /^((([0-9A-Fa-f]{1,4}:){7}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}:[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){5}:([0-9A-Fa-f]{1,4}:)?[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){4}:([0-9A-Fa-f]{1,4}:){0,2}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){3}:([0-9A-Fa-f]{1,4}:){0,3}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){2}:([0-9A-Fa-f]{1,4}:){0,4}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){6}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(([0-9A-Fa-f]{1,4}:){0,5}:((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|(::([0-9A-Fa-f]{1,4}:){0,5}((\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b)\.){3}(\b((25[0-5])|(1\d{2})|(2[0-4]\d)|(\d{1,2}))\b))|([0-9A-Fa-f]{1,4}::([0-9A-Fa-f]{1,4}:){0,5}[0-9A-Fa-f]{1,4})|(::([0-9A-Fa-f]{1,4}:){0,6}[0-9A-Fa-f]{1,4})|(([0-9A-Fa-f]{1,4}:){1,7}:))$/i.test(value);
		return domain || ipv4 || domainHTTP || ipv4HTTP;
    }, LANG.UI_KUBE_INPUT_IP_EXAMPLE);
    //验证端口
    $.validator.addMethod("port", function(value, element) {
        if(value >= 0 && value <= 65535){
            return true;
        }
        return false;
    }, LANG.UI_KUBE_INPUT_PORT_EXAMPLE);


    //添加/修改客户端数据格式校验
    var handleValidation = function(add_style) {
        var form2 = $('#form_add_cluster');
        var error2 = $('.alert-danger', form2);
        var success2 = $('.alert-success', form2);
        var rules = {};
        //获取添加方式
        switch (add_style){
            case "BY_MANUAL":
                rules = {
                    manual_ip_verify: {
                        required: true,
                        ipv4Ordomain: true,
                    },
                    manual_nickname_verify: {
                        required: true,
                    },
                    manual_port_verify: {
                        required: true,
                        port: true,
                    },
                };
                break;
            case "BY_SSH":
                rules = {
                    ssh_ip_verify: {
                        required: true,
                        ipv4Ordomain: true,
                    },
                    ssh_port_verify: {
                        required: true,
                        port: true,
                    },
                    ssh_name_verify: {
                        required: true,
                    },
                };
                break;
            default:
                rules = {};
                break;
        }
       
        if (form2.data('validator')) {
            form2.validate().resetForm();
            form2.validate().destroy();
        }
      

        form2.validate({
            errorElement: 'span', //default input error message container
            errorClass: 'help-block help-block-error', // default input error message class
            focusInvalid: false, // do not focus the last invalid input
            ignore: "",  // validate all fields including form hidden input
            rules: rules,

            invalidHandler: function (event, validator) { //display error alert on form submit              
                success2.hide();

            },

            errorPlacement: function (error, element) { // render error placement for each input type
                var icon = $(element).parent('.input-icon').children('i');
                icon.removeClass('fa-check').addClass("fa-warning");  
                icon.attr("data-original-title", error.text()).tooltip({'container': 'body'});
            },

            highlight: function (element) { // hightlight error inputs
                $(element).closest('.form_div').removeClass("has-success").addClass('has-error'); // set error class to the control group   
            },

            unhighlight: function (element) { // revert the change done by hightlight
                
            },

            success: function (label, element) {
                var icon = $(element).parent('.input-icon').children('i');
                $(element).closest('.form_div').removeClass('has-error').addClass('has-success'); // set success class to the control group
                icon.removeClass("fa-warning").addClass("fa-check");
            },

            submitHandler: function (form) {
                success2.show();
            }
            
        });

        $("#current_cluster_submit").off('click').click(function(){
            if (form2.validate().form()) {
                submitAddCluster()
            }
        });
      
    };







    return{
        init: function () {
            initDataTable();
            //初始化spinner插件
            initSpinner();
            initListeners();
        }
    };
}();
jQuery(document).ready(function () {
    kubernetes_cluster.init();
});