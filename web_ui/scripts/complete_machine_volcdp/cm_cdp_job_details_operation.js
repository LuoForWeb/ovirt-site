var _taskInfo = {};
//初始化操作按钮  
var initOpButton = function(data,autoTakeoverFlag,taskInfo){
    let taskConfInfo = data.task_conf_info;
    let taskType = taskConfInfo.task_type_value;
    let opCode = [];
    _taskInfo = taskInfo;
    let takeoverInfo = taskConfInfo.takeover_info;  //备份任务接管配置
    let  takeoverConfFlag = CONF.FLAG.UNSET;  //接管配置
     //启动1/停止2/修改3/删除4/暂停5/6启动差异/7启动增量/8启用策略/9迁移/10启动完备/11启动接管 /12停止接管/13日志备份 /14 启动回切 /15 停止回切/16创建手动标签 /17-禁用或启用自动接管
    let disableSrEnableDesc = LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_DISABLE; //停用自动接管
    let handoverInfo = data.handover_info;
    if(Object.keys(takeoverInfo).length === 0){
        takeoverConfFlag = CONF.FLAG.UNSET;
    }else{
        takeoverConfFlag = CONF.FLAG.SET;
    }
    if(taskType == CONF.TASK_TYPE.VOL_CDP_BACKUP || taskType == CONF.TASK_TYPE.VOL_CDP_REPLICATION){  //备份 或复制
        opCode = getBackupControlCode(autoTakeoverFlag,takeoverConfFlag);
        if(taskConfInfo.auto_takeover_enable_flag == true){
            disableSrEnableDesc = LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_DISABLE;  //停用自动接管
            taskInfo.auto_takeover_enable_flag = true;
        }else if(taskConfInfo.auto_takeover_enable_flag == false){
            disableSrEnableDesc = LANG.UI_VOL_CDP_BACKUP_AUTO_TAKEOVER_ENABLE;  //启用自动接管
            taskInfo.auto_takeover_enable_flag =false;
        }
    }else if(taskType==CONF.TASK_TYPE.VOL_CDP_RECOVERY){  //恢复
        opCode = getRecoverControllCode();
    }else if(CONF.TASK_TYPE.VOL_CDP_TAKEOVER ==taskType){  //接管
        opCode = getTakeoverControllCode();
    }
    var button = "";
    $.each(opCode, function(i, d){
        switch(d){
            case 1:
                button += '<li class="start"><a href="javascript:;" ><i class="viconfont vicon-ge_play"></i> ' + LANG.UI_JOB_START + '</a></li>';
                break;
            case 2:
                button += '<li class="stop"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_STOP + '</a></li>';
                break;
            case 5:
                button += '<li class="pause"><a href="javascript:;"><i class="viconfont vicon-ge_suspend-copy"></i> ' + LANG.UI_JOB_PAUSE + '</a></li>';
                break;
            case 8:
                button += '<li class="startStra"><a href="javascript:;" ><i class="viconfont vicon-ge_time_point"></i> ' + LANG.UI_JOB_START_STRATEGY + '</a></li>';
                break;
            case 11:
                if ($.inArray('global_observer', CONF.PERMISSION) != -1) break;
                button += '<li class="takeover "><a href="javascript:;" ><i class="viconfont vicon-vol_cdp_takeover"></i> ' + LANG.UI_JOB_START_TAKEOVER + '</a></li>';
                break;
            case 12:
                if ($.inArray('global_observer', CONF.PERMISSION) != -1) break;
                if(autoTakeoverFlag){ //如果是自动接管 
                    button += '<li class="stoptakeover"><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_AUTO_TAKEOVER + '</a></li>';
                }else{
                    button += '<li class="stoptakeover "><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_TAKEOVER + '</a></li>';
                }
                break;
            case 14:
                button += '<li class="startfailback"><a href="javascript:;" ><i class="viconfont vicon-ge_cutback"></i> '+LANG.UI_VOL_CDP_JOB_DETAILS_START_FAILBACK+' </a></li>';
                break;
            case 15:
                if(autoTakeoverFlag){ //如果是自动接管 
                    button += '<li class="stoptakeover"><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_AUTO_TAKEOVER + '</a></li>';
                }else{
                    button += '<li class="stoptakeover "><a href="javascript:;" ><i class="viconfont vicon-ge_stop_over"></i> ' + LANG.UI_JOB_STOP_TAKEOVER + '</a></li>';
                }
                break;
            case 16:
                button += '<li class="createlable"><a href="javascript:;"><i class="viconfont vicon-ge_sign"></i> '+LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABEL+' </a></li>';
                break;
            case 4:
                if(taskInfo.task_status == CONF.TASK_STATUS.DELETING){  //删除中
                    button += '<li class="delete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_JOB_FORCE_DELETE + '</a></li>';
                }else{
                    button += '<li class="delete"><a href="javascript:;"><i class="viconfont vicon-ge_delete"></i> ' + LANG.UI_JOB_DELETE + '</a></li>';
                }
                break;
            case 17:
                button += '<li class="disableSrEnable"><a href="javascript:;"><i class="viconfont vicon-ge_data_flow"></i> ' + disableSrEnableDesc +'</a></li>';
                break;
        }
    });
    $('#cmCdpOpList').html(button);
    initButFlag = true;
    initControlListener();
    setCmCdpBtnStatus();  //初始化/更新操作按钮
}
//根据任务状态设置按钮权限
var  setCmCdpBtnStatus = function(){
    let runningStage = _taskInfo.task_current_stage;
    let taskStatus = _taskInfo.task_status;
    let taskType = _taskInfo.task_type;
    $('.taskOperateButton').removeAttr("disabled");
    switch(taskStatus){
        case 1:	  //task is waitting for running
            setControlBtn('start', true);
            setControlBtn('stop', false);
            setControlBtn('pause', false);
            setControlBtn('createlable', false);
            setControlBtn('stoptakeover', false);
            setControlBtn('startfailback', false);
            setControlBtn('stoptfailback', false);
            setControlBtn('delete', true);
            if(taskType==CONF.TASK_TYPE.VOL_CDP_TAKEOVER ){
                setControlBtn('takeover', true);
            }else{
                setControlBtn('takeover', false);
            }
            if(runningStage==CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){
                setControlBtn('stoptakeover', false);
            }
            if(runningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || runningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
                $('.taskOperateButton').attr("disabled","disabled");
            }
            break;
        case 2:   //task is running
            setControlBtn('start', false);
            setControlBtn('startIncr', false);
            setControlBtn('startDiff', false);
            setControlBtn('startLog', false);
            setControlBtn('stop', true);
            //bug 18595需求，接管任务启动中允许停止接管任务
            // if(runningStage==CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING ){
            // 	$('.taskOperateButton').attr("disabled","disabled");
            // }
            break;
        case 10:  //运行和准备中,禁用运行
            setControlBtn('start', false);
            setControlBtn('startIncr', false);
            setControlBtn('startDiff', false);
            setControlBtn('startLog', false);
            setControlBtn('stop', true);
            break;
        case 4: //停止,禁用停止
            setControlBtn('start', true);
            setControlBtn('startIncr', true);
            setControlBtn('startDiff', true);
            setControlBtn('startLog', true);
            setControlBtn('stop', false);
            break;
        case 5:  //任务停止中
            $('.taskOperateButton').attr("disabled","disabled");
            break;
        default:
            //其他状态,开启控制
            setControlBtn('start', true);
            setControlBtn('startIncr', true);
            setControlBtn('startDiff', true);
            setControlBtn('startLog', true);
            setControlBtn('stop', true);
            break;
    }
};
//设置按钮是否可用
var  setControlBtn = function(id, available){
    if(available){
        $("." + id).find('a').removeClass('disablebtn');
    }else{
        $("." + id).find('a').addClass('disablebtn');
    }
};

