<?php
namespace app\v1\complete_machine_volcdp\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Base;
use app\v1\common\logic\Data;
use app\v1\job\v0\logic\JobInfo as JobInfos;
use app\v1\vm\v0\logic\VmJobInfo;
use app\v1\user\v0\logic\User;
use app\v1\opcode\VolcdpOpcode;
use app\v1\copy\v0\logic\CopyJobInfo;
use app\v1\hadoop\v0\logic\HadoopJobInfo;
use app\v1\opcode\PfOpcode;
use app\v1\os\v0\logic\OsJobInfo;
use app\v1\complete_machine_os\v0\logic\MachineOsBackup;
use app\v1\resources\v0\logic\Client;
use app\v1\resources\v0\logic\Index;
use app\v1\resources\v0\logic\Node;
use app\v1\resources\v0\logic\Storage;
use app\v1\system\v0\logic\Time;
use app\v1\volcdp\v0\validate\VolcdpRecover;

class CmJobInfo extends JobInfos
{
    /**
     * @param array $params 参数
     * @return array
     */
    public function getCmCdpTaskSpeed($params)
    {
        $taskUUID = $params['jobs_uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "SELECT bt.module_type, bri.speed, bri.speed_time, bt.task_status FROM bd_running_info bri, bd_task bt WHERE bri.task_uuid = bt.task_uuid AND bri.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        $speed = 0;
        if ($data) {
            if (
                $data[0]['task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ||
                $data[0]['task_status'] == xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
            ) {
                $speedCount = intval($data[0]['speed']);
                if ($speedCount < 0) {
                    $speedCount = 0;
                }
                $speed = round($speedCount / 1024, 2);
            }
        }
        if (time() - $data[0]['speed_time'] > 12) {
            //如果长时间没有更新速度,处理速度为0
            $speed = 0;
        }

        $data = array(
            'speed' => $speed,
            'test' => $data[0]['speed_time'],
            't' => time(),
            'nowTime' => date('H:i:s')
        );
        return $data;
    }
    
    public function getBasicInfoNew($params)
    {
        $taskUUID = $params['task_uuid'];
        $this->paramsCheck($taskUUID);
    }

    /**
     * 获取回切设备列表  
     * @param mixed $params
     * @return void
     */
    public function getFailbackDevicesList($params)
    { 
        $taskUuid = $params['jobs_uuid'];
        $sql = "select dev_uuid from cdp_vol_task_disk where task_uuid= ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $devUuids = array();
        foreach ($data as $d) {
            $devUuids[] =array(
                'dev_uuid' => $d['dev_uuid']
            );
        }
        return $devUuids;
    }

    /**
     * Summary of getTaskMonitorDeviceInfo
     * @param mixed $params
     * @return void
     */
    public function getTaskMonitorDeviceInfo($params)
    { 
        $start = $params['offset'];
        $length = $params['limit'];
        $taskUuid = $params['task_uuid'];
        $taskType = $params['task_type'];

        $sql = "SELECT current_task_running_stage from cdp_vol_task where task_uuid= ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $taskCurrentStage = 0; //任务阶段

        if(!empty($data)){
            $taskCurrentStage = $data[0]['current_task_running_stage'];
        }
        if($taskType == xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] || $taskType == xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION']){   //备份
            // if($taskCurrentStage== xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['IN_TAKEOVER'] || $taskCurrentStage== xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['IN_TAKEOVER_STARTING']){ //任务阶段处于接管中
                // $taskDevInfo = $this->getTakeoverTaskVolInfo($params);
            // }else{
                $taskDevInfo = $this->getBackupTaskDevInfo($params);
            // }
        }else if($taskType ==xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY'] || $taskType ==xphp_get_config('task','TASKTYPE')['PLATFORM_RECOVERY']){ //恢复
            $taskDevInfo = $this->getRecoverTaskVolInfo($params);

        }else if($taskType ==xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){ //接管
            $taskDevInfo = $this->getTakeoverTaskVolInfo($params);

        }
        return $taskDevInfo;
    }


    /**
     * Summary of getTakeoverTaskVolInfo
     * @param mixed $params
     * @return array
     */
    private function getTakeoverTaskVolInfo($params)  {
        $taskUUID = $params['task_uuid'];
        $start = $params['offset'];
        $length = $params['limit'];
        $records = array();
        $sql = "SELECT cvt.master_agent_uuid,bt.task_type, bt.task_status, cvt.current_task_running_stage,  
                        cvtti.takeover_standby_agent_uuid,cvtti.takeover_vm_hypervisor,cvtti.takeover_vm_config,cvtti.takeover_timestamp, 
                        cvtd.dev_name,cvtd.dev_uuid,cvtd.standby_target_dev_name  
                FROM bd_task AS bt 
                        LEFT JOIN cdp_vol_task AS cvt ON cvt.task_uuid = bt.task_uuid 
                        LEFT JOIN cdp_vol_task_takeover_info AS cvtti ON cvtti.task_uuid = bt.task_uuid
                        LEFT JOIN cdp_vol_task_disk AS cvtd ON cvtd.task_uuid = bt.task_uuid 
                WHERE bt.task_uuid = ?";
        $countSql = $sql;
        $listSql = $sql . " LIMIT ?,? ";
        $countData = $this->dbSelect($countSql, array($taskUUID));
        $data = $this->dbSelect($listSql, array($taskUUID,$start,$length));

        $records = array();
        $i=1;
        foreach ($data as $d){
            $takeoverVmHypervisor = $d['takeover_vm_hypervisor'];  //108备机为内嵌标志
            $dataSourceHost = $this->getDataSourceHosstInfo($d['master_agent_uuid']);  //数据源目标设备    
            $takeoverTargetInfo = $this->getBdAgentInfo($d['takeover_standby_agent_uuid'],$takeoverVmHypervisor);  //接管目标设备 
            $takeoverTargetName = $takeoverTargetInfo['agentInfo'];
            $currentTaskRunningStage = $d['current_task_running_stage'];  //任务阶段
            if($takeoverVmHypervisor == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_EMD']){
                $takeoverVmConfig = $d['takeover_vm_config'];  //内嵌虚拟机配置
                $vmConfig = json_decode($takeoverVmConfig);
                $standby = $vmConfig->vm_name;
                $takeoverTargetName =   $standby."(".xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC').")";
            }
            
            $devName = $d['dev_name'];  //接管数据源设备名
            $devUuid = $d['dev_uuid'];  //接管数据源设备uuid
            $taskType  = $d['task_type'];  //任务类型
            $takeoverMountPoint = $d['standby_target_dev_name'];  //整机接管无实际挂载点
            $takeoverTimeStr = $d['takeover_timestamp'];
            $dataSourceInfo = $this->getDataSourceVolInfo($devUuid,$takeoverTimeStr); //接管数据源卷
            
            $capacity = $dataSourceInfo['capacity'];
            $capacityStr = v1_calsize($capacity, true);
            $capacityInfo = $capacityStr;


            $taskRunningCapacityInfo = $this->getTaskRunningCapacityInfo($taskUUID,$currentTaskRunningStage,$devUuid,$taskType);//设备有效数据运行情况
            $completed = $taskRunningCapacityInfo['completed_size'];
            if($completed== xphp_get_config('app', 'NULLSPACE')){
                $completed = "0B";
            }
            $transferSize = $taskRunningCapacityInfo['transport_size']; //表格第5列数据，在回切状态下获取传输字节大小
            $writeInSize = $taskRunningCapacityInfo['write_size'];  //表格第6列数据，在回切状态下获取写入字节大小
            $taskValidCapacityInfo = $taskRunningCapacityInfo['volume_valid_info'];
            $volumeInfo = $taskRunningCapacityInfo['volume_info'];

            if($currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
                || $currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
                || $currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']){ //任务处于回切阶段
                    $devCapacityInfo = $volumeInfo;
                    $devValidCapacityInfo = $taskValidCapacityInfo;
            }else{
                $devCapacityInfo = $capacityInfo;
                $devValidCapacityInfo = $taskValidCapacityInfo;
            }

            $records[] = array(
                'num' => $i,
                'data_source_host' => $dataSourceHost,
                'takeover_dev' => $devName,
                'data_size' => $devCapacityInfo,
                'valid_data_size' => $devValidCapacityInfo,
                'takeover_time_point' => $takeoverTimeStr,
                'app_is_conf' => xphp_get_lang('UI_BACKUP_DATA_LABEL_WORM_UNSET'),
                'takeover_standby_host' => $takeoverTargetName,
                'mount_point' => $takeoverMountPoint,
                'dev_uuid' => $devUuid,
            );
            $i++;
        };
        $count = count($countData);
        return array(
            'rows' => $records,
            'total' =>$count
        );
    }
      /**
     * 通过恢复数据源卷UUID获取恢复卷信息
     * @param vol_uuid
     */
    private function getDataSourceVolInfo($vol_uuid,$timePoint)
    {
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
     * 获取任务在各个运行阶段下的容量执行信息
     * @param unknown $taskUUID
     * @param unknown $taskRunningStage
     */
    private function getTaskRunningCapacityInfo($taskUUID,$taskRunningStage,$volUUID,$taskType)
    {
        $volumeValidDataInfo = "--";
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
            if($taskRunningStage==xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC'] || $taskRunningStage ==xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']){
                $totalSize = $data[0]['total_size'];
                $totalSizeStr = v1_calsize($totalSize, true);
                $validSize = $data[0]['valid_size'];
                $validSizeStr = v1_calsize($validSize, true);
                $completedValidSize = $data[0]['completed_valid_size'];
                if($completedValidSize== 0 || $validSize==0){
                    $initialSynCapacity = 0;
                }else{
                    $initialSynCapacity = $data[0]['total_size']*($completedValidSize/$validSize);  //初始同步容量采用的总容量*有效数据百分比来获取，确保进度平滑
                }
                $completedValidStr = v1_calsize($completedValidSize, true);
                if($completedValidStr == xphp_get_config('app', 'NULLSPACE')){
                    $completedValidStr = "0B";
                }
                $volumeValidDataInfo = $validSizeStr."/".$completedValidStr;
                $initialSynCapacityStr = v1_calsize($initialSynCapacity, true);
                if($initialSynCapacityStr == xphp_get_config('app', 'NULLSPACE')){
                    $initialSynCapacityStr = "0B";
                }
                $volumeInfo = $totalSizeStr." / ".$initialSynCapacityStr;

                $taskRunningCapacityInfo = array(
                    'completed_size' => v1_calsize($data[0]['completed_size'], $this->verifyValueFalg($data[0]['completed_size'])),
                    'volume_valid_info' =>$volumeValidDataInfo,
                    'volume_info' =>$volumeInfo,
                    'write_size' => v1_calsize($data[0]['write_size'], $this->verifyValueFalg($data[0]['write_size'])),
                    'transport_size' => v1_calsize($data[0]['transport_size'], $this->verifyValueFalg($data[0]['transport_size'])),

                );
            }else if($taskRunningStage==xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']){
                $totalSize = $data[0]['total_size'];
                $totalSizeStr = v1_calsize($totalSize, $this->verifyValueFalg($totalSize));
                $validSize = $data[0]['valid_size'];
                $validSizeStr = v1_calsize($validSize, $this->verifyValueFalg($validSize));
                if($validSizeStr== xphp_get_config('app', 'NULLSPACE')){
                    $validSizeStr = "0B";
                }
                $completedValidSize = $data[0]['completed_valid_size'];
                $completedValidStr = v1_calsize($completedValidSize, $this->verifyValueFalg($completedValidSize));
                $volumeInfo = $totalSizeStr." / ".$validSizeStr;
                $taskRunningCapacityInfo = array(
                    'completed_size' => v1_calsize($data[0]['completed_size'], $this->verifyValueFalg($data[0]['completed_size'])),
                    'volume_valid_info' =>$completedValidStr,
                    'write_size' => v1_calsize($data[0]['write_size'], $this->verifyValueFalg($data[0]['write_size'])),
                    'transport_size' => v1_calsize($data[0]['transport_size'], $this->verifyValueFalg($data[0]['transport_size'])),
                    'volume_info' =>$volumeInfo
                );
            }
        }
        return $taskRunningCapacityInfo;
    }

    /**
     * Summary of getRecoverTaskVolInfo
     * @param mixed $params
     * @return object
     */
    private function getRecoverTaskVolInfo($params) {
        $taskUUID = $params['task_uuid'];
        $start = $params['offset'];
        $length = $params['limit'];
        $sql = "select restore_mode from cdp_vol_task_restore_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID));
        if(!empty($data)){
            $restoreMode = $data[0]['restore_mode'];
            if($restoreMode == xphp_get_config( 'cm_cdp','RESTORE_MODE')['VOL_RECOVERY']){  //数据卷恢复
                $dataList = $this->getVolRecoverMapInfo($taskUUID, $start,$length);
                $taskData = $dataList['data'];
                $countData = $dataList['count'];
            }else{  //整机恢复
                $dataList = $this->getCmReocveryMapInfo($taskUUID, $start,$length);
                $taskData = $dataList['data'];
                $countData = $dataList['count'];
            }
            $count = count($taskData);
        }
        
        $records = array();
        $i=1;
        foreach ($taskData as $d){
            $dataSourceHost = $this->getDataSourceHosstInfo($d['master_agent_uuid']);  //数据源目标设备    
            $recoveryTargetInfo = $this->getBdAgentInfo($d['recovery_target_agent_uuid']);  //恢复目标设备
            $recoveryTargetAHost = $recoveryTargetInfo['agentInfo'];

            $taskRunningStage = $d['current_task_running_stage'];
            if($restoreMode==xphp_get_config('cm_cdp','RESTORE_MODE')['VOL_RECOVERY']){  //数据卷恢复
                $devUuid = $d['vol_uuid'];  //整机备份数据对应的卷级uuid，该值为数据源的磁盘设备及卷组设备uuid
                $recoveryTimePoint = $d['recovery_target_timestamp'];
                $dataSourceDevName = $this->getDevuuidMapInfo($d['master_agent_uuid'],$devUuid,$recoveryTimePoint);

                // $getDataSourceVolInfo = $this->getDataSourceVolInfo($devUuid,$recoveryTimePoint); 
                $devName =  $dataSourceDevName;   

                $recoveryTargetVolUuid = $d['recovery_target_vol_uuid'];
                $recoveryTargetDevName = $this->getAgentVol($recoveryTargetVolUuid);
            }else{
                $recoveryTimePoint = $d['recovery_datetime'];
                $devName = $d['dev_name'];  //数据源磁盘名
                $devUuid = $d['dev_uuid'];  
                
                $recoveryTargetDevName = $d['recovery_target_dev_name']; //恢复目标设备名
            }
            $totalSize = $d['total_size'];
            $completedSize = $d['completed_size'];
            $validSize = $d['valid_size'];
            $completedValidSize = $d['completed_valid_size'];
            $totalSizeStr = v1_calsize($totalSize, true);
            $completedSizeStr =v1_calsize($completedSize,true);
            $validSizeStr = v1_calsize($validSize,true);
            $completedValidSizeStr = v1_calsize($completedValidSize,true);

            $dataSizeStr = $completedSizeStr."/". $totalSizeStr;
            $validDataStr = $completedValidSizeStr."/".$validSizeStr;
            $transporSize = v1_calsize($d['transport_size'], $this->verifyValueFalg($d['transport_size']));

            $records[] = array(
                'num' => $i,
                'data_source_host' => $dataSourceHost,
                'recovery_dev' => $devName,
                'data_size' => $dataSizeStr,
                'sync_data_size' => $validDataStr,
                'transfer_size' => $transporSize,
                'recovery_time_point' => $recoveryTimePoint,
                'recovery_target_host' => $recoveryTargetAHost,
                'recovery_target_dev' => $recoveryTargetDevName,
                'dev_uuid' => $devUuid,
            );
            $i++;
        }
        return array(
            'rows' => $records,
            'total' =>$count
        );
    }

