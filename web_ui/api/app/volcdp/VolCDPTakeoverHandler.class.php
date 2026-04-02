<?php
/*******************************************
 ** cdp备份任务创建
 **
 ** @author       jiangyongjie@vinchin.com
 ** @date         2021-12-17 16:43:15
 ** @version      6.0.0
 ** @copyright    Copyright 2021 vinchin.com
 ********************************************/
class VolCDPTakeoverHandler extends BLLHandler{
    /**
     * 获取生产有备份集的客户端
     * @param unknown $params
     */
    
    public function getDataSourceHostInfo($params){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $flag = Xphp::$_config['FLAG'];
        $availableFlag = $flag['SET'];  //时间点是否可用标志位
        
        $user_uuid = Xphp::$_user['useruuid'];
        $node_uuid = $params['node_uuid'];
        $start = intval($params['start']);
        $length = intval($params['length']);
        $sql = 'select DISTINCT agent.master_agent_uuid,agent.master_agent_detail,agent.storage_location,bbt.user_uuid,
                bbt.task_name,bbt.task_uuid,bbt.id  
                from bd_backup_timepoint as bbt,bd_storage_resource as store,cdp_vol_backup_agent as agent 
                where agent.timepoint_uuid = bbt.timepoint_uuid and bbt.storage_uuid = store.storage_uuid and agent.dev_type = ?';
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];
        if (!empty($authUser)) {
            $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and bbt.user_uuid in " . $useruuidArr;
        }else{
            $sql .= " and bbt.user_uuid = '{$user_uuid}' ";
        }
        $sql .= " and bbt.available_flag = ? and agent.storage_location !=? and bbt.import_flag = ? group by bbt.task_uuid,agent.master_agent_uuid ORDER BY bbt.id";

        $storageLocationUnknown =$VolCdpDes['RECOVERY_DATA_SOURCE']['UNKNOWN'];  //时间点是否可用标志位
        $sqlParams = array(Xphp::$_config['FLAG']['SET'],$availableFlag,$storageLocationUnknown,Xphp::$_config['FLAG']['UNSET']);
        $data = $this->dbSelect($sql, $sqlParams);
        $host = array();
        $host["data"] = array();
        $hostInfoList = [];
        $userUuidData = array();
        $backupSetHandler = Xphp::instance('VolCDPBackupSetHandler');
        if(!empty($data)){
            foreach ($data as $d){
                $masterAgentuuid = $d['master_agent_uuid'];
                $master_agent_detail = json_decode($d['master_agent_detail']);
                $hostname = $master_agent_detail->hostname;
                $agentIp = $master_agent_detail->ip;
                $agent_name = $master_agent_detail->agent_name;

                $taskName = $d['task_name'];
                $taskuuid = $d['task_uuid'];
                $storage_location_value = $d['storage_location'];
                if($storage_location_value == $VolCdpDes['RECOVERY_DATA_SOURCE']['STANDBY']){
                    continue;
                }

                if (!empty($agent_name) && $agent_name != $agentIp) {
                    $hostInfo =  $agent_name ? $agent_name : $hostname;
                }else {
                    $hostInfo = $hostname;
                }
                
                $os_type = $master_agent_detail->os_type;
                $os_version = $master_agent_detail->os_version;

                $sysIcon = '<img src ="./img/platform/linux.png">';
                if(strpos($os_version,'Windows') !== false){
                    $sysIcon = '<img src ="./img/platform/windows.png">';
                }

                $agentInfoExists = in_array($taskuuid.$masterAgentuuid, $hostInfoList); //判断主机IP或者uuid是否已存在数组中，兼容同一主机使用不同ip或者不同主机使用相同ip的情况
                
                if($agentInfoExists ){
                    continue;
                }
                $storage_location = $this->getMasterAgentStoragelocation($masterAgentuuid,$storageLocationUnknown,$agentIp,$taskuuid);
                if(empty($storage_location)){
                   // continue;
                }

                $currentTaskUUID = $backupSetHandler->getCurrentAllTaskUUID();  //得到当前任务所有uuid
                $taskAvailable = in_array($d['task_uuid'], $currentTaskUUID);
                $taskNameStr = $taskAvailable ? $taskName : $taskName . "(" . Xphp::$_lang['WEB_PLATFORM_DC_VM_TASK_DELETED'] . ")";
                
                if($taskAvailable){  //任务未删除
                    $agentBackupTask = $this->getAgentBackupTask($masterAgentuuid);
                    $agentIsRunningBackupTask = $agentBackupTask['task_status'];
                    $backupMode = $agentBackupTask['backup_mode'];
                    
                    if($backupMode == $VolCdpDes['VOL_CDP_BACKUP_MODE']['MASTER_AGENT_COPY']){  //主备复制
                        continue;
                    }
                }else{
                    $agentIsRunningBackupTask = 0;
                    $backupMode = 0;
                }
                
                

                $host["data"][] = array(
                    '<input type="checkbox" class="editor-active" name="'.$storage_location.'" data-hostip = "'.$agentIp.'" data-taskuuid = "'.$d['task_uuid'].'" value="'.$masterAgentuuid.'">',
                    $hostInfo,
                    $agentIp,
                    $taskNameStr,
                    $sysIcon.$os_version,
                    $masterAgentuuid,
                    $storage_location,
                    $os_type,
                    $d['task_uuid'],
                    $agentIsRunningBackupTask,
                    $backupMode,
                );
                $hostInfoStr = $taskuuid.$masterAgentuuid;
                array_push($hostInfoList,$hostInfoStr);
                array_push($userUuidData, $d['user_uuid']);
            }
        }
        $listData = $host["data"];
        $utils = Xphp::instance("Utils");
        $authUser = $_SESSION['authUser']['vol_cdp_protect_operate'] ?? [];
        $checkOperate = $utils->xphp_check_operate(Xphp::$_user['useruuid'], $userUuidData, $authUser);
        if (!$checkOperate) {
            $listData = array();
        }

