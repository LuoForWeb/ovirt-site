<?php

namespace app\v1\complete_machine_volcdp\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Client;
use app\v1\volcdp\v0\logic\VolcdpData;

class CmTakeover extends Base
{
    /**
     * 创建接管任务
     */
    public function createTakeoverJob($params = [])
    {
        // 检查授权是否到期
        $this->licenseCheck();
        $pfMsg = array();
        // 获取任务名称
        $pfMsg['task_name'] = htmlspecialchars_decode($params['task_name']);
        // 获取模块类型
        $pfMsg['module_type'] = xphp_get_config('module','MODULE_TYPE')['VOL_CDP'];
        // 获取任务类型
        $pfMsg['task_type'] = xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER'];
        $pfMsg['strategy_group_uuid'] = '';
        $pfMsg['storage_uuid' ] = $params['storage_uuid'];
        $pfMsg['node_uuid' ] = $params['node_uuid'];
        $node = $params['node_uuid'];
        $pfMsg['backup_server_ip' ] = $params['backup_server_ip'];
        $pfMsg['safe_config_strategy'] = $params['safe_config_strategy'];
        $pfMsg['script_list'] = $params['script_list'];
        $pfMsg['dev_type'] = $params['dev_type'];
        $pfMsg['master_agent_uuid'] = $params['master_agent_uuid'];
        $pfMsg['takeover_data_source'] = $params['takeover_data_source'];
        $pfMsg['takeover_timestamp'] = $params['takeover_timestamp'];
        $pfMsg['takeover_object'] = $params['takeover_object'];
        $pfMsg['recovery_position'] = $params['recovery_position'];
        $pfMsg['time_strategy_list'] = $params['time_strategy_list'];
        $pfMsg['transport_strategy'] = $params['transport_strategy'];
        $pfMsg['timepoint_uuid'] = $params['timepoint_uuid'];
        $msg = json_encode($pfMsg, JSON_UNESCAPED_UNICODE);
        //发送消息
        //得到接管创建操作码
        $opName = 'BD_TASK_OP_TAKEOVER_CREATE';
        $mbResult = $this->mbVolCdpMsg($node, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];

        $operate = (new PFOpcode())->getOpcodeDes($opName);
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }

    }




    /**
     * 获取接管备机
     */
    public function getTakeoverTargetHost($params){
        $master_agent_uuid = $params['master_uuid'];
        $master_os_type = $params['master_os_type'];
        $taskType = $params['task_type'];
        
        $clientHandler = new Client();
        $agentuuidArr = $clientHandler->getClientUuids();
        if (empty($agentuuidArr)) {
            // 表示没得数据
            $list = array();
            return $list;
        }
        
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,agent_type,net_model
                from bd_agent ";
        
        if($taskType  == xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP']){
            $sql .=  " where  os_type = ? ";
            $sqlParams = array($master_os_type);
        }else if($taskType  == xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION']){
             $sql .=  " where  agent_type =? or agent_type =?  ";
             $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL'],xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS']);
        }else {
            $sql .=  " where  agent_type =?  and os_type = ? ";
             $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL'],$master_os_type);
        }

        if (is_array( $agentuuidArr)) {
            $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
            $sql .= " and agent_uuid in {$agentuuidArrStr} ";
        }

        // 权限判断
        if(v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'agent_uuid');
            $sql .= " AND ($resourceUuidSql) ";
        }
        $data = $this->dbSelect($sql,$sqlParams);

        $list = array();
        $list[] = array(
            "standby_agent_uuid" => "",
            "standby_agent_name" => xphp_get_lang('UI_CM_CDP_STANDBY_SELECT_DESCRIPTION'),
            "os_type" => "",
            "agent_type" => 0,
            "in_task" => false,
            "task_name" => "",
            "vol_info" => [],
        );
        foreach ($data as $host_info){
            // 排除离线
            if($host_info['online_flag'] == xphp_get_config('app','FLAG')['UNSET']){
                continue;
            }

            //如果节点状态正常
            $agentUUID = $host_info['agent_uuid'];
            $onlineFlag = $host_info['online_flag'];
            $osType = $host_info['os_type'];
            $agentType = $host_info['agent_type'];
            $inTask = false;
            $task_name = "";
            $task_uuid = "";
            if($taskType  == xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION'] ){
                if($agentType == xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS']){  //如果是内存操作系统，需要判断数据源主机操作系统类型，如果 操作系统类型为windows，择备机
                    if($master_os_type!= "Windows" && $master_os_type != $osType){
                        continue;
                    }
                }else if($master_os_type != $osType){
                    continue;
                }
            } 
               
            $isCreateTask = $this->taskExist($agentUUID);

            if(count($isCreateTask)>0){
                $task_name = $isCreateTask['task_name'];
                $task_uuid = $isCreateTask['task_uuuid'];
                $inTask = true;
            }
            //判断是否有备份任务
            $isCreateBackupTask = $this->hostIsUsedBackupTask($agentUUID);

            if(count($isCreateBackupTask)>0){
                $task_name = $isCreateBackupTask['task_name'];
                $task_uuid = $isCreateBackupTask['task_uuid'];
                $task_type = $isCreateBackupTask['task_type'];
                $inTask = true;
            }

            //判断目标主机是否作为接管备机被使用
            $isUsedTakeoverStandby = $this->hostIsUsedTakeoverStandby($agentUUID);
            if(count($isUsedTakeoverStandby)>0){
                $task_name = $isUsedTakeoverStandby['task_name'];
                $task_uuid = $isUsedTakeoverStandby['task_uuid'];
                $task_type = $isCreateBackupTask['task_type'];
                $inTask = true;
            }

            //判断目标主机是否作为双机镜像备机使用
            $isUsedHaStandby = $this->hostIsUsedHaStandby($agentUUID);
            if(count($isUsedHaStandby)>0){
                $task_name = $isUsedHaStandby['task_name'];
                $task_uuid = $isUsedHaStandby['task_uuid'];
                $task_type = $isCreateBackupTask['task_type'];
                $inTask = true;
            }

            //判断目标主机是否作为双机镜像备机使用
            $isUsedFbTarget = $this->hostIsUsedFbTarget($agentUUID);
            if(count($isUsedFbTarget)>0){
                $task_name = $isUsedFbTarget['task_name'];
                $task_uuid = $isUsedFbTarget['task_uuid'];
                $task_type = $isCreateBackupTask['task_type'];
                $inTask = true;
            }

            //判断目标主机是否作为双机镜像备机使用
            $isUsedRecoveryTarget = $this->hostIsUsedRecoveryTarget($agentUUID);
            if(count($isUsedRecoveryTarget)>0){
                $task_name = $isUsedRecoveryTarget['task_name'];
                $task_uuid = $isUsedRecoveryTarget['task_uuid'];
                $task_type = $isCreateBackupTask['task_type'];
                $inTask = true;
            }

            // $hostVolInfo = $this->getHostVolInfoByUUID($agentUUID);
            $hostDesc = $this->agentStr($host_info['agent_name'],$host_info['hostname'],$host_info['ip']);

            if(xphp_get_config('app','FLAG')['SET'] == $onlineFlag){
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_ONLINE');
            }else{
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_OFFLINE');
                $hostDesc = $hostDesc."--".$statusDes;
            }

            $list[] = array(
                'standby_agent_uuid' => $agentUUID,
                'data_source_agentuuid' => $master_agent_uuid,
                'standby_agent_name' => $hostDesc,
                'os_type' => $host_info['os_type'],
                'in_task' => $inTask,
                'task_name' => $task_name,
                // 'mount_info' => $hostVolInfo['mount_info'],
                'agent_type' => $host_info['agent_type'],
                'net_model' => intval($host_info['net_model']),
                'online_flag' => $onlineFlag,
                'task_uuid' => $task_uuid,
                'task_type' => $task_type,
                // 'vol_data' => $this->getHostVolInfoByUUID($host_info['agent_uuid'])
            );
        }
        return $list;
    }

    /**
     * 获取选中客户端应用信息
     */
    public function getClientTakeoverAppInfo($params){
        $agent_uuid = $params['agent_uuid'];
        $take_over_time = $params['take_over_time'];
        $standby_host_uuid = $params['standby_host_uuid'];
        $task_uuid = $params['task_uuid'];

        $sql = "select master_agent_detail,id,master_agent_uuid 
                from cdp_vol_backup_agent
                where master_agent_uuid = ?";
        $data = $this->dbSelect($sql,array($agent_uuid));
        $host_info = "";
        $agent_id_list = [];
        $os_type = "";
        foreach ($data as $row){
            $master_agent_detail = json_decode($row['master_agent_detail']);
            $hostname = $master_agent_detail->hostname;
            $agent_ip = $master_agent_detail->ip;
            $os_type = $master_agent_detail->os_type;
            $host_info = $hostname."(".$agent_ip .")";
            array_push($agent_id_list,$row['id']);
        }
        $type_icon = "./img/platform/linux.png";
        if($os_type=='Windows'){
            $type_icon = "./img/platform/windows.png";
        }
        $tree = array();
        $tree[] = array(
            "id" => $agent_uuid,
            "pId" => "",
            "name" =>  $host_info,
            "title" => xphp_get_lang('UI_VOL_CDP_HOST').$host_info,
            "isParent" => true,
            "uuid" => $agent_uuid,
            "nocheck" => true,
            "isApp" =>false,
            "type" => 1,
            "icon" => $type_icon,
            "clickshow" => false,
            "checked" => false,
            "chkDisabled" => false,
            "open" =>  true,
            "agent_id_list" => $agent_id_list,
        );
        $appList = $this->getAppType($agent_uuid,$agent_id_list,$take_over_time,$standby_host_uuid,$task_uuid);//应用类型
        if(!empty($appList)){
            $tree = array_merge($tree, $appList);
        }
        return $tree;
    }

    /**
     * 获取客户端备份集信息
     */
    public function getClientBackupSetVolInfo($params)
    {
        $user_uuid = xphp_get_user_info()['userUuid'];
        $agent_uuid = $params['agent_uuid'];
        $createTaskType = $params['task_type'];
        $taskuuid = $params['task_uuid'];

        $list = array();

        $firsTime = $this->getClientFirstTime($params);
        $newTime = $this->getClientNewTime($params);
        $sql = "select bbt.timepoint_uuid,bbt.src_end_timepoint,agent.storage_location,st.storage_uuid,
                st.node_uuid,bbt.encrypted_flag,bbt.detail,agent.master_agent_detail 
                from cdp_vol_backup_agent as agent,bd_backup_timepoint as bbt,bd_storage_resource as st 
                where st.storage_uuid = bbt.storage_uuid and  agent.timepoint_uuid = bbt.timepoint_uuid 
                and agent.master_agent_uuid = ? and bbt.user_uuid = ? and agent.storage_location !=? 
                and bbt.task_uuid = ? ";

        if($createTaskType == xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){
            $sql = $sql."   ORDER BY agent.id DESC LIMIT 0,1";
            $data = $this->dbSelect($sql,array($agent_uuid,$user_uuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid));
        }else{
            $sql = $sql." and agent.storage_location !=? ORDER BY agent.id DESC LIMIT 0,1";
            $data = $this->dbSelect($sql,array($agent_uuid,$user_uuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']));
        }
        $srcEndTimeStamp = $data[0]['src_end_timepoint'];
        $timestamp = date('Y-m-d H:i:s',$srcEndTimeStamp);
        $timepointuuid = $data[0]['timepoint_uuid'];

        if($data[0]['encrypted_flag'] == xphp_get_config('app', 'FLAG')['SET']) { // 时间点加锁
            $encryptedFlag = true;
        }else {
            $encryptedFlag = false;
        }
        $detail = json_decode($data[0]['detail'],true);
        $passwordAutoFlag = intval($detail['password_auto_flag'])==xphp_get_config('app', 'FLAG')['SET'] ? true: false;
        if($newTime=="--"){
            $newTime = $timestamp;
        }else if($newTime<$timestamp){
            $newTime = $timestamp;
        }

        $list = $this->getClientVolSet($agent_uuid,$srcEndTimeStamp,$newTime,$createTaskType,$timepointuuid,$taskuuid);

        return $list;
    }

    /**
     * 获取数据源设备在备份集中对应的网卡信息
     * @param unknown $params
     */
    public function loadAgentNetworkInfo($params)
    {
        $agentuuid = $params['agent_uuid'];
        $taskuuid = $params['task_uuid'];
        $sql = "select cvba.id, cvba.master_agent_detail 
                from cdp_vol_backup_agent cvba, bd_backup_timepoint bbt 
                where master_agent_uuid = ? and bbt.task_uuid = ? 
                and bbt.timepoint_uuid =  cvba.timepoint_uuid 
                order by cvba.id limit 0,1";
        $data = $this->dbSelect($sql, array($agentuuid,$taskuuid));
        $detail = json_decode($data[0]['master_agent_detail']);
        $nicList = $detail->nic_list;
        return $nicList;
    }

    /**
     * 获取客户端对应的网卡信息
     * @param unknown $params
     */
    public function loadStandbyNetworkInfo($params)
    {
        $agentuuid = $params['agent_uuid'];
        $sql = "select detail from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentuuid));
        $detail = json_decode($data[0]['detail']);
        $nicList = $detail->nic_list;
        return $nicList;
    }



    # ———————————————————————————— 以下是私有方法 ————————————————————————————
    /**
     * 检查选择的目标是否有被其它任务选中
     * ---当主机配置了其他任务中的任意角色：备份主机，恢复目标机，双机镜像备机，自动接管备机，手动接管备机和回切目标机器时不能再作为手动接管备机使用
     * @param unknown $agentUuids
     */
    private function taskExist ($agentUUID)
    {
        $sql = "select task.task_status,task.task_name,task.task_type,task.task_uuid 
            from bd_task task,cdp_vol_task_takeover_info takeover 
            where takeover.task_uuid = task.task_uuid 
            and task.task_type = ? 
            and takeover.takeover_standby_agent_uuid = ? 
            and task.delete_flag = ? ";
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER'];
        $delFlag = xphp_get_config('app','FLAG')['UNSET'];
        $data = $this->dbSelect($sql,array($taskType,$agentUUID,$delFlag));
        $taskList = array();
        if(!empty($data)){
            $taskList = array(
                'task_name' => $data[0]['task_name'],
                'task_status' => $data[0]['task_status'],
                'task_type' => $data[0]['task_type'],
                'task_uuid' => $data[0]['task_uuid'],

            );
        }
        return $taskList;
    }

    /**
     * 目标机器是否被创建备份任务
     * @param $agentuuid
     */
    private function hostIsUsedBackupTask($agentuuid)
    {
        $sql = "select task.task_status,task.task_name,task_type 
                from bd_task task,cdp_vol_task cvt 
                where cvt.task_uuid = task.task_uuid 
                and task.task_type = ? 
                and cvt.master_agent_uuid = ? 
                and task.delete_flag = ?";
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'];
        $delFlag = xphp_get_config('app','FLAG')['UNSET'];
        $data = $this->dbSelect($sql,array($taskType,$agentuuid,$delFlag));
        $taskList = array();
        if(!empty($data)){
            $taskList = array(
                'task_name' => $data[0]['task_name'],
                'task_status' => $data[0]['task_status'],
                'task_type' => $data[0]['task_type'],
            );
        }
        return $taskList;
    }

    /**
     * 监测客户端是否作为备机被使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedTakeoverStandby($agentuuid)
    {
        $sqlTo = "select task.task_name,task.task_name,task.task_type,task.task_status 
                  from cdp_vol_task_takeover_info vt,bd_task task 
                  where takeover_standby_agent_uuid = ? 
                  and vt.task_uuid = task.task_uuid";
        $dataTo = $this->dbSelect($sqlTo,array($agentuuid));
        $taskList = array();
        if(!empty($dataTo)){
            $taskList = array(
                'task_name' => $dataTo[0]['task_name'],
                'task_status' => $dataTo[0]['task_status'],
                'task_type' => $dataTo[0]['task_type'],
            );
        }
        return $taskList;
    }

    /**
     * 监测客户端是否作为镜像备机被使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedHaStandby($agentuuid)
    {
        $sqlTo = "select task.task_name,task.task_type,task.task_status  
                  from cdp_vol_task vt,bd_task task 
                  where vt.standby_agent_uuid = ?
                  and vt.task_uuid = task.task_uuid";
        $dataTo = $this->dbSelect($sqlTo,array($agentuuid));
        $taskList = array();
        if(!empty($dataTo)){
            $isEnabled =  true;
            $taskName = $dataTo[0]['task_name'];
            $taskList = array(
                'task_name' => $taskName,
                'task_status' => $dataTo[0]['task_name'],
                'task_type' => $dataTo[0]['task_type'],
            );
        }
        return $taskList;
    }

    /**
     * 监测客户端是否作为回切目标机器使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedFbTarget($agentuuid)
    {
        $sqlFa = "select task.task_name,task.task_type,task.task_status 
                  from cdp_vol_task_takeover_failback_info vt,bd_task task 
                  where vt.failback_target_agent_uuid = ? 
                  and vt.task_uuid = task.task_uuid";
        $dataFa = $this->dbSelect($sqlFa,array($agentuuid));
        $taskList = array();
        if(!empty($dataFa)){
            $isEnabled =  true;
            $taskName = $dataFa[0]['task_name'];
            $taskList = array(
                'task_name' => $taskName,
                'task_status' => $dataFa[0]['task_name'],
                'task_type' => $dataFa[0]['task_type'],
            );
        }
        return $taskList;
    }

    /**
     * 检测客户端是否作为恢复目标机器使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedRecoveryTarget($agentuuid)
    {
        $sqlRe = "select task.task_name,task.task_type,task.task_status 
                  from cdp_vol_task vt,bd_task task 
                  where vt.recovery_target_agent_uuid =?
                  and vt.task_uuid = task.task_uuid";
        $dataRe = $this->dbSelect($sqlRe,array($agentuuid));
        $taskList = array();
        if(!empty($dataRe)){
            $isEnabled =  true;
            $taskList = array(
                'task_name' => $dataRe[0]['task_name'],
                'task_status' => $dataRe[0]['task_name'],
                'task_type' => $dataRe[0]['task_type'],
            );
        }
        return $taskList;
    }

    /**
     * 获取筛选主机卷信息
     * @param：agent_uuid
     */
    private function getHostVolInfoByUUID($hostUuid)
    {
        $agentSql = "select agent_type 
                     from bd_agent 
                     where agent_uuid = ? ";
        $data = $this->dbSelect($agentSql, array($hostUuid));
        $agentType = 1;
        if(!empty($data)){
            $agentType = $data[0]['agent_type'];
        }

        $sql = "select vol.vol_name,vol.vol_uuid,vol.capacity,vol.free_space,vol.is_boot,vol.display_name,vol.mount_point,vol.detail
                from bd_agent_disk as disk,bd_agent_vol as vol
                where vol.disk_uuid = disk.disk_uuid and disk.agent_uuid = ?";
        $data = $this->dbSelect($sql,array($hostUuid));
        $list = array();
        $HostMountPointArry = array();
        foreach ($data as $v){
            $detail = json_decode($v['detail']);
            $mountPoint = $v['mount_point'];
            $volName = $v['vol_name'];
            $volTypeValue = $detail->vol_type;
            $canSelect = true;

            $volTypeValue = $detail->vol_type;
            //保留分区、恢复分区、pv分区、扩展分区
            if($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_WIN_RESERVE_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_WIN_RECOVERY_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_PV_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_EXTEND_VOLUME']){
                continue;
            }
            //swap 分区
//            if($mountPoint =="[SWAP]"){
//                continue;
//            }
            if($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SWAP_VOLUME']){
                continue;
            }
            //内存操作系统的内置分区，如 live 等,以lvice-去过滤
            $pos = strstr($volName,"live-");
            if($pos !==false){
                continue;
            }
            if(($volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_BOOT_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SYSTEM_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp','VOL_TYPE')['BD_EFI_VOLUME']
                && $agentType!=xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS'])) {
                $canSelect = false;
            }

            // 普通分区，未挂载分区 自由勾选
            if(!empty($v['mount_point'])){
                array_push($HostMountPointArry,$v['mount_point']);
            }
            $list[] = array(
                "uuid" => $v['vol_uuid'],
                "capacity_value" => $v['capacity'],
                "capacity" =>v1_calsize($v['capacity'], true),
                "free_space" => $v['free_space'],
                "is_boot" => $v['is_boot'],
                "display_name" => $v['display_name'],
                "mount_point" => $v['mount_point'],
                "can_select" => $canSelect,
            );
        }
        $hostVolInfo = array(
            'mount_info' => $HostMountPointArry,
            'vol_info' => $list
        );
        return $hostVolInfo;
    }

    /**
     * 根据条件组合客户端的名称
     */
    private function agentStr($agentName,$hostName,$ip)
    {
        if (!empty($agentName) && $agentName != $ip) {
            $hostInfo =  $agentName ? $agentName."(". $ip .")" : $hostName."(". $ip .")";
        }else {
            $hostInfo = $hostName."(". $ip .")";
        }
        return $hostInfo;
    }

    /**
     * 获取客户端备份集应用类型
     */
    private function getAppType ($agentUUID,$agent_id_list,$take_over_time,$standby_host_uuid,$task_uuid){
        $id_str =  join(",", $agent_id_list);
        $sql = "select distinct id,app_type,app_name,app_uuid 
                from cdp_vol_backup_app 
                where backup_agent_id in ({$id_str}) group by app_type";
        $data = $this->dbSelect($sql);
        $appExist = false;
        $info = array();
        if(count($data)>0){
            foreach ($data as $appInfo){
                $alaisName = $appInfo['app_name'];
                $appTypeValue = $appInfo['app_type'];
                $appUuid = $appInfo['app_uuid'];
                $appTypeDes = xphp_get_lang('DB_TYPE_DES')[intval($appTypeValue)] . PHP_EOL;
                if($appTypeValue == xphp_get_config('db', 'DB_TYPE')['SQLSERVER']){
                    $icon = "./img/db/sqlserver.png";
                } else if($appTypeValue == xphp_get_config('db', 'DB_TYPE')['ORACLE']){
                    $icon = "./img/db/oracle.png";
                } else if($appTypeValue == xphp_get_config('db', 'DB_TYPE')['MYSQL']){
                    $icon = "./img/db/mysql.png";
                }
                $id = $agentUUID.$appTypeValue;
                $info[] = array(
                    "id" => $id,
                    "pId" => $agentUUID,
                    "name" => $appTypeDes,
                    "title" => xphp_get_lang('UI_VOL_CDP_BACKUP_DB_APPLICATION'),
                    "appTypeValue" => $appTypeValue,
                    'agent_id_list' =>$agent_id_list,
                    "icon" => $icon,
                    "isApp" =>false,
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" =>  true,
                );
                $childTree = $this->getAppData($agentUUID,$id,$appTypeValue,$agent_id_list,$take_over_time,$standby_host_uuid,$task_uuid);
                if(!empty($childTree)){
                    $info = array_merge($info, $childTree);
                }else{
                    $info = array();
                }
            }
        }
        return $info;
    }

    /**
     * 获取应用类型下的应用信息，此处需要注意节点对应关系
     */
    private function getAppData($agentUUID,$pId,$appTypeValue,$agent_id_list,$take_over_time,$standby_host_uuid,$task_uuid){
        $id_str =  join(",", $agent_id_list);
        $sql = "select distinct cvba.id,cvba.app_type,cvba.app_name,cvba.app_uuid
                from cdp_vol_backup_app cvba,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                where cvba.backup_agent_id in ({$id_str}) 
                and cvba.app_type =? and agent.id = cvba.backup_agent_id and agent.timepoint_uuid = bbt.timepoint_uuid
		        and bbt.task_uuid =? group by cvba.app_uuid";
        $data = $this->dbSelect($sql,array($appTypeValue,$task_uuid));
        $info = array();
        foreach ($data as $d){
            $id = $pId.$d['app_uuid'];
            $app_name = $d['app_name'];
            $app_type = $d['app_type'];

            $standbyAppList = $this->getStandbyAppinfo($standby_host_uuid,$app_type,$app_name);
            $standbyAppUuid = $standbyAppList['app_uuid'];
            $standbyAppName = $standbyAppList['standby_app_name'];
            $info[] = array(
                "id" => $id,
                "pId" => $pId,
                "name" => $app_name,
                "title" => $d['app_name'],
                "uuid" => $d['app_uuid'],
                "app_type" => $app_type,
                "app_type_value" => $appTypeValue,
                "icon" => "./img/db/instance.png",
                'appUUID' =>$d['app_uuid'],
                'standby_app_uuid' =>$standbyAppUuid,
                'standby_app_name' => $standbyAppName,
                "clickshow" => false,
                "checked" => false,
                "isApp" => true,
                "chkDisabled" => false,
            );
            $childTree = $this->getAppModuleInfo($id,$d['app_uuid'],$appTypeValue,$agent_id_list,$take_over_time);
            if(!empty($childTree)){
                $info = array_merge($info, $childTree);
            }else{
                continue;
            }
        }
        return $info;
    }

    /**
     * 通过选中备机的uuid及主机对应应用的类型及应用名,自动获取备机应用信息
     * @param $standby_host_uuid:备机UUID,$app_type:选定主机应用类型,$app_name:应用名
     */
    private function getStandbyAppinfo($standby_host_uuid,$app_type,$app_name){
        $sql = 'select app.app_uuid,app.app_name 
                from bd_agent as agent,bd_agent_app as app 
                where agent.agent_uuid = ? and agent.agent_uuid = app.agent_uuid and app.app_type = ? and app.app_name = ?';
        $data = $this->dbSelect($sql,array($standby_host_uuid,$app_type,$app_name));
        if(!empty($data)){
            $app_uuid = $data[0]['app_uuid'];
            $standby_app_name = $data[0]['app_name'];
        }else{
            $app_uuid = "";
            $standby_app_name = "";
        }
        $standbyAppList = array(
            "app_uuid" => $app_uuid,
            "standby_app_name" => $standby_app_name,
        );
        return $standbyAppList;
    }

    /**
     * 获取app 组件信息
     */
    private function getAppModuleInfo($pId,$appUUID,$appTypeValue,$agent_id_list,$takeoverTime){
        $takeoverTimetampArry = $this->getUnixTimetamp($takeoverTime);
        $takeoverTimetamp = $takeoverTimetampArry[0];
        $sql = "select distinct module.id,module.module_name,module.module_detail  
                from cdp_vol_backup_app_module module,cdp_vol_backup_app app 
                where module.backup_app_id = app.id and app.app_uuid = ? and 
                (module.start_timestamp <= ? and module.end_timestamp >=?) 
                group by module_name";
        $data = $this->dbSelect($sql,array($appUUID,$takeoverTime,$takeoverTime));
        $moduleInfo = array();
        if(!empty($data)){
            foreach ($data as $d){
                $id = $pId.$d['id'];
                $moduleInfo[] = array(
                    "id" => $id,
                    "pId" => $pId,
                    "name" => $d['module_name'],
                    "uuid" => $d['app_uuid'],
                    "module_name" => $d['module_name'],
                    "icon" => "./img/db/database.png",
                    "app_type_value" => $appTypeValue,
                    "clickshow" => false,
                    "checked" => false,
                    "nocheck" => true,
                    "isApp" => false,
                    "chkDisabled" => false,
                );
            }
        }
        return $moduleInfo;
    }

    /**
     * 时间时间字符串转换unix时间戳
     * @param unknown $params
     */
    private function getUnixTimetamp($params){
        $sql = "select UNIX_TIMESTAMP(?)";
        $data = $this->dbSelect($sql,array($params));
        return $data[0];
    }

    /**
     * 获取客户端最新时间戳
     * @param $params
     * @return void
     */
    private function getClientNewTime($params){
        $user_uuid = xphp_get_user_info()['userUuid'];
        $node_uuid = $params['node_uuid'];
        $agent_uuid = $params['agent_uuid'];
        $createTaskType = $params['task_type'];
        $taskuuid = $params['task_uuid'];
        $newTime = '--';
        $sql = "select vol.end_timestamp 
                from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol, bd_backup_timepoint bbt
                where vol.backup_agent_id = agent.id and agent.master_agent_uuid = ? and agent.storage_location !=? 
                and agent.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ? ";
        if($createTaskType = xphp_get_config('task')['TASKTYPE']['VOL_CDP_TAKEOVER']){
            $sql = $sql." and agent.storage_location =? ORDER BY vol.end_timestamp desc LIMIT 1";
        }else{
            $sql = $sql." and agent.storage_location !=? ORDER BY vol.end_timestamp desc LIMIT 1";
        }
        $data = $this->dbSelect($sql, array($agent_uuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']));
        if(!empty($data)){
            $newTime = $data[0]['end_timestamp'];
        }
        return $newTime;
    }

    private function getAgentData($agent_uuid,$createTaskType,$user_uuid,$taskuuid)
    {
        $sql = "select bbt.timepoint_uuid,bbt.src_end_timepoint,agent.storage_location,st.storage_uuid,
                st.node_uuid,bbt.encrypted_flag,bbt.detail,agent.master_agent_detail 
                from cdp_vol_backup_agent as agent,bd_backup_timepoint as bbt,bd_storage_resource as st 
                where st.storage_uuid = bbt.storage_uuid and  agent.timepoint_uuid = bbt.timepoint_uuid 
                and agent.master_agent_uuid = ? and bbt.user_uuid = ? and agent.storage_location !=? 
                and bbt.task_uuid = ? ";

        if($createTaskType == xphp_get_config('task')['TASKTYPE']['VOL_CDP_TAKEOVER']){
            $sql = $sql."   ORDER BY agent.id DESC LIMIT 0,1";
            $data = $this->dbSelect($sql,array($agent_uuid,$user_uuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid));
        }else{
            $sql = $sql." and agent.storage_location !=? ORDER BY agent.id DESC LIMIT 0,1";
            $data = $this->dbSelect($sql,array($agent_uuid,$user_uuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']));
        }
        return $data;
    }

    /**
     * 获取客户端对应的卷信息
     */
    private function getClientVolSet($agent_uuid,$srcEndTimeStamp,$timestamp,$createTaskType,$timepointuuid,$taskuuid)
    {
        $sql = "select distinct vol.vol_display_name,vol.vol_uuid,vol.standby_vol_uuid,vol.capacity,
                agent.master_agent_detail,vol.is_boot,vol.storage_status,vol.detail 
                from cdp_vol_backup_agent as agent,cdp_vol_backup_vol_set as vol, bd_backup_timepoint as bbt
                where vol.backup_agent_id = agent.id and agent.master_agent_uuid =? and agent.timepoint_uuid = bbt.timepoint_uuid 
                and bbt.src_end_timepoint = ? and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql,array($agent_uuid,$srcEndTimeStamp,$taskuuid));
        $i = 0;
        $info = array();
        foreach ($data as $vol_info){
            $standby_vol_uuid = $vol_info['standby_vol_uuid'];
            $master_agent_detail = json_decode($vol_info['master_agent_detail']);
            $os_type = $master_agent_detail->os_type;
            $storageStatus = $vol_info['storage_status'];
            $volDetail = json_decode($vol_info['detail']);
            $volType = $volDetail->vol_type;
            $systemVolume = false;
            if($volType && xphp_get_desc('Volcdp','VOL_TYPE')['BD_SYSTEM_VOLUME']){
                $systemVolume = true;
            }

            $verifyResult = (new VolcdpData) -> verifyBackupSetStatus($storageStatus,$createTaskType,$agent_uuid,$timestamp,$timepointuuid);
            $i++;
            $info['rows'][] = array(
                'vol_name' => $vol_info['vol_display_name'],
                'capacity' => v1_calsize($vol_info['capacity'], true),
                'capacity_value' =>$vol_info['capacity'],
                'new_timestamp' =>$timestamp,
                'vol_uuid' => $vol_info['vol_uuid'],
                'verify_result' => $verifyResult,
                'standby_vol' => $this->getVolInfoByVoluuid($standby_vol_uuid,$agent_uuid),
                'os_type' => $os_type,
                'is_boot' => $vol_info['is_boot'],
                'system_volume' =>$systemVolume,
            );
        }
        $info['total'] = $i;
        return $info;
    }

    /**
     * 获取对应卷名
     * @param unknown $standby_vol_uuid
     * @param unknown $agent_uuid
     * @desc 和后台确认，当前版本不考虑备用服务器被删除情况
     */
    private function getVolInfoByVoluuid($standby_vol_uuid,$agent_uuid)
    {
        $sql = "select vol.display_name from bd_agent_disk disk,bd_agent_vol vol
                where disk.agent_uuid = ? and disk.disk_uuid = vol.disk_uuid and vol.vol_uuid = ?";
        $data = $this->dbSelect($sql,array($agent_uuid,$standby_vol_uuid));
        $displayName = "";
        if(!empty($data)){
            $displayName = $data[0]['display_name'];
        }
        return $displayName;
    }

    /**
     * 获取客户端最早备份时间点
     * @param $params
     * @return string
     */
    private function getClientFirstTime($params){
        $user_uuid = xphp_get_user_info()['userUuid'];
        $agent_uuid = $params['agent_uuid'];
        $data_source = $params['data_source'];
        $taskuuid = $params['task_uuid'];
        $firsTime = '--';
        if($data_source==xphp_get_desc('Volcdp','RECOVERY_DATA_SOURCE')['STANDBY'] && $createTaskType = xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){
            $firstSql = "select bbt.timepoint,bbt.timepoint_uuid
                         from cdp_vol_backup_agent agent,bd_backup_timepoint bbt
                         where agent.timepoint_uuid = bbt.timepoint_uuid and agent.master_agent_uuid =? and bbt.user_uuid = ?
                         and agent.storage_location !=? and bbt.task_uuid =? ORDER BY bbt.timepoint limit 0,1";
            $firstData = $this->dbSelect($firstSql, array($agent_uuid,$user_uuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid));
        }else{
            $firstSql = "select bbt.timepoint,bbt.timepoint_uuid
                         from cdp_vol_backup_agent agent,bd_backup_timepoint bbt
                         where agent.timepoint_uuid = bbt.timepoint_uuid and agent.master_agent_uuid =? and bbt.user_uuid = ?
                         and agent.storage_location !=? and agent.storage_location !=? and bbt.task_uuid =? order by bbt.timepoint limit 0,1";
            $firstData = $this->dbSelect($firstSql, array($agent_uuid,$user_uuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],$taskuuid));
        }
        if(!empty($firstData)){
            $firsTime = $firstData[0]['timepoint'];
        }
        return $firsTime;
    }

}

