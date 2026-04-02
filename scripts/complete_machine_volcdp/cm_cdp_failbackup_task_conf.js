
(($) => {
	//解析并渲染备机网关信息
	$.fn.taskFailbackConf = (options) =>{
		let taskInfo = options.task_info;
        let taskDevList = options.task_dev_list;
        let taskUuid = taskInfo.task_uuid;
        let taskType = taskInfo.task_type;
        let targetAgentUuid = taskInfo.target_agent_uuid;  //任务备机UUID
        let targetAgent = taskInfo.target_agent;  //任务备机客户端
        let dataSourceAgentUuid = taskInfo.data_source_agent_uuid;  //任务数据源客户端UUID

        let _agentOsType = "";
        var _takeoverHisBusinessIpMap = "";
        var _failbackHisBusinessIpMap = "";
        var _failbackHisStorageuuid = "";
        var _failbackCationIp  = "";
        var _agentNetworkInfo = [];  //主机客户端对应的网卡信息
        var _standbyNetworkInfo = []; //备机客户端对应的网卡信息
        var _takeoverIpMapList =[];
        var _takeoverIpMapListView = [];
        var _standbyGatewayConf = [];
        var ipMapInfoList = [];
        var _modifyFailbackHisBusinessIpMap = []; //修改回切任务网卡历史配置
        var _failTransferCompressValue = 1;  //回切传输压缩默认值
        var _failTransferEncryptValue = 0;  //回切传输加密默认值
        var _failbackupHisTargetuuid = "";
        var _failbackTimepointUuid = "";  //回切数据源时间点UUID
        var _hisDevConf = {};  //回切历史配置dev
        let crossPlatformInfo = {
            "cross_platform_flag" : 0,
            "cross_platform_conf" :'',
        };
        let master_os_type = taskInfo.master_os_type;
        let master_memory_size = taskInfo.master_memory_size;
        //获取回切相关配置
        let getFailbackupConf = function(){
            intmemorycache();//内存缓存开关切换事件
		    transferSwitch();  //传输配置开关切换事件
            getTaskNetCardHistoryConf(); //获取回切网卡映射历史配置
            backupFailbackDataToBackupServer();  //回切数据到备份服务器
            
            var info = {};
			info.task_uuid = taskUuid;
            info.task_type = taskType;
			//初始化备份源
            // $('#cmCdpFailbackTaskConf').addClass("active");
            // $('.drawer-backdrop').addClass("active");

            Metronic.blockUI({target: '#cmCdpFailbackTaskConf',animate: true});
            pAjaxRequest(info, "/api/v1/complete_machine_volcdp/jobs/failback_conf", "GET", function (res) {
                Metronic.unblockUI("#cmCdpFailbackTaskConf");
                
    		    var msg = res.data;
                let data = msg[0];
                var failbackup_target_uuid = data['failbackup_target_uuid'];
    		    var file_cache_alloc_space = data['file_cache_alloc_space'];
    		    var file_cache_storage_path = data['file_cache_storage_path'];
                var hostList = data['host_list'];
                _failbackupHisTargetuuid = failbackup_target_uuid;

                var memory_cache_alloc_space = data['memory_cache_alloc_space'];
                var transport_compress_flag = data['transport_compress_flag'];
                var transport_compress_method = data['transport_compress_method'];  //压缩方式
                if(transport_compress_method!=0){
                    _failTransferCompressValue = transport_compress_method;
                }
                var mirror_backup_flag = data['mirror_backup_flag'];
                _agentOsType = data['standby_os_type'];
                var transport_thread_num = data['transport_thread_num'];
                var transport_block_size = data['transport_block_size'];
                
                var transport_encrypt_flag = data['transport_encrypt_flag'];
                var transport_encrypt_method = data['transport_encrypt_method'];  //传输方式
    
                var io_replication_mode = data['io_replication_mode']; //IO 复制模式
                var rebuildPartitionFlag = parseInt(data['rebuild_partition_flag']);
			    var netModel = data['net_model'];
                var node_uuid = data['node_uuid'];
                var default_storage_uuid = data['storage_uuid']; //已配置存儲信息；如果已配置接管回切，獲取回切配置信息，未配置則获取任务配置信息
			    _failbackHisStorageuuid = default_storage_uuid;
                
                var failbackTargetDev = data['failback_target_dev'];
                
                if(netModel==2){  //网络模式为2
                    $(".tasktransfernetworkDiv").show();
                    initNetworkList(node_uuid);  //加载传输网络
                }else if(netModel ==1){  //网络模式为1
                    $(".tasktransfernetworkDiv").hide();
                    $('#taskTransferNetwork').empty();
                }else{
                    $(".tasktransfernetworkDiv").hide();
                    initNetworkList(node_uuid);  //加载传输网络
                }
                if(io_replication_mode==0){  //未配置，默认复制为异步
                    io_replication_mode = 2;
                }
                if(file_cache_storage_path ==""){
                    file_cache_storage_path = LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT;
                }
                 $('.drivercheckview').hide();  //默认隐藏驱动监测
                initNetworkList(node_uuid);  //加载传输网络
                // 资源监测
                var oldInfo;
                if(master_os_type == 'Linux'){
                    oldInfo = data.high_pressure_strategy_linux;
                }else{
                    oldInfo = data.high_pressure_strategy_windows;
                }
                let options = {
                    osType:master_os_type,
                    data: oldInfo
                };
                if (master_os_type != 'Linux') {
                    options.windowsMemory = master_memory_size;
                }
                ResourceMonitoring.init(options);
                if(hostList.length>0){
                    fileCachePathChange();
                    loadStorageInfo(node_uuid,default_storage_uuid);
                    var unit = 1024*1024;  //该项配置，选项单位为MB，传输到后台的值为字节；
                    if(file_cache_alloc_space){
                        $("#file_cache_size").val(file_cache_alloc_space/unit);
                    }
                    if(memory_cache_alloc_space){
                        $('#memory_cache_size').val(memory_cache_alloc_space/unit);
                    }
                    if(file_cache_storage_path ) {
                        file_cache_storage_path = file_cache_storage_path.replace(/\\\\/g, "\\")
                    }
    
                    $('#takeoverFileCachePath').val(file_cache_storage_path);
                    $('#takeoverFileCachePath').prop('title', file_cache_storage_path);
    
                    $('#transfer_thread_number').val(transport_thread_num);
                    $('#transfer_datapackage_size').val(transport_block_size);
    
                    $('#cutbackTransferEncryptMethod').val(transport_encrypt_method);
                    $('#cutBacktransferCompressGrade').val(transport_compress_method);
    
                    if(io_replication_mode==1){ //同步
                        $("#ioReplicationSyncModle").prop("checked","checked");
                        $.uniform.update()
                    }else if(io_replication_mode==2){ //异步
                        $("#ioReplicationAsyncModle").prop("checked","checked");
                        $.uniform.update()
                    }
                    //回切生产数据是否备份到服务端标志位
                    if(mirror_backup_flag==CONF.FLAG.SET){
                        $('#fbMirrorDataToServer').bootstrapSwitch('state', true);
                        $('.selectstoragediv').show();
                    }else{
                        $('#fbMirrorDataToServer').bootstrapSwitch('state', false);
                        $('.selectstoragediv').hide();
                    }
                    
                    if(transport_compress_flag==CONF.FLAG.SET){
                        $('#takeoverTranCompressSwitch').bootstrapSwitch('state', true);    
                    }else{
                        $('#takeoverTranCompressSwitch').bootstrapSwitch('state', false);   
                    }
                    
                    if(transport_encrypt_flag==CONF.FLAG.SET){
                        $('#takeoverTranEncryptSwitch').bootstrapSwitch('state', true);
                    }else{
                        $('#takeoverTranEncryptSwitch').bootstrapSwitch('state', false);    
                    }
                    $('.failback_takeover_ip_map_list').html('');  //清空历史配置
                    parseFailBackHistDevConf(failbackTargetDev);  //解析并组装回切设备信息
                    parseHostInfo(hostList,failbackup_target_uuid, rebuildPartitionFlag);
                    if(_failbackupHisTargetuuid !=""){
                        parseFailBackHisNetCardConf();  //解析历史网络配置
                    }
                    ipMapInfoList = [];  //清空主机IP映射
                }else{
                    UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_VOL_CDP_JOB_DETAILS_BACKUP_SELECT_DATABASE_MESSAGE);
                    return false;
                }
            }, true);
        }
        /**
         * 解析回切目标设备信息
         */
        let parseFailBackHistDevConf = function(failbackTargetDev){
            if(failbackTargetDev.length>0){
                var info = {};
                for (var i = 0; i < failbackTargetDev.length; i++){
                    let sourceDev = failbackTargetDev[i]['dev_uuid'];
                    let targetDev = failbackTargetDev[i]['failback_target_dev_uuid'];
                    let selectedType = "manual";
                    if(targetDev==""){
                        selectedType = "selected";
                        targetDev = 2;  //回切目标设备为空，默认为2时表示自动分配
                    }
                    info[sourceDev] = {
                        'selected_type': "selected",
                        'selected_value':targetDev
                    };
                }
                _hisDevConf = info;
            }
        }
        /**
         * 加载节点所在存储，默认选中回切任务历史配置存储设备
         */
        var loadStorageInfo = function(node_uuid,default_storage_uuid){
            var data = {};
            data.nodeuuid = $('#selectnode').val();
            var jsonData = JSON.stringify(data);
            var info = {};
            info.offset = 0;
            info.limit = 100;
            info.node_uuid = node_uuid;
            pAjaxRequest(info, "/api/v1/storages", "GET", function (res) {  //获取任务主机网卡信息
                var softselect = $('#selectstorage');
                softselect.empty();
                if(res.success){
                    let data = res.data.rows;
                
                    for(var i=0; i<data.length; i++){
                        if(data[i].storage_type==CONF.BD_STORAGE_TYPE.CLOUD){
                            continue;
                        }
                        var selected = '';
                        if(_failbackHisStorageuuid == data[i].storage_uuid){
                            selected = "selected";
                        }
                        let storageDesc = data[i].storage_nickname + "(" + LANG.UI_PUBLIC_TOTAL_SIZE2+": "+data[i].total_size + " "+LANG.UI_PUBLIC_FREE_SIZE + ":" + data[i].free_size+")";
                        var option = $("<option "+ selected+">").text(storageDesc).val(data[i].storage_uuid);
                        softselect.append(option);
                    }
                }
            });
        }

        
        /**
         * 获取任务历史网络配合
         */
        var getTaskNetCardHistoryConf = function(){
            var p = {};
            p.task_uuid = $("#task_uuid").val();
            p.task_type = taskType;
            if(taskType == CONF.TASK_TYPE.VOL_CDP_TAKEOVER || taskType ==CONF.TASK_TYPE.VOL_CDP_BACKUP || taskType == CONF.TASK_TYPE.VOL_CDP_REPLICATION){
                pAjaxRequest(p, "/api/v1/volcdp/job/network/history_conf", "GET", function (res) {  //获取任务主机网卡信息
                    var res = res.data;
                    _takeoverHisBusinessIpMap = res.takeover_business_ip_map; //任务接管网络配置
                    _failbackHisBusinessIpMap = res.failback_business_ip_map; //回切网络配置
                });
            }
        }
        //回切传输配置开关
        var transferSwitch = function (){
            $('#takeoverTranEncryptSwitch').on('switchChange.bootstrapSwitch', takeoverTranEncryptChange);
            $('#takeoverTranCompressSwitch').on('switchChange.bootstrapSwitch', takeoverTranCompressChange);
            $('#cutBacktransferCompressGrade').unbind('change').bind('change',(transferCompressGradeChange));
        }
        /**
         * 传输压缩切换事件
         */
        var transferCompressGradeChange = function (){
            _failTransferCompressValue = $("#cutBacktransferCompressGrade").val();
        }
        //回切传输加密配置

        var takeoverTranEncryptChange = function (){
            if(this.checked){
                _failTransferEncryptValue = 1;
                $('.transfer-encrypt-method-form').hide();  //卷cdp模块暂时不支持国密加密方式，开启后传输加密方式的默认值未rsa
            }else{
                $('.transfer-encrypt-method-form').hide();
                _failTransferEncryptValue = 0;
            }
        }
        //回切传输数据压缩
        var takeoverTranCompressChange = function (){
            if(this.checked){
                $('.transferCompressGradeDiv').show();
                $('#cutBacktransferCompressGrade').val(_failTransferCompressValue);
            }else{
                $('.transferCompressGradeDiv').hide();
            }
        }
        /**
         * 备份回切生成数据到备份服务器绑定事件
         */
        var backupFailbackDataToBackupServer = function(){
            $('#fbMirrorDataToServer').on('switchChange.bootstrapSwitch', fbMirrorDataToServerChange);  //备份回切生成数据到备份服务器
        }
        /**
         * 备份回切生成数据到备份服务器
         */
        var fbMirrorDataToServerChange = function(){
            if(this.checked){
                $('.selectstoragediv').show();
            }else{
                $('.selectstoragediv').hide();
            }
        }

        /**
         * 解析回切历史网卡映射配置
         */
        var parseFailBackHisNetCardConf = function(){
            var failbackHisNetConf = _failbackHisBusinessIpMap;
            if(failbackHisNetConf.length>0){
                for(var i =0;i < failbackHisNetConf.length;i++){
                    var nicName = failbackHisNetConf[i].nic_name;
                    // var nicMac = failbackHisNetConf[i].nic_mac;
                    // var nicGateway = failbackHisNetConf[i].nic_gateway;
                    var targetNicInfo = failbackHisNetConf[i].nic_ip_info;
                    var gatewayStr = "";
                    for(var j= 0;j <targetNicInfo.length;j++){
                        var hostIp = targetNicInfo[j].ip;
                        // var hostNetMask = targetNicInfo[j].netmask;
                        var targetGateway = targetNicInfo[j].target_gateway;
                        var targetNicName = targetNicInfo[j].target_nic_name;
                        gatewayStr += hostIp+"("+LANG.UI_DRILLS_NETWORK_WAY +":"+ targetGateway +")" + LANG.UI_VOL_CDP_FAILBACK_TARGET_IP_SERVICE_CONF +":"+targetNicName;
                    }
                    var nicConfDes = LANG.UI_VOL_CDP_STANDBY_NETCARD +": " + nicName+"["+gatewayStr+"]";
                    historyHostGatewayConfDes(nicName,failbackHisNetConf,nicConfDes);
                }
            }
        }
        /**
         * @function:解析可供回切的目标主机及卷信息
         */
        var parseHostInfo = function (hostList,failbackup_target_uuid, rebuildPartitionFlag){
            var hostSelect = $('#failBackHost');
            hostSelect.empty();
            var targetAgentType = 1;
            var option = $("<option>").text(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TARGET_HOST_SELECT).val(0);
            hostSelect.append(option);
            hostSelect.append(0);
            var selectIndex = -1;
            for(var i=0; i<hostList.length; i++){
                var hostUuid = hostList[i].uuid;
                var agentType = parseInt(hostList[i].agent_type);
                var taskIsRunning = hostList[i]['task_is_running'];
                var netModel = hostList[i]['net_model'];
                option = $("<option>").text(hostList[i].text).val(hostUuid)
                    .attr('data-taskisrunning',taskIsRunning)
                    .attr('agent-type', agentType)
                    .attr('net-model',netModel);
                hostSelect.append(option);
                if(hostUuid == failbackup_target_uuid && taskIsRunning !=CONF.FLAG.SET){
                    hostSelect.val(failbackup_target_uuid);  //默认选中任务所在客户端
                    getFailbackTargetConfigData();
                    if (agentType === 2) {  // LiveCD显示重建分区，默认打开
                        targetAgentType = 2;
                        $('#rebuildPartDiv').show();
                        if (rebuildPartitionFlag === CONF.FLAG.SET) {
                            $('#rebuildPartSwitch').bootstrapSwitch('state', true);
                        } else {
                            $('#rebuildPartSwitch').bootstrapSwitch('state', false);
                        }
                    }
                }
                selectIndex = $.inArray(failbackup_target_uuid, hostList[i]);
            }
            $("#failBackHost").unbind('change').bind('change',(failBackupHostChange));
        }
        /**
         * 获取接管回切历史网卡网卡配置描述信息，并渲染
         */
        var historyHostGatewayConfDes = function(nicName,failbackHisNetConf,nicConfDes){
            var des = "";
            var conf_id = nicName.replace(/\s+/g, "_"); // 替换空格为下划线
            var des = '<li style="margin-top:15px;" class="list-group-item popovers takeoverNetcardTips input-sm" id="takeover_netcard_'+conf_id
                +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
                + nicConfDes + '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 80%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">'
                + nicConfDes + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="takeover_netcard_del'+  conf_id +'" >'
                +'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
            $('.failback_takeover_ip_map_list').append(des);
            $('.takeoverNetcardTips').popover();	   //初始化tips
            _modifyFailbackHisBusinessIpMap = _failbackHisBusinessIpMap;
            $('.takeover_netcard_del'+ conf_id ).bind("click",clickFailBakHistConfDesc);
        }
        /**
         * 删除回切历史网卡配置
         */
        var clickFailBakHistConfDesc = function(){
            $('.popover.in').remove();
            var className = this.className;
            var splitClassName = className.split('takeover_netcard_del');
            var eventConfID = splitClassName[1];
            for(var i=0;i<_modifyFailbackHisBusinessIpMap.length;i++){
                var confId = _modifyFailbackHisBusinessIpMap[i].nic_name.replace(/\s+/g, "_"); // 替换空格为下划线
                if(eventConfID == confId){
                    $('#takeover_netcard_' + confId).remove();
                    _modifyFailbackHisBusinessIpMap.splice($.inArray(_modifyFailbackHisBusinessIpMap[i],_modifyFailbackHisBusinessIpMap),1);
                    break;
                }
            }
        }


        /**
         * @function:回切目标主机change事件，获取当前接管任务对应的time_point_uuid
         */
        var failBackupHostChange = function(){
            getFailbackTargetConfigData();
        }

        //获取回切时间点信息
        let getFailbackTargetConfigData = () => {
            var p = {};
            p.task_uuid = taskUuid;
            p.task_type = taskType;
            let failbackupTargetuuid = $("#failBackHost").val();
            let agentType = $("#failBackHost option:selected").attr('agent-type');
            let agentName = $('#failBackHost option:selected').text();

            if(failbackupTargetuuid==0){
                return;
            }
            if(failbackupTargetuuid != dataSourceAgentUuid && failbackupTargetuuid!=_failbackupHisTargetuuid){  
                 $('.failback_takeover_ip_map_list li').remove();
                _takeoverIpMapList = [];
            }
            
            //获取回切备份点对应的磁盘结构
            Metronic.blockUI({target: '#cmCdpFailbackTaskConf',animate: true});
            pAjaxRequest(p, "/api/v1/complete_machine_volcdp/jobs/time_point_uuid", "GET", function (res) {
                if(res.success){
                    var data = res.data;
                    let timePointUuid = data.timepoint_uuid;
                    _failbackTimepointUuid = timePointUuid;
                    getFailbackTargetConfig(timePointUuid,failbackupTargetuuid);
                    //agent是内存操作系统，需要执行监测驱动
                    $('.drivercheckview').hide();
                    if(agentType  == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){  
                        $('.drivercheckview').show();
                        initDriverCheck(failbackupTargetuuid,_failbackTimepointUuid); //初始化驱动检测
                        $('#cmReplicationCheckDriver').unbind('click').click(checkDriverEvent);
                    }
                }
            });
        }
        /**
         * 触发检测驱动
         */
        let checkDriverEvent = function(){
            let cmStandbySelect = $('#failBackHost').val();
            if(cmStandbySelect == '' || cmStandbySelect == null){
                UIToastr.showWarning(LANG.UI_CM_CDP_STANDBY_CONF, LANG.UI_CM_CDP_COPY_STANDBY_CONF_TIPS);
                return false;
            } 
            $.fn.driverCheck.check($('#replicationDriverCheck'));
        }
        /**
         * 初始化驱动检测
         */
        let initDriverCheck = function  (agentUuid,timePointUuid) {
            $.fn.driverCheck.init($('#replicationDriverCheck'), {
                width: {
                    'config_label': 'col-md-2',
                    'config_content':'col-md-10',
                    'check_name':'col-md-5',  // 检测内容的名称宽度，默认值为col-md-3
                    'check_platform': 'col-md-5',  //检测内容的是否异构平台宽度，默认值为col-md-3
                    // check_driver: 检测内容的驱动检测宽度，默认值为col-md-6
                    // result_name: 检测结果的名称宽度，默认值为col-md-3
                    // result_detail: 检测结果的详情宽度，默认值为col-md-7
                    // result_upload: 检测结果的上传按钮宽度，默认值为col-md-2
                },
                getCheckObject: () => {
                    return {
                        source_list: [
                        {
                            name: targetAgent,
                            timepoint_uuid:timePointUuid,
                            module_type:CONF.MODULE_TYPE.VOL_CDP,
                            // agent_uuid: targetAgentUuid,
                        }],
                        target_info: {
                            target_type: $.fn.driverCheck.DRIVER_TARGET_TYPE.CLIENT,
                            client_info: {
                                agent_uuid: agentUuid,
                            },
                        }
                    };
                },
                show_check_btn: false,
                driver_usage:$.fn.driverCheck.DRIVER_USAGE_ENUM.RECOVERY,
                afterCheck:(result,data)=>{
                    crossPlatformInfo.cross_platform_flag = data[0].diff_platform_type;
                    crossPlatformInfo.cross_platform_conf = data[0].driver_hw_id_map;
                }
            });
        
        }
        /**
         * 获取回切目标主机配置
         */
        let getFailbackTargetConfig = (timePointUuid,failbackupTargetuuid) => {
            $('#cmCdpFailbackTargetDiskInfo').initTargetPlug({
                type: 7, //1为定时操作系统恢复, 2为定时虚拟机恢复, 3为实时备份, 4为实时恢复, 5为实时整机接管,6为实时挂机接管,7,接管任务回切
                origin_task_uuid: "",   //源任务uuid(实时,定时可不传或为空)  node.task_uuid
                origin_agent_uuid: "", //源代理主机uuid(实时,定时可不传或为空)  node.agent_uuid
                origin_timepoint_uuid: timePointUuid, //源时间点uuid或时间点(实时,定时)  node.timepoint_uuid
                target_agent_uuid: failbackupTargetuuid,  //目标代理主机uuid(目标端主机)  目标机
                target_visible_flag: true,  //控制恢复目标的列是否显示 true显示,false不显示
                recover_type: true, //控制恢复目标的选择框显示在哪儿, true为只显示在磁盘层,false为只显示在最后一层即为卷层
                target_auto_flag: false, //是否显示恢复目标选择框的自动分配选项
                target_input_flag:false, //是否把搜索框当成自定义输入框使用,true为自定义输入框,false为搜索框
                target_input_min: -1, //当选项中出现最少多少个选项后才显示搜索框或者输入框,为-1时不显示输入框或搜索框
                origin_data_array:[], //前端源信息,当origin_data_flag为true时使用这个数据
                nocheck: true, //是否显示所有勾选框, true为不显示勾选框, false为显示勾选框
                forced_relationship: true, //是否强制关联勾选.默认为强制关联勾选
                match_dev_uuid_list: taskDevList,
                first_column_name:LANG.UI_CM_CDP_SOURCE_DISK_NAME,
                second_column_name:LANG.UI_SYSTEM_MONITOR_MBIT,
                third_column_name:LANG.UI_CM_CDP_JOB_DETAILS_FAILBACK_TARGET,
                init_selected: _hisDevConf,
            });
            setTimeout(function(){
                Metronic.unblockUI("#cmCdpFailbackTaskConf");
            },1000);
            
        }

        /**
         * 接管回切网络配置
         */
        var takeoverFailbackNetworkConf = function(){
            var failBackupHostuuid = $('#failBackHost').val();
            if(failBackupHostuuid=="0"){
                UIToastr.showWarning(LANG.UI_VOL_CDP_HOST_IP_SERVICE_MAP, LANG.UI_VOL_CDP_FAILBACK_TARGET_TIPS);
                return;
            }
            var p = {};
            p.task_uuid = taskUuid;
            p.task_type = taskType;
            p.failbackhost = failBackupHostuuid;
            
            getTaskNetCardHistoryConf();
            pAjaxRequest(p, "/api/v1/volcdp/network/conf_info", "GET", function (res) {  //获取任务主机网卡信息
                if(res.success){
                    $('#taskFailbackupModal').modal('hide');
                    $('#failbackTakeoverNetworkConfModal').modal({'width':'850px', 'height':'460px'});
                   

                    $('.cancelnetworkconfmodal').unbind('click').click(closeNetworkConfModal);
                    $('.closenetworkconfmodal').unbind('click').click(closeNetworkConfModal);
                    _agentNetworkInfo = res['data']['agent_nic_list'];
                    _standbyNetworkInfo = res['data']['standby_nic_list'];
                    $('#failback_takeover_ip_server_conf').takeoverIpServerMapConfig({
                        agentNetwork:_agentNetworkInfo,
                        standbyNetwork: _standbyNetworkInfo,
                        takeoverHisNetwork:[],
                        failbackHisNetwork:_failbackHisBusinessIpMap,
                        failbackupCationIp:_failbackCationIp,  //接管回切IP
                        failbackupCationConf:true,
                    });
                    $('#failback_standby_gateway_conf').takeoverStandbyGatewayConfig({    
                        agentNetwork:_agentNetworkInfo,
                        standbyNetwork: _standbyNetworkInfo,
                        takeoverHisNetwork:[],
                        failbackHisNetwork:_failbackHisBusinessIpMap,
                        failbackupCationIp:"",
                        failbackupCationConf:true,
                    });
                }else{
                    UIToastr.showWarning(LANG.UI_CM_CDP_FAILBACK_NETWORK_CONFIG_FAIL, "info");
                }
                setTimeout(function(){
                    var parentElement = $('#failbackTakeoverNetworkConfModal').parent();
                    parentElement.css('z-index', 99999);
                },100);
            });
            $("#submit_failback_takeover_network_conf").unbind('click').click(submitHostNetworkConf);
        }
        /**
         * 获取配置网卡描述信息，并渲染配置
         */
        var submitHostNetworkConf = function(){
            var networCardConfInfo = {};
            var gateAwyConfDesList = [];
            _standbyGatewayConf = getStandbyGateWayConf();
            var checkIp = checkIpFormat(_standbyGatewayConf);
            if(!checkIp){
                _takeoverIpMapList = [];
                UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_STANDBY_DEFAULT_NETCARD_FOFMAT_ERROR);
                return false;
            }
            var standbyNetCardConfList = standbyNetCardConf();
            for(var i=0;i<_agentNetworkInfo.length;i++){
                var ipSetStr = "";
                var ipMapInfo = {};
                var nicIpList = [];
                var agentMapInfo  = {};
                ipMapInfo.nic_gateway = _agentNetworkInfo[i].gateway_address;
                ipMapInfo.nic_name = _agentNetworkInfo[i].name;
                ipMapInfo.nic_mac = _agentNetworkInfo[i].mac_address;
                var ipSet = _agentNetworkInfo[i].ip_set;
                if(!ipSet){break;}

                var liId = getUuid();
                var hostMac = $("#host_network_mac_"+i).text();  //遍历所有网卡
                //当前选中主机网卡名
                var activeNetCardText = $('#hostNetCardTab').find('li.active').filter(function() {
                    return $(this).closest('ul').is('ul');
                }).text();

                if( $.trim(activeNetCardText) != _agentNetworkInfo[i].name){
                    continue;
                }
 
                for(var j=0;j<ipSet.length;j++){
                    var nicIpInfo = {};
                    var standbyNetCardSelect =  "standby_network_card_"+i+j;
                    var hostIp = $("#"+standbyNetCardSelect).find("option:selected").attr("host-ipdrr");
                    var netMask = $("#"+standbyNetCardSelect).find("option:selected").attr("agent-netmask");
                    var targetGateway = $("#"+standbyNetCardSelect).find("option:selected").attr("standby-gateway");
                    var targetNicName = $("#"+standbyNetCardSelect).find("option:selected").text();
                    var targetHostMac = $("#"+standbyNetCardSelect).val();
                    var nicResult = false;
                    nicResult = ipMapInfoList.some(item => {
                        if (item.agent_map_info.nic_mac == _agentNetworkInfo[i].mac_address && hostMac == _agentNetworkInfo[i].mac_address ){
                            return true;
                        }else{
                            return false;
                        }
                    });
                    if(nicResult && $.trim(activeNetCardText) == _agentNetworkInfo[i].name){
                        UIToastr.showWarning(LANG.UI_VOL_CDP_MODIFY_TAKEOVER_IP_SERVICE_MAP, LANG.UI_VOL_CDP_HOST_IP_SERVICE_MAP_DP_TIPES);
                        return;
                    }
                    if(hostIp){
                        nicIpInfo.ip = hostIp;
                        nicIpInfo.netmask = netMask;
                        nicIpInfo.target_nic_name = targetNicName;
                        nicIpInfo.target_nic_mac = targetHostMac;
                        var result = standbyNetCardConfList.some(item=>{
                            if(item.selectNicname==targetNicName){
                                targetGateway = item.selectGateway;
                            }
                        });
                        var targetGatewayStr = targetGateway;
                        if(targetGateway=="" || targetGateway==null){
                            targetGatewayStr = "--"
                        }
                        nicIpInfo.target_gateway = targetGateway;
                        nicIpList.push(nicIpInfo);
                        var gatewayStr = "("+LANG.UI_DRILLS_NETWORK_WAY +":"+ targetGatewayStr +")";
                        ipSetStr += hostIp + gatewayStr + LANG.UI_VOL_CDP_FAILBACK_TARGET_IP_SERVICE_CONF+ ":"+targetNicName+"  ";
                    }
                }
                if(nicIpList.length>0){
                    ipMapInfo.nic_ip_info = nicIpList;
                    agentMapInfo.agent_map_info = ipMapInfo;
                    agentMapInfo.conf_id = liId;
                    agentMapInfo.conf_des = LANG.UI_VOL_CDP_STANDBY_NETCARD +": "+_agentNetworkInfo[i].name+" ["+ipSetStr+"] ";
                    ipMapInfoList.push(agentMapInfo);
                    _takeoverIpMapListView.push(agentMapInfo);
                }
            }
            
            if(ipMapInfoList.length>0){
                $('.failback_takeover_ip_map_list li').remove();
                _takeoverIpMapList = ipMapInfoList;
                hostGatewayConfDes();
            }
            closeNetworkConfModal();
        }
        /**
         * 获取配置网卡描述信息，并渲染
         */
        var hostGatewayConfDes = function(){
            var des = "";
            for(var i=0;i<_takeoverIpMapList.length;i++){
                var conf_id = _takeoverIpMapList[i].conf_id;
                var des = '<li style="margin-top:15px;" class="list-group-item popovers takeoverNetcardTips input-sm" id="takeover_netcard_'+ _takeoverIpMapList[i].conf_id 
                    +'" data-container="body" data-trigger="hover" data-placement="top" data-html="true" data-content="'
                    + _takeoverIpMapList[i].conf_des + '"><div class="col1"><div class="cont "><div class="cont-col1"></div><div class="cont-col2"><div class="desc list-one" style="width: 80%;overflow:hidden;text-overflow: ellipsis;white-space: nowrap">' 
                    + _takeoverIpMapList[i].conf_des + '</div></div></div></div><div class="col2  pull-right delete-list" style="position:absolute;right:5px;width:20px;top:4px;"><a class="takeover_netcard_del'+  _takeoverIpMapList[i].conf_id +'" >'
                    +'<div class="label label-sm label-danger" style="padding:0;"><i class="fa fa-times"></i></div></a></div></li>';
                $('.failback_takeover_ip_map_list').append(des);
                $('.takeoverNetcardTips').popover();	   //初始化tips
            
                $('.takeover_netcard_del'+ conf_id ).bind("click",{index:i},clickHandler);
            }
        }

        /**
         * 为每一个<li>绑定点击事件，并处理移除数组操作
         */
        var clickHandler = function(event){
            var className = this.className;
            var splitClassName = className.split('takeover_netcard_del');
            var eventConfID = splitClassName[1];
            $('.popover.in').remove();
            var index = event.data.index;
            var confId;
            var list = _takeoverIpMapListView;
            for(var i = 0;i<list.length;i++){
                confId =  list[i].conf_id;
                if(eventConfID == confId){
                    $('#takeover_netcard_' + confId).remove();
                    _takeoverIpMapListView.splice($.inArray(_takeoverIpMapListView[i],_takeoverIpMapListView),1);
                    break;
                }
            }
            for(var n=0;n<ipMapInfoList.length;n++){
                if(confId == ipMapInfoList[n].conf_id){
                    ipMapInfoList.splice($.inArray(ipMapInfoList[n],ipMapInfoList),1);
                    break;
                }
            }
            for(var j=0;j<_takeoverIpMapList.length; j++){
                if(confId == _takeoverIpMapList[j].conf_id){
                    _takeoverIpMapList.splice($.inArray(_takeoverIpMapList[j],_takeoverIpMapList),1);
                    break;
                }
            }
        }

        /**
         * 校验网关格式
         */
        var checkIpFormat = function (){
            var ipVerify = true; 
            for(var i=0;i<_standbyGatewayConf.length;i++){
                var gateway = _standbyGatewayConf[i].gateway;
                if(gateway=="" && !ipV4V6(gateway)){
                    ipVerify = false;
                    break;
                }
            }
            return ipVerify;
        }
        /**
         * @function 生成uuid
         */
        var getUuid = function() {
            var len = 36;//36长度
            var radix = 16;//16进制
            var chars = '0123456789abcdefghijklmnopqrstuvwxyz'.split('');
            var uuid = [], i;
            radix = radix || chars.length;
            if(len) {
            for(i = 0; i < len; i++)uuid[i] = chars[0 | Math.random() * radix];
            } else {
            var r;
            uuid[8] = uuid[13] = uuid[18] = uuid[23] = '-';
            uuid[14] = '4';
            for(i = 0; i < 36; i++) {
                if(!uuid[i]) {
                r = 0 | Math.random() * 16;
                uuid[i] = chars[(i == 19) ? (r & 0x3) | 0x8 : r];
                }
            }
            }
            return uuid.join('');
        }
        //获取备机网卡信息，用于替换主机映射网卡中的备机网关
        var standbyNetCardConf = function(){
            var standbyNetCardInfo = {};
            var standbyNetCardList = [];
            for(var i = 0;i<_standbyNetworkInfo.length;i++){
                var standbyGatewaySelectId = "standby_netcard_macaddress_"+_standbyNetworkInfo[i].name;
                var selectNicname = $("#"+standbyGatewaySelectId).find("option:selected").attr("agent-nicname");
                var selectGateway = $("#"+standbyGatewaySelectId).val();
                standbyNetCardInfo.selectNicname = selectNicname;
                standbyNetCardInfo.selectGateway = selectGateway;
                standbyNetCardList.push(standbyNetCardInfo);
            }
            return standbyNetCardList;
        }
        /**
         * 获取备机网卡配置
         */
        var getStandbyGateWayConf = function(){
            var list = [];
            for(var i = 0; i<_standbyNetworkInfo.length;i++){
                var gatewayInfo = {};
                var hostMac = _standbyNetworkInfo[i].mac_address
                hostMac = hostMac.replace(/:/g,'');

                gatewayInfo.netcard = _standbyNetworkInfo[i].name;
                var hostGateway = $('#standby_netcard_'+hostMac).val();
                if(hostGateway==""){
                    hostGateway = "0.0.0.0";
                }
                gatewayInfo.gateway = hostGateway;
                list.push(gatewayInfo);
            }
            return list;
        }
        /**
         * 关闭回切网络配置窗口
         */
        var closeNetworkConfModal = function(){
            $('#failbackTakeoverNetworkConfModal').modal('hide');
            $('#taskFailbackupModal').modal({'width':'925px', 'height':'460px'});
        }
        //初始化节点传输网络列表
        var initNetworkList = function(nodeuuid){
            var data = {};
            data.nodeuuid = nodeuuid;
            var p = JSON.stringify(data);
            pAjaxRequest({}, "/api/v1/nodes/"+nodeuuid+"/network_card", "GET", function (res) {  //获取任务主机网卡信息
                var data = res.data;
                var transferNetwork = $('#taskTransferNetwork');
                transferNetwork.empty();
                for(var i=0; i<data.length; i++){
                    var name = data[i].ip + ":" + data[i].port;
                    if(data[i].alias_name != ""){
                        name += "(" + data[i].alias_name +")";
                    }
                    var option = '<option  value="' + data[i].network_uuid + '">' + name + '</option>';
                    transferNetwork.append(option);
                }
                oldNode = $('#selectnode').val();

            });
        }
        
        /**
         * 创建回切任务
         */
        let createFailbackConf = function(){

            let currentStage = taskInfo.current_stage;
            if(currentStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_INIT_SYNC ||
                currentStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_REALTIME_SYNC ||
                currentStage==CONF.CDP_TASK_RUNNING_STAGE.FAILBACK_IN_STARTING){
                UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_STAGE_COMIT_TIPS);
                return false;
            }
            let failBackHost = $("#failBackHost").val();
            let failbackHostType = $("#failBackHost option:selected").attr('agent-type');
            if(failBackHost=="" || failBackHost==0){
                UIToastr.showWarning(LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK, LANG.UI_VOL_CDP_JOB_DETAILS_FAILBACK_TASK_MESSAGE);
                return false;
            }
            if(failbackHostType  == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){
                let getDriverData = $.fn.driverCheck.getCheckResult($("#replicationDriverCheck"));
                let diverCheckStatus = CONF.FLAG.UNKNOW;
                if(getDriverData!=null){
                    diverCheckStatus = getDriverData[0].diver_check_status;
                }
                if(failbackHostType  == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS && diverCheckStatus == CONF.FLAG.UNKNOW){
                    UIToastr.showWarning(LANG.UI_DRIVER_CHECK, LANG.UI_VERIFY_DRIVER_CHECK_TIPS);
                    return false;
                }
                if(failbackHostType  == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS && diverCheckStatus == CONF.FLAG.UNSET){
                    UIToastr.showWarning(LANG.UI_DRIVER_CHECK, LANG.UI_DRIVER_CHECK_RESULT_NOT_PASS);
                    return false;
                }
            }

            let failbackDevRelationSet = [];
            let changeData = $("#cmCdpFailbackTargetDiskInfo").getTargetData("getEachData",_failbackTimepointUuid);
            if(!changeData){
                return false;
            }
            let targetStrategyData = changeData.target_strategy;
            for(var i=0;i<targetStrategyData.length;i++){
                let info = {};
                info.dev_uuid = targetStrategyData[i].source_dev_uuid;
                info.failback_target_dev_uuid = targetStrategyData[i].destination_dev_uuid;
                failbackDevRelationSet.push(info);
            }

            let takeoverFileCachePath = $("#takeoverFileCachePath").val(); //文件缓存路径,当前阶段填充默认值,后期自定义时需要校验路径是否存在
            if(takeoverFileCachePath==LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT){
                takeoverFileCachePath = "";
            } else {
                if(!checkFilePath(takeoverFileCachePath,_agentOsType)){
                    UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE, LANG.UI_VOL_CDP_BACKUP_LUJING_CONFIGURE_TIPS2);
                    return false;
                }
            }

            //回切数据备份到服务器器
            var mirrorBackupFlag = CONF.FLAG.UNSET;
            if($('#fbMirrorDataToServer').is(':checked')){
                 mirrorBackupFlag = CONF.FLAG.SET;
            }
            var storage_uuid = $('#selectstorage').val();

            //缓存配置
            var blockUnit = 1024*1024;  //该项配置，选项单位为MB，传输到后台的值为字节；
            //文件缓存大小
            var fileCacheAllocSpaceValue = parseInt($("#file_cache_size").val());
            var fileCacheAllocSpace = fileCacheAllocSpaceValue * blockUnit;
            //内存缓存大小
            var takeoverMemoryCacheSize = parseInt($('#memory_cache_size').val());
            var memoryCacheAllocSpace = takeoverMemoryCacheSize * blockUnit;
            var ioReplication = $('input:radio[name="task_io_replication_modle"]:checked').val(); //IO 复制模式

            //传输相关配置
            var transportCompressFlag = CONF.FLAG.UNSET;
            if($('#takeoverTranCompressSwitch').is(':checked')){
                transportCompressFlag = CONF.FLAG.SET;
            }
            var transportEncryptFlag = CONF.FLAG.UNSET;
            if($('#takeoverTranEncryptSwitch').is(':checked')) { 	
                transportEncryptFlag = CONF.FLAG.SET;
            }
            var transferThreadNumber = $('#transfer_thread_number').val();  //传输线程个数
            if(transferThreadNumber>4){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE1);
                $("#volcdpThreadNum").val(1);
                return false;
            }
            if(transferThreadNumber<1){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE2);
                $("#volcdpThreadNum").val(1);
                return false;
            }
            var transportBlockSize = $('#transfer_datapackage_size').val(); //传输数据包大小
            var transportBlockSizeValue = transportBlockSize * 1024*1024;
            // var transferEncryptMethod = $('#cutbackTransferEncryptMethod').val();  //传输加密方法,模块暂不支持国密，暂时屏蔽
            var transferEncryptMethod = _failTransferEncryptValue;
            var transferCompressGrade = $('#cutBacktransferCompressGrade').val();  //传输压缩方法
            
            //网络映射
            var businessIpMap = groupTakeoverBusinessIpMap();
            if(businessIpMap.length==0){
                businessIpMap = _modifyFailbackHisBusinessIpMap;
            }

            // 资源监测配置
            let resourceMonitorData = ResourceMonitoring.getInfo({osType: master_os_type});
            if (!resourceMonitorData) {
                return false;
            }

            //封装回切配置消息结构
            var catBackParams = {};
            catBackParams.task_uuid = $('#task_uuid').val();
            catBackParams.transport_strategy = {
                "transport_encrypt_flag" : transportEncryptFlag,			//*注：传输加密标志 1.开启 2.关闭
		        "transport_compress_flag" : transportCompressFlag,			//  *注：传输压缩标志 1.开启 2.关闭
		        "transport_encrypt_method" :_failTransferEncryptValue,	 //*注：传输加密方法，实时模块目前不支持多种加密方法，页面上隐藏配置选项，参数固定传1
		        "transport_compress_method" :transferCompressGrade,		//*注：传输压缩方法(等级)
		        "transport_thread_num" : transferThreadNumber,		    //*注：传输线程数
		        "transport_block_size" : transportBlockSizeValue		//*注：传输数据包最大字节数
            }
            catBackParams.network_uuid = $("#taskTransferNetwork").val();  //*注：传输节点网络uuid

            let failbackObject = {};

            if(failbackHostType  == CONF.BD_AGENT_TYPE.BD_AGENT_TYPE_MEMORY_OS){
                failbackObject.cross_platform_flag = crossPlatformInfo.cross_platform_flag;    //是否异构
                failbackObject.cross_platform_conf = crossPlatformInfo.cross_platform_conf;    //异构信息
            }else{
                failbackObject.cross_platform_flag = crossPlatformInfo.cross_platform_flag;    //是否异构
                failbackObject.cross_platform_conf = '';    //异构信息
            }
            failbackObject.failback_target_agent_uuid = failBackHost;
            failbackObject.mirror_backup_flag = mirrorBackupFlag;
            failbackObject.storage_uuid = storage_uuid;
            failbackObject.monitor_data_io_replication_mode = ioReplication;
            failbackObject.failback_business_ip_map = businessIpMap;
            failbackObject.failback_dev_relation_set = failbackDevRelationSet;
            
            catBackParams.failback_object = failbackObject;
            
            let  cacheConfig = {};
            cacheConfig.file_cache_alloc_path = takeoverFileCachePath;
            cacheConfig.file_cache_alloc_size = fileCacheAllocSpace;
            cacheConfig.memory_cache_alloc_size = memoryCacheAllocSpace;
            catBackParams.cache_config = cacheConfig;
            catBackParams.resource_protect_config = resourceMonitorData.resource_protect_config;
            catBackParams.high_pressure_strategy = resourceMonitorData.high_pressure_strategy;
            submitFailbackConf(catBackParams);
        }
        //提交回切配置相关参数
        let submitFailbackConf = function(info){
            Metronic.blockUI({target: '#cmCdpFailbackTaskConf',animate: true});
            pAjaxRequest(info, "/api/v1/complete_machine_volcdp/jobs/submit_failback_task", "post", function (res) {    //提交回切配置
                Metronic.unblockUI("#cmCdpFailbackTaskConf");
                if(operateResponseList(res)){
                    $('#cmCdpFailbackTaskConf').removeClass("active");
                    $('.drawer-backdrop').removeClass("active");
                }
            });
        }
        /**
         * 组合接管业务网络
         */
        var groupTakeoverBusinessIpMap = function(){
            var takeoverIpMap = [];
            for(var i = 0;i<_takeoverIpMapList.length;i++){
                var ipMapConf = _takeoverIpMapList[i].agent_map_info;
                takeoverIpMap.push(ipMapConf);
            }
            
            for(var j=0;j<takeoverIpMap.length;j++){
                var nicIpInfo =  takeoverIpMap[j].nic_ip_info;
                for(var i =0;i<nicIpInfo.length;i++){
                    var nicName = nicIpInfo[i].target_nic_name;
                    var standbyGateway = replaceStandbyNetcard(nicName);
                    if(standbyGateway!=""){
                        nicIpInfo[i].target_gateway = standbyGateway;
                    }
                }
            }
            return takeoverIpMap;
        }
        /**
         * 因后台调整消息结构困难，这里web将备机配置的网卡对应网关赋值给主机映射的备机网关
         */
        var replaceStandbyNetcard = function(hostCardName){
            var gateway = "";
            for(var i = 0;i<_standbyGatewayConf.length;i++){
                if(_standbyGatewayConf[i]["netcard"] == hostCardName){
                    gateway = _standbyGatewayConf[i]["gateway"];
                    break;
                }
            }
            return gateway;
        }

        /**
         * 切换文件缓存路径方式
         */
        var fileCachePathChange = function(){
            $('#customFileCachePath').click(function(){
                $('#defaultFileCachePath').show();
                $('#customFileCachePath').hide();
                $('#takeoverFileCachePath').removeAttr("readonly");
                $('#takeoverFileCachePath').val("");
            });
            
            $('#defaultFileCachePath').click(function(){
                $('#defaultFileCachePath').hide();
                $('#customFileCachePath').show();
                $('#takeoverFileCachePath').attr("readonly","readonly");
                $('#takeoverFileCachePath').val(LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT);
                $('#takeoverFileCachePath').prop('title', LANG.UI_VOL_CDP_BACKUP_SYSTEM_DEFAULT);
            });
            
        }
        //内存缓存开关切换事件
        var intmemorycache = function () {
            $('#memorycachechange').unbind('click').on('click', function () {
                if($('#memorycacheset').is(':checked')){
                    $('.takeovermemorycachediv').show();
                }else{
                    $('.takeovermemorycachediv').hide();
                }

            });
        }
          //校验输入的传线程个数是否符合规则
        var transferThreadNumberChange = function(){
            var transferThreadNumber = $("#transfer_thread_number").val();
            if(transferThreadNumber>4){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE1);
                $("#transfer_thread_number").val(1);
                return false;
            }
            if(transferThreadNumber<1){
                UIToastr.showWarning(LANG.UI_VOL_CDP_BACKUP_TIPS,LANG.UI_VOL_CDP_BACKUP_THREAD_MESSAGE2);
                $("#transfer_thread_number").val(1);
                return false;
            }
        }
        let initLoadFailbackEvent = function() {
            $('.takeovernetworkconf').unbind('click').click(takeoverFailbackNetworkConf);
            $('#failbackupAddsubmit').unbind('click').bind('click', createFailbackConf);
            $('#transfer_thread_number').off().blur(transferThreadNumberChange);
        }

        //初始化下对应控件下拉取值范围,默认值
        var initSpinner = function(){
            
            // $('#memory_cache_div').spinner({value:_memoryCacheSize, step: 1, min: 1, max: 1024});
            // $('#file_cache_div').spinner({value:_fileCacheSize, step: 1, min: 1, max: 1024});
            $('#transfer_thread_div').spinner({value:1, step: 1, min: 1, max: 4});
        }

        var initResourceMonitoring = function (){
            let options = {
                osType:master_os_type
            };
            if (master_os_type != 'Linux') {
                options.windowsMemory = master_memory_size;
            }
            ResourceMonitoring.init(options);
        }
        var initfunction = function() { 
            getFailbackupConf();  //获取回切配置
            initLoadFailbackEvent();  //初始化回切相关事件
            initSpinner();
            initResourceMonitoring();
        };
        return initfunction();
	}
})(jQuery)