/**
 * @function 获取备份任务可控制项
 * @description 为标签策略各项操作绑定初始事件函数根据任务状态和当前阶段获取当前可操作的控制项
 * @return opCode array
 */
var getBackupControlCode = function(autoTakeoverFlag,takeoverConfFlag){
    let opCode = [];
    let runningStage = _taskInfo.current_stage;  //任务阶段
    let taskStatus = _taskInfo.task_status;  //任务状态
    let disOrEnableTakeoverNumber = 17; //启用或禁用自动接管
    // opCode:1启动/2停止/3修改/4删除/5暂停/6启动差异/7启动增量/8启用策略/9迁移/10启动完备/11接管/12停止接管/13日志备份 /14 启动回切 /15 停止回切 /16继续-对应暂停/17-禁用或启用自动接管（接管和回切阶段不能执行该操作）
    switch(taskStatus){
        case CONF.TASK_STATUS.WAITTING:		//任务等待运行
            opCode = [1,4];			//启动,删除
            break;
        case CONF.TASK_STATUS.RUNNING:		//任务正在运行
            if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC){ //任务阶段:运行阶段-等待
                opCode = [2];		//停止
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC){  //任务阶段:初始化同步 或实时同步
                opCode = [2];		//停止
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC){  //实时同步
                opCode = [2,16];
                if(takeoverConfFlag == CONF.FLAG.SET){  //在备份任务配置备机的情况下，实时同步阶段可以手动执行接管操作
                    opCode = [2,11,16];
                }
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC){  //任务阶段:实时同步,切换至实时同步阶段
                opCode = [2,16];	  //停止,暂停,创建标签
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK 
                || runningStage == CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK
                || runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK
            ){  //任务阶段:服务端的数据一致性校验 或 备机的数据一致性校验
                opCode = [2];		//停止
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER ){//任务阶段:接管
                opCode = [12];		//接管
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC  
                    || runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC 
                    || runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){  //任务阶段:逆向实时同步,初始同步，回切启动中
                opCode = [15]; 		//停止回切
            }else{
                opCode = [2];		//停止

            }
            break;
        case CONF.TASK_STATUS.PAUSED:	//任务暂停
            if(runningStage== CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC
                || runningStage== CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC
                || runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC){ //初始同步、实时备份、准备切换至实时同步
                opCode = [2,16]; 	//停止,继续
            }else{
                opCode = [];
            }
            break;
        case CONF.TASK_STATUS.STOPPED:	//任务停止	
            if(runningStage== CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC
                || runningStage== CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC
                || runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC
            ){ //初始同步、实时备份、准备切换至实时同步
                opCode = [1,3,4];	//启动备份,修改,删除
            }
            //服务端数据一致性校验 或备机数据一致性校验
            else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK 
                || runningStage == CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK 
                || runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK
            ){
                opCode = [1,3,4];  	//启动备份,修改,删除
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){	//接管
                opCode = [1,4,11];	//启动备份,删除,启动接管
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER){ //逆向实时同步
                opCode = [1,4];		//启动备份,删除
            }else{
                opCode = [1,4];
            }
            break;
        case CONF.TASK_STATUS.ERROR :	//任务错误(失败)
            if(runningStage== CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC
                || runningStage== CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC
                || runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC){ //初始同步、实时备份、准备切换至实时同步
                opCode = [1,3,4];	//启动备份,修改,删除
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK 
                || runningStage == CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK 
                || runningStage == CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK 
            ){	//任务阶段:服务端的数据一致性校验 或 备机的数据一致性校验
                opCode = [1,4];		//启动，删除
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){	//接管
                opCode = [1,3,4,11];	//启动备份,修改,删除,启动接管
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC){	//任务阶段:逆向初始同步,逆向实时同步
                opCode = [12,14];	//启动回切,停止接管
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_EXEC){ //暂时定义具有上述控制项
                opCode = [1,3,4];	//启动备份,修改,删除
            }else{
                opCode = [1,3,4];
            }
            break;
        case CONF.TASK_STATUS.NETWORK_FAULT:  //网络错误
            if(runningStage== CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC
                || runningStage == CONF.CDP_TASK_RUNNING_STAGE.WAIT_CONVERT_TO_REALTIME_SYNC){ //初始同步、实时备份、准备切换至实时
                opCode = [2]; //停止
            }else if(runningStage== CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC && _taskInfo.backup_task_takeover_conf == CONF.FLAG.SET){  //实时同步阶段且配置了接管
                 opCode = [2,11]; //停止任务，启动接管
            }else{
                opCode = [1,2]; //启动，停止
            }
            break;
        case CONF.TASK_STATUS.ABNORMAL:  //任务已完成但异常
        
            if(autoTakeoverFlag == CONF.FLAG.SET){  //备份任务，配置自动接管
                opCode = [12]; //停止接管
            }else{
                opCode = [2]; //停止
            }
            break;
        case CONF.TASK_STATUS.SUCCESSED:	//任务成功
            if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){  //接管状态
                opCode = [12,14];	//启动回切,停止接管
                break;
            }
            break;
        case CONF.TASK_STATUS.DELETING:  //删除中
            opCode = [4];
            break;
        
            
        
    }
    //配置自动接管，并且任务阶段不处于接管中、接管启动中、逆向初始同步、逆向实时同步、回切启动汇总时可以操作禁用或启用自动接管
    if(autoTakeoverFlag == CONF.FLAG.SET
            && runningStage !=CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER
            && runningStage != CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING
            && runningStage != CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
            && runningStage != CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC
            && runningStage != CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING) {
        opCode.push(disOrEnableTakeoverNumber);
    }
    return opCode;
}

