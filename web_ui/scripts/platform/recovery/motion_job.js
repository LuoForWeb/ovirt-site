var MotionJobDetails = function () {
    var taskUuid;
    var expandIndex = null
    var errorflag  =  false;
    var initTableFlag = false; // 列表初始化标志
    var isLoading = false; // 标记是否已经加载过策略信息
    var isFinal = false; // 标记是否点击了完成迁移按钮的过渡

    // 存储ztree配置和数据
    let ztreeDetails, ztreeId, zTreeSetting, zTreeData;
    var userUuid = '';
    //初始化基本信息
    var initBasicInfo = function(){
        var updateInterval = 3000;
        var init = function(){
            if(0 == $('#taskuuid').size()){
                clearTimeout(timerTask.JobDetails_taskRunningInfo);
                return;
            }
            var parmas = {};
            if (!isLoading) {
                parmas.simple = 2;
            }
            pAjaxRequest(parmas, "/api/v1/recovery/"+taskUuid+"/migrates", "GET", function (result) {
                if (result.code !== 0) {
                    setTimeout(function(){
                        LOCATION('./content/platform/jobs/jobs.php?uuid=' + taskUuid, 'task');
                    }, 1000);
                    UIToastr.showError(LANG.UI_PUBLIC_TIPS, result.message);
                    return;
                }

                isLoading = true;
                //如果任务类型变成了瞬时恢复
                if ([CONF.TASK_TYPE.OS_INSTANT_RECOVERY, CONF.TASK_TYPE.INSTANT_RECOVERY].includes(result.data.job_type)) {
                    clearTimeout(timerTask.JobDetails_taskRunningInfo);
                    if(errorflag){
                        //错误
                        UIToastr.showWarning(LANG.UI_OS_TASK_FAIL, LANG.UI_OS_TASK_FAIL_JUMP_TO_PAGE_TIPS);
                    }else{
                        //正确
                        $('#total-progress').css({width: '100%'});
                        $('#progressright').html('100%');
                        UIToastr.showSuccess(LANG.UI_JOB_OVER_TITLE, LANG.UI_JOB_MOTION_OVER_VALUE);
                    }
                    setTimeout(function(){
                        LOCATION('./content/platform/recovery/instantaneous_job.php?uuid=' + taskUuid, 'task');
                    }, 5000);
                    return;
                }

                timerTask.JobDetails_taskRunningInfo = setTimeout(init, updateInterval);
                setBasicInfo(result.data, timerTask.JobDetails_taskRunningInfo)
                if (parmas.simple == 2) {
                    // 初始化策略信息
                    initStragetgy(result.data);
                }

            });
        }
        init();
    }
    //设置基本信息
    var setBasicInfo =  function(data, timeoutID){
        //恢复任务的对象列表表格信息处理
        initRecoveryTable(data.module_type); // 初始化对象列表
        userUuid = data.user_uuid;

        //设置图片
        initJobImgInfo(data);
        //tab1
        $('#taskName').html(data.job_name);
        $('#taskType').html(data.job_type_des);
        if(data.job_status_value){
            $('#status').html('<span class="label ' + getStatusLevelClass(data.status_num) + '" >' + data.job_status_value + '</span>');
        }
        $('#totalSize').html(data.total_size);
        $('#currentSize').html(data.current_size);
        $('#speed').html(data.speed);
        $('#progress').html(data.progress);
        $('#startTime').html(data.start_time);
        $('#intervalTime').html(data.interval_time);
        // 任务运行阶段
        $('#stage').html(data.stage);
        //tab2
        $('#createTime').html(data.create_time);
        // 完成迁移
        var auto_migrate_flag = LANG.UI_PLATFORM_RECOVERY_AUTO_COMPLETE;
        if (data.auto_migrate_flag == false) {
            // 手动完成
            auto_migrate_flag = LANG.UI_PLATFORM_RECOVERY_HAND_COMPLETE + ',' + LANG.UI_PLATFORM_RECOVERY_DATA_SYNC_INTERVAL + ':' + data.auto_migrate_interval + LANG.UI_JOB_MINUTE;
        }
        $('#auto_migrate_flag2').html(auto_migrate_flag);

        $('#stop_instant_task_flag2').html(getFlagLevelInfo2(data.stop_instant_task_flag));

        // 过载保护
        $('#ignore_resource_limit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));

        //if (data.module_type == CONF.MODULE_TYPE.VM) {
        // 隐藏重试策略
        // $('.retry_div_block').hide();
        //}

        $('#total-progress').css({width: data.total_progress});
        $('#progressright').html(data.progress);
    }
    //设置图片
    var initJobImgInfo =  function(data){
        //设置服务器IP
        $('#serverip').html(data.server_ip);
        //设置主机IP
        $('#hostip').html(data.host_ip);
        $('#host_name2').html(data.host_ip);
        //设置瞬时恢复原主机和迁移主机ip
        if(data.recover_hostname != '()'){
            $('#recoverhostname').html(data.recover_hostname);
        }
        if(data.motion_hostname != '()'){
            $('#motionhostname').html(data.motion_hostname);
        }
        //设置主机状态
        if(data.online_flag){
            $("#agent_offline").hide();
            $("#agent_online").show();
        }else{
            $("#agent_offline").show();
            $("#agent_online").hide();
        }

        if (data.module_type == CONF.MODULE_TYPE.VM) {
            // 如果是虚拟化 那么更改下图片
            $('#agent_offline').attr('src', './img/platform/recovery/vm_offline.svg');
            $('#agent_online').attr('src', './img/platform/recovery/vm_online.svg');
            $('#host_name_des').html(LANG.UI_VCENTER_HOST);
            $('#host_name_des2').html(LANG.UI_VCENTER_HOST+':');
        } else {
            // 整机
            $('#agent_offline').attr('src', './img/platform/recovery/agent_offline.svg');
            $('#agent_online').attr('src', './img/platform/recovery/agent_online.svg');
            $('#host_name_des2').html(LANG.UI_PUBLIC_HOST+':');
            $('.vm_tab i').removeClass('vicon-pt_report_vm_report');
            $('.vm_tab i').addClass('vicon-dbhost');
        }

        // 设置显示停止瞬时恢复读写按钮 migrate_status
        if (data.migrate_status == 1) {
            // 可点击
            // Bug #29106 不可点击停止迁移
            setControlBtn('stopJob', false);

            setControlBtn('stopInstantJob', true);
            $('#stopInstantJob').unbind().on('click', function(){
                if($(this).find('a').hasClass('disablebtn')){
                    return true;
                }
                checkOperateAuth(
                    {
                        type: 1,
                        user_uuid: userUuid,
                        auth: 'current_job'
                    },
                    function (){
                        operateJob(LANG.UI_PLATFORM_RECOVERY_JOB_STOP_INSTANT_OPERATION, '/api/v1/recovery/job/'+taskUuid+'/motion', 'POST', true)
                    }
                );
            });
        } else {
            // 不可点击
            setControlBtn('stopInstantJob', false);
            if (!isFinal) {
                // 可点击停止迁移
                stopTask();
            }
        }

        if (data.job_status != CONF.TASK_STATUS.RUNNING) {
            // 不是运行中，都不能点击
            setControlBtn('stopJob', false);
            setControlBtn('stopInstantJob', false);
        }

        if (data.module_type == CONF.MODULE_TYPE.VM) {
            $('.right_div_item').css({
                'margin-left': '15px'
            });
        }

        chagngeStatusPic(data.job_status);
    }

    // stop task
    var stopTask = function (){
        setControlBtn('stopJob', true);
        $('#stopJob').unbind().on('click', function(){
            if($(this).find('a').hasClass('disablebtn')){
                return true;
            }
            checkOperateAuth(
                {
                    type: 1,
                    user_uuid: userUuid,
                    auth: 'current_job'
                },
                function (){
                    bootbox.prompt({
                        title: LANG.UI_PLATFORM_RECOVERY_STOP_JOB_MOTION_TIPS,
                        inputType: 'password',
                        callback: debounce(function (r) {
                            if (r == null) return;
                            // 执行操作验证密码
                            Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
                            var encrypt = new JSEncrypt();
                            encrypt.setPublicKey(CONF.PUBLIC_KEY);
                            var password = encrypt.encrypt(r);
                            var that = this;
                            pAjaxRequest({password: password}, '/api/v1/users/check/password', 'POST', function (result) {
                                Metronic.unblockUI('#jobDetail');
                                if (result.code == 0) {
                                    $(that).modal('hide');
                                    // 执行停止操作
                                    operateJob(LANG.UI_PLATFORM_RECOVERY_STOP_JOB,  '/api/v1/jobs/stop/'+taskUuid);
                                } else {
                                    UIToastr.showWarning(result.title, result.message);
                                }
                            });
                        }, 300, false)
                    })
                }
            );
        });
    }
    // 单独处理各个状态的图片显示问题
    var chagngeStatusPic = function (status) {
        // 默认运行中图标
        var src = './img/platform/recovery/data_transmission.gif';
        //设置任务状态
        switch(status){
            case CONF.TASK_STATUS.RUNNING: //运行
            case CONF.TASK_STATUS.STOPPING://停止中
            case CONF.TASK_STATUS.PREPARING://准备中
            case CONF.TASK_STATUS.PAUSING://任务暂停中
            case CONF.TASK_STATUS.STARTING://启动中
            case CONF.TASK_STATUS.FINISHED://已完成
            case CONF.TASK_STATUS.SUCCESSED://任务成功
                // $('#total-progress').css({width: '0%'});
                src = './img/platform/recovery/data_transmission.gif';
                break;
            case CONF.TASK_STATUS.WAITTING: //等待
                src = './img/platform/recovery/320loading.svg';
                break;
            case CONF.TASK_STATUS.PAUSED: //暂停
            case CONF.TASK_STATUS.STOPPED: //停止
                src = './img/platform/recovery/320parse.svg';
                break;
            case CONF.TASK_STATUS.NETWORK_FAULT: //网络故障
            case CONF.TASK_STATUS.ABNORMAL://任务已经完成但异常
                src = './img/platform/recovery/320warning.svg';
                break;
            case CONF.TASK_STATUS.ERROR://错误
                src = './img/platform/recovery/320error.svg';
                break;
        }
        $('#tasknormal img').attr('src', src);
    }

    //得到开启和关闭的HTML内容
    var getFlagLevelInfo = function(flag){
        var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
        if(!flag){
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        }
        return html;
    }

    //得到开启和关闭的HTML内容
    var getFlagLevelInfo2 = function(flag){
        var html = '<span class="label label-success">' + LANG.UI_PUBLIC_YES + '</span>';
        if(!flag){
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_NO + '</span>';
        }
        return html;
    }
    // 操作任务-中间状态
    /*var operateJob = function (operate, url, status = 2, method = 'POST'){
        Metronic.blockUI({target: '#tab_1_1',animate: true,cenrerY: true});
        if (status == 1) {
            // 需要给个提示。根据提示来选择调用接口
            bootbox.dialog({
                title: LANG.UI_PLATFORM_RECOVERY_STOP_MOTION,
                message: '<p>'+LANG.UI_PLATFORM_RECOVERY_STOP_MOTION_TIPS+'</p>',
                buttons: {
                    cancel: {
                        label: LANG.UI_PUBLIC_CANCEL,
                        className: "btn-secondary",
                        callback: function() {
                            // 用户点击了“取消”按钮
                            operateJobs(LANG.UI_PLATFORM_RECOVERY_STOP_JOB, '/api/v1/jobs/stop/' + taskUuid);
                        }
                    },
                    confirm: {
                        label: LANG.UI_PUBLIC_CONFIRM,
                        className: "btn-primary",
                        callback: function() {
                            // 用户点击了“确认”按钮
                            operateJobs(LANG.UI_PLATFORM_RECOVERY_JOB_STOP_INSTANT_OPERATION, '/api/v1/recovery/job/' + taskUuid + '/motion');
                        }
                    }
                },
                onEscape: function() {
                    // 用户按下了 ESC 键或点击了关闭按钮
                    Metronic.unblockUI('#tab_1_1');
                }
            }).on('hide.bs.modal', function(e) {
                // 监听模态框即将隐藏的事件
                console.log("Dialog is about to be hidden");
            }).on('hidden.bs.modal', function(e) {
                // 监听模态框已经隐藏的事件
                console.log("Dialog has been hidden");
            });
        } else {
            // 停止迁移
            operateJobs(LANG.UI_PLATFORM_RECOVERY_STOP_JOB,  '/api/v1/jobs/stop/'+taskUuid);
        }
    }*/

    // 操作任务
    var operateJob = function (operate, url, method = 'POST', finalFlag = false){
        Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
        pAjaxRequest({}, url, method, function (result) {
            Metronic.unblockUI('#jobDetail');
            if (result.code == 0 || result.code == 200) {
                setControlBtn('stopInstantJob', false);
                if (finalFlag) {
                    isFinal = true;
                }
                UIToastr.showSuccess(operate, result.message)
            } else {
                UIToastr.showError(operate, result.message);
            }
        });
    }

    //设置按钮是否可用
    var setControlBtn = function(id, available){
        if(available){
            $("#" + id).find('a').removeClass('disablebtn');
        }else{
            $("#" + id).find('a').addClass('disablebtn');
        }
    }
    //得到状态的显示类型
    var getStatusLevelClass = function(level){
        var levelClass = '';
        switch(level){
            case 1:
                levelClass = "label-info";
                break;
            case 12:
            case 2:
            case 3:
                levelClass = "label-success";
                break;
            case 4:
                levelClass = "label-default";
                break;
            case 7:
            case 19:
                levelClass = "label-warning";
                break;
            case 6:
            case 8:
                levelClass = "label-danger";
                break;
            default:
                levelClass = "label-info";
                break;
        }
        return levelClass;
    }
    //初始化日志表格
    const initLogGrid = function(){
        $('#runninglog').runningLog({
            job_uuid: taskUuid
        });
    }

    var initSwiper = function(){
        //先给swiper插件里面的元素加上class
        $('.swiper-detail').addClass('swiper');
        $('.swiper-detail').attr('style','overflow: hidden');
        $('.swiper-detail').find('ul.nav.nav-tabs ').addClass('swiper-wrapper');
        $('.swiper-detail').find('ul.nav.nav-tabs > li').addClass('swiper-slide widthauto');
        var mySwiper = new Swiper ('.swiper',{
            slidesPerView :'auto',
            freeMode: false,	//惯性滑动且不会贴合
            navigation: {
                nextEl: '.swiper-button-next_detail',
                prevEl: '.swiper-button-prev_detail',
                disabledClass: 'display-none',
            },
            allowTouchMove:false
        });
        var predisable = mySwiper.navigation.prevEl.ariaDisabled == 'true' ? true : false;
        var nextdisable = mySwiper.navigation.nextEl.ariaDisabled == 'true' ? true : false;
        if(predisable && nextdisable){
            $('.swiper-detail').addClass('swiper-no-swiping')
        }
    }

    /**
     * 设置脚本抽屉内容
     * @param {string} title
     * @param {string} scriptType
     * @param {string} scriptContent
     */
    const setScriptDrawerContent = (title, scriptType, scriptContent) => {
        $('#scriptContentDrawer .drawer-title .name').html(title);
        $('#scriptContentType').html(getScriptDesByType(scriptType));
        $('#scriptContent').html(scriptContent);
        $('#scriptContentDrawer').drawer('show');
    };
    /**
     * 获取脚本描述
     */
    const getScriptDesByType = scriptType => {
        scriptType = parseInt(scriptType);
        switch (scriptType) {
            case 1:
                return LANG.UI_PUBLIC_SCRIPT_TYPE1;
            case 2:
                return LANG.UI_PUBLIC_SCRIPT_TYPE2;
            case 3:
                return LANG.UI_PUBLIC_SCRIPT_TYPE3;
            case 4:
                return LANG.UI_PUBLIC_SCRIPT_TYPE4;
            case 5:
                return LANG.UI_PUBLIC_SCRIPT_TYPE5;
            case 6:
                return LANG.UI_PUBLIC_SCRIPT_TYPE6;
            case 7:
                return LANG.UI_PUBLIC_SCRIPT_TYPE7;
            case 8:
                return LANG.UI_PUBLIC_SCRIPT_TYPE8;
            case 9:
                return LANG.UI_PUBLIC_SCRIPT_TYPE9;
            default:
                return '--';
        }
    };

    //初始化恢复任务对象表格
    var initRecoveryTable = function (module_type) {
        if (!initTableFlag) {
            // 需要根据 module_type 来决定 下面的展开详情的 恢复目标显示情况
            let options = {
                vin_url: '/api/v1/recovery/job/'+taskUuid+'/object',
                vin_method: 'GET',
                queryParamsType: '',
                queryParams: {},
                pagination: false,
                sidePagination: 'server',
                pageNumber: 1,
                pageSize: 20,
                pageList: [10, 20, 50, 100],
                paginationLoop: false,
                uniqueId: 'no',
                changeHeightBtn: true, //改变高度按钮
                batchOperation: true, // 批量操作
                sortable: false,
                // sortName: 'id',
                // sortOrder: 'desc',
                resizable: true,
                singleSelect: false,
                detailView: true,
                detailFormatter: function (index, row, element) {
                    let treeId = 'job_tree_id' + row.uuid;
                    //初始化为一个树形结构
                    ztreeId = treeId;
                    zTreeData = row.detail.disk;
                    let str = '<table class="vm-detail-table">';
                    str += '<tr><td style="width: 200px">' + LANG.UI_RECOVERY_TIMEPOINT + ': </td><td>' + row.detail.timepoint_des + '</td></tr>';
                    if (module_type == CONF.MODULE_TYPE.VM) {
                        str += '<tr><td style="width: 200px">'+LANG.UI_PLATFORM_RECOVERY_SOURCE_OBJECT+': </td><td>' + row.detail.path + '</td></tr>';
                        str += `<tr>
								<td style="width: 200px">${LANG.UI_RECOVERY_GOAL}: </td>
								<td>${LANG.UI_VCENTER_VCENTER}: ${row.detail.d_vcenter_iP}</br> ${LANG.UI_VCENTER_HOST}: ${row.detail.d_host_op} </br>${LANG.UI_VCENTER_VM}: ${row.detail.d_name}</td>
							</tr>
							 <tr><td style="width: 200px">${LANG.UI_PLATFORM_RECOVERY_JOB_DISK_COMPARE}: </td><td>` + row.detail.disk + `</td></tr>`;
                    } else {
                        // 整机
                        str += '<tr><td style="width: 200px">'+LANG.UI_PLATFORM_RECOVERY_SOURCE_OBJECT+': </td><td>' + row.source_name + '</td></tr>';

                        let after_script_des = '';
                        var after_task_script = row.script_list.after_task_script;
                        if(after_task_script.length == 0){
                            after_script_des += LANG.UI_PUBLIC_NOTHING;
                        }else{
                            for(let i = 0; i < after_task_script.length; i++){
                                let info = 'value_agent_uuid="'+row.uuid+'"'+'value_script_name="'+after_task_script[i]+'"';
                                after_script_des += '<a class="script_show" '+info+' style="margin-right: 10px;">' + after_task_script[i] + '</a>';
                            }
                        }
                        str += '<tr><td style="width: 200px">' + LANG.UI_PLATFORM_RECOVERY_SCRIPT_RECOVERY + ': </td><td>' +after_script_des + '</td></tr>';

                        str += `<tr>
								<td style="width: 200px">${LANG.UI_RECOVERY_GOAL}: </td>
								<td> ${LANG.UI_PUBLIC_HOST}: ${row.detail.d_name}</td>
							</tr>
							<tr>
							<td style="width: 200px;position: absolute;">${LANG.UI_PLATFORM_RECOVERY_JOB_DISK_COMPARE}: </td>
							<td> <ul id="`+treeId+`" class="ztree" style="display:inline-block"></ul></td>
							</tr>`;
                    }
                    str += '</table>';
                    $(element).append(str);
                    if (module_type != CONF.MODULE_TYPE.VM) {
                        $('#'+treeId).html('');
                        setTaskDevTree(row.detail.disk, treeId);
                    }
                },
                onRefresh: function (params) {
                    $("#vm-table").bootstrapTable('hideLoading');
                },
                columns: [ //列定义
                    {
                        field: 'no',
                        title: LANG.UI_PUBLIC_TABLE_ID,
                    },
                    {
                        field: 'source_name',
                        title: LANG.UI_PLATFORM_RECOVERY_SOURCE_OBJECT_NAME,
                        // width: '300px',
                    },
                    {
                        field: 'name',
                        title: LANG.UI_PLATFORM_RECOVERY_TARGET_OBJECT_NAME,
                        formatter: (value, row) => {
                            return row.detail.d_name;
                        },
                        // width: '300px',
                    },
                    {
                        field: 'size',
                        title: LANG.UI_PLATFORM_RECOVERY_TARGET_SIZE,
                        // width: '100px',
                    },
                    {
                        field: 'valid_size',
                        title: LANG.UI_PUBLIC_VM_VALID_SIZE,
                        // width: '200px',
                    },
                    {
                        field: 'transport_size',
                        title: LANG.UI_PUBLIC_TRANSFER_SIZE,
                        // width: '200px',
                    },
                    {
                        field: 'write_size',
                        title:  LANG.UI_PUBLIC_REAL_SIZE,
                        // width: '200px',
                    },
                    {
                        field: 'speed',
                        title: LANG.UI_TASK_AWS_INSTANCE_SPEED,
                        // width: '200px',
                    },
                    {
                        field: 'percent',
                        title: LANG.UI_TASK_AWS_TRANS_PROGRESS,
                        // width: '200px',
                    },
                    {
                        field: 'status',
                        title: LANG.UI_PUBLIC_STATUS,
                        // width: '200px',
                    },
                    {
                        field: 'description',
                        title: LANG.UI_PUBLIC_DESCRIPTION,
                        // width: '200px',
                    }
                ],
                onPostBody: (data) => {
                    if (ztreeDetails) {
                        var expandedNodeIds = [];
                        var allNodes = ztreeDetails.transformToArray(ztreeDetails.getNodes());
                        // 获取之前的展开节点
                        allNodes.forEach(function(node) {
                            if (node.open) {
                                expandedNodeIds.push(node.id); // 保存展开状态的节点 ID
                            }
                        });
                        // 给节点对象重新配置open属性
                        zTreeData.forEach(function(node) {
                            if ($.inArray(node.id, expandedNodeIds) != -1) {
                                node.open = true;
                            } else {
                                node.open = false;
                            }
                        });
                        ztreeDetails = $.fn.zTree.destroy(ztreeId);
                        ztreeDetails = $.fn.zTree.init($("#"+ztreeId), zTreeSetting, zTreeData);
                    }

                    //先解除绑定再初始化点击事件
                    $(".script_show").off("click").on("click",function(){
                        Metronic.blockUI({target: '#vm-table', animate: true});
                        let requestData = {};
                        //获取当前点击的a标签的value_agent_uuid值
                        requestData.uuid = $(this).attr('value_agent_uuid');
                        //value_script_name
                        requestData.name = $(this).attr('value_script_name');
                        requestData.flag = $(this).attr('value_flag');
                        //获取任务uuid
                        requestData.job_uuid = $('#taskuuid').val();
                        pAjaxRequest(requestData, "/api/v1/recovery/job/script", "GET",function (res) {
                            Metronic.unblockUI('#vm-table');
                            if (res.code == 0) {
                                setScriptDrawerContent(res.data.script_name, res.data.script_type,res.data.script_content)
                            } else {
                                UIToastr.showError(LANG.UI_PUBLIC_TIPS, res.message);
                            }
                        });
                    });
                }
            }
            $('#vm-table').baseTableConfig().init(options);
            initTableFlag = true;
        } else {
            $('#vm-table').bootstrapTable('refresh', {query: {job_uuid: taskUuid}});
        }
    }

    //渲染设备树形结构
    var setTaskDevTree = function (zNodes, treeId) {
        var setting = {
            check: {
                enable: false,
                nocheckInherit: false
            },
            data: {
                simpleData: {
                    enable: true,
                    rootPId: 0
                },key: {
                    title: "name"
                }
            },
            view: {
                fontCss: getFontCss,
                showLine: false,
                showIcon: false,
                nameIsHTML: true
            },
        };
        zTreeSetting = setting;
        ztreeDetails = $.fn.zTree.init($("#"+treeId), setting, zNodes);
        ztreeDetails.expandAll(true);
    }
    var getFontCss = function(treeId, treeNode) {
        var css = {color:"#333", "font-weight":"normal"};
        if(!!treeNode.inbackup){
            css = {color:"green", "font-weight":"bold"};
        }
        if(!!treeNode.highlight){
            //搜索使用的样式
            css = {color:"#A60000", "font-weight":"bold"};
        }
        return css;
    }

    // 初始化策略信息
    var initStragetgy = function (data) {
        $('#taskDirection').html(data.direction);
        $('#taskSourcePoint').html(data.source_point);

        $('#threadCount').html(data.thread_num);
        $('#speedlimit').html(data.speed_limit.value);
        $('#speedlimit').prop('title', data.speed_limit.des);
        // 任务等级
        var task_priority = data.speed_limit.task_priority;
        if(task_priority){
            switch(data.speed_limit.task_priority) {
                case 1:
                    task_priority = LANG.UI_JOB_TASK_PRIORITY_PRIMARY;
                    break;
                case 2:
                    task_priority = LANG.UI_JOB_TASK_PRIORITY_HIGH;
                    break;
                case 3:
                    task_priority = LANG.UI_JOB_TASK_PRIORITY_HIGHEST;
                    break;
            }
            $('#taskPriority').html(task_priority);
        }else{
            $('.taskPriorityDiv').hide();
        }

        //设置传输网络
        if (data.net_model == 2) {
            $('.transportNetworkdiv').show();
            var network = LANG.UI_NODE_NETWORK_MODE_AUTO_MATCH;
            if (data.transport_strategy.network != '') {
                network = data.transport_strategy.network;
            }
            $('#transportNetwork').html(network);
        }

        // ...

        // 重试策略
        if (Array.isArray(data.retry_strategy) && !data.retry_strategy.length) {
            $('.retryDiv').hide();
        } else {
            $('#network_retry_times').html(data.retry_strategy.network_retry_times + LANG.UI_PUBLIC_UNIT_COUNT);
            $('#network_retry_interval').html(data.retry_strategy.network_retry_interval + LANG.UI_PUBLIC_SECOND);
            $('#op_retry_flag').html(getFlagLevelInfo(data.retry_strategy.op_retry_flag));
            if (data.retry_strategy.op_retry_flag) {
                $('.op_retry_div').show();
            } else {
                $('.op_retry_div').hide();
            }
            $('#op_retry_times').html(data.retry_strategy.op_retry_times + LANG.UI_PUBLIC_UNIT_COUNT);
            $('#op_retry_interval').html(data.retry_strategy.op_retry_interval + LANG.UI_PUBLIC_SECOND);
            $('#task_retry_flag').html(getFlagLevelInfo(data.retry_strategy.task_retry_flag));
            if (data.retry_strategy.task_retry_flag) {
                $('.task_retry_div').show();
            } else {
                $('.task_retry_div').hide();
            }
            let task_retry_object = '';
            if (1 == data.retry_strategy.task_retry_object) {
                task_retry_object = LANG.UI_RETRY_FAILED_OBJS_IN_TASK;
            } else if (2 == data.retry_strategy.task_retry_object) {
                task_retry_object = LANG.UI_RETRY_ALL_OBJS_IN_TASK;
            }
            $('#task_retry_object').html(task_retry_object);
            $('#task_retry_times').html(data.retry_strategy.task_retry_times + LANG.UI_PUBLIC_UNIT_COUNT);
            var minutes =  Math.floor(parseInt(data.retry_strategy.task_retry_interval) / 60);
            $('#task_retry_interval').html(minutes + LANG.UI_PUBLIC_MINUTE);
            $('.retryDiv').show();
        }

        // 过载保护
        $('#ignore_resource_limit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));

        var hypervisor = data.hypervisor;
        if (hypervisor == 47) {
            // scp的不显示更多详情
            $('.show-more-div').hide();
        } else {
            $('.show-more-div').show();
        }

        // 时间策略
        let timeDes;
        if (data.time_strategy.length) {
            let timeStrategy = getTimeStrategy(data.time_strategy);
            timeDes = timeStrategy.full;
        } else {
            timeDes = LANG.UI_JOB_ONCE_TIME_RECOVER;
        }
        $('#recoveryTimeDes').html(timeDes);

        // 如果是整机的话
        if (data.module_type != CONF.MODULE_TYPE.VM) {
            initOsStrategy(data);
        } else {
            initVmStrategy(data);
        }
    }

    // 初始化整机的策略
    var initOsStrategy = function (data){
        $('.machine_strategy').show();
        var taskConfInfo = data;
        $("#transportEncrypt1").html(getFlagLevelInfo(taskConfInfo.transport_strategy.encrypt));  //传输加密
        if(taskConfInfo.transport_strategy.encrypt){
            $('.transfer-encrypt-method-div1').show();
            let encryptMethod = taskConfInfo.transport_strategy.encrypt;
            let method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_RSA;
            if(encryptMethod == 2){
                method = LANG.UI_GLOBAL_STRATEGY_TRANSFER_ENCRYPT_SM;
            }
            $('#transferEncryptMethod1').html(method);
        }else{
            $('.transfer-encrypt-method-div1').hide();
        }
        if (CONF.FUNCTIONS.includes('multithread')) {
            // 有多线程
            $('#transportThreadNum1').html(taskConfInfo.thread_num + LANG.UI_PUBLIC_NUM);  //传输线程个数
        } else {
            $('#transportThreadNum1').parent().hide();
        }
    }

    // 初始化虚拟机的策略
    var initVmStrategy = function (data){
        $('.vm_strategy').show();

        // 传输策略
        let transportStrategy = data.transport_strategy;
        // 合并参数
        transportStrategy = $.extend(transportStrategy, {
            thread_num: data.thread_num,
            applianceFlag: data.appliance_flag,
            applianceDes: data.appliance_des,
            agent_pool_info: data.agent_pool_info,
            transport_ip_segment: data.transport_ip_segment
        });
        $.fn.showVmJobTransferStrategy(transportStrategy, 3);

        //隐藏数据块大小
        $('.blocksizediv').hide();

        //磁带隐藏部分信息
        if (data.tape_strategy) {
            $('.threadCountDiv').hide();
        }
    }

    var initListener = function (){
        let hideTimeout;

        $('.show-motion-div').on({
            mouseenter: function() {
                // 清除任何现有的隐藏计时器
                clearTimeout(hideTimeout);
                // 显示 #motion-div-block
                $('#motion-div-block').show();
            },
            mouseleave: function() {
                // 设置一个短暂的延迟以隐藏 #motion-div-block
                hideTimeout = setTimeout(function() {
                    $('#motion-div-block').hide();
                }, 300); // 延迟300毫秒后隐藏
            }
        });
        // 如果需要对 #motion-div-block 内部也应用相同的逻辑
        $('#motion-div-block').on({
            mouseenter: function() {
                clearTimeout(hideTimeout);
            },
            mouseleave: function() {
                hideTimeout = setTimeout(function() {
                    $('#motion-div-block').hide();
                }, 300);
            }
        });
    }

    return{
        init:function(){
            taskUuid = $("#taskuuid").val();
            initListener();
            // initSwiper();
            initBasicInfo(); //初始化基本信息
            initLogGrid();   //初始化日志表格
        }
    }

}();

jQuery(document).ready(function(){
    MotionJobDetails.init();
})
