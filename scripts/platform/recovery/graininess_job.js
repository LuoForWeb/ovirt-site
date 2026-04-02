var GraininessJob = function () {
    //当前文件路径, 目录是否完成 true未完成, 下次开始, 路径数组,时间点UUID,缓存文件目录,搜索
    var _pageSize = 40; //代理端文件列表每次显示条数;
    //模块类型,任务类型,子模块类型
    var hostTree, _module, _taskType, _subModule, _taskStatus,_treeNodeClick;
    var zTreeObj;
    var dirTree;
    var encrypt;
    var _path = '';		 //当前路径
    var _coverPath = '';		 //当前路径
    var netWorkselectValue = 2
    var mount_point_path = ''; // 选中的网络共享文件夹路径
    var isInit = false;// 是否初始化
    let newCount = 1;
    let filenameFlag = true;
    let share_path = ''//共享路径,用于文件恢复到nas
    let checkIsDown = false; // 标记是否正在下载
    let share_num = 0,transfer_num = 0;//标记是否存在传输或共享任务运行中
    let timer = null;
    let agentUuidTransfer = '';
    let agentOsTypeTransfer = '';
    let downFlags = true; // 是否下载文件成功，因为成功失败都会下载日志，但是失败会有错误提示，此时就不提示下载成功
    let node_uuid = '';
    let node_ip = '';
    let passWordFlag = true; // 网络共享的密码校验

    var _jobUuid = $("#task_uuid").val();
    var userUuid = '';

    //得到状态的显示类型
    var getLevelClass2 = function(level){
        var levelClass = '';
        switch(level){
            case 1:
            case 5:
            case 10:
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
            job_uuid: $('#task_uuid').val()
        });
    }

    var setJobStatus = function(data){
        var labelClass = getLevelClass2(data.status);
        var content = '<span class="label label-sm ' + labelClass + '">' + data.status_des + '</span>';
        $('#status').html(content);
    }
    var setSetting = function(data) {
        // 初始化策略信息
        if (!CONF.FUNCTIONS.includes('integrity')) {
            // 没有有完整性校验功能
            $('.completeConfig').hide();
        }
        if (!CONF.FUNCTIONS.includes('virusKill')) {
            // 没有病毒查杀功能
            $('.virusConfig').hide();
        }

        if (data.is_cdp) {
            // 源是实时的，没有完整性校验策略
            $('.completeConfig').hide();
        }

        $('#osType').html(data.os_type);
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

        // 过载保护
        $('#ignore_resource_limit').html(getFlagLevelInfo(data.ignore_resource_limiting_flag));
    }
    //得到任务详细信息
    var initTaskDetails = function(){
        var updateInterval = 5000;
        var getDetails = function(){
            var data = {};
            data.recovery_uuid = $("#task_uuid").val();
            pAjaxRequest(data, '/api/v1/recovery/graininess', 'get', function (res) {
                setTaskDetailsInfo(res);
            })
            timerTask.VMJobDetails_taskDetails = setTimeout(getDetails, updateInterval);
        }
        getDetails();
    }

    //设置任务详情
    var setTaskDetailsInfo = function(d){
        var data = d.data;
        $('#vmname').html(data.vm_name);
        $('#vmname').attr('title', data.vm_name);
        $('#timepoint').html(data.timepoint);
        $('#taskName').html(data.job_name);
        $('#taskStage').html(data.current_stage_value);
        node_ip = 'https://' + data.node_ip
        node_uuid = data.node_uuid
        setJobStatus(data);
        _module = data.module;
        _taskType = data.taskType;
        _subModule = data.subModule;
        _taskStatus = data.status;
        share_num = data.share_num;
        transfer_num = data.transfer_num;
        userUuid = data.user_uuid;

        if (!isInit) {
            var params = {
                'protocol':[{'name':'SMB','value': 2}],
                'showDiy': false,
                'node_uuid': data.node_uuid,
                'is_grain': true,
                'class': 'col-md-8'
            }
            storageMount.init(params);
            isInit = true;
        }

        if (_module == CONF.MODULE_TYPE.VM) {
            $('.modul_name_show').html(LANG.UI_JOB_OLD_NAME);
        } else {
            $('.modul_name_show').html(LANG.UI_FILE_SOURCE_CLIENT_IP);
        }

        if (CONF.VMTYPE_GROUP.PUBLICCLOUD.includes(_subModule)) {
            //AWS的显示
            $('#detailsbody .static-info').eq(1).find('.name').text(LANG.UI_GRAIN_AWS_INSTANCE + ':');
        }

        setSetting(data)
        if(2 == data.status){
            // 任务运行中 需要加载出细粒度文件树
            if(!zTreeObj){
                //如果是第一次加载,并且右边初始化的时候没有数据
                $('#recoverFileTree').empty();
                // 加载树
                initFileList();
            }
        } else {
            // 任务其它状态 关闭树
            zTreeObj = '';
            $('#recoverFileTree').html(LANG.UI_RECOVERY_NO_DATA);
        }
        setBtnStatus();
    }

    //根据任务状态设置按钮权限
    var setBtnStatus = function(){
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
        setControlBtn('delJob', false);

        if (startArr.includes(_taskStatus)) {
            // 可以启动
            setControlBtn('startJob', true);
        }
        if (stopArr.includes(_taskStatus)) {
            // 可以停止
            setControlBtn('stopJob', true);
        }

        // 任务是停止的，能删除
        if (_taskStatus == CONF.TASK_STATUS.STOPPED) {
            setControlBtn('delJob', true);
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
                    var msg;
                    // 需要判断此时是否正在下载文件
                    if (checkIsDown || share_num > 0 || transfer_num > 0) {
                        // 网页下载时停止任务会导致解挂失败
                        msg = LANG.UI_PLATFORM_RECOVERY_STOP_JOB_TIPS2;
                    } else {
                        msg = LANG.UI_SETTINGS_STORAGE_SAFE_CONFIRM;
                    }
                    bootbox.prompt({
                        title: msg,
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
                                    operateJob(LANG.UI_PLATFORM_RECOVERY_STOP_JOB, 1, '/api/v1/jobs/stop/'+_jobUuid);
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
                        callback: function (r) {
                            if (!r) return;
                            // 执行操作
                            operateJob(LANG.UI_JOB_DELETE_JOB, 2, '/api/v1/jobs/'+_jobUuid, 'DELETE');
                        }
                    })
                }
            );
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

    // 操作任务
    var operateJob = function (operate, type, url = '', method = 'POST'){
        if (url == '') {
            url = '/api/v1/jobs/start/' + _jobUuid;
        }
        Metronic.blockUI({target: '#jobDetail',animate: true,cenrerY: true});
        pAjaxRequest({start_type: type}, url, method, function (result) {
            Metronic.unblockUI('#jobDetail');
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

    // 清理定时
    var cleanTime = function (){
        clearTimeout(timerTask.VMJobDetails_taskDetails);
        clearTimeout(timerTask.VMJobDetails_logGrid);
    }

    function validateUsername(username) {
        const regex = /^[a-zA-Z][a-zA-Z0-9._-]{3,254}$/;

        if (!username || username.trim() === '') {
            return makeIcon(false, LANG.UI_PUBLIC_EMPTY_TIPS, '#grainness_user_name');
        }

        if (!regex.test(username)) {
            if (username.length < 4) {
                return makeIcon(false, LANG.UI_GRAIN_JOB_SHARE_MIN_NUM, '#grainness_user_name');
            }
            if (username.length > 255) {
                return makeIcon(false, LANG.UI_GRAIN_JOB_SHARE_MAX_NUM, '#grainness_user_name');
            }

            if (!/^[a-zA-Z]/.test(username)) {
                return makeIcon(false, LANG.UI_GRAIN_JOB_SHARE_MUST_STRING, '#grainness_user_name');
            }

            if (/[^a-zA-Z0-9._-]/.test(username)) {
                return makeIcon(false, LANG.UI_GRAIN_JOB_SHARE_MUST_STRING2, '#grainness_user_name');
            }
            return makeIcon(false, LANG.UI_GRAIN_JOB_SHARE_MUST_STRING2, '#grainness_user_name');
        }
        return makeIcon(true, '', '#grainness_user_name');
    }

    //得到开启和关闭的HTML内容
    var getFlagLevelInfo = function(flag){
        var html = '<span class="label label-success">' + LANG.UI_PUBLIC_ON + '</span>';
        if(!flag){
            html = '<span class="label label-warning">' + LANG.UI_PUBLIC_OFF + '</span>';
        }
        return html;
    }

    function makeIcon(checkIsOk = true,  msg = LANG.UI_PUBLIC_EMPTY_TIPS, id = '#grainness_password') {
        passWordFlag = checkIsOk;
        //$('#storagemount_component_body .inputipdiv').find('i').remove();
        $(id).parent().find('.fa-check').remove();
        $(id).parent().find('.fa-warning').remove();
        $(id).css('border', '1px solid #E6E6E6');
        if (checkIsOk) {
            $(id).before('<i class="fa fa-check" style="color:#45B6AF;"></i>').css('border-color', 'rgb(213, 233, 197)');
        } else {
            $(id).before('<i class="fa fa-warning" title="' + msg +'" style="color: red;"></i>').css('border', '1px solid #F1416C');
        }
    }

    //事件监听
    var initListener = function() {
        passWordFlag = true;
        $('#coverType').on('change', sameFileChange);
        // 监听帮助信息关闭事件
        $('#grain_marktips1').find('button').on('click', function (){
            $('.col-detail-grain').css('height', '100%');
        })

        // 网络共享点击事件
        $('.share_network').on('click', function () {
            var choose = getSelectNodes('zTreeObj');
            //得到所有勾选状态是全选中的节点
            var allCheckedNode = [];
            for(var i=0; i<choose.length; i++){
                //check_Child_State == -1不存在子节点（文件或者全选的无子节点的文件夹）  2是所有子节点被勾选
                if(choose[i].check_Child_State == 2 || choose[i].check_Child_State == -1) {
                    allCheckedNode.push(choose[i]);
                }
            }
            // 第一步：收集所有存在的 id
            const allIds = new Set(allCheckedNode.map(item => item.id));

            // 第二步：筛选出顶级节点（即 pid 不存在于 allIds 中）
            const topNodes = allCheckedNode.filter(item => !allIds.has(item.pid));

            if (topNodes.length !== 1 || parseInt(topNodes[0].type) !== 2) {
                UIToastr.showWarning(LANG.UI_RECOVERY_SHARE, LANG.UI_RECOVERY_PLEASE_CHOOSE_FILM);
                return false;
            }
            choose = topNodes;
            // 因为是联动的，所以需要判断下，是否是只选择了一个文件夹
            var ik = 0;
            for (var i in choose) {
                if (parseInt(choose[i].type) == 2) {
                    ik++;
                    mount_point_path = choose[i].path;
                }
            }
            if (ik != 1) {
                mount_point_path = '';
                UIToastr.showWarning(LANG.UI_RECOVERY_SHARE, LANG.UI_RECOVERY_PLEASE_CHOOSE_FILM);
                return false;
            }
            $('#grainness_password').parent().find('.fa-check').remove();
            $('#grainness_password').parent().find('.fa-warning').remove();
            $('#grainness_password').css('border', '1px solid #E6E6E6');
            $('#grainness_password').val('');
            $('#grainness_user_name').parent().find('.fa-check').remove();
            $('#grainness_user_name').parent().find('.fa-warning').remove();
            $('#grainness_user_name').css('border', '1px solid #E6E6E6');
            $('#grainness_user_name').val('');
            $('#drawer-network').drawer('show');
        });

        $('#grainness_user_name').on('input', function (){
            var value = $('#grainness_user_name').val();
            validateUsername(value);
        })

        $('#grainness_password').on('input', function (){
            var value = $('#grainness_password').val();
            if (value != '') {
                var match = "";
                switch (CONF.PASS_COMPLEXITY) {
                    case 1: //弱(包含字母(不区分大小写),数字,特殊字符(不是必须))
                        var pattern = "^[A-Za-z0-9!@#$%^&*,.]{" + CONF.PASS_LENGTH + ",}$";
                        match = new RegExp(pattern).test(value);
                        break;
                    case 2: //中(必须包含字母(不区分大小写),数字,特称字符)
                        var pattern = "^(?=.*[0-9])(?=.*[A-Za-z])(?=.*[!@#$%^&*,\.])[0-9a-zA-Z!@#$%^&*,\\.]{" + CONF.PASS_LENGTH + ",}$";
                        match = new RegExp(pattern).test(value);
                        break;
                    case 3: //强(必须包含大小写字母,数字,特称字符)
                        var pattern = "^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#$%^&*,\\.])[0-9a-zA-Z!@#$%^&*,\\\\.]{" + CONF.PASS_LENGTH + ",}$"
                        match = new RegExp(pattern).test(value);
                        break;
                }
                if (!match) {
                    var passTips = '';
                    switch (CONF.PASS_COMPLEXITY) {
                        case 1: //弱(包含字母(不区分大小写),数字,特殊字符)
                            passTips = LANG.UI_USER_PASSWORD_STRENGTH_WEAK_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                            break;
                        case 2: //中(必须包含字母(不区分大小写),数字,特称字符)
                            passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_MEDIUM;
                            break;
                        case 3: //强(必须包含大小写字母,数字,特称字符)
                            passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_STRONG;
                            break;
                        default:
                            passTips = LANG.UI_USER_PASSWORD_STRENGTH_COMMIT + CONF.PASS_LENGTH + LANG.UI_USER_PASSWORD_STRENGTH_WEAK;
                            break;
                    }
                    return makeIcon(match,  passTips);
                }
                return makeIcon();
            }
            return makeIcon(false);
        })

        // 网络共享确定事件
        $('#graininess_submit').on('click', graininessSubmit)
        //下载细粒度文件
        $('#fileDownload').on('click', function (){

            bootbox.confirm({
                title: LANG.UI_WEB_PAGE_DOWNLOAD,
                message: LANG.UI_WEB_PAGE_DOWNLOAD_TIPS,
                callback: function (r) {
                    if (!r) return;
                    // 执行操作
                    grainFileDownload();
                }
            })
        })

        //客户端文件传输确定按钮
        $('#agent_submit').on('click', agentSubmit)
        $('#searchCoverTree').on('keydown', function(event) { //搜索回车
            if (event.keyCode === 13) {
                searchTree()
            }
        });
        // 客户端传输
        $('.client_transfer').on('click', function () {
            var choose = getSelectNodes('zTreeObj');
            if (choose.length <= 0) {
                UIToastr.showWarning(LANG.UI_RECOVERY_CLIENT_TRANSLATE, LANG.UI_RECOVERY_PLEASE_CHOOSE_MORE_FILM);
                return false;
            } else {
                agentUuidTransfer = '';
                agentOsTypeTransfer = '';
                $('#savePath').html('');
                initFileClientTree();
                // 恢复目标客户端搜索
                $('#searchHost').on('keydown', debounces);
                // 初始化网络传输任务名
                var info = {
                    job_name: LANG.UI_RECOVERY_CLIENT_TRANSLATE_JOB_NAME,
                    job_uuid: _jobUuid
                };
                pAjaxRequest(info, "/api/v1/recovery/graininess/job_name", "GET", function (d) {
                    $('#tranlateName').val(d.data.value);
                }, false);
                $('#drawer-client').drawer('show');
            }
        });

        // 网络共享
        /*$('.amount_service').unbind('change').on('change', function () {
            netWorkselectValue = $('.amount_service').find('option:selected').val();
            if (netWorkselectValue == 1) {
                $('.grainness_user_name').hide()
                $('.grainness_password').hide()
                $('#grainness_user_name').val('');
                $('#grainness_password').val('');
            } else {
                $('.grainness_user_name').show()
                $('.grainness_password').show()
            }
        });*/
        //共享任务跳转
        $('.share_task').on('click',function() {
            let uuid = $("#task_uuid").val()
            var url = './content/platform/recovery/share-task.php?id=' + uuid
            LOCATION(url,'task');
        })
    }

    var sameFileChange = function() {
        var type = parseInt($('#coverType').val());
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
    var checkModeChange = function() {
        var check_mode = $('#check_mode').val();
        if (check_mode == 2) {
            $('.verification-algorithm-form').show();
        } else {
            $('.verification-algorithm-form').hide();
        }
    }

    const debounces = function () {
        clearTimeout(timer)
        timer = setTimeout(function () {
            let params = $('#searchHost').val();
            initFileClientTree(params);
        }, 500)
    }

    const initFileClientTree = (keyword = '') => {
        let params = {
            keyword: keyword
        }

        Metronic.blockUI({target: '#host_tree',animate: true});
        pAjaxRequest(params, '/api/v1/files/recover_host_tree', 'GET', (result) => {
            if (result.success) {
                if (result.data.length === 0) {
                    $('#noagenttips').show();
                    $('#host_tree').hide();
                    $('#noagenttips p').html(LANG.UI_FILE_RECOVERY_NO_AGENT);
                } else {
                    $('#noagenttips').hide();
                    $('#host_tree').show();

                    // 初始化文件客户端树
                    setHostTree(result.data);
                }
            } else {
                $('#noagenttips').show();
                $('#host_tree').hide();
                $('#noagenttips p').html(LANG.UI_FILE_RECOVERY_NO_AGENT);

                UIToastr.showWarning(LANG.UI_HADOOP_GET_CLIENT_FAILED, result.message);
            }

            Metronic.unblockUI('#host_tree');
        })
    }
    const setHostTree = (zNodes) => {
        let setting = {
            check: {
                enable: true,
            },
            data: {
                simpleData: {
                    enable: true
                }
            },
            callback: {
                beforeClick: hostNodeSelect,
                onCheck: hostOnCheck,
            }
        };

        hostTree = $.fn.zTree.init($("#host_tree"), setting, zNodes);
    }
    /**
     * 客户端树节点select
     * @param {*} treeId
     * @param {*} treeNode
     * @param {*} clickFlag
     * @returns
     */
    const hostNodeSelect = function(treeId, treeNode, clickFlag){
        if(treeNode.type === 1) {
            hostTree.expandNode(treeNode);
            return;
        }

        if(treeNode.chkDisabled) return;

        hostTree.checkNode(treeNode, !treeNode.checked, false, true);
    }
    /**
     * 客户端树节点onCheck
     * @param {*} e
     * @param {*} treeId
     * @param {*} treeNode
     * @returns
     */
    const hostOnCheck = function(e, treeId, treeNode){
        agentUuidTransfer = '';
        if(treeNode.chkDisabled) return;
        if(treeNode.checked){    // 注意，这里的树节点的checked状态表示勾选之后的状态
            hostTree.checkAllNodes(false); // 取消所有节点的选中状态
            hostTree.checkNode(treeNode,true,false,false); // 重新选中被勾选的节点
            $('#selectPath').show();
            $('#pathtreediv').show();
            // 获取选择客户端的高度后动态计算选择路径的fom-group的高度，以适应分辨率变化
            initSavePath(treeNode);
        } else { // 未勾选不显示 选择路径
            $('#selectPath').hide();
            $('#pathtreediv').hide();
        }
    }


    //获取保存路径
    var initSavePath = function(treeNode) {
        $('#savePath').html('');
        agentUuidTransfer = treeNode.uuid;
        agentOsTypeTransfer = treeNode.os_type
        let params = {
            agent_uuid: treeNode.uuid,
            offset: 0,
            limit: _pageSize,
            filename: '',
            dir: '',
            pid: 0,
            startAfter: ''
        }
        var div = "#pathtreediv";
        Metronic.blockUI({target: div,animate: true});
        let url = "/api/v1/files/recover_path_tree";
        pAjaxRequest(params, url,"GET", function (res) {
            if(res.success) {
                Metronic.unblockUI(div);
                setFileTreeAgent(res.data)
            } else {
                Metronic.unblockUI(div);
                $('#savePath').html(LANG.UI_RECOVERY_NO_DATA);
                UIToastr.showWarning(LANG.UI_RECOVERY_FILM_GRAININESS, res.message);
            }
        });
    }
    var setFileTreeAgent = function(data){
        newCount = 1;
        let setting = {
            view: {
                addHoverDom: addHoverDoms,
                removeHoverDom: removeHoverDoms,
                selectedMulti: false
            },
            edit: {
                enable: true,
                drag:false,//禁用拖拽
                editNameSelectAll: true,
                showRenameBtn:showRenameBtn,
                showRemoveBtn:showRemoveBtn,
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
        dirTree = $.fn.zTree.init($('#savePath'), setting, data);
        $('#savePath li').css("background-color","white");

    }

    const addHoverDoms = function(treeId, treeNode) {
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
                        code_type: treeNode.code_type
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
                        code_type: treeNode.code_type
                    },
                    false);
                let newnode = $.fn.zTree.getZTreeObj(treeId).getNodeByParam("isnew", true, null);
                beforeEditName(treeId,newnode);
                newnode.isnew = false;
            }
            return false;
        });
    };

    const removeHoverDoms = function(treeId, treeNode) {
        $("#addBtn_"+treeNode.tId).unbind().remove();
    };

    /**
     * 恢复路径树节点 - 是否显示编辑按钮
     * @param {*} treeId
     * @param {*} treeNode
     * @returns
     */
    const showRenameBtn = function(treeId, treeNode){
        // 获取节点所配置的noEditBtn属性值
        return !(treeNode.noEditBtn !== undefined && treeNode.noEditBtn);
    }
    /**
     * 恢复路径树节点 - 是否显示删除按钮
     * @param {*} treeId
     * @param {*} treeNode
     * @returns
     */
    const showRemoveBtn = function(treeId, treeNode){
        // 获取节点所配置的noRemoveBtn属性值
        return !(treeNode.noRemoveBtn !== undefined && treeNode.noRemoveBtn);
    }
    /**
     * 恢复路径树节点 - select
     * @param {*} treeId
     * @param {*} treeNode
     * @param {*} clickFlag
     * @returns
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
    /**
     * 恢复路径树节点 - 移除
     * @param {*} event
     * @param {*} treeId
     * @param {*} treeNode
     */
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
     * 恢复路径树节点 - 重命名
     * @param {*} event
     * @param {*} treeId
     * @param {*} treeNode
     * @param {*} isCancel
     * @returns
     */
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
        let os_type = agentOsTypeTransfer;
        let specialchar = ['/', ':','"','<','>','|','\\','?','*','&'];
        let des = LANG.UI_FILE_FILENAME_NO_CONTAIN + ' &';
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


    function setFileTree(data) {
        var setting = {
            check: {
                enable: true, // 开启复选框
                chkboxType: {"Y": "s", "N": ""}, // 设置复选框的样式
                // chkStyle: "checkbox", // 设置为checkbox样式
                // radioType: "all" // 这里虽然设置了radioType，但因为开启了chkbox，所以不影响复选框功能
            },
            data: {
                simpleData: {
                    enable: true,
                    pIdKey: "pid",//节点数据中保存其父节点唯一标识的属性名称
                },
                key: {
                    title: "title",
                }
            },
            callback: {
                beforeCheck: beforeCheck,
                beforeClick: NodeClick, //用于捕获单击节点之前的事件回调函数，并且根据返回值确定是否允许单击操作
                beforeExpand: NodeExpand, //父亲节点展开之前的回调函数
            },
            view: {
                addHoverDom: addHoverDom,
                removeHoverDom: removeHoverDom,
            }
        };
        zTreeObj = $.fn.zTree.init($("#recoverFileTree"), setting, data);
    }
    var addHoverDom = function(treeId, treeNode) {
        if(!treeNode.isParent) return false;
        if ($("#diyBtn_"+treeNode.tId).length>0 || ($('.custom-search-grain-tree').length > 0 && treeNode == _treeNodeClick)) return;
        var aObj = $("#" + treeNode.tId + "_a");
        var editStr =  "<i class='viconfont vicon-a-Component1' id='diyBtn_" + treeNode.tId + "'></i>";
        aObj.append(editStr);
        var btn = $("#diyBtn_"+treeNode.tId);
        if (btn) {
            btn.bind("click", function(event){
                if (_treeNodeClick) {
                    closeDom(_treeNodeClick);
                }
                _treeNodeClick = treeNode;
                aObj.find("#diyBtn_"+treeNode.tId + '_input').remove();
                aObj.find("#subBtn_"+treeNode.tId).remove();
                var editStr = '<input type="text" placeholder="'+LANG.UI_GLOBAL_STRATEGY_SPEED_LIMIT_SEARCH+'" style="border: 1px solid #ccc; margin-left: 15px;margin-top:-5px" className="form-control" id="diyBtn_' + treeNode.tId +'_input" autoComplete="off" />' +
                    '<div id="subBtn_' + treeNode.tId + '" class="custom-search-grain-tree" style="float: right;margin-left:8px;">' +
                    '<i class="viconfont vicon-ge_authorization2 confirm" title="'+ LANG.UI_PUBLIC_CONFIRM+'" style="font-size: 18px;line-height: 25px;margin-right: 4px;"></i>' +
                    '<i class="viconfont vicon-guanbi cancel" title="'+ LANG.UI_PUBLIC_CANCEL+'" style="font-size: 18px;line-height: 25px;"></i>' +
                    '</div>'
                ;
                aObj.append(editStr);
                var btnInput = $("#diyBtn_"+treeNode.tId + "_input");
                btnInput.bind("click", function(event) {
                    event.stopPropagation();
                })
                btnInput.focus(function(event) {
                    btnInput.css({
                        "border": "1px solid #ccc", // 1像素宽的灰色边框
                        // 你可以继续添加其他CSS属性，比如"outline: none;"来移除可能的轮廓线
                        "outline": "none" // 移除轮廓线（如果有的话）
                    });
                    event.stopPropagation();
                })
                btnInput.on('keydown', function(event) {
                    //搜索回车
                    if (event.keyCode === 13) {
                        searchTree();
                    }
                })
                aObj.find('.confirm').click(function (){
                    searchTree();
                })
                function searchTree() {
                    if(!btnInput.val()) {
                        UIToastr.showWarning(LANG.UI_RECOVERY_SEARCH_GRAINENESS_FILM, LANG.UI_RECOVERY_SEARCH_FILM_NO_EMPTY);
                        return false;
                    }
                    var job_uuid = $('#task_uuid').val();
                    var data = {};
                    data.dir_path = treeNode.path;
                    data.file_name = btnInput.val();
                    var url = "/api/v1/recovery/search/"+job_uuid+"/graininess/tree";
                    Metronic.blockUI({target: '#filediv',animate: true});
                    pAjaxRequest(data, url, "GET", function (res) {
                        Metronic.unblockUI('#filediv');
                        if (res.success) {
                            let result = res.data.file_list
                            for (var k in result) {
                                result[k].isParent = result[k].is_parent;
                                result[k].iconOpen = result[k].icon_open;
                                result[k].iconClose = result[k].icon_close;
                            }
                            $.fn.zTree.getZTreeObj(treeId).removeChildNodes(treeNode);
                            $.fn.zTree.getZTreeObj(treeId).addNodes(treeNode, result, true);
                            $.fn.zTree.getZTreeObj(treeId).expandNode(treeNode, true); //展开该节点的所有节点

                        } else {
                            UIToastr.showWarning(LANG.UI_RECOVERY_FILM_GRAININESS, LANG.UI_RECOVERY_NO_SEARCH);
                            return false;
                        }

                    });
                }
                var cancleSelect = $("#subBtn_"+treeNode.tId + ' .cancel');
                cancleSelect.bind("click", function(event) {
                    getSonTree(treeId,treeNode,false);
                    closeDom(treeNode);
                    event.stopPropagation();
                });
                event.stopPropagation();
            });
        }
    }

    function removeHoverDom(treeId, treeNode) {
        $("#diyBtn_" +treeNode.tId).unbind().remove();
    }

    function closeDom(treeNode) {
        $("#diyBtn_" +treeNode.tId).unbind().remove();
        $("#diyBtn_"+treeNode.tId + "_input").unbind().remove();
        $("#subBtn_" +treeNode.tId).unbind().remove();
    }

    //细粒度恢复的树
    var NodeClick = function(treeId, pNode,clickshow){
        //是否被禁用
        if(pNode.chkDisabled) {
            return;
        }

        //1文件 2 文件夹 3 磁盘
        if(pNode.type == 1){
            return;
        }else {
            _coverPath = pNode.filepath;
            NodeExpand(treeId, pNode);
            $.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true);
        }
    }
    // 被选中之前的事件
    function beforeCheck(treeId, treeNode) {
        var treeObj = $.fn.zTree.getZTreeObj(treeId);
        if (!treeNode.checked) {
            checkChildren(treeNode, treeObj);
        } else {
            uncheckChildren(treeNode, treeObj);
        }

        return true;
    }

    function uncheckChildren(node, treeObj) {
        var children = node.children;
        if (children && children.length > 0) {
            for (var i = 0; i < children.length; i++) {
                var child = children[i];
                treeObj.checkNode(child, false, true);
                uncheckChildren(child, treeObj);
            }
        }
    }

    function checkChildren(node, treeObj) {
        var children = node.children;
        if (children && children.length > 0) {
            for (var i = 0; i < children.length; i++) {
                var child = children[i];
                treeObj.checkNode(child, true, true);
                checkChildren(child, treeObj);
            }
        }
    }

    var NodeExpand = function (treeId,pNode) {
        if(pNode.children) return true;//如果该节点有子节点代表已经请求过文件子树了
        getSonTree(treeId,pNode,false);
    }
    var getSonTree = function (treeId,pNode,expendFlag) {
        var job_uuid = $('#task_uuid').val();
        var data = {dir_path: pNode.path};
        var url = "/api/v1/recovery/"+job_uuid+"/graininess/tree";
        Metronic.blockUI({target: '#recoverFileTree',animate: true});
        pAjaxRequest(data, url,"GET", function (res) {
            Metronic.unblockUI('#recoverFileTree');
            if(res.success){
                var result = res.data.file_nodes;
                for (var k in result) {
                    result[k].isParent = result[k].is_parent;
                    result[k].iconOpen = result[k].icon_open;
                    result[k].iconClose = result[k].icon_close;
                }
                $.fn.zTree.getZTreeObj(treeId).removeChildNodes(pNode);
                $.fn.zTree.getZTreeObj(treeId).addNodes(pNode, result, true);
                $.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true); //展开该节点的所有节点
                if(expendFlag == true){
                    $.fn.zTree.getZTreeObj(treeId).expandNode(pNode, true, true, true);
                }
                if(pNode.checked && pNode.children) { //如果该节点被选择了且该节点有子节点，子节点也就全部选中
                    pNode.children.forEach(item=>{
                        $.fn.zTree.getZTreeObj(treeId).checkNode(item,true);
                    });
                }
            }
        });
    }

    var searchTree = function() {

    }
    // 初始化外面细粒度文件树
    var initFileList = function(){
        var job_uuid = $('#task_uuid').val();
        var data = {dir_path: ''};
        var url = "/api/v1/recovery/"+job_uuid+"/graininess/tree";
        Metronic.blockUI({target: '#recoverFileTree',animate: true});
        pAjaxRequest(data, url, "GET", function (result) {
            Metronic.unblockUI('#recoverFileTree');
            if (result.success) {
                let file_nodes = result.data.file_nodes
                for (var k in file_nodes) {
                    file_nodes[k].isParent = file_nodes[k].is_parent;
                    file_nodes[k].iconOpen = file_nodes[k].icon_open;
                    file_nodes[k].iconClose = file_nodes[k].icon_close;
                }
                setFileTree(file_nodes)
            } else {
                UIToastr.showWarning(LANG.UI_RECOVERY_FILM_GRAININESS, result.message);
                return false;
            }

        });
    }
    //客户端文件传输确定按钮
    var agentSubmit = function() {
        let job_uuid = $('#task_uuid').val();
        let data = {};
        data.transport_task_name = $('#tranlateName').val();
        if (data.transport_task_name == '') {
            UIToastr.showWarning(LANG.UI_RECOVERY_CLIENT_TRANSLATE_SETTING, LANG.UI_BACKUP_EXPORT_ENTER_TASKNAME);
            return false;
        }
        data.agent_uuid = agentUuidTransfer;
        if (data.agent_uuid == undefined || data.agent_uuid == '') {
            UIToastr.showWarning(LANG.UI_RECOVERY_CLIENT_TRANSLATE_SETTING, LANG.UI_PLATFORM_JOB_CREATE_CLIENT);
            return false;
        }
        let pathNodes = dirTree.getCheckedNodes(true);
        if (pathNodes.length === 0) {
            UIToastr.showWarning(LANG.UI_RECOVERY_CLIENT_TRANSLATE_SETTING, LANG.UI_RECOVERY_PLEASE_CHOOSE_ONE_SAVE_PATH);
            return false;
        }
        var dir_path = pathNodes[0].dir_path;

        data.file_save_path = dir_path;
        var file_save_mode = $('#coverType').find('option:selected').val(); // 覆盖模式
        var sync_rule = $('#sync_rule').find('option:selected').val(); // 覆盖规则
        if (file_save_mode == 1 && sync_rule == 2) {
            // 不一致保留目标端最新
            file_save_mode = 2;
        }

        data.file_save_mode = file_save_mode;
        data.reserve_file_permission_flag = $('#reserve_file_permission_flag').get(0).checked; // 保持文件权限
        data.skip_dir_tree_flag = $('#job_skip_dir_tree_flag').get(0).checked; // 去除前缀

        var choose = filterFile(); // 过滤处理下未选中的和未全选的
        let object_list = [];
        for(let i=0; i<choose.length; i++) {
            let obj = {}
            obj.file_path = choose[i].path
            obj.file_type = choose[i].type
            obj.file_name = choose[i].name
            obj.file_size = choose[i].size
            obj.modify_time = choose[i].modify_time
            object_list.push(obj)
        }
        data.file_list = object_list
        let url = "/api/v1/recovery/"+job_uuid+"/graininess/clients";
        Metronic.blockUI({target: '#drawer-client',animate: true});
        pAjaxRequest(data, url, "POST", function (res) {
            Metronic.unblockUI('#drawer-client');
            if(res.success) {
                $('#drawer-client').drawer('hide');
                return UIToastr.showSuccess(LANG.UI_RECOVERY_CLIENT_TRANSLATE_SETTING, LANG.UI_RECOVERY_CLIENT_TRANSLATE_SETTING_SUCCESS);
            } else {
                return UIToastr.showWarning(LANG.UI_RECOVERY_CLIENT_TRANSLATE_SETTING, res.message);
            }

        })
    }

    //过滤文件
    var filterFile = function () {
        var allfileNodes = getSelectNodes('zTreeObj');
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
                    var str = allCheckedNode[n].pid==null ?'':allCheckedNode[n].pid;
                    if(str.includes(allCheckedNode[m].filepath) && allCheckedNode.indexOf(allCheckedNode[n])!=-1) {//判断全选的文件夹下是否还有文件  有则从数组中删除
                        allCheckedNode.splice(allCheckedNode.indexOf(allCheckedNode[n]),1);
                        n--;
                    }
                }
            }
        }
        return allCheckedNode;
    }

    var graininessSubmit = function() {
        // 组合信息，发送请求，最后跳转进入共享列表
        // 获取挂载配置信息 示例
        if(!$('#grainness_user_name').val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_SHARE_SETTING, LANG.UI_OBS_USERNAME_CANNOT_NULL);
            return false;
        }
        if(!$('#grainness_password').val()) {
            UIToastr.showWarning(LANG.UI_RECOVERY_SHARE_SETTING, LANG.UI_OBS_PASSWORD_CANNOT_NULL);
            return false;
        }
        if (!passWordFlag) {
            // 密码校验不过，直接阻止
            return false;
        }
        let inter = storageMount.getAmountInfo();
        if (inter == false) {
            return false;
        }
        let info = {};
        let job_uuid = $('#task_uuid').val();
        info.server_ip = inter.ip;
        info.protocol = inter.protocol;
        info.allow_ip_list = inter.allow_access_ips;
        info.username = $('#grainness_user_name').val();
        var pwd = $('#grainness_password').val();
        info.password = pwd || '';
        info.mount_point_path = mount_point_path
        let url = "/api/v1/recovery/"+job_uuid+"/graininess/network";
        Metronic.blockUI({target: '#drawer-network',animate: true});
        pAjaxRequest(info, url, "POST", function (res) {
            Metronic.unblockUI('#drawer-network');
            if(res.success) {
                $('#drawer-network').drawer('hide');
                $('#shareSuccess').modal({'width':'690px', 'height': '290px'});
                $('#account_name').text(res.message.username);
                $('#account_pwd').text(pwd);
                var share_name = res.message.share_name + '<i data-content="'+res.message.share_name + '"  class="viconfont vicon-a-Copyfuzhi" title="'+LANG.UI_CM_CDP_REPLICATION+'"></i>';
                $('#share_name').html(share_name);
                var access_path = res.message.access_path + '<i data-content="'+res.message.access_path + '" class="viconfont vicon-a-Copyfuzhi" title="'+LANG.UI_CM_CDP_REPLICATION+'"></i>';
                $('#account_path').html(access_path);
            } else {
                return UIToastr.showWarning(LANG.UI_RECOVERY_SHARE_SETTING, res.message);
            }

        })
    }

    $(document).off('click', '#shareSuccess .modal-body i').on('click', '#shareSuccess .modal-body i', async function () {
        try {
            const $element = $(this); // 获取当前点击的 <div>
            const htmlContent = $element.attr('data-content');
            // 如果需要解码转义字符
            // const decodedContent = $('<div>').html(htmlContent).text();
            // await navigator.clipboard.writeText(decodedContent);
            await navigator.clipboard.writeText(htmlContent);
            $('.clipboard-popup').show();
            setTimeout(function (){
                $('.clipboard-popup').hide();
            }, 3000)
            //UIToastr.showSuccess(LANG.UI_PUBLIC_NOTICE, LANG.UI_PLATFORM_COPY_SUCCESS_TIPS);
        } catch (err) {
            // console.error('复制失败:', err);
            // alert('复制失败，请重试！');
            UIToastr.showWarning(LANG.UI_PUBLIC_NOTICE, LANG.UI_PLATFORM_COPY_FAIL_TIPS);
        }
    });

    // 获取选中的节点
    var getSelectNodes = function (data){
        if(data == 'zTreeObj') {
            var selectedNodes = zTreeObj? zTreeObj.getCheckedNodes():[];
        } else {
            var selectedNodes = zTreeObj? dirTree.getCheckedNodes():[];
        }
        return selectedNodes;
    };

    //下载细粒度文件
    var grainFileDownload = function(){
        //var choose = getSelectNodes('zTreeObj');
        var choose = filterFile(); // 过滤处理下未选中的和未全选的
        if (choose.length <= 0) {
            UIToastr.showWarning(LANG.UI_WEB_PAGE_DOWNLOAD, LANG.UI_RECOVERY_PLEASE_CHOOSE_MORE_FILM);
            return false;
        }
        var question = {}
        var object_list = [];
        for(let i=0; i<choose.length; i++) {
            let obj = {}
            obj.file_path = choose[i].path
            obj.file_type = choose[i].type
            obj.file_name = choose[i].name
            obj.file_size = choose[i].size
            obj.modify_time = choose[i].modify_time
            object_list.push(obj)
        }
        question.file_list = object_list
        var job_uuid = $('#task_uuid').val();
        checkIsDown = true;
        var url = "/api/v1/recovery/"+job_uuid+"/graininess/download";
        Metronic.blockUI({target: '#recoverFileTree',animate: true});
        pAjaxRequest(question, url, "POST", function (result) {
            Metronic.unblockUI('#recoverFileTree');
            checkIsDown = false;
            if (result.code == 0 || result.code == 200) {
                let downs = [];
                if (result.message.compressed_package_path != '') {
                    downs.push(result.message.compressed_package_path)
                }
                // window.location.href = result.message.compressed_package_path;
                if (result.message.skip_files_log_path != '') {
                    downs.push(result.message.skip_files_log_path)
                    //window.location.href = result.message.skip_files_log_path;
                }
                if (result.message.message != '') {
                    downFlags = false;
                    UIToastr.showWarning(result.title, result.message.message);
                } else {
                    downFlags = true;
                }
                if (downs.length <= 0) {
                    return false;
                }
                downloadFilesAndNotifyServer(downs);
                // downloadFiles(downs);
            } else {
                UIToastr.showError(result.title, result.message);
                return false;
            }
        });
    }

    function downloadFiles(fileUrls) {
        fileUrls.forEach(function(url) {
            const iframe = document.createElement('iframe');
            iframe.style.display = 'none';
            iframe.src = url;
            document.body.appendChild(iframe);
            // 确保在下载完成后移除 iframe
            iframe.onload = function() {
                document.body.removeChild(iframe);
            };
        });
    }

    // 以下是下载文件的
    function downloadFilesAndNotifyServer(fileUrls) {
        Metronic.blockUI({target: '#filediv',animate: true});
        var completedDownloads = 0;
        var totalFiles = fileUrls.length;
        function notifyIfAllCompleted() {
            if (completedDownloads === totalFiles) {
                notifyServer(fileUrls);
            }
        }
        fileUrls.forEach(function(fileUrl) {
            downloadFile(fileUrl, function(success) {
                var fileName = extractFileNameFromUrl(fileUrl);
                if (success) {
                    // console.log('文件 ' + fileUrl + ' 下载完成');
                    downFlags && UIToastr.showSuccess(LANG.UI_PUBLIC_TIPS, LANG.UI_PUBLIC_DOWNLOAD + fileName + LANG.UI_PUBLIC_SUCCESS)
                } else {
                    // console.error('文件 ' + fileUrl + ' 下载失败');
                    UIToastr.showError(LANG.UI_PUBLIC_TIPS, LANG.UI_PUBLIC_DOWNLOAD + fileName + LANG.UI_PUBLIC_FAILED)
                }
                completedDownloads++;
                notifyIfAllCompleted();
            });
        });
    }
    // 下载操作
    function downloadFile(fileUrl, callback) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', node_ip + fileUrl, true);
        xhr.responseType = 'blob';

        xhr.onload = function() {
            if (xhr.status === 200) {
                var a = document.createElement('a');
                var url = window.URL.createObjectURL(xhr.response);
                a.href = url;
                a.download = extractFileNameFromUrl(fileUrl); // 提取文件名
                document.body.appendChild(a);
                a.click();

                // 在点击之后立即撤销对象 URL
                window.URL.revokeObjectURL(url);
                document.body.removeChild(a);

                callback(true);
            } else {
                //  console.error('文件下载失败:', xhr.statusText);
                // UIToastr.showError(LANG.UI_PUBLIC_TIPS, xhr.statusText)
                callback(false);
            }
        };
        xhr.onerror = function() {
            //UIToastr.showError(LANG.UI_PUBLIC_TIPS, LANG.UI_PUBLIC_NETWORK_ERROR)
            // console.error('请求错误');
            // 尝试用iframe的形式下载
            //downloadFiles([node_ip + fileUrl]);
            //location.href = node_ip + fileUrl;
            window.open(node_ip + fileUrl, '_blank');
            Metronic.unblockUI('#filediv');
            // callback(false);
        };
        xhr.send();
    }

    // 下载完后请求回调
    function notifyServer(fileUrls) {
        Metronic.unblockUI('#filediv');
        // 请求后台接口。删除对应的资源
        pAjaxRequest({files: fileUrls, node_uuid: node_uuid}, '/api/v1/recovery/graininess_file/', "DELETE", function (result) {
            // 不管结果
        });
    }

    // 从URL中提取文件名
    function extractFileNameFromUrl(url) {
        return url.substring(url.lastIndexOf('/') + 1);
    }

    return {
        //main function to initiate the module
        init: function () {
            initTaskDetails();
            initListener();
            initLogGrid();
        }
    };
}();

jQuery(document).ready(function() {
    GraininessJob.init();
});