/**
 * @function 获取恢复任务可控制项
 * @return opCode Array
 */
var getRecoverControllCode = function(){
    let opCode = [];
    let runningStage = _taskInfo.current_stage;  //任务阶段;
    let taskStatus = _taskInfo.task_status;  //任务状态

    switch(taskStatus){
         case CONF.TASK_STATUS.WAITTING:		//任务等待运行
             opCode = [1,4];			//启动,删除
             break;
         case CONF.TASK_STATUS.RUNNING:		//任务正在运行
             opCode = [2];			//停止
             break;
         case CONF.TASK_STATUS.STOPPED:	//任务停止	
             opCode = [1,3,4];
             break;
        case CONF.TASK_STATUS.NETWORK_FAULT: //网络错误
            opCode = [2];  //停止
            break;
         case CONF.TASK_STATUS.ERROR:	//任务出错
             opCode = [1,3,4];
             break;
         case CONF.TASK_STATUS.SUCCESSED:
             opCode = [1,2,4];
             break;
         case CONF.TASK_STATUS.ABNORMAL:  //任务已完成但异常
             opCode = [2];
             break;
         case CONF.TASK_STATUS.DELETING:  //删除中
            opCode = [4];	//启动,删除
            break;
    }
    return opCode;	
}

/**
 * @function 获取接管任务可控制项
 * @return opCode Array
 */