        $countNum = count($listData);
        $host['data'] = array_slice($listData, $start, $length);
        $host["draw"] = $params['draw'];
        $host["recordsTotal"] = $countNum;
        $host["recordsFiltered"] = $countNum;
        return  json_encode($host);
    }
    /**
     * 获取客户端是否有对应的备份任务正在运行
     */
    private function getAgentBackupTask($agentuuid) {
        $sql = "select task.task_status,vol_task.backup_mode  
                from bd_task task,cdp_vol_task vol_task 
                where vol_task.task_uuid = task.task_uuid and vol_task.master_agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentuuid));
        $info = array();
        if(!empty($data)){
             $info = array(
                "task_status" => $data[0]['task_status'],
                "backup_mode" => $data[0]['backup_mode'],
             );
        }
        return $info;
    }
    /**
     * 获取备份主机对应的存储类型集
     * @param unknown $host_uuid
     */
    private function getMasterAgentStoragelocation($hostuuid,$storageLocation,$agentIp,$taskuuid){
        $sql = "select DISTINCT cvba.storage_location,cvba.master_agent_detail,cvba.standby_agent_detail  
                from cdp_vol_backup_agent cvba,bd_backup_timepoint bbt 
                where cvba.master_agent_uuid = ? and cvba.storage_location !=? and bbt.timepoint_uuid = cvba.timepoint_uuid and bbt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($hostuuid,$storageLocation,$taskuuid));
        $locationArray = array();
        foreach($data as $item) {
            $agentDetail = $item['master_agent_detail'];
            $standbyDetail = $item['standby_agent_detail'];
            $standbyAgentType = json_decode($standbyDetail)->agent_type;
            $masterAgentDetail = json_decode($agentDetail);
            $masterAgentIp = $masterAgentDetail->ip;

            if($agentIp != $masterAgentIp){
                continue;
            }
            $standbyConf = $this->getDataSourceHostTaskConfInfo($hostuuid);
            $storageLocation = $item['storage_location'];
            if(!$standbyConf && $storageLocation==2){
                continue;
            }else if(!$standbyConf && $storageLocation==3){
                $storageLocation =1;
            }

            if($standbyAgentType == Xphp::$_config['AGENT_TYPE']['MEMORY_OS'] && $storageLocation ==3){
                $storageLocation = 1;
            }
            array_push($locationArray,$storageLocation);
        }
        return $locationArray;
    }
    /**
     * 获取数据源客户端主机任务信息及运行状态
     * @param unknown $hostuuid
     */
    private function getDataSourceHostTaskConfInfo($hostuuid){
         $sql = "select vol_task.standby_agent_uuid,task.task_status from cdp_vol_task vol_task,bd_task task 
                where task.task_uuid = vol_task.task_uuid and vol_task.master_agent_uuid = ? and  task.task_type =?";
         $data = $this->dbSelect($sql, array($hostuuid,Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']));
         $standbyAgentuuid = $data[0]['standby_agent_uuid'];
         $taskStatus = $data[0]['task_status'];
         $dataSourceStandbyConf = true;
         //调整为备机有备份任务择该设备不可用
         if(!empty($standbyAgentuuid) ){
            $dataSourceStandbyConf =false; //是否允许配置备机数据源
         }
//         if(!empty($standbyAgentuuid) && $taskStatus==Xphp::$_config['TASKSTATUS']['RUNNING']){
//             $dataSourceStandbyConf =false; //是否允许配置备机数据源
//         }
         return $dataSourceStandbyConf;
    } 
    
    /**
     * 判断当前输入值是否存在二维数组中
     * @param unknown $value
     * @param unknown $array
     * @return boolean
     */
    private function  deepInArray($value, $array) {
        foreach($array as $item) {
            if(!is_array($item)) {
                if ($item == $value) {
                    return true;
                } else {
                    continue;
                }
            }
            if(in_array($value, $item)) {
                return true;
            }else if($this->deepInArray($value, $item)) {
                return true;
            }
        }
        return false;
    }
    /**
     * 获取接管目标客户端
     * @param unknown $params
     */
    public function getTakeoverTargetHost($params){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $user_uuid = Xphp::$_user['useruuid'];
        $node_uuid = $params['node_uuid'];
        $master_agent_uuid = $params['master_uuid'];
        $master_os_type = $params['master_os_type'];
        $data_source = $params['data_source'];
        $clientHandler = Xphp::instance('ClientHandler');
        $taskuuid = $params['task_uuid'];
        $agentuuidArr = $clientHandler->getClientUuids();
        if (empty($agentuuidArr)) {
            // 表示没得数据
            $list = array();
            return json_encode($list);
        }
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,agent_type,net_model
                from bd_agent
                where  agent_type =?  and os_type = ? ";
        if (is_array($agentuuidArr)) {
            $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
            $sql .= " and agent_uuid in {$agentuuidArrStr} ";
        }

        $sqlParams = array(Xphp::$_config['AGENT_TYPE']['NORMAL'],$master_os_type);
        $data = $this->dbSelect($sql,$sqlParams);
        $list = array();
        $list[] = array(
            "uuid" => "",
            "text" => Xphp::$_lang['UI_VOL_CDP_TAKEOVER_TARGET_MACHINE_SELECT'],
            "value" => 0,
            "os_type" => "",
            "in_task" => false,
            "task_name" => "",
            "vol_info" => [],
        );
        foreach ($data as $host_info){
            //如果节点状态正常
            $agentUUID = $host_info['agent_uuid'];
            $onlineFlag = $host_info['online_flag'];
            
            $inTask = false;
            $task_name = " ";
            $task_uuid = "";
            $isCreateTask = $this->taskExist($agentUUID);
            $takeoverAgentRole = 0;

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
                $takeoverAgentRole = $isUsedTakeoverStandby['takeover_agent_role'];
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

            if($data_source==$VolCdpDes['DATA_SOURCE']['STANDBY']){
                $standbyConfuuid = $this->getDataSourceStandbyInfo($master_agent_uuid);
                if(!in_array($agentUUID,$standbyConfuuid)){
                    continue;
                }
            }
            
            $hostVolInfo = $this->getHostVolInfoByUUID($agentUUID);
            $hostDesc = $this->agentStr($host_info['agent_name'],$host_info['hostname'],$host_info['ip']); 
            
            if(Xphp::$_config['FLAG']['SET'] == $onlineFlag){
                $statusDes = Xphp::$_lang['WEB_AGENT_STATUS_ONLINE'];
            }else{
                $statusDes = Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'];
                $hostDesc = $hostDesc."--".$statusDes;
            }
            $list[] = array(
                'uuid' => $agentUUID,
                'data_source_agentuuid' => $master_agent_uuid,
                'text' => $hostDesc,
                'value' => $host_info['agent_uuid'],
                'os_type' => $host_info['os_type'],
                'in_task' => $inTask,
                'task_name' => $task_name,
                'vol_info' => $hostVolInfo['vol_info'],
                'mount_info' => $hostVolInfo['mount_info'],
                'agent_type' => $host_info['agent_type'],
                'net_model' => intval($host_info['net_model']),
                'online_flag' => $onlineFlag,
                'task_uuid' => $task_uuid,
                'task_type' => $task_type,
                'vol_info' => $this->getHostVolInfoByUUID($host_info['agent_uuid']),
                'takeover_agent_role' => $takeoverAgentRole,
            );
        }
        return json_encode($list);
    }
    /**
     * 获取数据源主机对应的备机信息
     * @param unknown $masteruuid
     */
    private  function getDataSourceStandbyInfo($masteruuid){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $sql = "select standby_agent_uuid from cdp_vol_backup_agent where master_agent_uuid = ? and (storage_location =? or storage_location =?) ";
        $data = $this->dbSelect($sql,array($masteruuid,$VolCdpDes['DATA_SOURCE']['STANDBY'],$VolCdpDes['DATA_SOURCE']['BACKUP_SERVER_OR_STANDBY']));
        $standbyAgentuuid = "";
        $standbyAgentArray = array();
        if (!empty($data)){
            foreach ($data as $v){
                $standbyAgentuuid = $v['standby_agent_uuid'];
                array_push($standbyAgentArray,$standbyAgentuuid);
                if(!$standbyAgentuuid){
                    break;
                }
            }
        }
        return $standbyAgentArray;
    }
    
    /**
     * 检查选择的目标是否有被其它任务选中
     * ---当主机配置了其他任务中的任意角色：备份主机，恢复目标机，双机镜像备机，自动接管备机，手动接管备机和回切目标机器时不能再作为手动接管备机使用
     * @param unknown $agentUuids
     */
    private function taskExist ($agentUUID){
        $sql = "SELECT task.task_status,task.task_name,task.task_type,task.task_uuid FROM bd_task task,cdp_vol_task_takeover_info takeover 
            WHERE takeover.task_uuid = task.task_uuid and task.task_type = ? and takeover.takeover_standby_agent_uuid = ? and task.delete_flag = ? ";
        $taskType = Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER'];
        $delFlag = Xphp::$_config['FLAG']['UNSET'];
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
    private function hostIsUsedBackupTask($agentuuid){
        $sql = "SELECT task.task_status,task.task_name,task_type,task.task_uuid FROM bd_task task,cdp_vol_task cvt 
                where cvt.task_uuid = task.task_uuid and task.task_type = ? and cvt.master_agent_uuid = ? and  task.delete_flag = ?";
        $taskType = Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'];
        $delFlag = Xphp::$_config['FLAG']['UNSET'];
        $data = $this->dbSelect($sql,array($taskType,$agentuuid,$delFlag));
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
     * 监测客户端是否作为备机被使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedTakeoverStandby($agentuuid){
        $sqlTo = "select task.task_name,task.task_name,task.task_type,task.task_status,vt.takeover_agent_role,task.task_uuid from cdp_vol_task_takeover_info vt,bd_task task 
                where takeover_standby_agent_uuid = ? and vt.task_uuid = task.task_uuid";
        $dataTo = $this->dbSelect($sqlTo,array($agentuuid));
        $taskList = array();
        if(!empty($dataTo)){
            $taskList = array(
                'task_name' => $dataTo[0]['task_name'],
                'task_status' => $dataTo[0]['task_status'],
                'task_type' => $dataTo[0]['task_type'],
                'task_uuid' => $dataTo[0]['task_uuid'],
                'takeover_agent_role' => $dataTo[0]['takeover_agent_role']
            );
        }
        return $taskList;
    }
    /**
     * 监测客户端是否作为镜像备机被使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedHaStandby($agentuuid){
        $sqlTo = "select task.task_name,task.task_type,task.task_status,task.task_uuid  from cdp_vol_task vt,bd_task task 
                where vt.standby_agent_uuid = ? and vt.task_uuid = task.task_uuid";
        $dataTo = $this->dbSelect($sqlTo,array($agentuuid));
        $taskList = array();
        if(!empty($dataTo)){
            $isEnabled =  true;
            $taskName = $dataTo[0]['task_name'];
            $taskList = array(
                'task_name' => $taskName,
                'task_status' => $dataTo[0]['task_name'],
                'task_type' => $dataTo[0]['task_type'],
                'task_uuid' => $dataTo[0]['task_uuid'],
            );
        }
        return $taskList;
    }
    /**
     * 监测客户端是否作为回切目标机器使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedFbTarget($agentuuid){
        $sqlFa = "select task.task_name,task.task_type,task.task_status,task.task_uuid from cdp_vol_task_takeover_failback_info vt,bd_task task 
                where vt.failback_target_agent_uuid = ? and vt.task_uuid = task.task_uuid";
        $dataFa = $this->dbSelect($sqlFa,array($agentuuid));
        $taskList = array();
        if(!empty($dataFa)){
            $isEnabled =  true;
            $taskName = $dataFa[0]['task_name'];
            $taskList = array(
                'task_name' => $taskName,
                'task_status' => $dataFa[0]['task_name'],
                'task_type' => $dataFa[0]['task_type'],
                'task_uuid' => $dataFa[0]['task_uuid'],
            );
        }
        return $taskList;
    }

    /**
     * 检测客户端是否作为恢复目标机器使用
     * @param unknown $agentuuid
     */
    private function hostIsUsedRecoveryTarget($agentuuid){
        $sqlRe = "select task.task_name,task.task_type,task.task_status,task.task_uuid from cdp_vol_task vt,bd_task task 
                where vt.recovery_target_agent_uuid =? and vt.task_uuid = task.task_uuid";
        $dataRe = $this->dbSelect($sqlRe,array($agentuuid));
        $taskList = array();
        if(!empty($dataRe)){
            $isEnabled =  true;
            $taskList = array(
                'task_name' => $dataRe[0]['task_name'],
                'task_status' => $dataRe[0]['task_name'],
                'task_type' => $dataRe[0]['task_type'],
                'task_uuid' => $dataRe[0]['task_uuid'],
            );
        }
        return $taskList;
    }

    /**
     * 获取客户端最早备份时间点
     * @param $params
     * @return string
     */
    private function getClientFirstTime($params){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $user_uuid = Xphp::$_user['useruuid'];
        $node_uuid = $params['node_uuid'];
        $agent_uuid = $params['agent_uuid'];
        $data_source = $params['data_source'];
        $createTaskType = $params['task_type'];
        $taskuuid = $params['task_uuid'];
        $firsTime = '--';
        if($data_source==$VolCdpDes['RECOVERY_DATA_SOURCE']['STANDBY'] && $createTaskType = Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){
            $firstSql = "select bbt.timepoint,bbt.timepoint_uuid
                    FROM cdp_vol_backup_agent agent,bd_backup_timepoint bbt
                    WHERE agent.timepoint_uuid = bbt.timepoint_uuid and agent.master_agent_uuid =? and bbt.user_uuid = ?
                        and agent.storage_location !=? and bbt.task_uuid =? ORDER BY bbt.timepoint limit 0,1";
            $firstData = $this->dbSelect($firstSql, array($agent_uuid,$user_uuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$taskuuid));
        }else{
            $firstSql = "select bbt.timepoint,bbt.timepoint_uuid
                    FROM cdp_vol_backup_agent agent,bd_backup_timepoint bbt
                    WHERE agent.timepoint_uuid = bbt.timepoint_uuid and agent.master_agent_uuid =? and bbt.user_uuid = ?
                        and agent.storage_location !=? and agent.storage_location !=? and bbt.task_uuid =? ORDER BY bbt.timepoint limit 0,1";
            $firstData = $this->dbSelect($firstSql, array($agent_uuid,$user_uuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$VolCdpDes['DATA_SOURCE']['STANDBY'],$taskuuid));
        }
        if(!empty($firstData)){
            $firsTime = $firstData[0]['timepoint'];
        }
        return $firsTime;
    }

    /**
     * 获取客户端最新时间戳
     * @param $params
     * @return void
     */
    private function getClientNewTime($params){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $user_uuid = Xphp::$_user['useruuid'];
        $node_uuid = $params['node_uuid'];
        $agent_uuid = $params['agent_uuid'];
        $data_source = $params['data_source'];
        $createTaskType = $params['task_type'];
        $taskuuid = $params['task_uuid'];
        $newTime = '--';
        $sql = "select vol.end_timestamp 
                from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol, bd_backup_timepoint bbt
                where vol.backup_agent_id = agent.id and agent.master_agent_uuid = ? and agent.storage_location !=? 
                    and agent.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ? ";
        if(($data_source==$VolCdpDes['RECOVERY_DATA_SOURCE']['STANDBY']
                || $data_source==$VolCdpDes['RECOVERY_DATA_SOURCE']['UNKNOWN'])
            && $createTaskType = Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){
            $sql = $sql." and agent.storage_location =? ORDER BY vol.end_timestamp desc LIMIT 1";
        }else{
            $sql = $sql." and agent.storage_location !=? ORDER BY vol.end_timestamp desc LIMIT 1";
        }
        $data = $this->dbSelect($sql, array($agent_uuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$taskuuid,$VolCdpDes['DATA_SOURCE']['STANDBY']));
        if(!empty($data)){
            $newTime = $data[0]['end_timestamp'];
        }
        return $newTime;
    }

    /**
     * 获取客户端备份集信息,取最新时间点
     * @param node:node_uuid,agent_uuid:agent_uuid
     */
    public function getClientBackupSetInfo ($params){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        
        $user_uuid = Xphp::$_user['useruuid'];
        $node_uuid = $params['node_uuid'];
        $agent_uuid = $params['agent_uuid'];
        $data_source = $params['data_source'];
        $createTaskType = $params['task_type'];
        $taskuuid = $params['task_uuid'];
        
        $list = array();

        $firsTime = $this->getClientFirstTime($params);
        $newTime = $this->getClientNewTime($params);
        $sql = "SELECT bbt.timepoint_uuid,bbt.src_end_timepoint,agent.storage_location,st.storage_uuid,
                st.node_uuid,bbt.encrypted_flag,bbt.detail,agent.master_agent_detail 
            FROM cdp_vol_backup_agent as agent,bd_backup_timepoint as bbt,bd_storage_resource as st 
            WHERE st.storage_uuid = bbt.storage_uuid and  agent.timepoint_uuid = bbt.timepoint_uuid 
                and agent.master_agent_uuid = ? and bbt.user_uuid = ? and agent.storage_location !=? 
                and bbt.task_uuid = ? ";
        
        if($createTaskType == Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){
            $sql = $sql."   ORDER BY agent.id DESC LIMIT 0,1";
            $data = $this->dbSelect($sql,array($agent_uuid,$user_uuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN'],$taskuuid));
        }else{
            $sql = $sql." and agent.storage_location !=? ORDER BY agent.id DESC LIMIT 0,1";
            $data = $this->dbSelect($sql,array($agent_uuid,$user_uuid,$VolCdpDes['DATA_SOURCE']['STANDBY'],$taskuuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN']));
        }
        $srcEndTimeStamp = $data[0]['src_end_timepoint'];
        $timestamp = date('Y-m-d H:i:s',$srcEndTimeStamp);
        $timepointuuid = $data[0]['timepoint_uuid'];
        
        if($data[0]['encrypted_flag'] == Xphp::$_config['FLAG']['SET']) { // 时间点加锁
            $encryptedFlag = true;
        }else {
            $encryptedFlag = false;
        }
        $detail = json_decode($data[0]['detail'],true);
        $passwordAutoFlag = intval($detail['password_auto_flag'])==Xphp::$_config['FLAG']['SET'] ? true: false;
        if($newTime=="--"){
            $newTime = $timestamp;
        }else if($newTime<$timestamp && $data_source==0){
            $newTime = $timestamp;
        }

        $list[] = array(
            'new_timestamp' => $newTime,
            '$newTime' =>$newTime,
            '$timestamp'=>$timestamp,
            'storage_location' => $data[0]['storage_location'],
            'storage_uuid' => $data[0]['storage_uuid'],
            'node_uuid' => $data[0]['node_uuid'],
            'backup_set' => $this->getClientBackupSet($agent_uuid),
            'timepoint_uuid' => $data[0]['timepoint_uuid'],
            'encrypted_flag' => $encryptedFlag,
            'password_auto_flag' => $passwordAutoFlag,
            'vol_set' => $this->getClientVolSet($agent_uuid,$srcEndTimeStamp,$newTime,$createTaskType,$timepointuuid,$taskuuid),
            'time_range' => $firsTime." -- ".$newTime
        );
        return json_encode($list);
    }
    /**
     * 获取客户端对应的备份集信息
     */
    private function getClientBackupSet($agent_uuid){
        $backup_set = [];
        return $backup_set;
    }
    /**
     * 获取客户端对应的卷信息
     */
    private function getClientVolSet($agent_uuid,$srcEndTimeStamp,$timestamp,$createTaskType,$timepointuuid,$taskuuid){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $sql = "select DISTINCT vol.vol_display_name,vol.vol_uuid,vol.standby_vol_uuid,vol.capacity,
                agent.master_agent_detail,vol.is_boot,vol.storage_status,vol.detail 
            from cdp_vol_backup_agent as agent,cdp_vol_backup_vol_set as vol, bd_backup_timepoint as bbt
            where vol.backup_agent_id = agent.id and agent.master_agent_uuid =? and agent.timepoint_uuid = bbt.timepoint_uuid 
                and bbt.src_end_timepoint = ? and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql,array($agent_uuid,$srcEndTimeStamp,$taskuuid));
        $list = array();
        $utils = Xphp::instance('Utils');
        $backupSetHandler = Xphp::instance('VolCDPBackupSetHandler');
        foreach ($data as $vol_info){
            $standby_vol_uuid = $vol_info['standby_vol_uuid'];
            $master_agent_detail = json_decode($vol_info['master_agent_detail']);
            $os_type = $master_agent_detail->os_type;
            $storageStatus = $vol_info['storage_status'];
            $volDetail = json_decode($vol_info['detail']);
            $volType = $volDetail->vol_type;
            $systemVolume = false;
            if($volType & $VolCdpDes['VOL_TYPE']['BD_SYSTEM_VOLUME']){
                $systemVolume = true;
            }
            $uefi = false;
            if($volType & $VolCdpDes['VOL_TYPE']['BD_EFI_VOLUME']){
                $uefi = true;
            }
            
            $verifyResult = $backupSetHandler ->verifyBackupSetStatus($storageStatus,$createTaskType,$agent_uuid,$timestamp,$timepointuuid);
            $list[] = array(
                'vol_name' => $vol_info['vol_display_name'],
                'capacity' => $utils->calSize($vol_info['capacity'], true),
                'capacity_value' =>$vol_info['capacity'],
                'new_timestamp' =>$timestamp,
                'vol_uuid' => $vol_info['vol_uuid'],
                'verify_result' => $verifyResult,
                'standby_vol' => $this->getVolInfoByVoluuid($standby_vol_uuid,$agent_uuid),
                'os_type' => $os_type,
                'is_boot' => $vol_info['is_boot'],
                'system_volume' =>$systemVolume,
                'is_uefi' => $uefi,

            );
        }
        return $list;
    }
    /**
     * 获取对应卷名
     * @param unknown $standby_vol_uuid
     * @param unknown $agent_uuid
     * @desc 和后台确认，当前版本不考虑备用服务器被删除情况
     */
    private function getVolInfoByVoluuid($standby_vol_uuid,$agent_uuid){
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
     * 获取选中客户端应用信息
     */
    public function getClientTakeoverAppInfo($params){
        $vol_set = $params['vol_set'];
        $agent_uuid = $params['agent_uuid'];
        $take_over_time = $params['take_over_time'];
        $standby_host_uuid = $params['standby_host_uuid'];
        $task_uuid = $params['task_uuid'];
        
        
        $sql = "SELECT master_agent_detail,id,master_agent_uuid from cdp_vol_backup_agent where master_agent_uuid = ?";
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
            "title" => Xphp::$_lang['UI_VOL_CDP_HOST'].$host_info,
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
        return json_encode($tree);
    }
    /**
     * 获取客户端备份集应用类型
     */
    private function getAppType ($agentUUID,$agent_id_list,$take_over_time,$standby_host_uuid,$task_uuid){
        $id_str =  join(",", $agent_id_list);
        $sql = "SELECT DISTINCT id,app_type,app_name,app_uuid 
                FROM cdp_vol_backup_app 
                where backup_agent_id in ({$id_str}) GROUP BY app_type";
        $data = $this->dbSelect($sql);
        $appExist = false;
        $info = array();
        if(count($data)>0){
            foreach ($data as $appInfo){
                $alaisName = $appInfo['app_name'];
                $appTypeValue = $appInfo['app_type'];
                $appUuid = $appInfo['app_uuid'];
                $appTypeDes = Xphp::$_config['DB_TYPE_DES'][intval($appTypeValue)] . PHP_EOL;
                if($appTypeValue == Xphp::$_config['DB_TYPE']['SQLSERVER']){
                    $icon = "./img/db/sqlserver.png";
                }else if($appTypeValue == Xphp::$_config['DB_TYPE']['ORACLE']){
                    $icon = "./img/db/oracle.png";
                }else if($appTypeValue == Xphp::$_config['DB_TYPE']['MYSQL']){
                    $icon = "./img/db/mysql.png";
                }
                $id = $agentUUID.$appTypeValue;
                $info[] = array(
                    "id" => $id,
                    "pId" => $agentUUID,
                    "name" => $appTypeDes,
                    "title" => Xphp::$_lang['UI_VOL_CDP_BACKUP_DB_APPLICATION'],
                    "appTypeValue" => $appTypeValue,
                    'agent_id_list' =>$agent_id_list,
                    "icon" => $icon,
                    "isApp" =>false,
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" =>  true,
                    "agent_id_list" => $agent_id_list,
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
        $sql = "SELECT DISTINCT cvba.id,cvba.app_type,cvba.app_name,cvba.app_uuid
                FROM cdp_vol_backup_app cvba,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                where cvba.backup_agent_id in ({$id_str}) and cvba.app_type =? and agent.id = cvba.backup_agent_id and agent.timepoint_uuid = bbt.timepoint_uuid
		        and bbt.task_uuid =? GROUP BY cvba.app_uuid";
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
     * 获取app 组件信息
     */
    private function getAppModuleInfo($pId,$appUUID,$appTypeValue,$agent_id_list,$takeoverTime){
        $takeoverTimetampArry = $this->getUnixTimetamp($takeoverTime);
        $takeoverTimetamp = $takeoverTimetampArry[0];
        $sql = "SELECT DISTINCT module.id,module.module_name,module.module_detail  
               FROM cdp_vol_backup_app_module module,cdp_vol_backup_app app 
               WHERE module.backup_app_id = app.id and app.app_uuid = ? and 
                     (module.start_timestamp <= ? and module.end_timestamp >=?) 
                     GROUP BY module_name";
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
        $sql = "SELECT UNIX_TIMESTAMP(?)";
        $data = $this->dbSelect($sql,array($params));
        return $data[0];
    }
    /**
     * 通过选中备机的uuid及主机对应应用的类型及应用名,自动获取备机应用信息
     * @param $standby_host_uuid:备机UUID,$app_type:选定主机应用类型,$app_name:应用名
     */
    private function getStandbyAppinfo($standby_host_uuid,$app_type,$app_name){
        $sql = 'select app.app_uuid,app.app_name from bd_agent as agent,bd_agent_app as app 
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
     * 得到卷CDP恢复任务名
     * @param unknown $params
     */
    public function getVolCdpTakeoverTaskName($params){
        $DefaultTaskName =$params['task_type'];
        $newTaskName = $this->getValidTaskName($DefaultTaskName);
        return html_entity_decode($newTaskName);
    }
    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    private function getValidTaskName($taskName){
        $oldTaskName = $taskName;
        for($i=1; $i<1000; $i++){
            $taskName .= $i;
            $sql = "select id from bd_task where task_name = ?";
            $data = $this->dbSelect($sql, array($taskName));
            $sql = "select id from bd_backup_timepoint where task_name = ?";
            $data1 = $this->dbSelect($sql, array($taskName));
            if(empty($data) && empty($data1)){
                return $taskName;
            }
            $taskName = $oldTaskName;
        }
        return $oldTaskName;
    }
    /*
     * 创建手动接管作业
     */
    public function createTakeoverJob($params){
        //public params
        $task_name = $params['taskName'];
        $this->paramsCheck($task_name);
        $vmHandler = Xphp::instance('Vmhandler');
        $module_type = Xphp::$_config['MODULE_TYPE']['VOL_CDP'];
        
        $time_strategy_list = array();
        $node = $params['node'];
        $transport_strategy = $params['transfer'];
        $pfMSg = array();
//         $recovery_position = "";
//         $recovery_time_type = 1;  //手动启动
//         $pfMSg = $this->pfCreateRecoveryTaskMessage($task_name, $module_type, $recovery_position,
//             $recovery_time_type, $time_strategy_list, $transport_strategy);
        $pfMSg['task_name'] = $task_name;
        $pfMSg['dev_type'] = 1;   //备份类型 1卷 2磁盘
        $pfMSg['module_type'] = $module_type;
        $pfMSg['task_type'] = Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']; //作业类型
        $pfMSg['master_agent_uuid'] = $params['master_agent_uuid'];
        $pfMSg['takeover_data_source'] = $params['takeover_data_source'];
        $pfMSg['takeover_timestamp'] = $params['takeover_timestamp'];
        $pfMSg['vol_uuid_set'] = $params['vol_uuid_set'];
        $pfMSg['takeover_object'] = $params['takeover_object'];
        $pfMSg['node_uuid'] = $node;
        $pfMSg['storage_uuid'] = $params['storage_uuid'];
        $pfMSg['takeover_standby_agent_uuid'] = $params['takeover_standby_agent_uuid'];
        /*
         * 下面7个参数为接管需要填充的默认参数,后台公共处理流程需要,后台接管模块暂不解析.
         */
        $pfMSg['recovery_position'] = 0;
        $pfMSg['time_strategy_list'] = array();
        $pfMSg['transport_strategy'] = $this->volcdpTransportStrategyMessage(0,0,0,0,$transport_strategy);
        
        $pfMSg['thread_num'] = 3;
        $pfMSg['speed_limit_strategy_list'] = array();
        $pfMSg['strategy_group_uuid'] = "";
        $pfMSg['rebuild_partition_flag'] = 0;
        $pfMSg['timepoint_uuid'] = $params['timepoint_uuid'];

        $opName = 'BD_TASK_OP_TAKEOVER_CREATE';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);

        $mbResult = $this->mbVolCdpMsg($node, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
            
    }
    /**
     * 传输策略消息
     * @param int $encrypt_flag
     * @param int $compress_flag
     * @param int $speed_limit_flag
     * @param int $max_speed
     * @return array $transportStrategyMessage
     */
    protected function volcdpTransportStrategyMessage($encrypt_flag, $compress_flag, $speed_limit_flag, $max_speed,$transport_strategy){
        $transportStrategyMessage = array(
            'encrypt_flag' => $encrypt_flag,
            'compress_flag' => $compress_flag,
            'speed_limit_flag' => $speed_limit_flag,
            'max_speed' => $max_speed,
            "network_uuid" =>$transport_strategy['network'],
            'strategy_group_uuid' => $transport_strategy['strategy_group_uuid']
        );
        return $transportStrategyMessage;
    }
    
    /**
     * 获取任务接管回切配置
     */
    public  function getVolCdpTaskHostConf($params){
        $user_uuid = Xphp::$_user['useruuid'];
        $task_uuid = $params['uuid'];
        $task_type = $params['task_type'];
        //首先判断回切信息表cdp_vol_task_takeover_failback_info中是否有记录，没有则是判断任务类型，备份和手动接管分别获取对应数据。
        $sql = "SELECT fback.failback_target_agent_uuid,fback.memory_cache_alloc_space,fback.file_cache_alloc_space,fback.file_cache_storage_path,
                    fback.transport_compress_flag,fback.transport_encrypt_flag,fback.monitor_data_io_replication_mode,fback.storage_uuid,task.agent_uuid,
                    task.node_uuid,fback.transport_thread_num,fback.transport_block_size,fback.mirror_backup_flag,
                    vol_task.rebuild_partition_flag,fback.transport_encrypt_method,fback.transport_compress_method  
                FROM cdp_vol_task_takeover_failback_info fback
                    INNER JOIN bd_task task ON task.task_uuid=fback.task_uuid
                    INNER JOIN cdp_vol_task vol_task ON vol_task.task_uuid=fback.task_uuid
                WHERE task.task_uuid =? AND task.task_uuid = fback.task_uuid";
        $data = $this->dbSelect($sql,array($task_uuid));
        $list = array();
        if(!empty($data)){
            //获取配置信息
            $failback_target_agent_uuid = $data[0]['failback_target_agent_uuid'];
            $memory_cache_alloc_space = $data[0]['memory_cache_alloc_space'];
            $file_cache_alloc_space = $data[0]['file_cache_alloc_space'];
            $file_cache_storage_path = $data[0]['file_cache_storage_path'];
            $transport_compress_flag = $data[0]['transport_compress_flag']; //传输压缩flag
            $transport_encrypt_flag = $data[0]['transport_encrypt_flag'];   //传输加密flag
            $transport_encrypt_method = $data[0]['transport_encrypt_method'];
            $transport_compress_method = $data[0]['transport_compress_method'];

            $io_replication_mode = $data[0]['monitor_data_io_replication_mode']; //IO 複製模式
            $taskMasterUuid =  $data[0]['agent_uuid']; //任务主机uuid
            $storage_uuid = $data[0]['storage_uuid']; 
            $node_uuid = $data[0]['node_uuid'];
            $mirror_backup_flag = $data[0]['mirror_backup_flag'];
            $rebuildPartitionFlag = $data[0]['rebuild_partition_flag'];
            
            $transport_thread_num = $data[0]['transport_thread_num'];  //传输线程个数
            $transport_block_size = $data[0]['transport_block_size']/1024/1024;  //传输数据包大小
            $agentInfo = $this->getHostInfo($failback_target_agent_uuid);
            $netModel = $agentInfo['net_model'];
            $host_list = $this->getTakeoverHost($failback_target_agent_uuid,$task_uuid,$task_type,$taskMasterUuid);
        }else{
            $sqlTask= "select vol_task.master_agent_uuid,vol_task.monitor_data_io_replication_mode,task.storage_uuid,
                          task.node_uuid, vol_task.rebuild_partition_flag
                       from cdp_vol_task vol_task,bd_task task 
                       where vol_task.task_uuid = task.task_uuid and vol_task.task_uuid = ?";
            
            $dataTask = $this->dbSelect($sqlTask,array($task_uuid));
            $taskMasterUuid  = $dataTask[0]['master_agent_uuid'];
            $io_replication_mode = $dataTask[0]['monitor_data_io_replication_mode'];
            $storage_uuid = $dataTask[0]['storage_uuid'];
            $node_uuid = $dataTask[0]['node_uuid'];
            $failback_target_agent_uuid = "";
            $mirror_backup_flag = Xphp::$_comment['FLAG']['UNSET'];
            $rebuildPartitionFlag = $dataTask[0]['rebuild_partition_flag'];
            $transport_thread_num = 1;  //default value;
            $transport_block_size = 4;  //传输数据包大小默认为4 MB
            $netModel = "";
            //获取配置信息
            if($task_type==Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']){
                $sqlCache = "select agent_memory_cache_alloc_space,agent_file_cache_alloc_space,agent_cache_file_storage_path 
                    from cdp_vol_task_cache_info where task_uuid = ?";
                $dataCache = $this->dbSelect($sqlCache,array($task_uuid));
                
                $memory_cache_alloc_space = $dataCache[0]['agent_memory_cache_alloc_space'];
                $file_cache_alloc_space = $dataCache[0]['agent_file_cache_alloc_space'];
                $file_cache_storage_path = $dataCache[0]['agent_cache_file_storage_path'];
                
                $sqlTransportStrategy = "select trans.encrypt_flag,trans.compress_flag,trans.compress_method,trans.encrypt_method 
                                         from bd_transport_strategy trans,bd_task task 
                                         where task.task_uuid = ? and task.strategy_id = trans.strategy_id ";
                $dataTrans = $this->dbSelect($sqlTransportStrategy,array($task_uuid));
                $transport_compress_flag = $dataTrans[0]['compress_flag'];
                $transport_encrypt_flag = $dataTrans[0]['encrypt_flag'];
                $transport_encrypt_method = $dataTrans[0]['encrypt_method'];
                $transport_compress_method = $dataTrans[0]['compress_method'];
            }elseif ($task_type==Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){
                $memory_cache_alloc_space = 0;
                $file_cache_alloc_space = 0;
                $file_cache_storage_path = "";
                $transport_compress_flag = 0;
                $transport_encrypt_flag = 0;
                $transport_encrypt_method = 0;
                $transport_compress_method = 0;
            }
            $host_list = $this->getTakeoverHost($failback_target_agent_uuid,$task_uuid,$task_type,$taskMasterUuid);
        }
        $standbyHostInfo = $this->getTaskStandbyHostInfo($task_uuid);
        $standbyAgentuuid = $standbyHostInfo['stand_agent_uuid'];
        $agentInfo = $this->getHostInfo($standbyAgentuuid);
        $osType = $agentInfo['os_type'];
        
        $list[] = array(
            'memory_cache_alloc_space' =>$memory_cache_alloc_space,
            'file_cache_alloc_space' => $file_cache_alloc_space,
            'file_cache_storage_path' => $file_cache_storage_path,
            'transport_compress_flag' => $transport_compress_flag,
            'transport_encrypt_flag' => $transport_encrypt_flag,
            'transport_encrypt_method' => $transport_encrypt_method,
            'transport_compress_method' => $transport_compress_method,
            'task_master_uuid' => $taskMasterUuid,
            'failbackup_target_uuid' => $failback_target_agent_uuid,
            'io_replication_mode' => $io_replication_mode,
            'host_list' => $host_list,
            'storage_uuid' => $storage_uuid,
            'node_uuid' => $node_uuid,
            'mirror_backup_flag' => $mirror_backup_flag,
            'transport_thread_num' =>$transport_thread_num,
            'transport_block_size' => $transport_block_size,
            'rebuild_partition_flag' => $rebuildPartitionFlag,
            'net_model' => $netModel,
            'standby_os_type' => $osType
        );
        return json_encode($list);
    }
    /**
     * 获取回切主机对应的网络模式
     */
    private function getHostInfo($agentuuid){
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,agent_type,net_model
                from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql,array($agentuuid));
        $netModel = $data[0]['net_model'];
        $osType = $data[0]['os_type'];
        $hostInfo = array(
            'net_model' => $netModel,
            'os_type'=> $osType
        );
        return $hostInfo;

    }
    /**
     * 获取可供回切用的主机信息
     */
    private function getTakeoverHost($fb_target_uuid,$task_uuid,$create_task_type,$taskMasterUuid){
        $list = array();
        $clientHandler = Xphp::instance('ClientHandler');
        $agentuuidArr = $clientHandler->getClientUuids();
        if (empty($agentuuidArr)) {
            return json_encode($list);
        }
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,agent_type,net_model
                from bd_agent
                where  (agent_type =? or agent_type =?)  ";
        if (is_array($agentuuidArr)) {
            $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
            $sql.=" and agent_uuid in {$agentuuidArrStr}";
        }

        if($create_task_type ==Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']){  //备份任务回切目标机器可以是数据源主机
            $dataSql  = $sql;
            $isAutoTakeover = true;
            $sqlParams = array(Xphp::$_config['AGENT_TYPE']['NORMAL'], Xphp::$_config['AGENT_TYPE']['MEMORY_OS']);
        }else{ 
             $dataSql = $sql;
             $isAutoTakeover = false;
             $sqlParams = array(Xphp::$_config['AGENT_TYPE']['NORMAL'],Xphp::$_config['AGENT_TYPE']['MEMORY_OS']);
        }
        $data = $this->dbSelect($dataSql,$sqlParams);
        $list = array();
        foreach ($data as $host_info){
            $agentUuid = $host_info['agent_uuid'];
            $netModel = $host_info['net_model'];
            $onlineFlag = $host_info['online_flag'];
            $agentType = $host_info['agent_type'];
            $agentName = $host_info['agent_name'];
            $hostName = $host_info['hostname'];
            $ip = $host_info['ip'];
            
            $agentEnabled = $this->getAgentEnabled($agentUuid,$task_uuid,$isAutoTakeover,$taskMasterUuid);
            if(!$agentEnabled){ //不可用
                continue;
            }
            
            if(Xphp::$_config['FLAG']['SET'] == $onlineFlag){
                $statusDes = Xphp::$_lang['WEB_AGENT_STATUS_ONLINE'];
            }else{
                $statusDes = Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'];
            }
            
            if (!empty($agentName) && $agentName != $ip) {
                $hostInfo =  $agentName ? $agentName."(". $ip .")" : $hostName."(". $ip .")";
            }else {
                $hostInfo = $hostName."(". $ip .")";
            }
            
            if(Xphp::$_config['FLAG']['SET'] == $onlineFlag){
                $statusDes = Xphp::$_lang['WEB_AGENT_STATUS_ONLINE'];
            }else{
                $statusDes = Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'];
                $hostInfo = $hostInfo."--".$statusDes;
            }
            
            $taskInfo = $this->taskExist($agentUuid);  //获取任务是否存在，获取任务类型
            $taskName = "";
            $bkTaskIsRunning = Xphp::$_config['FLAG']['UNSET'];
            if(count($taskInfo)>0){
                $taskName =  $taskInfo['task_name'];
                $taskType = $taskInfo['task_type'];
                $taskStatus = $taskInfo['task_status'];
                if($taskType==Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] && $taskType ==Xphp::$_config['TASKSTATUS']['RUNNING']){
                    $bkTaskIsRunning =  Xphp::$_config['FLAG']['SET'];
                }
            }
            $hostOsType  = $host_info['os_type'];
            $taskMasterAgentOsType = $this->getHostOsTypeForTaskuuid($task_uuid,$create_task_type);

            if($hostOsType!=$taskMasterAgentOsType && $agentType!= Xphp::$_config['AGENT_TYPE']['MEMORY_OS']){
                continue;
            }
            
            $diskInfo = $this->getHostDiskInfo($host_info['agent_uuid']);
            $list[] = array(
                'uuid' => $agentUuid,
                'text' => $hostInfo,
                'value' => $host_info['agent_uuid'],
                'os_type' => $hostOsType,
                'agent_type' => $agentType,
                'host_disk_info' => $diskInfo,
                'host_vol_info' => $this->getHostVolInfoByUUID($agentUuid),
                'task_is_running' => $bkTaskIsRunning,
                'net_model' => $netModel,
            );
        }
        return json_encode($list);
    }
    /**
     * 通过任务UUID获取任务主机或数据源对应的操作系统类型
     * @param $taskuuid
     */
    private function getHostOsTypeForTaskuuid($taskuuid,$taskType){
        if($taskType==Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){  //接管任务
            $sql = "select agent.master_agent_detail,agent.id from cdp_vol_backup_agent agent,cdp_vol_task task 
                    where task.task_uuid =? and task.master_agent_uuid = agent.master_agent_uuid ORDER BY agent.id desc LIMIT 0,1";
        }else{
            $sql = "SELECT agent.os_type from cdp_vol_task task,bd_agent agent 
                    where task.task_uuid = ? and task.master_agent_uuid = agent.agent_uuid";
        }

        $osType = "";
        $data = $this->dbSelect($sql, array($taskuuid));
        if(!empty($data)){
            if($taskType==Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']) {  //接管任务
                $detail = $data[0]['master_agent_detail'];
                $detailInfo = json_decode($detail);
                $osType = $detailInfo->os_type;
            }else{
                $osType = $data[0]['os_type'];
            }
        }
        return $osType;
    }

    /**
     * 获取筛选主机卷信息
     * @param：agent_uuid
     */
    private function getHostVolInfoByUUID($hostUuid){
        $utils = Xphp::instance('Utils');
        
        $agentSql = "select agent_type from bd_agent where agent_uuid = ? ";
        $data = $this->dbSelect($agentSql, array($hostUuid));
        $agentType = 1;
        if(!empty($data)){
            $agentType = $data[0]['agent_type'];
        }
        
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
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
            if($volTypeValue & $VolCdpDes['VOL_TYPE']['BD_WIN_RESERVE_VOLUME']
                || $volTypeValue & $VolCdpDes['VOL_TYPE']['BD_WIN_RECOVERY_VOLUME']
                || $volTypeValue & $VolCdpDes['VOL_TYPE']['BD_PV_VOLUME']
                || $volTypeValue & $VolCdpDes['VOL_TYPE']['BD_EXTEND_VOLUME']){
                continue;
            }
            //swap 分区
//            if($mountPoint =="[SWAP]"){
//                continue;
//            }
            if($volTypeValue & $VolCdpDes['VOL_TYPE']['BD_SWAP_VOLUME']){
                continue;
            }
            //内存操作系统的内置分区，如 live 等,以lvice-去过滤
            $pos = strstr($volName,"live-");
            if($pos !==false){
                continue;
            }
            if(($volTypeValue & $VolCdpDes['VOL_TYPE']['BD_BOOT_VOLUME']
                    || $volTypeValue & $VolCdpDes['VOL_TYPE']['BD_SYSTEM_VOLUME']
                    || $volTypeValue & $VolCdpDes['VOL_TYPE']['BD_EFI_VOLUME'])
                && $agentType!=Xphp::$_config['AGENT_TYPE']['MEMORY_OS']) {
                $canSelect = false;
            }
            
            // 普通分区，未挂载分区 自由勾选
            if(!empty($v['mount_point'])){
               array_push($HostMountPointArry,$v['mount_point']);
            }
            $list[] = array(
                "uuid" => $v['vol_uuid'],
                "capacity_value" => $v['capacity'],
                "capacity" =>$utils->calSize($v['capacity'], true),
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
     * 获取主机磁盘信息
     */
    private function getHostDiskInfo($uuid){
        $utils = Xphp::instance('Utils');
        $sql = "select disk_uuid,capacity,free_space,display_name,detail from bd_agent_disk where agent_uuid = ?";
        $data = $this->dbSelect($sql,array($uuid));
        $list = array();
        foreach ($data as $v){
            $diskDetail = json_decode($v['detail']);
            $deviceType = $diskDetail->device_type;
            if($deviceType=="12"){
                continue;
            }
            $list[] = array(
                "uuid" => $v['disk_uuid'],
                "capacity_value" => $v['capacity'],
                "capacity" =>$utils->calSize($v['capacity'], true),
                "free_space" => $v['free_space'],
                "display_name" => $v['display_name']
            );
        }
        return $list;
    } 
    
    /**
     * 获取当前主机是否有任务，是否可用
     */
    private function getAgentEnabled($host_uuid,$task_uuid,$isAutoTakeover,$taskMasterUuid){
        if($isAutoTakeover && ($taskMasterUuid == $host_uuid)){
            return true;
        }
        $sql = "select vol_task.task_uuid,task.task_status,task.task_type from cdp_vol_task vol_task,bd_task task 
            where (vol_task.master_agent_uuid = ? or vol_task.standby_agent_uuid = ? or vol_task.host_ha_standby_agent_uuid = ?) 
            and task.task_uuid =vol_task.task_uuid and task.task_type !=? ";
        $data = $this->dbSelect($sql,array($host_uuid,$host_uuid,$host_uuid,Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']));
        $isEnabled = false;
        if(!empty($data)){
            foreach ($data as $v){
                if($task_uuid ==$v['task_uuid']){
                    $isEnabled = true;
                }else{
                    $isEnabled = false;
                    return $isEnabled;
                }
                if($v['task_type'] == Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] && $v['task_status'] ==Xphp::$_config['TASKSTATUS']['RUNNING']){
                    $isEnabled = false;
                    return $isEnabled;
                }
            }
        }else{
            $isEnabled = true;
        }
        
        //目标机器是否已做为恢复目标机器被使用
        $sqlRe = "select task_uuid from cdp_vol_task where recovery_target_agent_uuid =?";
        $dataRe = $this->dbSelect($sqlRe,array($host_uuid));
        if(!empty($dataRe)){
            $isEnabled =  false;
            return $isEnabled;
        }else{
            $isEnabled =  true;
        }
        //目标机器是否已做为接管备机被使用
        $sqlTo = "select task_uuid from cdp_vol_task_takeover_info where takeover_standby_agent_uuid = ?";
        $dataTo = $this->dbSelect($sqlTo,array($host_uuid));
        if(!empty($dataTo)){
            $isEnabled =  false;
            return $isEnabled;
        }else{
            $isEnabled =  true;
        }
        
        //目标机器是否已做为回切目标主机被使用
        $sqlFa = "select task_uuid from cdp_vol_task_takeover_failback_info where failback_target_agent_uuid = ?";
        $dataFa = $this->dbSelect($sqlFa,array($host_uuid));
        if(!empty($dataFa)){
            foreach ($dataFa as $v){
                if($task_uuid ==$v['task_uuid']){  //如果是当前任务，运行被选择
                    $isEnabled = true;
                }else{
                    $isEnabled = false;
                    return $isEnabled;
                }
            }
        }else{
            $isEnabled =  true;
        }
        
        return $isEnabled;
    }

    /**
     * 获取回切目标主机对应的磁盘信息
     */
    public function getAgentDiskInfo($params)
    {
        $agentUuid = $params['agent_uuid'];
        $diskInfo = $this->getHostDiskInfo($agentUuid);
        return json_encode($diskInfo);
    }
    
    /**
     * 获取回切目标主机对应的卷信息
     */
    public function getAgentVolInfo($params){
        $agentuuid = $params['agent_uuid'];
        $volInfo = $this->getHostVolInfoByUUID($agentuuid);
        return json_encode($volInfo);
    }
    
    /**
     * 获取当前任务是否有进行接管回切配置
     * @param object
     */
    public function getTaskFailbackInfo($params){
        $task_uuid = $params['uuid'];
        $sql = "SELECT failback_target_agent_uuid FROM cdp_vol_task_takeover_failback_info WHERE task_uuid =?";
        $data = $this->dbSelect($sql,array($task_uuid));
        $list = array();
        if(!empty($data)){
            foreach ($data as $v){
                $list[] = array(
                    'failback_target_agent_uuid' => $v['failback_target_agent_uuid']
                );
            }
        }
        return json_encode($list);
    } 
    
    /**
     * 接管回切配置
     */
    public function takeOverCutBack($params){
        $pfMSg = array();
        $task_uuid = $params['task_uuid'];
        $pfMSg['task_uuid'] =$task_uuid;
        $pfMSg['failback_target_agent_uuid'] = $params['failback_target_agent_uuid'];
        $pfMSg['memory_cache_alloc_space'] = $params['memory_cache_alloc_space'];
        $pfMSg['file_cache_alloc_space'] = $params['file_cache_alloc_space'];
        $pfMSg['file_cache_storage_path'] = $params['file_cache_storage_path'];
        $pfMSg['transport_encrypt_flag'] = $params['transport_encrypt_flag'];
        $pfMSg['transport_compress_flag'] = $params['transport_compress_flag'];
        $pfMSg['transport_encrypt_method'] = $params['encrypt_method'];
        $pfMSg['transport_compress_method'] = $params['compress_method'];

        $pfMSg['monitor_data_io_replication_mode'] = $params['monitor_data_io_replication_mode'];
        $pfMSg['rebuild_partition_flag'] = $params['rebuild_partition_flag'];
        $pfMSg['vol_set'] = $params['vol_set'];
        $pfMSg['storage_uuid'] = $params['storage_uuid']; 
        $pfMSg['transport_thread_num'] = $params['transport_thread_num'];
        $pfMSg['transport_block_size'] = $params['transport_block_size'];
        $pfMSg['mirror_backup_flag'] = $params['mirror_backup_flag'];  //回切数据备份到备份服务器
        $pfMSg['failback_business_ip_map'] = $params['failback_business_ip_map'];
        $pfMSg['takeover_business_ip_map'] = $params['takeover_business_ip_map'];
        $pfMSg['network_uuid'] = $params['network_uuid'];

        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeUuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);   //任务UUID对应的存储节点uuid
       
        $opName = 'VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_CONFIG';
        $mbResult = $this->mbVolCdpMsg($nodeUuid, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        
        $pfOpcode = Xphp::instance('VolCDPOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 获取接管脚本配置信息
     */
    public function getTakeoveScriptConf($params){
        $taskUuid = $params['uuid'];
        $sql = "select script_type,script_path,exec_type,exec_interval,trigger_fail_num 
            from cdp_vol_task_takeover_script where task_uuid = ?";
        $data = $this->dbSelect($sql,array($taskUuid));
        $list = array();
        
        foreach ($data as $v){
            $list[]= array(
                'script_type' => $v['script_type'],
                'script_path' => $v['script_path'],
                'exec_type' => $v['exec_type'],
                'exec_interval' => $v['exec_interval'],
                'trigger_fail_num' => $v['trigger_fail_num']
            );
        }
        return json_encode($list);
    }
    
    /**
     *  判断数据对应主机ip是否在线,
     */
    public function checkIpIsOnline($params){
        $agentuuid = $params['agent_uuid'];
        $sql = "select master_agent_detail from cdp_vol_backup_agent where master_agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentuuid));
        $masterAgentDetail = json_decode($data[0]['master_agent_detail']);
        
        $agentIp = $masterAgentDetail->ip;
        $hostname = $masterAgentDetail->hostname;
        
        $output = exec('ping -c 1 -w 1 '.$agentIp,$out,$status); 
        if($status==0){  //代表能ping通
            $exists = false;
        }else{
            $exists = true;
        }
        $list = array(
            'host_name' => $hostname,
            'ip' => $agentIp,
            'exists' => $exists
        );
        
        return json_encode($list);
    }
    
    /**
     * 根据条件组合客户端的名称
     */
    private function agentStr($agentName,$hostName,$ip){
        if (!empty($agentName) && $agentName != $ip) {
            $hostInfo =  $agentName ? $agentName."(". $ip .")" : $hostName."(". $ip .")";
        }else {
            $hostInfo = $hostName."(". $ip .")";
        }
        return $hostInfo;
    }
    /**
     * 获取任务历史网络配置
     * @param unknown $params
     */
    public function getTaskNetworkHistoryConf($params){
        $taskuuid = $params['uuid'];
        $taskType = $params['task_type'];
        $hisNicConfList =array();
        if($taskType==Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] || $taskType ==  Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){
            $sql = "select detail from cdp_vol_task_takeover_info where task_uuid =?";
            $data = $this->dbSelect($sql,array($taskuuid));
            $detail = json_decode($data[0]['detail']);
            $hisNicConfList = array(
                "takeover_business_ip_map" => $detail->takeover_business_ip_map,
                "failback_business_ip_map" => $detail->failback_business_ip_map
            );
        }
        return json_encode($hisNicConfList);
    }
    /**
     * 获取任务配置的接管网络信息
     */
    public function getTaskNetworkConfInfo($params){
        $taskuuid = $params['uuid'];
        $taskType = $params['task_type'];
        $failbackhost = $params['failbackhost'];  //回切目标主机
        
        $taskNetworkConf =array();
        $nicList = [];
        $standbyNicList = [];
        /***接管任务，通过任务UUID获取主机信息，判断主机在bd_agent中是否存在。存在则更新客户端网卡信息，不存在需要获取接管时间点。
         * 通过接管时间点和主机UUID找到备份集，根据备份集对应的backup_agent_id 找到对应cdp_vol_backup_agent中master_agent_detail，
         * 在master_agent_detail中获取该客户端的历史网卡信息 
         */
        $isFailBack = false;
        if($failbackhost !="0" && $failbackhost!=""){
            $isFailBack = true;
        }
        $sql = "select master_agent_uuid FROM cdp_vol_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $masterAgentuuid = "";
        if(!empty($data)){
            $masterAgentuuid = $data[0]['master_agent_uuid']; //任务主机UUID
        }
        
        $sqlAgent = "select detail from bd_agent where agent_uuid = ? ";
        $agentData = $this->dbSelect($sqlAgent, array($masterAgentuuid));
        
        if($isFailBack){  //回切时，主机uuid为任务对应的接管备机信息
             $standbyHostInfo = $this->getTaskStandbyHostInfo($taskuuid);
             $masterAgentuuid = $standbyHostInfo['stand_agent_uuid'];
             $takeoverAgentType = $standbyHostInfo['takeover_agent_type'];
             if($takeoverAgentType == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){
                $nicList = $this->getHostNetworkInfo($masterAgentuuid);
             }else{  //内嵌虚拟机
                // $nicList = $this->getTempAgentConfigInfo($masterAgentuuid);
                $nicList =  $standbyHostInfo['standby_nic_list'];
             }
        }else{
            if($masterAgentuuid!=""){  //发送更新命令，更新网卡信息。
                $updateNetCard = $this->updateHostNetCardinf($masterAgentuuid);
                $nicList = $this->getHostNetworkInfo($masterAgentuuid);
            }else{
                //接管任务从备份集中取对应网卡信息，备份任务直接提示获取客户端网卡信息异常，“客户端离线或程序异常”
                if($taskType ==  Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']){  //接管任务
                    $takeoverSql = "select agent.master_agent_detail from cdp_vol_backup_vol_set vol_set,cdp_vol_task_takeover_info cvtt,
                                cdp_vol_backup_agent agent where agent.id = vol_set.backup_agent_id and cvtt.task_uuid = ?
                         and (UNIX_TIMESTAMP(cvtt.takeover_timestamp) >= UNIX_TIMESTAMP(vol_set.start_timestamp)
                         and UNIX_TIMESTAMP(cvtt.takeover_timestamp) <= UNIX_TIMESTAMP(vol_set.end_timestamp))";
                    $takeoverData = $this->dbSelect($takeoverSql, array($taskuuid));
                    if(!empty($takeoverData)){
                        $masterAgentDetail = $takeoverData[0]['master_agent_detail'];
                        $detail = json_decode($masterAgentDetail);
                        $nicList = $detail->nic_list;
                    }
                }
            }
        }
        //获取备机网卡信息
        if($isFailBack){  //回切任务时，主机为任务对应的takeover_standby_agent_uuid,备机为回切目标对象
//             $updateNetCard = $this->updateHostNetCardinf($failbackhost);
//             if($updateNetCard){
//                 $standbyNicList = $this->getHostNetworkInfo($masterAgentuuid);
//             }
            $standbyNicList = $this->getHostNetworkInfo($failbackhost);
        }else{
            $standbyHostInfo = $this->getTaskStandbyHostInfo($taskuuid);
            //TODO 判断备机类型，备机类型为代理时，对应takeover_vm_hypervisor值为0，按之前流程走
            //如果非0，从takeover_vm_config中获取interfaces
            $standbyNicList = $standbyHostInfo['standby_nic_list'];
        }
        $taskNetworkConf = array(
            "agent_nic_list" => $nicList,
            "standby_nic_list" => $standbyNicList,
        );
        return json_encode($taskNetworkConf);
    }
    
    /**
     * 获取任务备机信息
     * @param unknown $taskuuid
     */
    private function getTaskStandbyHostInfo($taskuuid){
        $standbyNicList = [];
        $standbySql = "select takeover_standby_agent_uuid,takeover_vm_hypervisor,takeover_vm_config  from cdp_vol_task_takeover_info where task_uuid = ?";
        $standbyData = $this->dbSelect($standbySql, array($taskuuid));
        $standbyAgentuuid = $standbyData[0]['takeover_standby_agent_uuid'];
        $takeoverAgentType = $standbyData[0]['takeover_vm_hypervisor'];
        $takeoverVmConfig = $standbyData[0]['takeover_vm_config'];

        $config = json_decode($takeoverVmConfig);
        $businessNicSet = $config->vm_interfaces_nic_set;
        if($takeoverAgentType == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){
            $updateNetCard = $this->updateHostNetCardinf($standbyAgentuuid);
            if($updateNetCard){
                $standbyNicList = $this->getHostNetworkInfo($standbyAgentuuid);
            }
        }else if($takeoverAgentType!= Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){  //内嵌虚拟机
            // $standbyNicList = $this->getTempAgentConfigInfo($standbyAgentuuid);
            $standbyNicList = $businessNicSet;
        }
        
        $cdpVolTaskTakeoverInfo = array(
            'stand_agent_uuid' => $standbyAgentuuid,
            'standby_nic_list' =>$standbyNicList,
            'takeover_agent_type' => $takeoverAgentType,
        );
        return $cdpVolTaskTakeoverInfo;
    }

    /**
     * 获取模板主机配置信息
     * @return void
     */
    private function getTempAgentConfigInfo($uuid){
        $sql = "select config from vm_emd where uuid = ?";
        $data = $this->dbSelect($sql,array($uuid));
        $config = json_decode($data[0]['config']);
        $businessNicSet = $config->special->business_nic_set;
        return $businessNicSet;
    }
    /**
     * 根据指定客户端网卡信息
     */
    public function updateHostNetCardinf($agentuuid){
        $refreshAgentSet = array();
        $refreshAgentSet['agent_uuid'] = $agentuuid;
        $refreshAgentSet['app_uuid_set'] = [];
        
        $refreshSet= array($refreshAgentSet);
        $pfMSg = array();
        $pfMSg['refresh_agent_set'] = $refreshSet;
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getvCenterUUID();  //获取主节点UUID
        $opName = 'VOL_CDP_TASK_SCANNING_NIC_INFO';
        $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg);
        
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $pfOpcode = Xphp::instance('VolCDPOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        
        //返回结果到UI
        if($result){
            return true;
        }else{
            return false;
        }
    }
    
    /**
     * 获取客户端对应的网卡信息
     * @param unknown $params
     */
    private function getHostNetworkInfo($params){
        $sql = "select detail from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql,array($params));
        $detail = json_decode($data[0]['detail']);
        $nicList = $detail->nic_list;
        return $nicList;
    }
    /**
     * 修改任务回切网络映射
     */
    public function modifyTaskTakeOverNetwork($params){
        $pfMSg = array();
        $task_uuid = $params['task_uuid'];
        $pfMSg['task_uuid'] =$task_uuid;
        $pfMSg['takeover_business_ip_map'] = $params['takeover_business_ip_map'];
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeUuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);   //任务UUID对应的存储节点uuid
        
        $opName = 'VOL_CDP_TASK_OP_TAKEOVER_CONFIG';
        $jobHandler = Xphp::instance('JobHandler');
        //检查是否有操作权限
        $jobHandler->checkOpPermission($task_uuid, $opName);
        
        $mbResult = $this->mbVolCdpMsg($nodeUuid, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        
        $pfOpcode = Xphp::instance('VolCDPOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 自动接管是否启用控制
     * @param $params
     * @return string
     */
    public function autoTakeoverOperation($params){
        $pfMSg = array();
        $task_uuid = $params['task_uuid'];
        $autoTakeoverEnableFlag = $params['enableFlag'];
        if($autoTakeoverEnableFlag==Xphp::$_config['FLAG']['SET']){
            $operate = Xphp::$_lang['UI_VOL_CDP_ENABLE_AUTO_TAKEOVER'];
        }else if($autoTakeoverEnableFlag==Xphp::$_config['FLAG']['UNSET']){
            $operate = Xphp::$_lang['UI_VOL_CDP_DISABLED_AUTO_TAKEOVER'];
        }
        $opName = "VOL_CDP_TASK_OP_AUTO_TAKEOVER_SET";  //自动接管设置，已配置可以禁用，禁用中可以启用
        $jobHandler = Xphp::instance('JobHandler');
        //检查是否有操作权限
        $jobHandler->checkOpPermission($task_uuid, $operate);
        $msg = array(
            'task_uuid' => $task_uuid,
            'auto_takeover_enable_flag'=> $autoTakeoverEnableFlag
        );
        $msg = json_encode($msg);
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);   //任务UUID对应的存储节点uuid
        $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg, FALSE, TRUE);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $pfOpcode = Xphp::instance('VolCDPOpcode');
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 得到添加存储的时候的可用节点列表,同时用户备份选择存储节点(新建备份任务,修改备份任务,系统配置等等)
     * @param unknown $params
     */
    public function getStorageNodeSelect($params){
        $failBackTaskStorageUuid = $params['default_storage_uuid'];
        $sql = "select ip, node_uuid, host_name, node_nickname, node_type from bd_node order by node_type";
        $data = $this->dbSelect($sql, array());
        $softwareType = Xphp::instance('SystemHandler', 'getSoftwareType');
        $list = array();
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty($_SESSION['tenantuuid'])){
            $resourceHandler = Xphp::instance('ResourceHandler');
            $resourceInfo = $resourceHandler->pGetUserAllResource(Xphp::$_user['useruuid'], Xphp::$_config['RESOURCE_TYPE']['NODE']);
            if(!empty($resourceInfo)){
                foreach ($resourceInfo as $r){
                    $resourceList[] = $r['resource_uuid'];
                }
            }
        }
        foreach ($data as $d){
            //如果是租户内部检查是否有该资源
            if(!empty($_SESSION['tenantuuid']) && !in_array($d['node_uuid'], $resourceList)) continue;

            if($softwareType == Xphp::$_config['SOFTWARE_VERSION']['EN_FREE_EDITION'] || $softwareType == Xphp::$_config['SOFTWARE_VERSION']['STANDARD_EN'] ||
                $softwareType == Xphp::$_config['SOFTWARE_VERSION']['ESSENTIAL_EN']){
                if(intval($d['node_type']) != Xphp::$_config['FLAG']['SET']) continue;
            }
            $nodeStatus = $this->getNodeAllStatus($d['node_uuid']);
            $isSelected = false;
            if($failBackTaskStorageUuid!=""){
                $locationNode = $this->storageLocationNode($failBackTaskStorageUuid);
                if($d['node_uuid']==$locationNode){
                    $isSelected = true;
                }
            }
            if($nodeStatus['flag']){
                //如果节点状态正常
                $list[] = array(
                    'uuid' => $d['node_uuid'],
                    'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                    'type' => intval($d['node_type']),
                    'select' => $isSelected
                );
            }
        }
        return json_encode($list);
    }

    /**
     * 得到节点展示名称
     * @param string $ip
     * @param string $nickname
     * @param string $hostname
     */
    private function getNodeShowName($ip, $nickname, $hostname){
        if(empty($ip) && empty($nickname) && empty($hostname)){
            return "--";
        }
        //如果IP和昵称一样,显示主机名+IP,否则,显示昵称+IP
        if($ip == $nickname || empty($nickname)){
            $name = $hostname . '(' . $ip . ')';
        }else{
            $name = $nickname . '(' . $ip . ')';
        }
        return $name;
    }
    /**
     * 获取指定存储所在节点
     */
    private function storageLocationNode($storageUuid){
        $sql = "select node_uuid from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageUuid));
        $nodeuuid = "";
        if(!empty($data)){
            $nodeuuid = $data[0]['node_uuid'];
        }
        return $nodeuuid;
    }

    /**
     * 得到节点总状态(存储也要调用,所以PUBLIC)
     * @param string $nodeuuid
     * @return array('flag'=> boolean, 'module'=>array(modules))
     */
    private function getNodeAllStatus($nodeuuid){
        $sql = "select module_type, online_flag from bd_module_server where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $flag = true;
        $module = array();
        foreach ($data as $d){
            if($d['online_flag'] == Xphp::$_config['FLAG']['UNSET']){
                $flag = false;
                $module[] = $d['module_type'];
            }
        }
        if(empty($data)) $flag = false; //如果没有记录
        $info = array(
            'flag' => $flag,
            'module' => $module
        );
        return $info;
    }

    /**
     * 将选中的客户端对应的卷信息进行组装（从JS移到PHP处理是为了满足全选需求）
     * @param $params
     * @return false|string
     */
    public function getDataSourceVolInfo($params){
        $data = $params['volinfo'];
        $start = $params['start'];
        $length = $params['length'];
        $volumesInfo = array();
        $i = 0;
        foreach ($data as $d){
            $voluuid = $d['vol_uuid'];
            $volName = $d['vol_name'];
            $capacity = $d['capacity'];
            $osType = $d['os_type'];
            $isBoot = $d['is_boot'];
            $isSysVolume = $d['system_volume'];
            $timePooint = $d['new_timestamp'];
            $timeIcon = '<img src ="./img/platform/timepoint.png"> ';
            $volIcon = '<img src ="./img/vm/pool.png"> ';
            $standbyVol = $d['standby_vol'];
            $targetInfo = '<input type ="text" readonly class = "form-control input-sm" name = "mount_target" id = "mount_target_'.$voluuid.'" value ='.Xphp::$_lang['WEB_VOL_CDP_TAKEOVER_AUTO_ALLOCATE'].'>';
            $volumesInfo[] = array(
                '<input type="checkbox" class="editor-active" name="id[]"  id="takeover_id_'.$i.'" value="'.$voluuid.'">',
                $volIcon.$volName,
                $capacity,
                $timeIcon.'<span id = "takeover_time_'.$voluuid.'">'.$timePooint.'</span>',
                $targetInfo,
                $timePooint,
                $voluuid,
                $volName,
                $osType,
                $isBoot,
                $isSysVolume,
            );
            $i+=1;
        }
        $sliceArray = array_slice($volumesInfo,$start,$length,false);
        $volumes['data'] = $sliceArray;

        $countNum = count($volumesInfo);
        $volumes["draw"] = $params['draw'];
        $volumes["recordsTotal"] = $countNum;
        $volumes["recordsFiltered"] = $countNum;
        return  json_encode($volumes);
    }
}
?>