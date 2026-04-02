var ExchangeRecover = function () {
    var data = {recover_info:{},type_info:{high:{transfer:{}},strategy: {}},high_conf:{}};
    data.exch_recovery_object_info_list = [];
    var totalDataList = [];//用于保存购物车表格数据
    data.normal_search = [];//用于保存普通搜索要用的数据
    var timepointTree;
    var flagDay = false;
    var initSpeedFlag = false;
    var speedList = [];
    var src_organization = '';
    var allpointlist = [];
    var forbidUserSelect = false;//用于第二步判断第一步是否禁用了用户下拉框
    var loadMoreShow = false;
    var networkFlag = false; //是否显示传输网络
    var region = '';
    var op_id = '';
    var auth_node;
    var auth_rowdata;
    var cache_finish_flag = false;//是否真正加载完成(全文搜索)
    var cache_finish_flag_advanced = false;//是否真正加载完成(高级搜索)
    var firstSearch = true;//是否是第一次搜索（只要切换搜索条件，就算第一次搜索，此时将last_search_info置空）
    var firstSearchAdvanced = true;
    let more_page_size = 50; //加载更多要读取多少个用户
    var storage_type; //存储恢复源数据类型
    var userTree;
    var limit_num = 50;
    // 从备份数据跳转恢复页面
	const externalPointUuid = $('#externalPointUuid').val();
	const externalTaskUuid = $('#externalTaskUuid').val();
	const externalItemUuid = $('#externalItemUuid').val();
    var firstFlag = false;//第一次从备份数据跳转恢复页面选中时间点
    var initListener = function () {
        //点击复制按钮
        $('#addVerify .copy').on('click',function () {
            copyToClipboard($('#vertifyCode').val());
            //显示复制成功的提示信息，1s后隐藏提示信息
            $('.copy-tips').show();
            setTimeout(function () {
                $('.copy-tips').hide();
            },1000)
        });
        //去身份验证
        $('.verifyLink').on('click',function () {
            $('.vertify-waiting').show();
            getAuthInfo();
        });
        //刷新验证码
        $('.vicon-a-Redozhongxin').on('click',function () {
            op_id = ''
            getVertifyCode();
        });
        //点击取消时间点身份验证
        $('.cancleBtn').on('click',function () {
            $('.point-table #pointTable').bootstrapTable('uncheck', 1)
        });

        $('#tobackup').on('click',function () {
            LOCATION('./content/exchange/exchange_backup.php', 'exchange_backup');
        });
        $('#toAdd').on('click',function () {
            LOCATION('./content/exchange/exchange_organization.php', 'exchange_organization');
        });
        
        // 选择恢复方式
		$('#recovertype').on('change',function(){
			if('1' == this.value){
			    data.type_info.strategy = {};
				$('.setOnceTime').hide();
			}else if("4" == this.value){
				$('.setOnceTime').show();
			}
			getTimeDes();
		});
        //时间策略确定
        $('.each-button').on("click",eachButtonClick);


        //初始化添加限速策略模态框
        $('#addSpeedlimit').on('click', function () {
            $('#speedlimitModal').modal({'width':'800px', 'height':'380px'});
            if (!initSpeedFlag) {
                initSpeedTimeStrategy();
            }

        });
        //切换限速模式
        $('#speedModeType').on('change', speedModeHandler);

        //添加限速策略确定
        $('#speed_submit').on('click', speedSubmit);
        //初始化限速大小设置值
        $('#speedSpinnerNum').spinner({value:10, step: 5, min: 1, max: 10000000000});
        $('#recoveryThreadDiv').spinner({value:3, step: 1, min: 1, max: 16});
        $('#retryTime').spinner({value:5, step: 5, min: 1, max: 30});
        $('#retryNum').spinner({value:3, step: 1, min: 1, max: 5});
        $('.advanceSearch').on('click',function () {
            toAdvanceSearch();
        });
        $('.totalDataDiv').on('click',function () {
            Metronic.blockUI({target: '#drawer-1',animate: true});
            totalDataList.forEach((item,i) => {
                totalDataList[i][0] = false;
            });
            setTimeout(function () {
                totalDatatable();
            },500);
        });
        $('.searchSubmit').on('click',function () {
            toSubmitSearch();
        });
        $('.clearSearch').on('click',function () {
            toClearSearch();
        });
        //导出
        $('.exportEmail').on('click',function () {
            toExportData(false,false);
        });
        //批量导出
        $('#batchExport').on('click',function () {
            toExportData(true,false);
        });
        //搜索的批量导出
        $('#batchExport_search').on('click',function () {
            toExportData(true,true);
        });
        //普通全文搜索
        $('#normalSearchBtn').on('click',function () {
            $('.seachDiv .squreBtn').css({"background-color":"#F0F0F0","color":"#D3D3D3"});
            normalSearch();
        });
        $('#searchExchange').on('keydown', debouncem365);
        $('#delete-shopping').on('click',function () {
            Metronic.blockUI({target: '#totalTable',animate: true,});
            setTimeout(function () {
                toDeteteShopData();
            },200);
        });
        $('#category').on('change',function () {
            categoryChange();
        });
        categoryChange();
        $('#modify-send').on("click",function () {
            if ($.inArray('setting_manager', CONF.PERMISSION) == -1) {
                UIToastr.showWarning(LANG.UI_MICROSOFT365_SETTING_CONFIG,LANG.UI_MICROSOFT365_NO_PERMISSION);
                return;
            }
            $('#emailModal').modal('hide');
            $('#drawer-1').drawer("hide");
            LOCATION('./content/platform/settings/settingstab/system_notice.php', 'setting_manager');
        })
        // 传输策略---加密传输
        $('#transport_encrypt_flag').on('switchChange.bootstrapSwitch', transferEncryptChange);
        //
        $('#recovery-way-div').on('change',function () {
            recoveryWayChange();
        });
        //初始化勾选框的颜色
        $('.loadMore-div input').iCheck({
            checkboxClass: 'icheckbox_minimal-aero',
            radioClass: 'iradio_minimal-aero'
        });
        $('.loadMore-div-search input').iCheck({
            checkboxClass: 'icheckbox_minimal-aero',
            radioClass: 'iradio_minimal-aero'
        });
        //切换自动重试开关
        $('#autoRetry').on('switchChange.bootstrapSwitch', autoRetryChange);
        $('#agentConfig').on('switchChange.bootstrapSwitch', agentConfigChange);
        $('#agentList').on('change', agentListChange);
        //用户模态框
        $('#showUserModal').on("click",function () {
            $('#userModal').modal({'width':'500px', 'height':'350px'});
        })
        $('.userSubmit').on('click',function () {
            toSubmitUser();
        });
        $('#searchUserInput').on('keydown', toSearchUser);
        $('#retry_config').retryStrategy();
        inintDatatimePicker();
    }

    var inintDatatimePicker = function () {
        //设置时间
        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
            //英文独有的
           $(".form_datetime").datetimepicker({
            autoclose: true,
            isRTL: Metronic.isRTL(),
            format: "yyyy-mm-dd hh:ii:ss",
            pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
            startDate: new Date()
           });
        }else{
           $(".form_datetime").datetimepicker({
                 language:  'zh-CN', 
                 autoclose: true,
                 isRTL: Metronic.isRTL(),
                 format: "yyyy-MM-dd hh:ii:ss",
                 pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                 startDate: new Date()
           });
        }
        $('#resetdate').on('click', function () {
            $('#oncetime').val('');
            getTimeDes();
        });
        $('#oncetime').on('change', getTimeDes);
    }

    // 获取时间策略信息描述
	var getTimeDes = function () {
		var des = '';
		des = $('#recovertype').find("option:selected").text();
		if ($('#recovertype').val() == 4) {
			des += '，' + LANG.UI_JOB_TIMING_RECOVER_TIME + "：" + $('#oncetime').val();
		}
		$('.recoveryTimeDes').html(des);
		$('.recoveryTimeDes').prop('title', des);
	}

    var toSubmitUser = function () {
        $('#userModal').modal('hide')
        var checkedNodes = userTree.getCheckedNodes(true);
        if (checkedNodes.length == 0) {
            $('#userSelect').val(LANG.UI_MICROSOFT365_NOT_SPECIFY_USER);
            $('#userSelect').attr({'destination_user_uuid': '', 'destination_mail': '', 'destination_user_type': 0});
        } else {
            $('#userSelect').val(checkedNodes[0].name);
            $('#userSelect').attr({'destination_user_uuid': checkedNodes[0].uuid, 'destination_mail': checkedNodes[0].mail, 'destination_user_type': checkedNodes[0].type});
        }
    }
    var userTimer = null
    var toSearchUser = function () {
        clearTimeout(userTimer)
        userTimer = setTimeout(function () {
            var key_word = $('#searchUserInput').val().trim();
            var organizationNode = zTree.getCheckedNodes(true);
           if(organizationNode.length == 0) {
               return;
           }
            if (key_word == '') {
                initUserTree(organizationNode[0]);
                return;
            }
            let params = {
                'organization_uuid': organizationNode[0].id,
                'recovery_key_word':key_word,
                'organization_name':organizationNode[0].name,
            };
            Metronic.blockUI({target: '#userModal',animate: true});
            pAjaxRequest(params, "/api/v1/exchange/jobs/organization", "GET", function (result) {
                if (result.success) {
                    var nodes = userTree.getNodes();
                    for (var i = nodes.length-1; i >= 0; i--) {
                        userTree.removeNode(nodes[i]);
                    }
                    userTree.addNodes(null, result.data, true);
                    userTree.expandAll(true, true, true);
                }
                Metronic.unblockUI('#userModal');
            });

        }, 500)
    }

    //指定客户端切换
    const agentListChange = function () {
        var selectNetModel = $('#agentList').find("option:selected").attr('net_model');
        if (selectNetModel == 2) {
            $('.transfernetworkDiv').show();
        } else {
            $('.transfernetworkDiv').hide();
        }
    }

    // 显示客户端配置
    var agentConfigChange = function () {
        if (this.checked) {
            $('.agentListDiv').show();
            agentListChange();
        } else {
            $('.agentListDiv').hide();
            //指定客户端关闭，根据networkFlag判断是否显示传输网络
            if (networkFlag) {
                $('.transfernetworkDiv').show();
            } else {
                $('.transfernetworkDiv').hide();
            }
        }
    }
    var autoRetryChange = function () {
        if (this.checked) {
            $('.retryTimeDiv').show();
            $('.retryNumDiv').show();
        } else {
            $('.retryTimeDiv').hide();
            $('.retryNumDiv').hide();
        }
    }

    //复制内容到剪切板
    var copyToClipboard = function (str) {
        var el = document.createElement("textarea");
        el.value = str;
        el.setAttribute("readonly","");
        document.body.appendChild(el);
        el.select();
        document.execCommand("copy");
        document.body.removeChild(el);
    }
    var recoveryWayChange = function () {
        if ($('#recovery-way-div').val() == 1) { //覆盖
            $('#userSelect').val(LANG.UI_MICROSOFT365_NOT_SPECIFY_USER);
            $('#userSelect').attr({'destination_user_uuid': '', 'destination_mail': '', 'destination_user_type': 0});
            $('.select-user').find('button, input').prop('disabled',true);
        } else {
            if (forbidUserSelect) {//本来就是禁用的
                $('.select-user').find('button, input').prop('disabled',true);
            } else {//本身没有禁用
                $('.select-user').find('button, input').prop('disabled',false);
            }
        }
    }
    // 显示加密算法
    var transferEncryptChange = function () {
        if (this.checked) {
            $('.transfer-encrypt-method-form').show();
        } else {
            $('.transfer-encrypt-method-form').hide();
        }
    }
    var categoryChange = function () {
        $('#folder').find('option').show();
        $('#position').find('option').show();
        var category = $('#category').val();
        switch (parseInt(category)) {
            case 0://邮件
                $('#folder').find('option[value="100"]').hide();
                $('#folder').find('option[value="200"]').hide();
                $('#folder').find('option[value="300"]').hide();
                $('#position').find('option[value="4"]').hide();
                $('#position').find('option[value="5"]').hide();
                $('#position').find('option[value="6"]').hide();
                $('#folder').val(0);
                $('#position').val(0);
                break;
            case 1://事件
                $('#folder').find('option[value="0"]').hide();
                $('#folder').find('option[value="1"]').hide();
                $('#folder').find('option[value="2"]').hide();
                $('#folder').find('option[value="3"]').hide();
                $('#folder').find('option[value="4"]').hide();
                $('#folder').find('option[value="5"]').hide();
                $('#folder').find('option[value="6"]').hide();
                $('#folder').find('option[value="7"]').hide();
                $('#folder').find('option[value="200"]').hide();
                $('#folder').find('option[value="300"]').hide();
                $('#position').find('option[value="4"]').hide();
                $('#position').find('option[value="5"]').hide();
                $('#position').find('option[value="6"]').hide();
                $('#folder').val(100);
                $('#position').val(0);
                break;
            case 2://联系人
                $('#folder').find('option[value="0"]').hide();
                $('#folder').find('option[value="1"]').hide();
                $('#folder').find('option[value="2"]').hide();
                $('#folder').find('option[value="3"]').hide();
                $('#folder').find('option[value="4"]').hide();
                $('#folder').find('option[value="5"]').hide();
                $('#folder').find('option[value="6"]').hide();
                $('#folder').find('option[value="7"]').hide();
                $('#folder').find('option[value="100"]').hide();
                $('#folder').find('option[value="300"]').hide();
                $('#position').find('option[value="2"]').hide();
                $('#position').find('option[value="3"]').hide();
                $('#position').find('option[value="4"]').hide();
                $('#position').find('option[value="5"]').hide();
                $('#position').find('option[value="6"]').hide();
                $('#folder').val(200);
                $('#position').val(0);
                break;
            case 3://任务
                $('#folder').find('option[value="0"]').hide();
                $('#folder').find('option[value="1"]').hide();
                $('#folder').find('option[value="2"]').hide();
                $('#folder').find('option[value="3"]').hide();
                $('#folder').find('option[value="4"]').hide();
                $('#folder').find('option[value="5"]').hide();
                $('#folder').find('option[value="6"]').hide();
                $('#folder').find('option[value="7"]').hide();
                $('#folder').find('option[value="100"]').hide();
                $('#folder').find('option[value="200"]').hide();
                $('#position').find('option[value="4"]').hide();
                $('#position').find('option[value="5"]').hide();
                $('#position').find('option[value="6"]').hide();
                $('#folder').val(300);
                $('#position').val(0);
                break;
            case 4://地址
                $('#position').find('option[value="1"]').hide();
                $('#position').find('option[value="2"]').hide();
                $('#position').find('option[value="3"]').hide();
                $('#folder').val(0);
                $('#position').val(0);
                break;
        }
    }
    // ****************************恢复源********************************
    //删除购物车数据
    var toDeteteShopData = function () {
        var selectedRow = $('#totalTable').bootstrapTable('getSelections');
        if (selectedRow < 1) {
            UIToastr.showWarning(LANG.UI_MICROSOFT365_DELETE_DATA,LANG.UI_MICROSOFT365_DELETE_DATA_TIPS);
            return;
        }
        //所有需要删除的ID
        const idsToRemove = selectedRow.map(item => item.id);
        // 批量删除
        $('#totalTable').bootstrapTable('remove', {
            field: 'id',
            values: idsToRemove,
        });
        totalDataList = totalDataList.filter(item => !idsToRemove.includes(item.id));
        //在所有表格中取消选中
        $('.task-table #taskTable, .calendar-table #calendarTable, .contacts-table #contactsTable, .rootdir-table #rootDirTable, .user-table #userTable, .point-table #pointTable').each(function() {
            const table = $(this);
            table.bootstrapTable('uncheckBy', {field: 'id', values: idsToRemove});
        });
        UIToastr.showSuccess(LANG.UI_MICROSOFT365_DELETE_DATA,LANG.UI_MICROSOFT365_DELETE_DATA + LANG.UI_PUBLIC_SUCCESS);
        $('.totalNum').text(totalDataList.length);
        modifyDelStyle();
        Metronic.unblockUI('#totalTable');
    }
    // 防抖
    var timer = null
    var nodeParamList;
    var debouncem365 = function () {
        clearTimeout(timer)
        timer = setTimeout(function () {
            var value = $('#searchExchange').val();
            // 输入验证
            if(!customInputValidate('string',value)){
                return false;
            }
            var nodes = timepointTree.getNodes();
            if (!nodes || nodes.length == 0) {
                return;
            }
            //获取所勾选的
            var allNode = timepointTree.transformToArray(timepointTree.getNodes());
            nodeParamList = timepointTree.getNodesByParamFuzzy('name', value);
            if (nodeParamList.length != 0) {
                timepointTree.hideNodes(allNode);
                $('#exchange_point_tree').show();
                $('#nosearchtips').hide();
            } else {
                $('#exchange_point_tree').hide();
                $('#nosearchtips').show();
            }
            //连接搜索的和所勾选的
            nodeParamList = timepointTree.transformToArray(nodeParamList);
            for (var n in nodeParamList) {
                findParent(timepointTree,nodeParamList[n]);
            }
            nodeParamList = $.unique(nodeParamList.sort());
            if (nodeParamList.length == 0) {
                $('#exchange_point_tree').hide();
                $('#nosearchtips').show();
            }
            timepointTree.showNodes(nodeParamList);
        }, 500)
    }
    //找到父节点
    var findParent = function (treeObj,node) {
        var pNode = node.getParentNode();
        if (pNode != null) {
            nodeParamList.push(pNode);
            findParent(timepointTree,pNode);
        } else {
            return;
        }
    }
    //普通全文搜索
    var normalSearch = function () {
        cache_finish_flag = false;
        firstSearch = false;
        var keywordVal = $.trim($('#normalSearchInput').val());
        var currentNum = 0;
        var page_size_metadata = '';//加载更多默认条数
        if (keywordVal == "") {
            return;
        }
        if (keywordVal.length > 200) {
            UIToastr.showInfo(LANG.UI_MICROSOFT365_SEARCH_DATA, LANG.UI_MICROSOFT365_SEARCH_DATA_TIPS);
            return;
        }
        $('.searchContent').html('<i style="display:inline-block;max-width: 250px;text-overflow: ellipsis;overflow: hidden;" title="' + keywordVal + '">'+ LANG.UI_SETTING_VM_DATA_SCREEN +'：' + keywordVal + '</i>');
        var params = {
            node_uuid:data.node_uuid,
            timepoint_uuid:data.recovery_timepoint_uuid ? data.recovery_timepoint_uuid : '',
            organization_uuid:data.organization_uuid,
            exch_search_condition : {
                user_uuid: data.search_user_uuid ? data.search_user_uuid : "",
                type: data.normal_search.type ? parseInt(data.normal_search.type) : 0,//目录类别
                folder_id: data.normal_search.folder_id ? data.normal_search.folder_id : '',//目录id
                keyword: keywordVal//搜索条件
            },
            last_search_info : {
                id:0,//上一次检索的用户id
                user_uuid: "",//上一次检索的用户
                index_container_id: 0,
                table_id: 0,
                type: 0
            },
            next_start : 0,
            page_size : 20,
        };
        var operates = {
            'click .detail' : function (e, value, row, index) {
                var arr = [];
                arr.push(row);
                getMedaDetails(arr,0);
            }
        };
        var options = {
            pagination: true,
            pageList: [5, 10, 25, 50],
            sidePagination: "client", //分页方式，client和server
            maintainMetaData: true,
            vin_params:function () {
                params = deepCloneObject(params);
                return params;
            },
            vin_url:"/api/v1/exchange/restore/normal_search",
            vin_method:"GET",
            columns: [{
                checkbox: true,
                sortable: false,
            },
                {
                    field: 'name',
                    title: LANG.UI_COPY_TYPE,
                    sortable: false,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'contact_name',
                    title: LANG.UI_MICROSOFT365_NAME,
                    sortable: false,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'send',
                    title: LANG.UI_MICROSOFT365_FROM,
                    sortable: false,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'receive',
                    title: LANG.UI_MICROSOFT365_RECIPIENT,
                    sortable: false,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'subject',
                    title: LANG.UI_MICROSOFT365_THEME,
                    sortable: false,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'recv_date',
                    title: LANG.UI_MICROSOFT365_RECEIVE_TIME,
                    sortable: false,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'operate',
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: false,
                    align: 'center',
                    events: operates,
                    opButton: true,
                    formatter: function (value,data,row) {
                        return value;
                    }
                },],
            onPostBody: function () {
                //普通全文搜索
                //获取bootstrap的加载完毕的数据
                var row = $('#searchTable').bootstrapTable('getData');
                for (var i = 0; i < row.length; i++) {
                    if (row[i] != undefined && row[i].more && !cache_finish_flag) {
                        currentNum = parseInt(row.length - 1);
                        $('#searchTable').bootstrapTable('remove', {field: '$index', values: [i]})//在表格中移除php返回的加载更多那一行
                        $('.loadMore-div-search').show();
                        $('#exchange-recover-content .addIt-list').css({"height":"calc(100% - 81px)","border-bottom":"none"});
                        $('input[name="page_num_search"]').on('ifClicked', function () {
                            if ($(this).val() == 0) {
                                $('.seachDiv .diy-label').hide();
                                $('.seachDiv .diy-input').show(); // 显示自定义输入框
                            } else {
                                $('.seachDiv .diy-label').show();
                                $('.seachDiv .diy-input').hide(); // 隐藏自定义输入框
                            }
                        });
                        $('.loadMoreSearchClick').off().on('click',function () {
                            var icheckArr = $('.loadMore-div-search').find('.icheck');
                            for (var j = 0; j < icheckArr.length; j++) {
                                if ($(icheckArr[j]).is(':checked')) {
                                    if ($(icheckArr[j]).val() == 0) {
                                        page_size_metadata = $.trim($('.seachDiv .diy-input').val());
                                        if (page_size_metadata == '') {
                                            UIToastr.showWarning(LANG.UI_MICROSOFT365_LOAD_MORE_DATA,LANG.UI_MICROSOFT365_INPUT_LOAD_ITEM);
                                            return;
                                        }
                                    } else {
                                        page_size_metadata = $(icheckArr[j]).val();
                                    }
                                }
                            }
                            var lastSearch = row[i - 2].last_search_info;
                            if (firstSearch) {
                                lastSearch = {id:0, user_uuid: "", index_container_id: 0, table_id: 0, type: 0};
                            }
                            var params = {
                                node_uuid:row[i - 2].node_uuid,
                                timepoint_uuid:row[i - 2].timepoint_uuid,
                                organization_uuid:row[i - 2].organization_uuid,
                                exch_search_condition : {
                                    user_uuid: data.search_user_uuid,
                                    type:row[i - 2].type,//目录类别
                                    folder_id:data.normal_search.folder_id,
                                    keyword: keywordVal//搜索条件
                                },
                                last_search_info : lastSearch,
                                next_start: row[i - 2].next_start ? row[i - 2].next_start : 0,
                                page_size: page_size_metadata,//加载更多请求条数
                                current_num:currentNum
                            };
                            params = deepCloneObject(params);
                            Metronic.blockUI({target: '.seachDiv',animate: true});
                            pAjaxRequest(params, "/api/v1/exchange/restore/normal_search", "GET", function (result) {
                                Metronic.unblockUI('.seachDiv');
                                if (result.success) {
                                    //success
                                    $('#searchTable').bootstrapTable('append', result.data.rows)
                                } else {
                                    operateResponseList(result, LANG.UI_MICROSOFT365_GET_METADATA);
                                }
                            });

                        });
                    } else {
                        if (row[i].finish_flag == 2 && row.length == i+1 && !cache_finish_flag) {//点分页的页码会走进这里面
                            $('.loadMore-div-search').show();
                            $('#exchange-recover-content .addIt-list').css({"height":"calc(100% - 81px)","border-bottom":"none"});
                            return;
                        } else if (row[i].finish_flag == 3) {
                            cache_finish_flag = true;
                            $('#searchTable').bootstrapTable('remove', {field: '$index', values: [i]})//在表格中移除php返回的数组为空时自定义的那一行
                        }
                        $('.loadMore-div-search').hide();
                        $('.seachDiv .addIt-list').css({"height":"calc(100% - 34px)","border-bottom":"1px solid #E6E6E6"});
                    }
                }
            },
            onCheck: function (rowdata) {
                // 单个选中行，加入统计数量的购物车
                addToTotal('#searchTable tbody .selected',1,rowdata,false);
                $('.seachDiv .squreBtn').css({"background-color":"#0FBF98","color":"white"});
                // 第四步显示
                $('.recoverpoint').html(rowdata.name);
            },
            onUncheck: function (rowdata) {
                //取消单个选中
                cancleToTotal(1,rowdata,false);
                //表格没有选中的行  置灰
                var checkedRows = $('#searchTable').bootstrapTable('getSelections');
                if (checkedRows == 0) {
                    $('.seachDiv .squreBtn').css({"background-color":"#F0F0F0","color":"#D3D3D3"});
                }
            },
            onCheckAll: function (a,b,c) {
                //全选
                var currentArr = a;
                if (a.length > b.length && b.length != 0) {
                    currentArr = a.filter(item => !b.includes(item));
                }
                var rows = $('#searchTable').bootstrapTable('getSelections').length;
                addToTotal('#searchTable tbody',rows,currentArr,true)
                $('.seachDiv .squreBtn').css({"background-color":"#0FBF98","color":"white"});
            },
            onUncheckAll: function (a,b,c) {
                // 取消全选
                var currentArr = b;
                if (b.length > a.length && a.length != 0) {
                    currentArr = b.filter(item => !a.includes(item));
                }
                var rows = $('#searchTable tbody tr').length;
                cancleToTotal(rows,currentArr,true);
                $('.seachDiv .squreBtn').css({"background-color":"#F0F0F0","color":"#D3D3D3"});
            },

        }
        $('.exchange-div').hide();
        $('.seachDiv').show();
        $('#searchTable').bootstrapTable('destroy');
        $('#searchTable').baseTableConfig().init(options);
        $('#normalSearchInput').val('');
    }
    //第一个flag是是否为批量导出，第二个参数为是否是搜索的批量导出
    var toExportData = function (flag,searchFlag) {
        //是否是批量导出
        var item_id_list = [];
        var item_uuid_list = [];
        var targetDiv = ".exchange-div";
        if (flag) {
            if (searchFlag) {
                var arr = $('#searchTable').bootstrapTable('getSelections');
                targetDiv = "#searchTable";
                //搜索结果可能是多个用户和多个目录的，不支持多用户以及多目录
                for (let m = 0; m < arr.length; m++) {
                    for (let n = m + 1; n < arr.length; n++) {
                        if (arr[m].user_uuid != arr[n].user_uuid || arr[m].parent_folder_id != arr[n].parent_folder_id) {
                            UIToastr.showWarning(LANG.UI_MICROSOFT365_EXPORT_DATA,LANG.UI_MICROSOFT365_EXPORT_DATA_TIPS);
                            return;
                        }
                    }
                }
            } else {
                var arr = $(data.seachDiv).bootstrapTable('getSelections');
            }
            if (arr.length == 0) {//批量导出未选中任何数据
                UIToastr.showWarning(LANG.UI_MICROSOFT365_BATCH_EXPORT,LANG.UI_MICROSOFT365_BATCH_EXPORT_TIPS);
                return;
            }
            for (var i = 0; i < arr.length; i++) {
                if (arr[i].dir_type == 6) {
                    UIToastr.showWarning(LANG.UI_MICROSOFT365_BATCH_EXPORT,LANG.UI_MICROSOFT365_BATCH_EXPORT_METADATA_TIPS);
                    return;
                }
                item_id_list.push(arr[i].item_id);
                item_uuid_list.push(arr[i].item_uuid);
            }
            var p = {
                node_uuid:data.node_uuid,
                timepoint_uuid:data.recovery_timepoint_uuid,
                organization_uuid:data.organization_uuid,
                user_uuid: arr[0].user_uuid,
                index_container_id: arr[0].index_container_id,
                table_id: arr[0].table_id,
                folder_id: arr[0].parent_folder_id,
                type:arr[0].type,
                item_id_list: item_id_list,
                item_uuid_list: item_uuid_list,
            };
        } else {
            item_id_list.push(data.send.item_id);
            item_uuid_list.push(data.send.item_uuid);
            var p = {
                node_uuid:data.node_uuid,
                timepoint_uuid:data.recovery_timepoint_uuid,
                organization_uuid:data.organization_uuid,
                user_uuid: data.search_user_uuid,
                index_container_id: data.send.index_container_id,
                table_id: data.send.table_id,
                folder_id: data.send.folder_id,
                type: data.send.type,
                item_id_list: item_id_list,
                item_uuid_list: item_uuid_list,
            };
        }
        Metronic.blockUI({target: targetDiv,animate: true});
        //发送导出的消息
        pAjaxRequest(p, "/api/v1/exchange/restore/export_zip", "POST", function (d) {
            if (d.data.result) {
                //获取导出的数据
                window.location.href = '/api/v1/exchange/restore/export_data' + '?node_uuid=' + p.node_uuid + '&export_file_name=' + d.data.msg.export_file_name + '&export_file_size=' + d.data.msg.export_file_size  + '&timepoint_uuid=' +  p.timepoint_uuid + '&x-api-version=1.0-rev0';
                UIToastr.showSuccess(LANG.UI_MICROSOFT365_EXPORT_DATA,LANG.UI_MICROSOFT365_EXPORT_DATA_SUCCESS);
            } else {
                UIToastr.showWarning(LANG.UI_MICROSOFT365_EXPORT_DATA,LANG.UI_MICROSOFT365_EXPORT_DATA_FAILED);
            }
            Metronic.unblockUI(targetDiv);
        }, true);
    }

    var toSendEmail = function (detail) {
        //检测邮件格式--可以是一个或者多个邮箱，多个邮箱之间用英文的分号隔开
        const emailRegex = /^[\w.%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,}(?:;[\w.%+-]+@[A-Za-z0-9.-]+\.[A-Za-z]{2,})*$/;
        // 检测邮箱格式是否正确
        const isValidEmailString = emailRegex.test($.trim($('#sendTo').val()));
        if (!isValidEmailString) {
            $('#sendTo').css({"border-color": "red",});
            UIToastr.showWarning(LANG.UI_MICROSOFT365_SEND_EMAIL, LANG.UI_MICROSOFT365_INPUT_CORRECT_EMAIL_TIPS);
            return;
        } else {
            $('#sendTo').css({"border-color": "#E6E6E6",});
        }
        if ($.trim($('#ccTo').val()) != '') {
            const isValidEmailString = emailRegex.test($.trim($('#ccTo').val()));
            if (!isValidEmailString) {
                $('#ccTo').css({"border-color": "red",});
                UIToastr.showWarning(LANG.UI_MICROSOFT365_SEND_EMAIL, LANG.UI_MICROSOFT365_INPUT_CORRECT_EMAIL_TIPS);
                return;
            } else {
                $('#ccTo').css({"border-color": "#E6E6E6",});
            }
        } else {
            $('#ccTo').css({"border-color": "#E6E6E6",});
        }
        $('#sendTo').attr('disabled',true);
        var params = {
            'detail': detail,
            'info': {
                'email' : $.trim($('#sendTo').val()).split(';'),
                'cc' : $.trim($('#ccTo').val()).split(';'),
            }
        }
        Metronic.blockUI({target: "#emailModal",animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore/send_email", "POST", function (d) {
            Metronic.unblockUI("#emailModal");
            if (d.success) {
                UIToastr.showSuccess(LANG.UI_MICROSOFT365_SEND_EMAIL,LANG.UI_MICROSOFT365_SEND_EMAIL_SUCCESS);
            } else {
                UIToastr.showWarning(LANG.UI_MICROSOFT365_SEND_EMAIL,d.message);
            }
            $('#sendTo').removeAttr('disabled');
        }, true);
    }
    var toClearSearch = function () {
        $('.seachDiv').hide();
        $('.loadMore-div-search').hide();
        $('.exchange-div').show();
        if (loadMoreShow) {
            $('.loadMore-div').show();
        } else {
            $('.loadMore-div').hide();
            $('#exchange-recover-content .addIt-list').css({"height":"calc(100% - 15px)","border-bottom":"1px solid #E6E6E6"});
        }
    }

    //显示搜索内容
    var addSearchContent = function (p) {
        var info = "";
        $('.searchContent').text('');
        //类别、文件夹、位置、条件、是否包含附件、关键字
        info += '<span id="category-span" style="padding: 3px 10px;" title="' + $('#category').find("option:selected").text() + '"> ' + LANG.UI_MICROSOFT365_CATEGORY + ': ' + $('#category').find("option:selected").text() + '</span>';
        info += '<span id="folder-span" style="padding: 3px 10px;" title="' + $('#folder').find("option:selected").text() + '"> ' + LANG.UI_DATA_FILE_DIR + ': ' + $('#folder').find("option:selected").text() + '</span>';
        info += '<span id="position-span" style="padding: 3px 10px;" title="' + $('#position').find("option:selected").text() + '"> ' + LANG.UI_MICROSOFT365_LOCATION + ': ' + $('#position').find("option:selected").text() + '</span>';
        info += '<span id="condition-span" style="padding: 3px 10px;" title="' + $('#condition').find("option:selected").text() + '"> ' + LANG.UI_MICROSOFT365_CONDITION + ': ' + $('#condition').find("option:selected").text() + '</span>';
        info += '<span id="attachment-span" style="padding: 3px 10px;" title="' + $('#attachment').find("option:selected").text() + '"> ' + LANG.UI_MICROSOFT365_INCLUDE_ATTACHMENTS + ': ' + $('#attachment').find("option:selected").text() + '</span>';
        info += '<span id="keywords-span" style="padding: 3px 10px; max-width: 250px;text-overflow: ellipsis;overflow: hidden;vertical-align: bottom;" title="' + $('#keywords').val() + '"> ' + LANG.UI_MICROSOFT365_KEYWORD + ':' + $('#keywords').val() + '</span>';
        $('.searchContent').append(info);

    }

    var toSubmitSearch = function () {
        $('.seachDiv .squreBtn').css({"background-color":"#F0F0F0","color":"#D3D3D3"});
        var keywordVal = $.trim($('#keywords').val());
        if (keywordVal == "") {
            UIToastr.showWarning(LANG.UI_JOB_SEARCH_EXP,LANG.UI_MICROSOFT365_INPUT_KEYWORD_TIPS);
            $('#keywords').css({"border-color":"red"});
            return;
        }
        if (keywordVal.length > 200) {
            UIToastr.showInfo(LANG.UI_JOB_SEARCH_EXP, LANG.UI_MICROSOFT365_INPUT_CONTENT_TIPS);
            return;
        }
        var params = {
            node_uuid:data.node_uuid,
            timepoint_uuid:data.recovery_timepoint_uuid,
            organization_uuid:data.organization_uuid,
            exch_advanced_search_condition : {
                user_uuid: data.search_user_uuid ? data.search_user_uuid : "",
                category: $('#category').val(),//类别
                priv_type: $('#folder').val(),//文件夹固定编号
                field: $('#position').val(),//位置
                condition: $('#condition').val(),//匹配条件
                is_attachment: $('#attachment').val(),
                keyword: keywordVal//搜索条件
            },
            last_search_info : {
                id: 0,//上一次检索的用户id
                user_uuid: "",//上一次检索的用户
                index_container_id: 0,
                table_id: 0,
                type: ""
            },
            next_start : 0,
            page_size : 20,
        };
        //显示要搜索的内容
        addSearchContent(params);
        initAdvancedSearchTable(params);
    }
    var initAdvancedSearchTable = function (params) {
        cache_finish_flag_advanced = false;
        firstSearchAdvanced = false;
        $('.exchange-div').hide();
        $('.seachDiv').show();
        params = deepCloneObject(params);
        pAjaxRequest(params, "/api/v1/exchange/restore/advanced_search", "GET", function (d) {
            var currentNum = 0;
            var page_size_metadata = '';
            var operates = {
                'click .detail' : function (e, value, row, index) {
                    var arr = [];
                    arr.push(row);
                    getMedaDetails(arr,0);
                }
            };
            var options = {
                pagination: true,
                pageList: [5, 10, 25, 50],
                sidePagination: "client", //分页方式，client和server
                maintainMetaData: true,
                data: d.data,
                columns: [{
                    checkbox: true,
                    sortable: false,
                },
                    {
                        field: 'name',
                        title: LANG.UI_COPY_TYPE,
                        sortable: false,
                        align: 'center',
                        formatter: function (value,data,row) {
                            return formatterData(value,data,row);
                        }
                    },
                    {
                        field: 'contact_name',
                        title: LANG.UI_MICROSOFT365_NAME,
                        sortable: false,
                        align: 'center',
                        formatter: function (value,data,row) {
                            return formatterData(value,data,row);
                        }
                    },
                    {
                        field: 'send',
                        title: LANG.UI_MICROSOFT365_FROM,
                        sortable: false,
                        align: 'center',
                        formatter: function (value,data,row) {
                            return formatterData(value,data,row);
                        }
                    },
                    {
                        field: 'receive',
                        title: LANG.UI_MICROSOFT365_RECIPIENT,
                        sortable: false,
                        align: 'center',
                        formatter: function (value,data,row) {
                            return formatterData(value,data,row);
                        }
                    },
                    {
                        field: 'subject',
                        title: LANG.UI_MICROSOFT365_THEME,
                        sortable: false,
                        align: 'center',
                        formatter: function (value,data,row) {
                            return formatterData(value,data,row);
                        }
                    },
                    {
                        field: 'recv_date',
                        title: LANG.UI_MICROSOFT365_RECEIVE_TIME,
                        sortable: false,
                        align: 'center',
                        formatter: function (value,data,row) {
                            return formatterData(value,data,row);
                        }
                    },
                    {
                        field: 'operate',
                        title: LANG.UI_PUBLIC_OPERATION,
                        sortable: false,
                        opButton: true,
                        align: 'center',
                        events: operates,
                        formatter: function (value,data,row) {
                            return value;
                        }
                    },],
                onPostBody: function () {
                    //高级搜索
                    //获取bootstrap的加载完毕的数据
                    var row = $('#searchTable').bootstrapTable('getData');
                    for (var i = 0; i < row.length; i++) {
                        if (row[i] != undefined && row[i].more && !cache_finish_flag_advanced) {
                            currentNum = parseInt(row.length - 1);
                            $('#searchTable').bootstrapTable('remove', {field: '$index', values: [i]})//在表格中移除php返回的加载更多那一行
                            $('.loadMore-div-search').show();
                            $('#exchange-recover-content .addIt-list').css({"height":"calc(100% - 81px)","border-bottom":"none"});
                            $('input[name="page_num_search"]').on('ifClicked', function () {
                                if ($(this).val() == 0) {
                                    $('.seachDiv .diy-label').hide();
                                    $('.seachDiv .diy-input').show(); // 显示自定义输入框
                                } else {
                                    $('.seachDiv .diy-label').show();
                                    $('.seachDiv .diy-input').hide(); // 隐藏自定义输入框
                                }
                            });
                            $('.loadMoreSearchClick').off().on('click',function () {
                                var icheckArr = $('.loadMore-div-search').find('.icheck');
                                for (var j = 0; j < icheckArr.length; j++) {
                                    if ($(icheckArr[j]).is(':checked')) {
                                        if ($(icheckArr[j]).val() == 0) {
                                            page_size_metadata = $.trim($('.seachDiv .diy-input').val());
                                            if (page_size_metadata == '') {
                                                UIToastr.showWarning(LANG.UI_MICROSOFT365_LOAD_MORE_DATA,LANG.UI_MICROSOFT365_INPUT_LOAD_ITEM);
                                                return;
                                            }
                                        } else {
                                            page_size_metadata = $(icheckArr[j]).val();
                                        }
                                    }
                                }
                                var lastSearch = row[i - 2].last_search_info;
                                if (firstSearchAdvanced) {
                                    lastSearch = {id:0, user_uuid: "", index_container_id: 0, table_id: 0, type: 0};
                                }
                                var params = {
                                    node_uuid:row[i - 2].node_uuid,
                                    timepoint_uuid:row[i - 2].timepoint_uuid,
                                    organization_uuid:row[i - 2].organization_uuid,
                                    exch_advanced_search_condition : {
                                        user_uuid: data.search_user_uuid,
                                        category: $('#category').val(),//类别
                                        priv_type: $('#folder').val(),//文件夹固定编号
                                        field: $('#position').val(),//位置
                                        condition: $('#condition').val(),//匹配条件
                                        is_attachment: $('#attachment').val(),
                                        keyword: $.trim($('#keywords').val())//搜索条件
                                    },
                                    last_search_info :lastSearch,
                                    next_start: row[i - 2].next_start ? row[i - 2].next_start : 0,
                                    page_size: page_size_metadata,//加载更多请求条数
                                    current_num:currentNum
                                };
                                params = deepCloneObject(params);
                                Metronic.blockUI({target: '.seachDiv',animate: true});
                                pAjaxRequest(params, "/api/v1/exchange/restore/advanced_search", "GET", function (result) {
                                    Metronic.unblockUI('.seachDiv');
                                    if (result.success) {
                                        //success
                                        $('#searchTable').bootstrapTable('append', result.data.rows)
                                    } else {
                                        operateResponseList(result, LANG.UI_MICROSOFT365_GET_METADATA);
                                    }
                                });

                            });
                        } else {
                            if(row[i].finish_flag == 2 && row.length == i+1 && !cache_finish_flag_advanced) {
                                $('.loadMore-div-search').show();
                                $('#exchange-recover-content .addIt-list').css({"height":"calc(100% - 81px)","border-bottom":"none"});
                                return;
                            }  else if (row[i].finish_flag == 3) {
                                cache_finish_flag_advanced = true;
                                $('#searchTable').bootstrapTable('remove', {field: '$index', values: [i]})//在表格中移除php返回的数组为空时自定义的那一行
                            }
                            $('.loadMore-div-search').hide();
                            $('.seachDiv .addIt-list').css({"height":"calc(100% - 34px)","border-bottom":"1px solid #E6E6E6"});
                        }
                    }
                },
                onCheck: function (rowdata) {
                    // 单个选中行，加入统计数量的购物车
                    addToTotal('#searchTable tbody .selected',1,rowdata,false);
                    // 第四步显示
                    $('.recoverpoint').html(rowdata.name);
                    $('.seachDiv .squreBtn').css({"background-color":"#0FBF98","color":"white"});
                },
                onUncheck: function (rowdata) {
                    //取消单个选中
                    cancleToTotal(1,rowdata,false);
                    //表格没有选中的行  置灰
                    var checkedRows = $('#searchTable').bootstrapTable('getSelections');
                    if (checkedRows == 0) {
                        $('.seachDiv .squreBtn').css({"background-color":"#F0F0F0","color":"#D3D3D3"});
                    }
                },
                onCheckAll: function (a,b,c) {
                    //全选
                    var currentArr = a;
                    if (a.length > b.length && b.length != 0) {
                        currentArr = a.filter(item => !b.includes(item));
                    }
                    var rows = $('#searchTable').bootstrapTable('getSelections').length;
                    addToTotal('#searchTable tbody',rows,currentArr,true)
                    $('.seachDiv .squreBtn').css({"background-color":"#0FBF98","color":"white"});
                },
                onUncheckAll: function (a,b,c) {
                    // 取消全选
                    var currentArr = b;
                    if (b.length > a.length && a.length != 0) {
                        currentArr = b.filter(item => !a.includes(item));
                    }
                    var rows = $('#searchTable tbody tr').length;
                    cancleToTotal(rows,currentArr,true);
                    $('.seachDiv .squreBtn').css({"background-color":"#F0F0F0","color":"#D3D3D3"});
                },
            }
            if (d.success) {
                $('#searchTable').bootstrapTable('destroy');
                $('#searchTable').baseTableConfig().init(options);
                $('#advanceSearch').modal('hide');
            }
        }, false);
    }
    //高级搜索
    var toAdvanceSearch = function () {
        $('.titleDes').html(data.organization_name + " " + data.recovery_timepoint_name);
        $('#advanceSearch').modal({'width': '760px'});
    }
    //已选择恢复源表格
    var totalDatatable = function () {
        $('.total-table').show();
        var operates = {
            'click .detail' : function (e, value, row, index) {
                var arr = [];
                arr.push(row);
                getMedaDetails(arr,0);
            }
        };
        var options = {
            pagination: true,
            pageList: [5, 10, 25, 50, 100,1000],
            data: totalDataList,
            sidePagination: "client", //分页方式，client和server
            maintainMetaData: true,
            columns: [{
                checkbox: true,
                sortable: false,
            },
                {
                    field: 'name',
                    title: LANG.UI_COPY_TYPE,
                    sortable: true,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'display_name',
                    title: LANG.UI_MICROSOFT365_NAME,
                    sortable: true,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'importance',
                    title: LANG.UI_MICROSOFT365_IMPORTANCE,
                    sortable: true,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'send',
                    title: LANG.UI_MICROSOFT365_FROM,
                    sortable: true,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'receive',
                    title: LANG.UI_MICROSOFT365_RECIPIENT,
                    sortable: true,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'subject',
                    title: LANG.UI_MICROSOFT365_THEME,
                    sortable: true,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'recv_date',
                    title: LANG.UI_MICROSOFT365_SEND_TIME,
                    sortable: true,
                    align: 'center',
                    formatter: function (value,data,row) {
                        return formatterData(value,data,row);
                    }
                },
                {
                    field: 'operate',
                    title: LANG.UI_PUBLIC_OPERATION,
                    sortable: false,
                    align: 'center',
                    events: operates,
                    opButton: true,
                    formatter: function (value,data,row) {
                        return value;
                    }
                },],
            onCheck: function () {
                modifyDelStyle();
            },
            onUncheck: function () {
                modifyDelStyle();
            },
            onCheckAll: function () {
                modifyDelStyle();
            },
            onUncheckAll: function () {
                modifyDelStyle();
            },
        }
        $('#totalTable').bootstrapTable('destroy');
        $('#totalTable').baseTableConfig().init(options);
        $('#totalTable').bootstrapTable('load', {"total":totalDataList.length});
        $('#totalTable').bootstrapTable('uncheckAll');
        var tabledes = '';
        if (totalDataList != 0) {
            var organization = timepointTree.getNodesByParam("id", totalDataList[0].organization_uuid, null);
            var timepoint = timepointTree.getNodesByParam("id", totalDataList[0].timepoint_uuid, null);
            if(organization.length != 0) {
                tabledes += organization[0].name;
            }
            if(timepoint.length != 0) {
                tabledes += timepoint[0].name;
            }
        }
        $('.tabledes').html(tabledes);
        Metronic.unblockUI('#drawer-1');
    }

    var modifyDelStyle = function () {
        var selectedRow = $('#totalTable').bootstrapTable('getSelections');
        if (selectedRow.length < 1) {
            $('#delete-shopping').addClass('exch-forbid-event');
            $('.del-parent-div').css({"cursor": "not-allowed"});
        } else {
            $('#delete-shopping').removeClass('exch-forbid-event');
            $('.del-parent-div').css({"cursor": "pointer"});
        }
    }

    var initPointTree = function () {
        var params = {};
        params.storage_uuid = $('#storageSelect').val() ? $('#storageSelect').val() : '';
        params.data_flag = 2;
        Metronic.blockUI({target:'#echangetimepointtree',animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data", "GET", setPointTree, true);
    };
    var setPointTree = function (result) {
        Metronic.unblockUI('#echangetimepointtree');
        if (!result.success) {
            $("#nopointtips").show();
            $(".exchange_tree").hide();
            return;
        } else {
            $("#nopointtips").hide();
            $(".exchange_tree").show();
        }
        var setting = {
            check: {
                enable: true,
            },
            data: {
                simpleData: {
                    enable: true
                }
            },
            callback: {
                beforeClick: nodeClick,
                beforeExpand: nodeExpand,
                onCheck: nodeOnCheck,
            },
            view: {
                showTitle: true,
                nameIsHTML: true,
                fontCss: function(treeId, treeNode) {
                    let css = {};
		            if (treeNode.point_status == 1) {
		            	css = { color: "#F19F00 " };
		            }
		            if (treeNode.point_status == 3) {
		            	css = { color: "#F1416C " };
		            }
		            return css;
                },
                addHoverDom: addPointTreeHoverDom,
				removeHoverDom: removePointTreeHoverDom,
            },
            data: {
                simpleData: {
                    enable: true
                },
                key:{
                    title: "title"
                },
            },
        };
        timepointTree = $.fn.zTree.init($("#exchange_point_tree"), setting, result.data);
        // 从备份数据跳转恢复页面，展开对象下的时间点
		let targetNode = timepointTree.getNodeByParam('id', externalItemUuid + '_' + externalTaskUuid);
		//有目标节点
		if(targetNode){
			// 异步获取时间点
            nodeExpand('exchange_point_tree',targetNode);
		}
    };

    /**
     * 添加时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
	  const addPointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.level !== 2) {//不是时间点
            return;
        }
        if ($(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).length) {
            return;
        }
        let sObj = $(`#${treeNode.tId}_span`);
        sObj.after(`<span id="${treeNode.tId}_${treeNode.timepoint_uuid}"><i class='viconfont vicon-Frame11'></i></span>`);
        // 注册点击事件
        $(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).on("click", (ev) => {
            ev.stopPropagation();  // 阻止click事件向上冒泡
            $('.page-content').initPointDetailDrawer({timepoint_uuid: treeNode.timepoint_uuid});
        });
    };

    /**
     * 移除时间点树的hover dom
     * @param treeId
     * @param treeNode
     */
    const removePointTreeHoverDom = (treeId, treeNode) => {
        if (treeNode.level !== 2) {//不是时间点
            return;
        }
        $(`#${treeNode.tId}_${treeNode.timepoint_uuid}`).off().remove();
    };

    //选择时间点节点事件绑定
    var nodeClick = function (treeId, treeNode, clickFlag) {
        timepointTree.expandNode(treeNode, true);
        nodeOnCheck(event, treeId, treeNode);
    }


    var nodeOnCheck = function (e, treeId, treeNode) {
        nodeExpand(treeId, treeNode);
    }

    //节点展开调用不同的接口
    var nodeExpand = function (treeId, treeNode) {
        storage_type = treeNode.storage_type;
        auth_rowdata = '';
        if (treeNode.dir_type == 1) {
            return;
        }
        if (treeNode.level == 1) {
            $(".exchange-div").show();
            $(".step1tips").hide();
        } else {
            $(".exchange-div").hide();
            $(".step1tips").show();
        }
        //数据加密开  自动生成密码关
        data.recover_info.password = '';
        if (treeNode.encrypted_flag && !treeNode.password_auto_flag) {
            if ($.inArray(treeNode.timepoint_uuid,allpointlist) == -1) {//判断是否点击过
                bootbox.prompt({
                    title: LANG.UI_VM_INPUT_DB_ENCRY_PWD,
                    inputType: 'password',
                    callback: debounce(function (result) {
                        if (result == null) {
                            return true;
                        }
                        if (result == '') {
                            UIToastr.showWarning(LANG.UI_MICROSOFT365_DATA_ENCRYPT,LANG.UI_MICROSOFT365_INPUT_PASSWORD_TIPS);
                            return false;
                        }
                        //同步ajax校验密码
                        var checkflag = true;
                        var params = {};
                        params.timepoint_uuid = treeNode.timepoint_uuid;
                        params.passwd =  signEncrypt($.trim(result));
                        data.recover_info.password = btoa($.trim(result));
                        pAjaxRequest(params, "/api/v1/files/check_encrypt", "POST", function (d) {
                            if (d.success) {
                                $(".exchange-div").show();
                                $(".step1tips").hide();
                                allpointlist = getAllPoints(treeNode);
                                //身份验证
                                toAuthentication(treeNode);
                            } else {
                                checkflag = false
                                operateResponseList(d, LANG.UI_MICROSOFT365_DATA_ENCRYPT);
                            }
                        }, false);
                        if (!checkflag) {
                            return false;
                        } else {
                            return true;
                        }
                    }, 300),
                });
            } else {//加密，但是在一条链上
                data.recover_info.password = '';
                $(".exchange-div").show();
                $(".step1tips").hide();
                toAuthentication(treeNode);
            }
        } else {//未加密
            data.recover_info.password = '';
            $(".exchange-div").show();
            $(".step1tips").hide();
            toAuthentication(treeNode);
        }
    }

    //时间点身份验证
    var toAuthentication = function (treeNode) {
        // 存储离线，直接返回
        if(treeNode.chkDisabled) {
            return;
        }
        if (treeNode.level != 2 && auth_rowdata == '') {
            getDataByType(treeNode);
            return;
        }
        // 从 session 中获取存储的字段 'exchange_recovery_pass'
        var value = sessionStorage.getItem('exchange_recovery_pass');
        if (value && value.includes(treeNode.organization_uuid) && auth_rowdata == '') {//已经进行过身份验证（树）
            getDataByType(treeNode);
            return;
        } else if (value && value.includes(treeNode.organization_uuid) && auth_rowdata != '') {//表格的
            addToTotal('.point-table #pointTable tbody .selected',1,auth_rowdata,false);
            return;
        }
        region = parseInt(treeNode.getParentNode().getParentNode().region);
        $('#authModal').modal({'width': '800px', 'height': '100%'});
        if (region == 100) {//server
            $('#managerName').val("").css({"border-color":"#E6E6E6"});
            $('#managerPwd').val("").css({"border-color":"#E6E6E6"});
            $('#authForm .server').show();
            $('#authForm .online').hide();
            $('.authSubmit').show();
        } else {//online
            $('#authForm .online').show();
            $('#authForm .server').hide();
            $('.authSubmit').hide();
            auth_node = treeNode;
            setTimeout(getVertifyCode(),50);
        }
        $('.authSubmit').off().on('click',function () {
            var p = {};
            p.app_username = $.trim($('#managerName').val());
            p.app_password = signEncrypt(btoa($.trim($('#managerPwd').val())));
            if (p.app_username == "") {
                $('#managerName').css({"border-color":"red"});
                UIToastr.showWarning(LANG.UI_MICROSOFT365_AUTH,LANG.UI_MICROSOFT365_INPUT_ADMINISTRATOR_ACCOUNT);
                return false;
            } else {
                $('#managerName').css({"border-color":"#E6E6E6"});
            }
            if (p.app_password == "") {
                $('#managerPwd').css({"border-color":"red"});
                UIToastr.showWarning(LANG.UI_MICROSOFT365_AUTH,LANG.UI_MICROSOFT365_INPUT_ADMINISTRATOR_PASSWORD);
                return false;
            } else {
                $('#managerPwd').css({"border-color":"#E6E6E6"});
            }
            p.timepoint_uuid = treeNode.timepoint_uuid;
            pAjaxRequest(p, "/api/v1/exchange/restore_auth", "GET", function (result) {
                if (!result.data.state) {
                    UIToastr.showWarning(LANG.UI_MICROSOFT365_AUTH,result.data.des);
                    if (auth_rowdata != '') {
                        $('.point-table #pointTable').bootstrapTable('uncheck', 1)
                    }
                    return;
                }
                // 将输入过密码的时间点存入session
                var str_time = sessionStorage.getItem('exchange_recovery_pass');
                str_time += treeNode.organization_uuid;
                sessionStorage.setItem('exchange_recovery_pass', str_time);
                $('#authModal').modal('hide');
                if (auth_rowdata == '') {
                    getDataByType(treeNode);
                } else {
                    addToTotal('.point-table #pointTable tbody .selected',1,auth_rowdata,false);
                }
            }, true);
        });
    }

    //请求用于身份验证opid和验证码
    var getVertifyCode = function () {
        Metronic.blockUI({target: '#authModal',animate: true, cenrerY: true,});
        pAjaxRequest({'region':region,'op_id_flag':true }, "/api/v1/office365/organization/auth_code", "GET", function (result) {
            //获取到opid
            if (result.data.op_status == 0) {
                op_id = result.data.op_id;
                pAjaxRequest({'region':region,'vertify_code_flag':true,'op_id':op_id }, "/api/v1/office365/organization/auth_code", "GET", function (result) {
                    Metronic.unblockUI('#authModal');
                    //获取验证码
                    if (result.data.op_status == 1) {
                        $('#vertifyCode').val(result.data.vertify_code);
                    } else {
                        UIToastr.showWarning(LANG.UI_MICROSOFT365_AUTH_FAIL);
                    }
                });
            }
        }, false);
    }

    //进行身份验证
    var getAuthInfo = function () {
        pAjaxRequest({'region':region,'auth_flag':true,'op_id':op_id }, "/api/v1/office365/organization/auth_code", "GET", function (result) {
            //进行身份验证
            if (result.data.op_status == 2) {
                pAjaxRequest({'tenant_uuid':result.data.tenant_uuid,'username':result.data.user,'timepoint_uuid':auth_node.timepoint_uuid }, "/api/v1/exchange/restore_auth", "GET", function (d) {
                    if (!d.data.state) {
                        UIToastr.showWarning(LANG.UI_MICROSOFT365_AUTH,d.data.des);
                        if (auth_rowdata != '') {
                            $('.point-table #pointTable').bootstrapTable('uncheck', 1)
                        }
                        return;
                    }
                    // 将输入过密码的时间点存入session
                    var str_time = sessionStorage.getItem('exchange_recovery_pass');
                    str_time += auth_node.organization_uuid;
                    sessionStorage.setItem('exchange_recovery_pass', str_time);
                    $('#authModal').modal('hide');
                    if (auth_rowdata == '') {
                        getDataByType(auth_node);
                    } else {
                        addToTotal('.point-table #pointTable tbody .selected',1,auth_rowdata,false);
                    }
                });
            } else if (result.data.op_status == 3) {
                //失败
                $('.vertify-outtime').show();
                $('.vertify-waiting').hide();
                $('.vertify-success').hide();
                UIToastr.showWarning(LANG.UI_MICROSOFT365_AUTH, result.message);
            } else if (result.data.op_status == 1) {
                //请求中
                $('.vertify-waiting').show();
                $('.vertify-outtime').hide();
                setTimeout(function () {
                    getAuthInfo();
                },2000)
            }

        });
    }

    //得到整条链
    var getAllPoints = function (treeNode) {
        var p_timepoint_uuid = '';
        if (treeNode.level == 2) {
            var treeNodeArr = treeNode.getParentNode().children;
            for (var i = 0; i < treeNodeArr.length; i++) {
                if (treeNode.mode == 1) {//完备
                    p_timepoint_uuid = treeNode.timepoint_uuid;
                } else if (treeNode.mode == 2) {//增备
                    p_timepoint_uuid = treeNode.depend_uuid;
                }
                if (treeNodeArr[i].depend_uuid == p_timepoint_uuid || treeNodeArr[i].timepoint_uuid == p_timepoint_uuid) {
                    allpointlist.push(treeNodeArr[i].timepoint_uuid);
                }
            }
        }
        //去重
        allpointlist = [...new Set(allpointlist)];
        return allpointlist;
    }

    //根据dir_type请求不同类型的数据--除了时间点数据
    var getDataByType = function (treeNode) {
        $('.exchange-div .table-div').hide();
        $('.seachDiv').hide();
        $('.batch-export-div').hide();
        $('.loadMore-div').hide();
        loadMoreShow = false;
        firstSearchAdvanced = true;
        firstSearch = true;
        $('.loadMore-div-search').hide();
        $('#exchange-recover-content .addIt-list').css({"height":"calc(100% - 15px)","border-bottom":"1px solid #E6E6E6"});
        switch (treeNode.dir_type) {
            case 2:
                $('.point-table').show();
                getPoints(treeNode);// 获取时间点
                break;
            case 3:
                $('.user-table').show();
                getUsers(treeNode);//得到用户和用户组
                break;
            case 4:
                $('.rootdir-table').show();
                getRootDir(treeNode);//得到顶级目录
                break;
            case 5:
            case 6:
                getChildDir(treeNode);//得到子目录
                break;
            case 7:
                getMetadataDetail(treeNode);//得到目录下的元数据详细信息
                break;
            case 'more':
                $('.user-table').show();
                getMoreUser(treeNode);//加载更多用户
                break;
        }
    }

    // 获取时间点
    var getPoints = function (treeNode) {
        let storage_uuid = $('#storageSelect').val();
        var params = {
            job_uuid:treeNode.job_uuid,
            storage_uuid:storage_uuid,
            organization_uuid:treeNode.pId,
            data_flag:2,
        };
        $('.topToolDiy').hide();
        Metronic.blockUI({target: ".exchange_tree",animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data/restore_points", "GET", function (result) {
            if (result.success) {
                //success
                initPointTable(params);//加载时间点表格
                Metronic.unblockUI(".exchange_tree");
                timepointTree.removeChildNodes(treeNode);
                timepointTree.addNodes(treeNode, result.data.rows, true);
                timepointTree.expandNode(treeNode, true);
                // 源组织uuid
                data.organization_uuid = result.data.rows[0].organization_uuid;
                data.organization_name = result.data.rows[0].organization_name;
                data.node_uuid = result.data.rows[0].node_uuid;
                data.integrity_check_flag = result.data.rows[0].integrity_check_flag;
                $('.totalDataDiv').show();
                //第四步显示
                $('.recovertask').html(result.data.rows[0].job_name);
                $('.srcdata').html(result.data.rows[0].organization_name);
                src_organization = result.data.rows[0].organization_name;//源组织
            } else {
                OPREL(data);
            }
        });
    }

    //把选中单个行加入统计数量的购物车
    var addToTotal = function (divdes,num,rowdata,allFlag) {
        // 获取被点击div的位置
        var div =  $(divdes).find('td').eq(1);
        var divTop = div.offset().top;
        var divLeft = div.offset().left;
        var totalNum = $(".totalNum");
        // 创建一个新的div副本，并将其添加到页面中,副本为一个图标，解决副本是所有已勾选数据时界面卡顿问题
        var divClone = $('<div class="viconfont vicon-Frame-31"></div>').clone().appendTo("body");
        divClone.css({
            "position": "absolute",
            "top": divTop,
            "left": divLeft,
            "z-index": 10000
        });
        // 计算统计数量的购物车位置
        var totalDataDiv = $(".totalDataDiv");
        var totalDataDivTop = totalDataDiv.offset().top;
        var totalDataDivLeft = totalDataDiv.offset().left;
        // 创建动画效果
        divClone.animate({
            "top": totalDataDivTop,
            "left": totalDataDivLeft,
            "width": 56,
            "height": 56,
            "opacity": 0.2
        }, 300, function () {
            // 移除新的div，并在购物车中增加数量
            divClone.remove();
            var m = checkData(rowdata, totalDataList);//检查数据中有没有不是一个时间点下的数据

            //将选中数据放入exch_recovery_object_info_list
            if (allFlag) {
                //判断是否是同一个时间点的数据
                if (totalDataList.length != 0 && rowdata[0].timepoint_uuid != totalDataList[0].timepoint_uuid) {
                    totalDataList = [];
                }
                for (var i = 0; i < rowdata.length; i++) {
                    //判断购物车中是否存在这条数据
                    var objectExists = totalDataList.some(function (obj) {
                        return obj.id === rowdata[i].id;
                    });
                    if (!objectExists) {
                        totalDataList.push(rowdata[i]);
                    }
                }
            } else {
                //判断是否是同一个时间点的数据
                if (totalDataList.length != 0 && rowdata.timepoint_uuid != totalDataList[0].timepoint_uuid) {
                    totalDataList = [];
                }
                var objectExists = totalDataList.some(function (obj) {
                    return obj.id === rowdata.id;
                });
                if (!objectExists) {
                    totalDataList.push(rowdata);
                }
            }
            data.exch_recovery_object_info_list = totalDataList;
            totalNum.text(totalDataList.length);
        });
    }

    //取消选中单个
    var cancleToTotal = function (num,rowdata,allFlag) {
        var totalNum = $(".totalNum");
        //将取消选中数据从exch_recovery_object_info_list中删除
        if (allFlag) {
            let rowdataIds = new Set(rowdata.map(item => item.id));
            // 使用filter方法过滤出totalDataList中id不在rowdataIds中的项
            totalDataList = totalDataList.filter(item => !rowdataIds.has(item.id));
        } else {
            totalDataList = totalDataList.filter(item => item.id !== rowdata.id);
        }
        data.exch_recovery_object_info_list = totalDataList;
        totalNum.text(totalDataList.length);
    }

    var checkData = function (rowdata, allData) {
        var num = 0;
        if (allData.length == 0) {
            return 0;
        }
        //新加入的是时间点
        if (rowdata.type == 10000) {
            for (var i = 0; i < allData.length; i++) {
                if (allData[i].type == 10000) {
                    //删除num
                    num = -1;
                    //从totalDataList找到时间点，并删除，因为时间点不可以重复
                    $.each(totalDataList, function (index) {
                        if ($.inArray(this, totalDataList) !== -1 && this === allData[i]) {
                            totalDataList.splice(index, 1);
                            return false; // 结束循环
                        }
                    });
                }
            }
        }
        return num;
    }

    //初始化时间点表格
    var initPointTable = function (params) {
        var options = {
            pagination:true,
            pageList: [5,10,25,50],
            vin_params:function () {
                params.table_flag = 1;
                return params;
            },
            singleSelect: true,//表格单选
            vin_url:"/api/v1/exchange/restore_data/restore_points",
            vin_method:"GET",
            columns:[{
                checkbox:true,
                sortable:false,
                formatter: function (value, row, index, field) {
                    if (row.chkDisabled === true) {
                        return {
                            disabled: true
                        };
                    }
                }
            },
                {
                    field: 'time_point',
                    title: LANG.UI_MICROSOFT365_TIME_POINT,
                    sortable: true,
                    align: 'center',
                },
                {
                    field: 'time_des',
                    title: LANG.UI_COPY_TYPE,
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
                    field: 'storage_name',
                    title: LANG.UI_COPY_DATA_STORAGE,
                    sortable: false,
                    align: 'center',
                },],
            onCheck: function (rowdata) {
                //时间点手动加密
                auth_rowdata = rowdata;
                var checkflag = true;
                if (rowdata.encrypted_flag && !rowdata.password_auto_flag) {
                    if ($.inArray(rowdata.timepoint_uuid,allpointlist) == -1) {//判断是否点击过
                        bootbox.prompt({
                            title: LANG.UI_VM_INPUT_DB_ENCRY_PWD,
                            inputType: 'password',
                            callback: debounce(function (result) {
                                if (result == null) {
                                    $('.point-table #pointTable').bootstrapTable('uncheck', 1)
                                    return true;
                                }
                                if (result == '') {
                                    UIToastr.showWarning(LANG.UI_MICROSOFT365_DATA_ENCRYPT, LANG.UI_MICROSOFT365_INPUT_PASSWORD_TIPS);
                                    $('.point-table #pointTable').bootstrapTable('uncheck', 1)
                                    return false;
                                }
                                //同步ajax校验密码
                                var params = {};
                                params.timepoint_uuid = rowdata.timepoint_uuid;
                                params.passwd = signEncrypt($.trim(result));
                                data.recover_info.password = btoa($.trim(result));
                                pAjaxRequest(params, "/api/v1/files/check_encrypt", "POST", function (d) {
                                    if (d.success) {//密码正确
                                        allpointlist = getAllPoints(timepointTree.getNodeByParam("id", rowdata.timepoint_uuid, null));
                                        //时间点
                                        data.recovery_timepoint_uuid = rowdata.timepoint_uuid;
                                        data.recovery_timepoint_name = rowdata.name;
                                        storage_type = rowdata.storage_type;
                                        // 第四步显示
                                        $('.recoverpoint').html(rowdata.name);
                                        checkflag = true;
                                        toAuthentication(timepointTree.getNodeByParam("id", rowdata.timepoint_uuid, null));
                                    } else {
                                        operateResponseList(d, LANG.UI_MICROSOFT365_DATA_ENCRYPT);
                                        checkflag = false;
                                    }
                                }, false);
                                if (!checkflag) {
                                    return false;
                                } else {
                                    return true;
                                }
                            }, 300),
                        });
                    } else {//已经输过密码了
                        //时间点
                        data.recovery_timepoint_uuid = rowdata.timepoint_uuid;
                        data.recovery_timepoint_name = rowdata.name;
                        storage_type = rowdata.storage_type;
                        // 第四步显示
                        $('.recoverpoint').html(rowdata.name);
                        toAuthentication(timepointTree.getNodeByParam("id", rowdata.timepoint_uuid, null));
                    }
                } else {//未加密
                    //时间点
                    data.recovery_timepoint_uuid = rowdata.timepoint_uuid;
                    data.recovery_timepoint_name = rowdata.name;
                    storage_type = rowdata.storage_type;
                    // 第四步显示
                    $('.recoverpoint').html(rowdata.name);
                    toAuthentication(timepointTree.getNodeByParam("id", rowdata.timepoint_uuid, null));
                }

            },
            onUncheck: function (rowdata) {
                //取消单个选中
                cancleToTotal(1,rowdata,false);
            },
            onPostBody: function () {
                let targetNode = timepointTree.getNodeByParam('id', externalPointUuid);
                if (targetNode && !firstFlag) {
                     $('.point-table #pointTable').bootstrapTable('checkBy', {
                        field: 'id',
                        values: [externalPointUuid]
                    });
                    setTimeout(function(){
                        firstFlag = true; 
                    },50)
                }
            }
        }
        $('.point-table #pointTable').bootstrapTable('destroy');
        $('.point-table #pointTable').baseTableConfig().init(options);
        // $('.point-table #pointTable').bootstrapTable('load', {"total":data.length});
    }

    //得到恢复数据的用户和用户组
    var getUsers = function (treeNode) {
        var nodeuuid = treeNode.node_uuid;
        var params = {
            timepoint_uuid:treeNode.timepoint_uuid,
            node_uuid:nodeuuid,
            organization_uuid:treeNode.organization_uuid,
            next_start: 0,//偏移
            page_size: more_page_size,//要读取多少个用户
            current_num: 0,//总共加载了多少个用户
            table_flag: false,
            storage_type: treeNode.storage_type,
        };
        data.normal_search.type = 0;
        data.normal_search.folder_id = '';
        data.search_user_uuid = '';
        Metronic.blockUI({target: ".exchange_tree",animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data/restore_points/" + treeNode.timepoint_uuid + "/users", "GET", function (result) {
            if (result.success) {
                //success
                $('.topToolDiy').show();
                initUserTable(params,treeNode.timepoint_uuid);//加载用户和用户组表格
                timepointTree.removeChildNodes(treeNode);
                timepointTree.addNodes(treeNode, result.data.rows, true);
                timepointTree.expandNode(treeNode, true);
                //时间点
                data.recovery_timepoint_uuid = treeNode.timepoint_uuid;
                data.recovery_timepoint_name = treeNode.name;
                data.node_uuid = treeNode.node_uuid;
                data.integrity_check_flag = treeNode.integrity_check_flag;
                // 第四步显示
                $('.recoverpoint').html(treeNode.name);
            } else {
                operateResponseList(result, LANG.UI_MICROSOFT365_GET_USER_LIST)
            }
            Metronic.unblockUI(".exchange_tree");
        });
    }

    const getMoreUser = function (treeNode) {
        var params = {
            timepoint_uuid:treeNode.timepoint_uuid,
            node_uuid:treeNode.node_uuid,
            organization_uuid:treeNode.organization_uuid,
            next_start: treeNode.next_start,//偏移
            page_size: more_page_size,//要读取多少个用户
            current_num: treeNode.current_num,//总共加载了多少个用户
            table_flag: false,
            storage_type: treeNode.storage_type,
        };
        data.normal_search.type = 0;
        data.normal_search.folder_id = '';
        data.search_user_uuid = '';
        Metronic.blockUI({target: ".exchange_tree",animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data/restore_points/" + treeNode.timepoint_uuid + "/users", "GET", function (result) {
            if (result.success) {
                //success
                $('.topToolDiy').show();
                // initUserTable(params,treeNode.timepoint_uuid);//加载用户和用户组表格
                timepointTree.removeNode(treeNode);
                timepointTree.addNodes(treeNode.getParentNode(), result.data.rows, true);
                timepointTree.expandNode(treeNode, true);
                //时间点
                data.recovery_timepoint_uuid = treeNode.timepoint_uuid;
                // data.recovery_timepoint_name = treeNode.name;
                data.node_uuid = treeNode.node_uuid;
                // 第四步显示
                // $('.recoverpoint').html(treeNode.name);
            } else {
                operateResponseList(result, LANG.UI_MICROSOFT365_GET_USER_LIST)
            }
            Metronic.unblockUI(".exchange_tree");
        });
    }

    //初始化用户和用户组表格
    var initUserTable = function (params,timepoint_uuid) {
        var options = {
            pagination:true,
            pageList: [5,10,25,50],
            sidePagination: "client", //分页方式，client和server
            vin_params:function () {
                params.table_flag = true;
                params.next_start = 0;//偏移
                params.page_size = 2147483647;//要读取多少个用户,这里直接取int的最大范围
                return params;
            },
            vin_url:'/api/v1/exchange/restore_data/restore_points/' + timepoint_uuid + "/users",
            vin_method:"GET",
            columns:[{
                checkbox:true,
                sortable:false
            },
                {
                    field: 'name',
                    title: LANG.UI_MICROSOFT365_USER,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'display_name',
                    title: LANG.UI_MICROSOFT365_NAME_NEW,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'email',
                    title: LANG.UI_MICROSOFT365_EMAIL,
                    sortable: false,
                    align: 'center',
                },
            ],
            onCheck: function (rowdata) {
                // 单个选中行，加入统计数量的购物车
                addToTotal('.user-table #userTable tbody .selected',1,rowdata,false);
            },
            onUncheck: function (rowdata) {
                //取消单个选中
                cancleToTotal(1,rowdata,false);
            },
            onCheckAll: function (a,b,c) {
                //全选
                var currentArr = a;
                if (a.length > b.length && b.length != 0) {
                    currentArr = a.filter(item => !b.includes(item));
                }
                var rows = $('.user-table #userTable').bootstrapTable('getSelections').length;
                addToTotal('.user-table #userTable tbody',rows,currentArr,true)
            },
            onUncheckAll: function (a,b,c) {
                // 取消全选
                var currentArr = b;
                if (b.length > a.length && a.length != 0) {
                    currentArr = b.filter(item => !a.includes(item));
                }
                var rows = $('.user-table #userTable tbody tr').length;
                cancleToTotal(rows,currentArr,true);
            },
        }
        $('.user-table #userTable').bootstrapTable('destroy');
        $('.user-table #userTable').baseTableConfig().init(options);
    }

    //得到顶级目录
    var getRootDir = function (treeNode) {
        var nodeuuid = treeNode.node_uuid;
        var params = {
            timepoint_uuid:treeNode.timepoint_uuid,
            node_uuid:nodeuuid,
            organization_uuid:treeNode.organization_uuid,
            user_uuid: treeNode.uuid,
            index_container_id: treeNode.index_container_id,
            table_id: treeNode.table_id,
            dir_type: treeNode.dir_type,
            storage_type: treeNode.storage_type,
        };
        data.normal_search.type = 0;
        data.normal_search.folder_id = '';
        data.search_user_uuid = treeNode.uuid;
        Metronic.blockUI({target: ".exchange_tree",animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data/restore_points/" + treeNode.timepoint_uuid + "/users/" + treeNode.uuid, "GET", function (result) {
            if (result.success) {
                //success
                initRootDirTable(params,treeNode.timepoint_uuid,treeNode.uuid);
                timepointTree.removeChildNodes(treeNode);
                timepointTree.addNodes(treeNode, result.data.rows, true);
                timepointTree.expandNode(treeNode, true);
                data.recovery_timepoint_uuid = treeNode.timepoint_uuid;
                data.recovery_timepoint_name = treeNode.getParentNode().name//时间点
                data.organization_name = treeNode.getParentNode().getParentNode().getParentNode().name//组织
                data.node_uuid = treeNode.node_uuid;
                data.integrity_check_flag = treeNode.getParentNode().integrity_check_flag;
            } else {
                operateResponseList(result, LANG.UI_MICROSOFT365_GET_ROOT_DIR)
            }
            Metronic.unblockUI(".exchange_tree");
        });
    }

    //初始化顶级目录表格
    var initRootDirTable = function (params,timepoint_uuid,uuid) {
        var options = {
            pagination:false,
            pageList: [5,10,25,50],
            vin_params:function () {
                return params;
            },
            vin_url:"/api/v1/exchange/restore_data/restore_points/" + timepoint_uuid + "/users/" + uuid,
            vin_method:"GET",
            columns:[{
                checkbox:true,
                sortable:false
            },
                {
                    field: 'name',
                    title: LANG.UI_COPY_TYPE,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'user',
                    title: LANG.UI_MICROSOFT365_USER_OR_GROUP,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'level',
                    title: LANG.UI_MICROSOFT365_PRIORITY,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'send',
                    title: LANG.UI_MICROSOFT365_FROM,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'receive',
                    title: LANG.UI_MICROSOFT365_RECIPIENT,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'subject',
                    title: LANG.UI_MICROSOFT365_THEME,
                    sortable: false,
                    align: 'center',
                },
                {
                    field: 'recv_date',
                    title: LANG.UI_MICROSOFT365_SEND_TIME,
                    sortable: false,
                    align: 'center',
                },
            ],
            onCheck: function (rowdata) {
                // 单个选中行，加入统计数量的购物车
                addToTotal('.rootdir-table #rootDirTable tbody .selected',1,rowdata,false);
            },
            onUncheck: function (rowdata) {
                //取消单个选中
                cancleToTotal(1,rowdata,false);
            },
            onCheckAll: function (a,b,c) {
                //全选
                var currentArr = a;
                if (a.length > b.length && b.length != 0) {
                    currentArr = a.filter(item => !b.includes(item));
                }
                var rows = $('.rootdir-table #rootDirTable').bootstrapTable('getSelections').length;
                addToTotal('.rootdir-table #rootDirTable tbody',rows,currentArr,true)
            },
            onUncheckAll: function (a,b,c) {
                // 取消全选
                var currentArr = b;
                if (b.length > a.length && a.length != 0) {
                    currentArr = b.filter(item => !a.includes(item));
                }
                var rows = $('.rootdir-table #rootDirTable tbody tr').length;
                cancleToTotal(rows,currentArr,true);
            },
        }
        $('.rootdir-table #rootDirTable').bootstrapTable('destroy');
        $('.rootdir-table #rootDirTable').baseTableConfig().init(options);
    }

    //得到子目录
    var getChildDir = function (treeNode) {
        var nodeuuid = treeNode.node_uuid;
        var params = {
            timepoint_uuid:treeNode.timepoint_uuid,
            node_uuid:nodeuuid,
            organization_uuid:treeNode.organization_uuid,
            user_uuid: treeNode.user_uuid,
            index_container_id: treeNode.index_container_id,
            table_id: treeNode.table_id,
            folder_id:treeNode.uuid,
            dir_type:5,
            type:treeNode.meta_type,
            next_start: treeNode.next_start ? treeNode.next_start : 0,
            page_size: 20,//元数据第一次请求条数
            table_flag:'',
            storage_type: treeNode.storage_type,
        };
        data.normal_search.folder_id = treeNode.uuid;
        //搜索的type只有0 1 2 3
        data.normal_search.type = treeNode.meta_type;
        Metronic.blockUI({target: ".exchange_tree",animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data/restore_points/" + treeNode.timepoint_uuid + "/users/" + treeNode.user_uuid, "GET", function (result) {
            Metronic.unblockUI(".exchange_tree");
            if (result.success) {
                //success
                initSonDirTable(params,treeNode.timepoint_uuid,treeNode.user_uuid,result.data.rows);
                timepointTree.removeChildNodes(treeNode);
                timepointTree.addNodes(treeNode, result.data.dir, true);
                timepointTree.expandNode(treeNode, true);
                data.recovery_timepoint_uuid = treeNode.timepoint_uuid;
                data.node_uuid = treeNode.node_uuid;
                if (treeNode.dir_type == 5) {
                    data.recovery_timepoint_name = treeNode.getParentNode().getParentNode().name//时间点
                    data.organization_name = treeNode.getParentNode().getParentNode().getParentNode().getParentNode().name//组织
                    data.integrity_check_flag = treeNode.getParentNode().getParentNode().integrity_check_flag;
                }
            } else {
                operateResponseList(result, LANG.UI_MICROSOFT365_GET_SON_DIR);
            }
        });
    }

    //获取元数据详情
    var getMedaDetails = function (d,index) {
        d = d[index];
        var params = {
            timepoint_uuid:d.timepoint_uuid,
            node_uuid:d.node_uuid,
            organization_uuid:d.organization_uuid,
            user_uuid: d.user_uuid,
            index_container_id: d.index_container_id,
            table_id: d.table_id,
            folder_id:d.parent_folder_id,
            dir_type:d.dir_type,
            item_id: d.item_id,
            item_uuid: d.item_uuid,
            type:d.type,
            storage_type:d.storage_type,
        };
        switch (parseInt(d.type)) {
            case 0://邮件类型
                getEmailDetail(d,params);
                break;
            case 1://日历类型
                getCanlendarDetail(d,params);
                break;
            case 2://联系人类型
                getContactDetail(d,params);
                break;
            case 3://任务类型
                getTaskDetail(d,params);
                break;
        }
    }

    var getEmailDetail = function (d,params) {
        $('#emailModal').modal({'width': '850px'});
        $('#sendTo').css({"border-color":"#E6E6E6"});
        Metronic.blockUI({target: "#emailModal",animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data/restore_points/" + d.timepoint_uuid + "/users/" + d.user_uuid, "GET", function (result) {
            if (result.success) {
                //success
                data.send = {}//用于保存发送邮件的数据
                data.send.index_container_id  = parseInt(result.data[0].index_container_id);
                data.send.table_id =  parseInt(result.data[0].table_id);
                data.send.folder_id =  result.data[0].folder_id;
                data.send.type =  parseInt(result.data[0].item_type);
                data.send.item_id =  result.data[0].item_id;
                data.send.item_uuid =  result.data[0].item_uuid;
                data.seachDiv = '.contacts-table #contactsTable';
                $('#sendFrom').val(result.data.system_send);
                var newHtml = formatHtml(result.data[0].content)
                $('.email-title').html(result.data[0].subject ? result.data[0].subject : '--');
                $('.contentName').html(result.data[0].mail_sender ? result.data[0].mail_sender : '--');
                $('.receiveName').html(result.data[0].mail_recipents != null ? result.data[0].mail_recipents.join(';') : '--');
                $('.contentTime').html(result.data[0].recv_date ? result.data[0].recv_date : '--');
                $('.ccName').html(result.data[0].mail_cc != null ? result.data[0].mail_cc.join(';') : '--');
                $('.exchange-contentBox .text').html(newHtml);
                //禁用a标签
                $('.exchange-contentBox .text a').click(function () {
                    return false;
                })
                $('.attachmentName').html(result.data[0].is_attachment == 1 ? result.data[0].attachments.join(';') : '--');
                //发送邮件
                $('.sendEmail').on('click',function () {
                    toSendEmail(params);
                });
            } else {
                operateResponseList(result, LANG.UI_MICROSOFT365_GET_METADATA_DETAILS)
            }
            Metronic.unblockUI("#emailModal");
        });
    }

    var formatHtml = function (html) {
        if (html == '') {
            return '--';
        }
        //正则匹配图片标签，并删除
        var newHtml = html.replace(/<img\b[^>]*>/gi, '');
        //正则匹配position: fixed;并删除
        var regex2 = /position:\s*fixed;/g;
        newHtml = newHtml.replace(regex2, "");
        return newHtml;
    }

    var getCanlendarDetail = function (d,params) {
        $('#calendarModal').modal({'width': '550px'});
        Metronic.blockUI({target: "#emailModal",animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data/restore_points/" + d.timepoint_uuid + "/users/" + d.user_uuid, "GET", function (result) {
            if (result.success) {
                //success
                data.send = {}//用于保存发送邮件的数据
                data.send.index_container_id  = parseInt(result.data[0].index_container_id);
                data.send.table_id =  parseInt(result.data[0].table_id);
                data.send.folder_id =  result.data[0].folder_id;
                data.send.type =  parseInt(result.data[0].item_type);
                data.send.item_id =  result.data[0].item_id;
                data.send.item_uuid =  result.data[0].item_uuid;
                data.seachDiv = '.calendar-table #calendarTable';
                $('.calendar-title').html(result.data[0].subject ? result.data[0].subject : '--');
                $('.calendar-address').html(result.data[0].location_dispaly ? result.data[0].location_dispaly : '--');
                $('.calendar-start').html(result.data[0].start_time ? result.data[0].start_time : '--');
                $('.calendar-end').html(result.data[0].stop_time ? result.data[0].stop_time : '--');
                $('.calendar-attach').html(result.data[0].is_attachment == 1 ? result.data[0].attachments.join(';') : '--');
                $('.calendar-text').html(result.data[0].event_body ? result.data[0].event_body : '--');
            } else {
                operateResponseList(result, LANG.UI_MICROSOFT365_GET_METADATA_DETAILS)
            }
            Metronic.unblockUI("#calendarModal");
        });
    }

    var getContactDetail = function (d,params) {
        if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
            $('#contactModal').modal({'width': '550px'});
        }else{
            $('#contactModal').modal({'width': '750px'});
        }
        
        Metronic.blockUI({target: "#contactModal",animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data/restore_points/" + d.timepoint_uuid + "/users/" + d.user_uuid, "GET", function (result) {
            if (result.success) {
                //success
                data.send = {}//用于保存发送邮件的数据
                data.send.index_container_id  = parseInt(result.data[0].index_container_id);
                data.send.table_id =  parseInt(result.data[0].table_id);
                data.send.folder_id =  result.data[0].folder_id;
                data.send.type =  parseInt(result.data[0].item_type);
                data.send.item_id =  result.data[0].item_id;
                data.send.item_uuid =  result.data[0].item_uuid;
                data.seachDiv = '.contacts-table #contactsTable';
                $('.contact-name').html(result.data[0].display_name ? result.data[0].display_name : '--');
                var department = result.data[0].department ? result.data[0].department : '--';
                var job_title = result.data[0].job_title ? result.data[0].job_title : '--';
                $('.contact-position').html(department + '/' + job_title);
                $('.contact-company').html(result.data[0].company ? result.data[0].company : '--');
                $('.contact-email').html(result.data[0].email_addresses != null ? result.data[0].email_addresses.join(';') : '--');
                $('.contact-email').attr("title",result.data[0].email_addresses != null ? result.data[0].email_addresses.join(';') : '--')
                $('.contact-address').html(result.data[0].web_page_and_im_addess != "" ? result.data[0].web_page_and_im_addess : '--');
                $('.contact-shangwu').html(result.data[0].business_phone ? result.data[0].business_phone : '--');
                $('.contact-zhuzhai').html(result.data[0].home_phone ? result.data[0].home_phone : '--');
                $('.contact-phone').html(result.data[0].mobile ? result.data[0].mobile : '--');
                $('.contact-youbian').html(result.data[0].postal_code ? result.data[0].postal_code : '--');
                $('.contact-xian').html(result.data[0].county ? result.data[0].county : '--');
                $('.contact-sheng').html(result.data[0].province_and_city ? result.data[0].province_and_city : '--');
                $('.contact-street').html(result.data[0].street ? result.data[0].street : '--');
                $('.contact-country').html(result.data[0].country ? result.data[0].country : '--' ? result.data[0].country : '--');
                $('.contact-text').html(result.data[0].remarks);
            } else {
                operateResponseList(result, LANG.UI_MICROSOFT365_GET_METADATA_DETAILS)
            }
            Metronic.unblockUI("#contactModal");
        });
    }

    var getTaskDetail = function (d,params) {
        if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
            $('#taskModal').modal({'width': '550px'});
        }else{
            $('#taskModal').modal({'width': '750px'});
        }
        
        Metronic.blockUI({target: "#emailModal",animate: true});
        pAjaxRequest(params, "/api/v1/exchange/restore_data/restore_points/" + d.timepoint_uuid + "/users/" + d.user_uuid, "GET", function (result) {
            if (result.success) {
                //success
                data.send = {}//用于保存发送邮件的数据
                data.send.index_container_id  = parseInt(result.data[0].index_container_id);
                data.send.table_id =  parseInt(result.data[0].table_id);
                data.send.folder_id =  result.data[0].folder_id;
                data.send.type =  parseInt(result.data[0].item_type);
                data.send.item_id =  result.data[0].item_id;
                data.send.item_uuid =  result.data[0].item_uuid;
                $('.task-title').html(result.data[0].display_name ? result.data[0].display_name : '--');
                $('.task-status').html(result.data[0].status ? result.data[0].status : '--');
                $('.task-start').html(result.data[0].start_date ? result.data[0].start_date : '--');
                $('.task-author').html(result.data[0].owner ? result.data[0].owner : '--');
                $('.task-import').html(result.data[0].importance ? result.data[0].importance : '--');
                $('.task-end').html(result.data[0].due_date ? result.data[0].due_date : '--');
                $('.task-attach').html(result.data[0].is_attachment == 1 ? result.data[0].attachments.join(';') : '--');
                $('.task-text').html(result.data[0].remarks ? result.data[0].remarks : '--');
            } else {
                operateResponseList(result, LANG.UI_MICROSOFT365_GET_METADATA_DETAILS)
            }
            Metronic.unblockUI("#taskModal");
        });
    }

    //初始化子目录表格
    var initSonDirTable = function (params,timepoint_uuid,user_uuid,tabledata) {
        var page_size_metadata = '';//加载更多默认条数
        var currentNum = 0;
        params.table_flag = 1;//是否请求的是表格类型数据
        $('.exchange-div .table-div').hide();
        $('.seachDiv').hide();
        if (tabledata.length == 0) {
            $('.contacts-table').show();
            $('.contacts-table #contactsTable').bootstrapTable('destroy');
            $('.contacts-table #contactsTable').baseTableConfig().init({});
            return;
        } else {
            $('.topToolDiy').show();
            $('.batch-export-div').show();//批量导出按钮
        }
        //根据子目录type判断显示哪个表
        var tableDiv = '';
        $('.batch-export-div').show();
        $('.exchange-div .squreBtn').css({"background-color":"#F0F0F0","color":"#D3D3D3"});
        switch (parseInt(tabledata[tabledata.length - 1].type)) {
            case 2://联系人
            case 0://发件箱
                $('.contacts-table').show();
                tableDiv = '.contacts-table #contactsTable';
                data.seachDiv = '.contacts-table #contactsTable';
                break;
            case 3://任务
                $('.task-table').show();
                tableDiv = '.task-table #taskTable';
                $('.batch-export-div').hide();//任务没有批量导出
                break;
            case 1://日历
                $('.calendar-table').show();
                tableDiv = '.calendar-table #calendarTable';
                data.seachDiv = '.calendar-table #calendarTable';
                break;
            default:
                $('.contacts-table').show();
                tableDiv = '.contacts-table #contactsTable';
                data.seachDiv = '.contacts-table #contactsTable';
                break;
        }
        var columnsData = getSonTableTitle(tabledata,tableDiv);//得到子目录表格列标题，不同类型的子目录每一列标题不同
        var options = {
            pagination: true,
            pageList: [5, 10, 25, 50],
            sidePagination: "client", //分页方式，client和server
            maintainMetaData: true,
            vin_params:function () {
                return params;
            },
            vin_url:"/api/v1/exchange/restore_data/restore_points/" + timepoint_uuid + "/users/" + user_uuid,
            vin_method:"GET",
            columns:columnsData,
            onPostBody: function () {
                //元数据
                //获取bootstrap的加载完毕的数据
                var row = $(tableDiv).bootstrapTable('getData');
                for (var i = 0; i < row.length; i++) {
                    if (row[i].total_count > row.length && row[i].more) {
                        currentNum = 0;
                        row.forEach(item => {
                            if (item.type != 100 && !item.more) {
                                currentNum++;//当前元数据个数
                            }
                        })
                        $(tableDiv).bootstrapTable('remove', {field: '$index', values: [i]})//在表格中移除php返回的加载更多那一行
                        $('.loadMore-div').show();
                        loadMoreShow = true;
                        $('#exchange-recover-content .addIt-list').css({"height":"calc(100% - 61px)","border-bottom":"none"});
                        var percent = ((currentNum * 100) / parseInt(row[i - 1].total_count)).toFixed(2)
                        if (percent > 100) {
                            $('.loadPercent').html('100%');
                        } else {
                            $('.loadPercent').html(percent + '%');
                        }
                        $('input[name="page_num"]').on('ifClicked', function () {
                            if ($(this).val() == 0) {
                                $('.exchange-div .diy-label').hide();
                                $('.exchange-div .diy-input').show(); // 显示自定义输入框
                            } else {
                                $('.exchange-div .diy-label').show();
                                $('.exchange-div .diy-input').hide(); // 隐藏自定义输入框
                            }
                        });
                        $('.loadMoreClick').off().on('click',function () {
                            var icheckArr = $('.exchange-div .loadMore-div').find('.icheck');
                            for (var j = 0; j < icheckArr.length; j++) {
                                if ($(icheckArr[j]).is(':checked')) {
                                    if ($(icheckArr[j]).val() == 0) {
                                        page_size_metadata = $.trim($('.exchange-div .diy-input').val());
                                        if (page_size_metadata == '') {
                                            UIToastr.showWarning(LANG.UI_MICROSOFT365_LOAD_MORE_DATA,LANG.UI_MICROSOFT365_INPUT_LOAD_ITEM);
                                            return;
                                        }
                                    } else {
                                        page_size_metadata = $(icheckArr[j]).val();
                                    }
                                }
                            }
                            var params = {
                                timepoint_uuid:row[i - 2].timepoint_uuid,
                                node_uuid:row[i - 2].node_uuid,
                                organization_uuid:row[i - 2].organization_uuid,
                                user_uuid: row[i - 2].user_uuid,
                                index_container_id: row[i - 2].index_container_id,
                                table_id: row[i - 2].table_id,
                                folder_id: data.normal_search.folder_id,
                                dir_type:6,
                                type:row[i - 2].type,
                                next_start: row[i - 2].next_start ? row[i - 2].next_start : 0,
                                page_size: page_size_metadata,//加载更多请求条数
                                table_flag:1,
                                current_num:currentNum
                            };
                            params = deepCloneObject(params);
                            var percent = ((currentNum  * 100) / parseInt(row[i - 2].total_count)).toFixed(2)
                            if (percent > 100) {
                                $('.loadPercent').html('100%');
                            }
                            Metronic.blockUI({target: '.exchange-div',animate: true});
                            pAjaxRequest(params, "/api/v1/exchange/restore_data/restore_points/" + row[i - 2].timepoint_uuid + "/users/" + row[i - 2].user_uuid, "GET", function (result) {
                                Metronic.unblockUI('.exchange-div');
                                if (result.success) {
                                    //success
                                    $(tableDiv).bootstrapTable('append', result.data)
                                } else {
                                    operateResponseList(result, LANG.UI_MICROSOFT365_GET_METADATA);
                                }
                            });

                        });
                    } else {
                        if (row[i].total_count > row.length && row.length == i+1) {//点分页的页码会走进这里面
                            $('.loadMore-div').show();
                            loadMoreShow = true;
                            $('#exchange-recover-content .addIt-list').css({"height":"calc(100% - 61px)","border-bottom":"none"});
                            return;
                        }
                        $('.loadMore-div').hide();
                        loadMoreShow = false;
                        $('#exchange-recover-content .addIt-list').css({"height":"calc(100% - 15px)","border-bottom":"1px solid #E6E6E6"});
                    }
                }
            },
            onCheck: function (rowdata) {
                // 单个选中行，加入统计数量的购物车
                addToTotal(tableDiv + ' tbody .selected',1,rowdata,false);
                $('.exchange-div .squreBtn').css({"background-color":"#0FBF98","color":"white"});
            },
            onUncheck: function (rowdata) {
                //取消单个选中
                cancleToTotal(1,rowdata,false);
                //表格没有选中的行  置灰
                var checkedRows = $(tableDiv).bootstrapTable('getSelections');
                if (checkedRows == 0) {
                    $('.exchange-div .squreBtn').css({"background-color":"#F0F0F0","color":"#D3D3D3"});
                }
            },
            onCheckAll: function (a,b,c) {
                //全选
                var currentArr = a;
                if (a.length > b.length && b.length != 0) {
                    currentArr = a.filter(item => !b.includes(item));
                }
                var rows = $(tableDiv).bootstrapTable('getSelections').length;
                addToTotal(tableDiv + ' tbody',rows,currentArr,true)
                $('.exchange-div .squreBtn').css({"background-color":"#0FBF98","color":"white"});
            },
            onUncheckAll: function (a,b,c) {//a代表的是操作的总共数组
                // 取消全选
                var currentArr = b;
                if (b.length > a.length && a.length != 0) {
                    currentArr = b.filter(item => !a.includes(item));
                }
                var rows = $(tableDiv + ' tbody tr').length;
                cancleToTotal(rows,currentArr,true);
                $('.exchange-div .squreBtn').css({"background-color":"#F0F0F0","color":"#D3D3D3"});
            },
        }
        $(tableDiv).bootstrapTable('destroy');
        $(tableDiv).baseTableConfig().init(options);
    }

    var getSonTableData = function (data) {
        var tableData = {};
        tableData.rows = data;
        tableData.total = data.length;
        return tableData
    }

    var getSonTableTitle = function (data,tableDiv) {
        var columnsData = [];
        var fieldVal = [];//用于保存每一列的字段名
        var titleVal = [];//用于保存表格列名
        var operates = {
            'click .detail' : function (e, value, row, index) {
                var data = $(tableDiv).bootstrapTable('getData');
                getMedaDetails(data,index);
            }
        };
        switch (parseInt(data[data.length - 1].type)) {
            case 2://联系人
                fieldVal = ['name','display_name','email_addresses','company','department','business_phone','operate'];
                titleVal = [LANG.UI_COPY_TYPE,LANG.UI_MICROSOFT365_NAME,LANG.UI_MICROSOFT365_E_EMAIL,LANG.UI_MICROSOFT365_COMPANY,LANG.UI_MICROSOFT365_DEPARTMENT,LANG.UI_MICROSOFT365_BUSINESS_PHONE,LANG.UI_PUBLIC_OPERATION];
                break;
            case 0://发件箱
                fieldVal = ['name','send','receive','cc','subject','recv_date','operate'];
                titleVal = [LANG.UI_COPY_TYPE,LANG.UI_MICROSOFT365_FROM,LANG.UI_MICROSOFT365_RECIPIENT,LANG.UI_MICROSOFT365_CC,LANG.UI_MICROSOFT365_THEME,LANG.UI_MICROSOFT365_SEND_TIME,LANG.UI_PUBLIC_OPERATION];
                break;
            case 3://任务
                fieldVal = ['name','subject','status','importance','due_date','owner_name','operate'];
                titleVal = [LANG.UI_COPY_TYPE,LANG.UI_MICROSOFT365_TITLE,LANG.UI_PUBLIC_STATUS,LANG.UI_MICROSOFT365_IMPORTANCE,LANG.UI_PUBLIC_EXPIRE,LANG.UI_MICROSOFT365_AUTHOR,LANG.UI_PUBLIC_OPERATION];
                break;
            case 1://日历
                fieldVal = ['name','subject','start_time','stop_time','state','location_display','operate'];
                titleVal = [LANG.UI_COPY_TYPE,LANG.UI_MICROSOFT365_TITLE,LANG.UI_PUBLIC_START_TIME,LANG.UI_PUBLIC_END_TIME,LANG.UI_PUBLIC_STATUS,LANG.UI_MICROSOFT365_REGION,LANG.UI_PUBLIC_OPERATION];
                break;
            default:
                fieldVal = ['name','send','receive','cc','subject','recv_date'];
                titleVal = [LANG.UI_COPY_TYPE,LANG.UI_MICROSOFT365_FROM,LANG.UI_MICROSOFT365_RECIPIENT,LANG.UI_MICROSOFT365_CC,LANG.UI_MICROSOFT365_THEME,LANG.UI_MICROSOFT365_SEND_TIME];
                break;
        }
        columnsData.push({checkbox:true, sortable:false});
        fieldVal.forEach((item,index) => {
            if (titleVal[index] == LANG.UI_PUBLIC_OPERATION) {
                columnsData.push({field: item ,title:titleVal[index],sortable: false,align: "center",events: operates,opButton: true,
                    formatter: function (value,data,row) {
                        return value;
                    }
                });
            } else {
                columnsData.push({field: item ,title:titleVal[index],sortable: false,align: "center",
                    formatter: function (value,data,row) {
                        if (value == undefined || value == "") {
                            return '<span title="--">--</span>';
                        } else {
                            return '<span title="' + value + '">' + value + '</span>';
                        }
                    }
                });
            }

        })
        return columnsData;
    }

    //得到目录下的元数据详细信息
    var getMetadataDetail = function (treeNode) {
        let storage_uuid = $('#storageSelect').val();
        var params = {
            timepoint_uuid:treeNode.timepoint_uuid,
            storage_uuid:storage_uuid,
            organization_uuid:treeNode.organization_uuid,
            user_uuid: treeNode.user_uuid,
            index_container_id: 0,
            table_id: 0,
            folder_id:treeNode.uuid,
            dir_type:treeNode.dir_type,
            next_start: 0,
            page_size: 0,
            type:treeNode.meta_type,
            storage_type: treeNode.storage_type,
        };
        var url = "/api/v1/exchange/restore_data/restore_points/" + treeNode.timepoint_uuid + "/users/" + treeNode.user_uuid;

    }

    //根据类型得到图标和对应的描述（表格第一列）
    var getIconDes = function (type,name) {
        var des = '';
        switch (parseInt(type)) {
            case 1000://用户
                des = '<i class="viconfont vicon-a-yonghubiaogeyong"></i>'+LANG.UI_MICROSOFT365_USER;
                break;
            case 1001://用户组
                des = '<i class="viconfont vicon-a-yonghuzubiaogeneiyong1"></i>'+LANG.UI_MICROSOFT365_USER_GROUP;
                break;
            case 0://邮件
                des = '<i class="viconfont vicon-youjian1 c3E767F"></i>' + name;
                break;
            case 1://日历
                des = '<i class="viconfont vicon-rili1 c3E767F"></i>'+LANG.UI_MICROSOFT365_CALENDAR;
                break;
            case 2://联系人
                des = '<i class="viconfont vicon-lianxiren1 c3E767F"></i>'+LANG.UI_MICROSOFT365_CONTACTS;
                break;
            case 3://任务
                des = '<i class="viconfont vicon-renwu c3E767F"></i>'+LANG.UI_MICROSOFT365_TASK;
                break;
            case 4://发件箱
                des = '<i class="viconfont vicon-fasongyoujian c3E767F"></i>'+LANG.UI_MICROSOFT365_OUTBOX;
                break;
            case 5://存档
                des = '<i class="viconfont vicon-cundang c3E767F"></i>'+LANG.UI_MICROSOFT365_FILE;
                break;
            case 6://对话历史记录
                des = '<i class="viconfont vicon-duihualishijilu c3E767F"></i>'+LANG.UI_MICROSOFT365_DIALOGUE_HISTORY;
                break;
            case 7://已删除邮件
                des = '<i class="viconfont vicon-shanchu c3E767F"></i>'+LANG.UI_MICROSOFT365_DELETED_EMAILS;
                break;
            case 8://已发送邮件
                des = '<i class="viconfont vicon-biaogefasong c3E767F"></i>'+LANG.UI_MICROSOFT365_SENT_EMAILS;
                break;
            case 9://收件箱
                des = '<i class="viconfont vicon-shoujianxiang1 c3E767F"></i>'+LANG.UI_MICROSOFT365_INBOX;
                break;
            case 10://草稿
                des = '<i class="viconfont vicon-caogao1 c3E767F"></i>'+LANG.UI_MICROSOFT365_DRAFT;
                break;
            case 11://子目录
                des = '<i class="viconfont vicon-zimulu1 c3E767F"></i>' + name;
                break;
        }
        return des;
    }

    //获取时间点所在的存储
    var initStorageSelect = function(){
        pAjaxRequest({}, '/api/v1/storages/type', "GET", (result) => {
            if (result.success) {
                let data = result.data;
                let storageSelect = $('#storageSelect')
                storageSelect.empty();
                for (let i = 0; i < data.length; i++) {
                    let option = $("<option>").text(data[i].text).val(data[i].storageid);
                    storageSelect.append(option);
                }
            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_STORAGE_FAILED, result.message);
            }
        })
        //绑定事件
        $('#storageSelect').on('change', initPointTree);
    }

    //获取速度单位换算大小
    var getSpeedUnit = function () {
        var type = parseInt($('#unit').val());
        var unit;
        switch (type) {
            case 1:
                unit = 1024;
                break;
            case 2:
                unit = 1024 * 1024;
                break;
            case 3:
                unit = 1024 * 1024 * 1024;
                break;
        }

        return unit;
    }

    var getUuid = function () {
        var len = 36;//36长度
        var radix = 16;//16进制
        var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
        var uuid = [], i;
        radix = radix || chars.length;
        if (len) {
            for (i = 0; i < len; i++) {
                uuid[i] = chars[0 | Math.random() * radix];
            }
        } else {
            var r;
            uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
            uuid[14] = '4';
            for (i = 0; i < 36; i++) {
                if (!uuid[i]) {
                    r = 0 | Math.random() * 16;
                    uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                }
            }
        }
        return uuid.join('');
    }

    //添加限速策略
    var speedSubmit = function () {
        var info = {};
        var des = '';
        info.mode = $('#speedModeType').val();
        var speedUnit = getSpeedUnit();
        var speedNum = parseInt($('#speedSpinnerNumInput').val());
        if (!speedNum || speedNum <= 0) {
            UIToastr.showWarning(LANG.UI_BACKUP_SPEED_LIMIT_TIPS);
            return false;
        }

        var unit = $('#unit').find('option:selected').text();
        var liId = getUuid();
        info.uuid = liId;
        info.value = speedNum * speedUnit;
        info.speed_num = speedNum;
        info.unit = unit;
        if (info.mode == 1) {
            var strategyConfig = $('#speedstrategy').getSpeedStrategyConfig();
            info.type = strategyConfig.speedInfo.type;
            info.start_time = strategyConfig.speedInfo.startTime;
            info.end_time = strategyConfig.speedInfo.endTime;
            info.days = strategyConfig.speedInfo.days;
            info.des = strategyConfig.speedInfo.des + ', ' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE + ':' + speedNum + unit;
            des += '<li style="margin-top:15px;" class="list-group-item popovers speedTips" id="speed' + liId + '" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + info.des +
                '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 94%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' + info.des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:10px;"><a class="del' + liId + '" >'
                + '<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
        } else {
            if (!checkSimpleForever(info.mode)) {
                UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE, LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_ADD_FOREVER_TIPS);
                return false;
            }
            info.type = 4;
            info.start_time = '';
            info.end_time = '';
            info.days = [];
            info.des = LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_FOREVER + ', ' + LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_VALUE + ':' + speedNum + unit;
            des += '<li style="margin-top:15px;" class="list-group-item popovers speedTips" id="speed' + liId + '"  data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="' + info.des +
                '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one"> ' + info.des +  '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:10px;"><a class="del' + liId + '" >'
                + '<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
        }
        //检测结束时间是否大于开始时间
        if (!checkTime(info.start_time,info.end_time)) {
            return;
        }
        $('#speedList').append(des);
        $('.speedTips').popover();     //初始化tips
        $('.del' + liId).on('click', function () {
            $('.popover.in').remove();
            $('#speed' + liId).remove();
            for (var i = 0; i < speedList.length; i++) {
                if (liId == speedList[i].uuid) {
                    speedList.splice($.inArray(speedList[i],speedList),1);
                }
            }
            initSpeedStrategyDes();
        });
        speedList.push(info);
        $('#speedlimitModal').modal('hide');
        initSpeedStrategyDes();
    }

    var checkTime = function (start,end) {
        var startnum = new Date("1970-01-01" + " " + start).getTime();
        var endnum = new Date("1970-01-01" + " " + end).getTime();
        if (endnum <= startnum && $("#speedModeType").val() == 1) {//按策略限速才判断
            UIToastr.showWarning(LANG.UI_FILE_CHECK_TIME_RANGE, LANG.UI_FILE_CHECK_TIME_RANGE_TIPS);
            return false;
        } else {
            return true;
        }
    }

    var initSpeedStrategyDes = function () {
        var titleDes = "";
        var des = "";
        if (speedList.length != 0) {
            des += LANG.UI_GLOBAL_STRATEGY_SPEED_NUM + ": " + speedList.length;
        }
        for (var i = 0; i < speedList.length; i++) {
            titleDes += speedList[i].des + '. ';
        }
        $('.speedlimitDes').html(des);
        $('.speedlimitDes').prop('title', titleDes);

    }
    var speedModeHandler = function () {
        if (this.value == 2) {
            $('.setSpeedStrategy').hide();
        } else {
            $('.setSpeedStrategy').show();
        }
    }

    var checkSimpleForever = function (mode) {
        for (var i = 0; i < speedList.length; i++) {
            if (mode == speedList[i].mode) {
                return false;
            }
        }

        return true;
    }

    var initSpeedTimeStrategy = function () {
        var strategy = [];
        strategy[0] = {
            mode: 1,
            strategy_type: 2,
            days: [0, 0, 0, 0, 1, 0, 0],
            start_time: '23:00:00',
            end_time: '23:30:00',
        };
        //延迟设置,因为这里icheck会默认修改里面的选中事件
        $('#speedstrategy').speedstrategy({config: strategy});
        initSpeedFlag = true;
    }





    var setStrategyInfo = function (info) {
        data.type_info.strategy = info;
        return true;
    }

    //得到strategy_each信息
    var getStrategyEach = function (type, tabpane) {
        var info = {};
        //type:每天/每周/每月
        info.type = type;
        info.startTime = tabpane.find('.starttime').val();
        info.rollFlag = tabpane.find('.make-switch').get(0).checked;
        info.rollInterval = tabpane.find('.rolltime').val();
        info.endTime = tabpane.find('.endtime').val();

        return info;
    }

    var getDays = function (type, tabpane) {
        var days = [];
        var input;
        if (type == CONF.STRATEGY_TYPE.WEEK) {
            input = tabpane.find('.weekcheck').find('input');
        } else if (type == CONF.STRATEGY_TYPE.MONTH) {
            input = tabpane.find('.monthcheck').find('input');
        } else {
            return days;
        }
        flagDay = false;
        input.each(function (i, d) {
            if (d.checked) {
                days[i] = 1;
                flagDay = true;
            } else {
                days[i] = 0;
            }
        });

        if (!flagDay) {
            if (type == CONF.STRATEGY_TYPE.WEEK) {
                $('.selectweektip').html(LANG.UI_STRATEGY_SELECT_WEEK).show();
            } else if (type == CONF.STRATEGY_TYPE.MONTH) {
                $('.selectmonthtip').html(LANG.UI_STRATEGY_SELECT_DATE).show();
            }
            setStrategyInfo({});
        } else {
            $('.selectweektip').hide();
            $('.selectmonthtip').hide();
        }

        return days;
    }

    //每天
    var daySelect = function (tabpane) {
        var info = {};
        info = getStrategyEach(CONF.STRATEGY_TYPE.DAY, tabpane);

        return setStrategyInfo(info);
    }
    //每周
    var weekSelect = function (tabpane) {
        var info = {}, days = [];
        info = getStrategyEach(CONF.STRATEGY_TYPE.WEEK, tabpane);
        info.days = getDays(CONF.STRATEGY_TYPE.WEEK, tabpane);
        if (!flagDay) {
            return false;
        }
        return setStrategyInfo(info);
    }
    //每月
    var monthSelect = function (tabpane) {
        var info = {}, days = [];
        info = getStrategyEach(CONF.STRATEGY_TYPE.MONTH, tabpane);
        info.days = getDays(CONF.STRATEGY_TYPE.MONTH, tabpane);
        if (!flagDay) {
            return false;
        }
        return setStrategyInfo(info);
    }
    //全局
    var globalSelect = function (tabpane, type) {

    }

    var eachButtonClick = function () {
        var tabpane = $(this).closest('.tab-pane');
        var panelDefault = tabpane.closest('.panel-default');
        var panelHeading = panelDefault.children('.panel-heading');
        var i = panelHeading.find('i');
        //完全/增量/差异
        var setFlag = true;
        if (tabpane.hasClass('day')) {
            setFlag = daySelect(tabpane);
        } else if (tabpane.hasClass('week')) {
            setFlag = weekSelect(tabpane);
        } else if (tabpane.hasClass('month')) {
            setFlag = monthSelect(tabpane);
        } else if (tabpane.hasClass('global')) {
            setFlag = globalSelect(tabpane);
        }
        if (setFlag) {
            i.prop('class', 'fa fa-check');
            $('#recover').collapse('hide');
        } else {
            i.prop('class', '');
        }
    }


    var wizardInit = function () {
        if (!jQuery().bootstrapWizard) {
            return;
        }
        var form = $('#submit_form');
        var error = $('.alert-danger', form);
        var success = $('.alert-success', form);
        var handleTitle = function (tab, navigation, index) {
            var total = navigation.find('li').length;
            var current = index + 1;
            jQuery('li', $('#exchange-recover-content')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }

            if (current == 1) {
                $('#exchange-recover-content').find('.button-previous').hide();
                $('#exchange-recover-content').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#exchange-recover-content').find('.button-previous').show();
                $('#exchange-recover-content').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#exchange-recover-content').find('.button-next').hide();
                $('#exchange-recover-content').find('.button-submit').show();
            } else {
                $('#exchange-recover-content').find('.button-next').show();
                $('#exchange-recover-content').find('.button-submit').hide();
            }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#exchange-recover-content').bootstrapWizard({
            'nextSelector': '.button-next',
            'previousSelector': '.button-previous',
            onTabClick: function (tab, navigation, index, clickedIndex) {
                return false;
            },
            onNext: function (tab, navigation, index) {
                success.hide();
                error.hide();
                switch (index) {
                    case 1:
                        if (step1Valid() == false) {
                            return false;
                        }
                        break;
                    case 2:
                        if (step2Valid() == false) {
                            return false;
                        }
                        break;
                    case 3:
                        if (step3Valid() == false) {
                            return false;
                        }
                        break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                success.hide();
                error.hide();

                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#exchange-recover-content').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#exchange-recover-content').find('.button-previous').hide();
        $('#exchange-recover-content .button-submit').click(submit).hide();
    };

    var step1Valid = function () {
        //exchange重试所有对象
        $('#task_retry_object').find("option[value=1]").remove();
        //恢复源
        if (totalDataList.length == 0) {
            UIToastr.showWarning(LANG.UI_MICROSOFT365_RECOVERY_TITLE, LANG.UI_MICROSOFT365_CHECK_RECOVERY_RESOURCE);
            return false;
        }
        data.node_uuid = totalDataList[0].node_uuid;
        data.recovery_timepoint_uuid = totalDataList[0].timepoint_uuid;
        initTree(totalDataList[0].organization_uuid);//初始化恢复第二步组织树
        var temparr = totalDataList;
        data.exch_recovery_object_info_list = [];
        temparr.forEach(item => {
            data.exch_recovery_object_info_list.push({
                "recovery_object_type":parseInt(item.type) === "" ? "" : parseInt(item.type),
                "user_uuid":item.user_uuid || "",
                "item_id":item.item_id || "",
            })
        })
        //如果恢复源选择多个用户，恢复目标不能指定用户
        getUserSelectStatus(totalDataList);
        showStep1();
        return true;
    }
    var getUserSelectStatus = function (data) {
        var user_uuid = [];
        var timepointFlag = false;//是否选中了整个时间点，选整个时间点禁用选择指定用户
        for (var i = 0; i < data.length; i++) {
            if (data[i].type == 10000) {//时间点
                timepointFlag = true
            } else if ($.inArray(data[i].user_uuid,user_uuid) == -1) {
                user_uuid.push(data[i].user_uuid);
            }
        }
        if (user_uuid.length > 1 || timepointFlag) {
            $('#userSelect').val(LANG.UI_MICROSOFT365_NOT_SPECIFY_USER);
            $('#userSelect').attr({'destination_user_uuid': '', 'destination_mail': '', 'destination_user_type': 0});
            forbidUserSelect = true;
            $('.select-user').find('button, input').prop('disabled',true);
        } else {
            forbidUserSelect = false;
            $('.select-user').find('button, input').prop('disabled',false);
        }
        recoveryWayChange();
    }

    var showStep1 = function () {
        //设置任务名
        if ($.trim($('#jobname').val()) == "") {
            pAjaxRequest({}, "/api/v1/exchange/jobs/restore/task_name", "GET", function (result) {
                if (result.success) {
                    $('#jobname').val(result.data);
                }
            }, false);
        }
        //设置时间点信息
        var tempArr = data.exch_recovery_object_info_list
        // 对数组去重
        var showArr =  tempArr.filter((val, i, arr) => {
            return arr.findIndex(obj => JSON.stringify(obj) === JSON.stringify(val)) === i;
        });
        showSrcData(showArr);
        return;
    }

    var showSrcData = function (showArr) {
        var organizationNum = 0;
        var userNum = 0;
        var groupNum = 0;
        var dirNum = 0;
        var emailNum = 0;
        var calenderNum = 0;
        var contactNum = 0;
        var taskNum = 0;
        showArr.forEach(item => {
            switch (parseInt(item.recovery_object_type)) {
                case 10000:
                    organizationNum++;
                    break;
                case 1000:
                    userNum++;
                    break;
                case 1001:
                    groupNum++;
                    break;
                case 100:
                    dirNum++;
                    break;
                case 0:
                    emailNum++;
                    break;
                case 1:
                    calenderNum++;
                    break;
                case 2:
                    contactNum++;
                    break;
                case 3:
                    taskNum++;
                    break;
            }
        });
        var des = '';
        var colondes = '';
        if(CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw"){
            colondes = ': ';
        }
        if (organizationNum != 0) {
            des += LANG.UI_MICROSOFT365_ORGANIZATION + colondes + organizationNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (userNum != 0) {
            des += LANG.UI_MICROSOFT365_USER + colondes + userNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (groupNum != 0) {
            des += LANG.UI_MICROSOFT365_USER_GROUP + colondes + groupNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (dirNum != 0) {
            des += LANG.UI_MICROSOFT365_DIR + colondes + dirNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (emailNum != 0) {
            des += LANG.UI_MICROSOFT365_EMAIL + colondes + emailNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (calenderNum != 0) {
            des += LANG.UI_MICROSOFT365_CALENDAR + colondes + calenderNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (contactNum != 0) {
            des += LANG.UI_MICROSOFT365_CONTACTS + colondes + contactNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        if (taskNum != 0) {
            des += LANG.UI_MICROSOFT365_TASK + colondes + taskNum + LANG.UI_MICROSOFT365_USER_UNIT;
        }
        $(".recoverdatalist").html(des);
    }
    var initTree = function (organization_uuid) {
        //获取组织数据
        pAjaxRequest({"restoreOrganization":true,"organization_uuid":organization_uuid}, "/api/v1/exchange/jobs/organization", "GET", setTree, false);
    };
    var setTree = function (result) {
        //如果结果为空
        if (!result.success) {
            $("#noOrganization").show();
            $("#exchange_tree").hide();
            return;
        } else {
            $("#exchange_tree").show();
            $(".searchDiv").show();
            $("#noOrganization").hide();
            $('#nosearchtips').hide();
        }
        var setting = {
            check: {
                enable: true,
                nocheckInherit: false
            },
            data: {
                simpleData: {
                    enable: true,
                },
                key:{
                    title: "title"
                }
            },
            callback: {
                beforeClick: nodeClickOrganization,
                onCheck: nodeCheckOrganization,
                beforeExpand: nodeExpandOrganization
            },
            view: {
                fontCss: setFontCss,
            }
        };
        zTree = $.fn.zTree.init($("#exchange_tree"), setting, result.data);
    };
    //设置未授权或者离线节点的样式
    function setFontCss(treeId, treeNode)
    {
        return treeNode.chkDisabled ? {color : "grey"} : {};
    };
    //勾选代理端节点
    var nodeCheckOrganization = function (treeId, id, treeNode) {
        nodeExpandOrganization(treeId, treeNode);
    }
    //点击代理端节点
    var nodeClickOrganization = function (treeId, treeNode) {
        nodeExpandOrganization(treeId, treeNode);
    }
    var nodeExpandOrganization = function (treeId, treeNode) {
        if (treeNode.chkDisabled) {
            return;
        }
        var allNodes = zTree.getCheckedNodes(true);
        for (var i = 0; i < allNodes.length; i++) {
            zTree.checkNode(allNodes[i], false, false, false);
        }
        zTree.checkNode(treeNode, true);
        if (treeNode.checked) {
            $('.user-select-div').show();
        } else {
            $('.user-select-div').hide();
        }
        // 选择原组织 可以选两种恢复方式 选择其他组织  只有新建恢复
        data.recovery_position = 2;
        $('.recovery-way-div').show();
        if (treeNode.src_organization_flag && treeNode.checked) {//原组织
            data.recovery_position = 1;//恢复到： 1原组织  2其他组织
            $('#recovery-way-div').find('option[value ="1"]').show();
        } else {
            $('#recovery-way-div').find('option[value ="1"]').hide();
            $('#recovery-way-div').val(0);
        }
        recoveryWayChange();
        //检测组织状态
        checkOrganizationStatus(treeNode);
        if (forbidUserSelect) {  //forbidUserSelect是指定用户被禁用
            $('#userSelect').val(LANG.UI_MICROSOFT365_NOT_SPECIFY_USER);
            $('#userSelect').attr({'destination_user_uuid': '', 'destination_mail': '', 'destination_user_type': 0});
        }
        //获取组织下的用户
        initUserTree(treeNode);
        //传输网络
        initNetwork(treeNode);
    }

    var initUserTree = function (treeNode) {
        var allData = [
            {"name": treeNode.name, "id": treeNode.id, "pId":0,"isParent": true,"open":true,"event_type" :10000,"organization_uuid": treeNode.id,"nocheck":true},
            {"name": LANG.UI_MICROSOFT365_USER, "id": treeNode.id + '1000', "pId":treeNode.id,"isParent": true,"event_type" :1000,"organization_uuid": treeNode.id,"nocheck":true},
            {"name": LANG.UI_MICROSOFT365_USER_GROUP, "id": treeNode.id + '1001', "pId":treeNode.id,"isParent": true,"event_type" :1001,"organization_uuid": treeNode.id,"nocheck":true}
        ];
        var setting = {
            check: {
                enable: true,
                nocheckInherit: false,
            },
            data: {
                simpleData: {
                    enable: true,
                },
            },
            callback: {
                onClick: userNodeClick,
                onCheck: userNodeCheck,
                onExpand: userNodeExpand,
            },
        };
        userTree = $.fn.zTree.init($("#userTree"), setting, allData);
    }

    var userNodeCheck = function (e, treeId, treeNode) {
        //单选
        let allNodes = userTree.getCheckedNodes(true);
        for(let i = 0; i < allNodes.length; i++){
            if(treeNode.id != allNodes[i].id) {
                userTree.checkNode(allNodes[i], false);
            }
        }
        userTree.checkNode(treeNode, treeNode.checked);
        userNodeExpand(e, treeId, treeNode);
    }

    var userNodeClick = function (e, treeId, treeNode) {
        if (treeNode.more) {//加载更多
            getMoreNode(treeNode);
            return;
        }
        userNodeExpand(e, treeId, treeNode);
    }
    var userNodeExpand = function (e, treeId, treeNode) {
        if ((treeNode.level != 2 && treeNode.children) || treeNode.level == 2) {
            return;
        }
        //获取用户信息
        let params = {
            'organization_uuid': treeNode.organization_uuid,
            'start_num':0,
            'limit_num':limit_num,
            'event_type':treeNode.event_type,
        };
        Metronic.blockUI({target: '#userModal',animate: true});
        pAjaxRequest(params, "/api/v1/exchange/jobs/organization", "GET", function (result) {
            if (result.success) {
                userTree.removeChildNodes(treeNode);
                userTree.addNodes(treeNode, result.data, true);
                userTree.expandNode(treeNode, true, true, true);
            }
            Metronic.unblockUI('#userModal');
        }, false);
    }

    var getMoreNode = function (treeNode) {
        let params = {
            'organization_uuid': treeNode.organization_uuid,
            'start_num':treeNode.start_num,
            'limit_num':limit_num,
            'event_type':treeNode.event_type,
        };
        Metronic.blockUI({target: '#userModal',animate: true});
        pAjaxRequest(params, "/api/v1/exchange/jobs/organization", "GET", function (result){
            if (result.success) {
                userTree.removeNode(treeNode);
                userTree.addNodes(treeNode.getParentNode(), result.data, true);
                userTree.expandNode(treeNode, true, true, true);
            }
            Metronic.unblockUI('#userModal');
        }, true);
    }

    var initNetwork = function (treeNode) {
        //是否需要显示传输网络标记
        networkFlag = false;
        $('.transport-encrypt-div').hide();
        $('.transfer-encrypt-method-form').hide();
        $('.transferLi').hide();
		$('.transferDiv').hide();
        data.transferlishow  = false;
        networkFlag = treeNode.net_model;   //是否显示传输网络
        if (treeNode.region == 100) {//sever组织
            //显示加密传输
            $('.transport-encrypt-div').show();
            //显示选择客户端
            initServerAgent(true, treeNode.agent_uuid_list);
            data.showAgentFlag = true;
            data.transferlishow  = true;
        } else {
            initServerAgent(false, treeNode.agent_uuid_list);
            data.showAgentFlag = false;
            data.transferlishow  = false;
        }
        //初始化传输网络
        if (networkFlag) {
            $('.transfernetworkDiv').show();
            data.transferlishow  = true;
            initNetworkList();
        } else {
            $('.transfernetworkDiv').hide();
        }
        //如果多线程已授权或者其他传输策略信息是显示的，显示传输策略
		if(CONF.FUNCTIONS.includes('multithread') || data.transferlishow){
			$('.transferLi').show();
			$('.transferDiv').show();
		}
        if (storage_type == 10) {
            //磁带只支持单线程，不显示线程配置
            $('#recoveryThreadDiv').spinner("value", 1);
            $('.recoveryThreadNumDiv').hide();
            //磁带屏蔽安全策略
			$('.safeLi').removeClass('active').hide();
            $('#tab_safety').removeClass('active');
			$('.safeDiv').hide();
            if (!data.transferlishow) {
                $('.transferLi').hide();
                $('.transferDiv').hide();  
            }
        } else {
            $('#recoveryThreadDiv').spinner("value", 3);
            $('.recoveryThreadNumDiv').show();
            $('.safeLi').show();
			$('.safeDiv').show();
        }
        if (!CONF.FUNCTIONS.includes('integrity')) {
            $('.safeLi').hide();
			$('.safeDiv').hide();
        }
        $('.tab3-nav > li').removeClass('active');
		$('.tab3-nav > li').eq(0).addClass('active');
		$('.tab3-content > .tab-pane').removeClass('active in');
		$('.tab3-content > .tab-pane').eq(0).addClass('active in');
    }

    var initServerAgent = function (showAgentFlag,agentList) {
        if (showAgentFlag) {
            $('.agentConfigDiv').show();
            $('#agentConfig').bootstrapSwitch('state', false);//默认关
            pAjaxRequest({}, "/api/v1/office365/organization/agent", "GET", function (result) {
                $('#agentList').html(result.data);
                $('#agentList option').filter(function() {
                    return !agentList.includes($(this).val());
                }).remove();
            });
        } else {
            $('.agentConfigDiv').hide();
            $('.agentListDiv').hide();
        }
    }

    //检查组织是否离线或者未授权
    var checkOrganizationStatus = function (treeNode) {
        // if(treeNode.chkDisabled){
        //  UIToastr.showWarning(LANG.UI_FILE_CLIENT_OFFLINE_OR_NOAUTHORIZED, LANG.UI_FILE_CLIENT_OFFLINE_OR_NOAUTHORIZED_TIPS);
        // }
    }

    var step2Valid = function () {
        var selectNode = zTree.getCheckedNodes(true);
        if (selectNode.length == 0) {
            UIToastr.showWarning(LANG.UI_MICROSOFT365_RECOVERY_TARGET,LANG.UI_MICROSOFT365_SELECT_TARGET_ORGANIZATION)
        }
        if (data.recovery_position == 2) {//恢复到其他组织
            data.overwrite = 0;
        } else {
            data.overwrite = $('#recovery-way-div').val();//0新建恢复 1覆盖恢复
            data.destination_user_uuid = "";
        }
        data.destination_organization_uuid = selectNode[0].id;
        data.destination_user_type = parseInt($('#userSelect').attr("destination_user_type"));//恢复目标用户是用户还是用户组
        data.destination_user_uuid = $('#userSelect').attr("destination_user_uuid");
        data.destination_mail = $('#userSelect').attr("destination_mail");
        showStep2();
        getTimeDes();
        initResourceLimit([data.node_uuid]);
         //初始化安全策略
         $('#completeConfig').completeStrategyCovery(CONF.MODULE_TYPE.M365, 0 , !data.integrity_check_flag );
        return true;
    }

    var showStep2 = function () {
        var recoverwayshow = '';
        var recoverdesshow = '';
        var recoverusershow = '';
        if (data.overwrite == 0) {//0新建恢复 1覆盖恢复
            recoverwayshow = LANG.UI_MICROSOFT365_NEW_RECOVERY
        } else {
            recoverwayshow = LANG.UI_MICROSOFT365_COVER_RECOVERY
        }
        //recoverdes恢复目标
        if (data.recovery_position == 1) {//恢复到： 1原组织  2其他组织
            recoverdesshow = src_organization;
        } else {
            recoverdesshow = zTree.getCheckedNodes(true)[0].name;
        }
        //目标用户
        recoverusershow = $('#userSelect').val();
        //恢复方式
        $('.recoverway').html(recoverwayshow);
        // 恢复目标
        $('.recoverdes').html(recoverdesshow);
        //目标用户
        $('.recoveruser').html(recoverusershow);
    }

    var step3Valid = function () {
        //恢复方式
        data.type_info.type = parseInt($('#recovertype').val());//恢复方式
		data.type_info.strategy.start_time = $('#oncetime').val(); //定时恢复时间
		data.type_info.strategy.type = data.type_info.type;
		if (data.type_info.type == 2 && data.type_info.strategy.start_time == '') {//1立即恢复  2定时恢复
			UIToastr.showWarning(LANG.UI_GLOBAL_STRATEGY_TIME,LANG.UI_BACKUP_SET_TIME_TIPS);
			return false;
        }
        
        if(CONF.FUNCTIONS.includes('multithread')){
            data.thread_num = $('#recoveryThreadNum').val();//传输线程
            if (data.thread_num > 16 || data.thread_num < 1 || data.thread_num == ""
                || !/^\d+$/.test(data.thread_num)) {
                //重置为默认值
                $('#recoveryThreadDiv').spinner("value", 3);
                $('.recoveryHighDes').html(LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $('#recoveryThreadNum').val());
                UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_MICROSOFT365_THREAD_TIPS);
                return false;
            }
        } else {
            data.thread_num = 1;
        }
        //传输策略
        data.type_info.high.transfer.encrypt = $('#transport_encrypt_flag').get(0).checked;
        // 传输加密算法
        data.type_info.high.transfer.encrypt_method = parseInt($('#transferEncryptMethod').val());
        // 高级配置
        if (!getHighConf()) {
            return false;
        }
        showStep3();
        return true;
    }

    //得到高级配置信息
    var getHighConf = function () {
        if ($('#retryTimeInput').val() < 1 || $('#retryTimeInput').val() > 30) {
            $('#retryTimeInput').val(5);
            UIToastr.showWarning(LANG.UI_BR_AUTOBAK_SETTING,LANG.UI_MICROSOFT365_RETRY_TIME_TIPS1);
            return false;
        }
        if ($('#retryNumInput').val() < 1 || $('#retryNumInput').val() > 5) {
            $('#retryNumInput').val(3)
            UIToastr.showWarning(LANG.UI_BR_AUTOBAK_SETTING,LANG.UI_MICROSOFT365_RETRY_TIME_TIPS2);
            return false;
        }
        // 忽略节点资源限制
		data.high_conf.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
		$('.ignoreResourceLimitShow').html($('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.high_conf.ignore_resource_limiting_flag));
        //重试策略
        data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
        if (!data.retry_strategy) {
			return false;
		}
         //安全策略
		let completeConfig = $('#completeConfig').getCompleteStrategyCovery();
        if (!CONF.FUNCTIONS.includes('integrity')) {
            completeConfig = '';
        }
		data.safe_strategy = safeData('', '', completeConfig);
		$('.safeStrategyShow').html(completeConfig.str);
        //客户端配置
        if ($('#agentConfig').get(0).checked) {
            data.high_conf.agent_uuid = $('#agentList').val();
        } else {
            data.high_conf.agent_uuid = "";
        }
        return true;
    }

    var showStep3 = function () {
        // 恢复方式
        let reservetypeshow = '';
        reservetypeshow = $('.recoveryTimeDes').text();
        $('.reservetypeshow').html(reservetypeshow);
        //限速策略
        data.speedLimit = getSpeedStrategyInfo();
        var speedlimitshow = $('.speedlimitshow');
        var speedLimitsStr = '';
        if (data.speedLimit.speedInfo && data.speedLimit.speedInfo.length != 0) {
            speedLimitsStr = '';
            for (let i = 0; i < data.speedLimit.speedInfo.length; i++) {
                speedLimitsStr += data.speedLimit.speedInfo[i].des + '<br>';
            }
        }
        if (speedLimitsStr == '') {
            speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
        }
        speedlimitshow.html(speedLimitsStr);
        //传输策略
        let transferdes = '';
        if ($('.transfernetworkDiv').css('display') != 'none') {//加密传输未被隐藏
            transferdes += LANG.UI_COPY_BACK_ENCRYPT + ': ' + getSwitchDes(data.type_info.high.transfer.encrypt) + "<br>";
            if ($('#transport_encrypt_flag').get(0).checked) {
                let encryptedMethodLabel = $('.transfer-encrypt-method-label').html();
                let method = parseInt($('#transferEncryptMethod').val());
                let grade = '';
                switch (method) {
                    case 1:
                        grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
                        break;
                    case 2:
                        grade = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
                        break;
                };
                transferdes += encryptedMethodLabel + ": " + grade + "<br>";
            }
        }
        //客户端配置
        if (data.showAgentFlag && data.high_conf.agent_uuid != "") {
            transferdes += $('.agentConfiglabel').html() + '：' + LANG.UI_PUBLIC_ON + '<br>';
            transferdes += $('.agentListLabel').html() + '：' + $('#agentList option:selected').text() + '<br>';
        } else if (data.showAgentFlag && data.high_conf.agent_uuid == "") {
            transferdes += $('.agentConfiglabel').html() + '：' + LANG.UI_PUBLIC_OFF + '<br>';
        }
        //传输网络
        if ($('.transfernetworkDiv').css('display') == 'block') {
            let networkNode = $('#transferNetworkTree').transferNetwork('getSelect');
			transferdes += LANG.UI_VOL_CDP_RECOVER_TRANSPORT_NET + ': ' + networkNode.str + '<br>';
            data.type_info.high.transfer.network = networkNode.network_uuid
        }
        if(CONF.FUNCTIONS.includes('multithread')){
			transferdes += $('.threadnumlabel').html() + ": " + data.thread_num;
		}
        $('.transfershow').html(transferdes);
        //高级配置
        if (data.high_conf.retry_flag) {
            $('.retrytimeshow').show();
            $('.retrynumshow').show();
            $('.retrytimeshow').html($('.retrytimelabel').html() + '：' + data.high_conf.retry_delay_time / 60 + LANG.UI_MICROSOFT365_MINUTE);
            $('.retrynumshow').html($('.retrynumlabel').html() + '：' + data.high_conf.retry_count + LANG.UI_MICROSOFT365_TIME);
        } else {
            $('.retrytimeshow').hide();
            $('.retrynumshow').hide();
        }
        $('.autoretryshow').html($('.autoretrylabel').html() + '：' + getSwitchDes(data.high_conf.retry_flag));
    }
    //得到开关的结果描述   开启/关闭
    var getSwitchDes = function (check) {
        if (check) {
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
    }

    var getStrategyDes = function (strategy) {
        //每天12:12:12开始,不滚动
        //每天12:12:12开始,滚动间隔01:11:11,滚动结束时间23:11:11
        //每周1,2,3,4,5,6,
        var des = "";
        if (CONF.STRATEGY_TYPE.DAY == strategy.type) {
            des += LANG.UI_STRATEGY_DAY + getEachStrategy(strategy);
        } else if (CONF.STRATEGY_TYPE.WEEK == strategy.type) {
            if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                des += LANG.UI_STRATEGY_WEEK + getStrategyDays(strategy.days) + getEachStrategy(strategy);
            } else {
                des += LANG.UI_STRATEGY_WEEK + getStrategyWeek(strategy.days) + getEachStrategy(strategy);
            }
        } else if (CONF.STRATEGY_TYPE.MONTH == strategy.type) {
            des += LANG.UI_STRATEGY_MONTH + getStrategyDays(strategy.days) + getEachStrategy(strategy);
        } else if (CONF.STRATEGY_TYPE.GLOBAL == strategy.type) {
            des += LANG.UI_STRATEGY_GLOBAL;
        } else {
            des += LANG.UI_PUBLIC_NOTHING + "<br><br>";
        }
        return des;
    }

    var getEachStrategy = function (strategy) {
        var desEach = '';
        desEach += strategy.startTime;
        desEach += LANG.UI_STRATEGY_START + ", ";
        if (strategy.rollFlag) {
            desEach += LANG.UI_STRATEGY_ROLL_INTERVAL + strategy.rollInterval + ", " + LANG.UI_STRATEGY_ROLL_OVER_TIME + strategy.endTime;
        } else {
            desEach += LANG.UI_STRATEGY_ROLL_NO;
        }
        desEach += "<br><br>";
        return desEach;
    }

    var getStrategyDays = function (days) {
        var desDays = '';
        $.each(days, function (i,d) {
            if (1 == d) {
                var day = i + 1;
                if (CONF.LANGUAGE == "zh-cn" || CONF.LANGUAGE == "zh-tw") {
                    desDays += day + ", ";
                } else {
                    desDays += "Day" + day + ", ";
                }
            }
        });
        return desDays;
    }
    //获取每周显示日期
    var getStrategyWeek = function (days) {
        var desDays = '';
        $.each(days, function (i,d) {
            if (1 == d) {
                desDays += CONF.WEEK[i] + ", ";
            }
        });
        return desDays;
    }

    var submit = function () {
        if ('' == $.trim($("#jobname").val())) {
            $('.jobnametip').html(LANG.UI_RECOVERY_RENAME).show();
            return;
        }
        $('.jobnametip').hide();
        let jobName = $.trim($("#jobname").val());
        // 输入验证
        if(!customInputValidate('string',jobName)){
            return false;
        }
        data.job_name = $.trim($("#jobname").val());
        //TODO提交
        Metronic.blockUI({target: '#exchange-recover-content',animate: true, cenrerY: true,});
        pAjaxRequest(data, "/api/v1/exchange/jobs/restore", "POST", function (result) {
            Metronic.unblockUI('#exchange-recover-content');
            if (operateResponseList(result, LANG.UI_MICROSOFT365_CREATE_RECOVERY_TASK)) {
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }
        }, false);

    }

    //初始化节点传输网络列表
    var initNetworkList = function () {
		$('#transferNetworkTree').transferNetwork({node_uuid: data.node_uuid});
    }

    var formatterData = function (value,data,row) {
        if (value == undefined || value == "") {
            return '<span title="--">--</span>';
        } else {
            if (data.dir_type == 3) {//时间点不显示图标
                return '<span title="' + data.title + '">' + data.title + '</span>';
            }
            return '<span title="' + value + '">' + value + '</span>';
        }
    }

    return {
        //main function to initiate the module
        init: function () {
            initStorageSelect();
            wizardInit();
            initPointTree();
            initListener();
        },
    };
}();

jQuery(document).ready(function () {
    ExchangeRecover.init();
});