    /**
     * 获取恢复配置
     * @param mixed $taskuuid
     * @return array
     */
    private function getTaskRestoreInfo($taskuuid)
    {
        $sql = "select host_reset_name,detail,restore_mode from cdp_vol_task_restore_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $hostResetName = $data[0]['host_reset_name'];
        $restoreMode = $data[0]['restore_mode'];
        $restoreArray = array(
            'host_reset_name' => $hostResetName,
            'restore_mode' => $restoreMode
        );
        return $restoreArray;
    }

    /**
     * 遍历解析恢复对象的设备名称
     * @param mixed $agentUuid
     * @param mixed $devUuid
     * @param mixed $timepoint
     */
    private function getDevuuidMapInfo($agentUuid,$devUuid,$timepoint){
        $sql = "select cvba.master_agent_detail 
                from cdp_vol_backup_agent cvba,bd_backup_timepoint bbt 
                where cvba.master_agent_uuid = ? and cvba.timepoint_uuid = bbt.timepoint_uuid and UNIX_TIMESTAMP(?) BETWEEN bbt.src_start_timepoint and src_end_timepoint";
        $data = $this->dbSelect($sql, array($agentUuid,$timepoint));
        $masterAgentDetail = json_decode($data[0]['master_agent_detail']);
       
        $allDiskList = $masterAgentDetail->all_disk_list;
        $devName = $this->findDeviceByUuid($allDiskList,$devUuid);
        return $devName;
    }

    private function findDeviceByUuid($devices,  $targetUuid) {
        foreach ($devices as $device) {
            // 检查当前层级的dev_uuid是否匹配
            if ($device->dev_uuid === $targetUuid) {
                return $device->display_name;
            }
    
            // 如果有子设备，递归搜索子设备（深度优先）
            if (isset($device->sub_devices) && is_array($device->sub_devices)) {
                $result = $this->findDeviceByUuid($device->sub_devices, $targetUuid);
                if ($result !== null) {
                    return $result; // 找到后直接跳出所有递归
                }
            }
        }
    }
    
    /**
     * 获取卷恢复磁盘映射关系
     * @param mixed $task_uuid
     * @return array
     */
    private function getVolRecoverMapInfo($task_uuid, $start,$length){
        $sql = "SELECT DISTINCT cvt.master_agent_uuid, cvt.standby_agent_uuid, cvt.recovery_target_agent_uuid, cvt.rebuild_partition_flag, bt.task_type, bt.task_status, cvt.current_task_running_stage, 
                cvt.auto_takeover_flag,cvt.mirror_backup_flag, cvtv.vol_uuid, cvtv.recovery_target_vol_uuid,cvtv.recovery_target_timestamp,  
                cvtp.total_size, cvtp.completed_size, cvtp.valid_size, cvtp.completed_valid_size, cvtp.write_size, cvtp.transport_size
            FROM bd_task AS bt 
                LEFT JOIN cdp_vol_task AS cvt ON cvt.task_uuid = bt.task_uuid 
                LEFT JOIN cdp_vol_task_vol AS cvtv ON cvtv.task_uuid = bt.task_uuid 
                LEFT JOIN cdp_vol_task_progress_info AS cvtp ON cvtp.vol_uuid = cvtv.vol_uuid 
            WHERE bt.task_uuid = ? GROUP BY cvtv.vol_uuid";
        $countSql = $sql;
        $listSql = $sql . " LIMIT ?,?";
        $countData = $this->dbSelect($countSql, array($task_uuid));
        $data = $this->dbSelect($listSql, array($task_uuid,$start,$length));
        $dataArray = array(
            'data' => $data,
            'total' => $countData
        );
        return $dataArray;
    }