var getTakeoverControllCode = function(){
    let opCode = [];
    let runningStage = _taskInfo.current_stage;  //任务阶段;
    let taskStatus = _taskInfo.task_status;  //任务状态
    // opCode:1启动/2停止/3修改/4删除 /5暂停/6启动差异/7启动增量/8启用策略/9迁移/10启动完备/11接管/12停止接管/13日志备份 /14 启动回切 /15 停止回切/16继续-对应暂停
    switch(taskStatus){
        case CONF.TASK_STATUS.WAITTING:		//任务等待运行
            opCode = [11,4];	//启动,删除
            break;
        case CONF.TASK_STATUS.RUNNING:		//任务正在运行	
            if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){ //任务阶段:接管中,接管启动中
                opCode = [12];			//停止接管,启动回切
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC
                    || runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC 
                    || runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){  //任务阶段:逆向实时同步,初始同步，回切启动中
                opCode = [15];
            }
            break;
        case CONF.TASK_STATUS.STOPPED:  //任务停止	
            if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){ //任务阶段:接管
                opCode = [11,3,4];
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC){ //任务阶段:逆向实时同步
                opCode = [11,4];
            }else{
                opCode = [11,4];	//启动,删除
            }
            break;
        case CONF.TASK_STATUS.SUCCESSED:  //接管任务成功
            if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER){  //任务阶段:接管中，允许停止接管和启动回切
                opCode = [12,14];
            }
            break;
        case CONF.TASK_STATUS.ABNORMAL:  //任务已完成但异常
            opCode = [12]; //停止接管
            break;
        case CONF.TASK_STATUS.ERROR:  //任务出错
            if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER || runningStage == CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING){ //任务阶段:接管中，接管启动中
                opCode = [11,4,3];
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC){ //任务阶段:逆向实时同步
                opCode = [14,15];  //启动回切,停止接管
            }else if(runningStage == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC 
                    || runningStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){ //任务阶段:逆向准备中/初始同步
                opCode = [14,15];  //启动回切,停止接管
            }
             break;
        case CONF.TASK_STATUS.DELETING:  //删除中
            opCode = [4];	//启动,删除
            break;
    }
    return opCode;
}

