var InstantJobDetails = function () {
    var taskUuid;
    var initTableFlag = false; //对象列表初始化标志
    var isLoading = false; // 标记是否已经加载过策略信息

    // 存储ztree配置和数据
    let ztreeDetails, ztreeId, zTreeSetting, zTreeData;
    var userUuid = '';

    // 初始化任务基本信息
    var initBaseInfo = function(){
        var updateInterval = 2000;
        var update = function(){
            if(0 == $('#instantflag').size()){
                cleanTime();
                return;
            }
            var parmas = {};
            if (!isLoading) {
                parmas.simple = 2;
            }
            pAjaxRequest(parmas, "/api/v1/recovery/"+taskUuid+"/instantaneous/", "GET", function (result){
                if (result.code != 0) {
                    UIToastr.showError(LANG.UI_PUBLIC_TIPS, result.message);
                    cleanTime();
                    // 出错了 返回任务列表
                    LOCATION('./content/platform/jobs/jobs.php?uuid=' + taskUuid, 'task');
                    return;
                }
                var data = result.data
                if(
                    $.inArray(data.job_type, [CONF.TASK_TYPE.OS_INSTANT_RECOVERY_MOTION, CONF.TASK_TYPE.INSTANT_RECOVERY_MOTION]) !== -1
                ){
                    //如果是迁移,直接跳转到迁移任务监控
                    cleanTime();
                    LOCATION('./content/platform/recovery/motion_job.php?uuid=' + taskUuid, 'task');
                    return;
                }

                var data = result.data;
                userUuid = data.user_uuid;
                $('#taskName').html(data.job_name);
                $('#taskStage').html(data.current_stage_value);
                $('#taskType').html(data.job_type_des);
                $('#startTime').html(data.start_time);
                $('#intervalTime').html(data.interval_time);
                $('#goalType').html(data.goal_type);
                if (data.hypervisor != '') {
                    $('#hypervisorType').html(data.hypervisor);
                } else {
                    $('#hypervisorType').parent().hide();
                }

                if (data.hypervisor_type == 108) {
                    // 到内嵌隐藏挂载点
                    $('.amoutInfo').hide();
                }

                if (!CONF.FUNCTIONS.includes('integrity')) {
                    // 没有有完整性校验功能
                    $('.completeConfig').hide();
                }
                if (!CONF.FUNCTIONS.includes('virusKill')) {
                    // 没有病毒查杀功能
                    $('.virusConfig').hide();
                }

                // 过载保护
                $('#ignore_resource_limit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));

                $('#createTime').html(data.create_time);
                $('#verifyStrategy').html(data.create_time);

                //设置服务器IP
                $('#serverip').html(data.server_ip);
                //设置主机IP
                $('#hostip').html(data.host_ip);
                $('#hostip').attr('title', data.host_ip);
                if (data.job_status == CONF.TASK_STATUS.RUNNING && data.console_url != '') {
                    var icon = '<i class="viconfont vicon-web-console" style="color:#0FBF98;margin-bottom:-3px;display:table-caption;"></i>';
                    var titles = icon + data.host_ip ;
                    var url = '<a href="javascript:void(0);" id="jumpUrl" title="'+data.vm_name+'">'+ titles +'</a>';
                    $('#hostip').html(url);
                    $('#jumpUrl').unbind().on('click', function(){
                        opereate('look', data.vm_uuid, data.console_url);
                    });
                }
                $('#serverIP').html(data.server_ip);
                $('#serverType').html(data.mount_service);
                //设置主机状态
                if(data.online_flag){
                    $("#agent_offline").hide();
                    $("#agent_online").show();
                }else{
                    $("#agent_offline").show();
                    $("#agent_online").hide();
                }

                if (parmas.simple == 2) {
                    isLoading = true;
                    if (data.module_type == CONF.MODULE_TYPE.VM) {
                        // 如果是虚拟化 那么更改下图片
                        $('#agent_offline').attr('src', './img/platform/recovery/vm_stop.svg');
                        $('#agent_online').attr('src', './img/platform/recovery/vm_line.svg');
                        $('#agent_offline').parent().parent().css({
                            'margin-left': 0,
                        })
                        $('#host_name_des').html(LANG.UI_VCENTER_HOST);
                    } else {
                        $('.vm_tab i').removeClass('vicon-pt_report_vm_report');
                        $('.vm_tab i').addClass('vicon-dbhost');
                    }

                    // 初始化安全策略信息
                    $('#virusConfig').html($.fn.getVirusConfigDes(data.safe_strategy, 'recovery'));
                    var complete = '';
                    switch (data.safe_strategy.integrity_check_config.recovery_error_policy) {
                        case 0: // 中断恢复
                            complete = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_TERMINAL_RECOVERY;
                            break;
                        case 1: // 继续恢复
                            complete = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_CONTINUE_RECOVERY;
                            break;
                        case 2: // 恢复到无网络环境
                            complete = LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_ABNORMAL_RECOVERY_TO_NO_NETWORK;
                            break;
                    }
                    if (!data.safe_strategy.integrity_check_flag) {
                        complete = LANG.UI_BACKUP_DATA_POINT_DES_NOT_CONFIGED;
                    }
                    $('#completeConfig').html(LANG.UI_SAFE_STRATEGY_INTEGRITY_CHECK_BACKUP_POINT_SETTING + complete);
                }

                //if (data.module_type == CONF.MODULE_TYPE.VM) {
                $('.vm_tab').show();
                // 虚拟化才有对象列表
                initRecoveryTable(data.module_type); // 初始化对象列表
                /*} else {
                    // 整机屏蔽对象列表
                    $('.vm_tab').hide();
                }*/


                // 设置按钮事件
                setBtnStatus(data.job_status, data);

                //设置任务状态
                $('#status').html('<span class="label ' + getStatusLevelClass(data.job_status) + '" >' + data.job_status_des + '</span>');
                chagngeStatusPic(data.job_status);

                timerTask.InstantJobDetails_img = setTimeout(update, updateInterval);
            });
        };
        update();
    }

    function opereate(type = 'look', vmUuid, url = '') {
        Metronic.blockUI({target: '#vm_list_table',animate: true,cenrerY: true});
        pAjaxRequest({type:type}, "/api/v1/virtual/operate/"+vmUuid, "POST", function (result) {
            Metronic.unblockUI('#vm_list_table');
            if (result.code == 0) {
                window.open(url, '_blank');
            } else {
                UIToastr.showError(LANG.UI_VM_MACHINE_OPERATION, result.message);
            }
        });
    }

    //得到开启和关闭的HTML内容
    var getFlagLevelInfo = function(flag){
        var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
        if(!flag){
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        }
        return html;
    }

    // 单独处理各个状态的图片显示问题
    var chagngeStatusPic = function (status) {
        //设置任务状态
        var src = './img/platform/recovery/data_transmission.gif';
        switch(status){
            case CONF.TASK_STATUS.RUNNING: //运行
            case CONF.TASK_STATUS.STOPPING://停止中
            case CONF.TASK_STATUS.PREPARING://准备中
            case CONF.TASK_STATUS.PAUSING://任务暂停中
            case CONF.TASK_STATUS.STARTING://启动中
            case CONF.TASK_STATUS.FINISHED://已完成
            case CONF.TASK_STATUS.SUCCESSED://任务成功
                src = './img/platform/recovery/data_transmission.gif';
                // $('#total-progress').css({width: '0%'});
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

    // 设置按钮事件
    var setBtnStatus = function (status, params) {
        // 等待、暂停、停止、错误、任务暂停中、已完成、任务成功、任务挂起 可以启动
        var startArr = [
            CONF.TASK_STATUS.WAITTING,
            CONF.TASK_STATUS.PAUSED,
            CONF.TASK_STATUS.STOPPED,
            CONF.TASK_STATUS.ERROR,
            CONF.TASK_STATUS.PAUSING,
            CONF.TASK_STATUS.FINISHED,
            CONF.TASK_STATUS.SUCCESSED,
            CONF.TASK_STATUS.CREATING,
        ];
        // 等待、运行、网络故障、任务已经完成但异常、错误、准备中、启动中、已完成、任务成功、任务创建中、任务挂起 可以停止
        var stopArr = [
            CONF.TASK_STATUS.WAITTING,
            CONF.TASK_STATUS.RUNNING,
            CONF.TASK_STATUS.NETWORK_FAULT,
            CONF.TASK_STATUS.ABNORMAL,
            CONF.TASK_STATUS.ERROR,
            CONF.TASK_STATUS.PREPARING,
            CONF.TASK_STATUS.STARTING,
            CONF.TASK_STATUS.FINISHED,
            CONF.TASK_STATUS.SUCCESSED,
            CONF.TASK_STATUS.CREATING,
            CONF.TASK_STATUS.PENDING,
        ];

        // 默认都不可操作
        setControlBtn('startJob', false);
        setControlBtn('stopJob', false);
        setControlBtn('motionJob', false);
        setControlBtn('pointJob', false);
        setControlBtn('delJob', false);

        if (startArr.includes(status)) {
            // 可以启动
            setControlBtn('startJob', true);
        }
        if (stopArr.includes(status)) {
            // 可以停止
            setControlBtn('stopJob', true);
        }

        if (status == CONF.TASK_STATUS.STOPPED) {
            // 任务是停止的，能删除
            setControlBtn('delJob', true);
        } else if (status == CONF.TASK_STATUS.RUNNING) {
            // 任务是运行的，能迁移和生成快照点
            setControlBtn('motionJob', true);
            setControlBtn('pointJob', true);
        }

        //任务详情按钮
        $('#startJob').unbind().on('click', function(){
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
                    operateJob(LANG.UI_JOB_START, 1)
                }
            );
        });
        $('#motionJob').unbind().on('click', function(){
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
                    if (params.hypervisor_type == 47) {
                        // 进行ajax请求
                        var data = {
                            is_scp_motion: 1,
                            task_uuid: taskUuid,
                            timepoint_uuid: taskUuid,
                            job_name: LANG.UI_PLATFORM_RECOVERY_MOTION_NAME
                        };
                        var url = '/api/v1/recovery/migrates/agentlessis'; // 无代理
                        Metronic.blockUI({target: '#instant_job_detail',animate: true,cenrerY: true,});
                        pAjaxRequest(data, url, "POST", function (result) {
                            Metronic.unblockUI('#instant_job_detail');
                            if (result.code == -1) {
                                return UIToastr.showError(LANG.UI_SEARCH_RECOVER_TASK, result.msg);
                            }
                            if (operateResponseList(result, LANG.UI_SEARCH_RECOVER_TASK)){
                                // 迁移任务创建完成
                                cleanTime();
                                LOCATION('./content/platform/recovery/motion_job.php?uuid=' + taskUuid, 'task');
                                return;
                            }
                        });
                    } else {
                        // 跳转到创建迁移任务界面
                        cleanTime();
                        var url = './content/platform/recovery/motion.php?uuid=' + taskUuid + '&module=' + params.module_type
                            + '&task_type=' + params.job_type + '&sub_module=' + params.hypervisor_type;
                        LOCATION(url, 'task');
                    }
                }
            );
        });
        $('#pointJob').unbind().on('click', function(){
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
                    operateJob(LANG.UI_PLATFORM_RECOVERY_CREATE_SNAPHOST, 1, '/api/v1/recovery/'+taskUuid+'/instantaneous/points')
                }
            );
        });

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
                        title: LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM,
                        inputType: 'password',
                        callback: debounce(function (r) {
                            if (r == null) return;
                            // 执行操作验证密码
                            Metronic.blockUI({target: '#instant_job_detail',animate: true,cenrerY: true});
                            var encrypt = new JSEncrypt();
                            encrypt.setPublicKey(CONF.PUBLIC_KEY);
                            var password = encrypt.encrypt(r);
                            var that = this;
                            pAjaxRequest({password: password}, '/api/v1/users/check/password', 'POST', function (result) {
                                Metronic.unblockUI('#instant_job_detail');
                                if (result.code == 0) {
                                    $(that).modal('hide');
                                    // 执行停止操作
                                    operateJob(LANG.UI_PLATFORM_RECOVERY_STOP_JOB, 1, '/api/v1/jobs/stop/'+taskUuid);
                                } else {
                                    UIToastr.showWarning(result.title, result.message);
                                }
                            });
                        }, 300, false)
                    })
                }
            );
        });

        $('#delJob').unbind().on('click', function(){
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
                    bootbox.confirm({
                        title: LANG.UI_JOB_DELETE_JOB,
                        message: LANG.UI_JOB_DELETE_JOB_TIPS,
                        callback: debounce(function (r) {
                            if (!r) return;
                            // 执行操作
                            operateJob(LANG.UI_JOB_DELETE_JOB, 2, '/api/v1/jobs/'+taskUuid, 'DELETE');
                        }, 300)
                    })
                }
            );
        });
    }

    // 清理定时
    var cleanTime = function (){
        clearTimeout(timerTask.InstantJobDetails_img);
        clearTimeout(timerTask.InstantJobDetails_logGrid);
    }

    // 操作任务
    var operateJob = function (operate, type, url = '', method = 'POST'){
        if (url == '') {
            url = '/api/v1/jobs/start/' + taskUuid;
        }
        Metronic.blockUI({target: '#instant_job_detail',animate: true,cenrerY: true});
        pAjaxRequest({start_type: type}, url, method, function (result) {
            Metronic.unblockUI('#instant_job_detail');
            if (result.code == 0 || result.code == 200) {
                if (type == 2) {
                    // 删除成功，要跳转到任务列表去
                    cleanTime();
                    setTimeout(function (){
                        LOCATION('./content/platform/jobs/jobs.php', 'task');
                    }, 2000)
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
                queryParams: {job_uuid: taskUuid},
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
                        str += '<tr><td style="width: 200px">' + LANG.UI_VM_AFTER_RECOVERY_SCRIPT + ': </td><td>' +after_script_des + '</td></tr>';

                        str += '<tr><td style="width: 200px">'+LANG.UI_PLATFORM_RECOVERY_SOURCE_OBJECT+': </td><td>' + row.detail.path + '</td></tr>';
                        str += `<tr>
								<td style="width: 200px">${LANG.UI_RECOVERY_GOAL}: </td>
								<td>${LANG.UI_VCENTER_VCENTER}: ${row.detail.d_vcenter_iP}</br> ${LANG.UI_VCENTER_HOST}: ${row.detail.d_host_op} </br>${LANG.UI_VCENTER_VM}: ${row.detail.d_name}</td>
							</tr>
							 <tr><td style="width: 200px">${LANG.UI_PLATFORM_RECOVERY_JOB_DISK_COMPARE}: </td><td>` + row.detail.disk + `</td></tr>`;
                    } else {
                        // 整机

                        var script_des = '';
                        var task_script = row.script_list.before_run_scripts;
                        if(task_script.length == 0){
                            script_des += LANG.UI_PUBLIC_NOTHING;
                        }else{
                            for(let i = 0; i < task_script.length; i++){
                                let info = 'value_agent_uuid="'+row.uuid+'"' +  ' value_flag="3" value_script_name="'+task_script[i]+'"' ;
                                script_des += '<a class="script_show" '+info+' style="margin-right: 10px;">' + task_script[i] + '</a>';
                            }
                        }
                        str += '<tr><td style="width: 200px">' + LANG.UI_PLATFORM_RECOVERY_SCRIPTS_BEFORE + ': </td><td>' +script_des + '</td></tr>';

                        var script_des = '';
                        var task_script = row.script_list.after_start_success_scripts;
                        if(task_script.length == 0){
                            script_des += LANG.UI_PUBLIC_NOTHING;
                        }else{
                            for(let i = 0; i < task_script.length; i++){
                                let info = 'value_agent_uuid="'+row.uuid+'"' +  ' value_flag="1" value_script_name="'+task_script[i]+'"' ;
                                script_des += '<a class="script_show" '+info+' style="margin-right: 10px;">' + task_script[i] + '</a>';
                            }
                        }
                        str += '<tr><td style="width: 200px">' + LANG.UI_PLATFORM_RECOVERY_SCRIPTS_AFTER + ': </td><td>' +script_des + '</td></tr>';

                        var script_des = '';
                        var task_script = row.script_list.before_stop_scripts;
                        if(task_script.length == 0){
                            script_des += LANG.UI_PUBLIC_NOTHING;
                        }else{
                            for(let i = 0; i < task_script.length; i++){
                                let info = 'value_agent_uuid="'+row.uuid+'"' +  ' value_flag="4" value_script_name="'+task_script[i]+'"' ;
                                script_des += '<a class="script_show" '+info+' style="margin-right: 10px;">' + task_script[i] + '</a>';
                            }
                        }
                        str += '<tr><td style="width: 200px">' + LANG.UI_PLATFORM_RECOVERY_SCRIPTS_STOP_BEFORE + ': </td><td>' +script_des + '</td></tr>';

                        var script_des = '';
                        var task_script = row.script_list.after_stop_scripts;
                        if(task_script.length == 0){
                            script_des += LANG.UI_PUBLIC_NOTHING;
                        }else{
                            for(let i = 0; i < task_script.length; i++){
                                let info = 'value_agent_uuid="'+row.uuid+'"' +  ' value_flag="2" value_script_name="'+task_script[i]+'"' ;
                                script_des += '<a class="script_show" '+info+' style="margin-right: 10px;">' + task_script[i] + '</a>';
                            }
                        }
                        str += '<tr><td style="width: 200px">' + LANG.UI_PLATFORM_RECOVERY_SCRIPTS_STOP_AFTER + ': </td><td>' +script_des + '</td></tr>';

                        str += `<tr>
								<td style="width: 200px">${LANG.UI_RECOVERY_GOAL}: </td>
								<td> ${LANG.UI_PUBLIC_HOST}: ${row.detail.d_name}</td>
							</tr>
							`;
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
                            if (module_type == CONF.MODULE_TYPE.VM) {
                                return row.detail.d_name;
                            } else {
                                return row.name;
                            }
                        },
                        // width: '300px',
                    },
                    /*{
                        field: 'size',
                        title: LANG.UI_PLATFORM_RECOVERY_TARGET_SIZE,
                        // width: '100px',
                    },
                    {
                        field: 'status',
                        title: LANG.UI_PUBLIC_STATUS,
                        // width: '200px',
                    },*/
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

    //初始化日志表格
    const initLogGrid = function(){
        $('#runninglog').runningLog({
            job_uuid: taskUuid
        });
    }

    return {
        init:function(){
            taskUuid =  $('#taskuuid').val();
            initBaseInfo();
            initLogGrid();
        }
    }

}();
jQuery(document).ready(function(){
    InstantJobDetails.init();
});