    /**
     * 获取整机恢复磁盘映射关系
     * @return array
     */
    private function getCmReocveryMapInfo($task_uuid, $start,$length){
        $sql = "SELECT DISTINCT cvt.master_agent_uuid, cvt.standby_agent_uuid, cvt.recovery_target_agent_uuid, cvt.rebuild_partition_flag, bt.task_type, bt.task_status, cvt.current_task_running_stage, cvt.auto_takeover_flag, 
                    cvt.mirror_backup_flag, cvtd.dev_uuid,cvtd.dev_name, cvtd.recovery_target_dev_uuid, cvtd.recovery_target_dev_name, cvtd.failback_target_dev_name,cvtd.recovery_datetime,  
                    cvtp.total_size, cvtp.completed_size, cvtp.valid_size, cvtp.completed_valid_size, cvtp.write_size, cvtp.transport_size
                FROM bd_task AS bt 
                    LEFT JOIN cdp_vol_task AS cvt ON cvt.task_uuid = bt.task_uuid 
                    LEFT JOIN cdp_vol_task_disk AS cvtd ON cvtd.task_uuid = bt.task_uuid 
                    LEFT JOIN cdp_vol_task_progress_info AS cvtp ON cvtp.task_uuid = bt.task_uuid 
                WHERE bt.task_uuid = ? and cvtp.vol_uuid =cvtd.dev_uuid  GROUP BY cvtd.dev_uuid ";
         $countSql = $sql;
         $listSql = $sql . " LIMIT ?,?";
         $countData = $this->dbSelect($countSql, array($task_uuid));
         $data = $this->dbSelect($listSql, array($task_uuid,$start,$length));
         $dataArray = array(
            'data' => $data,
            'total' => $countData
        );
        return $dataArray;
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
     * 获取任务进度信息
     * @param mixed $taskUuid
     * @param mixed $devUuid
     * @return void
     */
    private function getTaskProgressInfo ($taskUuid,$devUuid){
        $sql = "select total_size,completed_total_size,valid_size,completed_valid_size,write_size,transport_size 
                from cdp_vol_task_progress_info 
                where task_uuid = '{$taskUuid}' and vol_uuid = '{$devUuid}'";
        $data = $this->dbSelect($sql);
        $progressInfo = array();
        if(!empty($data)){
            $progressInfo = array(
                'total_size' => $data[0]['total_size'],
                'completed_size' => $data[0]['completed_total_size'],
                'valid_size' => $data[0]['valid_size'],
                'completed_valid_size' => $data[0]['completed_valid_size'],
                'write_size' => $data[0]['write_size'],
                'transport_size' => $data[0]['transport_size']
            );
        }
        return $progressInfo;
    }

    /**
     * Summary of getBackupTaskDevInfo
     * @param mixed $params
     * @return array
     */
    private function getBackupTaskDevInfo($params){
        $taskUUID = $params['task_uuid'];
        $start = $params['offset'];
        $length = $params['limit'];
        $sql = "SELECT DISTINCT cvt.master_agent_uuid, cvt.standby_agent_uuid,cvt.recovery_target_agent_uuid, cvtti.takeover_standby_agent_uuid, cvt.rebuild_partition_flag,
                     bt.task_type, bt.task_status, cvt.current_task_running_stage, cvt.auto_takeover_flag,cvt.mirror_backup_flag,cvtti.takeover_vm_hypervisor,
                     cvtd.dev_uuid,cvtd.dev_name, cvtd.standby_target_dev_uuid,cvtd.standby_target_dev_name, cvtd.failback_target_dev_name  
                FROM bd_task AS bt 
                    LEFT JOIN cdp_vol_task AS cvt ON cvt.task_uuid = bt.task_uuid 
                    LEFT JOIN cdp_vol_task_disk AS cvtd ON cvtd.task_uuid = bt.task_uuid 
                    LEFT JOIN cdp_vol_task_takeover_info AS cvtti ON cvtti.task_uuid = bt.task_uuid 
                WHERE bt.task_uuid = ? GROUP BY cvtd.dev_uuid ";
        $countData = $this->dbSelect($sql, array($taskUUID));

        $limitSql = $sql . " limit ? , ? ";
        $data = $this->dbSelect($limitSql, array($taskUUID,$start,$length));
        $records = array();
        $i=1;
        foreach ($data as $d){
            $masterAgentInfo = $this->getBdAgentInfo($d['master_agent_uuid']);

            
            $taskoverStandbhAgentuuid = $d['takeover_standby_agent_uuid'];
            $takeoverVmHypervisor = $d['takeover_vm_hypervisor'];
            $standbyAgentuuid = $d['standby_agent_uuid'];  
            $masterAgentName = $masterAgentInfo['agentInfo'];
            $standbyUuid = $standbyAgentuuid; //目标备机UUID
            if($standbyAgentuuid == "" && $taskoverStandbhAgentuuid != ""){  //非复制任务，需要查看子表cdp_vol_task_takeover_info中是否配置接管备机
               $standbyUuid = $taskoverStandbhAgentuuid;
            }
            $standbyAgentInfo = $this->getBdAgentInfo($standbyUuid,$takeoverVmHypervisor);

            $standbyAgent = $standbyAgentInfo['agentInfo'];
            $taskRunningStage = $d['current_task_running_stage'];
            $taskType = $d['task_type'];
            $taskStatus = $d['task_status'];
            $devName = $d['dev_name'];  //数据源磁盘名
            $devUuid = $d['dev_uuid'];  //磁盘UUID
            $progressInfo = $this->getTaskProgressInfo($taskUUID,$devUuid);
            
            $totalSize = $progressInfo['total_size'];
            $completedSize = $progressInfo['completed_size'];
            
            $validSize = $progressInfo['valid_size'];
            $completedValidSize = $progressInfo['completed_valid_size'];

            $totalSizeStr = v1_calsize($totalSize, true);
            $completedSizeStr =v1_calsize($completedSize,true);
            $validSizeStr = v1_calsize($validSize,true);
            $completedValidSizeStr = v1_calsize($completedValidSize,true);

            $dataSizeStr = "--";
            $validDataStr = "--";
            $mappingDiskStr = "--";
            $transporSize = "--";
            if($standbyAgentuuid!=""){  //备份任务配置备机，即为复制任务
                $mappingDiskStr = $d['standby_target_dev_name'];
            }
            //任务阶段处于 逆向初始同步,回切启动中,初始同步时
            if($taskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
                || $taskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']
                || $taskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['INIT_SYNC']){
                    $dataSizeStr = $completedSizeStr."/". $totalSizeStr;
                    $validDataStr = $completedValidSizeStr."/".$validSizeStr;

            }else if($taskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['REALTIME_SYNC']
                || $taskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['WAIT_CONVERT_TO_REALTIME_SYNC']
                || $taskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['SERVER_CONS_CHECK']
                || $taskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']){  //任务阶段处于实时同或数据一致性校验阶段，回切实时同步阶段
                    $dataSizeStr = $validSizeStr."/".$totalSizeStr;
                    $validDataStr = $completedValidSizeStr;
            }
            $writeSize = v1_calsize($progressInfo['write_size'], $this->verifyValueFalg($progressInfo['write_size']));
            $transporSize = v1_calsize($progressInfo['transport_size'], $this->verifyValueFalg($progressInfo['transport_size']));

            $records[] = array(
                'num' => $i,
                'host_name' => $masterAgentName,
                'backup_disk' => $devName,
                'data_size' => $dataSizeStr,
                'sync_data_size' => $validDataStr,
                'transfer_size' => $transporSize,
                'write_size' => $writeSize,
                'standby' => $standbyAgent,
                'mapping_disk' => $mappingDiskStr,
                'dev_uuid' => $devUuid
            ); 
            $i++;
        }
        $totalNum = count($countData);
        return array(
            'rows' => $records,
            'total' => $totalNum
        );
    }
    
    /**
     * 获取客户端别名
     * @param string $params agent_uuid
     */
    private function getBdAgentInfo($params,$takeoverAgentType=0){
        if ($params){
            if($takeoverAgentType == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']){
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
            }else if($takeoverAgentType!= xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']){  //内嵌虚拟机
                $sql = "select takeover_vm_config from cdp_vol_task_takeover_info where takeover_standby_agent_uuid = ?";
                $data = $this->dbSelect($sql, array($params));
                $takeoverVmConfig = $data[0]['takeover_vm_config'];
                $vmConfig = json_decode($takeoverVmConfig);
                $standby = $vmConfig->vm_name;
            
                $agentArry = array (
                    "agentInfo" => $standby,
                    "isOnline" => xphp_get_config('app','FLAG')['SET'],
                    "ip" => xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC')
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
     * 获取恢复数据源主机信息
     */
    private function getDataSourceHosstInfo($masterAgentUuid)
    {
        $sql = "select DISTINCT master_agent_detail 
            from cdp_vol_backup_agent AS cvba,bd_backup_timepoint AS bbt 
            where cvba.master_agent_uuid =? and cvba.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ?";
        $sql = "select DISTINCT master_agent_detail from cdp_vol_backup_agent where master_agent_uuid ='{$masterAgentUuid}'";
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
     * 获取当前任务配置基本信息及任务状态运行情况等信息
     */
    public function getBasicInfo($params)
    {
        $taskUUID = $params['task_uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,bt.module_type, bt.strategy_id, 
                        unix_timestamp(bt.create_time) create_time, bt.transport_ip_segment,bt.node_uuid, bt.storage_uuid, bt.ignore_resource_limiting_flag, 
                        bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time,bri.total_object_valid_size,bri.total_object_completed_valid_size,
                        vol_task.current_task_running_stage,vol_task.master_agent_uuid,vol_task.standby_agent_uuid,vol_task.recovery_target_agent_uuid,vol_task.auto_takeover_flag, 
                        vol_task.rebuild_partition_flag,vol_task.recovery_data_source,vol_task.monitor_data_io_replication_mode,vol_task.takeover_data_source, vol_task.auto_fault_resume_flag, 
                        vol_task.mirror_backup_flag,vol_task.backup_mode,vol_task.auto_takeover_enable_flag, vol_task.full_backup_flag,vol_task.skip_bad_block_flag,
                        bu.user_name, unix_timestamp(bs.next_start_time) next_start_time 
                from   bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs,cdp_vol_task vol_task 
                where  bt.user_uuid = bu.user_uuid and 
                       bt.task_uuid = bri.task_uuid and 
                       bt.strategy_id = bs.strategy_id and 
                       bt.task_uuid = vol_task.task_uuid and 
                	   bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        $taskConfInfo = array();  //任务配置信息
        $taskRuningInfo = array();  //任务运行信息

        if(!$data){
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        foreach ($data as $d){
            $masterHostInfo = $this->getHostInfo($d['master_agent_uuid'],$d['task_type'],$taskUUID);  //主机
            $taskType = $d['task_type'];

            $standbyHostInfoStr = "";
            $recoveryTargetInfoStr = "";
            $hostResetName = "";
            $restoreMode = 0;
            $agentInfo = '';
            if($taskType==xphp_get_config( 'task', 'TASKTYPE')['VOL_CDP_RECOVERY']){  //恢复
                $recoveryTargetAgentUuid = $d['recovery_target_agent_uuid'];
                $standbyHostInfo = $this->getBdAgentInfo($recoveryTargetAgentUuid);
                $recoveryTargetInfoStr = $standbyHostInfo['agentInfo'];
                $hostResetInfo = $this->getTaskRestoreInfo($taskUUID); 
                $hostResetName = $hostResetInfo['host_reset_name'];
                $restoreMode = $hostResetInfo['restore_mode'];
            }else{
                $standbyAgentUuid = $d['standby_agent_uuid'];
                $standbyHostInfo = $this->getHostInfo($standbyAgentUuid,$d['task_type'],$taskUUID);    //备机
                $standbyHostInfoStr = $standbyHostInfo['host_info'];
                $agentInfo = $this->getVolCdpBackupAgentInfo($d['master_agent_uuid']);
            }
            $totalObjectSize = $d['total_object_size'];
            $completedValidSize = $d['total_object_completed_valid_size'];

            $totalValidSize = $d['total_object_valid_size'];
            $currentTaskRunningStage = $d['current_task_running_stage'];

            $valueFlag = false;
            $currentSizeValue = 0;
            if($totalObjectSize!=0 && $completedValidSize!=0){
                $valueFlag = true;
                $currentSizeValue = $this->getTaskTotalCapacityInfo($taskUUID);
            }
            if(!(is_numeric($currentSizeValue))){
                $valueFlag = false;
            }
            $currentSize = v1_calsize($currentSizeValue,$valueFlag);
            $totailSize = v1_calsize($d['total_object_size'],$valueFlag);
            $progress = $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $currentSizeValue, false, $d['task_type']);

            $totalprogress = $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'],$currentSizeValue, true, $d['task_type']);
            //任务处于实时同步阶段或逆向实时同步阶段
            if($currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['REALTIME_SYNC']
                || $currentTaskRunningStage==xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['WAIT_CONVERT_TO_REALTIME_SYNC']
                || $currentTaskRunningStage==xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']){
                $currentSize =  $this->getTaskCurrentTotalSize($d['task_uuid']);
            }
            $consistencyCheckVol = "--";
            
            if($currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['SERVER_CONS_CHECK'] 
				|| $currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['SERVERDATA_REALTIME_CONS_CHECK']){  //服务端数据一致性校验和实时数据校验阶段
                $serverConsCheckInfo = $this->getServerConsCheckInfo($taskUUID);
                $totailSize = v1_calsize($serverConsCheckInfo['total_size'],$valueFlag);
                $currentSize = v1_calsize($serverConsCheckInfo['completed_size'],$valueFlag);
                $consistencyCheckVol = $serverConsCheckInfo['current_vol'];
                $totalprogress = $serverConsCheckInfo['total_progress'];
                $progress = $totalprogress;
            }

            $storageuuid = $d['storage_uuid'];
            $monitorDataIoReplicationMode = $d['monitor_data_io_replication_mode'];
            $threadNum = $d['thread_num'];
            $mirrorBackupFlag = $d['mirror_backup_flag'];
            $failbackTargetInfo =  "";
            if($currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
                || $currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
                || $currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']){  //任务处于回切阶段
                $failbackInfo = $this->getFailbackInfo($taskUUID);
                $storageuuid = $failbackInfo['storage_uuid'];
                $monitorDataIoReplicationMode = $failbackInfo['monitor_data_io_replication_mode'];
                $threadNum = $failbackInfo['transport_thread_num'];
                $mirrorBackupFlag = $failbackInfo['mirror_backup_flag'];
                $failbackTargetInfo = $failbackInfo['failback_target_agent_name']."(".$failbackInfo['failback_target_agent_ip'].")";
            }
            $taskStatusValue = $d['task_status'];
            $startTime = $this ->getTaskStartTime($d['start_time'],$d['task_status'],$taskUUID,$d['task_type']);
            $currentTaskRunningStageValue = $d['current_task_running_stage'];
            $speedValue = $d['speed'];
            if($speedValue==0){
                $speed = "--";
            }else{
                $speed = $this->getJobSpeed($d['task_status'], $d['speed']);
            }
           
            if($d['task_type'] == xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] || $d['task_type'] == xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION']){  //备份
                $storageInfo = $this->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $storageuuid,$taskUUID);

            }else{
                $nodeInfo = $this->getTimepointStorageInfo($d['node_uuid']);
                $storageInfo = array(
                    'flag' => true,
                    'node' => $nodeInfo,
                );
            }
            
            $taskRuningInfo = array(
                'status' => xphp_get_desc('Pf', 'TASKSTATUSDES')[$taskStatusValue],
                'status_value' => intval($taskStatusValue),
                'totalSize' => $totailSize,
                'currentSize' => $currentSize,  //当前已完成容量
                'speed' => $speed,
                'progress' => $progress,
                'totalprogress' => $totalprogress,
                'start_time' => $this->getStartTIme($startTime, $d['task_status']),
                'endTime' => (new OsJobInfo())->getCalEndTime($d['total_size'], $d['current_total_size'], $d['task_status'], $d['speed']),
                'next_time' => (new VmJobInfo())->getNextStartTime($d['next_start_time'], $d['task_status']),
                'current_task_running_stage' => xphp_get_desc('Pf', 'CDP_TASK_RUNNING_STAGE')[$currentTaskRunningStageValue], //当前任务运行阶段
                'current_task_running_stage_value' =>$currentTaskRunningStageValue, //当前任务运行阶段value
                'cache_info' => $this->getVolCdpTaskCacheInfo($d['task_uuid'],$currentTaskRunningStage),
                'consistency_check_vol' => $consistencyCheckVol,
                'interval_time' => $this->getTimeInterval($startTime, $d['task_status']),
            );
            
            $taskConfInfo = array(
                'task_name' => $d['task_name'],
                'create_time' => $this->parseDate($d['create_time']),
                'task_type' =>  xphp_get_desc('Pf', 'TASKTYPEDES')[$d['task_type']],
                'task_type_value' => intval($d['task_type']),
                'module_type' =>  xphp_get_desc('Pf', 'MODULE_TYPE_DES')[$d['module_type']],
                'time_strategy' => (new VmJobInfo())->getJobTimeStrategy($d['strategy_id']),
                'reserved_strategy' => $this->getReservedStrategy($d['strategy_id'],$d['task_uuid']),
                'transport_strategy' => $this->getVolCdpTransportStrategy($taskUUID, $d['strategy_id'],$currentTaskRunningStage),
                'storageInfo' => $storageInfo,
                'thread_num' => $threadNum,
                'speed_limit' => $this->getSpeedlimitDes($d['task_uuid']),  //限速策略
                
                'master_agent_uuid' => $d['master_agent_uuid'],
                'os_type' => $masterHostInfo['os_type'],
                'master_agent_info' => $masterHostInfo['host_info'],
                'agent_ip' =>$masterHostInfo['agent_ip'],
                'standby_agent_info' => $standbyHostInfoStr,  //备机
                'standby_agent_uuid' => $standbyAgentUuid,
                'recovery_target_agent_uuid' => $d['recovery_target_agent_uuid'],  //恢复代理
                'recovery_target_agent_info' => $recoveryTargetInfoStr,
                'rebuild_partition_flag' => v1_parse_flag_to_bool($d['rebuild_partition_flag']),  //重建分区标志位.恢复使用
                'monitor_data_io_replication_mode' => $this->getVolCdpTaskIoMode($monitorDataIoReplicationMode), //监控IO复制模式
                'auto_takeover_flag' => v1_parse_flag_to_bool($d['auto_takeover_flag']),  //自动接管标志位
                'takeover_info' => $this->getTaskTakeoverConfInfo($d['task_uuid'],$d['auto_takeover_flag'],$d['task_type']),
                'mirror_backup_flag' => v1_parse_flag_to_bool($mirrorBackupFlag),
                'takeover_app_info' => xphp_get_lang('WEB_JOB_GET_APP_INFO'), //TOGO 增加接管应用解析
                'handover_info' => $this->getHandoverConfInfo($d['task_uuid'],$d['task_type']), //获取手动接管配置
                'takeover_data_source' => $d['takeover_data_source'],

                
                'backup_mode' => $d['backup_mode'],
                'auto_takeover_enable_flag' => v1_parse_flag_to_bool($d['auto_takeover_enable_flag']),  //自动接管是否启用
                'safe_strategy' =>  $this-> getSafeStrategy($d['task_uuid']),
 
                'full_backup_flag' => v1_parse_flag_to_bool($d['full_backup_flag']),  //全量备份   
                'skip_bad_block_flag' => v1_parse_flag_to_bool($d['skip_bad_block_flag']),  //跳过坏块标志  
                'auto_fault_resume_flag' => $d['auto_fault_resume_flag'], //故障自动恢复
                'high_pressure_strategy' => $this->getTaskHighPressureStrategy($d['task_uuid'],$d['task_type']),  //任务高负载保护
                'task_common_script' => $this->getTaskCommonScript($d['task_uuid']),  //获取实时模块任务脚本配置
                'host_reset_name' => $hostResetName,
                'restore_mode' => $restoreMode,
                'ignore_resource_limiting_flag' => $d['ignore_resource_limiting_flag'],
                'failback_target_info' => $failbackTargetInfo,
                'master_agent_detail' => $agentInfo,
                'master_memory_size' =>$masterHostInfo['memory_size'],
            );
        }
        $basicInfo = array(
            'task_runing_info' => $taskRuningInfo,
            'task_conf_info' => $taskConfInfo,
            'flag' => true,
        );
        return $basicInfo;
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
            $totalSize = $data[0]['total_size'];
            $completedSize = $data[0]['completed_size'];
            $currentVoluuid = $data[0]['current_vol_uuid'];
            $currentVol = $this->getAgentVol($currentVoluuid);

            $speed = v1_calpercent($totalSize, $completedSize);
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
     * 得到当前任务节点和存储信息
     * @param string $nodeuuid
     * @param string $storageuuid
     */
    private function getNodeInfo($tasktype, $strategyid, $nodeuuid, $storageuuid,$taskUuid)
    {
        $info = array('flag' => false);
        if (
            xphp_get_config('task')['TASKTYPE']['BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['RECOVERY'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['DB_BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['DB_RECOVERY'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['VOL_CDP_BACKUP'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['VOL_CDP_REPLICATION'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['VOL_CDP_TAKEOVER'] != $tasktype
            && xphp_get_config('task')['TASKTYPE']['OS_BACKUP'] != $tasktype
        ) {
            return $info;
        }
        //恢复任务显示节点信息
        if (
            xphp_get_config('task')['TASKTYPE']['RECOVERY'] == $tasktype
            || xphp_get_config('task')['TASKTYPE']['OS_RECOVERY'] == $tasktype
            || xphp_get_config('task')['TASKTYPE']['DB_RECOVERY'] == $tasktype
        ) {
            $info['flag'] = true;
        }

        $poolSql = "SELECT bt.task_uuid, bt.node_uuid, bt.node_pool_uuid, bt.storage_uuid, bt.storage_pool_uuid,
                    bnp.node_pool_nickname, bsrp.storage_pool_nickname
                FROM bd_task bt
                    LEFT JOIN bd_node_pool bnp ON bt.node_pool_uuid = bnp.node_pool_uuid
                    LEFT JOIN bd_storage_resource_pool bsrp ON bt.storage_pool_uuid = bsrp.storage_pool_uuid
                WHERE bt.task_uuid = ?"; 
        $poolData = $this->dbSelect($poolSql, array($taskUuid));
        if (is_array($poolData) && $poolData) {
            $info['node_uuid'] = $poolData[0]['node_uuid'];
            $info['node_pool_uuid'] = $poolData[0]['node_pool_uuid'];
            $info['storage_uuid'] = $poolData[0]['storage_uuid'];
            $info['node_pool_uuid'] = $poolData[0]['node_pool_uuid'];
            $info['storage_pool_uuid'] = $poolData[0]['storage_pool_uuid'];
            $info['node_pool_nickname'] = $poolData[0]['node_pool_nickname'];
            $info['storage_pool_nickname'] = $poolData[0]['storage_pool_nickname'];
        }
        //节点信息
        $info['node'] = $this->getNodeNameAndIp($nodeuuid);
        //存储信息
        $sql = "select storage_nickname, storage_type, total_size, free_size from bd_storage_resource 
            where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageuuid));

        if ($data) {
            $info['flag'] = true;
            $totalSize = v1_calsize($data[0]['total_size'], true);
            $freeSize = v1_calsize($data[0]['free_size'], true);
            $quotaDes = '';
            $quotaInfo = User::instance()->getUserQuotaInfo();
            $quotaFlag = false;     //分配配额标记
            //设置了配额或者是在租户内部
            if ($quotaInfo['quota'] != -1 || !empty($_SESSION['tenantuuid'])) {
                $quotaDes = $quotaInfo['des'];
                $quotaFlag = true;
            }
            $info['storage'] = array(
                'name' => $data[0]['storage_nickname'],
                'type' => Storage::instance()->getStorageTypeDes($data[0]['storage_type']),
                'typenum' => $data[0]['storage_type'],
                'size' => $totalSize,
                'freesize' => $freeSize,
                'quotades' => $quotaDes,
                'tenantuuid' => $_SESSION['tenantuuid'],
                'quotaFlag' => $quotaFlag
            );
        }
        //高级信息(重删/压缩/数据块大小等)
        $sql = "select deduplication_flag, block_size, compressed_flag, encrypted_flag, password_auto_flag, compress_method, encrypt_method  
            from bd_storage_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyid));
        if ($data) {
            $info['high'] = array(
                'deduplication' => v1_parse_flag_to_bool($data[0]['deduplication_flag']),
                'blocksize' => intval($data[0]['block_size']) / 1024 . 'KB',
                'compressed' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                'compress_method' => intval($data[0]['compress_method']),
                'encrypt_flag' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                'encrypt_method' => intval($data[0]['encrypt_method'])
            );
        }
        return $info;
    }
    
     /**
     * 获取节点名称和ip
     * @param $nodeuuid
     * @return array
     */
    private function getNodeNameAndIp($nodeuuid)
    {
        $sql = "select ip, host_name, node_nickname from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql, array($nodeuuid));
        $re = array(
            'name' => xphp_get_config('app')['NULLSPACE'],
            'ip' => '',
        );
        if ($data) {
            $re = array(
                'name' => Node::instance()
                    ->getNodeGridName($data[0]['ip'], $data[0]['node_nickname'], $data[0]['host_name']),
                'ip' => $data[0]['ip'],
            );
        }
        return $re;
    }
      /**
     * @param int $startTime 开始时间
     * @param int $status    状态
     * @return mixed|String
     */
    public function getTimeInterval($startTime, $status)
    {
        $taskstatus = xphp_get_config('task', 'TASKSTATUS');
        if (
            $status == $taskstatus['RUNNING'] ||
            $status == $taskstatus['NETWORK_FAULT'] ||
            $status == $taskstatus['ABNORMAL'] ||
            $status == $taskstatus['SUCCESSED']
        ) {
            $nowTime = (new Time())->getSystemTime();
            if (!$startTime || $startTime == 0) {
                return xphp_get_config('app')['TIMESPACE'];
            }
            $intval = $nowTime - $startTime;
            return v1_sec_to_time($intval);
        }
        return xphp_get_config('app')['TIMESPACE'];
    }
    /**
     * 获取回切配置
     * @param unknown $taskuuid
     */
    public function getFailbackInfo($taskuuid)
    {
        // $sql = "select failback_target_agent_uuid,memory_cache_alloc_space,file_cache_alloc_space,file_cache_storage_path,transport_encrypt_flag,
        //             transport_compress_flag,monitor_data_io_replication_mode,storage_uuid,transport_thread_num,transport_block_size,mirror_backup_flag 
        //         from cdp_vol_task_takeover_failback_info 
        //         where task_uuid = ?";
        $sql = "select cvttfi.failback_target_agent_uuid,cvttfi.memory_cache_alloc_space,cvttfi.file_cache_alloc_space,
	                cvttfi.file_cache_storage_path,cvttfi.transport_encrypt_flag,cvttfi.transport_compress_flag,cvttfi.monitor_data_io_replication_mode,
	                cvttfi.storage_uuid,transport_thread_num,transport_block_size,mirror_backup_flag,ba.agent_name,ba.ip  
                from cdp_vol_task_takeover_failback_info  cvttfi,bd_agent ba 
                where task_uuid = ? and ba.agent_uuid = cvttfi.failback_target_agent_uuid";
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
            $failbackInfo['mirror_backup_flag'] = v1_parse_flag_to_bool($data[0]['mirror_backup_flag']);
            $failbackInfo['failback_target_agent_name'] = $data[0]['agent_name'];
            $failbackInfo['failback_target_agent_ip'] = $data[0]['ip'];
        }
        return $failbackInfo;
    }
    /**
     * 获取备份点对应备份服务器信息
     * @param mixed $taskUuid
     * @return void
     */
    public function getTimepointStorageInfo($nodeUuid) {
        $sql = "SELECT host_name,ip from bd_node where node_uuid = ?";
        $data = $this->dbSelect($sql,array($nodeUuid));
        $dataArray = array();
        if(!empty($data)){
            $dataArray = array(
                'name' => $data[0]['host_name'],
                'ip' => $data[0]['ip'],
            );
        }
        return $dataArray;
    }
    /**
     * 获取任务脚本配置详情
     * @return void
     */
    public function getTaskCommonScript($taskUUID) {
        $scriptInfo = array();
        $sql = "select script_uuid,type,script_type,exec_type,script_content,exec_interval,trigger_fail_num,exec_result,exec_error_code,script_name  
                from cdp_vol_task_common_script 
                where task_uuid='{$taskUUID}'";
        $data = $this->dbSelect($sql);
        if(!empty($data)){
            foreach($data as $d){
                $scriptInfo[] = array(
                    'script_uuid' => $d['script_uuid'],
                    'type' => $d['type'],
                    'script_type' => $d['script_type'],
                    'exec_type' => $d['exec_type'],
                    'script_content' => $d['script_content'],
                    'exec_interval' => $d['exec_interval'],
                    'script_name' => $d['script_name'],
                    'trigger_fail_num' => $d['trigger_fail_num']
                );
            }
        }
        return $scriptInfo;
    }

    /**
     * 获取高负载保护策略
     * 备份任务解析，恢复和接管任务不解析
     * @param string $taskType task_uuid
     * @param int $taskType task_type
     * @param int $sourceType source 0 is backup 1 is back_cut
     * @return array
     */
    public function getTaskHighPressureStrategy($taskUUID,$taskType, $sourceType = 0) {
        $highPressureInfo = array();
        if($taskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] || $taskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION'] || $taskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){
            $sql = "SELECT detect_type,mem_threshold,cbt_enable_flag,cbt_mem_threshold,cbt_detect_interval,resource_protect_config
                    FROM cdp_vol_task_high_pressure_strategy
                    WHERE task_uuid='{$taskUUID}' and failback_flag = {$sourceType}";
            $data = $this->dbSelect($sql);
            if(!empty($data)){
                $dataInfo = $data[0];
                $cacheConfig = json_decode($dataInfo['resource_protect_config'], true);
                $highPressureInfo = array(
                    'detect_type' => $dataInfo['detect_type'],
                    'mem_threshold' => $dataInfo['mem_threshold'],
                    'cbt_enable_flag' => $dataInfo['cbt_enable_flag'],
                    'cbt_mem_threshold' => $dataInfo['cbt_mem_threshold'],
                    'cbt_detect_interval' => $dataInfo['cbt_detect_interval'],
                    'resource_protect_config' => empty($cacheConfig) ? [] : $cacheConfig,
                );

                // 默认持续数据保护降级都是开并隐藏的
                $highPressureInfo['cbt_enable_flag'] = true;
            }
        }
        return $highPressureInfo;
    }

    /**
     * 获取高负载保护策略 windows
     */
    public function getTaskHighPressureStrategyByWindows($taskUUID)
    {
        $sql = "select detect_type,mem_threshold,cbt_enable_flag,cbt_mem_threshold,cbt_detect_interval
                from cdp_vol_task_high_pressure_strategy 
                where task_uuid = ?";
        $data = $this->dbSelect($sql,array($taskUUID));

        // 处理下资源监测
        $memThreshold = $data[0]['mem_threshold']; // 停止任务内存阈值
        $cbtMemThreshold = $data[0]['cbt_mem_threshold']; // 降级内存阈值
        $detectType = $data[0]['detect_type'];
        if ($detectType == 2) {
            // mb/gb
            $memThreshold = ceil($memThreshold / 1024 / 1024);
            $cbtMemThreshold = ceil($cbtMemThreshold / 1024 / 1024);
            if ($memThreshold % 1024 == 0 && $cbtMemThreshold % 1024 == 0) {
                // gb
                $memThreshold = ceil($memThreshold / 1024);
                $cbtMemThreshold = ceil($cbtMemThreshold / 1024);
                $detectType = 3;
            }
        }
        $highPressureInfo = [
            'detect_type' => $data[0]['detect_type'], // 单位 1百分比 2MB 3GB
            'mem_threshold' => $memThreshold, // 停止任务内存阈值
            'cbt_enable_flag' => v1_parse_flag_to_bool($data[0]['cbt_enable_flag']), // 持续数据保护降级开关
            'cbt_mem_threshold' => $cbtMemThreshold, // 降级内存阈值
            'cbt_detect_interval' => $data[0]['cbt_detect_interval'], // 恢复持续数据保护检测间隔
        ];
        return $highPressureInfo;
    }

    /**
     * 获取安全策略配置信息
     * @param mixed $taskUUID
     * @return mixed
     */
    public function getSafeStrategy ($taskUUID){
        $sql = "SELECT worm_protection_time, virus_scan_config_list, integrity_check_strategy, backup_integrity_check_full_error_policy,backup_integrity_check_inc_error_policy
                FROM bd_task_safe_config WHERE task_uuid='{$taskUUID}'";
        $data = $this->dbSelect(sql: $sql);
        $safeStrategy = $this->groupSafeStrategy($data[0]);
        return $safeStrategy;

    }

      /**
     * 根据任务状态计算速度
     * @param unknown $taskStatus
     * @param unknown $speed
     */
    private function getJobSpeed($taskStatus, $speed){
        if($taskStatus != xphp_get_config('task','TASKSTATUS')['RUNNING']){
            //如果任务没有运行
            return xphp_get_config('app', 'NULLSPACE');
        }
        return v1_calspeed($speed);
    }

    /**
     * 获取卷cdp任务缓存配置
     * @param string task_uuid
     * @return array
     */
    public function getVolCdpTaskCacheInfo($taskuuid,$currentTaskRunningStage)
    {
        $used_str = xphp_get_lang('UI_RECOVERY_STORAGE_VALID_SIZE');  //可用空间
        $free_space_str = xphp_get_lang('UI_RECOVERY_STORAGE_USED_SIZE');  //已用空间

        $sql = "SELECT agent_memory_cache_alloc_space,agent_memory_cache_used_space,agent_cache_file_storage_path,agent_file_cache_alloc_space,agent_file_cache_used_space 
                FROM cdp_vol_task_cache_info
                WHERE task_uuid = ? and failback_flag = ?";

        //获取当前任务是否配置回切，如果配置回切且任务阶段处于回切阶段需要获取回切的相关缓存配置，其他任务阶段获取任务配置的缓存配置
        if($currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
            || $currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
            || $currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']){ //任务处于回切阶段

            $data = $this->dbSelect($sql, array($taskuuid,xphp_get_config('app','FLAG')['SET']));
        }else{
            $data = $this->dbSelect($sql, array($taskuuid,xphp_get_config('app','FLAG')['UNKNOWN']));
        }
        $memory_cache_space = $data[0]['agent_memory_cache_alloc_space'];
        $memory_cache_used = $data[0]['agent_memory_cache_used_space'] ?? 0;
        $memory_free_space = 0;
        $memory_cache_flag = false;
        $file_cache_space = $data[0]['agent_file_cache_alloc_space'];
        $file_cache_used = $data[0]['agent_file_cache_used_space'] ?? 0;
        $file_cache_path = $data[0]['agent_cache_file_storage_path'] ?? '--';
        $info = array();
        $info['agent_memory_cache_des'] = "--";
        $memoryCacheUsedStr = v1_calsize($memory_cache_used,true);
        if($memoryCacheUsedStr=="--"){
            $memoryCacheUsedStr = "0B";
        }
        if($memory_cache_space!=0){
            $memory_free_space = $memory_cache_space - $memory_cache_used;
            $info['agent_memory_cache_des'] = $used_str." ".v1_calsize($memory_free_space,true)." / ".$free_space_str." ".$memoryCacheUsedStr;
            $memory_cache_flag = true;
        }

        $file_free_space = 0;
        $valueFlag = false;
        if($file_cache_space!=0){
            $valueFlag = true;
            $file_free_space = $file_cache_space - $file_cache_used;
        }
        
        $fileCacheUsedStr = v1_calsize($file_cache_used,true);
        if($fileCacheUsedStr=="--"){
            $fileCacheUsedStr = "0B";
        }
        $info['agent_file_cache_des'] = $used_str." ".v1_calsize($file_free_space,$valueFlag)."/".$free_space_str." ".$fileCacheUsedStr;
        $info['file_cach_path'] = $file_cache_path;
        $info['memory_cache_flag'] = $memory_cache_flag;
        return $info;
    }
    /**
     * 获取任务开始时间，因备份任务更新到bd_running_info start_time自动值为客户端时间，会在一些条件下无法正常获取开始，需要分别获取
     * @param 开始时间 : $startTime
     * @param 任务状态: $taskStatus
     * @param 任务uuid $taskuuid
     * @param 任务类型 $taskType
     */
    public  function getTaskStartTime($startTime,$taskStatus,$taskuuid,$taskType)
    {
        if($taskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] || $taskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION']){
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
     * 获取当前任务所有执行卷的实时同步有效数据总和
     * @param task_uuid
     */
    public function getTaskCurrentTotalSize($taskUuid){
        $sql = "select total_object_completed_valid_size from bd_running_info where task_uuid = ?";
        $data = $this->dbSelect($sql,array($taskUuid));
        $totalVaildSize = "--";
        if(!empty($data)){
            $sizeFlag = true;
            if($data[0]['total_object_completed_valid_size']==0){
                $sizeFlag = false;
            }
            $totalVaildSize = v1_calsize($data[0]['total_object_completed_valid_size'], $sizeFlag);
        }
        return $totalVaildSize;
    }
    /**
     * 获取任务所有卷或磁盘已完成容量总和
     * @param string $taskuuid
     * @return float|int
     */
    public function getTaskTotalCapacityInfo(string $taskuuid)
    {

        $sql = "select total_size,valid_size,completed_valid_size  from cdp_vol_task_progress_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $completedSize = 0;
        foreach ($data as $d) {
            $volSize = 0;
            if ($d['completed_valid_size'] != 0) {
                $volSize = $d['total_size'] * ($d['completed_valid_size'] / $d['valid_size']);
            }
            $completedSize += $volSize;
        }
        return $completedSize;
    }
    /**
     * 得到卷CDP任务传输策略  跨平台恢复任务会用
     * @param string $taskuuid  任务UUID
     * @param int $strategyid   策略ID
     */
    public function  getVolCdpTransportStrategy($taskuuid,$strategyid,$currentTaskRunningStage)
    {
        $info = array();
        $sql = "select str.encrypt_flag, str.compress_flag,str.block_size,bnn.ip,bnn.port, bnn.alias_name,
                    str.max_transport_speed, str.compress_method,str.reconnect_times,str.reconnect_interval, str.encrypt_method,
                    str.network_pool_uuid,bnnp.network_pool_nickname
                from bd_transport_strategy str 
                    left join bd_node_network bnn on str.network_uuid = bnn.network_uuid
                    left join bd_node_network_pool bnnp on str.network_pool_uuid = bnnp.network_pool_uuid
                where strategy_id = ?";

        $data = $this->dbSelect($sql, array($strategyid));
        $encrypt = $data[0]['encrypt_flag'];
        $compress = $data[0]['compress_flag'];
        $transportBlockSize = $data[0]['block_size'];
        $compressMethod = $data[0]['compress_method'];
        //获取当前任务是否配置回切，如果配置回切且任务阶段处于回切阶段需要获取回切的相关缓存配置，其他任务阶段获取任务配置的缓存配置
        if($currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
            || $currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
            || $currentTaskRunningStage == xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING'])
        {  //任务处于回切阶段

            $fbSql = "SELECT transport_compress_flag,transport_encrypt_flag,transport_thread_num,transport_block_size,transport_compress_method
                FROM cdp_vol_task_takeover_failback_info  WHERE task_uuid = ? ";
            $fbData = $this->dbSelect($fbSql, array($taskuuid));
            $encrypt = $fbData[0]['transport_encrypt_flag'];
            $compress = $fbData[0]['transport_compress_flag'];
            $transportBlockSize = $fbData[0]['transport_block_size'];
            $compressMethod = $fbData[0]['transport_compress_method'];
        }
        if(!$transportBlockSize){
            $transportBlockSize =0;
        }
        $transportBlockSizeStr = v1_calsize($transportBlockSize);
        $info['encrypt'] = v1_parse_flag_to_bool($encrypt);
        $info['compress'] = v1_parse_flag_to_bool($compress);
        $info['compress_method'] = $compressMethod;
        $info['transport_block_size'] = $transportBlockSizeStr;
        $info['mode'] = xphp_get_lang('UI_BACKUP_TRANSPORT_NBD');
        $info['max_transport_speed'] = $data[0]['max_transport_speed']; //最大传输速度
        $info['reconnect_times'] = $data[0]['reconnect_times'];  //重连次数
        $info['reconnect_interval'] = $data[0]['reconnect_interval']; //重连间隔
        $info['encrypt_method'] = $data[0]['encrypt_method']; //传输加密算法
        $info['network_pool_nickname'] = $data[0]['network_pool_nickname'];
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
     * 获取卷CDP客户主机别名及IP信息
     * @param string agent_uuid
     * @return string agent_info
     */
    private  function getHostInfo($agent_uuid,$tasktype = 1 ,$taskuuid = '')
    {
        $agentInfo = array();
        if(xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] == $tasktype || xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION'] == $tasktype){  //备份 or 复制
            $sql = "SELECT hostname,ip,os_type,other_detail_hardware_info FROM bd_agent where agent_uuid = ?";
            $data = $this->dbSelect($sql, array($agent_uuid));
            $hostname = $data[0]['hostname'];
            $agentIp = $data[0]['ip'];
            $osType = $data[0]['os_type'];
            $other_detail_hardware_info = $data[0]['other_detail_hardware_info'];

            $hostInfo = $hostname."(".$agentIp.")";
            $agentInfo = array(
                "host_info" => $hostInfo,
                "agent_ip" => $agentIp,
                'os_type' => $osType,
                'memory_size' => json_decode($other_detail_hardware_info,true)['memory_size']
            );
        }else if(xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY'] == $tasktype || xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER'] == $tasktype){  //恢复，接管
            $agentInfo = $this->getVolCdpBackupAgentInfo($agent_uuid);
        }else if(xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER'] == $tasktype){
            $sql = "select takeover_vm_hypervisor from cdp_vol_task_takeover_info where task_uuid = ?";
            $data = $this->dbSelect($sql,array($taskuuid));
            $takeoverAgentType = $data[0]['takeover_vm_hypervisor'];
            if($takeoverAgentType == xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL']){
                $agentInfo = $this->getVolCdpBackupAgentInfo($agent_uuid);
            }else if($takeoverAgentType == xphp_get_config('module', 'MODULE_TYPE')['TEMP_AGENT']){
                $tempSql = "select name,os_type from vm_emd where uuid = ?";
                $data = $this->dbSelect($tempSql, array($agent_uuid));
                $hostInfo = $data[0]['name']."(".xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC').")";
                $agentInfo = array(
                    "host_info" => $hostInfo,
                    "agent_ip" => xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC'),
                    'os_type' => $data[0]['os_type']
                );
            }
        }
        return $agentInfo;
    }
    

    /**
     * 从备份集中获取agent信息 通过master uuid
     * @param $agentuuid
     * @return void
     */
    private function getVolCdpBackupAgentInfo($agent_uuid)
    {
        $sql = "select master_agent_detail from cdp_vol_backup_agent where master_agent_uuid  = ?";
        $data = $this->dbSelect($sql, array($agent_uuid));
        try {
            $master_agent_detail = json_decode($data[0]['master_agent_detail']);
            $hostname = $master_agent_detail->hostname;
            $agentIp = $master_agent_detail->ip;
            $osType = $master_agent_detail->os_type;
        }catch (Throwable $e) {
            $hostname = "--";
            $agentIp = "--";
            $osType = "--";
        }
        $hostInfo = $hostname."(".$agentIp.")";
        $agentInfo = array(
            "host_info" => $hostInfo,
            "agent_ip" => $agentIp,
            'os_type' => $osType,
        );
        return $agentInfo;
    }
    /**
     * 获取卷cdp任务IO复制模式
     * @param int io_mode
     * @return IO mode string
     */
    private function  getVolCdpTaskIoMode($io_mode)
    {
        $IoModeStr = xphp_get_desc('Volcdp','IoReplicationMode')[$io_mode]; //Io复制模式
        return  $IoModeStr;
    }

    /**
     * 获取保留策略相关配置信息 --可以添加为公共方法
     * @param mixed $strategyID
     * @param mixed $taskuuid
     * @return array|bool
     */
    public function getReservedStrategy($strategyID, $taskuuid)
    {
        $this->paramsCheck($strategyID);
        $sql = "SELECT strategy_type, number, auto_archive, strategy_mode from bd_reserved_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, array($strategyID));
        $reserved = false;
        foreach ($data as $d) {
            $reserved = array(
                'type' => $d['strategy_type'],
                'value' => $d['number'],
                'archive' => $d['auto_archive'],
                'strategyMode' => $d['strategy_mode'],
            );
        }
        return $reserved;
    }
    /**
     * 获取接管相关配置
     * @param task_uuid,auto_takeover_flag,task_type
     * @retrun array
     */
    public  function getTaskTakeoverConfInfo($task_uuid,$auto_takeover_flag,$task_type)
    {
        $take_info = array();
        if(xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] == $task_type || xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION'] == $task_type){
            try {
                $sql = "select takeover_standby_agent_uuid,agent_failback_standby_ip,app_takeover_flag,app_consecutive_failure_num,
                        app_fault_detection_interval,agent_heartbeat_failure_time,takeover_vm_hypervisor,takeover_vm_config 
                    from cdp_vol_task_takeover_info
                    where task_uuid = ? and takeover_type = ? ";
                $data = $this->dbSelect($sql, array($task_uuid,$auto_takeover_flag));
                if(!empty($data)){
                    $failbackup_standby_ip = $data[0]['agent_failback_standby_ip'];
                    $app_takeover_flag = $data[0]['app_takeover_flag'];
                    $app_consecutive_failure_num = $data[0]['app_consecutive_failure_num']; //App连续失败次数
                    $app_fault_detection_interval = $data[0]['app_fault_detection_interval']; //App故障检测间隔时间
                    $takeoverAgentType = $data[0]['takeover_vm_hypervisor'];  //虚拟机模块定义
                    $heartbeatFailureTime = $data[0]['agent_heartbeat_failure_time'];
                    $consoleUrl = "";
                    $tempStatus = 0;
                    $takeoverVmConfig = $data[0]['takeover_vm_config'];
                    $vmConfig = json_decode($takeoverVmConfig);
                    $standby_agent_uuid = $vmConfig->vm_uuid;
                    $prefixStatus = 0;
                    $standbyAgentInfo = array();
                    if($takeoverAgentType == xphp_get_config('vm','VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']){
                        $standbyAgentInfo = $this->getHostInfo($standby_agent_uuid,$task_type);
                        $take_info['standby_agent'] = $standbyAgentInfo['host_info'];
                    }else if($takeoverAgentType != xphp_get_config('vm','VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']){  //内嵌虚拟机
                        $takeoverVmConfig = $data[0]['takeover_vm_config'];
                        $vmConfig = json_decode($takeoverVmConfig);
                        $standby = $vmConfig->vm_name;
                        $take_info['standby_agent'] = $standby."(".xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC').")"; //接管备机
                        $sql = "select name,console_url,status,prefix_status from vm_emd where task_uuid = ? ";
                        $data = $this->dbSelect($sql, array($task_uuid));
                        if(!empty($data)){
                            $consoleUrl = $data[0]['console_url'];
                            $tempStatus = $data[0]['status'];
                            $prefixStatus = $data[0]['prefix_status'];
                        }
                       
                    }

                    $serverIp = $_SERVER['SERVER_ADDR'];
                    $consoleUrl = str_replace("0.0.0.0:6080", $serverIp . '/web_console', $consoleUrl);
					$consoleUrl = 'https://' . $_SERVER['HTTP_HOST'] . ':6080/jump.html?url=' . urlencode($consoleUrl);
                    $take_info['failbackup_ip'] = $failbackup_standby_ip;    //接管恢复IP
                    $take_info['app_takeover_flag'] = $app_takeover_flag; //是否启用自动接管
                    $take_info['app_consecutive_failure_num'] = $app_consecutive_failure_num; //App连续失败次数
                    $take_info['app_fault_detection_interval'] = $app_fault_detection_interval; //App故障检测间隔时间
                    $take_info['heartbeat_failure_time'] = $heartbeatFailureTime;  //心跳故障最大检测时间
                    $take_info['takeover_standby_agent_uuid'] = $standby_agent_uuid;
                    $take_info['standbyAgentInfo'] = $standbyAgentInfo;
                    // $take_info['script_info'] = $this->get_takeover_script($task_uuid);  
                    $take_info['temp_console_url'] = $consoleUrl;
                    $take_info['takeover_agent_type'] = $takeoverAgentType;
                    $take_info['temp_status'] = $tempStatus;
                    $take_info['prefix_status'] = $prefixStatus;
                    // if($takeoverAgentType != xphp_get_config('vm','VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']){   //模板虚拟机
                    //     $this->updateTempAgent($task_uuid);
                    // }
                }
            } catch (Exception $e) {
                $take_info = array();
            }
        }
        return  $take_info;
    }
    /**
     * 获取手动接管相关配置信息
     * @param task_uuid,task_type
     * @return array
     */
    public function getHandoverConfInfo($task_uuid,$task_type)
    {
        $handOverConf = array();
        if(xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER'] == $task_type){
            try {
                $sql = "select takeover_standby_agent_uuid,agent_failback_standby_ip,app_takeover_flag,takeover_timestamp,
                        master_agent_ip_switch_flag,takeover_vm_config,takeover_agent_role,takeover_vm_hypervisor   
                     FROM cdp_vol_task_takeover_info 
                    where  task_uuid = ? and takeover_type = ?";
                $data = $this->dbSelect($sql,array($task_uuid,xphp_get_desc('Volcdp','TAKEOVER_TYPE')['HAND_OVER']));
                $failbackup_standby_ip = $data[0]['agent_failback_standby_ip'];
                $app_takeover_flag = $data[0]['app_takeover_flag'];
                $takeoverVmConfig = $data[0]['takeover_vm_config'];
                $vmConfig = json_decode($takeoverVmConfig);
                $tempInfo = $this->getTempAgentConsoleUrl($vmConfig->vm_uuid);
                $takeoverStandbyAgentuuid = $data[0]['takeover_standby_agent_uuid'];
                $takeoverAgentType = $data[0]['takeover_vm_hypervisor'];
                if($takeoverAgentType != xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']){
                    $standby = $vmConfig->vm_name;
                    $standbyAgent =   $standby."(".xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC').")";
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
                // $handOverConf['script_info'] =  $this->get_takeover_script($task_uuid);
                $handOverConf['script_info'] =  "";
                $handOverConf['takeover_timestamp'] = $data[0]['takeover_timestamp'];
                $handOverConf['master_ip_switch'] = $data[0]['master_agent_ip_switch_flag'];
                $handOverConf['temp_console_url'] = $tempInfo['console_url'];
                $handOverConf['temp_status'] = $tempInfo['status'];
                $handOverConf['prefix_status'] = $tempInfo['prefix_status'];
                $handOverConf['takeover_agent_type'] = $takeoverAgentType;
                $handOverConf['takeover_agent_role'] = $data[0]['takeover_agent_role'];
                $handOverConf['takeover_standby_agent_uuid'] = $vmConfig->vm_uuid;
                if($takeoverAgentType != xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']){   //模板虚拟机
                    $this->updateTempAgent($task_uuid);
                }
            }catch (Exception $e){
                $handOverConf = array();
            }
        }
        return $handOverConf;
    }
    /**
     * @return void
     * 更新模板主机状态
     **/
    private function  updateTempAgent($task_uuid){
        $sync = FALSE;
        $command = TRUE;
        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($task_uuid);   //任务UUID对应的存储节点uuid

        $opName = 'TEMP_AGENT_VM_OP_GET_STATUS_ALL_EMD';  //停止接管启动回切
        $msg = [
            'node_uuid' => $nodeuuid,
            'temp_agent_uuid' => '',
            'hypervisor_type' => ''
        ];
        $msg = json_encode($msg);
        $mbResult = $this->mbTempAgentMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $operate = (new PfOpcode())->getOpcodeDes($opName);
        return $msg;
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
     * 根据条件组合客户端的名称
     * @param $agentName agentname
     * @param $hostName  hostname
     * @param $ip        ip
     * @return string
     */
    public function agentStr($agentName, $hostName, $ip): string
    {
        if (empty($agentName) && empty($hostName) && empty($ip)) {
            return '';
        }

        if (!empty($agentName) && $agentName != $ip) {
            $hostInfo = $agentName ? $agentName . '(' . $ip . ')' : $hostName . '(' . $ip . ')';
        } else {
            $hostInfo = $hostName . '(' . $ip . ')';
        }
        return $hostInfo;
    }

    /**
     * 校验入参boole值
     */
    private function verifyValueFalg($value)
    {
        $flag = true;
        if(!$value){
            $flag = false;
        }
        return  $flag;
    }
    /**
     * 获取任务接管回切配置
     * @param mixed $params
     * @return void
     */
    public function getCmCdpTaskHostConf($params){
        $user_uuid = xphp_get_user_info()['userUuid'];
        $task_uuid = $params['task_uuid'];
        $task_type = $params['task_type'];
        //首先判断回切信息表cdp_vol_task_takeover_failback_info中是否有记录，没有则是判断任务类型，备份和手动接管分别获取对应数据。
        $sql = "SELECT fback.failback_target_agent_uuid,fback.memory_cache_alloc_space,fback.file_cache_alloc_space,fback.file_cache_storage_path,
                    fback.transport_compress_flag,fback.transport_encrypt_flag,fback.monitor_data_io_replication_mode,fback.storage_uuid,task.agent_uuid,
                    task.node_uuid,fback.transport_thread_num,fback.transport_block_size,fback.mirror_backup_flag,
                    vol_task.rebuild_partition_flag,fback.transport_encrypt_method,fback.transport_compress_method,
                    ba.agent_name,ba.hostname,bsr.storage_nickname,bsr.total_size,bsr.free_size
                FROM cdp_vol_task_takeover_failback_info fback
                    INNER JOIN bd_task task ON task.task_uuid=fback.task_uuid
                    INNER JOIN cdp_vol_task vol_task ON vol_task.task_uuid=fback.task_uuid
					LEFT JOIN bd_agent ba ON ba.agent_uuid =  fback.failback_target_agent_uuid
					LEFT JOIN bd_storage_resource bsr on bsr.storage_uuid = fback.storage_uuid
                WHERE task.task_uuid = ? AND task.task_uuid = fback.task_uuid";
        $data = $this->dbSelect($sql,array($task_uuid));
        $list = array();
        if(!empty($data)){  //获取配置信息
            $failback_target_agent_uuid = $data[0]['failback_target_agent_uuid'];
            $failback_target_agent_name = $data[0]['hostname'].'('.$data[0]['agent_name'].')';
            $storage_nickname = $data[0]['storage_nickname'];
            $storage_total_size = v1_calsize($data[0]['total_size'],true);
            $storage_free_size = v1_calsize($data[0]['free_size'],true);
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
            $nodeUuid = $data[0]['node_uuid'];
            $mirror_backup_flag = $data[0]['mirror_backup_flag'];
            $rebuildPartitionFlag = $data[0]['rebuild_partition_flag'];

            $transport_thread_num = $data[0]['transport_thread_num'];  //传输线程个数
            $transport_block_size = $data[0]['transport_block_size']/1024/1024;  //传输数据包大小
            $agentInfo = $this->getHostInfo($failback_target_agent_uuid);
            $netModel = $agentInfo['net_model'];
            $failbackTargetDev = $this->getFailbackTargetDev($task_uuid);
            $host_list = $this->getTakeoverHost($failback_target_agent_uuid,$task_uuid,$task_type,$taskMasterUuid);
            // Linux
            $high_pressure_strategy_linux = $this->getTaskHighPressureStrategy($task_uuid,$task_type,xphp_get_config('app','FLAG')['SET']);
            // Windows
            $high_pressure_strategy_windows = $this->getTaskHighPressureStrategyByWindows($task_uuid);
        }else{  //未配置接管回切配置
            $sqlTask= "SELECT vol_task.master_agent_uuid,vol_task.monitor_data_io_replication_mode,task.storage_uuid, task.node_uuid, vol_task.rebuild_partition_flag
                       FROM cdp_vol_task vol_task,bd_task task 
                       WHERE vol_task.task_uuid = task.task_uuid and vol_task.task_uuid = ?";
            $dataTask = $this->dbSelect($sqlTask,array($task_uuid));
            $taskMasterUuid  = $dataTask[0]['master_agent_uuid'];
            $io_replication_mode = $dataTask[0]['monitor_data_io_replication_mode'];
            $storage_uuid = $dataTask[0]['storage_uuid'];
            $nodeUuid = $dataTask[0]['node_uuid'];
            $failback_target_agent_uuid = "";
            $mirror_backup_flag = xphp_get_config('app','FLAG')['UNSET'];
            $rebuildPartitionFlag = $dataTask[0]['rebuild_partition_flag'];
            $transport_thread_num = 1;  //default value;
            $transport_block_size = 4;  //传输数据包大小默认为4 MB
            $netModel = "";
            $failbackTargetDev = array();
            //获取配置信息
            if($task_type==xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] || $task_type==xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION']){
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

            }elseif($task_type==xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){
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
            'memory_cache_alloc_space_des' =>v1_calsize($data[0]['memory_cache_alloc_space'],true),
            'file_cache_alloc_space' => $file_cache_alloc_space,
            'file_cache_alloc_space_des' => v1_calsize($file_cache_alloc_space,true),
            'file_cache_storage_path' => $file_cache_storage_path,
            'transport_compress_flag' => v1_parse_flag_to_bool($transport_compress_flag),
            'transport_encrypt_flag' => v1_parse_flag_to_bool($transport_encrypt_flag),
            'transport_encrypt_method' => $transport_encrypt_method,
            'transport_compress_method' => $transport_compress_method,
            'task_master_uuid' => $taskMasterUuid,
            'failbackup_target_uuid' => $failback_target_agent_uuid,
            'failback_target_agent_name' => $failback_target_agent_name,
            'io_replication_mode' => $io_replication_mode,
            'host_list' => $host_list,
            'storage_uuid' => $storage_uuid,
            'node_uuid' => $nodeUuid,
            'mirror_backup_flag' => v1_parse_flag_to_bool($mirror_backup_flag),
            'transport_thread_num' =>$transport_thread_num,
            'transport_block_size' => $transport_block_size,
            'transport_block_size_des' => v1_calsize($data[0]['transport_block_size'],true),
            'rebuild_partition_flag' => v1_parse_flag_to_bool($rebuildPartitionFlag),
            'net_model' => $netModel,
            'standby_os_type' => $osType,
            'failback_target_dev' => $failbackTargetDev,
            'high_pressure_strategy_linux' => $high_pressure_strategy_linux,
            'high_pressure_strategy_windows' => $high_pressure_strategy_windows,
            'storage_nickname' => $storage_nickname,
            'storage_free_size' => $storage_free_size,
            'storage_total_size' => $storage_total_size,
            'high_pressure_strategy' => $this->getTaskHighPressureStrategy($task_uuid,$task_type,xphp_get_config('app','FLAG')['SET']),  //任务高负载保护
        );
        return $list;
    }
    /**
     * 获取回切任务配置设备信息
     * @param mixed $taskuuid
     * @return void
     */
    private function getFailbackTargetDev($taskuuid)
    {
        $sql = "select dev_uuid,failback_target_dev_uuid from cdp_vol_task_disk where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $devInfo = array();
        foreach ($data as $key => $value) {
            $devUuid = $value['dev_uuid'];
            $targetDevUuid = $value['failback_target_dev_uuid'];    
            $devInfo[] = array(
                'dev_uuid' => $devUuid,
                'failback_target_dev_uuid' => $targetDevUuid
            );
        }
        return $devInfo;
    }

     /**
     * 获取任务备机信息
     * @param unknown $taskuuid
     */
    private function getTaskStandbyHostInfo($taskuuid)
    {
        $standbyNicList = [];
        $standbySql = "select takeover_standby_agent_uuid,takeover_vm_hypervisor,takeover_vm_config from cdp_vol_task_takeover_info where task_uuid = ?";
        $standbyData = $this->dbSelect($standbySql, array($taskuuid));
        $standbyAgentuuid = $standbyData[0]['takeover_standby_agent_uuid'];
        $takeoverAgentType = $standbyData[0]['takeover_vm_hypervisor'];
        if($takeoverAgentType == xphp_get_config('vm','VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']){
            $updateNetCard = $this->updateHostNetCardinf($standbyAgentuuid);
            if($updateNetCard){
                $standbyNicList = $this->getHostNetworkInfo(['agent_uuid' => $standbyAgentuuid]);

            }
        }else if($takeoverAgentType!= xphp_get_config('vm','VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']){  //内嵌虚拟机
            $takeoverVmConf = json_decode($standbyData[0]['takeover_vm_config']);
            $standbyNicList = $takeoverVmConf->interfaces;
            
            // 定义键名映射规则,因为内嵌虚拟主机创建的网卡对应键名与agent的网卡信息不匹配，需要将对应键名做映射处理，确保输出键位一致
            $keyMap = [
                "nic_mac" => "mac_address",
                "nic_name" => "name"
            ];
            foreach ($standbyNicList as $obj) {
                foreach ($keyMap as $oldKey => $newKey) {
                    if (property_exists($obj, $oldKey)) {
                        $obj->$newKey = $obj->$oldKey;
                        unset($obj->$oldKey);
                    }
                }
            }
        }

        $cdpVolTaskTakeoverInfo = array(
            'stand_agent_uuid' => $standbyAgentuuid,
            'standby_nic_list' =>$standbyNicList,
            'takeover_agent_type' => $takeoverAgentType,
        );
        return $cdpVolTaskTakeoverInfo;
    }
    /**
     * 获取客户端对应的网卡信息
     * @param unknown $params
     */
    public function getHostNetworkInfo($params)
    {
        $agentuuid = $params['agent_uuid'];
        $sql = "select detail from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentuuid));
        $detail = json_decode($data[0]['detail']);
        $nicList = $detail->nic_list;
        return $nicList;
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
    public function updateHostNetCardinf($agentuuid)
    {
        $refreshAgentSet = array();
        $refreshAgentSet['agent_uuid'] = $agentuuid;
        $refreshAgentSet['app_uuid_set'] = [];

        $refreshSet= array($refreshAgentSet);
        $pfMSg = array();
        $pfMSg['refresh_agent_set'] = $refreshSet;
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $nodeuuid = (new Node())->getLocalNodeUUID();  //获取主节点UUID
        $opName = 'VOL_CDP_TASK_SCANNING_NIC_INFO';
        $operate = $this->pfOpcode->getOpcodeDes($opName);
        //返回结果到UI
        $result = true;
        if($result){
            return true;
        }else{
            return false;
        }
    }
    /**
     * 获取可供回切用的主机信息
     * Summary of getTakeoverHost
     * @return void
     */
    private function getTakeoverHost($fb_target_uuid,$task_uuid,$create_task_type,$taskMasterUuid){
        $list = array();
        $agentuuidArr = (new Client())->getClientUuids();
        if (empty($agentuuidArr)) {
            return json_encode($list);
        }
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,agent_type,net_model
                from bd_agent
                where  (agent_type =? or agent_type =?) and online_flag =?  ";
        if (is_array($agentuuidArr)) {
            $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
            $sql.=" and agent_uuid in {$agentuuidArrStr}";
        }

        if($create_task_type ==xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] || $create_task_type ==xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION']){  //备份任务回切目标机器可以是数据源主机
            $dataSql  = $sql;
            $isAutoTakeover = true;
            $sqlParams = array(xphp_get_config('client','AGENT_TYPE','resources')['NORMAL'],xphp_get_config('client','AGENT_TYPE','resources')['MEMORY_OS'],xphp_get_config('app','FLAG')['SET']);
        }else{
            $dataSql = $sql;
            $isAutoTakeover = false;
            $sqlParams = array(xphp_get_config('client','AGENT_TYPE','resources')['NORMAL'],xphp_get_config('client','AGENT_TYPE','resources')['MEMORY_OS'],xphp_get_config('app','FLAG')['SET']);
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
           

            if (!empty($agentName) && $agentName != $ip) {
                $hostInfo =  $agentName ? $agentName."(". $ip .")" : $hostName."(". $ip .")";
            }else {
                $hostInfo = $hostName."(". $ip .")";
            }

            // if(xphp_get_config('app','FLAG')['SET'] == $onlineFlag){
            //     $statusDes = xphp_get_lang('WEB_AGENT_STATUS_ONLINE');
            // }else{
            //     $statusDes = xphp_get_lang('WEB_AGENT_STATUS_OFFLINE');
            //     $hostInfo = $hostInfo."--".$statusDes;
            // }
            $taskInfo = $this->taskExist($agentUuid);  //获取任务是否存在，获取任务类型
            $taskName = "";
            $bkTaskIsRunning = xphp_get_config('app','FLAG')['UNSET'];
            if(count($taskInfo)>0){
                $taskName =  $taskInfo['task_name'];
                $taskType = $taskInfo['task_type'];
                $taskStatus = $taskInfo['task_status'];
                if(($taskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] || $taskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION']) && $taskType ==xphp_get_config('task','TASKSTATUS')['RUNNING']){
                    $bkTaskIsRunning =  xphp_get_config('app','FLAG')['SET'];;
                }
            }
            $hostOsType  = $host_info['os_type'];
            $taskMasterAgentOsType = $this->getHostOsTypeForTaskuuid($task_uuid,$create_task_type);
            if($hostOsType!=$taskMasterAgentOsType && $agentType!= xphp_get_config('client','AGENT_TYPE','resources')['MEMORY_OS']){
                continue;
            }
            $list[] = array(
                'uuid' => $agentUuid,
                'text' => $hostInfo,
                'value' => $host_info['agent_uuid'],
                'os_type' => $hostOsType,
                'agent_type' => $agentType,
                // 'host_vol_info' => $this->getHostVolInfoByUUID($agentUuid),
                'task_is_running' => $bkTaskIsRunning,
                'net_model' => $netModel,
            );
        }
        return $list;
    }

     /**
     * 通过任务UUID获取任务主机或数据源对应的操作系统类型
     * @param $taskuuid
     */
    private function getHostOsTypeForTaskuuid($taskuuid,$taskType)
    {
        if($taskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){  //接管任务
            $sql = "select agent.master_agent_detail,agent.id from cdp_vol_backup_agent agent,cdp_vol_task task 
                    where task.task_uuid =? and task.master_agent_uuid = agent.master_agent_uuid ORDER BY agent.id desc LIMIT 0,1";
        }else{
            $sql = "SELECT agent.os_type from cdp_vol_task task,bd_agent agent 
                    where task.task_uuid = ? and task.master_agent_uuid = agent.agent_uuid";
        }

        $osType = "";
        $data = $this->dbSelect($sql, array($taskuuid));
        if(!empty($data)){
            if($taskType == xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']) {  //接管任务
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
     * 检查选择的目标是否有被其它任务选中
     * ---当主机配置了其他任务中的任意角色：备份主机，恢复目标机，双机镜像备机，自动接管备机，手动接管备机和回切目标机器时不能再作为手动接管备机使用
     * @param unknown $agentUuids
     */
    private function taskExist ($agentUUID)
    {
        $sql = "SELECT task.task_status,task.task_name,task_type FROM bd_task task,cdp_vol_task_takeover_info takeover 
            WHERE takeover.task_uuid = task.task_uuid and task.task_type = ? and takeover.takeover_standby_agent_uuid = ? and task.delete_flag = ? ";
        $taskType = xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER'];
        $delFlag = xphp_get_config('app','FLAG')['UNSET'];
        $data = $this->dbSelect($sql,array($taskType,$agentUUID,$delFlag));
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
     * 获取当前主机是否有任务，是否可用
     */
    private function getAgentEnabled($host_uuid,$task_uuid,$isAutoTakeover,$taskMasterUuid)
    {
        if($isAutoTakeover && ($taskMasterUuid == $host_uuid)){
            return true;
        }
        $sql = "select vol_task.task_uuid,task.task_status,task.task_type from cdp_vol_task vol_task,bd_task task 
            where (vol_task.master_agent_uuid = ? or vol_task.standby_agent_uuid = ? or vol_task.host_ha_standby_agent_uuid = ?) 
            and task.task_uuid =vol_task.task_uuid and task.task_type !=? ";
        $data = $this->dbSelect($sql,array($host_uuid,$host_uuid,$host_uuid,xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY']));
        $isEnabled = false;
        if(!empty($data)){
            foreach ($data as $v){
                if($task_uuid ==$v['task_uuid']){
                    $isEnabled = true;
                }else{
                    $isEnabled = false;
                    return $isEnabled;
                }
                if(($v['task_type'] == xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] || $v['task_type'] == xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION'])
                    && $v['task_status'] ==xphp_get_config('task','TASKSTATUS')['RUNNING']){
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
     * 获取当前任务接管时间点
     */
    public function getTakeoverTaskTimePoint($params) {
        $taskUUID = $params['task_uuid'];
        $taskType = $params['task_type'];
        $timePointUuid = [];
        
        if($taskType == xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] || $taskType == xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION']){
            // $sql = "select task_start_time from cdp_vol_task where task_uuid = ?";
            $sql = "SELECT bbt.timepoint_uuid,cvba.id 
                    from bd_backup_timepoint AS bbt,cdp_vol_backup_agent as cvba,cdp_vol_task AS cvt 
                    where cvba.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = cvt.task_uuid and cvt.task_uuid = ? ORDER BY  cvba.id desc LIMIT 0,1";
            $data = $this->dbSelect($sql,array($taskUUID));
            if(!empty($data)){
                $timePointUuid = $data[0]['timepoint_uuid'];
            }
        }elseif($taskType == xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){
            $taskSql = "SELECT cvt.master_agent_uuid,cvtti.takeover_timestamp 
                    from cdp_vol_task AS cvt, cdp_vol_task_takeover_info as cvtti 
                    where cvt.task_uuid = cvtti.task_uuid and cvt.task_uuid = ?";
            $taskData = $this->dbSelect($taskSql,array($taskUUID));
            if(!empty($taskData)){
                $taskoverTimestamp = $taskData[0]['takeover_timestamp'];
                $masterAgentUUID= $taskData[0]['master_agent_uuid'];
                $sql = "select cvba.timepoint_uuid 
                        from cdp_vol_backup_agent as cvba,cdp_vol_backup_vol_set as cvbcs 
                        where cvba.master_agent_uuid = ? and cvba.id = cvbcs.backup_agent_id and (UNIX_TIMESTAMP(?) >= UNIX_TIMESTAMP(cvbcs.start_timestamp) and  UNIX_TIMESTAMP(?) <= UNIX_TIMESTAMP(cvbcs.end_timestamp))";
                $data = $this->dbSelect($sql,array($masterAgentUUID,$taskoverTimestamp,$taskoverTimestamp));
                if(!empty($data)){
                    $timePointUuid =  $data[0]['timepoint_uuid'];
                }
            }
        }
        return $timePointUuid = array('timepoint_uuid'=>$timePointUuid);
    }
    /**
     * 提交回切配置
     * @return void
     */
    public function submitFailbackTask($params){
        $pfMSg = array();
        $task_uuid = $params['task_uuid'];
        $pfMSg['task_uuid'] = $task_uuid;
        $pfMSg['transport_strategy'] = $params['transport_strategy'];
        $pfMSg['network_uuid'] = $params['network_uuid'];
        $pfMSg['failback_object'] = $params['failback_object'];
        $pfMSg['cache_config'] = $params['cache_config'];
        $pfMSg['resource_protect_config'] = $params['resource_protect_config'];
        $pfMSg['high_pressure_strategy'] = $params['high_pressure_strategy'];
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $nodeUuid = (new Node())->getNodeUUIDWithTaskUUID($task_uuid);   //任务UUID对应的存储节点uuid

        $opName = 'VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_CONFIG';
        $mbResult = $this->mbVolCdpMsg($nodeUuid, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];

        $operate = (new VolcdpOpcode())->getOpcodeDes($opName);
        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取任务详情机器的状态
     */
    public function getCmCdpAgentStatus($params)
    {
        $sql = "select online_flag from bd_agent where agent_uuid = ?";
        $sourceData = $this->dbSelect($sql,array($params['source_agent_uuid']));
        $targetData = $this->dbSelect($sql,array($params['target_agent_uuid']));
        return array(
            'source_agent_online' => v1_parse_flag_to_bool($sourceData[0]['online_flag']),
            'target_agent_online' => v1_parse_flag_to_bool($targetData[0]['online_flag']),
        );
    }
    /**
     * 获取任务监控设备信息，过滤掉排除设备
     * @param mixed $params
     * @return \xphp\db\fetchAll
     */
    public function getMonitorDeviceDetails($params){
        $taskUuid = $params['task_uuid'];
        $DevUuid = $params['dev_uuid'];
        $taskType = $params['task_type'];
        $allDisk = array();
        if($taskType == xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] || $taskType == xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION']){   //备份
            $sql = "select master_agent_detail from cdp_vol_task where task_uuid = ?";
            $data = $this->dbSelect($sql,array($taskUuid));
            if(!empty($data)){
                $masterAgentDetail = json_decode($data[0]['master_agent_detail'],true);
            }else{
                return $allDisk;
            }
        }else if($taskType ==xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){ //接管
            $taskSql  = "SELECT cvt.master_agent_uuid,cvtti.takeover_timestamp 
                from cdp_vol_task AS cvt, cdp_vol_task_takeover_info as cvtti 
                where cvt.task_uuid = cvtti.task_uuid and cvt.task_uuid = ?";
            $taskData = $this->dbSelect($taskSql,array($taskUuid));
            if(!empty($taskData)){
                $taskMasterUuid = $taskData[0]['master_agent_uuid'];
                $taskoverTimestamp = $taskData[0]['takeover_timestamp'];
                $backupDataInfo = $this->getMasterBackupDataInfo($taskMasterUuid,$taskoverTimestamp);  //通过任务数据源客户端UUID和事件点获取数据源主机对应的备份数据信息
                $masterAgentDetail = json_decode($backupDataInfo['master_agent_detail'],true);
            }else{
                return $allDisk;
            }
        }else if($taskType ==xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY'] || $taskType ==xphp_get_config('task','TASKTYPE')['PLATFORM_RECOVERY']){ //恢复

            $sql = "select restore_mode from cdp_vol_task_restore_info where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskUuid));
            if(!empty($data)){
                $restoreMode = $data[0]['restore_mode'];
                if($restoreMode == xphp_get_config('cm_cdp','RESTORE_MODE')['VOL_RECOVERY']){  //数据卷恢复
                    $taskSql = "SELECT cvt.master_agent_uuid,cvtv.recovery_target_timestamp 
                        from cdp_vol_task as cvt,cdp_vol_task_vol as cvtv 
                        where cvt.task_uuid = cvtv.task_uuid and cvtv.task_uuid = ?";
                    $taskData = $this->dbSelect($taskSql,array($taskUuid));
                    $taskMasterUuid = $taskData[0]['master_agent_uuid'];
                    $recoveryTimestamp = $taskData[0]['recovery_target_timestamp'];
                }else{  //整机恢复
                    $taskSql = "SELECT cvt.master_agent_uuid,cvtd.recovery_datetime 
                        from cdp_vol_task as cvt,cdp_vol_task_disk as cvtd 
                        where cvt.task_uuid = cvtd.task_uuid and cvtd.task_uuid = ?";
                    $taskData = $this->dbSelect($taskSql,array($taskUuid));
                    $taskMasterUuid = $taskData[0]['master_agent_uuid']; 
                    $recoveryTimestamp = $taskData[0]['recovery_datetime'];
                }
                $backupDataInfo = $this->getMasterBackupDataInfo( $taskMasterUuid,$recoveryTimestamp);  //通过任务数据源客户端UUID和事件点获取数据源主机对应的备份数据信息
                $masterAgentDetail = json_decode($backupDataInfo['master_agent_detail'],true);
            }
        }
        $diskList = $masterAgentDetail['all_disk_list'] ?? [];
        for($i = 0; $i< count($diskList);$i++){
            if($restoreMode == xphp_get_config('cm_cdp','RESTORE_MODE')['VOL_RECOVERY']){  //数据卷恢复
                $hasDevUuidMatch = $this->hasDevUuidMatch($diskList[$i], $DevUuid);
                if($hasDevUuidMatch){
                    array_push($allDisk,$diskList[$i]);
                }
            }else{  //整机恢复
                if($diskList[$i]['dev_node_uuid'] == $DevUuid){
                    array_push($allDisk,$diskList[$i]);
                }
            }
        }
        $excludeDevicesList = $this->getCmTaskExcludeVol($taskUuid);
        $taskDev = array(
            'all_disk_list' => $allDisk,
            'exclude_device_list' => $excludeDevicesList,
        );
        $objectType = array(
            'nocheck' => false,
            'recover_type' => true,
        );
        $diskTree = (new MachineOsBackup())->getBackupDevices($taskDev, $objectType);
        return $diskTree;
    }

    //匹配当前设备uuid是否存在
    private function hasDevUuidMatch($data, $target) {
        // 统一处理数组和对象
        $data = (array)$data;
        // 检查当前层级匹配
        if (isset($data['dev_uuid']) && $data['dev_uuid'] === $target) {
            return true;
        }
        // 递归检查子设备
        if (isset($data['sub_devices']) && is_iterable($data['sub_devices'])) {
            foreach ($data['sub_devices'] as $subDevice) {
                if ($this->hasDevUuidMatch($subDevice, $target)) {
                    return true; // 发现匹配立即退出
                }
            }
        }
        return false;
    }
    
    /**
     * 获取任务指定磁盘设备
     */
    private function getCmTaskExcludeVol($taskuuid){
        $sql = "select * from cdp_vol_task_exclude_vol where  task_uuid = ?";
        $data = $this->dbSelect($sql,array($taskuuid));
        $excludeDevNodeInfo = array();
        foreach ($data as $d){
            $excludeDevNodeInfo[] = array(
                'dev_node_uuid' => $d['dev_node_uuid'],
                'dev_uuid' => $d['dev_uuid'],
            );
        }
        return $excludeDevNodeInfo;
    }
    /**
     * 获取主机对应的备份点信息
     * @param mixed $masterAgentUUID
     * @param mixed $timestamp
     * @return void
     */
    private function getMasterBackupDataInfo($masterAgentUUID,$timestamp)
    {
        $sql = "select cvba.master_agent_detail,cvba.timepoint_uuid 
            from cdp_vol_backup_agent as cvba,cdp_vol_backup_vol_set as cvbcs 
            where cvba.master_agent_uuid = ? and cvba.id = cvbcs.backup_agent_id 
                and (UNIX_TIMESTAMP(?) >= UNIX_TIMESTAMP(cvbcs.start_timestamp)  and  UNIX_TIMESTAMP(?) <= UNIX_TIMESTAMP(cvbcs.end_timestamp))";
        $data = $this->dbSelect($sql,array($masterAgentUUID,$timestamp,$timestamp));
        if(empty($data)){
            return array();
        }
        $backupDataInfo = array(
            'master_agent_detail' => $data[0]['master_agent_detail'],
            'timepoint_uuid' => $data[0]['timepoint_uuid']
        );
        return $backupDataInfo;
    }

    /**
     * @param $params 获取启动任务启动对象详细信息
     */
    public function getStartTaskObjectInfo($params){
        $startObjectInfo = $this->getStartTaskObjectDetail($params);
        return $startObjectInfo;
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
        if((xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER'] == $taskType 
            || xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP'] == $taskType 
            || xphp_get_config('task','TASKTYPE')['VOL_CDP_REPLICATION'] == $taskType)
            && $startType == xphp_get_config('task','TASK_CONTROL')['START_FAILBACK']){
            $sql = "SELECT takeover_info.takeover_standby_agent_uuid,takeover.failback_target_agent_uuid 
                    from cdp_vol_task_takeover_info takeover_info,cdp_vol_task_takeover_failback_info takeover 
                    where takeover_info.task_uuid = takeover.task_uuid and takeover_info.task_uuid = ?";
            $data = $this->dbSelect($sql,array($taskuuid));
            if(!empty($data)){
                $taskInfo = $data[0];
                $takeoverStandbyAgentuuid = $taskInfo['takeover_standby_agent_uuid'];
                $targetAgentUuid = $taskInfo['failback_target_agent_uuid'];
                $masterAgentObject = $this->getBdAgentInfo($takeoverStandbyAgentuuid);  //数据源主机信息
                $targetAgentObject = $this->getBdAgentInfo($targetAgentUuid);  //目标主机信息
                $masterInfo = $masterAgentObject['agentInfo'];
                $targetMachineInfo = $targetAgentObject['agentInfo'];
                $cutBackupTargetDev = $this->getCutBackupTargetDev($data,$taskuuid);
                $agentInfo = array(
                    "master_info" => $masterInfo,
                    "target_machine" => $targetMachineInfo,
                    "target_dev" => $cutBackupTargetDev
                );
            }
        }
        //判断执行对象任务类型为恢复任务，且执行操作必须为启动任务
        else if(xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY'] == $taskType && $startType == xphp_get_config('task','TASK_CONTROL')['START']){
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
     * 获取回切目标设备信息
     * @param $data
     * @return void
     */
    private function getCutBackupTargetDev($data,$task_uuid){
        $volName = array();
        foreach ( $data as $d) {
            $targetVoluuid = $d['takeover_failback_target_vol_uuid'];
            $targetDiskuuid = $d['recovery_target_disk_uuid'];
            $sql = "select failback_target_dev_name from cdp_vol_task_disk where task_uuid = ?";
            $data = $this->dbSelect($sql,array($task_uuid));
            foreach ($data as $d){
                $volName[] = array(
                    "dev_name" => $d['failback_target_dev_name']
                );
            }

        }
        return $volName;
    }

    public function getTaskNetworkConfInfo($params){
        $taskuuid = $params['task_uuid'];
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
            if($takeoverAgentType == xphp_get_config('vm','VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']){
                $nicList = $this->getHostNetworkInfo(['agent_uuid' => $masterAgentuuid]);
            }else{
                $nicList = $this->getTempAgentConfigInfo($masterAgentuuid);
            }
        }else{
            if($masterAgentuuid!=""){  //发送更新命令，更新网卡信息。
                $this->updateHostNetCardinf($masterAgentuuid);
                $nicList = $this ->getHostNetworkInfo(['agent_uuid' => $masterAgentuuid]);
            }else{
                //接管任务从备份集中取对应网卡信息，备份任务直接提示获取客户端网卡信息异常，“客户端离线或程序异常”
                if($taskType ==  xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){  //接管任务
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
            $standbyNicList = $this->getHostNetworkInfo(['agent_uuid' => $failbackhost]);
        }else{
            $standbyHostInfo = $this->getTaskStandbyHostInfo($taskuuid);
            //TODO 判断备机类型，备机类型为代理时，对应takeover_vm_hypervisor值为0，按之前流程走
            //如果非0，从takeover_vm_config中获取interfaces
            // $takeoverAgentType = $standbyHostInfo['takeover_agent_type'];
            $standbyNicList = $standbyHostInfo['standby_nic_list'];
           
        }
        $taskNetworkConf = array(
            "agent_nic_list" => $nicList,
            "standby_nic_list" => $standbyNicList,
        );
        return $taskNetworkConf;
    }
     /**
     * 修改任务回切网络映射
     */
    public function modifyTaskTakeOverNetwork($params)
    {
        $pfMSg = array();
        $task_uuid = $params['task_uuid'];
        $pfMSg['task_uuid'] =$task_uuid;
        $pfMSg['takeover_business_ip_map'] = $params['takeover_business_ip_map'];
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);

        $nodeUuid = (new Node())->getNodeUUIDWithTaskUUID($task_uuid);   //任务UUID对应的存储节点uuid

        $opName = 'VOL_CDP_TASK_OP_TAKEOVER_CONFIG';
        $mbResult = $this->mbVolCdpMsg($nodeUuid, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];

        $operate = (new VolcdpOpcode())->getOpcodeDes($opName);

        //返回结果到UI
        if($result){
            return $this->muOpResult($result, $operate, $msg);
        }else{
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     *  获取整机实内嵌虚拟机配置
     */
    public function getTakeoverVmConfig($params){
        $task_uuid = $params['task_uuid'];
        $sql = "select takeover_vm_config, host_reset_name from cdp_vol_task_takeover_info where task_uuid = ?";
        $data = $this->dbSelect($sql,array($task_uuid));
        $takeover_vm_config = json_decode($data[0]['takeover_vm_config'],true);
        // 重置主机名
        $host_reset_name = $data[0]['host_reset_name'];
        // 备机名称
        $vm_name = $takeover_vm_config['vm_name'];
        // CPU
        // 插槽数
        $cpu_slot_num = $takeover_vm_config['cpu_slot_num'];
        // 每个插槽核心数
        $cpu_slot_core_num = $takeover_vm_config['cpu_slot_core_num'];
        // CPU模式
        $cpu_mode = $takeover_vm_config['cpu_mode'];
        // 内存
        $memoryMB = v1_calsize($takeover_vm_config['memoryMB']*1024*1024,true);
        // 磁盘配置
        $disks = $takeover_vm_config['disks'];
        // MAC地址
        // 网络配置
        $interfaces = $takeover_vm_config['interfaces'];
        // 高级配置
        // 系统固件类型
        $firmware_type = $takeover_vm_config['os']['firmware_type'];
        // 最大开机等待时间
        $max_boot_wait_time = $takeover_vm_config['max_boot_wait_time'];
        // 节点
        $params = array(
            'offset'=> 0,
            'limit'=> 100,
        );
        $nodes = (new Node())->getNodes($params);
        $rows = $nodes['rows'];
        $node_uuid_list = array();
        foreach ($rows as $node){
            $node_uuid_list[] = $node['node_uuid'];
        }
        $takeoverVmConfig = array(
            'host_reset_name' => $host_reset_name,
            'vm_name' => $vm_name,
            'cpu_slot_num' => $cpu_slot_num,
            'cpu_slot_core_num' => $cpu_slot_core_num,
            'cpu_mode' => $cpu_mode,
            'memoryMB' => $memoryMB,
            'disks' => $disks,
            'interfaces' => $interfaces,
            'firmware_type' => $firmware_type,
            'max_boot_wait_time' => $max_boot_wait_time,
            'node_uuid_list' => $node_uuid_list
        );
        return $takeoverVmConfig;
    }

    /**
     *  获取任务信息回填修改
     * @param array $params 请求参数
     * @return array
     */
    public function getJobInfos(array $params = []): array
    {
        $joubUuid = $params['job_uuid'];
        $sql = "select 
                    bt.node_uuid,bt.task_name, bt.storage_uuid, bt.strategy_id, bt.thread_num, bt.worm_flag,
                    bt.virus_scan_flag,bt.integrity_check_flag, bt.ignore_resource_limiting_flag, 
                    bt.node_pool_uuid, bt.storage_pool_uuid, bsr.storage_type,bt.task_type,
                    bss.compressed_flag, bss.encrypted_flag, bss.password_auto_flag, bss.password,
                    bss.encrypt_method as bss_encrypt_method, bss.compress_method, bss.deduplication_flag,
                    bss.redundant_data_proportiont, bss.data_container_size, bss.block_size block_size_storge,
                    bss.deduplication_flag,
                    brs.strategy_type, brs.number, brs.strategy_mode,
                    bts.encrypt_flag, bts.encrypt_method as bts_encrypt_method,bts.network_pool_uuid,
                    bts.network_uuid, bts.reconnect_times,bts.compress_flag as bts_compress_flag,
                    bts.compress_method as bts_compress_method,bts.block_size,
                    cvtci.agent_memory_cache_alloc_space,cvtci.agent_memory_cache_used_space,
                    cvtci.agent_file_cache_alloc_space,cvtci.agent_file_cache_used_space,
                    cvtci.agent_cache_file_storage_path,
                    cvthps.detect_type,cvthps.mem_threshold,cvthps.cbt_enable_flag,
                    cvthps.cbt_mem_threshold,cvthps.cbt_detect_interval,cvthps.resource_protect_config,
                    cvt.skip_bad_block_flag,cvt.auto_takeover_enable_flag,cvt.cross_platform_flag,  
                    cvt.cross_platform_conf,cvt.auto_fault_resume_flag,cvt.full_backup_flag,
                    cvt.monitor_data_io_replication_mode
                from bd_task bt 
                left join cdp_vol_task cvt on cvt.task_uuid = bt.task_uuid
                left join bd_storage_resource bsr on bt.storage_uuid = bsr.storage_uuid
                left join bd_storage_strategy bss on bt.strategy_id = bss.strategy_id
                left join bd_reserved_strategy brs on bt.strategy_id = brs.strategy_id
                left join bd_transport_strategy bts on bt.strategy_id = bts.strategy_id
                left join cdp_vol_task_cache_info cvtci on cvtci.task_uuid = bt.task_uuid
                left join cdp_vol_task_high_pressure_strategy cvthps on cvthps.task_uuid = bt.task_uuid
                                                                            and cvthps.failback_flag = 0
                where bt.task_uuid = ?";
        $data = $this->dbSelect($sql, [$joubUuid]);
        if (empty($data)) {
            return [];
        }

        // 客户端内存缓存大小 单位是mb
        $clientCache = $data[0]['agent_memory_cache_alloc_space'] / 1024 / 1024;
        if (!empty($data[0]['agent_memory_cache_used_space'])) {
            $clientCacheUsed = $data[0]['agent_memory_cache_used_space'] / 1024 / 1024;
        }
        // 客户端文件缓存大小
        $clientFsCache = $data[0]['agent_file_cache_alloc_space'] / 1024 / 1024;
        if (!empty($data[0]['agent_file_cache_used_space'])) {
            $clientFsCacheUsed = $data[0]['agent_file_cache_used_space'] / 1024 / 1024;
        }
        // 处理下资源监测
        $memThreshold = $data[0]['mem_threshold']; // 停止任务内存阈值
        $cbtMemThreshold = $data[0]['cbt_mem_threshold']; // 降级内存阈值
        $detectType = $data[0]['detect_type'];
        if ($detectType == 2) {
            // mb/gb
            $memThreshold = ceil($memThreshold / 1024 / 1024);
            $cbtMemThreshold = ceil($cbtMemThreshold / 1024 / 1024);
            if ($memThreshold % 1024 == 0 && $cbtMemThreshold % 1024 == 0) {
                // gb
                $memThreshold = ceil($memThreshold / 1024);
                $cbtMemThreshold = ceil($cbtMemThreshold / 1024);
                $detectType = 3;
            }
        }

        // 接管信息
        $config = $this->getTakeOverConfig($joubUuid, $data[0]['task_type']);
        $cacheConfig = json_decode($data[0]['resource_protect_config'], true);
        return [
            //任务UUID
            'job_uuid' => $joubUuid,
            'job_type' => $data[0]['task_type'],
            'job_name' => $data[0]['task_name'],
            //获取备份源信息
            'backup_oss_info' => $this->getBackupAgentInfo($joubUuid),
            //获取目标节点目标存储
            'node' => array(
                //获取节点uuid
                'node_uuid' => $data[0]['node_uuid'],
                //获取节点资源池
                'node_pool_uuid' => $data[0]['node_pool_uuid'],
                //获取存储uuid
                'storage_uuid' => $data[0]['storage_uuid'],
                //获取存储资源池
                'storage_pool_uuid' => $data[0]['storage_pool_uuid'],
                //获取存储类型
                'storage_pool_type' => $data[0]['storage_type']
            ),
            // --通用策略--
            // 获取通用策略-标签策略
            'label_strategy' => $this->getLabelStrategt($data[0]['strategy_id']),
            //获取通用策略-限速策略
            'speed_strategy' => $this->getSpeedStrategy($joubUuid),
            //获取通用策略-存储策略
            'storage_strategy' => array(
                //获取压缩存储
                'compress' => v1_parse_flag_to_bool($data[0]['compressed_flag']),
                //获取数据加密
                'encrypt' => v1_parse_flag_to_bool($data[0]['encrypted_flag']),
                //获取自动生成密码
                'password_auto_flag' => v1_parse_flag_to_bool($data[0]['password_auto_flag']),
                //获取密码
                'password' => base64_encode(v1_pt_pass_decrypt($data[0]['password'])),
            ),
            //获取通用策略-保留策略
            'reserve_strategy' => array(
                // 'type' => intval($data[0]['strategy_type']),
                // 'strategy_mode' => intval($data[0]['strategy_mode']),
                'number' => intval($data[0]['number']), // 最大可回退天数
            ),
            // --传输策略--
            'transfer_strategy' => array(
                //加密算法
                // 'encrypt_method' => intval($data[0]['bts_encrypt_method']),
                //传输网络
                'network' => $data[0]['network_uuid'],
                //网络资源池
                'network_pool_uuid' => $data[0]['network_pool_uuid'],
                //加密传输
                'encrypt' => v1_parse_flag_to_bool($data[0]['encrypt_flag']),
                //源端压缩
                'original_compress_flag' => v1_parse_flag_to_bool($data[0]['bts_compress_flag']),
                //源端压缩等级
                'original_compress_method' => intval($data[0]['bts_compress_method']),
                //传输线程
                'thread_num' => intval($data[0]['thread_num']),
                // 传输数据包大小
                'block_size' => v1_calsize_to_value_and_unit(intval($data[0]['block_size']))['value'],
            ),
            // --应急接管--
            'take_over' => [
                // 是否开启接管
                'auto_takeover_enable_flag' => !empty($config['takeover_type']),
                'config' => $config,
                'cross_platform_flag' => $data[0]['cross_platform_flag'],
                'cross_platform_conf' => $data[0]['cross_platform_conf'],
            ],
            // --脚本配置--
            'scripts' => $this->getScriptStrategy($joubUuid),
            // --高级配置--
            //获取高级配置-公共配置
            'high_config' => array(
                // 存储数据块大小
                'block_size_storge' => (intval($data[0]['block_size_storge']) / 1024),
                // IO复制模式
                'io_mode' => $data[0]['monitor_data_io_replication_mode'],
                // 有效数据备份 是反的
                'valid_flag' => !v1_parse_flag_to_bool($data[0]['full_backup_flag']),
                // 跳过坏块备份
                'skip_bad_block_flag' => v1_parse_flag_to_bool($data[0]['skip_bad_block_flag']),
                // 断点续传
                'continue_flag' => v1_parse_flag_to_bool($data[0]['auto_fault_resume_flag']),
            ),
            //获取高级配置-缓存配置
            'cache_config' => [
                'agent_memory_cache_alloc_space' => $clientCache, // 客户端内存缓存大小
                'agent_memory_cache_used_space' => $clientCacheUsed ?? 0, // 客户端内存缓存大小-已使用
                'agent_file_cache_alloc_space' => $clientFsCache, // 客户端文件缓存大小
                'agent_file_cache_used_space' => $clientFsCacheUsed ?? 0, // 客户端文件缓存大小-已使用
                'agent_cache_file_storage_path' => $data[0]['agent_cache_file_storage_path'], // 客户端文件缓存路径
                'agent_memory_cache_flag' => $clientCache !== 0, // 如果客户端内存缓存大小为0，那么表示关闭
            ],
            //获取高级配置-资源监测
            'source_config' => [
                'detect_type' => $detectType, // 单位 1百分比 2MB 3GB
                'mem_threshold' => $memThreshold, // 停止任务内存阈值
                'cbt_enable_flag' => v1_parse_flag_to_bool($data[0]['cbt_enable_flag']), // 持续数据保护降级开关
                'cbt_mem_threshold' => $cbtMemThreshold, // 降级内存阈值
                'cbt_detect_interval' => $data[0]['cbt_detect_interval'], // 恢复持续数据保护检测间隔
            ],
            'resource_protect_config' => empty($cacheConfig) ? [] : $cacheConfig, // 处理下新增的linux的资源监测结果
            //获取高级配置-过载保护
            'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($data[0]['ignore_resource_limiting_flag']),
        ];
    }

    /***
     * 获取整个任务备份源信息
     * @param string $jobUuid 任务uuid
     * @return array
     */
    private function getBackupAgentInfo(string $jobUuid)
    {
        $sql = "SELECT
                    cvt.master_agent_uuid,cvt.master_agent_detail,
                    ba.agent_name, ba.hostname, ba.ip
                FROM
                    cdp_vol_task cvt 
                    left join bd_agent ba on ba.agent_uuid = cvt.master_agent_uuid
                WHERE
                    cvt.task_uuid = ?";
        $data = $this->dbSelect($sql, [$jobUuid]);
        $return = [
            'agent_uuid' => '', // 客户端uuid
            'app_uuid' => '', // 客户端应用uuid
            'agent_name' => '', // 客户端名称
            'disk_struct_backup_mode' => true, // 是否关联勾选，未找到哪儿存储
            'exclude_devices_list' => [], // 排除磁盘
        ];
        if (!empty($data)) {
            $return['agent_uuid'] = $data[0]['master_agent_uuid'];
            $agentName = !empty($data[0]['hostname']) ?
                ($data[0]['hostname'] . '(' . $data[0]['ip'] . ')') :
                $data[0]['agent_name'];
            $return['agent_name'] = $agentName;

            // 计算下哪些磁盘未选中
            $disk = $this->dbSelect('select * from cdp_vol_task_exclude_vol where task_uuid = ?', [$jobUuid]);
            foreach ($disk as $item) {
                $return['exclude_devices_list'][] = [
                    'dev_name' => $item['dev_name'],
                    'dev_node_uuid' => $item['dev_node_uuid'],
                    'dev_type' => '',
                    'dev_uuid' => $item['dev_uuid'],
                ];
            }

            // 获取选择的应用uuid
            $app = $this->dbSelect(
                'select app_uuid from cdp_vol_task_takeover_app where task_uuid = ?',
                [$jobUuid]
            );
            if (!empty($app)) {
                $return['app_uuid'] = $app[0]['app_uuid'];
            }
        }
        return $return;
    }

    /**
     * 获取标签策略
     * @param string $strageUuid 策略uuid
     * @return array
     */
    private function getLabelStrategt(string $strageUuid): array
    {
        $info = [];
        $sql = "select * from bd_time_strategy where strategy_id = ?";
        $data = $this->dbSelect($sql, [$strageUuid]);
        if (!empty($data)) {
            foreach ($data as $item) {
                $info[] = [
                    'strategy_id' => $item['strategy_id'],
                    'mode' => $item['mode'],
                    'strategy_type' => $item['strategy_type'],
                    'day' => array_map('intval', str_split($item['days'])),
                    'days' => $item['days'],
                    'start_time' => $item['start_time'],
                    'roll_flag' => v1_parse_flag_to_bool($item['roll_flag']),
                    'roll_interval' => v1_sec_to_time($item['roll_interval']),
                    'roll_end_time' => $item['roll_end_time'],
                ];
            }
        }
        return $info;
    }

    /**
     * 获取脚本配置策略
     * @param string $jobUuid 任务uuid
     * @return array
     */
    private function getScriptStrategy(string $jobUuid): array
    {

        $info = [
            'before_task_script' => [], // 备份前
            'after_task_script' => [], // 备份后
            'before_take_over_script' => [], // 接管前
            'after_take_over_script' => [], // 接管后
            'take_over_script' => [], // 接管监测
        ];
        // 查找脚本内容
        $scripts = $this->dbSelect('select * from cdp_vol_task_common_script where task_uuid = ?', [$jobUuid]);
        if (!empty($scripts)) {
            $config = [
                1 => 'before_take_over_script',
                2 => 'after_take_over_script',
                3 => 'before_task_script',
                4 => 'after_task_script',
                6 => 'take_over_script',
            ];
            foreach ($scripts as $items) {
                $script = [
                    'exec_interval' => $items['exec_interval'],
                    'script_content' => $items['script_content'],
                    'script_method' => !empty($items['template_uuid']) ? 1 : 0,
                    'script_name' => $items['script_name'],
                    'script_type' => $items['script_type'],
                    'script_uuid' => $items['template_uuid'],
                    'trigger_fail_num' => $items['trigger_fail_num'],
                ];
                $info[$config[$items['exec_type']]][] = $script;
            }
        }
        return $info;
    }

    /**
     * 获取接管配置
     * @param string $jobUuid 任务uuid
     * @param int    $jobType 任务类型
     * @return array
     */
    private function getTakeOverConfig(string $jobUuid, int $jobType): array
    {
        $info = [];
        $sql = 'select cvt.auto_takeover_flag,cvt.mirror_backup_flag,cvt.standby_agent_uuid
                from cdp_vol_task cvt
                where cvt.task_uuid = ?';
        $data = $this->dbSelect($sql, [$jobUuid]);
        if (!empty($data)) {
            $takeover = $this->dbSelect('select * from cdp_vol_task_takeover_info where task_uuid = ?', [$jobUuid]);
            if (!empty($takeover)) {
                $vmHyper = xphp_get_config('vm', 'VMHYPERVISORTYPE');
                // 组装接管配置
                $info = [
                    // 接管方式 // takeover_vm_hypervisor 如果是 108 那么就是整机接管
                    'takeover_type' => $takeover[0]['takeover_vm_hypervisor'] == $vmHyper['VM_HYPERVISOR_TYPE_EMD']
                        ? 1 : 2,
                    // 网络配置
                    'network' => json_decode($takeover[0]['detail'], true),
                    // 应用接管
                    'app_flag' => v1_parse_flag_to_bool($takeover[0]['app_takeover_flag']),
                    // 自动接管
                    'auto_flag' => v1_parse_flag_to_bool($data[0]['auto_takeover_flag']),
                    // 自动接管-心跳失效时间
                    'agent_heartbeat_failure_time' => $takeover[0]['agent_heartbeat_failure_time'],
                    // 自动接管-应用故障监测 --如果次数和间隔都是0，那么就是没设置
                    'agent_heartbeat_failure_flag' => !($takeover[0]['app_consecutive_failure_num'] == 0 &&
                        $takeover[0]['app_fault_detection_interval'] == 0),
                    // 自动接管-连续故障次数
                    'app_consecutive_failure_num' => $takeover[0]['app_consecutive_failure_num'],
                    // 自动接管-故障监测间隔
                    'app_fault_detection_interval' => $takeover[0]['app_fault_detection_interval'],
                    // 重置主机名
                    'host_reset_name_flag' => !empty($takeover[0]['host_reset_name']),
                    'host_reset_name' => $takeover[0]['host_reset_name'],
                    'safe' => [],
                ];

                if ($info['takeover_type'] == 1) {
                    // 容灾备机
                    $info['vm_config'] = json_decode($takeover[0]['takeover_vm_config'], true);
                    // 内嵌接管，还需要查询出病毒扫描
                    $safe = $this->dbSelect(
                        'select virus_scan_config_list from bd_task_safe_config where task_uuid = ?',
                        [$jobUuid]
                    );
                    $info['vm_config']['takeover_vm_hypervisor'] = $takeover[0]['takeover_vm_hypervisor'];
                    $info['vm_config']['takeover_vm_node_uuid'] = $takeover[0]['takeover_vm_node_uuid'];
                    if (!empty($safe)) {
                        $info['safe'] = json_decode($safe[0]['virus_scan_config_list'], true);
                    }
                } else {
                    // 接管备机
                    $info['takeover_standby_agent_uuid'] = $takeover[0]['takeover_standby_agent_uuid'];
                    // 挂载接管的回切通讯IP
                    $info['agent_failback_standby_ip'] = $takeover[0]['agent_failback_standby_ip'];
                    // 还差磁盘的映射配置
                }
            } else {
                $info['takeover_type'] = 0;
            }

            if ($jobType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION']) {
                // 如果是整机复制任务
                // 查询备机以及选择的磁盘映射关系
                $info['standby_agent_uuid'] = $data[0]['standby_agent_uuid'];
                $info['standby_agent_list'] = [];
                $sql = 'select standby_target_dev_uuid,dev_uuid from cdp_vol_task_disk where task_uuid = ?';
                $datas = $this->dbSelect($sql, [$jobUuid]);
                if (!empty($datas)) {
                    foreach ($datas as $ir) {
                        $info['standby_agent_list'][$ir['dev_uuid']] = [
                            'selected_type' => 'selected',
                            'selected_value' => $ir['standby_target_dev_uuid'],
                        ];
                    }
                }
                // 是否备份数据
                $info['mirror_backup_flag'] = v1_parse_flag_to_bool($data[0]['mirror_backup_flag']);
            }
        }
        return $info;
    }

}