var initControlListener = function(){
    $('.stop').unbind().on('click', stopJob);   //终止任务
    $('.start').unbind().on('click', startJob); //启动完备任务
    // $('.startIncr').unbind().on('click', startIncr);//增量
    // $('.startDiff').unbind().on('click', startDiff);//差异
    // $('.startLog').unbind().on('click', startLog);//日志
    $('.takeover').unbind().on('click', startTakeover);  //启动接管
    $('.stoptakeover').unbind().on('click',stopTakeover); //停止接管
    $('.startfailback').unbind().on('click',startFailback); //启动回切
    $('.stoptfailback').unbind().on('click',stopFailback); //停止回切
    $('.createlable').unbind().on('click',createLable);  //创建标签点
    $('.delete').unbind().on('click',deleteTask);  //删除任务
    $('.disableSrEnable').unbind('click').click(autoTakeoverEnableAndDisableConf);
    // $('#transfer_thread_number').off().blur(transferThreadNumberChange);
}
/**
 * 启用或禁用自动接管配置
 */
var autoTakeoverEnableAndDisableConf = function (){
    let autoTakeoverEnableFlag = _taskInfo.auto_takeover_enable_flag;
    if(autoTakeoverEnableFlag ==CONF.FLAG.SET){
        var enableFlag = CONF.FLAG.UNSET;
    }else if(autoTakeoverEnableFlag == CONF.FLAG.UNSET){
        var enableFlag = CONF.FLAG.SET;
    }else{
        var enableFlag = CONF.FLAG.SET;
    }
    let uuids = [];
    uuids.push(_taskInfo.task_uuid);
    Metronic.blockUI({target: '#jobDetailDiv',animate: true});
    pAjaxRequest({'job_uuid':uuids , 'enable_flag': enableFlag}, '/api/v1/jobs/switch_autotakeover', 'POST', (res)=>{
        Metronic.unblockUI('#jobDetailDiv');
        operateResponseList(res);
    });
}


//删除任务
var deleteTask = function(){
    var params = [];
    var deleteFlag = false;
    var message = LANG.UI_JOB_DELETE_JOB_TIPS;
    bootbox.confirm({
        title: LANG.UI_JOB_DELETE_JOB,
        message: message,
        callback: debounce(function(result){
            if (!result) return;
            if (!deleteFlag) {
                params.push(_taskInfo.task_uuid);
                Metronic.blockUI({target: '#jobDetail',animate: true });
                pAjaxRequest({ "job_uuids": params }, '/api/v1/jobs', 'DELETE', function (data) {
                    Metronic.unblockUI('#jobDetail');
                    var op = LANG.UI_JOB_DELETE_JOB;
                    operateResponseList(data, op);
                });
            }
        },300)
    });  //延迟执行
}

//创建标签点
var createLable = function(){
    bootbox.prompt({
        title: LANG.UI_VOL_CDP_JOB_DETAILS_ADD_LABEL_REMARK,
        callback: debounce(function(result){
            if(result=="") {
                UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABELNODE, LANG.UI_VOL_CDP_JOB_DETAILS_LABELNODE_IS_NULL_MESSAGE);
                return false;
            }else if(!result){
                return;
            }
            $(this).modal('hide');
            submitLableRemark($.trim(result));
        },300,false)
    });  //延迟执行
}

//提交创建标签点
var submitLableRemark = function(remark){
    var byteLength = remark.replace(/[^\u0000-\u00ff]/g,"aa").length;
    if(byteLength>256){
        UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABELNODE, LANG.UI_VOL_CDP_JOB_DETAILS_LABELNODE_MESSAGE);
        return false;
    }
    Metronic.blockUI({target: '#jobDetail',animate: true});
    var params = {};
    params.uuid = _taskInfo.task_uuid;
    params.remark = remark;
    pAjaxRequest(params, '/api/v1/volcdp/job/tag_point', 'POST', function (res) {
        Metronic.unblockUI('#jobDetail');
        var op = LANG.UI_VOL_CDP_JOB_DETAILS_CREATE_LABELNODE;
        operateResponseList(res, op)
    });
}

