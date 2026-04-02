/**
 * 整机卷实时任务详情数据流向
 */
var old_options = null;
(($) => {
    $.fn.dataFlow = (options) => {
        let isDataEqual = (options,old_options) => {
            // 这里可以根据你的数据结构进行相等性比较
            return JSON.stringify(options) === JSON.stringify(old_options);
        }
        if(isDataEqual(options,old_options)){
            return;
        }
        // 需要深拷贝
        old_options = JSON.parse(JSON.stringify(options));
        // 任务阶段
        let task_stage = options.task_info.current_stage;
        // 任务状态
        let task_status = options.task_info.task_status;
        // 任务类型
        let task_type = options.task_info.task_type;
        // 源机信息
        let source_info = options.task_info.data_source_agent;
        let source_agent_uuid = options.task_info.data_source_agent_uuid;
        // 备份服务器信息
        let backup_info = options.task_info.backup_server;
        // 目标机信息
        let target_info = options.task_info.target_agent;
        let target_agent_uuid = options.task_info.target_agent_uuid;
        // 是否复制任务
        let is_replication_task = options.task_info.is_replication_task;
        // （复制）是否配置了接管
        let backup_task_takeover_conf = options.task_info.backup_task_takeover_conf;
        // 自动接管标志
        let auto_takeover_enable_flag = options.task_info.auto_takeover_enable_flag;
        // 速度
        let speed = options.task_info.speed;
        // 回切目标的IP
        let failback_target_info = options.task_info.failback_target_info;
        $('.data_flow_content').empty();

        let params = {
            'source_agent_uuid':source_agent_uuid,
            'target_agent_uuid':target_agent_uuid
        }

        let source_agent_online ='';
        let target_agent_online = '';
        let getAgentStatus = () => {
            pAjaxRequest(params,'/api/v1/complete_machine_volcdp/jobs/agent_status','GET',(d)=>{
                source_agent_online = d.data.source_agent_online;
                target_agent_online = d.data.target_agent_online;
                if(source_agent_online){
                    $('.sourceAgent').attr('src','./img/cm_cdp/sourceHost_online.svg');
                }else{
                    $('.sourceAgent').attr('src','./img/cm_cdp/sourceHost_offline.svg');
                }
                if(target_agent_online){
                    $('.targetAgent').attr('src','./img/cm_cdp/targetHost_online.svg');
                }else{
                    $('.targetAgent').attr('src','./img/cm_cdp/targetHost_offline.svg');
                }
            },false);
        }

        getAgentStatus();

        // 备份任务
        if(task_type == CONF.TASK_TYPE.VOL_CDP_BACKUP || task_type == CONF.TASK_TYPE.VOL_CDP_REPLICATION){
            // 复制任务和非复制任务都有是否开启接管配置
            if(is_replication_task){
                // 复制任务(复制一定有备机)
                $('.data_flow_content').empty();
                let content =  `<div class="backup_takeover_content" style="display:flex;align-items:baseline;">
                                    <div class="col-md-2" style="position:relative;">
                                        <div style="text-align: center">`;
                if(source_agent_online){
                    content += `<img class="sourceAgent" src="./img/cm_cdp/sourceHost_online.svg">`;
                }else{
                    content += `<img class="sourceAgent" src="./img/cm_cdp/sourceHost_offline.svg">`;
                }
                content += `</div>
                                        <div class="textonline">
                                            <span class="agentcolor sourceName"></span>
                                            <span class="sourceIP titleDes approve-time ipcolor"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="position: relative;z-index: 1;">
                                        <div class="sourceTobackup" style="">
                                            <div class="sourceToBackupServerSpeed textalignc"></div>
                                            <img class="sourceToBackupServerStatus" src="./img/cm_cdp/stateless.svg" style="width: 205px;">
                                        </div>
                                    </div>
                                    <div class="col-md-2" style="position:relative;">
                                        <div style="text-align: center">
                                            <img class="backupAgent" src="./img/cm_cdp/backupServer_online.svg" alt="">
                                        </div>
                                        <div class="textonline">
                                        <span class="agentcolor">
                                            <span>`+ LANG.UI_BACKUP_NODE +`:</span>
                                        </span>
                                            <span class="backupIP ipcolor"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="position: relative;z-index: 1;">
                                        <div class="backupTotarget">
                                            <div class="backupServerTotargetSpeed textalignc"></div>
                                            <img class="backupServerTotargetStatus" src="./img/cm_cdp/stateless.svg" style="width: 205px;">
                                        </div>
                                    </div>
                                    <div class="col-md-2" style="position:relative;">
                                        <div style="text-align: center">`;
                if(target_agent_online){
                    content += `<img class="targetAgent" style="margin-left: 13px;" src="./img/cm_cdp/targetHost_online.svg">`;
                }else{
                    content += `<img class="targetAgent" style="margin-left: 13px;" src="./img/cm_cdp/targetHost_offline.svg">`;
                }
                content += `</div>
                                        <div class="textonline">
                                            <span class="agentcolor">`+ LANG.UI_PUBLIC_STANDBY_HOST +`:</span>
                                            <span class="targetIP titleDes approve-time ipcolor"></span>
                                        </div>
                                    </div>
                                    <div class="failbackSpeed textalignc"></div>
                                    <img id="failbackStatus" src="" alt="">
                                    
                                </div>`;
                $('.data_flow_content').append(content);
                $('.backupServerTotargetStatus').css({'margin-top':'0px'});
                // 任务状态
                switch (task_status){
                    case CONF.TASK_STATUS.UNKNOWN:
                    case CONF.TASK_STATUS.WAITTING:
                    case CONF.TASK_STATUS.STOPPED:
                        $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                        $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                        break;
                    case CONF.TASK_STATUS.RUNNING:
                        switch (task_stage){
                            case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:
                            case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:
                            case CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK:
                                $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/running.gif'); // 2
                                $('.sourceToBackupServerSpeed').html(speed);
                                $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running.gif'); // 2
                                $('.backupServerTotargetSpeed').html(speed);
                                break;
                            case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
                            case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                                $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                                $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running.gif'); // 2
                                $('.backupServerTotargetSpeed').html(speed);
                                break;
                            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:
                                $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                                $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/heart_beat.gif'); // 5
                                $('.backupServerTotargetStatus').css({'margin-top':'-10px'}); // 5
                                break;
                            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:
                            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:
                                $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  //  1
                                $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/heart_beat.gif'); //  5
                                $('.backupServerTotargetStatus').css({'margin-top':'-10px'});       // 5
                                $('#failbackStatus').attr('src','./img/cm_cdp/failback.gif');       // 7
                                $('.failbackSpeed').html(speed);
                                break;
                        }
                        $('.speed').html(speed);
                        break;
                    case CONF.TASK_STATUS.NETWORK_FAULT:
                    case CONF.TASK_STATUS.ERROR:
                        $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/error.svg');  // 3
                        $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/error.svg');  // 3
                        break;
                    case CONF.TASK_STATUS.SUCCESSED:
                        switch (task_stage) {
                            case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                                $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                                $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running.gif');    // 2
                                $('.backupServerTotargetSpeed').html(speed);
                                break;
                        }
                        break;
                }
            }else{
                // 备份任务
                let content = `<div class="backup_no_target_content" style="display:flex;align-items:baseline;">
                                    <div class="col-md-3" style="position:relative;">
                                        <div style="text-align: center">`;
                if(source_agent_online){
                    content += `<img class="sourceAgent" src="./img/cm_cdp/sourceHost_online.svg">`;
                }else{
                    content += `<img class="sourceAgent" src="./img/cm_cdp/sourceHost_offline.svg">`;
                }
                content += `</div>
                                        <div class="textonline">
                                            <span class="agentcolor sourceName"></span>
                                            <span class="sourceIP titleDes approve-time ipcolor"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="position: relative;z-index: 1;">
                                        <div class="sourceTobackup" style="">
                                            <div class="sourceToBackupServerSpeed textalignc"></div>
                                            <img class="sourceToBackupServerStatus sourceToBackupServerStatus_backup" src="./img/cm_cdp/stateless_470.svg" style="">
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="position:relative;">
                                        <div style="text-align: center">
                                            <div class="backupServerTotargetSpeed textalignc"></div>
                                            <img class="backupAgent" src="./img/cm_cdp/backupServer_online.svg" alt="">
                                        </div>
                                        <div class="textonline">
                                        <span class="agentcolor">
                                            <span>`+ LANG.UI_BACKUP_NODE +`:</span>
                                        </span>
                                            <span class="backupIP ipcolor"></span>
                                        </div>
                                    </div>
                                </div>`;
                $('.data_flow_content').append(content);
                switch (task_status){
                    case CONF.TASK_STATUS.UNKNOWN:
                    case CONF.TASK_STATUS.WAITTING:
                    case CONF.TASK_STATUS.STOPPED:
                        $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless_470.svg');  // 1
                        $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/stateless_470.svg');  // 1
                        break;
                    case CONF.TASK_STATUS.RUNNING:
                        switch (task_stage){
                            case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:
                            case CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK:
                                $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/running_470.gif');    //  2
                                $('.sourceToBackupServerSpeed').html(speed);
                                $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/stateless_470.svg');  //  1
                                break;
                            case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:
                            case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:
                                $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/running_470.gif');  // 2
                                $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running_470.gif');  // 2
                                $('.sourceToBackupServerSpeed').html(speed);
                                break;
                            case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
                            case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                            case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK:
                                $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless_470.svg');  // 1
                                $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running_470.gif');  // 2
                                break;
                            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:
                                $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/heart_beat_470.gif');  // 5
                                $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/heart_beat_470.gif');  // 5
                                break;
                            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:
                            case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:
                                $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/heart_beat_470.gif');  // 5
                                $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/heart_beat_470.gif'); //  5
                                $('#failbackStatus').attr('src','./img/cm_cdp/failback.gif');    //7
                                $('.failbackSpeed').html(speed);
                                break;
                        }
                        $('.speed').html(speed);
                        break;
                    case CONF.TASK_STATUS.NETWORK_FAULT:
                    case CONF.TASK_STATUS.ERROR:
                        $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/error_470.svg');  // 3
                        $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/error_470.svg');  // 3
                        break;
                    case CONF.TASK_STATUS.SUCCESSED:
                        switch (task_stage){
                            case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                                $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless_470.svg');  // 1
                                $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running_470.gif');  // 2
                                break;
                        }
                        break;
                }
                if(backup_task_takeover_conf == CONF.FLAG.SET){
                    // （备份）配置了接管
                    $('.data_flow_content').empty();
                    let content =  `<div class="backup_takeover_content" style="display:flex;align-items:baseline;">
                                    <div class="col-md-2" style="position:relative;">
                                        <div style="text-align: center">`;
                    if(source_agent_online){
                        content += `<img class="sourceAgent" src="./img/cm_cdp/sourceHost_online.svg">`;
                    }else{
                        content += `<img class="sourceAgent" src="./img/cm_cdp/sourceHost_offline.svg">`;
                    }
                    content += `</div>
                                        <div class="textonline">
                                            <span class="agentcolor sourceName"></span>
                                            <span class="sourceIP titleDes approve-time ipcolor"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="position: relative;z-index: 1;">
                                        <div class="sourceTobackup" style="">
                                            <div class="sourceToBackupServerSpeed textalignc"></div>
                                            <img class="sourceToBackupServerStatus" src="./img/cm_cdp/stateless.svg" style="width: 205px;">
                                        </div>
                                    </div>
                                    <div class="col-md-2" style="position:relative;">
                                        <div style="text-align: center">
                                            <img class="backupAgent" src="./img/cm_cdp/backupServer_online.svg" alt="">
                                        </div>
                                        <div class="textonline">
                                        <span class="agentcolor">
                                            <span>`+ LANG.UI_BACKUP_NODE +`:</span>
                                        </span>
                                            <span class="backupIP ipcolor"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="position: relative;z-index: 1;">
                                        <div class="backupTotarget">
                                            <div class="backupServerTotargetSpeed textalignc"></div>
                                            <img class="backupServerTotargetStatus" src="./img/cm_cdp/stateless.svg" style="width: 205px;">
                                        </div>
                                    </div>
                                    <div class="col-md-2" style="position:relative;">
                                        <div style="text-align: center">`;
                    if(target_agent_online){
                        content += `<img class="targetAgent" style="margin-left: 13px;" src="./img/cm_cdp/targetHost_online.svg">`;
                    }else{
                        content += `<img class="targetAgent" style="margin-left: 13px;" src="./img/cm_cdp/targetHost_offline.svg">`;
                    }
                    content += `</div>
                                        <div class="textonline">
                                            <span class="agentcolor">`+ LANG.UI_PUBLIC_STANDBY_HOST +`:</span>
                                            <a id="standbyHostRemoteControl">
                                                <span class="targetIP titleDes approve-time ipcolor"></span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="failbackSpeed textalignc"></div>
                                    <img id="failbackStatus" src="" alt="">
                                    
                                </div>`;
                    $('.data_flow_content').append(content);
                    // 任务状态
                    switch (task_status){
                        case CONF.TASK_STATUS.UNKNOWN:
                        case CONF.TASK_STATUS.WAITTING:
                        case CONF.TASK_STATUS.STOPPED:
                            $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                            $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                            break;
                        case CONF.TASK_STATUS.RUNNING:
                            switch (task_stage){
                                case CONF.CDP_TASK_RUNNING_STAGE.INIT_SYNC:
                                case CONF.CDP_TASK_RUNNING_STAGE.SERVER_REEALTIME_CONS_CHECK:
                                    $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/running.gif');    //  2
                                    $('.sourceToBackupServerSpeed').html(speed);
                                    $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/stateless.svg');  //  1
                                    break;
                                case CONF.CDP_TASK_RUNNING_STAGE.REALTIME_SYNC:
                                case CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK:
                                    $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/running.gif');  // 2
                                    $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running.gif');  // 2
                                    $('.sourceToBackupServerSpeed').html(speed);
                                    if(task_status == CONF.CDP_TASK_RUNNING_STAGE.SERVER_CONS_CHECK){
                                        $('.backupServerTotargetSpeed').html(speed);
                                    }
                                    break;
                                case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
                                case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                                case CONF.CDP_TASK_RUNNING_STAGE.STANDBY_CONS_CHECK:
                                    $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                                    $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running.gif');  // 2
                                    $('.backupServerTotargetSpeed').html(speed);
                                    break;
                                case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:
                                    $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/heart_beat.gif');  // 5
                                    $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/heart_beat.gif');  // 5
                                    break;
                                case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:
                                case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:
                                    $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/heart_beat.gif');  // 5
                                    $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/heart_beat.gif'); //  5
                                    $('#failbackStatus').attr('src','./img/cm_cdp/failback.gif');    //7
                                    $('.failbackSpeed').html(speed);
                                    break;
                            }
                            break;
                        case CONF.TASK_STATUS.NETWORK_FAULT:
                        case CONF.TASK_STATUS.ERROR:
                            $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/error.svg');  // 3
                            $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/error.svg');  // 3
                            break;
                        case CONF.TASK_STATUS.SUCCESSED:
                            switch (task_stage){
                                case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                                    $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                                    $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running.gif');  // 2
                                    $('.backupServerTotargetSpeed').html(speed);
                                    break;
                            }
                            break;
                    }
                }
            }
        }
        // 接管任务
        else if(task_type == CONF.TASK_TYPE.VOL_CDP_TAKEOVER){
            // 任务状态
            let content =  `<div class="takeover_content" style="display:flex;align-items:baseline;">
                                    <div class="col-md-2" style="position:relative;">
                                        <div style="text-align: center">`;
            if(source_agent_online){
                content += `<img class="sourceAgent" src="./img/cm_cdp/sourceHost_online.svg">`;
            }else{
                content += `<img class="sourceAgent" src="./img/cm_cdp/sourceHost_offline.svg">`;
            }
            content += `</div>
                                        <div class="textonline textonline_takeover">
                                            <span class="agentcolor sourceName"></span>
                                            <span class="sourceIP titleDes approve-time ipcolor"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="position: relative;z-index: 1;">
                                        <div class="sourceTobackup" style="">
                                            <div class="sourceToBackupServerSpeed textalignc"></div>
                                            <img class="sourceToBackupServerStatus" src="./img/cm_cdp/stateless.svg" style="width: 205px;">
                                        </div>
                                    </div>
                                    <div class="col-md-2" style="position:relative;">
                                        <div style="text-align: center">
                                            <img class="backupAgent" src="./img/cm_cdp/backupServer_online.svg" alt="">
                                        </div>
                                        <div class="textonline textonline_takeover">
                                        <span class="agentcolor">
                                            <span>`+ LANG.UI_BACKUP_NODE +`:</span>
                                        </span>
                                            <span class="backupIP ipcolor"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="position: relative;z-index: 1;">
                                        <div class="backupTotarget">
                                            <div class="backupServerTotargetSpeed textalignc"></div>
                                            <img class="backupServerTotargetStatus" src="./img/cm_cdp/stateless.svg" style="width: 205px;">
                                        </div>
                                    </div>
                                    <div class="col-md-2" style="position:relative;">
                                        <div style="text-align: center">`;
            if(target_agent_online){
                content += `<img class="targetAgent" style="margin-left: 13px;" src="./img/cm_cdp/targetHost_online.svg">`;
            }else{
                content += `<img class="targetAgent" style="margin-left: 13px;" src="./img/cm_cdp/targetHost_offline.svg">`;
            }
            content += `</div>
                                        <div class="textonline textonline_takeover">
                                            <span class="agentcolor">` + LANG.UI_PUBLIC_STANDBY_HOST +`:</span>
                                            <a id="standbyHostRemoteControl">
                                                <span class="targetIP titleDes approve-time ipcolor"></span>
                                            </a>
                                        </div>
                                    </div>
                                    <div class="failbackSpeed textalignc"></div>
                                    <img id="failbackStatus" src="" alt="">
                                </div>`;
            $('.data_flow_content').append(content);
            switch (task_status){
                case CONF.TASK_STATUS.UNKNOWN:
                case CONF.TASK_STATUS.WAITTING:
                case CONF.TASK_STATUS.STOPPED:
                    $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                    $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                    break;
                case CONF.TASK_STATUS.RUNNING:
                    if(auto_takeover_enable_flag){
                        $('.targetAgent').attr('src','./img/cm_cdp/takeoverAgent.gif');
                    }
                    switch (task_stage){
                        case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER_STARTING:
                        case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                            $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                            $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running.gif');  // 2
                            $('.backupServerTotargetSpeed').html(speed);
                            break;
                        case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING:
                            $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                            $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/heart_beat.gif');  // 5
                            $('.backupServerTotargetStatus').css({'margin-top':'-10px'});
                            break;
                        case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC:
                        case CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC:
                            $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');   // 1
                            $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/heart_beat.gif');  // 5
                            $('#failbackStatus').attr('src','./img/cm_cdp/failback.gif');                // 7
                            $('.backupServerTotargetStatus').css({'margin-top':'-10px'});
                            $('.failbackSpeed').html(speed);
                            break;
                    }
                    break;
                case CONF.TASK_STATUS.NETWORK_FAULT:
                case CONF.TASK_STATUS.ERROR:
                    $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/error.svg');  // 3
                    $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/error.svg');  // 3
                    break;
                case CONF.TASK_STATUS.SUCCESSED:
                    switch (task_stage){
                        case CONF.CDP_TASK_RUNNING_STAGE.IN_TAKEOVER:
                            $('.sourceToBackupServerStatus').attr('src','./img/cm_cdp/stateless.svg');  // 1
                            $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running.gif');  // 2
                            $('.backupServerTotargetSpeed').html(speed);
                            break;
                    }
                    break;
            }
        }
        // 恢复任务
        else if(task_type == CONF.TASK_TYPE.VOL_CDP_RECOVERY){
            // 任务状态
            let content = `<div class="recover_content" style="display:flex;align-items:baseline;">
                                    <div class="col-md-3" style="position:relative;">
                                        <div style="text-align: center">
                                            <img class="backupAgent" src="./img/cm_cdp/backupServer_online.svg" alt="">
                                        </div>
                                        <div class="textonline">
                                        <span class="agentcolor">
                                            <span>`+ LANG.UI_BACKUP_NODE +`:</span>
                                        </span>
                                            <span class="backupIP ipcolor"></span>
                                        </div>
                                    </div>
                                    <div class="col-md-6" style="position: relative;z-index: 1;">
                                        <div class="backupTotarget">
                                            <div class="sourceToBackupServerSpeed textalignc"></div>
                                            <img class="backupServerTotargetStatus backupServerTotargetStatus_recovery" src="./img/cm_cdp/stateless.svg">
                                        </div>
                                    </div>
                                    <div class="col-md-3" style="position:relative;">
                                        <div style="text-align: center">
                                            <div class="backupServerTotargetSpeed textalignc"></div>`;
            if(target_agent_online){
                content += `<img class="targetAgent" style="margin-left: 13px;" src="./img/cm_cdp/targetHost_online.svg">`;
            }else{
                content += `<img class="targetAgent" style="margin-left: 13px;" src="./img/cm_cdp/targetHost_offline.svg">`;
            }
            content += `</div>
                                        <div class="textonline">
                                            <span class="agentcolor">`+ LANG.UI_PUBLIC_STANDBY_HOST +`:</span>
                                            <span class="targetIP titleDes approve-time ipcolor"></span>
                                        </div>
                                    </div>
                                </div>`;
            $('.data_flow_content').append(content);
            $('.sourceToBackupServerStatus').css({'width':'470px'});
            switch (task_status){
                case CONF.TASK_STATUS.UNKNOWN:
                case CONF.TASK_STATUS.WAITTING:
                    $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/stateless_470.svg');  // 1
                    break;
                case CONF.TASK_STATUS.RUNNING:
                    $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/running_470.gif');  // 2
                    $('.sourceToBackupServerSpeed').html(speed);
                    break;
                case CONF.TASK_STATUS.NETWORK_FAULT:
                case CONF.TASK_STATUS.ERROR:
                    $('.backupServerTotargetStatus').attr('src','./img/cm_cdp/error_470.svg');  // 3
                    break;
            }
        }
        const isFailbackStage = (
            task_stage === CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC ||
            task_stage === CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC ||
            task_stage === CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING
        );
        $('.sourceIP').text(isFailbackStage ? failback_target_info : source_info);
        $('.sourceName').text(isFailbackStage ? LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_HOST + ':' : LANG.UI_PUBLIC_HOST + ':');
        $('.backupIP').text(backup_info);
        $('.targetIP').text(target_info);


        let initListeners = () => {
            var divElement = document.querySelectorAll('.cmCdpAgent');
            for(let i=0;i<divElement.length;i++){
                // 为该元素添加mouseover事件监听器
                divElement[i].addEventListener('mouseover', function () {
                    var cmCdpAgentTitle = $(this).siblings('.cmCdpAgentTitle').first();
                    cmCdpAgentTitle.show();
                });

                divElement[i].addEventListener('mouseout',function (){
                    var cmCdpAgentTitle = $(this).siblings('.cmCdpAgentTitle').first();
                    cmCdpAgentTitle.hide();
                })
            }

        }

        initListeners();


        // 检测浏览器类型
        const isChrome = /Chrome/.test(navigator.userAgent) && /Google Inc/.test(navigator.vendor) &&!(/Edg/.test(navigator.userAgent));
        const isEdge = /Edg/.test(navigator.userAgent);
        const failbackStatusDom = document.querySelector('#failbackStatus');
        if (failbackStatusDom) {
            if (isChrome) {
                failbackStatusDom.classList.add('chrome');
            } else if (isEdge) {
                failbackStatusDom.classList.add('edge');
            }
        }
    }
})(jQuery);