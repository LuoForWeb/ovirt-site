var FileCopyTask = function () {
    var data = {};
    var _timeStamp = '';    //时钟时间戳,全局
    var oldNode, networkFlag = false;   //用于比对加载传输网络的节点
    var _pageSize = 20;//一次性请求多少个文件或者目录
    var dirTree,pathTree,recycleBinTree;//第一步的源端文件树、目标端目录树、回收站树
    let _daterangepicker_starttime = "";
	let _daterangepicker_endtime = "";
    let EDIT_FLAG = false; //修改页面标识
    let OLD_INFO = ''; //修改任务需要使用的旧任务信息
    var speedList = [];//修改任务需要使用的限速策略
    let currentmax = 0; // 修改备份任务时记录步骤条当前最大下标
    let pageIndex = 0; //轮播索引
    let newCount = 1;//新增目标端文件夹使用
    let share_path = ''//共享路径,用于nas设备的目录判断 
    let filenameFlag = true;

    var initData = function () {
        //通用策略
        data.copy_info = {}
        //传输策略
        data.transfer_info = {}
        // 高级配置
        data.high_info = {};
        //目标节点
        data.node_info = {};
        // 策略默认配置
	    const defaultConfig = {
	    	time: false,
	    	store: false,
	    	reserve: false,
            speed: true,
            module_type: CONF.MODULE_TYPE.FILE_COPY,
	    };
        if (EDIT_FLAG) {
            data.copy_info.copy_interval = timeToSeconds(OLD_INFO.time_strategy[0].roll_interval);
            data.copy_info.first_copy_time = OLD_INFO.time_strategy[0].first_start_time;
            data.copy_info.source_uuid = OLD_INFO.source_uuid;
            data.copy_info.source_type = OLD_INFO.source_type;
            data.copy_info.target_uuid = OLD_INFO.target_uuid;
            data.copy_info.target_type = OLD_INFO.target_type;
            data.transfer_info.compress_transport_flag = OLD_INFO.transport_strategy.compress_flag_value;
            data.transfer_info.transport_encrypt_flag = OLD_INFO.transport_strategy.encrypt_flag_value;
            data.transfer_info.transport_encrypt_method = OLD_INFO.transport_strategy.encrypt_method;
            data.transfer_info.compress_method = OLD_INFO.transport_strategy.compress_method;
            data.high_info.snapshot = OLD_INFO.snap_shot_flag
            data.high_info.copy_file_permission = OLD_INFO.permission_operate_flag;
            data.high_info.sync_self_flag = OLD_INFO.filter_info.sync_self_flag == 1 ? true : false;
            data.high_info.backup_thread_num = OLD_INFO.thread_num;
            data.high_info.scan_thread_num = OLD_INFO.scan_thread_number;
            data.high_info.scan_file_num = OLD_INFO.scan_file_speed;
            data.high_info.skip_file_alarm_flag = OLD_INFO.skip_file_alarm_flag;
            data.high_info.skip_file_alarm_min_num = OLD_INFO.skip_file_alarm_min_num;
            data.high_info.skip_file_alarm_min_ratio = OLD_INFO.skip_file_alarm_min_ratio;
            data.high_info.same_file_strategy = OLD_INFO.same_file_strategy;
            data.high_info.check_mode = OLD_INFO.filter_info.check_mode;
            data.high_info.file_verification_algorithm = 1;//直接默认就是MD5
            data.high_info.sync_rule = OLD_INFO.filter_info.sync_rule;
            data.high_info.wildcard_list = OLD_INFO.filter_info.wildcard_list;
            $.each(data.high_info.wildcard_list.wildcard, function(index, item) {
                if (item.wildcard) {
                    data.high_info.wildcard_list.wildcard[index].wildcard = signEncrypt(item.wildcard);// 对 wildcard 字符串进行 base64 加密
                }
            });
            data.high_info.time_range_list = OLD_INFO.filter_info.time_range_list;
            data.retry_strategy = OLD_INFO.retry_strategy;
            data.node_info.node_uuid = OLD_INFO.storage_info.node_uuid;
            data.node_info.node_pool_uuid = OLD_INFO.storage_info.node_pool_uuid;
            defaultConfig.strategy = {
                speedlimit: OLD_INFO.speed_info
            };
            //过载保护
            data.high_info.ignore_resource_limiting_flag = OLD_INFO.ignore_resource_limiting_flag;
        }
        //使用这个插件主要是为了使用限速策略
        $('#backupStrategyDiv').initBackupStrategy(defaultConfig);
    }

    //初始化表格
    var initDataTable = function(){
        var beforeDiv = '';
        beforeDiv = '<div style="cursor:not-allowed;"><button class="btn viconfont vicon-a-Deleteshanchu1 b-btn brr2 mr12 exch-forbid-event" id="deleteData"></button></div>';
        var options  = {
            toolbarId: '#vin_file_copy_toolbar',
            buttonsToolbar: '#vin_file_copy_toolbar .vin_btnToolbar',
            searchInput: false, //搜索框
            pagination:false,
            customTool: {
                beforeInput: beforeDiv,
            },
            data: [],
            resizable: false,
            columns:[{
                checkbox:true,
                sortable: false,
            },
                {
                    field: 'src_file',
                    title: LANG.UI_FILE_COPY_SRC_PATH,
                    sortable: false,
                    align: 'center',
                    formatter: function (value,row,index,field) {
                        var html = '';
                        for (var i = 0; i < row.source_path_list.length; i++) {
                            switch (parseInt(row.source_path_list[i].file_type)) {
                                case 2:
                                case 3:
                                    var icon = '<i class="viconfont vicon-a-Folder-closewenjianjia-guan mr5"></i>';
                                    html += icon + $('<div>').text(row.source_path_list[i].file_path).html() + '<br/>';
                                    break;
                                default:
                                    var icon = '<i class="viconfont vicon-a-Notesbiji mr5"></i>';
                                    html += icon + $('<div>').text(row.source_path_list[i].file_path).html() + '<br/>';
                                break;
                            }
                        }
                        return html;
                    },
                },
                {
                    field: 'des_path',
                    title: LANG.UI_FILE_COPY_DES_PATH,
                    sortable: false,
                    align: 'center',
                    formatter: function (value,row,index,field) {
                        
                        var icon = '<i class="viconfont vicon-a-Folder-closewenjianjia-guan mr5"></i>';
                        var html = icon + $('<div>').text(value).html();
                        return html;
                    },
                },
            ],
            onPostBody: function() {
                modifyDelStyle('fileCopyTable', 'deleteData');
            },
            onCheck: function () {
                modifyDelStyle('fileCopyTable', 'deleteData');
            },
            onUncheck: function () {
                modifyDelStyle('fileCopyTable', 'deleteData');
            },
            onCheckAll: function () {
                modifyDelStyle('fileCopyTable', 'deleteData');
            },
            onUncheckAll: function () {
                modifyDelStyle('fileCopyTable', 'deleteData');
            },
        }
        $('#fileCopyTable').baseTableConfig().init(options);
        modifyDelStyle('fileCopyTable', 'deleteData');
        //绑定事件
        toBindEvent();
        if(EDIT_FLAG) {
            initStep1OldInfo();
        }
    }
    var toBindEvent = function () {
        //删除数据
        $('#deleteData').on('click', function () {
            //所有需要删除的ID
            const selectedRow = $('#fileCopyTable').bootstrapTable('getSelections');
            const idsToRemove = selectedRow.map(item => item.id);
            // 批量删除
            $('#fileCopyTable').bootstrapTable('remove', {
                field: 'id',
                values: idsToRemove,
            });
            checkTableIsEmpty();
        });
    }

    //初始化源端和目标端下拉框
    var initAgentList = function () {
        Metronic.blockUI({target:'.copy-list',animate: true});
        pAjaxRequest({}, "/api/v1/filecopy/resource", "GET", function (result) {
            $('.src-path-tree').hide();
            $('.des-path-tree').hide();
            if (result.success) {
                $('#noSrcAgent').hide();
                $('#noDesAgent').hide();
                // $('.file-copy-tree').show();
                $('#toSelectSrcTips').show();
                $('#toSelectDesTips').show();
                // $('.src-path-tree').hide();
                // $('.des-path-tree').hide();
                populateSelect(result.data,$('#srcAgent'),1);
                populateSelect(result.data,$('#desAgent'),2);
                $(".selectpicker").selectpicker({
                    liveSearch: true, //是否显示搜索框,
                    actionsBox: false,//是否显示全部
                    noneSelectedText: LANG.BILLING_PLEASE_SELECT,
                    deselectAllText: LANG.BILLING_DESELECT_ALL,
                    selectAllText: LANG.BILLING_SELECT_ALL,
                    liveSearchPlaceholder: LANG.BILLING_SEARCH,
                });
                if(EDIT_FLAG) {
                    $('#srcAgent').selectpicker('val',OLD_INFO.source_uuid);
                    $('#desAgent').selectpicker('val',OLD_INFO.target_uuid);
                    initSrcPath();
                    initDesDir();
                }
            } else {
                $('#noSrcAgent').show();
                $('#noDesAgent').show();
            }
            Metronic.unblockUI('.copy-list');
            initDataTable();
        });
    }
    // 按 event_type 分组数据
    var populateSelect = function(data,div,type) {
        $(div).empty(); // 清空已有的选项
        $(div).html('<option class="select-tips'+ type +'" value="">'+ LANG.UI_JOB_SELECT +'</option>');
        var groupedData = {};
        data.forEach(function(item) {
            var eventType = item.event_type;
            if (!groupedData[eventType]) {
                groupedData[eventType] = [];
            }
            groupedData[eventType].push(item);
        });
        // 添加各组数据
        addOptions(LANG.UI_PUBLIC_CLIENT, groupedData.agent,div);
        addOptions(LANG.UI_NAS_BAK_DETAIL_HIS, groupedData.nas,div);
        //暂时屏蔽hadoop和对象存储
        // addOptions(LANG.UI_VISUAL_HADOOP_BACKUP, groupedData.hadoop_cluster,div);
        // addOptions(LANG.UI_VISUAL_OBS, groupedData.obsItem,div);
    }

    // 添加 option 和 optgroup
    var addOptions =  function(groupLabel, items,div) {
        if (items && items.length > 0) {
            var $optgroup = $('<optgroup>', { label: groupLabel });
            items.forEach(function(item) {
                var disabledFlag = item.chkDisabled || item.chk_disabled;
                if (EDIT_FLAG) {
                    disabledFlag = false;
                }
                var $option = $('<option>', {
                    value: item.uuid,
                    text: item.name,
                    event_type: item.event_type,
                    path: item.path ?? '',
                    agent_uuid:item.agent_uuid ?? '',
                    group_uuid:item.pId ?? '',
                    share_path:item.sharepath ?? '',
                    os_type:item.os_type ?? '',
                    vendor:item.vendor ?? '',
                    chk_disabled: item.chkDisabled || item.chk_disabled,
                    disabled: disabledFlag
                });
                $optgroup.append($option);
            });
            $(div).append($optgroup);
        }
    }
    //初始化界面所有内容
    var initPage = function() {
        let job_uuid = $('#job_uuid').val();
        if (job_uuid != '') {
            EDIT_FLAG = true;
            initEditInfo(job_uuid);
            data.job_uuid = job_uuid;
        } else {
            initListener();
        }
        wizardInit();
        inintDatatimePicker();
        initData();
        initAgentList();//初始化源端和目标端下拉框
        initJobName();
    }

    var initJobName = function() {
        //任务名
        pAjaxRequest({"job_name":LANG.UI_FILE_COPY_TASK}, "/api/v1/exchange/jobs/backup/task_name", "GET", function (result) {
            if (result.success) {
                $('#file_copy_job_name').val(result.data);
            }
        }, false);
        if(EDIT_FLAG) {
            $('#file_copy_job_name').val(OLD_INFO.job_name);
        }
    }

    var initListener = function () {
        $('#toAddAgent').on('click',function () {
            LOCATION('./content/client/client.php', 'client');
        });
        $('#toAddNas').on('click',function () {
            LOCATION('./content/nas/nasmanager.php', 'nas');
        });
        $('#toAddHadoop').on('click',function () {
            LOCATION('./content/hadoop/hadoop_cluster.php', 'hadoop_cluster');
        });
        $('#toAddObs').on('click',function () {
            LOCATION('./content/s3/obsmanager.php', 'obsmanager');
        });
        $('#srcAgent').on('change', initSrcPath);
        $('#desAgent').on('change', initDesDir)
        //添加至列表
        $('#addToList').on('click',function () {
            addToList();
        });
        //初始化首次复制启动时间
        initFirstCopyTime();
        initSpinner();
        $('#transport_encrypt_flag').on('switchChange.bootstrapSwitch', transferEncryptChange);
        //初始化重试策略
        if(EDIT_FLAG) {
            $('#retry_config').retryStrategy({'retry_strategy': OLD_INFO.retry_strategy},'edit');
        } else {
            $('#retry_config').retryStrategy();
        }
        $('#task_retry_object').parent().parent().hide();//隐藏重试对象
        $('#copy_del').on('switchChange.bootstrapSwitch', copyDelChange);
        $('#del_reserve').on('change', delReserveChange);
        $('#toBrowse').on("click",function () {
            $('#recycleBinModal').modal({'width':'500px', 'height':'350px'});
        });
        $('.recycleBinSubmit').on("click",function () {
            var nodes = recycleBinTree.getCheckedNodes(true);
            if (nodes.length <= 0) {
                UIToastr.showWarning(LANG.UI_FILE_COPY_RECYCLE_BIN_SELECT_POSITION,LANG.UI_FILE_COPY_RECYCLE_BIN_SELECT_POSITION_PLEASE);
                return;
            }
            $('#recycle_bin_path').val(nodes[0].dir_path);
            $('#recycleBinModal').modal('hide');
        });
        $('#scanThreadNum').on('input propertychange', function(){
        	intTransThreadNum();
        });
        $('.scanThreadDiv .spinner-up').on('click', function(){
        	intTransThreadNum();
        });
        $('.scanThreadDiv .spinner-down').on('click', function(){
        	intTransThreadNum();
        });
        $('#addWildcardToList').on('click', function(){
        	addWildcardClick();
        });
        //跳过文件告警智能判断
        $('#passfilealarmcheck').on('switchChange.bootstrapSwitch', passAlarmChange);
        //定义icheck样式
        $('#tab_other input[type=radio]').iCheck({
			checkboxClass: 'icheckbox_square-blue',
			radioClass: 'iradio_square-blue',
			increaseArea: '20%' // optional
		});
        //给动态生成的删除按钮绑定点击事件
        $('#all_advance_config').on('click','.label-danger', function(){
            deleteLabelClick($(this));
        });
        // 压缩传输
		$('#compress_transport_flag').on('switchChange.bootstrapSwitch', compressChange);
        //选择时间范围单选框
		$('#rangeMode').find('input').on('ifClicked', recentRangeClick);
        //通配符配置-选中了才显示下面的
		$('#wildcardMatchMode').find('input').on('ifClicked', showWildcardClick);
        //时间配置-选中了才显示下面的
		$('#timeMatchMode').find('input').on('ifClicked', showRangeClick);
        $('#sameFile').on('change', sameFileChange);
        $('#check_mode').on('change', checkModeChange);

    }

    var checkModeChange = function() {
        var check_mode = $('#check_mode').val();
        if (check_mode == 2) {
            $('.verification-algorithm-form').show();
        } else {
            $('.verification-algorithm-form').hide();
        }
    }

    var sameFileChange = function() {
        var type = parseInt($('#sameFile').val());
        switch (type) {
            case 1: // 覆盖
                $('.recover-way-form').show();
                checkModeChange();
                break;
            case 3: // 跳过
            case 4: // 重命名
                $('.recover-way-form').hide();
                break;
            default:
                break;
        }
    }

    //时间范围
    var recentRangeClick = function(){
		var mode = $(this).data('mode');
		if(mode==1) {//最近几天
			$('.recent-days-form').show();
			$('.recent-range-form').hide();
            //隐藏并清空固定时间范围相关内容
            $('#timeInputRange').val('');
            _daterangepicker_starttime = "";
			_daterangepicker_endtime = "";
            $('.time-range-info-div').hide();
            $('#timeRangeList').html('');
		}else{//最近一段时间内
			$('.recent-days-form').hide();
			$('.recent-range-form').show();
		}
	}

    var showWildcardClick = function(){
        var mode = $(this).data('mode');
        switch (mode) {
            case 0:
                $('.wildcard-form').hide();
                break;
            case 1:
            case 2:
                $('.wildcard-form').show();
                break;
        }
	}

    var showRangeClick = function(){
        var mode = $(this).data('mode');
        switch (mode) {
            case 0:
                $('.time-rang-form').hide();
                break;
            case 1:
            case 2:
                $('.time-rang-form').show();
                break;
        }
	}

    // 显示压缩等级
	var compressChange = function () {
		if ($('#compress_transport_flag').get(0).checked) {
			$('.compressGradeDiv').show();
		} else {
			$('.compressGradeDiv').hide();
		}
	};

    var getSubModuleType = function(des) {
        var type = '';
        switch (des) {
            case 'agent':
                type = 1;
                break;
            case 'nas':
                type = 2;
                break;
            case 'hadoop_cluster':
                type = 3;
                break;
            case 'obs_agent':
                type = 4;
                break;
            default:
                type = 0;
                break;
        }
        return type;
    }

    var addTimeRangeClick = function(input) {
        //检测时间格式
        // var reg = /^[1-9]\d{3}-(0[1-9]|1[0-2])-(0[1-9]|[1-2][0-9]|3[0-1])\s+(20|21|22|23|[0-1]\d):[0-5]\d:[0-5]\d$/
		// if (!reg.test(input)) {
        //     UIToastr.showWarning("时间配置","请输入正确格式的时间！");
        //     return;
		// }
        if (input == '') {
            $('#timeInputRange').css('border-color', "red");
            return;
        } else {
            $('#timeInputRange').css('border-color', "#E6E6E6");
            $('.time-range-info-div').show();
            var des = '';
            des += LANG.UI_HISTORY_JOB_DETAIL_FILE_EDIT_TIME_RANGE + ':' + input;
            $('#timeRangeList').append(`
                <div class="bg_grey" title="` + des + `" 
                    time_fliter_time_start="` + _daterangepicker_starttime + `"
                    time_fliter_time_end="` + _daterangepicker_endtime + `">
                    ` + des + `
                    <div class="label label-sm label-danger">
                        <i class="viconfont vicon-cuowu"></i>
                    </div>
                </div>
            `);
        }
    }

    var deleteLabelClick = function(div) {
        $(div).parents('div.bg_grey').remove();
        if ($("#timeRangeList").html().trim() == '') {
            $('.time-range-info-div').hide();
        }
        if ($("#wildcardList").html().trim() == '') {
            $('.wildcard-info-div').hide();   
        }
    }

    var addWildcardClick = function() {
        var input = $('#wildcardInput').val().trim();
        if (input == '') {
            $('#wildcardInput').css('border-color', "red");
            return;
        } else {
            //检测通配符是否包含一些特殊字符
			var specialchar = ['/', ':','"','<','>','|','\\'];
			for (var key in specialchar) {
				if (input.indexOf(specialchar[key]) != -1) {
					UIToastr.showInfo(LANG.UI_FILE_WILDCARD_RULES_TIPS1);
					return;
				}
			}
            var des = '';
            $('#wildcardInput').css('border-color', "#E6E6E6");
            $('.wildcard-info-div').show();
            var effectiveObject = $('#effectiveObject').find('option:selected').val();
            des += LANG.UI_HISTORY_JOB_DETAIL_WILDCARD_OBJECT + ':' + $('#effectiveObject').find('option:selected').text() + '，';
            des += LANG.UI_FILE_WILDCARD + ':' + input;
            $('#wildcardList').append(`
                <div class="bg_grey" title="` + des + `" 
                    wildcard="` + input + `" 
                    wildcard_file_type="` + effectiveObject + `">
                    ` + des + `
                    <div class="label label-sm label-danger">
                        <i class="viconfont vicon-cuowu"></i>
                    </div>
                </div>
            `);
        }
    }

    var getWildcardMode = function() {
        var mode = 0;
        if($('#wildcardExcludeCheck').is(':checked')){
			mode = 1;
		}else if($('#wildcardSelectedCheck').is(':checked')){
			mode = 2;
		}
        return mode;
    }

    var getTimeRangeMode = function() {
        var mode = 0;
        if($('#timeExcludeCheck').is(':checked')){
			mode = 1;
		}else if($('#timeSelectedCheck').is(':checked')){
			mode = 2;
		}
        return mode;
    }

    var getTimeRangeWayMode = function() {
        var mode = '';
        if($('#recentRange').is(':checked')){
			mode = 1;
		}else if($('#fixedRange').is(':checked')){
			mode = 2;
		}
        return mode;
    }

    var checkWildcardSameType = function() {
        var tempArray = {wildcard:[]};
        var wildcardList = $('#wildcardList').find('.bg_grey')
        var wildcard_mode = getWildcardMode();
        tempArray.wildcard_mode = wildcard_mode;
        switch (wildcard_mode) {
            case 0: //无
                return tempArray;
            case 1: //排除
            case 2: //选定
                if (wildcardList.length == 0) {
                    return tempArray;
                } else {
                    
                    for(var i = 0; i < wildcardList.length; i++) {
                        var wildcard = $(wildcardList[i]).attr('wildcard');
                        var wildcard_file_type = $(wildcardList[i]).attr('wildcard_file_type');
                        tempArray.wildcard.push({
                            "wildcard": signEncrypt($(wildcardList[i]).attr('wildcard')),
                            "wildcard_file_type": wildcard_file_type,
                            "wildcard_des": wildcard,
                        });
                    }
                }
                break;
        }
        return tempArray;
    }

    var checkTimeRangeSameType = function() {
        var tempArray = {time_list:[]};
        var timeRangeList = $('#timeRangeList').find('.bg_grey')
        var time_fliter_mode = getTimeRangeMode();
        tempArray.time_fliter_mode = time_fliter_mode;
        if (time_fliter_mode == 0) {
            return tempArray;
        }
        var timeRangeWayMode = getTimeRangeWayMode();
        switch (timeRangeWayMode) {
            case 1: //最近时间范围
                var last_days = $('#copyDays').val().trim();
                tempArray.last_days = last_days;
                if (last_days == '') {
                    return tempArray;
                }
                break;
            case 2: //固定时间范围
                if (timeRangeList.length == 0) {
                    return tempArray;
                }
                for(var i = 0; i < timeRangeList.length; i++) {
                    var time_fliter_time_start = padDateString($(timeRangeList[i]).attr('time_fliter_time_start'));
                    var time_fliter_time_end = padDateString($(timeRangeList[i]).attr('time_fliter_time_end'));
                    time_fliter_time_start = new Date(time_fliter_time_start);
                    time_fliter_time_end = new Date(time_fliter_time_end);
                    time_fliter_time_start = time_fliter_time_start.getTime();
                    time_fliter_time_end = time_fliter_time_end.getTime();
                    tempArray.time_list.push({
                        "time_fliter_time_start": time_fliter_time_start,
                        "time_fliter_time_end": time_fliter_time_end,
                    });
                }
                tempArray.last_days = 0;
        }
        return tempArray;
    }

    // 补全日期字符串
    var padDateString = function (dateString) {
        var [datePart, timePart] = dateString.split(' ');
        var [year, month, day] = datePart.split('-').map(Number); // 转换为数字
        var [hour, minute, second] = timePart.split(':');
        // 补全时间部分为两位数字
        hour = hour.padStart(2, '0');
        minute = minute.padStart(2, '0');
        second = second.padStart(2, '0');
        // 返回补全后的日期字符串
        return `${year}-${month.toString().padStart(2, '0')}-${day.toString().padStart(2, '0')}T${hour}:${minute}:${second}`;
    }

    var passAlarmChange = function() {
		var flag = $('#passfilealarmcheck').get(0).checked;
		if(flag) {
			$('.passfilealarmDiv').show();
		} else {
			$('.passfilealarmDiv').hide();
		}
	}

    var intTransThreadNum = function () {
		var transSpeed = $("#scanThreadNum").val();
		if(transSpeed == 1) {//为1（极慢）时显示文件扫描速度
			$(".scanFileDiv").show();
		}else {
			$(".scanFileDiv").hide();
		}
	}

    //切换保留个数、天数
    var delReserveChange = function () {
        var val = $('#del_reserve').val();
        if(CONF.RESERVE_TYPE.NUM == val){
			$('.reserveNum').show();
			$('.reserveDay').hide();
		}else if(CONF.RESERVE_TYPE.DAY == val){
			$('.reserveNum').hide();
			$('.reserveDay').show();
		}
		data.high_info.reserve_type = val;
    }

    //显示隐藏删除保留相关
    var copyDelChange = function () {
        if (this.checked) {
            $('.copy-del-div').show();
            delReserveChange();
        } else {
            $('.copy-del-div').hide();
        }
    }

    // 显示加密算法
    var transferEncryptChange = function () {
        if ($('#transport_encrypt_flag').get(0).checked) {
            $('.transfer-encrypt-method-form').show();
        } else {
            $('.transfer-encrypt-method-form').hide();
        }
    }

    var initFirstCopyTime = function () {
        pAjaxRequest({}, '/api/v1/system/times/info', 'GET', function (res) {
            Metronic.unblockUI('#settimeform');
            if (res.code == 0) {
                // 将时间字符串解析为 Date 对象
                var date = new Date(res.data.date);
                // 增加半小时（30分钟）
                date.setMinutes(date.getMinutes() + 30);
                // 格式化
                var year = date.getFullYear();
                var month = String(date.getMonth() + 1).padStart(2, '0'); // 月份从0开始
                var day = String(date.getDate()).padStart(2, '0');
                var hours = String(date.getHours()).padStart(2, '0');
                var minutes = String(date.getMinutes()).padStart(2, '0');
                var seconds = String(date.getSeconds()).padStart(2, '0');
                var newTime = `${year}-${month}-${day} ${hours}:${minutes}:${seconds}`;
                $('#firstCopyTime').val(newTime);
                if (EDIT_FLAG) {
                    $('#firstCopyTime').val(OLD_INFO.time_strategy[0].first_start_time);
                }
            }
        });
    }

    var initSpinner = function () {
        $('.backupThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
        $('.scanThreadDiv').spinner({value:3, step: 1, min: 1, max: 32});
        $('#delReserveNum').spinner({value:5, step: 5, min: 1, max: 30});
        $('#delReserveDay').spinner({value:5, step: 5, min: 1, max: 30});
        $('#reserveSize').spinner({value:5, step: 5, min: 1, max: 999});
        $('#passFileSpinnerpercent').spinner({value: 20, step: 5, min: 1,max: 100});//跳过文件告警比例
		$('#passFileSpinnerNum').spinner({value:10, step: 5, min: 1, max: 999999999});//跳过文件告警个数
    }

    /*******************************复制源*********************************/

    /*************************************初始化源的端文件树**********************************/
    const initSrcPath = function () {
        $('.select-tips1').remove();
        $('#srcAgent').selectpicker('refresh');
        var srcAgentInput =  $("#srcAgent").find("option:selected").attr('value');
        if (srcAgentInput == '') {
            $('#toSelectSrcTips').show();
            $('.src-path-tree').hide();
            return;
        } else {
            $('#toSelectSrcTips').hide();
            $('.src-path-tree').show();
        }
        var event_type = $("#srcAgent").find("option:selected").attr('event_type');
        var uuid = $("#srcAgent").find("option:selected").attr('value');
        var agent_uuid = $("#srcAgent").find("option:selected").attr('agent_uuid');
        var name = $("#srcAgent").find("option:selected").text();
        var group_uuid = $("#srcAgent").find("option:selected").attr('group_uuid');
        var share_path = $("#srcAgent").find("option:selected").attr('share_path');
        //获取到了值之后  再禁用离线的客户端,修改才需要判断
        if (EDIT_FLAG) {
            disableOfflineAgent();
        }
        switch (event_type) {
            case 'agent': // 文件客户端
                initAgentPathTree(uuid,group_uuid);
                break;
            case 'nas': // nas设备
                var path = $("#srcAgent").find("option:selected").attr('path');
                initNasPathTree(uuid, agent_uuid,path,share_path);
                break;
            case 'obsItem': // 对象存储
                initObsPathTree(uuid);
                break;
            case 'hadoop_cluster': // hadoop
                var path = $("#srcAgent").find("option:selected").attr('path');
                initHadoopPathTree(uuid,path);
                break;
            default:
                break;
        }
    }

    const disableOfflineAgent = () => {
        $('#srcAgent option').each(function() {
            var $option = $(this);
            if ($option.attr('chk_disabled') === 'true') {
              $option.attr('disabled', true);
            }
        });
        $('#desAgent option').each(function() {
            var $option = $(this);
            if ($option.attr('chk_disabled') === 'true') {
              $option.attr('disabled', true);
            }
        });
    }
    
    //源端文件树 - 文件客户端
    const initAgentPathTree = (agent_uuid,group_uuid) => {
        var params = {
            "agent_uuid":agent_uuid,
            "start":0,
            "limit":40,
            "filename":"",
            "dir":"",
            "pid":0,
            "group_uuid":group_uuid
        }
        Metronic.blockUI({target: '.src-tree-div',animate: true});
        pAjaxRequest(params, '/api/v1/files/agent_tree', 'POST', (result) => {
            if (result.success) {
                setPathTree(result.data.file_nodes);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_CLIENT_FILE_FAILED, result.message);
            }
            Metronic.unblockUI('.src-tree-div');
        });
    }
    //源端文件树 - nas设备
    const initNasPathTree = (nas_uuid, agent_uuid,path,share_path) => {
        var params = {
            "nas_uuid":nas_uuid,
            "offset":0,
            "limit":40,
            "filename":"",
            "dir":path,
            "pid":path,
            "agent_uuid":agent_uuid
        }
        Metronic.blockUI({target: '.src-tree-div',animate: true});
        pAjaxRequest(params, '/api/v1/nas/backup_son_tree', 'GET', (result) => {
            if (result.success) {
                //第一次请求目录时加一层父节点 可支持全选
                result.data.file_nodes.push({
                    "id":  path,
                    "pId": 0,
                    "name": share_path,
                    "title": share_path,
                    "isParent": true,
                    "open": true,
                    "uuid": nas_uuid,
                    "nocheck": false,
                    "type": "2",
                    "icon": "./img/fs/wenjianjia.png",
                    "iconOpen": "./img/fs/wenjianjiaopen.png",
                    "iconClose": "./img/fs/wenjianjia.png",
                    "filepath": path,
                    "more": false,
                    "checked": false,
                    "parentFlag":true,
                    "event_type": 'nas'
                });
                setPathTree(result.data.file_nodes);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_CLIENT_FILE_FAILED, result.message);
            }
            Metronic.unblockUI('.src-tree-div');
        });
    }
    //源端文件树 - 对象存储
    const initObsPathTree = (uuid,group_uuid) => {
        let params = {
            start: 0,
            limit: _pageSize,
            filename: '',
            pid: 0,
            obs_uuid: uuid,
            editFlag: false,
            startAfter: '',
            taskId: ''
        };
        Metronic.blockUI({target: '.src-tree-div',animate: true});
        pAjaxRequest(params, '/api/v1/s3/file_dir_tree', 'GET', (result) => {
            if (result.success) {
                setPathTree(result.data.fileNodes);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_CLIENT_FILE_FAILED, result.message);
            }
            Metronic.unblockUI('.src-tree-div');
        });
    }
    //源端文件树 - hadoop
    const initHadoopPathTree = (uuid, path) => {
        var params = {
            "hadoop_cluster_id":uuid,
            "limit_count":_pageSize,
            "fetch_root_dir":path, //父级路径
            "start_after":"", //第一次使用空字符串
            "edit_flag":false,
            "start":0
        }
        Metronic.blockUI({target: '.src-tree-div',animate: true});
        pAjaxRequest(params, '/api/v1/hadoop/backup/cluster/file_ztree', 'GET', (result) => {
            if (result.success) {
                setPathTree(result.data.file_nodes);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_CLIENT_FILE_FAILED, result.message);
            }
            Metronic.unblockUI('.src-tree-div');
        });
    }
    const setPathTree = (data) => {
        var setting = {
            check: {
                enable: true,
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
                beforeClick: pathNodeClick,
                onCheck: pathNodeCheck,
                beforeExpand:pathNodeExpand
            },
        };
        pathTree = $.fn.zTree.init($('#fileCopySrcTree'), setting, data);
    }

    const pathNodeClick = (treeId, treeNode,clickshow) =>{
        //1文件 2 文件夹 3 磁盘
        let method = 'GET';
        if(treeNode.type == 1){
            return;
        } else {
            //如果不是文件 加载文件/目录树
            if(treeNode.more) {//加载更多
                let params = {}
                let api = '';
                switch (treeNode.event_type) {
                    case 'agent':
                        params = {
                            agent_uuid: treeNode.uuid,
                            offset: treeNode.next_index,
                            limit: _pageSize,
                            filename: treeNode.search_file_name,
                            dir: treeNode.dir_path,
                            pid: treeNode.pid,
                        }
                        api = '/api/v1/files/agent_sontree';
                        method = 'POST';
                        break;
                    case 'nas':
                        params = {
                            nas_uuid:treeNode.uuid,
                            agent_uuid: treeNode.agent_uuid,
                            offset: treeNode.next_index,
                            limit: _pageSize,
                            filename: treeNode.search_file_name,
                            dir: treeNode.dir_path,
                            pid: treeNode.pid,
                        }
                        api = '/api/v1/nas/backup_son_tree';
                        break;
                    case 'obsItem':
                        params = {
                            obs_uuid: treeNode.uuid,
                            limit: _pageSize,
                            startAfter: treeNode.search_file_name,
                            dir: treeNode.dir_path,
                            pid: treeNode.pId,
                        }
                        api = '/api/v1/s3/file_dir_son_tree';
                        break;
                    case 'hadoop_cluster':
                        params = {
                            hadoop_cluster_id: treeNode.clusteruuid,
                            limit_count: _pageSize,
                            start: treeNode.next_index,
                            start_after: treeNode.next_start,
                            fetch_root_dir: treeNode.pId, //父级路径
                        }
                        api = '/api/v1/hadoop/backup/cluster/file_ztree';
                        break;
                    default:
                        break;
                }
                var div = '.src-tree-div';
                Metronic.blockUI({target: div,animate: true});
                pAjaxRequest(params, api, method, (result) => {
                    if (result.success) {
                        $.fn.zTree.getZTreeObj(treeId).removeNode(treeNode);
                        let treeData = treeNode.event_type === 'obsItem' ? result.data.fileNodes : result.data.file_nodes;
                        if (treeNode.getParentNode().check_Child_State == 2) {
                            for(var i = 0; i < treeData.length; i++) {
                                treeData[i].checked = true;
                            }
                        }
                        $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode.getParentNode(), treeData, true);
                    } else {
                        UIToastr.showWarning(LANG.UI_HADOOP_RECOVERY_GET_SUBDIR_FAILED, result.message);
                    }
                    Metronic.unblockUI(div);
                })
                return;
            }
            // _path = treeNode.filepath;
            pathNodeExpand(treeId, treeNode);
            // $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true);
        }
    }

    const pathNodeCheck = (treeId,treeNode) => {

    }
    const pathNodeExpand = (treeId,treeNode) => {
        if (treeNode.children || (treeNode.type != 3 && treeNode.type != 2)) return;
        let agentuuid = treeNode.event_type === 'hadoop_cluster' ? treeNode.clusteruuid : treeNode.uuid;
        if (!agentuuid)	return false;
        let dir = treeNode.id;
        let params = {};
        let api = '';
        let method = 'GET';
        switch (treeNode.event_type) {
            case 'agent':
                params = {
                    agent_uuid: agentuuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/files/agent_sontree';
                method = 'POST';
                break;
            case 'nas':
                params = {
                    nas_uuid:treeNode.uuid,
                    agent_uuid: agentuuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/nas/backup_son_tree';
                break;
            case 'obsItem':
                params = {
                    obs_uuid: treeNode.uuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/s3/file_dir_son_tree';
                break;
            case 'hadoop_cluster':
                params = {
                    hadoop_cluster_id: agentuuid,
                    limit_count: _pageSize,
                    fetch_root_dir: dir,
                    code_type: treeNode.code_type,
                    start: 0,
                    start_after: '',
                    edit_flag: false
                }
                api = '/api/v1/hadoop/backup/cluster/file_ztree';
                break;
            default:
                break;
        }
        Metronic.blockUI({target: '.src-tree-div', animate: true});
        pAjaxRequest(params, api, method, (result) => {
            if (result.success) {
                let treeData = treeNode.event_type === 'obsItem' ? result.data.fileNodes : result.data.file_nodes;
                if (treeNode.checked) {
                    for(var i = 0; i < treeData.length; i++) {
                        treeData[i].checked = true;
                    }
                }
                pathTree.addNodes(treeNode, treeData, true);
                pathTree.expandNode(treeNode,true,false,false);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_RECOVERY_GET_SUBDIR_FAILED, result.message);
            }
            Metronic.unblockUI('.src-tree-div');
        })

    }




    /****************************初始化目的端文件树**************************************/
    const initDesDir = function () {
        $('.select-tips2').remove();
        $('#desAgent').selectpicker('refresh');
        var desAgentInput = $("#desAgent").find("option:selected").attr('value');
        if (desAgentInput == '') {
            $('#toSelectDesTips').show();
            $('.des-path-tree').hide();
            return;
        } else {
            $('#toSelectDesTips').hide();
            $('.des-path-tree').show();
        }
        var event_type = $("#desAgent").find("option:selected").attr('event_type');
        var uuid = $("#desAgent").find("option:selected").attr('value');
        var agent_uuid = $("#desAgent").find("option:selected").attr('agent_uuid');
        var name = $("#desAgent").find("option:selected").text();
         //获取到了值之后  再禁用离线的客户端,修改才需要判断
         if (EDIT_FLAG) {
            disableOfflineAgent();
        }
        switch (event_type) {
            case 'agent': // 文件客户端
                initAgentDirTree(uuid);
                break;
            case 'nas': // nas设备
                var path = $("#desAgent").find("option:selected").attr('path');
                initNasDirTree(agent_uuid,path,name);
                break;
            case 'obsItem': // 对象存储
                var vendor = $("#desAgent").find("option:selected").attr('vendor');
                initObsDirTree(uuid, vendor);
                break;
            case 'hadoop_cluster': // hadoop
                var path = $("#desAgent").find("option:selected").attr('path');
                initHdoopDirTree(uuid,path);
                break;
            default:
                break;
        }
    };
    // 目的端目录树-文件
    const initAgentDirTree = (uuid) => {
        let params = {
            agent_uuid: uuid,
            offset: 0,
            limit: _pageSize,
            filename: '',
            dir: '',
            pid: 0,
            startAfter: ''
        }
        Metronic.blockUI({target: '.des-tree-div',animate: true});
        pAjaxRequest(params, '/api/v1/files/recover_path_tree', 'GET', (result) => {
            if (result.success) {
                $('#fileCopyDesTree').show();
                setDirTree(result.data);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_CLIENT_FILE_FAILED, result.message);
            }
            Metronic.unblockUI('.des-tree-div');
        });
    }

    // 目的端目录树-nas
    const initNasDirTree = (agent_uuid,path,name) => {
        let params = {
            agent_uuid: agent_uuid,
            share_path: path,
            offset: 0,
            limit: _pageSize,
            filename: '',
            dir: path,
            pid: 0,
            startAfter: ''
        }
        Metronic.blockUI({target: '.des-tree-div',animate: true});
        pAjaxRequest(params, '/api/v1/nas/recover_path_tree', 'GET', (result) => {
            $('#fileCopyDesTree').show();
            if (result.success) {
                result.data.push({
                    "id": 0,
                    "name": name,
                    "title": "/",
                    "dir_path": "/",
                    "isParent": true,
                    "open": true,
                    "icon": "./img/fs/wenjianjia.png",
                    "icon_close": "./img/fs/wenjianjia.png",
                    "icon_open": "./img/fs/wenjianjiaopen.png",
                    "type": "0",
                    "path": path,
                    "new_dir_create": 2,
                    "parentFlag": true,
                    "noRemoveBtn": true,
                    "noEditBtn": true,
                    "event_type": "nas"
                });
                setDirTree(result.data);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_NAS_FILE_FAILED, result.message);
            }
            Metronic.unblockUI('.des-tree-div');
        })
    }

    // 目的端目录树-对象存储
    const initObsDirTree = (uuid, vendor) => {
        let params = {
            agent_uuid: uuid,
            offset: 0,
            limit: _pageSize,
            filename: '',
            dir: '',
            pid: 0,
            code_type: 2,
            startAfter: ''
        }
        Metronic.blockUI({target: '.des-tree-div',animate: true});
        pAjaxRequest(params, '/api/v1/s3/recover_path_tree', 'GET', (result) => {
            if (result.success) {
                $('#fileCopyDesTree').show();
                if (result.data && result.data.length > 0) {
                    let treeData = result.data.map(item => {
                        return {
                            ...item,
                            name: item.name.split('|')[0],
                            title: item.title.split('|')[0],
                            dir_path: item.dir_path + '/',
                            vendor: vendor
                        }
                    });
                    setDirTree(treeData);
                }
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_GET_OBS_FILE_FAILED, result.message);
            }
            Metronic.unblockUI('.des-tree-div');
        })
    }

    // 目的端目录树-hadoop
    const initHdoopDirTree = (uuid,path) => {
        let params = {
            hadoop_cluster_id: uuid,
            limit_count: _pageSize,
            fetch_root_dir: path,
            start_after: '', //上一个节点路径
            edit_flag: false
        };
        Metronic.blockUI({target: '.des-tree-div',animate: true});
        pAjaxRequest(params, '/api/v1/hadoop/recover_path_tree', 'GET', (result) => {
            if (result.success) {
                let treeData = result.data.file_nodes;
                $('#fileCopyDesTree').show();
                treeData.push({
                    "id": '/',
                    "name": '(/)',
                    "title": "/",
                    "dir_path": "/",
                    "isParent": true,
                    "open": false,
                    "icon": "./img/fs/wenjianjia.png",
                    "icon_close": "./img/fs/wenjianjia.png",
                    "icon_open": "./img/fs/wenjianjiaopen.png",
                    "type": "0",
                    "path": path,
                    "parentFlag": true,
                    "noRemoveBtn": true,
                    "noEditBtn": true,
                    "event_type": "hadoop_cluster",
                    "clusteruuid": uuid
                })

                setDirTree(treeData);
            } else {
                UIToastr.showWarning(LANG.UI_OBS_GET_RECOVER_HADOOP_CLUSTER_DIR_FAILED, result.message);
            }
            Metronic.unblockUI('.des-tree-div');
        })
    }

    // 目的端目录树
    const setDirTree = function (data) {
        let setting = {
            view: {
                addHoverDom: addHoverDom,
                removeHoverDom: removeHoverDom,
                selectedMulti: false
            },
            edit: {
                enable: true,
                drag:false,//禁用拖拽
                editNameSelectAll: true,
                showRenameBtn: showRenameBtn,
                showRemoveBtn: showRemoveBtn,
                removeTitle: LANG.BILLING_DELETE,
                renameTitle: LANG.UI_FILE_EDIT
            },
            check: {
                enable: true,
            },
            data: {
                simpleData: {
                    enable: true
                }
            },
            callback: {
                beforeClick: dirNodeSelect,
                onCheck: dirOnCheck,
                beforeExpand: dirNodeExpand,
                beforeEditName: beforeEditName,
                onRemove: onRemove, //移除事件
                onRename: onRename,
            }
        };
        dirTree = $.fn.zTree.init($('#fileCopyDesTree'), setting, data);
        recycleBinTree = $.fn.zTree.init($('#recycleBinTree'), setting, data);
    }

    const onRename = function(event, treeId, treeNode, isCancel) {
		//选中新增的节点
		let allNodes = dirTree.getCheckedNodes(true);

		for(let i = 0; i < allNodes.length; i++){
			dirTree.checkNode(allNodes[i], false, false, false);
		}

		dirTree.checkNode(treeNode, true, false, false);
		//给新增的节点设置title
		let pnode = treeNode.getParentNode();
		//去掉sharepath最前面的斜杠
		let newpath = share_path.substring(1, share_path.length);
		let parentPath = pnode.dir_path.replace(newpath, '');
		treeNode.title = treeNode.name;
		treeNode.dir_path = parentPath + treeNode.name + '/';

		let str = $.trim(treeNode.name);

		if(str.length==0) {
			UIToastr.showInfo(LANG.UI_NAS_RECOVERY_FILENAME_NO_NULL);
			filenameFlag = false;
			dirTree.editName(treeNode);
			return;
		}else {
			filenameFlag = true;
		}
		let specialchar = ['/', ':','"','<','>','|','\\','?','*','&'];
		let des = LANG.UI_FILE_FILENAME_NO_CONTAIN + ' &';
        var os_type = $("#desAgent").find("option:selected").attr('os_type');
		if(os_type == 'Windows')  {//linux不判断文件夹名称
			//文件夹名称不能包含特殊符号,不包含  /  :  "  <  >  | \
			specialchar = ['/', ':','"','<','>','|','\\','?','*'];
			des = LANG.UI_FILE_FILENAME_NO_CONTAIN;
		}
		for (let key in specialchar) {
			if (str.indexOf(specialchar[key]) != -1) {
				UIToastr.showInfo(des);
				filenameFlag = false;
				dirTree.editName(treeNode);
				return;
			}else {
				filenameFlag = true;
			}
		}
	}

    const dirNodeExpand = function(treeId, treeNode) {
        if (treeNode.children) return true;
        let agentuuid = treeNode.event_type === 'hadoop_cluster' ? treeNode.clusteruuid : treeNode.uuid;
        if (!agentuuid)	return false;
        let dir = treeNode.dir_path;
        if (treeNode.title === "/" && treeNode.event_type === undefined) { // nas新增的根目录
            dir = treeNode.id;
        }
        let params = {};
        let api = '';
        switch (treeNode.event_type) {
            case 'agent':
                params = {
                    agent_uuid: agentuuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/files/recover_path_tree';
                break;
            case 'nas':
                params = {
                    agent_uuid: agentuuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/nas/recover_path_tree';
                break;
            case 'obs_agent':
                params = {
                    agent_uuid: agentuuid,
                    start: 0,
                    limit: _pageSize,
                    filename: '',
                    dir: dir,
                    pid: treeNode.id,
                    code_type: treeNode.code_type,
                    start_after: ''
                }
                api = '/api/v1/s3/recover_path_tree';
                break;
            case 'hadoop_cluster':
                params = {
                    hadoop_cluster_id: agentuuid,
                    limit_count: _pageSize,
                    fetch_root_dir: dir,
                    code_type: treeNode.code_type,
                    start: 0,
                    start_after: '',
                    edit_flag: false
                }
                api = '/api/v1/hadoop/recover_path_tree';
                break;
            default:
                break;
        }
        Metronic.blockUI({target: '.des-tree-div', animate: true});
        pAjaxRequest(params, api, 'GET', (result) => {
            if (result.success) {
                let treeData = treeNode.event_type === 'hadoop_cluster' ? result.data.file_nodes : result.data
                $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, treeData, true);
            } else {
                UIToastr.showWarning(LANG.UI_HADOOP_RECOVERY_GET_SUBDIR_FAILED, result.message);
            }

            Metronic.unblockUI('.des-tree-div');
        })
    }
    const beforeEditName = function(treeId, treeNode) {
        $.fn.zTree.getZTreeObj(treeId).selectNode(treeNode);
        $.fn.zTree.getZTreeObj(treeId).editName(treeNode);
        return false;
    }

    const onRemove = function(event, treeId, treeNode){
		//获取删除节点的父节点
		let parentNode = treeNode.getParentNode();
		//如果父节点下的子节点不存在
		if(parentNode.children.length == 0) {
			//修改父节点图标为文件夹样式
			parentNode.isParent = true;
			// parentNode.open = true;
			dirTree.updateNode(parentNode);
		}

	}

    /**
     * 恢复路径树添加悬停节点
     * @param {*} treeId
     * @param {*} treeNode
     * @returns
     */
    const addHoverDom = function(treeId, treeNode) {
        let sObj = $("#" + treeNode.tId + "_span");
        if (treeNode.more || treeNode.editNameFlag || $("#addBtn_"+treeNode.tId).length > 0) return;
        let addStr = '<span class="button add" id="addBtn_'+ treeNode.tId + '" title="'+ LANG.UI_FILE_NEW_DIR +'" onfocus="this.blur();"></span>';
        sObj.after(addStr);
        let btn = $("#addBtn_"+treeNode.tId);
        // 点击添加节点
        if (btn) btn.bind("click", function(){
            if(treeNode.noRemoveBtn) {//不是新增的节点
                dirNodeExpand(treeId, treeNode);
            }
            if(treeNode.children && treeNode.children.length != 0) {
                $.fn.zTree.getZTreeObj(treeId).addNodes(
                    treeNode,
                    -1,
                    {
                        id: (treeNode.id + '_' + treeNode.children.length),
                        icon: './img/fs/wenjianjia.png',
                        iconOpen: "./img/fs/wenjianjiaopen.png",
                        iconClose: "./img/fs/wenjianjia.png",
                        title: '',
                        isParent:false,
                        pId: treeNode.id,
                        name: 'recovery-destination' + (newCount++),
                        isnew: true,
                        new_dir_create: CONF.FLAG.SET,
                        code_type: treeNode.code_type,
                        event_type: treeNode.event_type
                    },
                    false);
                let newnode = $.fn.zTree.getZTreeObj(treeId).getNodeByParam("isnew", true, null);
                beforeEditName(treeId,newnode);
                newnode.isnew = false;
            }else {
                $.fn.zTree.getZTreeObj(treeId).addNodes(
                    treeNode,
                    -1,
                    {
                        id: (treeNode.id + '_' + 0),
                        icon: './img/fs/wenjianjia.png',
                        iconOpen: "./img/fs/wenjianjiaopen.png",
                        iconClose: "./img/fs/wenjianjia.png",
                        title: '',
                        isParent: false,
                        pId: treeNode.id,
                        name: 'recovery-destination' + (newCount++),
                        isnew: true,
                        new_dir_create: CONF.FLAG.SET,
                        code_type: treeNode.code_type,
                        event_type: treeNode.event_type
                    },
                    false);
                let newnode = $.fn.zTree.getZTreeObj(treeId).getNodeByParam("isnew", true, null);
                beforeEditName(treeId,newnode);
                newnode.isnew = false;
            }
            return false;
        });
    };

    /**
     * 恢复路径树移除悬停节点
     * @param {*} treeId
     * @param {*} treeNode
     */
    const removeHoverDom = function(treeId, treeNode) {
        $("#addBtn_"+treeNode.tId).unbind().remove();
    }

    /**
     * 是否显示编辑按钮
     * @param {*} treeId
     * @param {*} treeNode
     */
    const showRenameBtn = function(treeId, treeNode) {
        // 获取节点所配置的noEditBtn属性值
        return !(treeNode.noEditBtn !== undefined && treeNode.noEditBtn);
    }

    /**
     * 是否显示删除按钮
     * @param {*} treeId
     * @param {*} treeNode
     * @returns
     */
    const showRemoveBtn = function(treeId, treeNode) {
        // 获取节点所配置的noRemoveBtn属性值
        return !(treeNode.noRemoveBtn !== undefined && treeNode.noRemoveBtn);
    }

    /**
     * 恢复路径树节点选择
     * @param treeId
     * @param treeNode
     */
    const dirNodeSelect = function(treeId, treeNode) {
        if (treeNode.more) {
            let params = {}
            let api = '';
            switch (treeNode.event_type) {
                case 'agent':
                    params = {
                        agent_uuid: treeNode.uuid,
                        offset: treeNode.next_index,
                        limit: _pageSize,
                        filename: treeNode.search_file_name,
                        dir: treeNode.dir_path,
                        pid: treeNode.pid
                    }
                    api = '/api/v1/files/recover_path_tree';
                    break;
                case 'nas':
                    params = {
                        agent_uuid: treeNode.uuid,
                        offset: treeNode.next_index,
                        limit: _pageSize,
                        filename: treeNode.search_file_name,
                        dir: treeNode.dir_path,
                        pid: treeNode.pid
                    }
                    api = '/api/v1/nas/recover_path_tree';
                    break;
                case 'obs_agent':
                    params = {
                        agent_uuid: treeNode.uuid,
                        offset: treeNode.next_index,
                        limit: _pageSize,
                        filename: treeNode.search_file_name,
                        dir: treeNode.dir_path,
                        pid: treeNode.pid
                    }
                    api = '/api/v1/s3/recover_path_tree';
                    break;
                case 'hadoop_cluster':
                    params = {
                        hadoop_cluster_id: treeNode.uuid,
                        start: treeNode.next_index,
                        limit_count: _pageSize,
                        fetch_root_dir: treeNode.dir_path,
                        start_after: treeNode.next_start
                    }
                    api = '/api/v1/hadoop/recover_path_tree';
                    break;
                default:
                    break;
            }
            var div = $('#' + treeId).parents('.des-tree-div').first()
            Metronic.blockUI({target: div,animate: true});
            pAjaxRequest(params, api, 'GET', (result) => {
                if (result.success) {
                    $.fn.zTree.getZTreeObj(treeId).removeNode(treeNode);
                    if(treeNode.event_type == "hadoop_cluster"){
                        $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode.getParentNode(), result.data.file_nodes, true);
                    }else{
                        $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode.getParentNode(), result.data, true);
                    }
                } else {
                    UIToastr.showWarning(LANG.UI_HADOOP_RECOVERY_LOAD_MORE_FAILED, result.message);
                }

                Metronic.unblockUI(div);
            })
        } else {
            $.fn.zTree.getZTreeObj(treeId).checkNode(treeNode, !treeNode.checked, false, true);
        }
    }

    /**
     * 恢复路径树节点选中
     * @param e
     * @param id
     * @param node
     */
    const dirOnCheck = function(e, treeId, node) {
        let allNodes = $.fn.zTree.getZTreeObj(treeId).getCheckedNodes(true);

        for(let i = 0; i < allNodes.length; i++){
            $.fn.zTree.getZTreeObj(treeId).checkNode(allNodes[i], false, false, false);
        }

        $.fn.zTree.getZTreeObj(treeId).checkNode(node, true, false, false);
    }

    /************************************添加选中数据到列表****************************************/
    const addToList = function(){
        if (!filenameFlag) {
            return;
        }
        let srcNodes = pathTree.getCheckedNodes(true);
        if (srcNodes.length == 0) {
            UIToastr.showWarning(LANG.UI_FILE_COPY_SRC,LANG.UI_FILE_COPY_SRC_TIPS1);
            return;
        }
        srcNodes = filterFile(srcNodes);
        let desNodes = dirTree.getCheckedNodes(true);
        if (desNodes.length == 0) {
            UIToastr.showWarning(LANG.UI_FILE_COPY_SRC,LANG.UI_FILE_COPY_SRC_TIPS2);
            return;
        }
        //检测节点中是否包含根节点,给出提示，允许添加
        checkRootNode([...srcNodes, ...desNodes]);
        let rowsData = [];//要加入列表的数据
        let source_path_list = [];
        let srcPath = "";//源端文件
        // replaceBetweenStartEnd(dir_path, '|', '/', '').replace('|', '');
        srcNodes.forEach(item => {
            let srcname = formateFilePath(item.id,item.event_type, $('#srcAgent').find('option:selected').attr('path'));
            srcPath += srcname + '<br>';
            source_path_list.push({
                "file_path" : srcname,
                "code_type" : item.code_type,
                "file_type" : item.type,
            });
        });
        let data = $('#fileCopyTable').bootstrapTable('getData');
        let num = data.length + 1;
        let desname = formateFilePath(desNodes[0].dir_path,desNodes[0].event_type, $('#desAgent').find('option:selected').attr('path'));
        rowsData.push({
            "id": num,
            "src_file": srcPath,
            "des_path": desname,
            "source_path_list": source_path_list,
            "target_path": {
                "file_path" : desname,
                "code_type" : desNodes[0].code_type,
                "file_type" : desNodes[0].type,
            },
        });
        if(checkDataIsReapeat(data,rowsData[0])) {
            UIToastr.showWarning(LANG.UI_FILE_COPY_DES, LANG.UI_FILE_COPY_CHECK_PATH_TIPS);
            return;
        }
        data.push.apply(data, rowsData); // 追加数据
        $('#fileCopyTable').bootstrapTable('load', data); // 重新加载数据
        checkTableIsEmpty();
    }
    var checkRootNode = function (nodes) {
        for(let i = 0; i < nodes.length; i++){
            if(nodes[i].level == 0){
                UIToastr.showWarning(LANG.UI_FILE_COPY_DES,LANG.UI_FILE_COPY_ROOT_DIR_TIPS);
                return false;
            }
        }
        return true;
    }

    var formateFilePath = function (path,type,share_path) {
        switch (type) {
            case 'agent':
                break;
            case 'nas':
                if (path.startsWith(share_path)) {
                    // 剪切掉 share_path 部分
                    path = path.substring(share_path.length);
                }
                if (!path.startsWith("/")) {
                    path = '/' + path;
                }
                break;
            case 'obsItem':
                break;
            case 'hadoop_cluster':
                break;
        }
        return path;
    }

    //检测数据是否重复或者存在包含关系，有则给出提示，并禁止添加
    var checkDataIsReapeat = function(data,rowsData) {
        if (data.length == 0) {
            return false;
        }
        for (var i = 0; i < data.length; i++) {
            if (rowsData.target_path.file_path == data[i].target_path.file_path) {
                const isMatch = rowsData.source_path_list.some(rowsItem => {
                    return data[i].source_path_list.some(dataItem => {
                        if (rowsItem.file_path.includes(dataItem.file_path) || 
                            dataItem.file_path.includes(rowsItem.file_path)) {
                            return true;
                        }
                    });
                });
                if (isMatch) return true; //直接退出
            }
        }
        return false;
    }

    //检测表格是不是空的，如果表格中有数据，不能切换源和目标
    var checkTableIsEmpty = function() {
        let data = $('#fileCopyTable').bootstrapTable('getData');
        if (data.length != 0) {
            $('#srcAgent').prop('disabled', true);
            $('#desAgent').prop('disabled', true);
        } else {
            $('#srcAgent').prop('disabled', false);
            $('#desAgent').prop('disabled', false);
        }
        $('#srcAgent').selectpicker('refresh');
        $('#desAgent').selectpicker('refresh');
    }

    //过滤文件
    var filterFile = function (allfileNodes) {
        var allCheckedNode = [];
        //得到所有勾选状态是全选中的节点
        for(var i=0; i<allfileNodes.length; i++){
            //check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
            if(allfileNodes[i].check_Child_State == 2 || allfileNodes[i].check_Child_State == -1) {
                allCheckedNode.push(allfileNodes[i]);
            }
        }
        //过滤掉重复的
        for(var m = 0;m<allCheckedNode.length;m++) {
            if(allCheckedNode[m].type != 1) {//磁盘或文件夹
                for(var n = 0;n<allCheckedNode.length;n++) {
                    var str = allCheckedNode[n].pId==null ?'':allCheckedNode[n].pId;
                    if(str.includes(allCheckedNode[m].filepath) && allCheckedNode.indexOf(allCheckedNode[n])!=-1) {//判断全选的文件夹下是否还有文件  有则从数组中删除
                        allCheckedNode.splice(allCheckedNode.indexOf(allCheckedNode[n]),1);
                        n--;
                    }
                }
            }
        }
        return allCheckedNode;
    }


    //第一步数据检测以及一些数据的初始化
    var step1Valid = function (showTitleCallback) {
        data.copy_info.source_uuid =  $("#srcAgent").find("option:selected").attr('value');
        data.copy_info.source_type =  getSubModuleType($('#srcAgent').find('option:selected').attr('event_type'));
        data.copy_info.target_uuid =  $("#desAgent").find("option:selected").attr('value');
        data.copy_info.target_type =  getSubModuleType($('#desAgent').find('option:selected').attr('event_type'));
        var rows = $('#fileCopyTable').bootstrapTable('getData');
        if (rows.length == 0) {
            UIToastr.showWarning(LANG.UI_FILE_COPY_DES, LANG.UI_FILE_COPY_PLEASE_ADD_TO_LIST);
            return false;
        }
        var pathList = [];
        for(var i = 0; i < rows.length; i++) {
            pathList.push({
                source_path_list:rows[i].source_path_list,
                target_path:rows[i].target_path
            });
        }
        data.copy_info.path_list = pathList;
        showStep1();
        initBackupTarget();
        getCurrentUseLicense().then(showTitleCallback);
        return false;
    }

    //第一步数据展示
    var showStep1 = function () {
        $('#srcAgentShow').html($("#srcAgent").find("option:selected").text());
        $('#desAgentShow').html($("#desAgent").find("option:selected").text());
        var path_list = data.copy_info.path_list;
        var eachDes = '';
        var copyListDes = '';
        for(var i = 0; i < path_list.length; i++) {
            path_list[i].source_path_list.forEach(item => {
                eachDes += item.file_path + '->' + path_list[i].target_path.file_path +'；\n'
            });
            copyListDes += eachDes
            eachDes = '';
        }
        $('#copyList').text(copyListDes);
        //判断配置是否显示
        let src_os_type = $("#srcAgent").find("option:selected").attr('os_type')
        let des_os_type = $("#desAgent").find("option:selected").attr('os_type')
        showCongig(data.copy_info.source_type, data.copy_info.target_type,src_os_type,des_os_type);
    }

    var showCongig = function (source_type, target_type,src_os_type,des_os_type) {
        let permissionShowFlag = false;
        let snapshotShowFlag = false;
        //源端和目标端,只要有一端是文件或者hadoop就显示快照
        if (source_type == CONF.SUBMODULE_TYPE.FS || source_type == CONF.SUBMODULE_TYPE.HADOOP
            || target_type == CONF.SUBMODULE_TYPE.FS || target_type == CONF.SUBMODULE_TYPE.HADOOP) {
            $('.snapshot-div').show();
            $('.snapshotShowDiv').show();
            snapshotShowFlag = true;
        } else {
            $('.snapshot-div').hide();
            $('.snapshotShowDiv').hide();
            snapshotShowFlag = false;
        }
        //操作系统类型不一样，屏蔽权限复制
        if (src_os_type != des_os_type || source_type != target_type) {
            $('.copy-file-permission-div').hide();
            $('.copyFilePermissionShow').hide();
            permissionShowFlag = false;
        } else {
            $('.copy-file-permission-div').show();
            $('.copyFilePermissionShow').show();
            permissionShowFlag = true;
        }
        //源端和目标端都为nas设备时，屏蔽整个传输策略和网络重连配置
        if (source_type == CONF.SUBMODULE_TYPE.NAS && target_type == CONF.SUBMODULE_TYPE.NAS) {
            $('.transferLi').removeClass('active').hide();
            $('#tab_transfer').removeClass('active');
            $('.transfer-strategy-show').hide();
            $('.highLi').removeClass('active');
            $('#tab_other').removeClass('active');
		    $('.commonLi').removeClass('active').addClass('active');
            $('#tab_common').removeClass('active').addClass('active');
            $('.network-retry-wrap').hide();
            $('.network_retry_times_show').hide();
            $('.network_retry_interval_show').hide();
        } else {
            $('.transferLi').show();
            $('.transfer-strategy-show').show();
            $('.network-retry-wrap').show();
            $('.network_retry_times_show').show();
            $('.network_retry_interval_show').show();
        }
    }
    /*******************************复制目的地*********************************/
    /**
    * 初始化备份目的地
    */
    const initBackupTarget = () => {
        let allow_node_uuid_list = [];
        let src_temp_arr = [];
        let des_temp_arr = [];
        let p = {
            hide_backup_storage_flag: true,  // 是否隐藏备份存储
        };
        if (data.copy_info.source_type == CONF.SUBMODULE_TYPE.NAS) {
            pAjaxRequest({'nas_uuid': data.copy_info.source_uuid}, "/api/v1/nas/mount_node", "GET", function (result) {
                if(result.success) {
                    src_temp_arr = result.data;
               }
           }, false);
        }
        if (data.copy_info.target_type == CONF.SUBMODULE_TYPE.NAS) { 
            pAjaxRequest({'nas_uuid': data.copy_info.target_uuid}, "/api/v1/nas/mount_node", "GET", function (result) {
                if(result.success) {
                    des_temp_arr = result.data;
               }
           }, false);
        }
        //源端是nas,目标端不是nas
        if (data.copy_info.source_type == CONF.SUBMODULE_TYPE.NAS && data.copy_info.target_type != CONF.SUBMODULE_TYPE.NAS) {
            p.allow_node_uuid_list = src_temp_arr;
            p.not_allow_node_suffix = LANG.UI_STORAGE_STATUS_UNMOUNT;
        }
        //源端不是nas,目标端是nas
        if (data.copy_info.source_type != CONF.SUBMODULE_TYPE.NAS && data.copy_info.target_type == CONF.SUBMODULE_TYPE.NAS) {
            p.allow_node_uuid_list = des_temp_arr;
            p.not_allow_node_suffix = LANG.UI_STORAGE_STATUS_UNMOUNT;
        }
        // 都是nas，获取交集
        if (data.copy_info.source_type == CONF.SUBMODULE_TYPE.NAS && data.copy_info.target_type == CONF.SUBMODULE_TYPE.NAS) {
            allow_node_uuid_list = src_temp_arr.filter(item => des_temp_arr.includes(item));
            p.allow_node_uuid_list = allow_node_uuid_list;
            p.not_allow_node_suffix = LANG.UI_STORAGE_STATUS_UNMOUNT;
        }
        if (EDIT_FLAG) {
            p.node_uuid = OLD_INFO.storage_info.node_uuid;
            p.node_pool_uuid = OLD_INFO.storage_info.node_pool_uuid;
            $('#backupTarget').backupTarget(p);
        } else {
            $('#backupTarget').backupTarget(p);
        }
        $('#backupTargetOnlyNodeTipsWrapper').html(`
            <div class="col-md-offset-3 col-md-9">
                <div class="alert alert-block alert-info fade in">
                    <button type="button" class="close" data-dismiss="alert"></button>
                    <h4 class="alert-heading"><strong>`+ LANG.UI_PUBLIC_TIPS +`</strong></h4>
                    <ol class = "alert-ol">
                        <li>
                            `+ LANG.UI_BACKUP_TARGET_ONLY_NODE_TIPS +`
                        </li>
                        <li>
                            `+ LANG.UI_BACKUP_TARGET_ONLY_NODE_FILE_COPY_TIPS +`
                        </li>
                    </ol>
                </div>
            </div>
        `);
    };
    var step2Valid = function () {
        if (!$('#backupTarget').backupTarget('validateSelect')) {
			return false;
		}
        showStep2();
        return true;
    }
    
    var showStep2 = function () {
        //备份目的地(节点)
        let backupTargetInfo = $('#backupTarget').backupTarget('getSelect');
        data.node_info.nodecheck = !backupTargetInfo.node_uuid;
        data.node_info.node_uuid = backupTargetInfo.node_uuid;
        data.node_info.node_pool_uuid = backupTargetInfo.node_pool_uuid;
        initResourceLimit(backupTargetInfo.node_uuid_list);
        $('.nodeInfoShow').html(backupTargetInfo.node_text);
    };

    /*******************************复制策略*********************************/
    //扫描文件速度描述
    var getScanSpeed = function (level) {
        var des = "";
        switch(level) {
            case 0: 
                des = LANG.UI_FILE_BAK_SCAN_SPEED_FIVE;
                break;
            case 1000: 
                des = LANG.UI_FILE_BAK_SCAN_SPEED_FOUR;
                break;
            case 800: 
                des = LANG.UI_FILE_BAK_SCAN_SPEED_THREE;
                break;
            case 600: 
                des = LANG.UI_FILE_BAK_SCAN_SPEED_TWO;
                break;
            case 400: 
                des = LANG.UI_FILE_BAK_SCAN_SPEED_ONE;
                break;
        }
        return des;
    }

    var step3Valid = function () {
        //通用策略
        data.copy_info.copy_interval = timeToSeconds($("#copyInterval").val());//复制间隔
        if (data.copy_info.copy_interval < 300) {
            UIToastr.showWarning(LANG.UI_STRATEGY_TIME, LANG.UI_FILE_COPY_INTERVAL_TIPS);
            return false;
        }
        data.copy_info.first_copy_time = $("#firstCopyTime").val().trim();//首次复制启动时间
        data.speedLimit = getSpeedStrategyInfo();//限速策略
        //传输策略
        data.transfer_info.compress_transport_flag = $('#compress_transport_flag').get(0).checked;
        data.transfer_info.transport_encrypt_flag = $('#transport_encrypt_flag').get(0).checked;
        data.transfer_info.transport_encrypt_method = $('#transferEncryptMethod').val();
        if (data.transfer_info.compress_transport_flag) {
            data.transfer_info.compress_method = $('#compressGrade').val();
        }
        //高级配置-复制策略
        data.high_info.snapshot = $('#snapshot').get(0).checked;
        data.high_info.copy_file_permission = $('#copy_file_permission').get(0).checked;
        data.high_info.sync_self_flag = $('#sync_self_flag').get(0).checked;
        // data.high_info.charset_copy = $('#charset_copy').get(0).checked;
        //高级配置-回收保留配置
        // data.high_info.copy_del_flag = $('#copy_del').get(0).checked;
        // if (data.high_info.copy_del_flag) {
        //     data.high_info.del_reserve_type = $('#del_reserve').val();
        //     if (data.high_info.del_reserve_type == 1) {
        //         data.high_info.del_reserve_val = $('#spinnerNumInput').val();
        //     } else {
        //         data.high_info.del_reserve_val = $('#spinnerDayInput').val();
        //     }
        //     data.high_info.recycle_bin_path =  $('#recycle_bin_path').val().trim();
        //     data.high_info.reserve_size_input =  calSize($('#reserve_size_input').val(), $('#quotaUnit').val());
        // } else {
        //     data.high_info.del_reserve_type = '';
        //     data.high_info.del_reserve_val = '';
        //     data.high_info.recycle_bin_path = '';
        //     data.high_info.reserve_size_input = ''; 
        // }
        //高级配置-线程配置
        if(CONF.FUNCTIONS.includes('multithread')){
            data.high_info.backup_thread_num = $('#backupThreadNum').val();
            if(data.high_info.backup_thread_num == "" || data.high_info.backup_thread_num > 32 || data.high_info.backup_thread_num <= 0
            || !/^\d+$/.test(data.high_info.backup_thread_num)){
                UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_FILE_BACKUP_THREAD_NUM_TIPS);
                return false;
            }
        } else {
			//未授权多线程直接传1
			data.high_info.backup_thread_num = 1;
		}
        data.high_info.scan_thread_num = $('#scanThreadNum').val();
        if(data.high_info.scan_thread_num == "" || data.high_info.scan_thread_num > 32 || data.high_info.scan_thread_num <= 0
        || !/^\d+$/.test(data.high_info.scan_thread_num)){
            UIToastr.showWarning(LANG.UI_BACKUP_HIGH_SETTING_TITLE, LANG.UI_FILE_BACKUP_THREAD_NUM_TIPS);
            return false;
        }
        if(data.high_info.scan_thread_num == 1) {
            data.high_info.scan_file_num = $('#scanFileNum').val();
        } else {
            data.high_info.scan_file_num = 0;
        }
        //高级配置-异常配置
        data.high_info.skip_file_alarm_flag = $('#passfilealarmcheck').get(0).checked;
        $('.passfilealarmcheckShow').html($('.passfilealarmlabel').html() + ": " + getSwitchDes(data.high_info.skip_file_alarm_flag));
		if(data.high_info.skip_file_alarm_flag) {//开启
			data.high_info.skip_file_alarm_min_num = $('#passFileNum').val();
			data.high_info.skip_file_alarm_min_ratio = $('#warningpercent').val();
			$('.passFileNumShow').show();
			$('.warningpercentShow').show();
			$('.passFileNumShow').html($('.passfilenumlabel').html() + ": " + data.high_info.skip_file_alarm_min_num);
			$('.warningpercentShow').html($('.passfilepercentlabel').html() + ": " + data.high_info.skip_file_alarm_min_ratio + '%');
			// 输入数据检测
			if(data.high_info.skip_file_alarm_min_num == "" || data.high_info.skip_file_alarm_min_num > 9999999999 || data.high_info.skip_file_alarm_min_num <= 0
			|| !/^\d+$/.test(data.high_info.skip_file_alarm_min_num)){
				UIToastr.showWarning(LANG.UI_NAS_SKIP_OBJECT_ALARM_NUM, LANG.UI_NAS_SKIP_OBJECT_ALARM_NUM_TIPS);
				return false;
			}
			if(data.high_info.skip_file_alarm_min_ratio == "" || data.high_info.skip_file_alarm_min_ratio > 100 || data.high_info.skip_file_alarm_min_ratio <= 0
			|| !/^\d+$/.test(data.high_info.skip_file_alarm_min_ratio)){
				UIToastr.showWarning(LANG.UI_NAS_SKIP_OBJECT_ALARM_RATIO, LANG.UI_NAS_SKIP_OBJECT_ALARM_RATIO_TIPS);
				return false;
			}
		} else {
			data.high_info.skip_file_alarm_min_num = '';
			data.high_info.skip_file_alarm_min_ratio = '';
			$('.passFileNumShow').hide();
			$('.warningpercentShow').hide();
		}
        //同名文件处理
        data.high_info.same_file_strategy = $('#sameFile').val();
        if (data.high_info.same_file_strategy == 1) {//同名文件处理是覆盖
            //校验方式
            data.high_info.check_mode = $('#check_mode').val();
            if (data.high_info.check_mode == 2) {//校验方式是文件内容
                //校验算法
                data.high_info.file_verification_algorithm = $('#file_verification_algorithm').val();
            } else {
                data.high_info.file_verification_algorithm = 0;
            }
            //覆盖规则
            data.high_info.sync_rule = $('#sync_rule').val();
        } else {
            data.high_info.check_mode = 0;
            data.high_info.sync_rule = 0;
            data.high_info.file_verification_algorithm = 0;
        }
        
        //高级配置-通配符配置
        data.high_info.wildcard_list = checkWildcardSameType();
        //匹配条件不是无并且没有添加通配符，直接退出
        if (data.high_info.wildcard_list.wildcard.length == 0 && data.high_info.wildcard_list.wildcard_mode != 0) {
            UIToastr.showWarning(LANG.UI_FILE_COPY_WILDCARD,LANG.UI_FILE_COPY_WILDCARD_TIPS);
            return false;
        }
        // 检测通配符生效对象和通配符的组合是否重复
        var seenWildcards = {};
        for (var i = 0; i < data.high_info.wildcard_list.wildcard.length; i++) {
            var wildcard = data.high_info.wildcard_list.wildcard[i].wildcard + data.high_info.wildcard_list.wildcard[i].wildcard_file_type;
            if (seenWildcards[wildcard]) {
                // 检测到重复的 wildcard，立即退出循环并返回 false
                UIToastr.showWarning(LANG.UI_FILE_COPY_WILDCARD,LANG.UI_FILE_COPY_WILDCARD_REPEAT);
                return false;
            }
            seenWildcards[wildcard] = true;
        }
        //高级配置-时间配置
        data.high_info.time_range_list = checkTimeRangeSameType();
        var timeRangeWayMode = getTimeRangeWayMode();
        //匹配条件不是无、时间范围是最近时间范围、没有输入文件修改最近天数，直接退出
        if (timeRangeWayMode == 1 && data.high_info.time_range_list.time_fliter_mode != 0 && (data.high_info.time_range_list.last_days == '' || data.high_info.time_range_list.last_days == 0)) {
            UIToastr.showWarning(LANG.UI_PLATFORM_SET_TIME,LANG.UI_FILE_COPY_CONFIG_TIME_TIPS1);
            return false;
        }
         //时间范围是固定时间范围
         if (timeRangeWayMode == 2) {
            //匹配条件不是无、没有添加文件修改时间范围，直接退出
            if (data.high_info.time_range_list.time_fliter_mode != 0 && data.high_info.time_range_list.time_list.length == 0) {
                UIToastr.showWarning(LANG.UI_PLATFORM_SET_TIME,LANG.UI_FILE_COPY_CONFIG_TIME_TIPS2);
                return false;
            }
            //检测是否有相同的时间范围
            var seen_time = new Set();
            var hasDuplicate = false;
            var timeList = data.high_info.time_range_list.time_list;
            for (var i = 0; i < timeList.length; i++) {
                var item = timeList[i];
                var itemString = JSON.stringify(item);
                if (seen_time.has(itemString)) {
                    hasDuplicate = true;
                    break;
                }
                seen_time.add(itemString);
            }
            if (hasDuplicate) {
                UIToastr.showWarning(LANG.UI_PLATFORM_SET_TIME,LANG.UI_FILE_COPY_TIME_REPEAT);
                return false;
            }
        }
        //高级配置-脚本配置
        //重试策略
		data.retry_strategy = $('#retry_config').retryStrategy({} ,'value');
		if (!data.retry_strategy) {
			return false;
		}
        // 忽略节点资源限制
		data.high_info.ignore_resource_limiting_flag = !!$('#ignoreResourceLimit').get(0).checked;
		$('.ignoreResourceLimitShow').html($('.ignoreResourceLimitLabel').html() + ": " + getSwitchDes(data.high_info.ignore_resource_limiting_flag));
        showStep3();
        return true;
    }
    
    var showStep3 = function () {
         //通用策略
        $('#copyIntervalShow').html($("#copyInterval").val());
        $('#copyStartTimeShow').html(data.copy_info.first_copy_time);
        //限速策略
        var speedLimitsStr = '';
        if (data.speedLimit.speedInfo.length != 0) {
            speedLimitsStr = '';
            for (let i = 0; i < data.speedLimit.speedInfo.length; i++) {
                speedLimitsStr += data.speedLimit.speedInfo[i].des + '<br>';
            }
        }
        if (speedLimitsStr == '') {
            speedLimitsStr = LANG.UI_PUBLIC_NOTHING;
        }
        $('#speedLimitShow').html(speedLimitsStr);
        //传输策略
        var encryptDes = LANG.UI_OS_BACKUP_TRANSFER_ENCRY + ':'+ getSwitchDes(data.transfer_info.transport_encrypt_flag) + '<br>';
        if (data.transfer_info.transport_encrypt_flag) {
            encryptDes += LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_METHOD + ':'+ $('#transferEncryptMethod').find("option:selected").text();
        }
        $('#encryptTransportShow').html(encryptDes);

        var compressDes = LANG.UI_FILE_COPY_TRANSFER_COMPRESS + ':'+ getSwitchDes(data.transfer_info.compress_transport_flag) + '<br>';
        if (data.transfer_info.compress_transport_flag) {
            compressDes += LANG.UI_GLOBAL_STRATEGY_COMPRESS_GRADE + ':'+ $('#compressGrade').find("option:selected").text();
        }
        $('#compressTransportShow').html(compressDes);
        //--------------------------高级配置----------------------------------
        //复制策略
        $('.snapshotShow').html(LANG.UI_FILE_COPY_SNAPSHOOT + ': ' + getSwitchDes(data.high_info.snapshot));
        $('.copyFilePermissionShow').html(LANG.UI_FILE_COPY_PERMISSION + ': ' + getSwitchDes(data.high_info.copy_file_permission));
        $('.copySelfFlagShow').html(LANG.UI_FILE_COPY_SELECTED_DIR + ': ' + getSwitchDes(data.high_info.sync_self_flag));
        // $('#charsetCopyShow').html(getSwitchDes(data.high_info.charset_copy));
        //线程配置
        $('.backupThreadNumShow').html($('.threadnumlabel').html() + ": " + data.high_info.backup_thread_num);
        $('.scanThreadNumShow').html($('.scanthreadlabel').html() + ": " + data.high_info.scan_thread_num);
        if(data.high_info.scan_thread_num == 1) {
            $('.scanFileNumShow').show();
            $('.scanFileNumShow').html($('.scanfilelabel').html() + ": " + getScanSpeed(parseInt(data.high_info.scan_file_num)));
        } else {
            $('.scanFileNumShow').hide();
        }
        //初始化传输网络
        if (networkFlag) {
            $('.transfernetworkDiv').show();
            if (oldNode != data.high_info.node.node_uuid) {
                initNetworkList();
            }
        } else {
            $('.transfernetworkDiv').hide();
        }
        //异常配置
        var sameFileDes = '';
        sameFileDes += $('.samefilelabel').html() + ": " + $('#sameFile').find("option:selected").text();
        if (data.high_info.same_file_strategy == 1) {//同名文件处理是覆盖
            sameFileDes += '<br>' + $('.check-mode-label').text() +':'+  $('#check_mode').find("option:selected").text();
            if (data.high_info.check_mode == 2) {//校验方式是文件内容
                //校验算法
                sameFileDes += '<br>' + $('.file-verification-algorithm-label').text() +':'+  $('#file_verification_algorithm').find("option:selected").text();
            }
            //覆盖规则
            sameFileDes += '<br>' + $('.sync-rule-label').text() +':'+  $('#sync_rule').find("option:selected").text();
        }
        $('.sameFileShow').html(sameFileDes);
        //通配符配置
        $('.wildcardShow').html(getWildcardDes(data.high_info.wildcard_list));
        //时间配置
        $('.timeRangeShow').html(getTimeRangeDes(data.high_info.time_range_list));
    }

    var getWildcardDes = function(info) {
        var des = '';
        var wildcardList = info.wildcard;
        if (wildcardList.length == 0) {
            return LANG.UI_PUBLIC_NOTHING;
        }
        var mode = getMatchMode(info.wildcard_mode);
        des += LANG.UI_HISTORY_JOB_DETAIL_COMPARE_CONDITION + ':'  + mode + '</br>';
        for(var i = 0; i < wildcardList.length; i++) {
            des += LANG.UI_HISTORY_JOB_DETAIL_WILDCARD_OBJECT + ':' + (wildcardList[i].wildcard_file_type == 1 ?  LANG.UI_DATA_FILE_FILE : LANG.UI_DATA_FILE_DIR) + ',';
            des += LANG.UI_FILE_WILDCARD + ':' + wildcardList[i].wildcard_des + '</br>'
        }
        return des;
    }

    var getTimeRangeDes = function(info) {
        var des = '';
        var mode = getMatchMode(info.time_fliter_mode);//排除和选定的描述
        var time_range_mode = getTimeRangeWayMode(); //1最近时间范围  2固定时间范围
        var time_config_mode = getTimeRangeMode();//是否配置时间过滤 0无  1排除  2选定
        var timeList = info.time_list;
        if (time_config_mode == 0) {
            return LANG.UI_PUBLIC_NOTHING;
        }
        des += LANG.UI_HISTORY_JOB_DETAIL_COMPARE_CONDITION + ':'  + mode + '</br>';
        if (time_range_mode == 1) {
            des += LANG.UI_HISTORY_JOB_DETAIL_COPY_RECENT + info.last_days + LANG.UI_PUBLIC_UNIT_DAY + '</br>';
        } else {
            for(var i = 0; i < timeList.length; i++) {
                var start_time = parseInt(timeList[i].time_fliter_time_start);
                var end_time = parseInt(timeList[i].time_fliter_time_end);
                des += LANG.UI_HISTORY_JOB_DETAIL_FILE_EDIT_TIME_RANGE + ':' + formatDate(start_time) + '-' + formatDate(end_time) + '</br>'
            }
        }
        return des;
    }

    var getMatchMode = function(mode) {
        var des = '';
        switch(parseInt(mode)) {
            case 1: //排除复制
                des = LANG.UI_HISTORY_JOB_DETAIL_EXCEPT_COPY;
                break;
            case 2: //选定复制
                des = LANG.UI_HISTORY_JOB_DETAIL_SELECT_COPY;
                break;
        }
        return des;
    }

    var formatDate = function(timestamp) {
        var date = new Date(timestamp);
        // 格式化日期和时间
        var year = date.getFullYear();
        var month = ('0' + (date.getMonth() + 1)).slice(-2); // 月份从0开始，需要加1
        var day = ('0' + date.getDate()).slice(-2);
        var hours = date.getHours();
        var minutes = ('0' + date.getMinutes()).slice(-2);
        var seconds = ('0' + date.getSeconds()).slice(-2);
        // 格式化后的日期和时间字符串
        return year + '-' + month + '-' + day + ' ' + hours + ':' + minutes + ':' + seconds;
    }

    //得到开关的结果描述   开启/关闭
    var getSwitchDes = function (check) {
        if (check) {
            return LANG.UI_PUBLIC_ON;
        }
        return LANG.UI_PUBLIC_OFF;
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
            jQuery('li', $('#fileCopyContent')).removeClass("done");
            var li_list = navigation.find('li');
            for (var i = 0; i < index; i++) {
                jQuery(li_list[i]).addClass("done");
            }
             // 修改备份任务时把最大步骤存入内存中 用于判断提交的按钮显示
             if (EDIT_FLAG && current >= currentmax){
            	currentmax = current;
            }
            if (current == 1) {
                $('#fileCopyContent').find('.button-previous').css('visibility', 'hidden');
                $('#fileCopyContent').find('.button-next').addClass('next-btn-margin-left');
            } else {
                $('#fileCopyContent').find('.button-previous').css('visibility', 'visible');
                $('#fileCopyContent').find('.button-next').removeClass('next-btn-margin-left');
            }

            if (current >= total) {
                $('#fileCopyContent').find('.button-next').hide();
                $('#fileCopyContent').find('.button-submit').css('visibility', 'visible');
            } else {
                $('#fileCopyContent').find('.button-next').show();
                $('#fileCopyContent').find('.button-submit').css('visibility', 'hidden');
            }
            // 用于判断修改备份任务时 是否展示提交按钮
            // if (EDIT_FLAG) {
            //     if (current < currentmax){
            //         $('#fileCopyContent').find('.button-submit').css('visibility', 'hidden');
            //     } else {
            //         $('#fileCopyContent').find('.button-submit').css('visibility', 'visible');
            //     }
            // }
            Metronic.scrollTo($('.page-title'));
        }

        // default form wizard
        $('#fileCopyContent').bootstrapWizard({
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
                        if(step1Valid(
							() => {
								$('a[href="#tab2"]').tab('show'); // 手动切换步骤页面
								handleTitle(tab, navigation, index);
                                if (EDIT_FLAG) {
                                    pageIndex = 1;
                                }
							}
						) == false){
                			return false;
                		}
                        if (EDIT_FLAG) {
                            pageIndex = 1;
                        }
                        break;
                    case 2:
                		if(step2Valid() == false){
                			return false;
                		}
                        if (EDIT_FLAG) {
                            pageIndex = 2;
                        }
                		break;
                    case 3:
                        if (step3Valid() == false) {
                            return false;
                        }
                        if (EDIT_FLAG) {
                            pageIndex = 3;
                        }
                        break;
                }
                handleTitle(tab, navigation, index);
            },
            onPrevious: function (tab, navigation, index) {
                pageIndex = index;
                success.hide();
                error.hide();
                handleTitle(tab, navigation, index);
            },
            onTabShow: function (tab, navigation, index) {
                var total = navigation.find('li').length;
                var current = index + 1;
                var $percent = (current / total) * 100;
                $('#fileCopyContent').find('.progress-bar').css({
                    width: $percent + '%'
                });
            }
        });

        $('#fileCopyContent').find('.button-previous').css('visibility', 'hidden');
        // if (EDIT_FLAG) {
        //     $('#fileCopyContent .button-submit').click(() => {
        //         // 授权判断
        //         getCurrentUseLicense().then(() => {
        //             if (pageIndex === 0) {
        //                 var step1 = step1Valid(() => {
        //                     submit();
        //                 });
        //                 if (!step1) return false;
        //             } else if (pageIndex === 1) {
        //                 var step2 = step2Valid();
        //                 if (!step2) return false;
        //             } else if (pageIndex === 2) {
        //                 var step3 = step3Valid();
        //                 if (!step3) return false;
        //             }
        //             submit();
        //         });
        //     }).css('visibility', 'visible');
        // } else {
        //     $('#fileCopyContent .button-submit').click(() => {
        //         getCurrentUseLicense().then(submit);
        //     }).css('visibility', 'hidden');
        // }
        $('#fileCopyContent .button-submit').click(() => {
            getCurrentUseLicense().then(submit);
        }).css('visibility', 'hidden');
    };

    var submit = function () {
        if (EDIT_FLAG) {
            //限速策略
            data.speedLimit = getSpeedStrategyInfo();
		}
        if ('' == $.trim($("#file_copy_job_name").val())) {
            $('.jobnametip').html(LANG.UI_BACKUP_NAME_TIPS).show();
            return;
        }
        $('.jobnametip').hide();
        data.job_name = $.trim($("#file_copy_job_name").val());
        data.strategygroupuuid = $('#strategySelect option:selected').val();
        Metronic.blockUI({target: '#fileCopyContent',animate: true,cenrerY: true,});
        data = deepCloneObject(data);
        pAjaxRequest(data, "/api/v1/filecopy/job/backup", "POST", function (data) {
            if (operateResponseList(data,data.title)) {
                LOCATION('./content/platform/jobs/jobs.php', 'task');
            }
            Metronic.unblockUI('#fileCopyContent');
        }, false);
    }

    var inintDatatimePicker = function () {
        if (CONF.LANGUAGE != "zh-cn" && CONF.LANGUAGE != "zh-tw") {
         //英文独有的
            $(".form_datetime").datetimepicker({
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-mm-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        } else {
            $(".form_datetime").datetimepicker({
                language:  'zh-CN',
                autoclose: true,
                isRTL: Metronic.isRTL(),
                format: "yyyy-MM-dd hh:ii:ss",
                pickerPosition: (Metronic.isRTL() ? "bottom-right" : "bottom-left"),
                startDate: new Date()
            });
        }
        //初始化复制间隔
        $('.copy-interval').timepicker({
            autoclose: true,
            minuteStep: 5,
            showSeconds: true,
            showMeridian: false,
            // defaultTime:'00:30:00'  //这个配置不起作用，可能是插件版本原因
        });
        $('.copy-interval').data('timepicker').setTime('00:30:00'); //设置默认时间
        if(EDIT_FLAG) {
            $('#copyInterval').data('timepicker').setTime(OLD_INFO.time_strategy[0].roll_interval);
        }
        $('.copy-interval').parent('.input-group').on('click', '.input-group-btn', function(e){
            e.preventDefault();
            $(this).parent('.input-group').find('.timepicker').timepicker('showWidget');
        });
        //初始化时间过滤-文件修改时间范围
        //初始化日期时间选择控件
		$('#timeInputRange').daterangepicker({
			"autoUpdateInput": false,											//是否自动填充input
			"startDate": moment().subtract(6, 'days').startOf('day'),			//默认开始时间
			"endDate": moment({hour: 23, minute: 59}),	//默认结束时间
			"maxDate": moment({hour: 23, minute: 59}),												//最大可用时间
			"timePicker": true,													//是否显示时间,时分
			"timePicker24Hour": true,											//是否是24小时制
			"alwaysShowCalendars": true,										//是否总是显示日期选择
			"ranges": DateRangePickerLocales.getRangesConfig(CONF.LANGUAGE),	//根据语言定义默认ranges,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
			"locale": DateRangePickerLocales.getLocalConfig(CONF.LANGUAGE),		//根据语言定义默认local,可以在此基础上增减,不要动DateRangePickerLocales里面的方法.
		}, function(start, end, label) {});

		//如果不是选择后自动填充input(autoUpdateInput:true),需要监听下面两个方法apply.daterangepicker和cancel.daterangepicker
		$('#timeInputRange').on('apply.daterangepicker', function(ev, picker) {
			//给全局变量赋值,然后设置input
			_daterangepicker_starttime = picker.startDate.format('YYYY-MM-DD HH:mm:ss');
			_daterangepicker_endtime = picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			var des = picker.startDate.format('YYYY-MM-DD HH:mm:ss') + ' - ' + picker.endDate.format('YYYY-MM-DD HH:mm:ss');
			$(this).val(des);
            addTimeRangeClick(des);
        });

		$('#timeInputRange').on('cancel.daterangepicker', function(ev, picker) {
			//清除全局变量,然后设置input
			_daterangepicker_starttime = "";
			_daterangepicker_endtime = "";
			$(this).val('');
		});
		//input右侧的图标事件
		$('.file-edit-time-range-div button').click(function() {
			$(this).parent().siblings().click();
		});
    }


    //初始化高级策略配置信息
    var initHighStrategyDes = function () {
        var des = "";
        des += LANG.UI_GLOBAL_STRATEGY_THREAD_NUM + ": " + $("#backupThreadNum").val();
        $('.higeDes').html(des);
        $('.higeDes').attr('title', des);
    }
    //初始化节点传输网络列表
    var initNetworkList = function () {
        var data = {};
        data.nodeuuid = $('#selectnode').val();
        var p = JSON.stringify(data);
        $.post(CONF.AJAXPATH, {m:CONF.M.NODE,f:'getNodeNetworkList',p:p}, function (d) {
            var data = JSON.parse(d);
            var transferNetwork = $('#transferNetwork');
            transferNetwork.empty();
            for (var i = 0; i < data.length; i++) {
                var name = data[i].ip + ":" + data[i].port;
                if (data[i].alias_name != "") {
                    name += "(" + data[i].alias_name + ")";
                }
                var option = '<option  value="' + data[i].network_uuid + '">' + name + '</option>';
                transferNetwork.append(option);
            }
            oldNode = $('#selectnode').val();

        });
    }

    var timeToSeconds = function(timeString) {
        // 使用冒号分割时间字符串为小时、分钟和秒
        const [hours, minutes, seconds] = timeString.split(':').map(Number);
        // 计算总秒数
        const totalSeconds = hours * 3600 + minutes * 60 + seconds;
        return totalSeconds;
    }

    //单位换算 (参数1：值；参数2：单位)
	var calSize = function(value, unit) {  
		var unitObj = {  
			'B': 1,  
			'KB': 1024,  
			'MB': 1024 * 1024,  
			'GB': 1024 * 1024 * 1024,  
			'TB': 1024 * 1024 * 1024 * 1024,  
			'PB': 1024 * 1024 * 1024 * 1024 * 1024  
		};  
		// 确保单位是大写的，与单位转换对象中的键匹配  
		unit = unit.toUpperCase();  
		// 执行转换  
		var result = value * unitObj[unit];  
		// 使用Intl.NumberFormat来格式化数字  
		var formatter = new Intl.NumberFormat('en-US', {  
			minimumFractionDigits: 0, // 最小小数位数  
			maximumFractionDigits: 0  // 最大小数位数，如果你想显示小数，可以调整这两个值  
		}); 
		// 返回格式化的字符串  
		return formatter.format(result).replace(/,/g, '');  
	} 

    var initEditInfo = function(jobUuid) {
        pAjaxRequest({}, "/api/v1/filecopy/job/" + jobUuid + "/info", "GET", function (result) {
            OLD_INFO = result.data;
            OLD_INFO.filter_info.time_range_list.time_list.forEach((item,i) => {
                OLD_INFO.filter_info.time_range_list.time_list[i]['time_fliter_time_start'] = item.time_fliter_time_start * 1000;
                OLD_INFO.filter_info.time_range_list.time_list[i]['time_fliter_time_end'] = item.time_fliter_time_end * 1000;
            });
            initListener();
            //传输策略
            initOldTransferStrategy();
            //高级配置
            initOldHighConfig();
        }, false);
    }

    var initOldHighConfig = function() {
        //复制策略
        $('#snapshot').bootstrapSwitch('state', OLD_INFO.snap_shot_flag);
        $('#copy_file_permission').bootstrapSwitch('state', OLD_INFO.permission_operate_flag);
        $('#sync_self_flag').bootstrapSwitch('state', OLD_INFO.filter_info.sync_self_flag == 1 ? true : false);
        //线程配置
        $('.backupThreadDiv').spinner("value", OLD_INFO.thread_num);
        $('.scanThreadDiv').spinner("value", OLD_INFO.scan_thread_number);
        intTransThreadNum();
        if(OLD_INFO.scan_thread_number == 1) {
            // 扫描文件速度
            $('#scanFileNum').val(OLD_INFO.scan_file_speed);
        }
        //异常配置
        $('#passfilealarmcheck').bootstrapSwitch('state', OLD_INFO.skip_file_alarm_flag);
        if(OLD_INFO.skip_file_alarm_flag) {//开启
			passAlarmChange();
			$('#passFileNum').val(OLD_INFO.skip_file_alarm_min_num);
			$('#warningpercent').val(OLD_INFO.skip_file_alarm_min_ratio);
		}
        $('#sameFile').val(OLD_INFO.same_file_strategy);
        if (OLD_INFO.same_file_strategy == 1) {//同名文件处理是覆盖
            $('#check_mode').val(OLD_INFO.filter_info.check_mode);
            if (OLD_INFO.check_mode == 2) {//校验方式是文件内容
                //校验算法
                //直接默认就是MD5
                // $('#file_verification_algorithm').val(1);
            }
            //覆盖规则
            $('#sync_rule').val(OLD_INFO.filter_info.sync_rule);
        }
        sameFileChange();
        //通配符配置
        var wildcard_list = OLD_INFO.filter_info.wildcard_list;
        $('#wildcardMatchMode input[name="icheckbox-wildcard"][data-mode=' + wildcard_list.wildcard_mode + ']').iCheck('check');
        if (wildcard_list.wildcard_mode == 0) {
            $('.wildcard-form').hide();
        } else {
            $('.wildcard-form').show();
            initOldWildcard(OLD_INFO.filter_info.wildcard_list.wildcard);
        }
        //时间配置
        var time_range_list = OLD_INFO.filter_info.time_range_list;
        $('#timeMatchMode input[name="icheckbox-time"][data-mode=' + time_range_list.time_fliter_mode + ']').iCheck('check');
        if (time_range_list.time_fliter_mode == 0) {
            $('.time-rang-form').hide();
        } else {
            $('.time-rang-form').show();
            //文件修改最近天数 是空就选中 固定时间范围
            if (time_range_list.last_days == '' || time_range_list.last_days == 0) {
                $('#rangeMode input[name="icheckbox-range-mode"][data-mode= 2]').iCheck('check');
                initOldTimeRange(time_range_list.time_list);
                $('.recent-days-form').hide();
			    $('.recent-range-form').show();
            } else {
                $('#rangeMode input[name="icheckbox-range-mode"][data-mode= 1]').iCheck('check');
                $('#copyDays').val(time_range_list.last_days);
                $('.recent-days-form').show();
			    $('.recent-range-form').hide();
                $('.time-range-info-div').hide();
                $('#timeRangeList').html('');
            }
        }
        //过载保护-忽略节点资源限制
		$('#ignoreResourceLimit').bootstrapSwitch('state', OLD_INFO.ignore_resource_limiting_flag);
    }

    var initOldTimeRange = function(timeList) {
        $('#timeInputRange').css('border-color', "#E6E6E6");
        $('.time-range-info-div').show();
        for(var i = 0; i < timeList.length; i++) {
            var des = '';
            var start_time = parseInt(timeList[i].time_fliter_time_start);
            var end_time = parseInt(timeList[i].time_fliter_time_end);
            des += LANG.UI_HISTORY_JOB_DETAIL_FILE_EDIT_TIME_RANGE + ':' + formatDate(start_time) + '-' + formatDate(end_time) + '</br>'
            $('#timeRangeList').append(`
                <div class="bg_grey" title="` + des + `" 
                    time_fliter_time_start="` + formatDate(start_time) + `"
                    time_fliter_time_end="` + formatDate(end_time) + `">
                    ` + des + `
                    <div class="label label-sm label-danger">
                        <i class="viconfont vicon-cuowu"></i>
                    </div>
                </div>
            `);
        }
    }

    var initOldWildcard = function(wildcard_list) {
        for(var i = 0; i < wildcard_list.length;i++) {
            var des = '';
            var input = wildcard_list[i].wildcard;
            $('#wildcardInput').css('border-color', "#E6E6E6");
            $('.wildcard-info-div').show();
            var effectiveObject = wildcard_list[i].wildcard_file_type;
            des += LANG.UI_HISTORY_JOB_DETAIL_WILDCARD_OBJECT + ':' + (effectiveObject == 1 ?  LANG.UI_DATA_FILE_FILE : LANG.UI_DATA_FILE_DIR) + '，';
            des += LANG.UI_FILE_WILDCARD + ':' + input;
            $('#wildcardList').append(`
                <div class="bg_grey" title="` + des + `" 
                    wildcard="` + input + `" 
                    wildcard_file_type="` + effectiveObject + `">
                    ` + des + `
                    <div class="label label-sm label-danger">
                        <i class="viconfont vicon-cuowu"></i>
                    </div>
                </div>
            `);
        }
    }

    var initOldTransferStrategy = function() {
        //压缩传输
        $('#compress_transport_flag').bootstrapSwitch('state', OLD_INFO.transport_strategy.compress_flag_value);
        //压缩等级
        if (OLD_INFO.transport_strategy.compress_flag_value) {
            $('#compressGrade').val(OLD_INFO.transport_strategy.compress_method);
        }
        compressChange();
        //加密传输
        $('#transport_encrypt_flag').bootstrapSwitch('state', OLD_INFO.transport_strategy.encrypt_flag_value);
        //加密算法
        if(OLD_INFO.transport_strategy.encrypt_flag_value) {
            $('#transferEncryptMethod').val(OLD_INFO.transport_strategy.encrypt_method);
        }
        transferEncryptChange();
    }

    //修改任务文件复制源
    var initStep1OldInfo = function() {
        var file_list = OLD_INFO.file_list;
        //将路径按目标端分组
        var groupedData = {};
        file_list.forEach(function(item) {
            if (!groupedData[item.target_path_name]) {
                groupedData[item.target_path_name] = [];
            }
            groupedData[item.target_path_name].push(item);
        });
        //开始组装插入表格和传往后台的数据
        let num = 1;
        for (var targetPath in groupedData) {
            let rowsData = [];//要加入列表的数据
            let source_path_list = [];
            let srcPath = "";//源端文件
            if (groupedData.hasOwnProperty(targetPath)) { // 检查属性是否是对象自身的，而不是继承的
                //循环获取到源端信息
                groupedData[targetPath].forEach(item => {
                    srcPath += item.path_name + '<br>';
                    source_path_list.push({
                        "file_path" : item.path_name,
                        "code_type" : item.path_code_type,
                        "file_type" : item.path_type,
                    });
                });
                rowsData.push({
                    "id": num,
                    "src_file": srcPath,
                    "des_path": groupedData[targetPath][0].target_path_name,
                    "source_path_list": source_path_list,
                    "target_path": {
                        "file_path" : groupedData[targetPath][0].target_path_name,
                        "code_type" : groupedData[targetPath][0].target_path_code_type,
                        "file_type" : 2,//文件夹
                    },
                });
                num ++;
                let data = $('#fileCopyTable').bootstrapTable('getData');
                data.push.apply(data, rowsData); // 追加数据
                $('#fileCopyTable').bootstrapTable('load', data); // 重新加载数据
            }
        }
        checkTableIsEmpty();
    }

    var getAuthModuleDes = function(type) {
        let module  = '';
        switch (parseInt(type)) {
            case 1:
                module = 'copy_fs';
                break;
            case 2:
                module = 'copy_nas';
                break;
            case 3:
                module = 'copy_hadoop';
                break;
            case 4:
                module = 'copy_obs';
                break;
        }
        return module;
    }

    var getCurrentUseLicense = () => {
		let currentUse = 1;
        let uuids = [data.copy_info.source_uuid];
        let module;
        switch (parseInt(data.copy_info.source_type)) {
            case 1:
                module = 'copy_fs';
                break;
            case 2:
                module = 'copy_nas';
                break;
            case 3:
                module = 'copy_hadoop';
                break;
            case 4:
                module = 'copy_obs';
                break;
        }
        return new Promise((resolve) => {
            let params = {
                module: module,
                currentUse,
                showMetronic: true,
                judge: true,
                showAlertMsg: true,
				uuids: uuids,
            }
            getModuleAuthInfo(params).then(result => {
                if (result) {
                    resolve();
                }
            });
        });
    };
    
    return {
        init: () => {
            initPage()
        },

    };
}();

jQuery(document).ready(function () {
    FileCopyTask.init();
});