// 停止回切
var stopFailback = function(){
    var initErrorFlag = false;
    getUserPassword();
    let bootBoxText = LANG.UI_VOL_CDP_JOB_STOP_TAKEOVER_CONFIRM;
    bootbox.prompt({
        title: bootBoxText,
        inputType: 'password',
        callback: debounce(function(result){
            if (result == null) return;
            if (hex_md5(result) == _taskInfo.user_password) {
                _userIsVerify = true;
                stopTakeoverJob(row);
                $(this).modal('hide');
            } else {
                $('.bootbox-input').css('border-color', "#a94442");
                if (!initErrorFlag) {
                    var des = '<p class="password-error" style="margin-top:5px;color:#a94442">' + LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS + '</p>';
                    $('.bootbox-input').after(des);
                    initErrorFlag = true;
                }
                return false;
            }
        },300,false)
    });  //延迟执行
}


//启动回切操作
var startFailback = function(){
    let info = {};
    info.task_uuid = _taskInfo.task_uuid;
    info.task_type = _taskInfo.task_type;
    info.start_type = 14; //启动回切
    Metronic.blockUI({target: '#jobDetail', animate: true});
    // 获取回切配置相关参数
    pAjaxRequest(info, "/api/v1/complete_machine_volcdp/jobs/object_info", "GET", function (result) {
        Metronic.unblockUI('#jobDetail');
        let data = result.data;
        if(data.length==0){
            $("#takeoverFailbackConf").trigger("click");
            return;
        }
        var masterInfo = data['master_info'];
        var targetMachine = data['target_machine'];
        var targetDev = data['target_dev'];
        let targetDevStr = "";

        let isNull = false;
        var devStr = $.map(targetDev, function(item) {
            var name = item.dev_name;
            if(name == ""){
                isNull = true;
            }
            return name;
        }).join(', ');
        if(isNull){
            targetDevStr = LANG.UI_PUBLIC_HOST;
        }else{
            targetDevStr =  LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS1 + "  [  " + devStr+" ] ";
        }
        

        var titleTips = "<span>" + LANG.UI_VOL_CDP_BACKUP_TIPS + "</span>:  ";
        var desSpan = " <span style='font-size: 14px;'>"
            + LANG.UI_VOL_CDP_JOB_START_FAILBACK_OPERATE_TIPS1 + "  "
            + masterInfo + " " + LANG.UI_VOL_CDP_JOB_START_FAILBACK_OPERATE_TIPS2 + "   "
            + targetMachine + targetDevStr + ",  " 
            + LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS
            + LANG.UI_CM_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS +"</br></span>"
            + "<span>"+LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS1+"</span>";
        var failBackDes = titleTips + desSpan;
        var initErrorFlag = false;
        getUserPassword();
        bootbox.prompt({
            title: failBackDes,
            inputType: 'password',
            callback: debounce(function(result){
                if(result == null) return;
                if(hex_md5(result) == _taskInfo.user_password){
                    _userIsVerify = true;
                    startFailbackJob();
                    $(this).modal('hide');
                }else{
                    $('.bootbox-input').css('border-color', "#a94442");
                    if(!initErrorFlag){
                        var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
                        $('.bootbox-input').after(des);
                        initErrorFlag = true;
                    }
                    return false;
                }
            },300,false)
        });  //延迟执行

    });
}

/**
 * 启动回切任务执行函数
 */
var startFailbackJob = function (row) {
    var uuids = [];
    uuids.push(_taskInfo.task_uuid);
    Metronic.blockUI({target: '#jobDetail', animate: true});
    pAjaxRequest({ 'job_uuids': uuids}, '/api/v1/jobs/start_failback', 'POST', function (res) {
        Metronic.unblockUI('#jobDetail');
        if (res.data.info.length > 0) {
            var op = LANG.UI_JOB_SEND_START_FAILBACK_MSG_TIPS;
            operateResponseList(res, op)
        } else {
            UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_START_FAILBACK, LANG.UI_VOL_CDP_JOB_DEFAILS_FAILBACK_CONFIG_MESSAGE);
            return;
        }
    })
}

