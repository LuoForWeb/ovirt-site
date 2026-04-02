<?php
/******************************************* 
 ** 卷CDP任务处理类 
 ** @author       jiangyongjie@vinchin.com
 ** @date         2021-11-19 17:06:44
 ** @version      1.0.0
 ** @copyright    Copyright 2021 vinchin.com
 ********************************************/
class VolCDPTaskHandler extends BLLHandler{
    /**
     * 获取备份模块卷/应用信息详情
     * @param array $params
     * @return json string
     */
    public function getBackupTaskVolInfo($params){
        $utils = Xphp::instance('Utils');
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $start = $params['start'];
        $taskType = $params['task_type'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $taskCurrentStage = $params['task_current_stage'];

        $sql = "select DISTINCT vol_task.master_agent_uuid,vol_task.standby_agent_uuid,takeover.takeover_standby_agent_uuid,vol_task.rebuild_partition_flag, 
                vol.vol_uuid,vol.standby_target_vol_uuid,task.task_type,task.task_status, vol_task.current_task_running_stage,
                vol.takeover_failback_target_vol_uuid, vol_task.auto_takeover_flag,vol_task.mirror_backup_flag,vol.recovery_target_disk_uuid 
                from bd_task as task 
                    LEFT JOIN cdp_vol_task as vol_task on vol_task.task_uuid = task.task_uuid 
                    LEFT JOIN cdp_vol_task_vol as vol on vol.task_uuid = task.task_uuid 
                    LEFT JOIN cdp_vol_task_takeover_info as takeover on takeover.task_uuid = task.task_uuid 
                where task.task_uuid =?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        $records["data"] = array();
        foreach ($data as $d){
            $standbyAgentuuid = $d['standby_agent_uuid'];
            $takeoverStandbyAgentuuid = $d['takeover_standby_agent_uuid'];
            $taskCurrentStage = $d['current_task_running_stage'];
            $takeoverFailbackTargetVoluuid = $d['takeover_failback_target_vol_uuid'];
            $masterAgentInfo = $this->getBdAgentInfo($d['master_agent_uuid']);
            $standbyAgentInfo = $this->getBdAgentInfo($d['standby_agent_uuid']);
            $masterAgentName = $masterAgentInfo['agentInfo'];
            $standbyAgent = $standbyAgentInfo['agentInfo'];

            $volUUID = $d['vol_uuid'];
            $taskstandbyTargetVoluuid = $d['standby_target_vol_uuid'];
            $volInfo = $this->getVolInfo($volUUID);
            $standbyVolInfoObj = $this->getVolInfo($taskstandbyTargetVoluuid);

            $volDisplayName = $volInfo['vol_name'];
            $standbyVolInfo = $standbyVolInfoObj['vol_name'];

            $taskRunningStage = $d['current_task_running_stage'];
            $rebuildPartitionFlag = $d['rebuild_partition_flag'];

            $taskRunningCapacityInfo = $this->getTaskRunningCapacityInfo($taskUUID,$taskRunningStage,$volUUID,$taskType,$rebuildPartitionFlag);//卷有效数据运行情况
            $completed = $taskRunningCapacityInfo['completed_size'];
            if($completed== Xphp::$_config['NULLSPACE']){
                $completed = "0B";
            }
            $transferSize = $taskRunningCapacityInfo['transport_size'];
            $writeInSize = $taskRunningCapacityInfo['write_size'];
            $taskValidCapacityInfo = $taskRunningCapacityInfo['volume_valid_info'];
            $volumeInfo = $taskRunningCapacityInfo['volume_info'];
            $capacity = $volInfo['capacity'];
            $capacityStr = $utils->calSize($capacity, true);
            $capacityInfo = $capacityStr."/".$completed;
            $taskType = $d['task_type'];

            $autoTakeoverFlag = $d['auto_takeover_flag'];
            $mirrorBackupFlag = $d['mirror_backup_flag'];

            if($autoTakeoverFlag==Xphp::$_config['FLAG']['SET'] && $rebuildPartitionFlag == Xphp::$_config['FLAG']['UNSET']){  //已配置自动接管
                $autoTakeoverInfo = $this->getAutoTakeoverVolInfo($taskUUID,$volUUID);
                $standbyAgent  = $autoTakeoverInfo['standby_agent'];

                $standbyVolInfo = $autoTakeoverInfo['mount_point'];
                $standbyRealMountPoint = $autoTakeoverInfo['$autoTakeoverInfo'];
                $standbyTargetVoluuid = $autoTakeoverInfo['standby_target_vol_uuid'];
                if($standbyVolInfo=="" ){
                    $standbyVolInfo = Xphp::$_lang['WEB_VOL_CDP_TAKEOVER_AUTO_ALLOCATE'];
                }
            }
            $failbackInfo = array();
            if($taskCurrentStage== $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC'] ||
                $taskCurrentStage== $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC'] ||
                $taskCurrentStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']){ //任务处于回切阶段
                    // $failbackInfo = $this->getTaskFailbackHostInfo($taskUUID,$volUUID);
                    // $failbackTargetHostuuid = $failbackInfo['target_host_uuid'];  //回切目标主机uuid
                    // $standbyAgentInfo = $this->getBdAgentInfo($takeoverStandbyAgentuuid);  //暂时展示接管备机的客户端信息
                    // $standbyAgent = $standbyAgentInfo['agentInfo'];
                    // 和后台同事商讨确定，回切应用获取接管备机的客户端和对应卷信息
                    $taskAppInfo = $this->getFailbackAppTree($taskUUID,$taskstandbyTargetVoluuid,$takeoverStandbyAgentuuid);

            }else{
                $taskAppInfo = $this->getBackuptaskHostAppTree($taskUUID,$volUUID);
            }

            $records["data"][] = array(
                $i++,
                $masterAgentName,
                $volDisplayName,
                $volumeInfo,
                $taskValidCapacityInfo,  //卷有效数据运行情况
                $transferSize,
                $writeInSize,
                $standbyAgent,
                $standbyVolInfo,
                $d['task_type'],
                $d['task_status'], //10
                $taskAppInfo,
                $d['vol_uuid'],
                $capacityStr,
                $d['takeover_failback_target_vol_uuid'],  //回切目标卷UUID，未配置为空 14
                $capacity,
                '',  // 占位
                '',  // 占位
                $d['recovery_target_disk_uuid'],  // 18 回切目标磁盘UUID，LiveCD开启配置重建分区开启才有
                $taskstandbyTargetVoluuid,
                $taskCurrentStage,
                $failbackInfo
            );
        }
        $records["draw"] = $params['draw'];
        return  json_encode($records);
    }
    /**
     * 获取备份任务回切目标机及卷信息
     * @param unknown $taskuuid
     */
    private  function getTaskFailbackHostInfo ($taskuuid,$voluuid){
        $sql = "select  vol.takeover_failback_target_vol_uuid,failback.failback_target_agent_uuid,vol.recovery_target_disk_uuid
                from cdp_vol_task_vol vol,cdp_vol_task_takeover_failback_info failback 
                where  failback.task_uuid = vol.task_uuid and failback.task_uuid = ? and vol.vol_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid,$voluuid));
        $failbackInfo = array();
        if(!empty($data)){
            $failbackTargetAgentuuid = $data[0]['failback_target_agent_uuid'];
            $takeoverFailbackTargetVoluuid = $data[0]['takeover_failback_target_vol_uuid'];
            $recoveryTargetDiskuuid = $data[0]['recovery_target_disk_uuid'];

            $targetVol = $this->getAgentVol($takeoverFailbackTargetVoluuid);
            $agentInfo = $this->getBdAgentInfo($failbackTargetAgentuuid);
            $targetDisk = $this->getRecoverTargetDisk($recoveryTargetDiskuuid);
            $agentName = $agentInfo['agentInfo']; ;
            $failbackInfo = array(
                'agent_info' => $agentName,
                'target_vol' => $targetVol,
                'target_disk' => $targetDisk,
                'target_host_uuid' => $failbackTargetAgentuuid,
            );
        }else{
            $failbackInfo = array(
                'agent_info' => "--",
                'target_vol' => "--",
                'target_disk' => "--",
                'target_host_uuid' => "--",
            );
        }
        return $failbackInfo;
    }

    /**
     * 获取自动接管配置的备机及挂载点信息
     * @param unknown $taskuuid
     */
    private function getAutoTakeoverVolInfo($taskuuid,$voluuid){
        $sql = "select ti.takeover_standby_agent_uuid,vol.takeover_target_mount_point,vol.standby_target_vol_uuid 
            from cdp_vol_task_vol vol,cdp_vol_task_takeover_info ti 
            where ti.task_uuid = vol.task_uuid and ti.task_uuid = ? and vol.vol_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid,$voluuid));
        $standbyAgentUuid = $data[0]['takeover_standby_agent_uuid'];
        $standbyAgent = $this-> getHostInfo($standbyAgentUuid);
        $standbyTargetVoluuid = $data[0]['standby_target_vol_uuid'];
        $takeoverStandbyRealMountPoint = $data[0]['takeover_standby_real_mount_point'];
        $dataArray = array(
            'standby_agent' => $standbyAgent,
            'mount_point' => $data[0]['takeover_target_mount_point'],
            'standby_target_vol_uuid' => $standbyTargetVoluuid,
            'standby_real_mount_point' => $takeoverStandbyRealMountPoint
        );
        return  $dataArray;
    }
    /**
     * 获取恢复模块卷信息详情
     * @param array $params
     * @return json string
     */
    public function getRecoverTaskVolInfo($params){
        $utils = Xphp::instance('Utils');
        $start = $params['start'];
        $taskType = $params['task_type'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $sql = "select vol_task.master_agent_uuid,vol_task.standby_agent_uuid,vol.vol_uuid,vol.standby_target_vol_uuid,vol_task.rebuild_partition_flag,
                    task.task_type,task.task_status,vol_task.recovery_target_agent_uuid,vol_task.recovery_data_source,vol.recovery_target_disk_uuid,
                    vol.recovery_target_vol_uuid,vol_task.recovery_type,vol.recovery_target_timestamp,vol_task.current_task_running_stage
                from bd_task as task
                    LEFT JOIN cdp_vol_task as vol_task on vol_task.task_uuid = task.task_uuid
                    LEFT JOIN cdp_vol_task_vol as vol on vol.task_uuid = task.task_uuid
                where task.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        $records["data"] = array();
        foreach ($data as $d){
            $taskCurrentStage = $d['current_task_running_stage'];
            $recoveryTargetInfo = $this->getBdAgentInfo($d['recovery_target_agent_uuid']); //恢复目标
            $recoveryTargetName = $recoveryTargetInfo['agentInfo'];
            $dataSourceHost = $this->getDataSourceHosstInfo($d['master_agent_uuid']);
            $volUUID = $d['vol_uuid'];
            $timepointStr = $d['recovery_target_timestamp'];
            $dataSourceInfo = $this->getDataSourceVolInfo($volUUID,$timepointStr);               //恢复数据源卷
            $volName = $dataSourceInfo['vol_name'];
            $volDisplayName = $dataSourceInfo['vol_display_name'];
            $capacity = $dataSourceInfo['capacity'];
            $targetVol = $this->getAgentVol($d['recovery_target_vol_uuid']);    //恢复目标卷
            $targetDisk = $this->getRecoverTargetDisk($d['recovery_target_disk_uuid']);  //恢复磁盘
            $targetInfo = $targetVol;

            if($d['rebuild_partition_flag']==1){
                $targetInfo = $targetDisk;
            }
            $taskRunningStage = $d['current_task_running_stage'];
            $rebuildPartitionFlag = $d['rebuild_partition_flag'];  //重建分区

            $taskRunningCapacityInfo = $this->getTaskRunningCapacityInfo($taskUUID,$taskRunningStage,$volUUID,$taskType,$d['rebuild_partition_flag']);//卷有效数据运行情况
            $completed = $taskRunningCapacityInfo['completed_size'];
            $transferSize = $taskRunningCapacityInfo['transport_size'];
            $writeInSize = $taskRunningCapacityInfo['write_size'];
            $taskValidCapacityInfo = $taskRunningCapacityInfo['volume_valid_info'];
            $volumeInfo = $taskRunningCapacityInfo['volume_info'];
            $capacityInfo = $utils->calSize($capacity)."/".$completed;

            $timePointDesc = $this->getTimePointDescription($taskUUID,$timepointStr,$volUUID);
            $records["data"][] = array(
                $i++,
                $dataSourceHost,
                $volDisplayName,
                $volumeInfo,
                $taskValidCapacityInfo,
                $transferSize,
                $d['recovery_target_timestamp'],
                $recoveryTargetName,
                $targetInfo,
                $d['task_type'],
                $d['task_status'],
                $timepointStr,
                $timePointDesc,
            );
        }
        $records["draw"] = $params['draw'];
        return  json_encode($records);
    }
    /**
     * 获取接管任务对应卷及应用信息
     * @param array $params
     * @return json string
     */
    public function getTakeoverTaskVolInfo($params){
        $utils = Xphp::instance('Utils');
        $start = $params['start'];
        $taskType = $params['task_type'];
        $length = $params['length'];
        $draw = $params['draw'];
        $taskUUID = $params['uuid'];
        $taskCurrentStage = 0; //任务阶段
        $taskCurrentStage = $params['task_current_stage'];

        $sql = "select DISTINCT vol_task.master_agent_uuid,vol.vol_uuid,vol.standby_target_vol_uuid,task.task_type,
                    task.task_status,vol_task.recovery_type,vol_task.current_task_running_stage,
                    vol_task.rebuild_partition_flag,takeover.takeover_timestamp,target.id,
                    takeover.takeover_standby_agent_uuid,vol.takeover_target_mount_point,
                    vol.takeover_failback_target_vol_uuid,vol.takeover_standby_real_mount_point,
                    takeover.app_takeover_flag,vol.recovery_target_disk_uuid,takeover.takeover_vm_hypervisor 
    			FROM bd_task as task
        			LEFT JOIN cdp_vol_task as vol_task on vol_task.task_uuid = task.task_uuid
        			LEFT JOIN cdp_vol_task_vol as vol on vol.task_uuid = task.task_uuid
        			LEFT JOIN cdp_vol_task_takeover_target as target on target.task_uuid = task.task_uuid
        			LEFT JOIN cdp_vol_task_takeover_info as takeover on takeover.task_uuid = task.task_uuid
    			WHERE task.task_uuid = ? GROUP BY vol.vol_uuid";
        $data = $this->dbSelect($sql, array($taskUUID));
        $records = array();
        $i = 1;
        $records["data"] = array();
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        foreach ($data as $d){
            $taskCurrentStage = $d['current_task_running_stage'];
            $takeoverStandbyAgentuuid = $d['takeover_standby_agent_uuid'];
            $takeoverTargetInfo = $this->getBdAgentInfo($d['takeover_standby_agent_uuid'],$d['vm_hypervisor_type']); //接管目标机器
            $takeoverTargetName = $takeoverTargetInfo['agentInfo'];
            $dataSourceHost = $this->getDataSourceHosstInfo($d['master_agent_uuid']);
            $volUUID = $d['vol_uuid'];
            $failbackVoluuid = $d['takeover_failback_target_vol_uuid'];
            $standbyTargetVoluuid = $d['standby_target_vol_uuid'];
            $takeoverTimeStr = $d['takeover_timestamp'];
            $dataSourceInfo = $this->getDataSourceVolInfo($volUUID,$takeoverTimeStr); //接管数据源卷
            $volName = $dataSourceInfo['vol_name'];
            $volDisplayName = $dataSourceInfo['vol_display_name'];
            $capacity = $dataSourceInfo['capacity'];
            $taskRunningStage = $d['current_task_running_stage'];


            $takeoverMountPoint = $d['takeover_target_mount_point'];
            $takeoverRealMountPoint = $d['takeover_standby_real_mount_point'];
            $appTakeoverFlag = $d['app_takeover_flag'];

            $takeoverMountPointStr = Xphp::$_lang['WEB_VOL_CDP_TAKEOVER_AUTO_ALLOCATE'];
            $takeoverRealMountPointStr = "--";
            if($takeoverRealMountPoint){
                $takeoverRealMountPointStr = $takeoverRealMountPoint;
            }
            if($takeoverMountPoint){
                $takeoverMountPointStr = $takeoverMountPoint;
            }
            $rebuildPartitionFlag = $d['rebuild_partition_flag'];
            $taskRunningCapacityInfo = $this->getTaskRunningCapacityInfo($taskUUID,$taskRunningStage,$volUUID,$taskType,$rebuildPartitionFlag);//卷有效数据运行情况
            $completed = $taskRunningCapacityInfo['completed_size'];
            $transferSize = $taskRunningCapacityInfo['transport_size'];
            $writeInSize = $taskRunningCapacityInfo['write_size'];
            $taskValidCapacityInfo = $taskRunningCapacityInfo['volume_valid_info'];
            $volumeInfo = $taskRunningCapacityInfo['volume_info'];

            $capacityStr = $utils->calSize($capacity, true);
            if($completed== Xphp::$_config['NULLSPACE']){
                $completed = "0B";
            }
            $capacityInfo = $capacityStr;
            $timePointDesc = $this->getTimePointDescription($taskUUID,$takeoverTimeStr,$volUUID);
            $failbackInfo = array();
            if($appTakeoverFlag == Xphp::$_config['FLAG']['SET']){
                if($taskCurrentStage== $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC']
                    || $taskCurrentStage== $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']
                    || $taskCurrentStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']){ //任务处于回切阶段

                    $failbackInfo = $this->getTaskFailbackHostInfo($taskUUID,$volUUID);
                    $taskAppTree = $this->getFailbackAppTree($taskUUID,$standbyTargetVoluuid,$takeoverStandbyAgentuuid);
                }else{
                    $taskAppTree = $this->getTakeoverAppTree($volUUID,$d['master_agent_uuid'],$takeoverTimeStr,$d['takeover_standby_agent_uuid'],$taskRunningStage,$taskUUID);
                }

                if($taskAppTree){
                    $configApp = Xphp::$_lang['WEB_DRILLS_YES'];
                }else{
                    $configApp = Xphp::$_lang['WEB_DRILLS_NO'];
                }
            }else{
                $taskAppTree = array();
                $configApp = Xphp::$_lang['WEB_DRILLS_NO'];
            }

            $taskRunningCapacityInfo = $this->getTaskRunningCapacityInfo($taskUUID,$taskRunningStage,$volUUID,$taskType,$rebuildPartitionFlag);//卷有效数据运行情况
            $completed = $taskRunningCapacityInfo['completed_size'];
            if($completed== Xphp::$_config['NULLSPACE']){
                $completed = "0B";
            }
            $transferSize = $taskRunningCapacityInfo['transport_size'];
            $writeInSize = $taskRunningCapacityInfo['write_size'];
            $taskValidCapacityInfo = $taskRunningCapacityInfo['volume_valid_info'];
            $volumeInfo = $taskRunningCapacityInfo['volume_info'];

            $tab5 = $takeoverTimeStr;  //表格第5列数据，在接管状态获取接管时间
            $tab6 = $configApp;     //表格第6列数据，在接管状态获取是否配置应用

            if($taskCurrentStage== $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC']
                || $taskCurrentStage== $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']
                || $taskCurrentStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']){ //任务处于回切阶段
                $volCapacityInfo = $volumeInfo;
                $volValidCapacityInfo = $taskValidCapacityInfo;
                $tab5 = $transferSize;  //表格第5列数据，在回切状态下获取传输字节大小
                $tab6 = $writeInSize;  //表格第6列数据，在回切状态下获取写入字节大小
            }else{
                $volCapacityInfo = $capacityInfo;
                $volValidCapacityInfo = $taskValidCapacityInfo;
            }
            $records["data"][] = array(
                $i++,
                $dataSourceHost,
                $volDisplayName,
                $volCapacityInfo,
                $volValidCapacityInfo,
                $tab5,
                $tab6,
                $takeoverTargetName,
                $takeoverMountPointStr." / ".$takeoverRealMountPointStr,
                $d['task_type'],
                $d['task_status'], //10
                $taskAppTree,
                $d['vol_uuid'],
                $capacityStr,
                $takeoverTimeStr,
                $d['takeover_failback_target_vol_uuid'],  //15回切目标卷，未配置为空
                $timePointDesc,
                $capacity,
                $d['recovery_target_disk_uuid'],  // 18 回切目标磁盘UUID，LiveCD开启配置重建分区开启才有
                $taskCurrentStage,
                $failbackInfo,
            );
        }
        $records["draw"] = $params['draw'];
        return  json_encode($records);
    }
    /**
     * 获取恢复时间点描述信息，分别解析标签点备注和事件信息
     * @param string $taskuuid
     * @param string $timepointStr
     * @param string $voluuid
     * @return string timepointStr
     */
    private function getTimePointDescription($taskuuid,$timepointStr,$voluuid){
        $sql = "SELECT vol_set.backup_agent_id 
                from cdp_vol_task_vol vol,cdp_vol_backup_vol_set as vol_set 
                where vol.task_uuid = ? 
                    and vol.vol_uuid=? 
                    and vol.recovery_target_timestamp = ? 
                    and vol.vol_uuid = vol_set.vol_uuid 
                    and unix_timestamp(vol.recovery_target_timestamp) 
                    BETWEEN unix_timestamp(vol_set.start_timestamp) AND unix_timestamp(vol_set.end_timestamp)";
        $data = $this->dbSelect($sql,array($taskuuid,$voluuid,$timepointStr));
        $timepointDesc =Xphp::$_lang['UI_VOL_CDP_ANY_TIME_POINT'];
        if(!empty($data)){
            $backupAgentId = $data[0]['backup_agent_id'];
            $labelSql = "select remarks from  cdp_vol_agent_label_set where backup_agent_id = ? and label_timestamp = ?";
            $labelData = $this->dbSelect($labelSql,array($backupAgentId,$timepointStr));
            $labelTimePointStr = '';
            if(!empty($labelData)){
                $labelTimePointStr = Xphp::$_lang['UI_VOL_CDP_LABEL_POINTS']."：".$labelData[0]['remarks'];
            }
            $eventSql = "select description_key,description_param from cdp_vol_agent_event_info where backup_agent_id = ? and event_time = ?";
            $eventData = $this->dbSelect($eventSql,array($backupAgentId,$timepointStr));
            $eventInfoStr = '';
            if(!empty($eventData)){
                $backupSetHandler = Xphp::instance('VolCDPBackupSetHandler');
                $eventInfoStr =Xphp::$_lang['UI_VOL_CDP_EVENT_INFO']."，". $backupSetHandler->getEventDesriptionNotice($eventData[0]['description_key'],$eventData[0]['description_param']);
            }
            if(!empty($labelData) || !empty($eventData)){
                $timepointDesc = $labelTimePointStr."<br>".$eventInfoStr;
            }

        }
        return $timepointDesc;
    }
    /**
     * 获取任务在各个运行阶段下的容量执行信息
     * @param unknown $taskUUID
     * @param unknown $taskRunningStage
     */
    private function getTaskRunningCapacityInfo($taskUUID,$taskRunningStage,$volUUID,$taskType,$rebuildPartFlag){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $utils = Xphp::instance('Utils');
        $volumeValidDataInfo = "--";
        $taskRunningCapacityInfo = array();
        $taskRunningCapacityInfo = array(
            'completed_size' => '--',
            'volume_valid_info' => '--',
            'write_size' => '--',
            'transport_size' => '--',
            'volume_info' => '--'
        );
        $sql = "select total_size,completed_size,valid_size,completed_valid_size,write_size,transport_size
                from cdp_vol_task_progress_info
                where task_uuid = ? and vol_uuid = ?";
        $data = $this->dbSelect($sql,array($taskUUID,$volUUID));
        if(!empty($data)){
            //任务阶段处于 逆向初始同步,回切,初始同步
            switch ($taskType){
                case Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']:
                case Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION']:
                    if($taskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC']
                        || $taskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']
                        || $taskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['INIT_SYNC']){
                        $totalSize = $data[0]['total_size'];
                        $totalSizeStr = $utils->calSize($totalSize, true);

                        $validSize = $data[0]['valid_size'];
                        $validSizeStr = $utils->calSize($validSize, true);
                        $completedValidSize = $data[0]['completed_valid_size'];
                        $completedValidFlag = true;
                        if($completedValidSize==0 || $validSize==0){
                            $initialSynCapacity = 0;
                        }else{
                            $initialSynCapacity = $data[0]['total_size']*($completedValidSize/$validSize);//初始同步容量采用的总容量*有效数据百分比来获取，确保进度平滑
                        }
                        $completedValidStr = $utils->calSize($completedValidSize, $this->verifyValueFalg($completedValidSize));
                        if($completedValidStr==Xphp::$_config['NULLSPACE']){
                            $completedValidStr = "0B";
                        }
                        $volumeValidDataInfo = $validSizeStr."/".$completedValidStr;

                        $completedValidSizeStr = $utils->calSize($initialSynCapacity, $this->verifyValueFalg($data[0]['completed_valid_size']));
                        if($completedValidSizeStr == Xphp::$_config['NULLSPACE']){
                            $completedValidSizeStr = "0B";
                        }
                        $volumeInfo = $totalSizeStr." / ".$completedValidSizeStr;

                        $taskRunningCapacityInfo = array(
                            'completed_size' => $utils->calSize($data[0]['completed_size'], $this->verifyValueFalg($data[0]['completed_size'])),
                            'volume_valid_info' =>$volumeValidDataInfo,
                            'write_size' => $utils->calSize($data[0]['write_size'], $this->verifyValueFalg($data[0]['write_size'])),
                            'transport_size' => $utils->calSize($data[0]['transport_size'], $this->verifyValueFalg($data[0]['transport_size'])),
                            'volume_info' =>$volumeInfo
                        );
                    }else if($taskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['REALTIME_SYNC']
                        || $taskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['WAIT_CONVERT_TO_REALTIME_SYNC']
                        || $taskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['SERVER_CONS_CHECK']
                        || $taskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC'] 
                        || $taskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['REALTIME_CONSISTENCY_CHECK']){ //任务阶段处于实时同或数据一致性校验阶段，回切实时同步阶段
                        $totalSize = $data[0]['total_size'];
                        $validSize = $data[0]['valid_size'];

                        $completedValidSize = $data[0]['completed_valid_size'];

                        $completedSize = $data[0]['completed_size'];

                        $writeSize = $data[0]['write_size'];
                        $transportSize = $data[0]['transport_size'];
                        $totalSizeStr = $utils->calSize($totalSize, $this->verifyValueFalg($totalSize));
                        $validSizeStr = $utils->calSize($validSize, $this->verifyValueFalg($validSize));
                        if($validSizeStr==Xphp::$_config['NULLSPACE']){
                            $validSizeStr = "0B";
                        }
                        if($totalSizeStr==Xphp::$_config['NULLSPACE']){
                            $totalSizeStr = "0B";
                        }
                        $completedValidStr = $utils->calSize($completedValidSize, $this->verifyValueFalg($completedValidSize));
                        $volumeInfo = $totalSizeStr." / ".$validSizeStr;

                        $taskRunningCapacityInfo = array(
                            'completed_size' => $utils->calSize($completedSize, $this->verifyValueFalg($completedSize)),
                            'volume_valid_info' =>$completedValidStr,
                            'write_size' => $utils->calSize($writeSize, $this->verifyValueFalg($writeSize)),
                            'transport_size' => $utils->calSize($transportSize, $this->verifyValueFalg($transportSize)),
                            'volume_info' =>$volumeInfo
                        );
                    }
                    break;
                case Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']:
                    $totalSize = $data[0]['total_size'];
                    $totalSizeStr = $utils->calSize($totalSize, $this->verifyValueFalg($totalSize));

                    $validSize = $data[0]['valid_size'];
                    $validSizeStr = $utils->calSize($validSize, $this->verifyValueFalg($validSize));
                    $completedSize = $data[0]['completed_size'];

                    $completedValidSize = $data[0]['completed_valid_size'];
                    if($completedValidSize==0 || $validSize ==0){
                        $initialSynCapacity = 0;
                    }else{
                        $initialSynCapacity = $totalSize *($completedValidSize/$validSize);//初始同步容量采用的总容量*有效数据百分比来获取，确保进度平滑
                    }

                    $completedValidStr = $utils->calSize($completedValidSize, $this->verifyValueFalg($completedValidSize));
                    if($validSizeStr==Xphp::$_config['NULLSPACE']){
                        $validSizeStr = "0B";
                    }
                    if($completedValidStr == Xphp::$_config['NULLSPACE']){
                        $completedValidStr = "0B";
                    }
                    $volumeValidDataInfo = $validSizeStr."/".$completedValidStr;
                    if($totalSizeStr==Xphp::$_config['NULLSPACE']){
                        $totalSizeStr = "0B";
                    }

                    $volumeInfo = $totalSizeStr." / ".$utils->calSize($initialSynCapacity, $this->verifyValueFalg($initialSynCapacity));
                    if($completedValidSize==0 || $validSize==0){
                        $volumeInfo = $totalSizeStr." / 0B";
                    }
                    $writeSize = $data[0]['write_size'];
                    $transportSize = $data[0]['transport_size'];
                    $taskRunningCapacityInfo = array(
                        'completed_size' => $utils->calSize($completedSize, $this->verifyValueFalg($completedSize)),
                        'volume_valid_info' =>$volumeValidDataInfo,
                        'write_size' => $utils->calSize($writeSize, $this->verifyValueFalg($writeSize)),
                        'transport_size' => $utils->calSize($transportSize, $this->verifyValueFalg($transportSize)),
                        'volume_info' =>$volumeInfo
                    );
                    break;
                case Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']:
                    if($taskRunningStage==$VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC'] || $taskRunningStage ==$VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']){
                        $totalSize = $data[0]['total_size'];
                        $totalSizeStr = $utils->calSize($totalSize, true);
                        $validSize = $data[0]['valid_size'];
                        $validSizeStr = $utils->calSize($validSize, true);
                        $completedValidSize = $data[0]['completed_valid_size'];
                        if($completedValidSize== 0 || $validSize==0){
                            $initialSynCapacity = 0;
                        }else{
                            $initialSynCapacity = $data[0]['total_size']*($completedValidSize/$validSize);  //初始同步容量采用的总容量*有效数据百分比来获取，确保进度平滑
                        }
                        $completedValidStr = $utils->calSize($completedValidSize, true);
                        if($completedValidStr == Xphp::$_config['NULLSPACE']){
                            $completedValidStr = "0B";
                        }
                        $volumeValidDataInfo = $validSizeStr."/".$completedValidStr;
                        $initialSynCapacityStr = $utils->calSize($initialSynCapacity, true);
                        if($initialSynCapacityStr == Xphp::$_config['NULLSPACE']){
                            $initialSynCapacityStr = "0B";
                        }
                        $volumeInfo = $totalSizeStr." / ".$initialSynCapacityStr;

                        $taskRunningCapacityInfo = array(
                            'completed_size' => $utils->calSize($data[0]['completed_size'], $this->verifyValueFalg($data[0]['completed_size'])),
                            'volume_valid_info' =>$volumeValidDataInfo,
                            'volume_info' =>$volumeInfo,
                            'write_size' => $utils->calSize($data[0]['write_size'], $this->verifyValueFalg($data[0]['write_size'])),
                            'transport_size' => $utils->calSize($data[0]['transport_size'], $this->verifyValueFalg($data[0]['transport_size'])),

                        );
                    }else if($taskRunningStage==$VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']){
                        $totalSize = $data[0]['total_size'];
                        $totalSizeStr = $utils->calSize($totalSize, $this->verifyValueFalg($totalSize));
                        $validSize = $data[0]['valid_size'];
                        $validSizeStr = $utils->calSize($validSize, $this->verifyValueFalg($validSize));
                        if($validSizeStr== Xphp::$_config['NULLSPACE']){
                            $validSizeStr = "0B";
                        }
                        $completedValidSize = $data[0]['completed_valid_size'];
                        $completedValidStr = $utils->calSize($completedValidSize, $this->verifyValueFalg($completedValidSize));
                        $volumeInfo = $totalSizeStr." / ".$validSizeStr;
                        $taskRunningCapacityInfo = array(
                            'completed_size' => $utils->calSize($data[0]['completed_size'], $this->verifyValueFalg($data[0]['completed_size'])),
                            'volume_valid_info' =>$completedValidStr,
                            'write_size' => $utils->calSize($data[0]['write_size'], $this->verifyValueFalg($data[0]['write_size'])),
                            'transport_size' => $utils->calSize($data[0]['transport_size'], $this->verifyValueFalg($data[0]['transport_size'])),
                            'volume_info' =>$volumeInfo
                        );
                    }
                    break;
            }
        }
        return $taskRunningCapacityInfo;
    }
    /**
     * 校验入参boole值
     */
    private function verifyValueFalg($value){
        $flag = true;
        if(!$value){
            $flag = false;
        }
        return  $flag;
    }
    /**
     * 通过接管配置目标客户端UUID获取目标客户端信息
     * @param int id (takeover target id)
     */
    private function getTakeoverTargetVol($target_target_id,$vol_uuid){
        $sql = "select standby_mount_point from cdp_vol_task_takeover_lun where target_id =? and vol_uuid = ?";
        $data = $this->dbSelect($sql,array($target_target_id,$vol_uuid));
        $standbyMountPoint = "";
        if(!empty($data)){
            $standbyMountPoint =  $data[0]['standby_mount_point'];
        }
        return $standbyMountPoint;
    }
    /**
     * 通过目标卷UUID获取卷信息
     * @param target_vol_uuid
     */
    private function getAgentVol($target_vol_uuid){
        $sql = "select display_name from bd_agent_vol where vol_uuid = '{$target_vol_uuid}'";
        $data = $this->dbSelect($sql);
        $recoverTargetVol = "";
        if(!empty($data)){
            $recoverTargetVol = $data[0]['display_name'];
        }
        return $recoverTargetVol;
    }
    /**
     * 通过恢复目标磁盘UUID获取目标磁盘信息
     */
    private function getRecoverTargetDisk($disk_uuid){
        $sql = "select display_name from bd_agent_disk where disk_uuid = ?";
        $data = $this->dbSelect($sql,array($disk_uuid));
        $recoverTargetDisk = "";
        if(!empty($data)){
            $recoverTargetDisk = $data[0]['display_name'];
        }
        return $recoverTargetDisk;
    }
    /**
     * 通过恢复数据源卷UUID获取恢复卷信息
     * @param vol_uuid
     */
    private function getDataSourceVolInfo($vol_uuid,$timePoint){
        $sql = "select DISTINCT vol_name,vol_display_name,capacity from cdp_vol_backup_vol_set where vol_uuid = ? 
            and ?  BETWEEN start_timestamp and end_timestamp ";
        $data = $this->dbSelect($sql,array($vol_uuid,$timePoint));
        $dataSourceVol = "";
        if(!empty($data)){
            $dataSourceVol = $data[0]['vol_name'];
        }
        $sourceVolList = array(
            "vol_name" => $data[0]['vol_name'],
            "vol_display_name" => $data[0]['vol_display_name'],
            "capacity" => $data[0]['capacity']
        );
        return $sourceVolList;
    }
    /**
     * 获取恢复数据源主机信息
     */
    private function getDataSourceHosstInfo($master_agent_uuid_){
        $sql = "select DISTINCT master_agent_detail from cdp_vol_backup_agent where master_agent_uuid ='{$master_agent_uuid_}'";
        $data = $this->dbSelect($sql);
        $dataSourceHost = "--";
        if(!empty($data)){
            $master_agent_detail = json_decode($data[0]['master_agent_detail']);
            $hostName = $master_agent_detail->hostname;
            $agentName = $master_agent_detail->agent_name;
            $agentIp = $master_agent_detail->ip;
            $hostNameStr = $this->agentStr($agentName,$hostName,$agentIp);
        }

        return $hostNameStr;
    }

    /**
     * 获取回切目标主机应用信息,回切过程中显示接管备机的应用信息
     * @param $taskRunningStage
     * @param $taskUUID
     */
    private function getFailbackAppTree($taskUUID,$volUUID,$agentuuid){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $failbackHostuuid = $this->getFailBackupAgentInfo($taskUUID);
        $sql = "select DISTINCT app_type,alias_name from bd_agent_app where agent_uuid = '{$agentuuid}' GROUP BY app_type";
        $data = $this->dbSelect($sql);
        $appTree = array();
        if(count($data)>0){
            foreach ($data as $appInfo){
                $alaisName = $appInfo['alias_name'];
                $appTypeValue = $appInfo['app_type'];
                $appTypeDes = Xphp::$_config['DB_TYPE_DES'][intval($appTypeValue)] . PHP_EOL;
                if($appTypeValue == Xphp::$_config['DB_TYPE']['SQLSERVER']){
                    $icon = "./img/db/sqlserver.png";
                }else if($appTypeValue == Xphp::$_config['DB_TYPE']['ORACLE']){
                    $icon = "./img/db/oracle.png";
                }else if($appTypeValue == Xphp::$_config['DB_TYPE']['MYSQL']){
                    $icon = "./img/db/mysql.png";
                }
                $id = $failbackHostuuid.$appTypeValue;
                $appTree[] = array(
                    "id" => $id,
                    "pId" => $failbackHostuuid,
                    "name" => $appTypeDes,
                    "title" => Xphp::$_lang['UI_VOL_CDP_BACKUP_DB_APPLICATION'],
                    "appTypeValue" => $appTypeValue,
                    "icon" => $icon,
                    "isApp" =>false,
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" =>  true,
                    "module_vol_info" => array()
                );

                $childTree = $this->getFailbackAppData($agentuuid,$id,$appTypeValue,$volUUID);
                if(!empty($childTree)){
                    $appTree = array_merge($appTree, $childTree);
                }else{
                    $appTree = array();
                }
            }
        }
        return $appTree;

    }
    /**
     * 获取应用类型下的应用信息，此处需要注意节点对应关系
     */
    private function getFailbackAppData($agentUUID,$pId,$appTypeValue,$volUUID){
        $sql = "select app_name,app_username,app_password,app_type,app_uuid,online_flag from bd_agent_app 
                where agent_uuid = ? and app_type = ? group by app_uuid";
        $data = $this->dbSelect($sql,array($agentUUID,$appTypeValue));
        $info = array();
        foreach ($data as $d){
            $id = $pId.$d['app_uuid'];
            $chkDisabled = false;
            $nodeName = $d['app_name'];
            $nodeTitle = $d['app_name'];
            if($d['online_flag']==Xphp::$_config['FLAG']['UNSET']){
                $chkDisabled = true;
                $nodeName = $d['app_name']."(".Xphp::$_lang['WEB_AGENT_STATUS_OFFLINE'].")";
                $nodeTitle = $d['app_name'].Xphp::$_lang['UI_VOL_CDP_BACKUP_APPLICATION_OFFLINE'];
            }

            $info[] = array(
                "id" => $id,
                "pId" => $pId,
                "name" =>$nodeName,
                "title" => $nodeTitle,
                "uuid" => $d['app_uuid'],
                "app_type" => $d['app_type'],
                "app_type_value" => $appTypeValue,
                "icon" => "./img/db/instance.png",
                "clickshow" => false,
                'agent_uuid' => $agentUUID,
                "nodeType" => 'app',
                "online_flag" => $d['online_flag'],
                "checked" => false,
                "isApp" => true,
                "chkDisabled" => $chkDisabled,
                'vol_uuid' =>$volUUID
            );
            $childTree = $this->getFailbackAppModuleInfo($id,$d['app_uuid'],$appTypeValue,$chkDisabled,$volUUID);
            if(!empty($childTree)){
                $info = array_merge($info, $childTree);
            }else{
                $info = array();
            }
        }
        return $info;
    }
    /**
     * 获取app 组件信息
     */
    private function  getFailbackAppModuleInfo($pId,$appUUID,$appTypeValue,$parentNodeOnlineFlag,$volUUID){
        $sql = "SELECT id,app_uuid,module_name,module_detail FROM cdp_vol_app_module WHERE app_uuid = ?";
        $data = $this->dbSelect($sql,array($appUUID));
        $moduleInfo = array();
        foreach ($data as $d){

            $id = $pId.$d['id'];
            $moduleDetail = json_decode($d['module_detail']);

            $module_log_file = $moduleDetail->module_log_file;
            $module_config_file = $moduleDetail->module_config_file;
            $module_file_info = $moduleDetail->module_file_info;

            $isMatch = $this->getModuleVolinfo($module_log_file,$module_config_file,$module_file_info,$volUUID);
            $moduleArray = array(
                "id" => $id,
                "pId" => $pId,
                "name" => $d['module_name'],
                "title" => $d['module_name'],
                "uuid" => $d['app_uuid'],
                "module_name" => $d['module_name'],
                "icon" => "./img/db/database.png",
                "app_uuid" => $appUUID,
                "file_info" => $module_file_info,
                "app_type_value" => $appTypeValue,
                "clickshow" => false,
                "checked" => false,
                "isApp" =>false,
                "nocheck" => true,
                "chkDisabled" => true,
                'ismatch' => $isMatch,
            );
            if ($isMatch){
                $moduleInfo[] = $moduleArray;
            }
        }
        return $moduleInfo;
    }

    /**
     * 获取当前任务配置的应用信息
     * @param unknown $params
     */
    private function getTakeoverAppTree($voluuid,$masterAgentuuid,$takeoverTimeStr,$standbyHostuuid,$taskRunningStage,$taskUUID){

        $sql = "SELECT agent.master_agent_detail,agent.id,agent.master_agent_uuid
                from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol 
                where agent.master_agent_uuid = ? and agent.id = vol.backup_agent_id 
                  and (UNIX_TIMESTAMP(vol.start_timestamp) <= UNIX_TIMESTAMP(?)) 
                  and  (UNIX_TIMESTAMP(vol.end_timestamp) >= UNIX_TIMESTAMP(?))";
        $data = $this->dbSelect($sql,array($masterAgentuuid,$takeoverTimeStr,$takeoverTimeStr));
        $host_info = "";
        $agentIdList = [];
        $os_type = "";
        $appExist = false;
        if(!empty($data)){
            foreach ($data as $row){
                $master_agent_detail = json_decode($row['master_agent_detail']);
                $hostname = $master_agent_detail->hostname;
                $agent_ip = $master_agent_detail->agent_ip;
                $os_type = $master_agent_detail->os_type;
                $host_info = $hostname."(".$agent_ip .")";
                array_push($agentIdList,$row['id']);
            }
            $type_icon = "./img/platform/linux.png";
            if($os_type=='Windows'){
                $type_icon = "./img/platform/windows.png";
            }
            $agent_uuid = $row['master_agent_uuid'];
            $tree[] = array(
                "id" => $agent_uuid,
                "pId" => "",
                "name" =>  $hostname,
                "title" => $agent_ip,
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
            );

            $appTypeData = $this->getTakeoverAppType($masterAgentuuid,$tree,$agentIdList,$takeoverTimeStr,$standbyHostuuid,$voluuid,$taskRunningStage,$taskUUID);//应用类型
            $childTree = $appTypeData[0];
            $appTypeExist = $appTypeData[1];
            if($appTypeExist){
                $appTree =  $childTree;
                $appExist = true;
            }
        }
        if($appExist){
            $appDataInfo = $appTree;
        }else{
            $appDataInfo = array();
        }

        return $appDataInfo;
    }

    /**
     * 获取接管应用类型
     */
    private function getTakeoverAppType($agentuuid,$appTree,$agentIdList,$takeoverTimeStr,$standbyHostuuid,$voluuid,$taskRunningStage,$taskUUID){
        $id_str =  join(",", $agentIdList);
        $sql = "SELECT DISTINCT id,app_type,app_name,app_uuid FROM cdp_vol_backup_app where backup_agent_id in ({$id_str}) GROUP BY app_type";
        $data = $this->dbSelect($sql);
        $appExist = false;
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
                $id = $agentuuid.$appTypeValue;
                $appTree[] = array(
                    "id" => $id,
                    "pId" => $agentuuid,
                    "name" => $appTypeDes,
                    "title" => Xphp::$_lang['UI_VOL_CDP_BACKUP_DB_APPLICATION'],
                    "appTypeValue" => $appTypeValue,
                    'agent_id_list' =>$agentIdList,
                    "icon" => $icon,
                    "isApp" =>false,
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" =>  true,
                );
                $appData = $this->getTakeoverAppData($agentuuid,$id,$appTypeValue,$agentIdList,$takeoverTimeStr,$standbyHostuuid,$voluuid);
                $childTree = $appData[0];
                $appModuleExist = $appData[1];
                if($appModuleExist){
                    $appTree = array_merge($appTree, $childTree);
                    $appExist = true;
                }
            }
        }
        return array($appTree,$appExist);
    }
    /**
     * 获取接管应用信息
     * @return array|unknown[][]|string[][]|boolean[][]|fetchAll()[][]
     */
    private function getTakeoverAppData($agentUUID,$pId,$appTypeValue,$agentIdList,$takeoverTimeStr,$standbyHostuuid,$voluuid){
        $id_str =  join(",", $agentIdList);
        $sql = "SELECT DISTINCT id,app_type,app_name,app_uuid
                FROM cdp_vol_backup_app
                WHERE backup_agent_id in ({$id_str}) and app_type = ? GROUP BY app_uuid,app_name";
        $data = $this->dbSelect($sql,array($appTypeValue));
        $info = array();
        $appModuleExist = false;
        foreach ($data as $d){
            $id = $pId.$d['app_uuid'];
            $app_name = $d['app_name'];
            $app_type = $d['app_type'];
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
                "clickshow" => false,
                "checked" => false,
                "isApp" => true,
                "chkDisabled" => false,
            );
            $childTree = $this->getTakeoverAppModuleInfo($id,$d['app_uuid'],$agentIdList,$takeoverTimeStr,$voluuid,$appTypeValue);
            if(!empty($childTree)){
                $info = array_merge($info, $childTree);
                $appModuleExist = true;
            }
        }
        return array($info,$appModuleExist);
    }
    /**
     * 获取接管app 组件信息
     */
    private function getTakeoverAppModuleInfo($pId,$appUUID,$agent_id_list,$takeoverTimeStr,$voluuid,$appTypeValue){
        $sql = "SELECT DISTINCT module.id,module.module_name,module.module_detail
               FROM cdp_vol_backup_app_module module,cdp_vol_backup_app app
               WHERE module.backup_app_id = app.id and app.app_uuid = ? and
                     (unix_timestamp(module.start_timestamp) <= ? or unix_timestamp(module.start_timestamp) >=?)
                    GROUP BY module_name";
        $data = $this->dbSelect($sql,array($appUUID,$takeoverTimeStr,$takeoverTimeStr));
        if(!empty($data)){
            $moduleInfo = array();
            foreach ($data as $d){
                $moduleDetail = json_decode($d['module_detail']);

                $moduleLogFile = $moduleDetail->module_log_file;
                $moduleConfigFile = $moduleDetail->module_config_file;
                $moduleFileInfo = $moduleDetail->module_file_info;

                $isMatch =  $this->getModuleVolinfo($moduleLogFile,$moduleConfigFile,$moduleFileInfo,$voluuid);

                $id = $pId.$d['id'];
                $moduleArray = array(
                    "id" => $id,
                    "pId" => $pId,
                    "name" => $d['module_name'],
                    "title" => $d['module_name'],
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
                if ($isMatch){
                    $moduleInfo[] = $moduleArray;
                }
            }
        }
        return $moduleInfo;
    }

    /**
     * 获取客户端对应卷对应的应用信息
     * @param unknown $taskUUID
     * @param unknown $voluuid
     * @return array
     */
    private function getBackuptaskHostAppTree($taskuuid,$voluuid){
        $sql = "select DISTINCT agent_app.app_type,agent_app.app_name,agent_app.alias_name,agent_app.app_uuid 
                from bd_agent_app as agent_app,cdp_vol_task_takeover_app as task_app 
                where task_app.app_uuid = agent_app.app_uuid and task_app.task_uuid ='{$taskuuid}'";
        $data = $this->dbSelect($sql);
        $appExist = false;
        $app_tree = array();
        if(count($data)>0){
            foreach ($data as $appInfo){
                $alaisName = $appInfo['alias_name'];
                $appTypeValue = $appInfo['app_type'];
                $appTypeDes = Xphp::$_config['DB_TYPE_DES'][intval($appTypeValue)] . PHP_EOL;
                if($appTypeValue == Xphp::$_config['DB_TYPE']['SQLSERVER']){
                    $icon = "./img/db/sqlserver.png";
                }else if($appTypeValue == Xphp::$_config['DB_TYPE']['ORACLE']){
                    $icon = "./img/db/oracle.png";
                }else if($appTypeValue == Xphp::$_config['DB_TYPE']['MYSQL']){
                    $icon = "./img/db/mysql.png";
                }
                $appUUID = $appInfo['app_uuid'];
                $id = $appUUID.$appTypeValue;
                $appTree[] = array(
                    "id" => $id,
                    "pId" => "",
                    "name" => $appTypeDes,
                    "title" => $appTypeDes,
                    "appTypeValue" => $appTypeValue,
                    "icon" => $icon,
                    "isApp" =>false,
                    "appUuid" => $appUUID,
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" =>  true,
                );
                $appData = $this->getAppData($id,$appUUID,$appTypeValue,$voluuid);
                $childTree = $appData[0];
                $appModuleExist = $appData[1];
                if($appModuleExist){
                    $appTree = array_merge($appTree, $childTree);
                    $appExist = true;
                }
            }
        }
        if($appExist){
            $appDataInfo = $appTree;
        }else{
            $appDataInfo = array();
        }

        return $appDataInfo;
    }

    /**
     *  获取应用类型下的应用信息
     */
    private  function getAppData($pId,$appUUID,$appTypeValue,$voluuid){
        $sql = "select app_name,app_username,app_password,app_type,app_uuid,online_flag from bd_agent_app
        where app_uuid = '{$appUUID}' group by app_uuid";
        $data = $this->dbSelect($sql);
        $info = array();
        $appModuleExist = false;
        if(!empty($data)){
            foreach ($data as $d){
                $id = $pId.$d['app_uuid'];
                $info[] = array(
                    "id" => $id,
                    "pId" => $pId,
                    "name" =>$d['app_name'],
                    "title" => $d['app_name'],
                    "uuid" => $d['app_uuid'],
                    "app_type" => $d['app_type'],
                    "icon" => "./img/db/instance.png",
                    "clickshow" => false,
                    "checked" => false,
                    "open" =>  false,
                    "checked" => false,
                    "isApp" =>true,
                    "chkDisabled" => false,
                    "app_username" => $d['app_username'],
                    "app_password" => $d['app_password']
                );
                $childTree = $this->getAppModuleInfo($id,$d['app_uuid'],$appTypeValue,$voluuid);
                if(!empty($childTree)){
                    $info = array_merge($info, $childTree);
                    $appModuleExist = true;
                }
            }
        }
        return array($info,$appModuleExist);
    }
    /**
     * 获取app 组件信息
     */
    private function  getAppModuleInfo($pId,$appUUID,$appTypeValue,$voluuid){
        $sql = "SELECT id,app_uuid,module_name,module_detail FROM cdp_vol_app_module WHERE app_uuid = '{$appUUID}'";
        $data = $this->dbSelect($sql);
        $moduleInfo = array();
        if(!empty($data)){
            foreach ($data as $d){
                $id = $pId.$d['module_name'];
                $moduleDetail = json_decode($d['module_detail']);

                $moduleLogFile = $moduleDetail->module_log_file;
                $moduleConfigFile = $moduleDetail->module_config_file;
                $moduleFileInfo = $moduleDetail->module_file_info;
                //判断组件信息是否存放指定卷中
                $isMatch =  $this->getModuleVolinfo($moduleLogFile,$moduleConfigFile,$moduleFileInfo,$voluuid);
                $moduleArray = array(
                    "id" => $id."123",
                    "pId" => $pId,
                    "name" => $d['module_name'],
                    "title" => $d['module_name'],
                    "uuid" => $d['app_uuid'],
                    "module_name" => $d['module_name'],
                    "icon" => "./img/db/database.png",
                    "app_type_value" => $appTypeValue,
                    "clickshow" => false,
                    "checked" => false,
                    "open" =>  false,
                    "isApp" =>false,
                    "chkDisabled" => false,
                    'ismatch' => $isMatch
                );
                if ($isMatch){
                    $moduleInfo[] = $moduleArray;
                }
            }
        }
        return $moduleInfo;
    }

    /**
     * 匹配数据文件卷信息
     * @param unknown $obj1
     * @param unknown $obj2
     * @param unknown $obj3
     */
    private function  getModuleVolinfo($obj1,$obj2,$obj3,$agentVoluuid){
        $volInfo = array();
        $objArry = array();
        $objArry[] = $obj1;
        $objArry[] = $obj2;
        $objArry[] = $obj3;
        $inVolume = false;
        if ($objArry != null) {
            for($i=0;$i<count($objArry);$i++){
                $list = $objArry[$i];
                if($list ==null){
                    continue;
                }
                for($n=0;$n<count($list);$n++){
                    $mountPath = $list[$n]->mount_path;
                    $volUuid = $list[$n]->vol_uuid;

                    if ($agentVoluuid==$volUuid){
                        $inVolume = true;
                        break;
                    }
                }
            }
        }
        return $inVolume;
    }
    /**
     * 获取备份任务配置的自动接管机器信息
     * @param unknown $task_uuid
     */
    private  function  getAutoTakeoverHostInfo($task_uuid){
        $sql = "select agent.agent_name,agent.hostname,agent.ip,agent.online_flag 
            from bd_agent as agent,cdp_vol_task_takeover_info as takeover_info 
            where agent.agent_uuid = takeover_info.takeover_standby_agent_uuid and takeover_info.task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $agentArry = array (
            "agentInfo" => "--",
            "isOnline" => 0,
            "ip" => "--"
        );
        if(!empty($data)){
            $agentName = $data[0]['agent_name'];
            $agentIp = $data[0]['ip'];
            $agentHostName = $data[0]['hostname'];
            $agentInfo = $agentHostName."(".$agentIp.")";
            $agentIsOnline = $data[0]['online_flag'];
            $agentArry = array (
                "agentInfo" => $agentInfo,
                "isOnline" => $agentIsOnline,
                "ip" => $data[0]['ip']
            );
        }
        return $agentArry;
    }
    /**
     * 获取客户端别名
     * @param string $params agent_uuid
     */
    private function getBdAgentInfo($params,$takeoverAgentType=0){
        if ($params){
            if($takeoverAgentType == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){
                $sql = "select agent_name,hostname, ip,online_flag from bd_agent where agent_uuid = ? ";
                $data = $this->dbSelect($sql, array($params));
                $agentName = $data[0]['agent_name'];
                $agentIp = $data[0]['ip'];
                $hostName = $data[0]['hostname'];
                $agentIsOnline = $data[0]['online_flag'];

                $agentInfo = $this->agentStr($agentName,$hostName,$agentIp);
                $agentArry = array (
                    "agentInfo" => $agentInfo,
                    "isOnline" => $agentIsOnline,
                    "ip" => $data[0]['ip']
                );
            }else if($takeoverAgentType!= Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){  //内嵌虚拟机
                $sql = "select name from vm_emd where uuid = ? ";
                $data = $this->dbSelect($sql, array($params));
                $agentArry = array (
                    "agentInfo" => $data[0]['name'],
                    "isOnline" => Xphp::$_config['FLAG']['SET'],
                    "ip" => Xphp::$_lang['UI_VOL_CDP_TAKE_TEMP_AGENT_DESC']
                );
            }

        }else{
            $agentArry = array (
                "agentInfo" => "--",
                "isOnline" => 0,
                "ip" => "--"
            );
        }
        return $agentArry;
    }

    /**
     * 获取模板代理信息
     * @return void
     */
    private function getTempAgentInfo($uuid){
        $sql = "select name,status from vm_emd where uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $agentName = "";
        $vmStatus = 0;
        if(!empty($data)){
            $agentName = $data[0]['name'];
            $vmStatus = $data[0]['status'];
        }
        
        $agentArry = array (
            "agent_name" => $agentName,
            "ip" => "",
            "vm_status" => $vmStatus
        );
        return $agentArry;
    }
    /**
     * 通过卷uuid获取磁盘卷基本信息
     * @param string vol_uuid
     */
    private function getVolInfo($vol_uuid){
        if($vol_uuid){
            $utils = Xphp::instance('Utils');
            $sql = "SELECT capacity,free_space,display_name from bd_agent_vol where vol_uuid =? ";
            $data = $this->dbSelect($sql, array($vol_uuid));
            $capacity = $data[0]['capacity'];
            $freeSpace = $data[0]['free_space'];
            $displayName = $data[0]['display_name'];
            $volList = array(
                "vol_name" => $displayName,
                "capacity" => $capacity
            );
        }else{
            $volList = array(
                "vol_name" => "--",
                "capacity" => 0
            );
        }
        return $volList;
    }
    /**
     * 获取备份数据源对应的客户端信息
     */
    private  function getDataSourceAgentInfo($master_agent_uuid_){
        $sql = "select DISTINCT master_agent_detail from cdp_vol_backup_agent where master_agent_uuid ='{$master_agent_uuid_}'";
        $data = $this->dbSelect($sql);
        $hostInfo = "--";
        $agentIp = "--";
        if(!empty($data)){
            $master_agent_detail = json_decode($data[0]['master_agent_detail']);
            $hostname = $master_agent_detail->hostname;
            $agentIp = $master_agent_detail->ip;
            $hostInfo = $hostname."(".$agentIp.")";
        }

        $agentInfo = array(
            "host_info" => $hostInfo,
            "agent_ip" => $agentIp
        );
        return $agentInfo;
    }
    /**
     * 获取接管任务数据流向map
     * @param string taskuuid,int task_type
     */
    public function  getTakeoverTaskMapInfo($params){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $taskUuid = $params['taskuuid'];
        $taskType = $params['tasktype'];
        $sql = "select task.task_status,task.agent_uuid,task.node_uuid,vol_task.current_task_running_stage,vol_task.master_agent_uuid,
				    take.takeover_standby_agent_uuid,take.takeover_vm_hypervisor,take.takeover_vm_config  
                from bd_task as task,cdp_vol_task as vol_task,cdp_vol_task_takeover_info as take 
                where task.task_uuid = vol_task.task_uuid and take.task_uuid = task.task_uuid and task.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $taskStatus = $data[0]['task_status'];  //任务状态
        $agent_uuid = $data[0]['agent_uuid'];
        $nodeUuid = $data[0]['node_uuid'];
        $nodeInfo = $this->getNodeServersInfo($nodeUuid);  //节点信息
        $currentTaskStage = $data[0]['current_task_running_stage'];  //当前任务阶段
        $masterAgentUuid = $data[0]['master_agent_uuid'];
        $takeoverStandbyAgentUuid = $data[0]['takeover_standby_agent_uuid'];
        $takeoverAgentType = $data[0]['takeover_vm_hypervisor'];
        $dataSource = 1;   //恢复数据来源：默认值：0（备份作业），1：接管数据来源于备份服务器，2：接管数据来源与备机

        if($takeoverAgentType == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){
            $standbyInfo = $this->getBdAgentInfo($takeoverStandbyAgentUuid);  //获取接管目标机器
            $standby = $standbyInfo['agentInfo'];                   //接管目标主机名
            $standbyIsOnline = $standbyInfo['isOnline'];            //接管目标主机在线状态
            $standbyIp = $standbyInfo['ip'];  //备机IP
            $standbyAgentStatus = $standbyIsOnline;
        }else if($takeoverAgentType != Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){
            $tempAgentInfo = $this->getTempAgentInfo($takeoverStandbyAgentUuid);  //获取接管目标机器
            
            // $standby = $tempAgentInfo['agent_name'];
            $takeoverVmConfig = $data[0]['takeover_vm_config'];
            $vmConfig = json_decode($takeoverVmConfig);
            $standby = $vmConfig->vm_name;

            // $standbyIsOnline = $tempAgentInfo['vm_status'];
            $standbyIsOnline = Xphp::$_config['FLAG']['SET'];
            $standbyAgentStatus = $tempAgentInfo['vm_status'];
            $standbyIp = $standby;
        }


        $standbyAppIsOnline = $this->getHostAppStatus($takeoverStandbyAgentUuid);  //备机应用是否在线;
        $standbyIsConf = 1;
        $dataSourceAgentInfo = $this->getDataSourceAgentInfo($agent_uuid);  //获取数据主机信息
        $backupServerStatus = 1; //保留获取备份服务器服务状态项
        $masterDesc = $dataSourceAgentInfo['host_info'];   //数据源Host
        $masterIp = $dataSourceAgentInfo['agent_ip'];   //数据源IP

        //回切
        if($currentTaskStage==$VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC']
            || $currentTaskStage==$VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']
            || $currentTaskStage==$VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']){
            $failbackup_agent_uuid = $this->getFailBackupAgentInfo($taskUuid);
            //主机信息
            $masterInfo = $this->getBdAgentInfo($failbackup_agent_uuid);
            $masterDesc = $masterInfo['agentInfo'];   //备机名
            $masterIp = $masterInfo['ip'];
        }
        $info = array(
            "masterDesc" => $masterDesc,
            "masterIp" => $masterIp,
            "standbyIp" => $standbyIp,  //备机IP
            "standbyDesc" => $standby,  //备机描述信息
            "nodeInfo" => $nodeInfo['nodeInfo'],  //备份节点信息
            "nodeIp" => $nodeInfo['ip'], //备份节点IP
            "standbyIsConf" => $standbyIsConf, //是否配置备机
            "currentTaskStage" => $currentTaskStage,  //任务阶段
            "taskStatus" =>$taskStatus,
            "masterAgentMapStatus" => $this->parseMasterMapStatus($standbyIsOnline,$standbyAppIsOnline,$taskStatus,$currentTaskStage),
            "hostToBSTransStatus" => $this->parseHostToBSTransStatus($taskStatus,$currentTaskStage,$taskType,$dataSource,$standbyIsOnline),    //主机与备份服务器的传输示意图
            "backupServerMap" => $backupServerStatus,
            "bsToStandbyTransStatus" => $this->parseBsToStandbyTransStatus($taskStatus,$taskUuid,$currentTaskStage,$taskType,$dataSource,$standbyIsConf), //备份服务器到备机传输示意图
            "standbyMapStatus" => $this->parseStandbyMapStatus($standbyIsOnline,$standbyAppIsOnline,$taskStatus,$currentTaskStage),
            "dataIsStandbyToHost" => $this->parseDataIsStandbyToHost($taskStatus,$currentTaskStage,$taskType,$dataSource),   //解析是否满足数据从备机直接到目标机器
            "standbyAgentStatus" => $standbyAgentStatus,
            "agentType" => $takeoverAgentType
        );
        return json_encode($info);
    }
    /**
     * 获取恢复任务数据流向map
     * @params string taskuuid,int task_type
     */
    public function getRecoverTaskMapInfo($params){
        $taskUuid = $params['taskuuid'];
        $taskType = $params['tasktype'];
        $sql = "select task.task_status,task.agent_uuid,task.node_uuid,vol_task.current_task_running_stage,vol_task.master_agent_uuid,
                     vol_task.standby_agent_uuid,vol_task.recovery_target_agent_uuid,vol_task.recovery_data_source,vol_task.recovery_type
                from bd_task as task,cdp_vol_task as vol_task 
                where task.task_uuid = vol_task.task_uuid and task.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $taskStatus = $data[0]['task_status'];  //任务状态
        $agent_uuid = $data[0]['agent_uuid'];
        $nodeUuid = $data[0]['node_uuid'];
        $nodeInfo = $this->getNodeServersInfo($nodeUuid);  //节点信息

        $currentTaskStage = $data[0]['current_task_running_stage'];  //当前任务阶段
        $masterAgentUuid = $data[0]['master_agent_uuid'];
        $recoveryTargetAgentUuid = $data[0]['recovery_target_agent_uuid'];
        $recoveryDataSource = $data[0]['recovery_data_source'];   //恢复数据来源：默认值：0（备份作业），1：恢复数据来源于备份服务器，2：恢复数据来源与备机
        $standby_agent_uuid = $data[0]['standby_agent_uuid'];   //恢复数据源来自备机时取该值,其他为空
        $recovery_type = $data[0]['recovery_type'];  //恢复类型
        if($recoveryDataSource==2){
            $standbyInfo = $this->getBdAgentInfo($standby_agent_uuid);  //恢复数据源为备机,获取备机信息
            $standby = $standbyInfo['agentInfo'];                   //恢复数据源主机名
            $standbyIsOnline = $standbyInfo['isOnline'];            //数据源主机在线状态
            $standbyAppIsOnline = 1;
            $standbyIsConf = 1;
        }else{
            $standby = "";
            $standbyIsOnline = 1;
            $standbyAppIsOnline = 0;
            $standbyIsConf = 0;
        }
        $recoveryTargetHostInfo = $this->getBdAgentInfo($recoveryTargetAgentUuid);  //获取恢复目标主机信息
        $recoveryTargetAgent = $recoveryTargetHostInfo['agentInfo'];   //恢复目标主机名
        $recoveryTargeIsOnline = $recoveryTargetHostInfo['isOnline'];    //恢复目标主机在线状态
        $backupServerStatus = 1; //保留获取备份服务器服务状态项
        $info = array(
            "masterDesc" => $recoveryTargetAgent, //恢复目标主机
            "masterIp" => $recoveryTargetHostInfo['ip'], //恢复目标IP
            "standbyIp" => $standbyInfo['ip'], //恢复数据源为备机:备机IP
            "standbyDesc" => $standby, //数据数据源为备机:备机描述信息
            "nodeInfo" => $nodeInfo['nodeInfo'], //备份节点信息
            "nodeIp" => $nodeInfo['ip'], //备份节点IP
            "standbyIsConf" => $standbyIsConf, //是否配置备机
            "currentTaskStage"=>$currentTaskStage,
            "masterAgentMapStatus" => $this->parseMasterMapStatus($standbyIsOnline,$standbyAppIsOnline,$taskStatus,$currentTaskStage),
            "hostToBSTransStatus" => $this->parseHostToBSTransStatus($taskStatus,$currentTaskStage,$taskType,$recoveryDataSource,$standbyIsOnline),    //主机与备份服务器的传输示意图
            "backupServerMap" => $backupServerStatus,
            "bsToStandbyTransStatus" => $this->parseBsToStandbyTransStatus($taskStatus,$taskUuid,$currentTaskStage,$taskType,$recoveryDataSource,$standbyIsConf), //备份服务器到备机传输示意图
            "standbyMapStatus" => $this->parseStandbyMapStatus($standbyIsOnline,$standbyAppIsOnline,$taskStatus,$currentTaskStage),
            "dataIsStandbyToHost" => $this->parseDataIsStandbyToHost($taskStatus,$currentTaskStage,$taskType,$recoveryDataSource),   //解析是否满足数据从备机直接到目标机器
            "standbyAgentStatus" => "",
            "agentType" => 0,
        );
        return json_encode($info);
    }
    /**
     * 获取备份任务数据流向map
     * @param string taskuuid,int task_type
     */
    public function getBackupTaskMapInfo($params){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $taskUuid = $params['taskuuid'];
        $taskType = $params['tasktype'];
        $sql =  "select task.task_status,task.agent_uuid,task.node_uuid,vol_task.current_task_running_stage,vol_task.master_agent_uuid,
                    vol_task.standby_agent_uuid,vol_task.auto_takeover_flag,vol_task.recovery_data_source,vol_task.backup_mode 
                from bd_task as task,cdp_vol_task as vol_task 
                where task.task_uuid = vol_task.task_uuid and task.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $taskStatus = $data[0]['task_status']; //任务状态
        $agent_uuid = $data[0]['agent_uuid'];

        $nodeUuid = $data[0]['node_uuid'];
        $currentTaskStage = $data[0]['current_task_running_stage']; //当前任务阶段
        $masterAgentUuid = $data[0]['master_agent_uuid'];
        $standbyAgentUuid = $data[0]['standby_agent_uuid'];

        $autoTakeoverFlag = $data[0]['auto_takeover_flag'];   //是否启用自动接管
        $recoveryDataSource = $data[0]['recovery_data_source']; //恢复数据来源：默认值：0（备份作业），1：恢复数据来源于备份服务器，2：恢复数据来源与备机
        $nodeInfo = $this->getNodeServersInfo($nodeUuid);  //节点信息
        //回切
        if($currentTaskStage==$VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC']
            || $currentTaskStage==$VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']
            || $currentTaskStage==$VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']){

            $masterAgentUuid = $this->getFailBackupAgentInfo($taskUuid);
        }
        //主机信息
        $masterInfo = $this->getBdAgentInfo($masterAgentUuid);
        $masterAgent = $masterInfo['agentInfo'];   //主机名
        $masterAgentIsOnline = $masterInfo['isOnline'];    //主机在线状态
        $masterAgentAppIsOnline = $this->getHostAppStatus($masterAgentUuid);  //主机应用是否在线


        if($standbyAgentUuid!=""){ //备机信息
            $standbyInfo = $this->getBdAgentInfo($standbyAgentUuid);
            $standby = $standbyInfo['agentInfo'];   //备机名
            $standbyIp = $standbyInfo['ip'];
            $standbyIsOnline = $standbyInfo['isOnline'];    //在线状态
            $standbyAppIsOnline = $this->getHostAppStatus($standbyAgentUuid);  //备机应用是否在线
            $standbyIsConf = $VolCdpDes['STANDBYCONF']['CONFIGURED'];  //任务配置备机
            if($taskType ==Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'] && $recoveryDataSource!=$VolCdpDes['RECOVERY_DATA_SOURCE']['STANDBY']){
                $standbyIsConf =$VolCdpDes['STANDBYCONF']['NOT_CONF'];  //未配置备机
            }
            $standbyIsShow = $VolCdpDes['STANDBYCONF']['CONFIGURED'];  //任务配置备机，相关图标显示
        }else{  //未配置备机
            $standbyIsConf = $VolCdpDes['STANDBYCONF']['NOT_CONF'];  //未配置备机
            $standby = "";
            $standbyIsOnline = 0;
            $standbyAppIsOnline = 0;
            $standbyIsShow = $VolCdpDes['STANDBYCONF']['NOT_CONF'];  //任务未配置备机，相关图标隐藏
        }
        $standbyAgentStatus = $standbyIsOnline;
        $takeoverAgentType = 0;
        if($autoTakeoverFlag==$VolCdpDes['TAKEOVER_TYPE']['AUTO_TAKEOVER']){
            $takeoverAgentUuid = $data[0]['takeover_standby_agent_uuid'];
            $autoTakeoverSql = "select takeover_standby_agent_uuid,takeover_vm_hypervisor,takeover_vm_config 
                                    from cdp_vol_task_takeover_info where task_uuid = ?";
            $takeoverData = $this->dbSelect($autoTakeoverSql, array($taskUuid));
            $takeoverAgentUuid = $takeoverData[0]['takeover_standby_agent_uuid'];
            $takeoverAgentType = $takeoverData[0]['takeover_vm_hypervisor'];

            if($takeoverAgentType == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){
                $standbyInfo = $this->getAutoTakeoverHostInfo($taskUuid);
                $standby = $standbyInfo['agentInfo'];   //备机名
                $standbyIsOnline = $standbyInfo['isOnline'];    //在线状态
                $standbyIp = $standbyInfo['ip'];
                $standbyAgentStatus = $standbyIsOnline;
            }else if($takeoverAgentType != Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){
                $tempAgentInfo = $this->getTempAgentInfo($takeoverAgentUuid);  //获取接管目标机器

                $takeoverVmConfig = $takeoverData[0]['takeover_vm_config'];
                $vmConfig = json_decode($takeoverVmConfig);
                $standby = $vmConfig->vm_name;
            
                $standbyIsOnline = Xphp::$_config['FLAG']['SET'];
                $standbyAgentStatus = $tempAgentInfo['vm_status'];
                $standbyIp = $standby;
            }
            $standbyAppIsOnline = $this->getHostAppStatus($takeoverAgentUuid);  //备机应用是否在线
            $standbyIsShow = $VolCdpDes['STANDBYCONF']['CONFIGURED'];  //任务配置备机，相关图标显示
        }
        $backupServerStatus = 1; //保留获取备份服务器服务状态项
        $info = array(
            "masterDesc" => $masterAgent,
            "masterIp" => $masterInfo['ip'],
            "standbyIp" => $standbyIp,
            "standbyDesc" => $standby,
            "nodeInfo" => $nodeInfo['nodeInfo'],
            "nodeIp" => $nodeInfo['ip'],
            "standbyIsConf" => $standbyIsShow,
            'currentTaskStage'=>$currentTaskStage,
            "masterAgentMapStatus" => $this->parseMasterMapStatus($masterAgentIsOnline,$masterAgentAppIsOnline,$taskStatus,$currentTaskStage),
            "hostToBSTransStatus" => $this->parseHostToBSTransStatus($taskStatus,$currentTaskStage,$taskType,$recoveryDataSource,$masterAgentIsOnline),    //主机与备份服务器的传输示意图
            "backupServerMap" => $backupServerStatus,
            "bsToStandbyTransStatus" => $this->parseBsToStandbyTransStatus($taskStatus,$taskUuid,$currentTaskStage,$taskType,$recoveryDataSource,$standbyIsConf), //备份服务器到备机传输示意图
            "standbyMapStatus" => $this->parseStandbyMapStatus($standbyIsOnline,$standbyAppIsOnline,$taskStatus,$currentTaskStage),
            "dataIsStandbyToHost" => $this->parseDataIsStandbyToHost($taskStatus,$currentTaskStage,$taskType,$recoveryDataSource),   //解析是否满足数据从备机直接到目标机器
            "standby_agent_uuid" => $standbyAgentUuid,
            "backupMode" => $data[0]['backup_mode'],
            "standbyAgentStatus" => $standbyAgentStatus,
            "agentType" => $takeoverAgentType
        );
        return json_encode($info);
    }
    /**
     * 任务在回切阶段，根据任务uuid获取回切目标主机信息
     */
    private function getFailBackupAgentInfo($task_uuid){
        $sql = "select failback_target_agent_uuid from cdp_vol_task_takeover_failback_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $agentuuid = $data[0]['failback_target_agent_uuid'];
        return $agentuuid;
    }

    /**
     * 解析是否满足数据从备机直接到目标机器的数据流向
     * @param $task_status:任务状态;$current_task_stage:任务阶段;$taskType:任务类型,$recovery_data_source:恢复数据来源
     * @return int flag;
     */
    private function parseDataIsStandbyToHost($task_status,$current_task_stage,$taskType,$recovery_data_source){
        $dataIsStandbyToHostMap = 0; // 0:不显示备机到目标机器示意图;1:显示数据流向;2:传输出错
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';

        //备份任务或手动接管任务,任务阶段处于逆向初始同步或逆向实时同步状态
        if(($current_task_stage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC']
                ||$current_task_stage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']
                ||$current_task_stage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING'])
            && ($taskType == Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] ||$taskType == Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION']||$taskType == Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']))
        {
            if($task_status==Xphp::$_config['TASKSTATUS']['RUNNING']){ //任务运行中
                $dataIsStandbyToHostMap =1;
            }elseif($task_status==Xphp::$_config['TASKSTATUS']['ERROR']){ //任务出错
                $dataIsStandbyToHostMap =2;
            }
        }
        //恢复任务   恢复数据来源与备机 (默认值：0（备份作业），1：恢复数据来源于备份服务器，2：恢复数据来源与备机)
        if($taskType == Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'] && $recovery_data_source ==2){
            if($task_status==Xphp::$_config['TASKSTATUS']['RUNNING']){ //任务运行中
                $dataIsStandbyToHostMap =1;
            }elseif($task_status==Xphp::$_config['TASKSTATUS']['ERROR']){ //任务出错
                $dataIsStandbyToHostMap =2;
            }
        }
        return  $dataIsStandbyToHostMap;
    }

    /**
     * 解析目标主机示意图
     * @param $masterAgentIsOnline:主机在线状态,$masterAgentAppIsOnline:主机应用在线状态,
     *        $task_status:任务状态,$task_stage:任务阶段
     * @return int hostMapStatus
     */
    private  function parseMasterMapStatus($masterAgentIsOnline,$masterAgentAppIsOnline,$task_status,$task_stage){
        //[0:设备离线,1:设备在线 ;服务在线且 对外提供服务;2:设备在线,未对外提供服务]
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $hostMapStatus = 0; //主机离线
        /*
         * 暂时不判断应用是否在线
        if($masterAgentIsOnline==Xphp::$_config['FLAG']['SET'] && $masterAgentAppIsOnline==Xphp::$_config['FLAG']['SET']){
            $hostMapStatus =1;  //主机在线,服务在线
        }else if($masterAgentIsOnline==Xphp::$_config['FLAG']['SET'] && $masterAgentAppIsOnline==Xphp::$_config['FLAG']['UNSET']){
            $hostMapStatus =2;  //设备在线，服务离线；
        }else if($masterAgentIsOnline==Xphp::$_config['FLAG']['UNSET']){
            $hostMapStatus =0;  //设备离线，服务离线
        }else{
            $hostMapStatus = 0;  //离线
        }
        */
        if($masterAgentIsOnline==Xphp::$_config['FLAG']['SET'] ){
            $hostMapStatus =1;  //主机在线
        }else{
            $hostMapStatus = 0;  //离线
        }

        return $hostMapStatus;
    }

    /**
     * 解析目标备机示意图
     * @param $masterAgentIsOnline:主机在线状态,$masterAgentAppIsOnline:主机应用在线状态,
     *        $task_status:任务状态,$task_stage:任务阶段
     * @return int hostMapStatus
     */
    private function parseStandbyMapStatus($standbyIsOnline,$standbyAppIsOnline,$task_status,$task_stage){
        //[0:设备离线,1:设备在线 ;服务在线且 对外提供服务;2:设备在线,未对外提供服务]
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $standbyMapStatus = 0; //备机离线
        /*
         * 暂时只判断主机的在线状态
        if($standbyIsOnline==Xphp::$_config['FLAG']['SET'] && $standbyAppIsOnline==Xphp::$_config['FLAG']['SET']){
            $standbyMapStatus =1; //设备在线，服务在线；
        }else if($standbyIsOnline==Xphp::$_config['FLAG']['SET'] && $standbyAppIsOnline==Xphp::$_config['FLAG']['UNSET']){
            $standbyMapStatus =2; //设备在线，服务离线；
        }else if($standbyIsOnline==Xphp::$_config['FLAG']['UNSET']){
            $standbyMapStatus =0; //设备离线，服务离线
        }else{
            $standbyMapStatus = 0;  //离线
        }
        */
        if($standbyIsOnline==Xphp::$_config['FLAG']['SET'] ){
            $standbyMapStatus =1; //设备在线；
        }else{
            $standbyMapStatus = 0;  //离线
        }
        return $standbyMapStatus;
    }
    /**
     * 解析备份服务器到备机的传输示意图
     * @param $task_status:任务状态,$task_stage:任务阶段
     * return int status
     */
    private function parseBsToStandbyTransStatus($taskStatus,$taskUuid,$currentTaskStage,$taskType,$recoveryDataSource,$standbyIsConf){
        $sqlISonline = "select agent.online_flag
                         from bd_task as task,bd_agent as agent,cdp_vol_task as vol_task
                         where task.task_uuid = vol_task.task_uuid and vol_task.standby_agent_uuid = agent.agent_uuid and task.task_uuid = ?";
        $dataISonline = $this->dbSelect( $sqlISonline, array($taskUuid));
        if($dataISonline[0]['online_flag'] == 2) {
            $taskStatus = 6;  // 备机离线状态也显示网络异常情况
        }
        //[1. 连通无状态,2. 连通有数据传输;3. 连接异常(网络异常),4. 连通任务暂停;5. 连通有心跳;6.任务出错;7:反向数据(与2方向相反);8:备机未创建]
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $transMapStatus = 0;//默认状态
        switch ($taskStatus){
            case Xphp::$_config['TASKSTATUS']['WAITTING']:
            case Xphp::$_config['TASKSTATUS']['STOPPED']:
                $transMapStatus = 1;
                break;
            case Xphp::$_config['TASKSTATUS']['RUNNING']:
                $transMapStatus = 1;    // 连通有数据传输
                switch ($currentTaskStage){
                    case $VolCdpDes['TASK_RUNNING_STAGE']['IN_TAKEOVER']:
                    case $VolCdpDes['TASK_RUNNING_STAGE']['IN_TAKEOVER_STARTING']:
                    case $VolCdpDes['TASK_RUNNING_STAGE']['INIT_SYNC']:
                    case $VolCdpDes['TASK_RUNNING_STAGE']['REALTIME_SYNC']:
                    case $VolCdpDes['TASK_RUNNING_STAGE']['WAIT_CONVERT_TO_REALTIME_SYNC']:
                        if($standbyIsConf == $VolCdpDes['STANDBYCONF']['CONFIGURED']){
                            $transMapStatus = 2;
                        }
                        break;
                    case $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC']:
                    case $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']:
                    case $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']:
                        $transMapStatus = 5;
                        break;
                    case $VolCdpDes['TASK_RUNNING_STAGE']['REALTIME_CONSISTENCY_CHECK']:
                        $transMapStatus = 2;
                        break;
                }
                break;
            case Xphp::$_config['TASKSTATUS']['SUCCESSED']:
                if ($currentTaskStage == $VolCdpDes['TASK_RUNNING_STAGE']['IN_TAKEOVER'] ||$currentTaskStage == $VolCdpDes['TASK_RUNNING_STAGE']['IN_TAKEOVER_STARTING']){ //接管完成,任务阶段处于接管中
                    $transMapStatus = 5;
                }
                break;
            case Xphp::$_config['TASKSTATUS']['PAUSING']:
                $transMapStatus = 4; //心跳保持
                break;
            case Xphp::$_config['TASKSTATUS']['ERROR']:
                $transMapStatus = 6; //任务出错
                break;
            case Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']:
                $transMapStatus = 3; //网络出错
                break;
            default:
                $transMapStatus =1; //连通无状态
        }
        return  $transMapStatus;
    }

    /**
     * 解析主机到备份服务器的传输示意图
     * @param $task_status:任务状态,$task_stage:任务阶段
     * return int status
     */
    private function parseHostToBSTransStatus($task_status,$current_task_stage,$taskType,$recovery_data_source,$agentIsOnline){
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $transMapStatus = 0;  //默认状态
        switch ($task_status){  //[1. 连通无状态,2. 连通有数据传输;3. 连接异常(网络异常),4. 连通任务暂停;5. 连通有心跳;6.任务出错;7:反向数据(与2方向相反);]
            //任务状态:新建/停止
            case Xphp::$_config['TASKSTATUS']['WAITTING']:
            case Xphp::$_config['TASKSTATUS']['STOPPED']:
                $transMapStatus = 1;
                break;
            case Xphp::$_config['TASKSTATUS']['RUNNING']:  //任务状态:运行中
                switch ($current_task_stage){
                    case $VolCdpDes['TASK_RUNNING_STAGE']['IN_TAKEOVER']:
                    case $VolCdpDes['TASK_RUNNING_STAGE']['IN_TAKEOVER_STARTING']:
                        $transMapStatus = 1;
                        break;
                    case $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC']:
                    case $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']:
                    case $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']:
                        $transMapStatus = 5; //连通有心跳
                        break;
                    default:
                        $transMapStatus = 2;    // 连通有数据传输
                }
                break;
            case Xphp::$_config['TASKSTATUS']['PAUSING']:
                $transMapStatus = 4;
                break;
            case Xphp::$_config['TASKSTATUS']['ERROR']:
                $transMapStatus = 6;
                break;
            case Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']:
                $transMapStatus = 3;
                break;
            default:
                $transMapStatus =1;
        }
        if($taskType==Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']){  //任务类型:恢复
            //备份服务器到目标机器
            if($recovery_data_source==1 && $task_status ==Xphp::$_config['TASKSTATUS']['RUNNING'] ){ //恢复数据源为备份服务器
                $transMapStatus = 7;
            }elseif($recovery_data_source==2 && $task_status ==Xphp::$_config['TASKSTATUS']['RUNNING']){
                $transMapStatus = 5;
            }else if(($recovery_data_source==2 || $recovery_data_source==1)&& $task_status == Xphp::$_config['TASKSTATUS']['NETWORK_FAULT']){
                $transMapStatus = 3;
            }else{
                $transMapStatus = 1;    //连通无状态
            }

        }elseif($task_status==Xphp::$_config['TASKSTATUS']['ERROR']){ //任务出错
            $transMapStatus = 6;    //任务出错
        }
        if(($taskType == Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] || $taskType == Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION']) && $agentIsOnline != Xphp::$_config['FLAG']['SET']){
            $transMapStatus = 3;
        }
        return $transMapStatus;
    }



    /**
     * 获取备份节点信息
     * @param string nodeuuid
     */
    private function getNodeServersInfo($node_uuid){
        $sql = "SELECT ip,host_name from bd_node where node_uuid =? ";
        $data = $this->dbSelect($sql, array($node_uuid));
        $ip = $data[0]['ip'];
        $host_name = $data[0]['host_name'];
        $hostArry = array (
            "nodeInfo" => $host_name."(".$ip.")",
            "ip" => $data[0]['ip']
        );
        return $hostArry;
    }
    /**
     * 获取客户端对应的应用是否在线
     * @param string agent_uuid
     * @return int app_type
     */
    private  function getHostAppStatus($node_uuid){
        $sql = "select app_type,online_flag from bd_agent_app where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($node_uuid));
        $appOnLine = 0;
        if(!empty($data)){
            foreach ($data as $d){
                $appIsOnline = $d['online_flag'];
                if($appIsOnline==1){
                    $appOnLine = 1;
                }
            }
        }
        return $appOnLine;
    }

    /**
     * 获取卷cdp任务缓存配置
     * @param string task_uuid
     * @return array
     */
    public function getVolCdpTaskCacheInfo($taskuuid,$currentTaskRunningStage){
        $utils = Xphp::instance('Utils');
        $used_str = Xphp::$_lang['UI_RECOVERY_STORAGE_VALID_SIZE'];  //可用空间
        $free_space_str = Xphp::$_lang['UI_RECOVERY_STORAGE_USED_SIZE'];  //已用空间
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';

        $sql = "SELECT agent_memory_cache_alloc_space,agent_memory_cache_used_space,agent_cache_file_storage_path,agent_file_cache_alloc_space,agent_file_cache_used_space
                FROM cdp_vol_task_cache_info
                WHERE task_uuid = ? ";

        $data = $this->dbSelect($sql, array($taskuuid));
        $memory_cache_space = $data[0]['agent_memory_cache_alloc_space'];
        $memory_cache_used = $data[0]['agent_memory_cache_used_space'];
        $memory_free_space = 0;
        $memory_cache_flag = false;
        $file_cache_space = $data[0]['agent_file_cache_alloc_space'];
        $file_cache_used = $data[0]['agent_file_cache_used_space'];
        $file_cache_path = $data[0]['agent_cache_file_storage_path'];

        //获取当前任务是否配置回切，如果配置回切且任务阶段处于回切阶段需要获取回切的相关缓存配置，其他任务阶段获取任务配置的缓存配置
        if($currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC']
            || $currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']
            || $currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']){  //任务处于回切阶段

            $fbSql = "SELECT memory_cache_alloc_space,file_cache_alloc_space,file_cache_storage_path 
                FROM cdp_vol_task_takeover_failback_info  WHERE task_uuid = ? ";

            $fbData = $this->dbSelect($fbSql, array($taskuuid));
            $memory_cache_space = $fbData[0]['memory_cache_alloc_space'];  //回切配置的内存缓存空间
            $file_cache_space = $fbData[0]['file_cache_alloc_space'];  //回切配置文件缓存空间
            $file_cache_path = $fbData[0]['file_cache_storage_path'];  //回切配置的文件缓存路径
        }

        $info = array();
        $info['agent_memory_cache_des'] = "--";
        $memoryCacheUsedStr = $utils->calSize($memory_cache_used);
        if($memoryCacheUsedStr=="--"){
            $memoryCacheUsedStr = "0B";
        }
        if($memory_cache_space!=0){
            $memory_free_space = $memory_cache_space - $memory_cache_used;
            $info['agent_memory_cache_des'] = $used_str." ".$utils->calSize($memory_free_space)." / ".$free_space_str." ".$memoryCacheUsedStr;
            $memory_cache_flag = true;
        }

        $file_free_space = 0;
        if($file_cache_space!=0){
            $file_free_space = $file_cache_space - $file_cache_used;
        }
        $fileCacheUsedStr = $utils->calSize($file_cache_used);
        if($fileCacheUsedStr=="--"){
            $fileCacheUsedStr = "0B";
        }
        $info['agent_file_cache_des'] = $used_str." ".$utils->calSize($file_free_space)."/".$free_space_str." ".$fileCacheUsedStr;
        $info['file_cach_path'] = $file_cache_path;
        $info['memory_cache_flag'] = $memory_cache_flag;
        return $info;
    }

    /**
     * 获取自动接管相关配置
     * @param task_uuid,auto_takeover_flag,task_type
     * @retrun array
     */
    public  function getAutoTakeoverConfInfo($task_uuid,$auto_takeover_flag,$task_type){
        $take_info = array();
        if($auto_takeover_flag==1 && Xphp::$_config['TASKTYPE']['BACKUP']){
            try {
                $sql = "select takeover_standby_agent_uuid,agent_failback_standby_ip,app_takeover_flag,app_consecutive_failure_num,
                        app_fault_detection_interval,agent_heartbeat_failure_time,takeover_vm_hypervisor,takeover_vm_config
                    from cdp_vol_task_takeover_info
                    where task_uuid = ? and takeover_type = ? ";
                $data = $this->dbSelect($sql, array($task_uuid,$auto_takeover_flag));

                $failbackup_standby_ip = $data[0]['agent_failback_standby_ip'];
                $app_takeover_flag = $data[0]['app_takeover_flag'];
                $app_consecutive_failure_num = $data[0]['app_consecutive_failure_num']; //App连续失败次数
                $app_fault_detection_interval = $data[0]['app_fault_detection_interval']; //App故障检测间隔时间
                $takeoverAgentType = $data[0]['takeover_vm_hypervisor'];  //虚拟机模块定义
                $heartbeatFailureTime = $data[0]['agent_heartbeat_failure_time'];
                $consoleUrl = "";
                $tempStatus = 0;
                $prefixStatus = 0;
                if($takeoverAgentType == Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){
                    $standby_agent = $this->getHostInfo($data[0]['takeover_standby_agent_uuid']);
                    $take_info['standby_agent'] = $standby_agent; //接管备机
                }else if($takeoverAgentType != Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){  //内嵌虚拟机
                    $takeoverVmConfig = $data[0]['takeover_vm_config'];
                    $vmConfig = json_decode($takeoverVmConfig);
                    $hostName = $vmConfig->vm_name;
                    $take_info['standby_agent'] = $hostName; //接管备机
                    $tempConsol = $this->getTempAgentConsoleUrl($data[0]['takeover_standby_agent_uuid']);
                    $consoleUrl = $tempConsol['console_url'];
                    $tempStatus = $tempConsol['status'];
                    $prefixStatus = $tempConsol['prefix_status'];
                }


                $serverIp = $_SERVER['SERVER_ADDR'];
                $consoleUrl = str_replace("0.0.0.0:6080", $serverIp . '/web_console', $consoleUrl);
				 $consoleUrl = 'https://' . $_SERVER['HTTP_HOST'] . ':6080/jump.html?url=' . urlencode($consoleUrl);
                $take_info['failbackup_ip'] = $failbackup_standby_ip;    //接管恢复IP
                $take_info['app_takeover_flag'] = $app_takeover_flag; //是否启用自动接管
                $take_info['app_consecutive_failure_num'] = $app_consecutive_failure_num; //App连续失败次数
                $take_info['app_fault_detection_interval'] = $app_fault_detection_interval; //App故障检测间隔时间
                $take_info['heartbeat_failure_time'] = $heartbeatFailureTime;  //心跳故障最大检测时间
                $take_info['script_info'] = $this->get_takeover_script($task_uuid);
                $take_info['temp_console_url'] = $consoleUrl;
                $take_info['takeover_agent_type'] = $takeoverAgentType;
                $take_info['temp_status'] = $tempStatus;
                $take_info['prefix_status'] = $prefixStatus;
                $take_info['takeover_standby_agent_uuid'] = $data[0]['takeover_standby_agent_uuid'];
                if($takeoverAgentType != Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){   //模板虚拟机
                    $this->updateTempAgent($task_uuid);
                }
            } catch (Exception $e) {
                $take_info = array();
            }
        }
        return  $take_info;
    }
    /**
     * @return void
     * 更新模板主机状态
     **/
    private function  updateTempAgent($task_uuid){
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($task_uuid);   //任务UUID对应的存储节点uuid

        $opName = 'TEMP_AGENT_VM_OP_GET_STATUS_ALL_EMD';  //停止接管启动回切
        $msg = [
            'node_uuid' => $nodeuuid,
            'temp_agent_uuid' => '',
            'hypervisor_type' => '',
        ];
        $msg = json_encode($msg);
        $mbResult = $this->mbTempAgentMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $pfOpcode = Xphp::instance('VolCDPOpcode');
        $operate = $pfOpcode->getOpcodeDes($opName);
        return $msg;
        //返回结果到UI
//        if($result){
//            return $this->muOpResult($result, $operate, $msg);
//        }else{
//            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
//        }
    }

    /**
     * 获取手动接管相关配置信息
     * @param task_uuid,task_type
     * @return array
     */
    public function getHandoverConfInfo($task_uuid,$task_type){
        $handOverConf = array();
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        if(Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER'] == $task_type){
            try {
                $sql = "select takeover_standby_agent_uuid,agent_failback_standby_ip,app_takeover_flag,takeover_timestamp,
                        master_agent_ip_switch_flag,takeover_vm_config,takeover_agent_role,takeover_vm_hypervisor  
                     FROM cdp_vol_task_takeover_info 
                    where  task_uuid = ? and takeover_type = ?";
                $data = $this->dbSelect($sql,array($task_uuid,$VolCdpDes['TAKEOVER_TYPE']['HAND_OVER']));
                // $standby_agent = $this->getHostInfo($data[0]['takeover_standby_agent_uuid'],$data[0]['takeover_vm_hypervisor']);
                $failbackup_standby_ip = $data[0]['agent_failback_standby_ip'];
                $app_takeover_flag = $data[0]['app_takeover_flag'];
                $tempInfo = $this->getTempAgentConsoleUrl($data[0]['takeover_standby_agent_uuid']);
                $takeoverStandbyAgentuuid = $data[0]['takeover_standby_agent_uuid'];
                $takeoverAgentType = $data[0]['takeover_vm_hypervisor'];
                if($takeoverAgentType != Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){
                    $takeoverVmConfig = $data[0]['takeover_vm_config'];
                    $vmConfig = json_decode($takeoverVmConfig);
                    $standby = $vmConfig->vm_name;
                    $standbyAgent =   $standby;
                }else{
                    $sqlAgent = "SELECT hostname,ip,agent_name FROM bd_agent where agent_uuid = ?";
                    $dataAgent = $this->dbSelect($sqlAgent, array($takeoverStandbyAgentuuid));
                    $hostName = $dataAgent[0]['hostname'];
                    $ip = $dataAgent[0]['ip'];
                    $agentName = $dataAgent[0]['agent_name'];
                    $standbyAgent = $this->agentStr($agentName,$hostName,$ip); 
                }
               
                $handOverConf['failbackup_ip'] = $failbackup_standby_ip;
                $handOverConf['standby_agent'] = $standbyAgent; //接管备机
                $handOverConf['app_takeover_flag'] = $app_takeover_flag; //是否启用自动接管
                $handOverConf['script_info'] = $this->get_takeover_script($task_uuid);
                $handOverConf['takeover_timestamp'] = $data[0]['takeover_timestamp'];
                $handOverConf['master_ip_switch'] = $data[0]['master_agent_ip_switch_flag'];
                $handOverConf['temp_console_url'] = $tempInfo['console_url'];
                $handOverConf['temp_status'] = $tempInfo['status'];
                $handOverConf['prefix_status'] = $tempInfo['prefix_status'];
                $handOverConf['takeover_agent_type'] = $takeoverAgentType;
                $handOverConf['takeover_agent_role'] = $data[0]['takeover_agent_role'];
                $handOverConf['takeover_standby_agent_uuid'] = $data[0]['takeover_standby_agent_uuid'];
                if($takeoverAgentType != Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){   //模板虚拟机
                    $this->updateTempAgent($task_uuid);
                }
            }catch (Exception $e){
                $handOverConf = array();
            }
        }
        return $handOverConf;
    }
    /**
     * 获取任务监控脚本信息
     * @param string $task_uuid
     * @return script array
     */
    private  function get_takeover_script($task_uuid){
        $script_info = array();
        $scriptList = array();
        $sql = "select script_type,script_path,exec_type,exec_interval,trigger_fail_num from cdp_vol_task_takeover_script where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        if(!empty($data)){
            foreach ($data as $d){
                $script_info['script_type'] = $d['script_type'];
                $script_info['script_path'] = $d['script_path'];
                $script_info['exec_type'] = $d['exec_type'];
                $script_info['exec_interval'] = $d['exec_interval'];
                $script_info['trigger_fail_num'] = $d['trigger_fail_num'];

                $scriptList[]=$script_info;
            }
        }
        return $scriptList;
    }
    /**
     * 获取客户主机别名及IP信息
     * @param string agent_uuid
     * @return string agent_info
     */
    private  function getHostInfo($agent_uuid,$takeover_agent_type =0){
        if($takeover_agent_type==Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){
            $sql = "SELECT hostname,ip,agent_name FROM bd_agent where agent_uuid = ?";
            $data = $this->dbSelect($sql, array($agent_uuid));
            $hostName = $data[0]['hostname'];
            $ip = $data[0]['ip'];
            $agentName = $data[0]['agent_name'];
            $agentInfo = $this->agentStr($agentName,$hostName,$ip);
        }else if($takeover_agent_type != Xphp::$_config['VMHYPERVISORTYPE']['VM_HYPERVISOR_TYPE_UNKNOWN']){
            $sql = "select name from vm_emd where uuid = ? ";
            $data = $this->dbSelect($sql, array($agent_uuid));
            $agentInfo = $data[0]['name'];
        }
        return $agentInfo;
    }
    /*
     * 获取模板主机console url
     */
    private function getTempAgentConsoleUrl($uuid){
        $sql = "select console_url,status,prefix_status from vm_emd where uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        if(!empty($data)){
            $consoleUrl = $data[0]['console_url'];
            $serverIp = $_SERVER['SERVER_ADDR'];
            $consoleUrl = str_replace("0.0.0.0:6080", $serverIp . '/web_console', $consoleUrl);
			$consoleUrl = 'https://' . $_SERVER['HTTP_HOST'] . ':6080/jump.html?url=' . urlencode($consoleUrl);
            $tempStatus = $data[0]['status'];
            $prefixStatus = $data[0]['prefix_status'];
        }else{
            $tempStatus = 0;
            $consoleUrl = "";
            $prefixStatus = 0;
        }
        $tempInfo = array(
            'status' => $tempStatus,
            'console_url' => $consoleUrl,
            'prefix_status' => $prefixStatus,
        );
        return $tempInfo;
    }
    /**
     * 停止卷CDP接管任务
     * @param unknown $params
     */
    public function stopTakeoverJob($params){
        $uuid = $params['uuid'];
        $this->paramsCheck($uuid);

        $status = $params['status'];
        $module = intval($params['module']);
        $subModule = intval($params['subModule']);

        $taskType = $params['taskType'];
        $startType = intval($params['startType']);
        $this->checkTaskType($taskType);

        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($uuid);   //任务UUID对应的存储节点uuid

        $pfMSg = array();
        $task_list = array($uuid);
        $opName = "BD_TASK_OP_TAKEOVER_STOP";  //停止接管
        $msg = array(
            'task_uuid_set' => $task_list,
            'control_code' => $opName,
            'backup_mode' => $startType,
            'time_strategy_id' => 0,
            'auto_start_flag' => Xphp::$_config['FLAG']['UNSET'],
        );
        $msg = json_encode($msg);
        $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg, $sync, $command);

        $result = $mbResult['result'];
        $operate = $this->getUnifyOpcodeDes($opName);
        $msg = $mbResult['msg'];
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }
    /**
     * 启动接管任务/自动接管任务回切
     * @param unknown $params
     */
    public function startTaskCatBack($params){
        $uuid = $params['uuid'];
        $this->paramsCheck($uuid);
        $taskType = $params['taskType'];
        $this->checkTaskType($taskType);

        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($uuid);   //任务UUID对应的存储节点uuid

        $pfMSg = array();
        $task_list = array($uuid);
        $opName = "VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_START";  //停止接管,启动回切
        $msg = array(
            'task_uuid_set' => $task_list,
            'control_code' => $opName,
            'dev_type' => 1,   //备份类型 1卷 2磁盘
        );
        $msg = json_encode($msg);
        $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg, $sync, $command);
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
     * 获取操作名
     * @param string $opName
     */
    private function getUnifyOpcodeDes($opCode){
        $pfOpcode = Xphp::instance('PFOpcode');
        $operate = $pfOpcode->getOpcodeDes($opCode);
        if($operate == Xphp::$_lang['WEB_PUBLIC_OPRATION_UNKNOWN']){
            $pfOpcode = Xphp::instance('VMOpcode');
            $operate = $pfOpcode->getOpcodeDes($opCode);
        }
        return $operate;
    }
    /**
     * 检查任务操作的任务类型参数
     * @param int $taskType
     * @return boolean
     */
    private function checkTaskType($taskType){
        $taskType = intval($taskType);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        if($taskTypeConf['BACKUP'] == $taskType || $taskTypeConf['RECOVERY'] == $taskType ||
            $taskTypeConf['VM_INSTANT_RECOVERY'] == $taskType || $taskTypeConf['VM_INSTANT_RECOVERY_MOTION'] == $taskType ||
            $taskTypeConf['ORCH_TASK'] == $taskType || $taskTypeConf['VM_CDP_BACKUP'] == $taskType ||
            $taskTypeConf['VM_FILE_RECOVERY'] == $taskType ||
            $taskTypeConf['BACKUP_COPY'] == $taskType || $taskTypeConf['BACKUP_COPY_FETCH'] == $taskType ||
            $taskTypeConf['ARCHIVE'] == $taskType || $taskTypeConf['ARCHIVE_FETCH'] == $taskType ||
            $taskTypeConf['DB_CDP_BACKUP'] == $taskType || $taskTypeConf['DB_CDP_RECOVERY'] == $taskType ||
            $taskTypeConf['FILE_BACKUP_COPY'] == $taskType || $taskTypeConf['FILE_BACKUP_COPY_FETCH'] == $taskType ||
            $taskTypeConf['DB_BACKUP'] == $taskType || $taskTypeConf['DB_RECOVERY'] == $taskType ||
            $taskTypeConf['FILE_CDP_BACKUP'] == $taskType || $taskTypeConf['FILE_CDP_RECOVERY'] == $taskType ||
            $taskTypeConf['DB_BACKUP_COPY'] == $taskType || $taskTypeConf['DB_BACKUP_COPY_FETCH'] == $taskType ||
            $taskTypeConf['VOL_CDP_BACKUP'] == $taskType || $taskTypeConf['VOL_CDP_TAKEOVER'] == $taskType ||
            $taskTypeConf['SURE_BACKUP'] == $taskType || $taskTypeConf['OS_BACKUP'] == $taskType || $taskTypeConf['OS_RECOVERY'] == $taskType){
            return true;
        }else{
            $this->writeLog("task operation tasktype error.", 4);
            exit($this->muOpResult(false, Xphp::$_lang['WEB_OPHANDLER_PARAMS_CHECK'], Xphp::$_lang['WEB_JOB_TYPE_ERROR'], 'warning'));
        }
    }
    /**
     * 获取当前任务所有执行卷的实时同步有效数据总和
     * @param task_uuid
     */
    public function getTaskCurrentTotalSize($taskUuid){
        $utils = Xphp::instance('Utils');
        $sql = "select total_object_completed_valid_size from bd_running_info where task_uuid = ?";
        $data = $this->dbSelect($sql,array($taskUuid));
        $totalVaildSize = "--";
        if(!empty($data)){
            $sizeFlag = true;
            if($data[0]['total_object_completed_valid_size']==0){
                $sizeFlag = false;
            }
            $totalVaildSize = $utils->calSize($data[0]['total_object_completed_valid_size'], $sizeFlag);
        }
        return $totalVaildSize;
    }
    /**
     * 创建手动标签并添加描述
     * @param unknown $params
     */
    public function createLablePoint($params){
        $uuid = $params['uuid'];
        $labelDesc = $params['remark'];
        $this->paramsCheck($uuid);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $sync = FALSE;
        $command = TRUE;
        $nodeHandler = Xphp::instance('NodeHandler');
        $nodeuuid = $nodeHandler->getNodeUUIDWithTaskUUID($uuid);   //任务UUID对应的存储节点uuid

        $pfMSg = array();
        $task_list = array($uuid);
        $opName = "VOL_CDP_TASK_CREATE_CONSISTENCY_LABEL";  
        $msg = array(
            'task_uuid' => $uuid,
            'label_remarks' => $labelDesc,
            'auto_start_flag'=> 0,
            'time_strategy_id'=> 0
        );

        $msg = json_encode($msg);
        $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg, $sync, $command);
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
     * 获取备份任务当前运行状态
     * @param unknown $taskUuid
     */
    public function getVolCdpTaskRunningStage($taskUuid){
        $sql = "select current_task_running_stage from cdp_vol_task where task_uuid = ?";
        $data = $this->dbSelect($sql,array($taskUuid));
        $runningStage = $data[0]['current_task_running_stage'];
        return $runningStage;
    }
    /**
     * 获取服务端数据一致性校验的容量信息
     * @param $taskUuid
     */
    public function getServerConsCheckInfo($taskUuid){
        $sql = "select total_size,completed_size,current_vol_uuid from cdp_vol_task_data_consistency_check_info where task_uuid = ?";
        $data = $this->dbSelect($sql,array($taskUuid));
        $totalSize = "--";
        $completedSize = "0";
        $totalProgress = "0";
        $currentVol = "--";
        if(!empty($data)){
            $utils = Xphp::instance('Utils');
            $totalSize = $data[0]['total_size'];
            $completedSize = $data[0]['completed_size'];
            $currentVoluuid = $data[0]['current_vol_uuid'];
            $currentVol = $this->getAgentVol($currentVoluuid);

            $speed = $utils->calPercent($totalSize, $completedSize);
            $totalProgress = sprintf("%.2f",substr($speed, 0, -1)) . "%";
        }
        $consData = array(
            'total_size' => $totalSize,
            'completed_size' => $completedSize,
            'total_progress' => $totalProgress,
            'current_vol' => $currentVol,
        );
        return $consData;
    }
    /**
     * 获取单个模块的任务信息
     * @param unknown $params
     */
    public function getCurrentVolCdpJobInfo($params){
        $p = json_decode($_POST['p'], true);
        $start = $p['start'];
        $length = $p['length'];
        $search = $p['search'];
        $draw = $params['draw'];
        if($params['start'] > $start){
            $start = $params['start'];
        }
        if($params['length'] > $length){
            $length = $params['length'];
        }
        $sortColumn = $params['sortColumn'];
        $sortType = $params['sortType'];

        $sortArr = array('bt.task_name','bt.task_type', 'cdp_task.master_agent_uuid', 'bn.node_uuid','bt.create_time','bt.task_status',
            'cdp_task.current_task_running_stage', '', 'bri.speed','', '','' );

        $utils = Xphp::instance('Utils');
        $taskName = $search['name'];
        $taskName = $utils->escapeWildcard($taskName);
        $accurateFlag = $params['accurateFlag'];
        $sql = "select DISTINCT bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, unix_timestamp(bt.create_time) create_time,
                    bu.user_name,bt.task_status, bt.strategy_id, bn.node_uuid,bri.total_object_size, bri.total_object_completed_size,
                    bri.total_object_valid_size,bri.total_object_completed_valid_size, bri.speed, bri.speed_time, 
                    unix_timestamp(bri.start_time) start_time,cdp_task.master_agent_uuid,cdp_task.current_task_running_stage
                from bd_running_info bri,bd_user bu,cdp_vol_task cdp_task, bd_task bt left join bd_node bn on bt.node_uuid = bn.node_uuid
                where bt.task_uuid = bri.task_uuid and bt.user_uuid = bu.user_uuid  and cdp_task.task_uuid = bt.task_uuid
                    and bt.delete_flag = ? and bt.task_type != ? and bt.module_type = ? ";

        $sqlCount = $sql;
        $sqlParams = array(Xphp::$_config['FLAG']['UNSET'], Xphp::$_config['TASKTYPE']['BACKUP_EXPORT'], Xphp::$_config['MODULE_TYPE']['VOL_CDP']);
        $sqlCountParams = array(Xphp::$_config['FLAG']['UNSET'],  Xphp::$_config['TASKTYPE']['BACKUP_EXPORT'], Xphp::$_config['MODULE_TYPE']['VOL_CDP']);

        if(!empty($taskName) && !$accurateFlag){  //按任务名搜索
            $sql .= " and bt.task_name like '%" . $taskName . "%' ";
            $sqlCount .= " and bt.task_name like '%" . $taskName . "%' ";
        }
        $sql .= "and bt.user_uuid = ? ";
        $sqlCount .= "and bt.user_uuid = ? ";
        $sqlParams = array_merge($sqlParams, array(Xphp::$_user['useruuid'], $start, $length));
        $sqlCountParams = array_merge($sqlCountParams, array(Xphp::$_user['useruuid']));
        if(!empty($accurateFlag)){
            $search = $params['search'];
            $taskType = intval($search['volcdpTasktype']);
            $taskStatus = intval($search['volcdpStatus']);
            $taskName = $search['volCdpTaskName'];
            $taskName = $utils->escapeWildcard($taskName);
            $startTime = $search['startTime'];
            $endTime = $search['endTime'];
            $nodeuuid = $search['volCdpNode'];

            $sql .= " and (bt.task_type = ". $taskType ." or ". $taskType ." = '') 
        			and (bt.task_status = ". $taskStatus ." or ". $taskStatus ." = '') 
        			and bt.task_name like '%" . $taskName . "%' ";
            $sqlCount .= " and (bt.task_type = ". $taskType ." or ". $taskType ." = '') 
                    and (bt.task_status = ". $taskStatus ." or ". $taskStatus ." = '') 
                    and bt.task_name like '%" . $taskName . "%' ";
            if(!empty($nodeuuid)){
                $sql .= " and (bt.node_uuid = '". $nodeuuid ."' or '". $nodeuuid ."' = '') ";
                $sqlCount .= " and (bt.node_uuid = '". $nodeuuid ."' or '". $nodeuuid ."' = '') ";
            }

            if(!empty($startTime) && !empty($endTime)){ //如果填了开始时间范围查询
                $sql .= " and bt.create_time between '". $startTime ."' and '". $endTime ."' ";
                $sqlCount .= " and bt.create_time between '". $startTime ."' and '". $endTime ."' ";
            }
        }
        $sql .= " order by  $sortArr[$sortColumn]  $sortType, bt.id desc limit ? , ? ";

        $data = $this->dbSelect($sql, $sqlParams);
        $count = $this->dbSelect($sqlCount, $sqlCountParams);
        $records = array();
        $ptDes = include APP_PATH . 'platform/PFDescription.php';
        $nodeHandler = Xphp::instance('NodeHandler');
        $agentHandler = Xphp::instance('AgentHandler');

        $jobHandler = Xphp::instance('JobHandler');
        $records["data"] = array();
        foreach ($data as $d){
            $agentInfo = $this->getVolCdpTaskAgentInfo($d['task_uuid'],$d['task_type'],$d['master_agent_uuid']);
            $records["data"][] = array(
                $d['task_name'],
                $ptDes['TASKTYPEDES'][$d['task_type']], //任务类型
                $agentInfo,
                $nodeHandler->getNodeName($d['node_uuid']),
                $this->parseDate($d['create_time']),
                $ptDes['TASKSTATUSDES'][$d['task_status']],
                $ptDes['CDP_TASK_RUNNING_STAGE'][$d['current_task_running_stage']], //当前任务运行阶段
                $jobHandler->getTimeInterval($d['start_time'], $d['task_status']),
                $jobHandler->getCurrentJobSpeed($d['task_status'], $d['speed'], $d['speed_time']),
                $jobHandler->getCurrentTaskProgress($d),
                $d['user_name'], //10
                $this->getTaskOpCodeByType($d['task_uuid'],$d['task_type'], $d['task_status']),
                $this->getOpInfo($d),
                $this->getJobOtherInfo($d,$agentInfo),
                $d['task_status'],
                $this->getBackupStrategy($d['strategy_id']),
                $d['task_type'],
                $d['total_object_size'],
                $d['total_object_completed_size'],
                $d['current_task_running_stage']

            );
        }
        $records["draw"] = $draw;
        $records["recordsTotal"] = count($count);
        $records["recordsFiltered"] = count($count);
        return  json_encode($records);
    }
    /**
     * 根据任务类型获取模块对应的客户端信息
     * @param $taskUuid,$taskType,$masterAgentUuid
     */
    private  function getVolCdpTaskAgentInfo($taskUuid,$taskType,$masterAgentUuid){
        $agentHandler = Xphp::instance('AgentHandler');
        $agentInfo = "--";
        switch ($taskType){
            case Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP']:
            case Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION']:
                $sql = "select agent_name,hostname,ip,os_type from bd_agent where agent_uuid = ?";
                $data = $this->dbSelect($sql,array($masterAgentUuid));
                if(!empty($data)){
                    $hostName = $data[0]['hostname'];
                    $agentName = $data[0]['agent_name'];
                    $ip = $data[0]['ip'];
                    $agentInfo = $this->agentStr($agentName,$hostName,$ip);
                }
                break;
            case Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']:
                $sql = "select agent.master_agent_detail from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol_set,
                        cdp_vol_task task,cdp_vol_task_vol task_vol 
                    where vol_set.backup_agent_id = agent.id and agent.master_agent_uuid = ? 
                    and task_vol.recovery_target_timestamp BETWEEN vol_set.start_timestamp and vol_set.end_timestamp 
                    and task.master_agent_uuid = agent.master_agent_uuid
                    and task.task_uuid = task_vol.task_uuid and task.task_uuid =?";
                $data = $this->dbSelect($sql,array($masterAgentUuid,$taskUuid));
                if(!empty($data)){
                    $hostDetal = $data[0]['master_agent_detail'];
                    $masterAgentDetail = json_decode($hostDetal);
                    $hostName = $masterAgentDetail->hostname;
                    $ip = $masterAgentDetail->ip;
                    $agentName = $masterAgentDetail->agent_name;
                    $agentInfo = $this->agentStr($agentName,$hostName,$ip);
                }
                break;
            case Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER']:
                $sql = "select agent.master_agent_detail from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol_set,cdp_vol_task_takeover_info takeover
                        where agent.master_agent_uuid = ?  and agent.id = vol_set.backup_agent_id and takeover.task_uuid = ?
                        and takeover.takeover_timestamp BETWEEN vol_set.start_timestamp and vol_set.end_timestamp";
                $data = $this->dbSelect($sql,array($masterAgentUuid,$taskUuid));
                if(!empty($data)){
                    $hostDetal = $data[0]['master_agent_detail'];
                    $masterAgentDetail = json_decode($hostDetal);
                    $hostName = $masterAgentDetail->hostname;
                    $ip = $masterAgentDetail->ip;
                    $agentName = $masterAgentDetail->agent_name;
                    $agentInfo = $this->agentStr($agentName,$hostName,$ip);
                }
                break;
        }
        return  $agentInfo;
    }
    /**
     * TODO 临时站位使用,后续标签策略完善后将获取标签策略相关信息
     * 获取备份时间策略
     * @param string $strategyID
     */
    private function getBackupStrategy($strategyID){
        $sql = "select unix_timestamp(start_time) start_time, mode,strategy_type from bd_time_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql,array($strategyID));
        $info = array();
        $type = 0;
        $flag = false;
        foreach ($data as $d){
            $info[] = intval($d['mode']);
            if($d['strategy_type'] == 4){
                $type = intval($d['strategy_type']);
                if(intval($d['start_time']) <= time()){
                    $flag = true;
                }
            }
        }
        $strategy = array(
            'modeList' => $info,
            'type' => $type,
            'timeout' => $flag
        );
        return $strategy;
    }
    /**
     * 根据任务状态得到任务能够进行的操作码数组
     * @param int $status
     * @return array 启动1/停止2/修改3/删除4/暂停5/6启动差异/7启动增量/8启用策略/9迁移/10启动完备/11接管/12停止接管/13日志备份 /14 启动回切(新增)/15 停止回切(新增)/16创建标签点
     * @modify 将任务控制数字定义移植到配置文件中,读取Array TASK_CONTROL中对应的元素
     */
    private function getTaskOpCodeByType($taskuuid,$tasktype, $status){
        $status = intval($status);
        $this->paramsCheck($tasktype);
        $taskTypeConf = Xphp::$_config['TASKTYPE'];
        $taskControl = Xphp::$_config['TASK_CONTROL'];
        $opCode = array();
        switch ($tasktype){
            case $taskTypeConf['VOL_CDP_BACKUP']: //卷CDP暂时添加上述可控制项
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
//                     $taskControl['MODIFY'],
                    $taskControl['DELETE'],
                    $taskControl['STOP_TAKEOVER'],
                    $taskControl['START_FAILBACK'],
                );
                break;
            case $taskTypeConf['VOL_CDP_RECOVERY']: //卷CDP恢复添加上述可控制项
                $opCode = array(
                    $taskControl['START'],
                    $taskControl['STOP'],
                    $taskControl['DELETE']
                );
                break;
            case $taskTypeConf['VOL_CDP_TAKEOVER']: //手动接管添加上述可控制项
                $opCode = array(
                    $taskControl['START_TAKEOVER'],
                    $taskControl['STOP_TAKEOVER'],
                    $taskControl['START_FAILBACK'],
                    $taskControl['DELETE']
                );
                break;
            default:
                break;
        }
        return $opCode;
    }
    /**
     * 得到每个任务的操作控制辅助信息,用于链接到任务详情和任务控制
     * @param array $d
     * @return array
     */
    private function getOpInfo($d){
        $opInfo = array(
            'uuid'=>$d['task_uuid'],
            'module'=>intval($d['module_type']),
            'taskType'=>intval($d['task_type']),
            'subModule'=>0,
            'status' => intval($d['task_status']),
            'tenantuuid' => $_SESSION['tenantuuid'],
            'runningStage' => $d['current_task_running_stage']
        );
        return $opInfo;
    }
    /**
     * 得到每个任务的额外信息展示            策略信息/保留策略/存储信息/其他信息
     * @param array $d 每一条数据
     * @return array
     */
    private function getJobOtherInfo($d,$agentInfo){
        $info = array();
        $reservedStrategy = false;
        $createTime = $this->parseDate($d['create_time']);
        $masterAgent =  $agentInfo;
        $backupDetail = false;
        $recoveryDetail = false;
        $takeoverDetail = false;
        $jobHandler = Xphp::instance('JobHandler');
        if(Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] == intval($d['task_type']) || Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION'] == intval($d['task_type'])){  //备份
            $reservedStrategy = $jobHandler->getReservedStrategy($d['strategy_id'],$d['task_uuid']);
            $backupDetail = $this->getBackupVolInfo($d['task_uuid']);
        }else if(Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'] == intval($d['task_type'])){  //恢复
            $recoveryDetail = $this->getRecoveryVolInfo($d['task_uuid']);
        }else if(Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER'] == intval($d['task_type'])){  //接管
            $takeoverDetail = $this->getTakeoverDetail($d['task_uuid']);
        }
        $info['reservedStrategy'] = $reservedStrategy;
        $info['agentInfo'] = $agentInfo;
        $info['createTime'] = $createTime;
        $info['backupDetail'] = $backupDetail;
        $info['recoveryDetail'] = $recoveryDetail;
        $info['takeoverDetail'] = $takeoverDetail;

        return $info;
    }
    /**
     * 获取接管卷信息
     * @param unknown $task_uuid
     */
    private function getTakeoverDetail($taskUuid){
        $sql = "SELECT task_vol.vol_uuid,takeover_info.takeover_timestamp,takeover_info.takeover_standby_agent_uuid,task_vol.takeover_standby_real_mount_point
            from cdp_vol_task_vol task_vol,cdp_vol_task_takeover_info takeover_info 
            where task_vol.task_uuid =? and takeover_info.task_uuid = task_vol.task_uuid ";
        $data = $this->dbSelect($sql,array($taskUuid));
        $utils = Xphp::instance('Utils');
        $dataArray = array();
        if(!empty($data)){
            foreach ($data as $d){
                $volUuid = $d['vol_uuid'];
                $takeoverTimestamp = $d['takeover_timestamp'];
                $dataSourceInfo = $this->getDataSourceVolInfo($volUuid,$takeoverTimestamp); //接管数据源卷
                $volDisplayName = $dataSourceInfo['vol_display_name'];
                $capacity = $utils->calSize($dataSourceInfo['capacity'], true);
                $realMountPoint = $d['takeover_standby_real_mount_point'];
                $dataArray[] = array(
                    'display_name' => $volDisplayName,
                    'capacity' => $capacity,
                    'takeover_timestamp' =>$takeoverTimestamp,
                    'real_mount_point' => $realMountPoint
                );
            }
        }
        return $dataArray;
    }
    /**
     * 获取恢复卷信息
     * @param unknown $taskUuid
     */
    private function getRecoveryVolInfo($taskUuid){
        $sql = "select DISTINCT vol_set.vol_display_name,vol_set.capacity,vol.recovery_target_timestamp,vol.recovery_target_vol_uuid,
                vol_task.rebuild_partition_flag,vol.recovery_target_disk_uuid
            from cdp_vol_backup_vol_set vol_set,cdp_vol_task_vol vol,cdp_vol_task vol_task 
            where vol_set.vol_uuid = vol.vol_uuid and vol.task_uuid = ? and vol_task.task_uuid = vol.task_uuid";
        $data = $this->dbSelect($sql,array($taskUuid));
        $utils = Xphp::instance('Utils');
        $dataArray = array();
        if(!empty($data)){
            foreach ($data as $d){
                $volCapacity = $utils->calSize($d['capacity'], true);

                $targetVol = $this->getAgentVol($d['recovery_target_vol_uuid']);    //恢复目标卷
                $targetDisk = $this->getRecoverTargetDisk($d['recovery_target_disk_uuid']);  //恢复磁盘
                $targetInfo = $targetVol;
                if($d['rebuild_partition_flag']==1){
                    $targetInfo = $targetDisk;
                }
                $dataArray[] = array(
                    'display_name' => $d['vol_display_name'],
                    'capacity' => $volCapacity,
                    'recovery_target_timestamp' => $d['recovery_target_timestamp'],
                    'recovery_target_vol' => $targetInfo,
                    'rebuild_partition_flag' => $d['rebuild_partition_flag']
                );
            }
        }
        return $dataArray;
    }
    /**
     * 获取备份卷概要信息
     * @param unknown $taskUuid
     */
    private function getBackupVolInfo($taskUuid){
        $volSql = "SELECT vol.display_name,vol.capacity from cdp_vol_task_vol task_vol,bd_agent_vol vol 
            where vol.vol_uuid = task_vol.vol_uuid and task_vol.task_uuid = ? ";
        $volData = $this->dbSelect($volSql,array($taskUuid));
        $utils = Xphp::instance('Utils');
        $volInfo = array();
        if(!empty($volData)){
            foreach ($volData as $d){
                $volCapacity = $utils->calSize($d['capacity'], true);
                $volInfo[] = array(
                    'display_name' => $d['display_name'],
                    'capacity' => $volCapacity
                );
            }
        }
        return $volInfo;
    }
    /**
     * 得到卷CDP任务传输策略
     * @param string $taskuuid  任务UUID
     * @param int $strategyid   策略ID
     */
    public function  getVolCdpTransportStrategy($taskuuid,$strategyid,$currentTaskRunningStage){
        $info = array();
        $utils = Xphp::instance('Utils');
        $VolCdpDes = include APP_PATH . 'volcdp/VolCDPDescription.php';
        $sql = "select str.encrypt_flag, str.compress_flag,str.block_size,bnn.ip,bnn.port, bnn.alias_name,
                str.max_transport_speed, str.compress_method,str.reconnect_times,str.reconnect_interval, str.encrypt_method
                from bd_transport_strategy str left join bd_node_network bnn on str.network_uuid = bnn.network_uuid
                where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        $encrypt = $data[0]['encrypt_flag'];
        $compress = $data[0]['compress_flag'];
        $transportBlockSize = $data[0]['block_size'];
        $compressMethod = $data[0]['compress_method'];
        //获取当前任务是否配置回切，如果配置回切且任务阶段处于回切阶段需要获取回切的相关缓存配置，其他任务阶段获取任务配置的缓存配置
        if($currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_INIT_SYNC']
            || $currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_REALTIME_SYNC']
            || $currentTaskRunningStage == $VolCdpDes['TASK_RUNNING_STAGE']['FAILBACK_IN_STARTING']){  //任务处于回切阶段

            $fbSql = "SELECT transport_compress_flag,transport_encrypt_flag,transport_thread_num,transport_block_size,transport_compress_method
                FROM cdp_vol_task_takeover_failback_info  WHERE task_uuid = ? ";
            $fbData = $this->dbSelect($fbSql, array($taskuuid));
            $encrypt = $fbData[0]['transport_encrypt_flag'];
            $compress = $fbData[0]['transport_compress_flag'];
            $transportBlockSize = $fbData[0]['transport_block_size'];
            $compressMethod = $fbData[0]['transport_compress_method'];
        }

        $info['encrypt'] = Xphp::instance('Utils', 'parseFlagToBool', $encrypt);
        $info['compress'] = Xphp::instance('Utils', 'parseFlagToBool', $compress);
        $info['compress_method'] = $compressMethod;
        $info['transport_block_size'] = $utils->calSize($transportBlockSize);
        $info['mode'] = Xphp::$_lang['UI_BACKUP_TRANSPORT_NBD'];
        $info['max_transport_speed'] = $data[0]['max_transport_speed']; //最大传输速度
        $info['reconnect_times'] = $data[0]['reconnect_times'];  //重连次数
        $info['reconnect_interval'] = $data[0]['reconnect_interval']; //重连间隔
        $info['encrypt_method'] = $data[0]['encrypt_method']; //传输加密算法
        $name = "";
        if(!empty($data[0]['ip'])){
            $name = $data[0]['ip'].":".$data[0]['port'];
            if(!empty($data[0]['alias_name'])){
                $name .= "(".$data[0]['alias_name'].")";
            }
        }
        $info['network'] = $name;
        $info['transport_ip'] = $data[0]['ip'];

        return $info;
    }
    /**
     * 获取回切配置
     * @param unknown $taskuuid
     */
    public function getFailbackInfo($taskuuid){
        $sql = "select failback_target_agent_uuid,memory_cache_alloc_space,file_cache_alloc_space,file_cache_storage_path,transport_encrypt_flag,
                    transport_compress_flag,monitor_data_io_replication_mode,storage_uuid,transport_thread_num,transport_block_size,mirror_backup_flag 
                from cdp_vol_task_takeover_failback_info 
                where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $failbackInfo = array();
        if(!empty($data)){
            $failbackInfo['target_agent_uuid'] = $data[0]['failback_target_agent_uuid'];
            $failbackInfo['memory_cache_alloc_space'] = $data[0]['memory_cache_alloc_space'];
            $failbackInfo['file_cache_alloc_space'] = $data[0]['file_cache_alloc_space'];
            $failbackInfo['file_cache_storage_path'] = $data[0]['file_cache_storage_path'];
            $failbackInfo['transport_encrypt_flag'] = $data[0]['transport_encrypt_flag'];
            $failbackInfo['transport_compress_flag'] = $data[0]['transport_compress_flag'];
            $failbackInfo['monitor_data_io_replication_mode'] = $data[0]['monitor_data_io_replication_mode'];
            $failbackInfo['storage_uuid'] = $data[0]['storage_uuid'];
            $failbackInfo['transport_thread_num'] = $data[0]['transport_thread_num'];
            $failbackInfo['transport_block_size'] = $data[0]['transport_block_size'];
            $failbackInfo['mirror_backup_flag'] = $data[0]['mirror_backup_flag'];
        }
        return $failbackInfo;
    }

    /**
     * 获取任务开始时间，因备份任务更新到bd_running_info start_time自动值为客户端时间，会在一些条件下无法正常获取开始，需要分别获取
     * @param 开始时间 : $startTime
     * @param 任务状态: $taskStatus
     * @param 任务uuid $taskuuid
     * @param 任务类型 $taskType
     */
    public  function getTaskStartTime($startTime,$taskStatus,$taskuuid,$taskType){
        if($taskType==Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] ||  $taskType==Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION']){
            $sql = "select dst_start_timepoint from bd_backup_timepoint where task_uuid = ? ORDER BY id DESC LIMIT 0,1";
            $data = $this->dbSelect($sql, array($taskuuid));
            if(!empty($data)){
                $startTime = $data[0]['dst_start_timepoint'];
            }else{
                return 0;
            }
        }
        return $startTime;
    }

    /**
     * 根据条件组合客户端的名称
     */
    public function agentStr($agentName,$hostName,$ip){
        if(empty($agentName)&&empty($hostName) && empty($ip)){
            return "";
        }
        if (!empty($agentName) && $agentName != $ip) {
            $hostInfo =  $agentName ? $agentName."(". $ip .")" : $hostName."(". $ip .")";
        }else {
            $hostInfo = $hostName."(". $ip .")";
        }
        return $hostInfo;
    }
    /**
     * 获取任务所有卷已完成容量总和
     * @param unknown $taskuuid
     */
    public function getTaskTotalCapacityInfo($taskuuid){
        $sql = "select total_size,valid_size,completed_valid_size  from cdp_vol_task_progress_info where task_uuid = ?";
        $data = $this->dbSelect($sql,array($taskuuid));
        $completedSize = 0;
        foreach ($data as$d){
            $volSize = 0;
            if($d['completed_valid_size']!=0){
                $volSize = $d['total_size']*($d['completed_valid_size']/$d['valid_size']);
            }
            $completedSize +=$volSize;
        }
        return $completedSize;
    }

    /**
     * @param $params 获取卷cdp模块启动任务对象详细信息
     */
    public function  getStartTaskObjectDetail($params){
        $taskuuid = $params['task_uuid'];
        $taskType = $params['task_type'];
        $startType = $params['start_type'];
        $agentInfo = array();
        //判断执行对象任务类型为备份或接管，且执行操作必须为启动接管
        if((Xphp::$_config['TASKTYPE']['VOL_CDP_TAKEOVER'] == $taskType || Xphp::$_config['TASKTYPE']['VOL_CDP_BACKUP'] == $taskType || Xphp::$_config['TASKTYPE']['VOL_CDP_REPLICATION'] == $taskType)
            && $startType == Xphp::$_config['TASK_CONTROL']['START_FAILBACK']){
            $sql = "SELECT takeover_info.takeover_standby_agent_uuid,takeover.failback_target_agent_uuid,
                            vol.takeover_failback_target_vol_uuid,vol.recovery_target_disk_uuid 
                    from cdp_vol_task_takeover_info takeover_info,cdp_vol_task_takeover_failback_info takeover,
                         cdp_vol_task_vol vol 
                    where takeover_info.task_uuid = takeover.task_uuid and takeover_info.task_uuid = ? 
                         and vol.task_uuid = takeover_info.task_uuid ";
            $data = $this->dbSelect($sql,array($taskuuid));
            if(!empty($data)){
                $taskInfo = $data[0];
                $takeoverStandbyAgentuuid = $taskInfo['takeover_standby_agent_uuid'];
                $targetAgentUuid = $taskInfo['failback_target_agent_uuid'];
                $masterAgentObject = $this->getBdAgentInfo($takeoverStandbyAgentuuid);  //数据源主机信息
                $targetAgentObject = $this->getBdAgentInfo($targetAgentUuid);  //目标主机信息
                $masterInfo = $masterAgentObject['agentInfo'];
                $targetMachineInfo = $targetAgentObject['agentInfo'];
                $cutBackupTargetVol = $this->getCutBackupTargetVol($data);
                $agentInfo = array(
                    "master_info" => $masterInfo,
                    "target_machine" => $targetMachineInfo,
                    "target_vol" => $cutBackupTargetVol
                );
            }
        }
        //判断执行对象任务类型为恢复任务，且执行操作必须为启动任务
        else if(Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY'] == $taskType && $startType == Xphp::$_config['TASK_CONTROL']['START']){
            $sql = "SELECT master_agent_uuid,recovery_target_agent_uuid from cdp_vol_task where task_uuid= ?";
            $data = $this->dbSelect($sql,array($taskuuid));
            if(!empty($data)){
                $taskInfo = $data[0];
                $masterAgentUuid = $taskInfo['master_agent_uuid'];
                $recoveryTargetAgentUuid = $taskInfo['recovery_target_agent_uuid'];
                $masterAgentObject = $this->getBdAgentInfo($masterAgentUuid);
                $recoveryTargetAgentObject = $this->getBdAgentInfo($recoveryTargetAgentUuid);
                $masterInfo = $masterAgentObject['agentInfo'];
                $targetMachineInfo = $recoveryTargetAgentObject['agentInfo'];
                $targetVol = array();
                $agentInfo = array(
                    "master_info" => $masterInfo,
                    "target_machine" => $targetMachineInfo,
                    "target_vol" => $targetVol
                );
            }

        }
        return $agentInfo;
    }

    /**
     * 获取回切目标卷信息
     * @param $data
     * @return void
     */
    private function getCutBackupTargetVol($data){
        $volName = array();
        foreach ( $data as $d) {
            $targetVoluuid = $d['takeover_failback_target_vol_uuid'];
            $targetDiskuuid = $d['recovery_target_disk_uuid'];
            $sql = "select vol_name,display_name from bd_agent_vol where vol_uuid = ?";
            $data = $this->dbSelect($sql,array($targetVoluuid));
            if(!empty($data)){
                $volName[] = array(
                    "vol_name"=>$data[0]['display_name']
                );
            }
            $sqlDisk = "select display_name from bd_agent_disk where disk_uuid = '{$targetDiskuuid}'";
            $diskData = $this->dbSelect($sqlDisk);
            if(!empty($diskData)){
                $volName[] = array(
                    "vol_name" => $diskData[0]['display_name']
                );
            }
        }
        return $volName;
    }


}
    