//停止接管
var stopTakeover = function(){
    let initErrorFlag = false;
    getUserPassword();
    var bootBoxText = LANG.UI_VOL_CDP_JOB_STOP_TAKEOVER_CONFIRM;
    if(_taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_TAKEOVER){
        var bootBoxText = LANG.UI_CM_CDP_JOB_STOP_TAKEOVER_AND_VERIF_CONFIRM;
    }
    //回切阶段下停止接管
    var currentState = _taskInfo.current_stage;
    if(currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC || currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC || currentState == CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
        bootBoxText = LANG.UI_VOL_CDP_JOB_FAILBACK_STOP_TAKEOVER_CONFIRM;	
    }
    bootbox.prompt({ 
        title: bootBoxText,
        inputType: 'password',
        callback: debounce(function(result){
            if(result == null) return;
            if(hex_md5(result) == _taskInfo.user_password){
                _userIsVerify = true;
                stopTakeoverJob();
                $(this).modal('hide');
            }else{
                $('.bootbox-input').css('border-color', "#a94442");
                if(!initErrorFlag){
                    var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
                    $('.bootbox-input').after(des);
                    initErrorFlag = true;
                }
                return false;
            }
        },300,false) //延迟执行
    });  
}
//停止接管任务
var stopTakeoverJob = function(){
    var uuids = [];
    uuids.push(_taskInfo.task_uuid);
    var op = LANG.UI_OS_SEND_STOP_TAKEOVER_TASK_MESSAGE;
    Metronic.blockUI({target: '#jobDetail', animate: true});
    pAjaxRequest({
        'job_uuids': uuids
    }, '/api/v1/jobs/stop_takeover', 'POST', function (d) {
        Metronic.unblockUI('#jobDetail');
        operateResponseList(d, op);
    })
}


//启动接管
var startTakeover = function (){
    if($(this).find('a').hasClass('disablebtn')){
        return true;
    }
    //判断任务类型，如果是备份任务或者复制任务启动接管，需要进行密码校验
    if(_taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_BACKUP || _taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_REPLICATION){
        var titleTips= "<span>"+LANG.UI_VOL_CDP_BACKUP_TIPS+"</span></br></br>";
        var desSpan = "<span style='font-size: 14px;'>"+LANG.UI_CM_CDP_START_TAKEOVER_TIPS+ "</span>";
        var recoverDes = titleTips + desSpan ;
        var initErrorFlag = false;
        getUserPassword();
        bootbox.prompt({
            title: recoverDes,
            inputType: 'password',
            callback: debounce(function(result){
                if(result == null) return;
                if(hex_md5(result) == _taskInfo.user_password){
                    _userIsVerify = true;
                    sendStartTakeover();
                    $(this).modal('hide');
                }else{
                    $('.bootbox-input').css('border-color', "#a94442");
                    if(!initErrorFlag){
                        var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
                        $('.bootbox-input').after(des);
                        initErrorFlag = true;
                    }
                    return false;
                }
            },300,false)
        });  //延迟执行
    }else{
        sendStartTakeover();
    }
}

//发送启动接管消息
var sendStartTakeover = function(){
    let op = LANG.UI_OS_SEND_START_TAKEOVER_TASK_MESSAGE;
    var uuids = [];
    uuids.push(_taskInfo.task_uuid);
    // 增加启动接管api
    Metronic.blockUI({target: '#jobDetail',animate: true});
    pAjaxRequest({'job_uuids': uuids},  '/api/v1/jobs/start_takeover', 'POST', function (data) {
        Metronic.unblockUI('#jobDetail');
       operateResponseList(data, op)
    });
}

//启动完全
var startJob = function(){
    if($(this).find('a').hasClass('disablebtn')){
        return true;
    }
    let taskType = _taskInfo.task_type;
    if(taskType==CONF.TASK_TYPE.VOL_CDP_RECOVERY){
        getStarTaskObjectInfo(taskType,1);
    }else{
        startJobFunc(taskType, 1);
    }
}

 /**
 * 获取启动任务主备机/源和目标机详情
 */
 var getStarTaskObjectInfo = function(){
    var info = {};
    info.task_uuid = _taskInfo.task_uuid;
    info.task_type = _taskInfo.task_type;
    info.start_type = 1;
    var paramsInfo = JSON.stringify(info);
    Metronic.blockUI({target: '#jobDetail',animate: true});
    $.post(CONF.AJAXPATH, {m:CONF.M.JOB,f:"getStartTaskObjectInfo",p:paramsInfo}, function(d){
        Metronic.unblockUI('#jobDetail');
        var data = JSON.parse(d);
        var masterInfo = data['master_info'];
        var targetMachine = data['target_machine'];
        var titleTips= "<span>"+LANG.UI_VOL_CDP_BACKUP_TIPS+"</span></br>";
        var desSpan = "<span style='font-size: 14px;'>"+LANG.UI_VOL_CDP_JOB_START_RECOVERY_OPERATE_TIPS1+ "： " + masterInfo +" " + LANG.UI_VOL_CDP_JOB_START_RECOVERY_OPERATE_TIPS2 + ": "+ targetMachine +" " +LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_COVER_TIPS + LANG.UI_VOL_CDP_JOB_START_DATA_COVERAGE_OPERATE_TIPS+"</span>";
        var recoverDes = titleTips + desSpan ;

        var initErrorFlag = false;
        getUserPassword();
        bootbox.prompt({
            title: recoverDes,
            inputType: 'password',
            callback: debounce(function(result){
                if(result == null) return;
                if(hex_md5(result) == _taskInfo.user_password){
                    _userIsVerify = true;
                    startJobFunc(_taskInfo.taskType, 1);
                     $(this).modal('hide');
                }else{
                    $('.bootbox-input').css('border-color', "#a94442");
                    if(!initErrorFlag){
                        var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
                        $('.bootbox-input').after(des);
                        initErrorFlag = true;
                    }
                    return false;
                }
            },300,false)
        });  //延迟执行
    });
}
//执行启动操作
var  startJobFunc = function (taskType, type) {
    Metronic.blockUI({target: '#jobDetail', animate: true});
    var params = {
        'start_type': type
    };
    let taskUuid = $('#task_uuid').val();
    pAjaxRequest(params, "/api/v1/jobs/start/" + taskUuid + "", 'POST', function (data) {
        Metronic.unblockUI('#jobDetail');
        var op = LANG.UI_COPY_SEND_START_JOB_MESSAGE;
        operateResponseList(data, op);

    });
}

//停止任务
var stopJob = function(){
    if($(this).find('a').hasClass('disablebtn')){
        return true;
    }
    if(_taskInfo.task_type ==CONF.TASK_TYPE.VOL_CDP_BACKUP || _taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_REPLICATION ||  _taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_RECOVERY){
        let initErrorFlag = false;
        
        getUserPassword();
        let title = LANG.UI_VOL_CDP_JOB_STOP_RECOVERY_CONFIRM;
        if(_taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_BACKUP){
            title = LANG.UI_VOL_CDP_JOB_STOP_BACKUP_CONFIRM;
        }else if(_taskInfo.task_type == CONF.TASK_TYPE.VOL_CDP_REPLICATION){
            title = LANG.UI_VOL_CDP_JOB_STOP_COPY_CONFIRM;
        }
       
        bootbox.prompt({ 
            title: title,
            inputType: 'password',
            // callback: function (result) {
            callback: debounce(function(result){
                if(result == null) return;
                if(hex_md5(result) == _taskInfo.user_password){
                    _userIsVerify = true;
                    stopJobFunction();
                    $(this).modal('hide');
                }else{
                    $('.bootbox-input').css('border-color', "#a94442");
                    if(!initErrorFlag){
                        var des = '<p class="password-error" style="margin-top:5px;color:#a94442">'+LANG.UI_VOL_CDP_BACKUP_ERROR_PASSWORD_TIPS+'</p>';
                        $('.bootbox-input').after(des);
                        initErrorFlag = true;
                    }
                    return false;
                }
            },300,false) //延迟执行
        });  
    }else{
        stopJobFunction();
    }
}
/**
 * 执行任务控制操作
 * @param {*} funName 
 */
var stopJobFunction = function(){
    let op = LANG.UI_COPY_SEND_STOP_JOB_MESSAGE;
    let data = {};
    data.task_type = _taskInfo.task_type;
    data.task_status = _taskInfo.task_status;
    data.uuid = $('#task_uuid').val();
    data.module = CONF.MODULE_TYPE.VOL_CDP;
    let taskUuid = $('#task_uuid').val();
    params = JSON.stringify(data);
    Metronic.blockUI({target: '#jobDetail',animate: true});

    pAjaxRequest({}, "/api/v1/jobs/stop/" + taskUuid + "", "POST",function (res) {
        Metronic.unblockUI('#jobDetail');
        operateResponseList(res, op)
    });

}
//初始化当前用户密码用于删除二次确认
var getUserPassword = function(){
    let p = {};
    pAjaxRequest(p, " /api/v1/users/password", "GET", function (result) {
        let data = result.data;
        _taskInfo.user_password = data.password;
    });
}