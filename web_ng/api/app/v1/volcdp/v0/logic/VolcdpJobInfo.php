<?php

namespace app\v1\volcdp\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\Data;
use app\v1\common\logic\JobInfo;
use app\v1\copy\v0\logic\CopyJobInfo;
use app\v1\hadoop\v0\logic\HadoopJobInfo;
use app\v1\opcode\PfOpcode;
use app\v1\opcode\VolcdpOpcode;
use app\v1\os\v0\logic\OsJobInfo;
use app\v1\resources\v0\logic\Client;
use app\v1\resources\v0\logic\Index;
use app\v1\resources\v0\logic\Node;
use app\v1\system\v0\validate\Time;
use app\v1\vm\v0\logic\VmJobInfo;

/**
 * note          卷实时 之任务信息 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VolcdpJobInfo extends Base
{

    /**
     * 获取回切目标卷信息
     * @param $data
     * @return void
     */
    private function getCutBackupTargetVol($data)
    {
        $volName = array();
        foreach ($data as $d) {
            $targetVoluuid = $d['takeover_failback_target_vol_uuid'];
            $targetDiskuuid = $d['recovery_target_disk_uuid'];
            $sql = "select vol_name,display_name from bd_agent_vol where vol_uuid = ?";
            $data = $this->dbSelect($sql, array($targetVoluuid));
            if (!empty($data)) {
                $volName[] = array(
                    "vol_name" => $data[0]['display_name']
                );
            }
            $sqlDisk = "select display_name from bd_agent_disk where disk_uuid = '{$targetDiskuuid}'";
            $diskData = $this->dbSelect($sqlDisk);
            if (!empty($diskData)) {
                $volName[] = array(
                    "vol_name" => $diskData[0]['display_name']
                );
            }
        }
        return $volName;
    }

    /**
     * 获取备份任务当前运行状态
     * @param string $taskUuid
     * @return mixed
     */
    public function getVolCdpTaskRunningStage(string $taskUuid)
    {

        $sql = "select current_task_running_stage from cdp_vol_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        return $data[0]['current_task_running_stage'];

    }

    /**
     * 获取任务所有卷已完成容量总和
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
     * 根据条件组合客户端的名称
     */
    public function agentStr($agentName, $hostName, $ip)
    {
        if (empty($agentName) && empty($hostName) && empty($ip)) {
            return "";
        }
        if (!empty($agentName) && $agentName != $ip) {
            $hostInfo = $agentName ? $agentName . "(" . $ip . ")" : $hostName . "(" . $ip . ")";
        } else {
            $hostInfo = $hostName . "(" . $ip . ")";
        }
        return $hostInfo;
    }

    /**
     * 获取客户端别名
     * @param string $params agent_uuid
     */
    private function getBdAgentInfo($params)
    {
        if ($params) {
            $sql = "select agent_name,hostname, ip,online_flag from bd_agent where agent_uuid = ? ";
            $data = $this->dbSelect($sql, array($params));
            $agentName = $data[0]['agent_name'];
            $agentIp = $data[0]['ip'];
            $hostName = $data[0]['hostname'];
            $agentIsOnline = $data[0]['online_flag'];

            $agentInfo = $this->agentStr($agentName, $hostName, $agentIp);
            $agentArry = array(
                "agentInfo" => $agentInfo,
                "isOnline" => $agentIsOnline,
                "ip" => $data[0]['ip']
            );
        } else {
            $agentArry = array(
                "agentInfo" => "--",
                "isOnline" => 0,
                "ip" => "--"
            );
        }
        return $agentArry;
    }


    /**
     * @param $params 获取卷cdp模块启动任务对象详细信息
     */
    public function getStartTaskObjectDetail($params)
    {
        $taskuuid = $params['task_uuid'];
        $taskType = $params['task_type'];
        $startType = $params['start_type'];
        $agentInfo = array();

        //判断执行对象任务类型为备份或接管，且执行操作必须为启动接管
        if (
            (xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER'] == $taskType
                || xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'] == $taskType
                || xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION'] == $taskType)
            && $startType == xphp_get_config('task', 'TASK_CONTROL')['START_FAILBACK']
        ) {
            $sql = "SELECT takeover_info.takeover_standby_agent_uuid,takeover.failback_target_agent_uuid 
                    from cdp_vol_task_takeover_info takeover_info,cdp_vol_task_takeover_failback_info takeover 
                    where takeover_info.task_uuid = takeover.task_uuid and takeover_info.task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
            if (!empty($data)) {
                $taskInfo = $data[0];
                $takeoverStandbyAgentuuid = $taskInfo['takeover_standby_agent_uuid'];
                $targetAgentUuid = $taskInfo['failback_target_agent_uuid'];
                $masterAgentObject = $this->getBdAgentInfo($takeoverStandbyAgentuuid);  //数据源主机信息
                $targetAgentObject = $this->getBdAgentInfo($targetAgentUuid);  //目标主机信息
                $masterInfo = $masterAgentObject['agentInfo'];
                $targetMachineInfo = $targetAgentObject['agentInfo'];
                $cutBackupTargetDev = $this->getCutBackupTargetDev($data, $taskuuid);
                $agentInfo = array(
                    "master_info" => $masterInfo,
                    "target_machine" => $targetMachineInfo,
                    "target_dev" => $cutBackupTargetDev
                );
            }
        }
        //判断执行对象任务类型为恢复任务，且执行操作必须为启动任务
        else if (xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY'] == $taskType && $startType == xphp_get_config('task', 'TASK_CONTROL')['START']) {
            $sql = "SELECT master_agent_uuid,recovery_target_agent_uuid from cdp_vol_task where task_uuid= ?";
            $data = $this->dbSelect($sql, array($taskuuid));
            if (!empty($data)) {
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
        } else if (xphp_get_config('task', 'TASKTYPE')['CDP_DB_BACKUP'] == $taskType) {
            $sql = "SELECT failback_info.failback_target_agent_uuid, ba1.agent_name as source_failback_agent_name, cddt.target_agent_uuid, ba2.agent_name as target_failback_agent_name
                    FROM cdp_db_dr_task_takeover_failback_info failback_info
                    JOIN cdp_db_dr_task_takeover_info takeover_info ON failback_info.task_uuid = takeover_info.task_uuid
                    JOIN cdp_db_dr_task cddt ON failback_info.task_uuid = cddt.task_uuid
                    JOIN bd_agent ba1 ON ba1.agent_uuid = failback_info.failback_target_agent_uuid
                    JOIN bd_agent ba2 ON ba2.agent_uuid = cddt.target_agent_uuid
                    WHERE failback_info.task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
            if (!empty($data)) {
                $taskInfo = $data[0];
                $failbackTargetAgentUuid = $taskInfo['failback_target_agent_uuid'];
                $sourceFailbackAgentName = $taskInfo['source_failback_agent_name'];
                $targetAgentUuid = $taskInfo['target_agent_uuid'];
                $targetFailbackAgentName = $taskInfo['target_failback_agent_name'];
                $agentInfo = array(
                    "failback_target_agent_uuid" => $failbackTargetAgentUuid,
                    "source_failback_agent_name" => $sourceFailbackAgentName,
                    "target_agent_uuid" => $targetAgentUuid,
                    "target_failback_agent_name" => $targetFailbackAgentName
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
    private function getCutBackupTargetDev($data, $task_uuid)
    {
        $volName = array();
        foreach ($data as $d) {
            $targetVoluuid = $d['takeover_failback_target_vol_uuid'];
            $targetDiskuuid = $d['recovery_target_disk_uuid'];
            $sql = "select failback_target_dev_name from cdp_vol_task_disk where task_uuid = ?";
            $data = $this->dbSelect($sql, array($task_uuid));
            foreach ($data as $d) {
                $volName[] = array(
                    "dev_name" => $d['failback_target_dev_name']
                );
            }

        }
        return $volName;
    }

    /**
     * 获取任务历史网络配置
     * @param unknown $params
     */
    public function getTaskNetworkHistoryConf($params)
    {
        $taskuuid = $params['task_uuid'];
        $taskType = $params['task_type'];
        $hisNicConfList = array();
        if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'] 
            || $taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER'] 
            || $taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_REPLICATION']) {
            $sql = "select detail from cdp_vol_task_takeover_info where task_uuid =?";
            $data = $this->dbSelect($sql, array($taskuuid));
            $detail = json_decode($data[0]['detail']);
            $failbackVusinessIpMap = $detail->failback_business_ip_map;
            if(!$failbackVusinessIpMap){
                $failbackVusinessIpMap = [];
            }
            $hisNicConfList = array(
                "takeover_business_ip_map" => $detail->takeover_business_ip_map,
                "failback_business_ip_map" => $failbackVusinessIpMap
            );
        }
        return $hisNicConfList;
    }

    /**
     * 获取卷CDP任务数据流向状态
     * @param string task_id,int task_type
     * @return 可操作项array
     */
    public function getVolCdpTaskMapInfo($params)
    {
        $taskUuid = $params['taskuuid'];
        $taskType = $params['tasktype'];

        if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP']) {   //备份
            $taskMapInfo = $this->getBackupTaskMapInfo($params);
        } else if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY']) { //恢复
            $taskMapInfo = $this->getRecoverTaskMapInfo($params);
        } else if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER']) { //接管
            $taskMapInfo = $this->getTakeoverTaskMapInfo($params);
        }
        return $taskMapInfo;
    }

    /**
     * 任务详情: 得到卷CDP模块任务基本信息
     * @param unknown $params
     */
    public function getVolCdpBasicInfo($params)
    {
        $taskUUID = $params['task_uuid'];
        $this->paramsCheck($taskUUID);
        $sql = "select bt.task_uuid, bt.task_name, bt.module_type, bt.task_type, bt.task_status, bt.thread_num,
                       bt.module_type, bt.strategy_id, unix_timestamp(bt.create_time) create_time, bt.transport_ip_segment,
                	   bt.node_uuid, bt.storage_uuid, bu.user_name, unix_timestamp(bs.next_start_time) next_start_time,
                	   bri.total_object_size, bri.total_object_completed_size, bri.speed, unix_timestamp(bri.start_time) start_time,
                       bri.total_object_valid_size,bri.total_object_completed_valid_size,vol_task.current_task_running_stage,
                       vol_task.master_agent_uuid,vol_task.standby_agent_uuid,vol_task.recovery_target_agent_uuid,vol_task.auto_takeover_flag,
                       vol_task.rebuild_partition_flag,vol_task.recovery_data_source,vol_task.monitor_data_io_replication_mode,
                       vol_task.takeover_data_source,vol_task.mirror_backup_flag,vol_task.backup_mode,vol_task.auto_takeover_enable_flag
                from   bd_task bt, bd_user bu, bd_running_info bri, bd_strategy bs,cdp_vol_task vol_task
                where  bt.user_uuid = bu.user_uuid and 
                       bt.task_uuid = bri.task_uuid and 
                       bt.strategy_id = bs.strategy_id and 
                       bt.task_uuid = vol_task.task_uuid and 
                	   bt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        if (!$data) {
            $basicInfo = array('flag' => false);
            return json_encode($basicInfo);
        }
        foreach ($data as $d) {
            $masterHostInfo = $this->getHostInfo($d['master_agent_uuid'], $d['task_type'], $taskUUID);  //主机
            $standbyHostInfo = $this->getHostInfo($d['standby_agent_uuid'], $d['task_type'], $taskUUID);    //备机

            $totalObjectSize = $d['total_object_size'];
            $completedValidSize = $d['total_object_completed_valid_size'];

            $totalValidSize = $d['total_object_valid_size'];
            $currentTaskRunningStage = $d['current_task_running_stage'];

            $valueFlag = false;
            $currentSizeValue = 0;
            if ($totalObjectSize != 0 && $completedValidSize != 0) {
                $valueFlag = true;
                $currentSizeValue = $this->getTaskTotalCapacityInfo($taskUUID);
            }
            if (!(is_numeric($currentSizeValue))) {
                $valueFlag = false;
            }
            $currentSize = v1_calsize($currentSizeValue, $valueFlag);
            $totailSize = v1_calsize($d['total_object_size']);
            $progress = $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $currentSizeValue, false, $d['task_type']);

            $totalprogress = $this->getTaskTotalProgress($d['task_status'], $d['total_object_size'], $currentSizeValue, true, $d['task_type']);
            //任务处于实时同步阶段或逆向实时同步阶段
            if (
                $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['REALTIME_SYNC']
                || $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['WAIT_CONVERT_TO_REALTIME_SYNC']
                || $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
            ) {
                $currentSize = $this->getTaskCurrentTotalSize($d['task_uuid']);
            }
            $consistencyCheckVol = "--";
            if ($currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['SERVER_CONS_CHECK']) { //服务端数据一致性校验阶段
                $serverConsCheckInfo = $this->getServerConsCheckInfo($taskUUID);
                $totailSize = v1_calsize($serverConsCheckInfo['total_size']);
                $currentSize = v1_calsize($serverConsCheckInfo['completed_size']);
                $consistencyCheckVol = $serverConsCheckInfo['current_vol'];
                $totalprogress = $serverConsCheckInfo['total_progress'];
                $progress = $totalprogress;
            }

            $storageuuid = $d['storage_uuid'];
            $monitorDataIoReplicationMode = $d['monitor_data_io_replication_mode'];
            $threadNum = $d['thread_num'];
            $mirrorBackupFlag = $d['mirror_backup_flag'];
            if (
                $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
                || $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
                || $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']
            ) {  //任务处于回切阶段
                $failbackInfo = $this->getFailbackInfo($taskUUID);
                $storageuuid = $failbackInfo['storage_uuid'];
                $monitorDataIoReplicationMode = $failbackInfo['monitor_data_io_replication_mode'];
                $threadNum = $failbackInfo['transport_thread_num'];
                $mirrorBackupFlag = $failbackInfo['mirror_backup_flag'];
            }
            $taskStatusValue = $d['task_status'];
            $startTime = $this->getTaskStartTime($d['start_time'], $d['task_status'], $taskUUID, $d['task_type']);
            $currentTaskRunningStageValue = $d['current_task_running_stage'];
            $speedValue = $d['speed'];
            if ($speedValue == 0) {
                $speed = "--";
            } else {
                $speed = $this->getJobSpeed($d['task_status'], $d['speed']);
            }
            $basicInfo = array(
                'taskName' => $d['task_name'],
                'moduleType' => xphp_get_desc('Pf', 'MODULE_TYPE_DES')[$d['module_type']],
                'taskType' => xphp_get_desc('Pf', 'TASKTYPEDES')[$d['task_type']],
                'user' => $d['user_name'],
                'status' => xphp_get_desc('Pf', 'TASKSTATUSDES')[$taskStatusValue],
                'statusValue' => intval($taskStatusValue),
                'totalSize' => $totailSize,
                'currentSize' => $currentSize,
                'speed' => $speed,
                'progress' => $progress,
                'totalprogress' => $totalprogress,
                'createTime' => $this->parseDate($d['create_time']),
                'startTime' => $this->getStartTIme($startTime, $d['task_status']),
                'intervalTime' => (new CopyJobInfo())->getTimeInterval($startTime, $d['task_status']),
                'endTime' => (new OsJobInfo())->getCalEndTime($d['total_size'], $d['current_total_size'], $d['task_status'], $d['speed']),
                'nextTime' => (new VmJobInfo())->getNextStartTime($d['next_start_time'], $d['task_status']),
                'timeStrategy' => (new VmJobInfo())->getJobTimeStrategy($d['strategy_id']),
                'reservedStrategy' => (new CopyJobInfo())->getReservedStrategy($d['strategy_id'], $d['task_uuid']),
                'transportStrategy' => $this->getVolCdpTransportStrategy($taskUUID, $d['strategy_id'], $currentTaskRunningStage),
                'storageInfo' => (new HadoopJobInfo())->getNodeInfo($d['task_type'], $d['strategy_id'], $d['node_uuid'], $storageuuid),
                'flag' => true,
                'taskTypeValue' => intval($d['task_type']),
                'modeStrategy' => '',
                'thread_num' => $threadNum,
                'speed_limit' => (new JobInfo())->getSpeedlimitDes($d['task_uuid']),
                'applianceDes' => $this->getJobAppliance($d['task_uuid'])['des'],
                'fulldiskRestore' => (new Data())->getRecoveryFullDisk($d['task_uuid']),
                'transport_ip_segment' => $d['transport_ip_segment'],
                'cache_info' => $this->getVolCdpTaskCacheInfo($d['task_uuid'], $currentTaskRunningStage),
                'current_task_running_stage' => xphp_get_desc('Pf', 'CDP_TASK_RUNNING_STAGE')[$currentTaskRunningStageValue], //当前任务运行阶段
                'current_task_running_stage_value' => $currentTaskRunningStageValue, //当前任务运行阶段value
                'os_type' => $masterHostInfo['os_type'],
                'master_agent_info' => $masterHostInfo['host_info'],
                'agent_ip' => $masterHostInfo['agent_ip'],
                'master_agent_uuid' => $d['master_agent_uuid'],
                'standby_agent_info' => $standbyHostInfo['host_info'],  //备机
                'standby_agent_uuid' => $d['standby_agent_uuid'],
                'recovery_target_agent_uuid' => $d['recovery_target_agent_uuid'],  //恢复代理
                'rebuild_partition_flag' => v1_parse_flag_to_bool($d['rebuild_partition_flag']),  //重建分区标志位.恢复使用
                'monitor_data_io_replication_mode' => $this->getVolCdpTaskIoMode($monitorDataIoReplicationMode), //监控IO复制模式
                'auto_takeover_flag' => v1_parse_flag_to_bool($d['auto_takeover_flag']),  //自动接管标志位
                'auto_takeover_info' => $this->getAutoTakeoverConfInfo($d['task_uuid'], $d['auto_takeover_flag'], $d['task_type']),
                'mirror_backup_flag' => v1_parse_flag_to_bool($mirrorBackupFlag),
                'takeover_app_info' => xphp_get_lang('WEB_JOB_GET_APP_INFO'), //TOGO 增加接管应用解析
                'handover_info' => $this->getHandoverConfInfo($d['task_uuid'], $d['task_type']), //获取手动接管配置
                'takeover_data_source' => $d['takeover_data_source'],
                'consistency_check_vol' => $consistencyCheckVol,
                'backup_mode' => $d['backup_mode'],
                'auto_takeover_enable_flag' => v1_parse_flag_to_bool($d['auto_takeover_enable_flag']),  //自动接管是否启用
            );
        }
        return $basicInfo;
    }

    /**
     * 获取接管脚本配置信息
     */
    public function getTakeoveScriptConf($params)
    {
        $taskUuid = $params['task_uuid'];
        $sql = "select script_type,script_path,exec_type,exec_interval,trigger_fail_num 
            from cdp_vol_task_takeover_script where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $list = array();

        foreach ($data as $v) {
            $list[] = array(
                'script_type' => $v['script_type'],
                'script_path' => $v['script_path'],
                'exec_type' => $v['exec_type'],
                'exec_interval' => $v['exec_interval'],
                'trigger_fail_num' => $v['trigger_fail_num']
            );
        }
        return $list;
    }

    /**
     * 获取任务配置的接管网络信息
     */
    public function getTaskNetworkConfInfo($params)
    {
        $taskuuid = $params['task_uuid'];
        $taskType = $params['task_type'];
        $failbackhost = $params['failbackhost'];  //回切目标主机

        $taskNetworkConf = array();
        $nicList = [];
        $standbyNicList = [];
        /***接管任务，通过任务UUID获取主机信息，判断主机在bd_agent中是否存在。存在则更新客户端网卡信息，不存在需要获取接管时间点。
         * 通过接管时间点和主机UUID找到备份集，根据备份集对应的backup_agent_id 找到对应cdp_vol_backup_agent中master_agent_detail，
         * 在master_agent_detail中获取该客户端的历史网卡信息
         */
        $isFailBack = false;
        if ($failbackhost != "0" && $failbackhost != "") {
            $isFailBack = true;
        }
        $sql = "select master_agent_uuid FROM cdp_vol_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $masterAgentuuid = "";
        if (!empty($data)) {
            $masterAgentuuid = $data[0]['master_agent_uuid']; //任务主机UUID
        }

        $sqlAgent = "select detail from bd_agent where agent_uuid = ? ";
        $agentData = $this->dbSelect($sqlAgent, array($masterAgentuuid));

        if ($isFailBack) {  //回切时，主机uuid为任务对应的接管备机信息
            $standbyHostInfo = $this->getTaskStandbyHostInfo($taskuuid);
            $masterAgentuuid = $standbyHostInfo['stand_agent_uuid'];
            $takeoverAgentType = $standbyHostInfo['takeover_agent_type'];
            if ($takeoverAgentType == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {
                $nicList = (new VolcdpBackUp())->getHostNetworkInfo(['agent_uuid' => $masterAgentuuid]);
            } else {
                $nicList = $this->getTempAgentConfigInfo($masterAgentuuid);
            }
        } else {
            if ($masterAgentuuid != "") {  //发送更新命令，更新网卡信息。
                $updateNetCard = $this->updateHostNetCardinf($masterAgentuuid);
                $nicList = (new VolcdpBackUp())->getHostNetworkInfo(['agent_uuid' => $masterAgentuuid]);
            } else {
                //接管任务从备份集中取对应网卡信息，备份任务直接提示获取客户端网卡信息异常，“客户端离线或程序异常”
                if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER']) {  //接管任务
                    $takeoverSql = "select agent.master_agent_detail from cdp_vol_backup_vol_set vol_set,cdp_vol_task_takeover_info cvtt,
                                cdp_vol_backup_agent agent where agent.id = vol_set.backup_agent_id and cvtt.task_uuid = ?
                         and (UNIX_TIMESTAMP(cvtt.takeover_timestamp) >= UNIX_TIMESTAMP(vol_set.start_timestamp)
                         and UNIX_TIMESTAMP(cvtt.takeover_timestamp) <= UNIX_TIMESTAMP(vol_set.end_timestamp))";
                    $takeoverData = $this->dbSelect($takeoverSql, array($taskuuid));
                    if (!empty($takeoverData)) {
                        $masterAgentDetail = $takeoverData[0]['master_agent_detail'];
                        $detail = json_decode($masterAgentDetail);
                        $nicList = $detail->nic_list;
                    }
                }
            }
        }
        //获取备机网卡信息
        if ($isFailBack) {  //回切任务时，主机为任务对应的takeover_standby_agent_uuid,备机为回切目标对象
//             $updateNetCard = $this->updateHostNetCardinf($failbackhost);
//             if($updateNetCard){
//                 $standbyNicList = $this->getHostNetworkInfo($masterAgentuuid);
//             }
            $standbyNicList = (new VolcdpBackUp())->getHostNetworkInfo(['agent_uuid' => $failbackhost]);
        } else {
            $standbyHostInfo = $this->getTaskStandbyHostInfo($taskuuid);
            //TODO 判断备机类型，备机类型为代理时，对应takeover_vm_hypervisor值为0，按之前流程走
            //如果非0，从takeover_vm_config中获取interfaces
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
        $pfMSg['task_uuid'] = $task_uuid;
        $pfMSg['takeover_business_ip_map'] = $params['takeover_business_ip_map'];
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);

        $nodeUuid = (new Node())->getNodeUUIDWithTaskUUID($task_uuid);   //任务UUID对应的存储节点uuid

        $opName = 'VOL_CDP_TASK_OP_TAKEOVER_CONFIG';
        $mbResult = $this->mbVolCdpMsg($nodeUuid, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];

        $operate = (new VolcdpOpcode())->getOpcodeDes($opName);

        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 获取任务接管回切配置
     */
    public function getVolCdpTaskHostConf($params)
    {
        $user_uuid = xphp_get_user_info()['userUuid'];
        $task_uuid = $params['task_uuid'];
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
        $data = $this->dbSelect($sql, array($task_uuid));
        $list = array();
        if (!empty($data)) {
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
            $taskMasterUuid = $data[0]['agent_uuid']; //任务主机uuid
            $storage_uuid = $data[0]['storage_uuid'];
            $node_uuid = $data[0]['node_uuid'];
            $mirror_backup_flag = $data[0]['mirror_backup_flag'];
            $rebuildPartitionFlag = $data[0]['rebuild_partition_flag'];

            $transport_thread_num = $data[0]['transport_thread_num'];  //传输线程个数
            $transport_block_size = $data[0]['transport_block_size'] / 1024 / 1024;  //传输数据包大小
            $agentInfo = $this->getHostInfo($failback_target_agent_uuid);
            $netModel = $agentInfo['net_model'];
            $host_list = $this->getTakeoverHost($failback_target_agent_uuid, $task_uuid, $task_type, $taskMasterUuid);
        } else {
            $sqlTask = "select vol_task.master_agent_uuid,vol_task.monitor_data_io_replication_mode,task.storage_uuid,
                          task.node_uuid, vol_task.rebuild_partition_flag
                       from cdp_vol_task vol_task,bd_task task 
                       where vol_task.task_uuid = task.task_uuid and vol_task.task_uuid = ?";

            $dataTask = $this->dbSelect($sqlTask, array($task_uuid));
            $taskMasterUuid = $dataTask[0]['master_agent_uuid'];
            $io_replication_mode = $dataTask[0]['monitor_data_io_replication_mode'];
            $storage_uuid = $dataTask[0]['storage_uuid'];
            $node_uuid = $dataTask[0]['node_uuid'];
            $failback_target_agent_uuid = "";
            $mirror_backup_flag = xphp_get_config('app', 'FLAG')['UNSET'];
            $rebuildPartitionFlag = $dataTask[0]['rebuild_partition_flag'];
            $transport_thread_num = 1;  //default value;
            $transport_block_size = 4;  //传输数据包大小默认为4 MB
            $netModel = "";
            //获取配置信息
            if ($task_type == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP']) {
                $sqlCache = "select agent_memory_cache_alloc_space,agent_file_cache_alloc_space,agent_cache_file_storage_path 
                    from cdp_vol_task_cache_info where task_uuid = ?";
                $dataCache = $this->dbSelect($sqlCache, array($task_uuid));

                $memory_cache_alloc_space = $dataCache[0]['agent_memory_cache_alloc_space'];
                $file_cache_alloc_space = $dataCache[0]['agent_file_cache_alloc_space'];
                $file_cache_storage_path = $dataCache[0]['agent_cache_file_storage_path'];

                $sqlTransportStrategy = "select trans.encrypt_flag,trans.compress_flag,trans.compress_method,trans.encrypt_method 
                                         from bd_transport_strategy trans,bd_task task 
                                         where task.task_uuid = ? and task.strategy_id = trans.strategy_id ";
                $dataTrans = $this->dbSelect($sqlTransportStrategy, array($task_uuid));
                $transport_compress_flag = $dataTrans[0]['compress_flag'];
                $transport_encrypt_flag = $dataTrans[0]['encrypt_flag'];
                $transport_encrypt_method = $dataTrans[0]['encrypt_method'];
                $transport_compress_method = $dataTrans[0]['compress_method'];
            } elseif ($task_type == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER']) {
                $memory_cache_alloc_space = 0;
                $file_cache_alloc_space = 0;
                $file_cache_storage_path = "";
                $transport_compress_flag = 0;
                $transport_encrypt_flag = 0;
                $transport_encrypt_method = 0;
                $transport_compress_method = 0;
            }
            $host_list = $this->getTakeoverHost($failback_target_agent_uuid, $task_uuid, $task_type, $taskMasterUuid);
        }
        $standbyHostInfo = $this->getTaskStandbyHostInfo($task_uuid);
        $standbyAgentuuid = $standbyHostInfo['stand_agent_uuid'];
        $agentInfo = $this->getHostInfo($standbyAgentuuid);
        $osType = $agentInfo['os_type'];
        $list[] = array(
            'memory_cache_alloc_space' => $memory_cache_alloc_space,
            'file_cache_alloc_space' => $file_cache_alloc_space,
            'file_cache_storage_path' => $file_cache_storage_path,
            'transport_compress_flag' => v1_parse_flag_to_bool($transport_compress_flag),
            'transport_encrypt_flag' => v1_parse_flag_to_bool($transport_encrypt_flag),
            'transport_encrypt_method' => $transport_encrypt_method,
            'transport_compress_method' => $transport_compress_method,
            'task_master_uuid' => $taskMasterUuid,
            'failbackup_target_uuid' => $failback_target_agent_uuid,
            'io_replication_mode' => $io_replication_mode,
            'host_list' => $host_list,
            'storage_uuid' => $storage_uuid,
            'node_uuid' => $node_uuid,
            'mirror_backup_flag' => v1_parse_flag_to_bool($mirror_backup_flag),
            'transport_thread_num' => $transport_thread_num,
            'transport_block_size' => $transport_block_size,
            'rebuild_partition_flag' => v1_parse_flag_to_bool($rebuildPartitionFlag),
            'net_model' => $netModel,
            'standby_os_type' => $osType
        );
        return $list;
    }

    /**
     * 得到添加存储的时候的可用节点列表,同时用户备份选择存储节点(新建备份任务,修改备份任务,系统配置等等)
     * @param unknown $params
     */
    public function getStorageNodeSelect($params)
    {
        $failBackTaskStorageUuid = $params['default_storage_uuid'];
        $sql = "select ip, node_uuid, host_name, node_nickname, node_type from bd_node order by node_type";
        $data = $this->dbSelect($sql, array());
        $softwareType = (new \app\v1\system\v0\logic\Index())->getSoftwareType();
        $list = array();
        $resourceList = array();
        //如果是租户内部，获取当前用户拥有所有资源集合
        if (!empty($_SESSION['tenantuuid'])) {
            $resourceInfo = (new Index())->pGetUserAllResource(xphp_get_user_info()['userUuid'], xphp_get_config('resource', 'RESOURCE_TYPE')['NODE']);
            if (!empty($resourceInfo)) {
                foreach ($resourceInfo as $r) {
                    $resourceList[] = $r['resource_uuid'];
                }
            }
        }
        foreach ($data as $d) {
            //如果是租户内部检查是否有该资源
            if (!empty($_SESSION['tenantuuid']) && !in_array($d['node_uuid'], $resourceList))
                continue;

            if (
                $softwareType == xphp_get_config('app', 'SOFTWARE_VERSION')['EN_FREE_EDITION'] || $softwareType == xphp_get_config('app', 'SOFTWARE_VERSION')['STANDARD_EN'] ||
                $softwareType == xphp_get_config('app', 'SOFTWARE_VERSION')['ESSENTIAL_EN']
            ) {
                if (intval($d['node_type']) != xphp_get_config('app', 'FLAG')['SET'])
                    continue;
            }
            $nodeStatus = (new Node())->getNodeAllStatus($d['node_uuid']);
            $isSelected = false;
            if ($failBackTaskStorageUuid != "") {
                $locationNode = $this->storageLocationNode($failBackTaskStorageUuid);
                if ($d['node_uuid'] == $locationNode) {
                    $isSelected = true;
                }
            }
            if ($nodeStatus['flag']) {
                //如果节点状态正常
                $list[] = array(
                    'uuid' => $d['node_uuid'],
                    'text' => $this->getNodeShowName($d['ip'], $d['node_nickname'], $d['host_name']),
                    'type' => intval($d['node_type']),
                    'select' => $isSelected
                );
            }
        }
        return $list;
    }

    /**
     * 获取回切目标主机对应的卷信息
     */
    public function getAgentVolInfo($params)
    {
        $agentuuid = $params['agent_uuid'];
        $volInfo = $this->getHostVolInfoByUUID($agentuuid);
        return $volInfo;
    }

    /**
     * 获取回切目标主机对应的磁盘信息
     */
    public function getAgentDiskInfo($params)
    {
        $agentUuid = $params['agent_uuid'];
        $diskInfo = $this->getHostDiskInfo($agentUuid);
        return $diskInfo;
    }

    /**
     * 接管回切配置
     */
    public function takeOverCutBack($params)
    {
        $pfMSg = array();
        $task_uuid = $params['task_uuid'];
        $pfMSg['task_uuid'] = $task_uuid;
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
        $nodeUuid = (new Node())->getNodeUUIDWithTaskUUID($task_uuid);   //任务UUID对应的存储节点uuid

        $opName = 'VOL_CDP_TASK_OP_TAKEOVER_FAILBACK_CONFIG';
        $mbResult = $this->mbVolCdpMsg($nodeUuid, $opName, $msg);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];

        $operate = (new VolcdpOpcode())->getOpcodeDes($opName);

        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * 创建手动标签并添加描述
     * @param unknown $params
     */
    public function createLablePoint($params)
    {
        $uuid = $params['uuid'];
        $labelDesc = $params['remark'];
        $this->paramsCheck($uuid);
        $sync = FALSE;
        $command = TRUE;
        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($uuid);   //任务UUID对应的存储节点uuid

        $pfMSg = array();
        $task_list = array($uuid);
        $opName = "VOL_CDP_TASK_CREATE_CONSISTENCY_LABEL";  //停止接管启动回切
        $msg = array(
            'task_uuid' => $uuid,
            'label_remarks' => $labelDesc,
            'auto_start_flag' => 0,
            'time_strategy_id' => 0
        );

        $msg = json_encode($msg);
        $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg, $sync, $command);
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        $operate = (new VolcdpOpcode())->getOpcodeDes($opName);
        //返回结果到UI
        if ($result) {
            return $this->muOpResult($result, $operate, $msg);
        } else {
            return $this->muOpResult($result, $operate, $msg, '', $mbResult['errorCode']);
        }
    }

    /**
     * @param $params 获取启动任务启动对象详细信息
     */
    public function getStartTaskObjectInfo($params)
    {
        $startObjectInfo = $this->getStartTaskObjectDetail($params);
        return $startObjectInfo;
    }

    /**
     * 获取卷/应用信息详情
     * @param array $params
     */
    public function getDetailsVolInfo($params)
    {
        $taskType = $params['task_type'];
        $taskUuid = $params['uuid'];
        $sql = "SELECT current_task_running_stage from cdp_vol_task where task_uuid= ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $taskCurrentStage = 0; //任务阶段
        if (!empty($data)) {
            $taskCurrentStage = $data[0]['current_task_running_stage'];
        }
        if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP']) {   //备份
            if ($taskCurrentStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['IN_TAKEOVER'] || $taskCurrentStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['IN_TAKEOVER_STARTING']) { //任务阶段处于接管中
                $taskVolInfo = $this->getTakeoverTaskVolInfo($params);
            } else {
                $taskVolInfo = $this->getBackupTaskVolInfo($params);
            }
        } else if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY']) { //恢复
            $taskVolInfo = $this->getRecoverTaskVolInfo($params);
        } else if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER']) { //接管
            $taskVolInfo = $this->getTakeoverTaskVolInfo($params);
        }
        return $taskVolInfo;
    }

    /**
     * 获取任务配置的内嵌虚拟主机信息
     */
    public function getTaskVmTemplateConfig($params)
    {
        $taskUuid = $params['task_uuid'];
        $sql = "select takeover_vm_config from cdp_vol_task_takeover_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $resultData = array();
        if (!empty($data)) {
            $takeoverVmConfig = $data[0]['takeover_vm_config'];
            $configDetails = json_decode($takeoverVmConfig);

            $interfaces = $configDetails->interfaces;
            $memsStr = $configDetails->mems_str;
            $numCpus = $configDetails->num_cpus;
            $cpuMode = $configDetails->cpu_mode;
            $vmName = $configDetails->vm_name;
            $osConf = $configDetails->os;

            $resultData = array(
                'vm_temp_name' => $vmName,
                'cpu_core' => $numCpus,
                'memory_size' => $memsStr,
                'interfaces' => $interfaces,
                'firmware' => $osConf->firmware,
                'boot_mode' => $osConf->boot_dev,
                'cpu_mode' => $cpuMode,
            );
        }
        return $resultData;
    }

    /**
     * 获取恢复模块卷信息详情
     * @param array $params
     * @return json string
     */
    public function getRecoverTaskVolInfo($params)
    {
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
        foreach ($data as $d) {
            $taskCurrentStage = $d['current_task_running_stage'];
            $recoveryTargetInfo = $this->getBdAgentInfo($d['recovery_target_agent_uuid']); //恢复目标
            $recoveryTargetName = $recoveryTargetInfo['agentInfo'];
            $dataSourceHost = $this->getDataSourceHosstInfo($d['master_agent_uuid']);
            $volUUID = $d['vol_uuid'];
            $timepointStr = $d['recovery_target_timestamp'];
            $dataSourceInfo = $this->getDataSourceVolInfo($volUUID, $timepointStr);               //恢复数据源卷
            $volName = $dataSourceInfo['vol_name'];
            $volDisplayName = $dataSourceInfo['vol_display_name'];
            $capacity = $dataSourceInfo['capacity'];
            $targetVol = $this->getAgentVol($d['recovery_target_vol_uuid']);    //恢复目标卷
            $targetDisk = $this->getRecoverTargetDisk($d['recovery_target_disk_uuid']);  //恢复磁盘
            $targetInfo = $targetVol;

            if ($d['rebuild_partition_flag'] == 1) {
                $targetInfo = $targetDisk;
            }
            $taskRunningStage = $d['current_task_running_stage'];
            $rebuildPartitionFlag = $d['rebuild_partition_flag'];  //重建分区

            $taskRunningCapacityInfo = $this->getTaskRunningCapacityInfo($taskUUID, $taskRunningStage, $volUUID, $taskType, $d['rebuild_partition_flag']);//卷有效数据运行情况
            $completed = $taskRunningCapacityInfo['completed_size'];
            $transferSize = $taskRunningCapacityInfo['transport_size'];
            $writeInSize = $taskRunningCapacityInfo['write_size'];
            $taskValidCapacityInfo = $taskRunningCapacityInfo['volume_valid_info'];
            $volumeInfo = $taskRunningCapacityInfo['volume_info'];
            $capacityInfo = v1_calsize(intval($capacity)) . "/" . $completed;

            $timePointDesc = $this->getTimePointDescription($taskUUID, $timepointStr, $volUUID);
            $records["data"][] = array(
                'dataSourceHost' => $dataSourceHost,
                'volDisplayName' => $volDisplayName,
                'volumeInfo' => $volumeInfo,
                'taskValidCapacityInfo' => $taskValidCapacityInfo,
                'transferSize' => $transferSize,
                'recovery_target_timestamp' => $d['recovery_target_timestamp'],
                'recoveryTargetName' => $recoveryTargetName,
                'targetInfo' => $targetInfo,
                'task_type' => $d['task_type'],
                'task_status' => $d['task_status'],
                'timepointStr' => $timepointStr,
                'timePointDesc' => $timePointDesc,
            );
            $i++;
        }
        $records["total"] = $i;
        return $records;
    }

    /**
     * 获取接管任务对应卷及应用信息
     * @param array $params
     * @return json string
     */
    public function getTakeoverTaskVolInfo($params)
    {
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
        $i = 0;
        $records["data"] = array();
        foreach ($data as $d) {
            $taskCurrentStage = $d['current_task_running_stage'];
            $takeoverStandbyAgentuuid = $d['takeover_standby_agent_uuid'];
            $takeoverTargetInfo = $this->getBdAgentInfo($d['takeover_standby_agent_uuid'], $d['vm_hypervisor_type']); //接管目标机器
            $takeoverTargetName = $takeoverTargetInfo['agentInfo'];
            $dataSourceHost = $this->getDataSourceHosstInfo($d['master_agent_uuid']);
            $volUUID = $d['vol_uuid'];
            $failbackVoluuid = $d['takeover_failback_target_vol_uuid'];
            $standbyTargetVoluuid = $d['standby_target_vol_uuid'];
            $takeoverTimeStr = $d['takeover_timestamp'];
            $dataSourceInfo = $this->getDataSourceVolInfo($volUUID, $takeoverTimeStr); //接管数据源卷
            $volName = $dataSourceInfo['vol_name'];
            $volDisplayName = $dataSourceInfo['vol_display_name'];
            $capacity = $dataSourceInfo['capacity'];
            $taskRunningStage = $d['current_task_running_stage'];


            $takeoverMountPoint = $d['takeover_target_mount_point'];
            $takeoverRealMountPoint = $d['takeover_standby_real_mount_point'];
            $appTakeoverFlag = $d['app_takeover_flag'];

            $takeoverMountPointStr = xphp_get_lang('WEB_VOL_CDP_TAKEOVER_AUTO_ALLOCATE');
            $takeoverRealMountPointStr = "--";
            if ($takeoverRealMountPoint) {
                $takeoverRealMountPointStr = $takeoverRealMountPoint;
            }
            if ($takeoverMountPoint) {
                $takeoverMountPointStr = $takeoverMountPoint;
            }
            $rebuildPartitionFlag = $d['rebuild_partition_flag'];
            $taskRunningCapacityInfo = $this->getTaskRunningCapacityInfo($taskUUID, $taskRunningStage, $volUUID, $taskType, $rebuildPartitionFlag);//卷有效数据运行情况
            $completed = $taskRunningCapacityInfo['completed_size'];
            $transferSize = $taskRunningCapacityInfo['transport_size'];
            $writeInSize = $taskRunningCapacityInfo['write_size'];
            $taskValidCapacityInfo = $taskRunningCapacityInfo['volume_valid_info'];
            $volumeInfo = $taskRunningCapacityInfo['volume_info'];

            $capacityStr = v1_calsize($capacity, true);
            if ($completed == xphp_get_config('app', 'NULLSPACE')) {
                $completed = "0B";
            }
            $capacityInfo = $capacityStr;
            $timePointDesc = $this->getTimePointDescription($taskUUID, $takeoverTimeStr, $volUUID);
            $failbackInfo = array();
            if ($appTakeoverFlag == xphp_get_config('app', 'FLAG')['SET']) {
                if (
                    $taskCurrentStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
                    || $taskCurrentStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
                    || $taskCurrentStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']
                ) { //任务处于回切阶段

                    $failbackInfo = $this->getTaskFailbackHostInfo($taskUUID, $volUUID);
                    $taskAppTree = $this->getFailbackAppTree($taskUUID, $standbyTargetVoluuid, $takeoverStandbyAgentuuid);
                } else {
                    $taskAppTree = $this->getTakeoverAppTree($volUUID, $d['master_agent_uuid'], $takeoverTimeStr, $d['takeover_standby_agent_uuid'], $taskRunningStage, $taskUUID);
                }

                if ($taskAppTree) {
                    $configApp = xphp_get_lang('WEB_DRILLS_YES');
                } else {
                    $configApp = xphp_get_lang('WEB_DRILLS_NO');
                }
            } else {
                $taskAppTree = array();
                $configApp = xphp_get_lang('WEB_DRILLS_NO');
            }

            $taskRunningCapacityInfo = $this->getTaskRunningCapacityInfo($taskUUID, $taskRunningStage, $volUUID, $taskType, $rebuildPartitionFlag);//卷有效数据运行情况
            $completed = $taskRunningCapacityInfo['completed_size'];
            if ($completed == xphp_get_config('app', 'NULLSPACE')) {
                $completed = "0B";
            }
            $transferSize = $taskRunningCapacityInfo['transport_size'];
            $writeInSize = $taskRunningCapacityInfo['write_size'];
            $taskValidCapacityInfo = $taskRunningCapacityInfo['volume_valid_info'];
            $volumeInfo = $taskRunningCapacityInfo['volume_info'];

            $tab5 = $takeoverTimeStr;  //表格第5列数据，在接管状态获取接管时间
            $tab6 = $configApp;     //表格第6列数据，在接管状态获取是否配置应用

            if (
                $taskCurrentStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
                || $taskCurrentStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
                || $taskCurrentStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']
            ) { //任务处于回切阶段
                $volCapacityInfo = $volumeInfo;
                $volValidCapacityInfo = $taskValidCapacityInfo;
                $tab5 = $transferSize;  //表格第5列数据，在回切状态下获取传输字节大小
                $tab6 = $writeInSize;  //表格第6列数据，在回切状态下获取写入字节大小
            } else {
                $volCapacityInfo = $capacityInfo;
                $volValidCapacityInfo = $taskValidCapacityInfo;
            }
            $records["data"][] = array(
                'dataSourceHost' => $dataSourceHost,
                'volDisplayName' => $volDisplayName,
                'volCapacityInfo' => $volCapacityInfo,
                'volValidCapacityInfo' => $volValidCapacityInfo,
                'transferSize' => $tab5,
                'writeInSize' => $tab6,
                'takeoverTargetName' => $takeoverTargetName,
                'takeoverMountPointStr' => $takeoverMountPointStr . " / " . $takeoverRealMountPointStr,
                'task_type' => $d['task_type'],
                'task_status' => $d['task_status'], //10
                'taskAppTree' => $taskAppTree,
                'vol_uuid' => $d['vol_uuid'],
                'capacityStr' => $capacityStr,
                'takeoverTimeStr' => $takeoverTimeStr,
                'takeover_failback_target_vol_uuid' => $d['takeover_failback_target_vol_uuid'],  //15回切目标卷，未配置为空
                'timePointDesc' => $timePointDesc,
                'capacity' => $capacity,
                'recovery_target_disk_uuid' => $d['recovery_target_disk_uuid'],  // 18 回切目标磁盘UUID，LiveCD开启配置重建分区开启才有
                'taskCurrentStage' => $taskCurrentStage,
                'failbackInfo' => $failbackInfo,
            );
            $i++;
        }
        $records["total"] = $i;
        return $records;
    }

    /**
     * 获取备份模块卷/应用信息详情
     * @param array $params
     * @return json string
     */
    public function getBackupTaskVolInfo($params)
    {
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
        foreach ($data as $d) {
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

            $taskRunningCapacityInfo = $this->getTaskRunningCapacityInfo($taskUUID, $taskRunningStage, $volUUID, $taskType, $rebuildPartitionFlag);//卷有效数据运行情况
            $completed = $taskRunningCapacityInfo['completed_size'];
            if ($completed == xphp_get_config('app', 'NULLSPACE')) {
                $completed = "0B";
            }
            $transferSize = $taskRunningCapacityInfo['transport_size'];
            $writeInSize = $taskRunningCapacityInfo['write_size'];
            $taskValidCapacityInfo = $taskRunningCapacityInfo['volume_valid_info'];
            $volumeInfo = $taskRunningCapacityInfo['volume_info'];
            $capacity = $volInfo['capacity'];
            $capacityStr = v1_calsize($capacity, true);
            $capacityInfo = $capacityStr . "/" . $completed;
            $taskType = $d['task_type'];

            $autoTakeoverFlag = $d['auto_takeover_flag'];
            $mirrorBackupFlag = $d['mirror_backup_flag'];

            if ($autoTakeoverFlag == xphp_get_config('app', 'FLAG')['SET'] && $rebuildPartitionFlag == xphp_get_config('app', 'FLAG')['UNSET']) {  //已配置自动接管
                $autoTakeoverInfo = $this->getAutoTakeoverVolInfo($taskUUID, $volUUID);
                $standbyAgent = $autoTakeoverInfo['standby_agent'];

                $standbyVolInfo = $autoTakeoverInfo['mount_point'];
                $standbyRealMountPoint = $autoTakeoverInfo['$autoTakeoverInfo'];
                $standbyTargetVoluuid = $autoTakeoverInfo['standby_target_vol_uuid'];
                if ($standbyVolInfo == "") {
                    $standbyVolInfo = xphp_get_lang('WEB_VOL_CDP_TAKEOVER_AUTO_ALLOCATE');
                }
            }
            $failbackInfo = array();
            if (
                $taskCurrentStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC'] ||
                $taskCurrentStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC'] ||
                $taskCurrentStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']
            ) { //任务处于回切阶段
                $failbackInfo = $this->getTaskFailbackHostInfo($taskUUID, $volUUID);
                $failbackTargetHostuuid = $failbackInfo['target_host_uuid'];  //回切目标主机uuid
                $standbyAgentInfo = $this->getBdAgentInfo($takeoverStandbyAgentuuid);  //暂时展示接管备机的客户端信息
                $standbyAgent = $standbyAgentInfo['agentInfo'];
                $taskAppInfo = $this->getFailbackAppTree($taskUUID, $takeoverFailbackTargetVoluuid, $failbackTargetHostuuid);

            } else {
                $taskAppInfo = $this->getBackuptaskHostAppTree($taskUUID, $volUUID);
            }

            $records["data"][] = array(
                'masterAgentName' => $masterAgentName,
                'volDisplayName' => $volDisplayName,
                'volumeInfo' => $volumeInfo,
                'taskValidCapacityInfo' => $taskValidCapacityInfo,  //卷有效数据运行情况
                'transferSize' => $transferSize,
                'writeInSize' => $writeInSize,
                'standbyAgent' => $standbyAgent,
                'standbyVolInfo' => $standbyVolInfo,
                'task_type' => $d['task_type'],
                'task_status' => $d['task_status'], //10
                'taskAppInfo' => $taskAppInfo,
                'vol_uuid' => $d['vol_uuid'],
                'capacityStr' => $capacityStr,
                'takeover_failback_target_vol_uuid' => $d['takeover_failback_target_vol_uuid'],  //回切目标卷UUID，未配置为空 14
                'capacity' => $capacity,
                'recovery_target_disk_uuid' => $d['recovery_target_disk_uuid'],  // 18 回切目标磁盘UUID，LiveCD开启配置重建分区开启才有
                'taskstandbyTargetVoluuid' => $taskstandbyTargetVoluuid,
                'taskCurrentStage' => $taskCurrentStage,
                'failbackInfo' => $failbackInfo
            );
            $i++;
        }
        $records["total"] = $i;
        return $records;
    }

    /**
     * 获取客户端对应卷对应的应用信息
     * @param unknown $taskUUID
     * @param unknown $voluuid
     * @return array
     */
    private function getBackuptaskHostAppTree($taskuuid, $voluuid)
    {
        $sql = "select DISTINCT agent_app.app_type,agent_app.app_name,agent_app.alias_name,agent_app.app_uuid 
                from bd_agent_app as agent_app,cdp_vol_task_takeover_app as task_app 
                where task_app.app_uuid = agent_app.app_uuid and task_app.task_uuid ='{$taskuuid}'";
        $data = $this->dbSelect($sql);
        $appExist = false;
        $app_tree = array();
        if (count($data) > 0) {
            foreach ($data as $appInfo) {
                $alaisName = $appInfo['alias_name'];
                $appTypeValue = $appInfo['app_type'];
                $appTypeDes = xphp_get_config('db', 'DB_TYPE_DES')[intval($appTypeValue)] . PHP_EOL;
                if ($appTypeValue == xphp_get_config('db', 'DB_TYPE')['SQLSERVER']) {
                    $icon = "./img/db/sqlserver.png";
                } else if ($appTypeValue == xphp_get_config('db', 'DB_TYPE')['ORACLE']) {
                    $icon = "./img/db/oracle.png";
                } else if ($appTypeValue == xphp_get_config('db', 'DB_TYPE')['MYSQL']) {
                    $icon = "./img/db/mysql.png";
                }
                $appUUID = $appInfo['app_uuid'];
                $id = $appUUID . $appTypeValue;
                $appTree[] = array(
                    "id" => $id,
                    "pId" => "",
                    "name" => $appTypeDes,
                    "title" => $appTypeDes,
                    "appTypeValue" => $appTypeValue,
                    "icon" => $icon,
                    "isApp" => false,
                    "appUuid" => $appUUID,
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" => true,
                );
                $appData = $this->getAppData($id, $appUUID, $appTypeValue, $voluuid);
                $childTree = $appData[0];
                $appModuleExist = $appData[1];
                if ($appModuleExist) {
                    $appTree = array_merge($appTree, $childTree);
                    $appExist = true;
                }
            }
        }
        if ($appExist) {
            $appDataInfo = $appTree;
        } else {
            $appDataInfo = array();
        }

        return $appDataInfo;
    }

    /**
     *  获取应用类型下的应用信息
     */
    private function getAppData($pId, $appUUID, $appTypeValue, $voluuid)
    {
        $sql = "select app_name,app_username,app_password,app_type,app_uuid,online_flag from bd_agent_app
        where app_uuid = '{$appUUID}' group by app_uuid";
        $data = $this->dbSelect($sql);
        $info = array();
        $appModuleExist = false;
        if (!empty($data)) {
            foreach ($data as $d) {
                $id = $pId . $d['app_uuid'];
                $info[] = array(
                    "id" => $id,
                    "pId" => $pId,
                    "name" => $d['app_name'],
                    "title" => $d['app_name'],
                    "uuid" => $d['app_uuid'],
                    "app_type" => $d['app_type'],
                    "icon" => "./img/db/instance.png",
                    "clickshow" => false,
                    "checked" => false,
                    "open" => false,
                    "isApp" => true,
                    "chkDisabled" => false,
                    "app_username" => $d['app_username'],
                    "app_password" => $d['app_password']
                );
                $childTree = $this->getAppModuleInfo($id, $d['app_uuid'], $appTypeValue, $voluuid);
                if (!empty($childTree)) {
                    $info = array_merge($info, $childTree);
                    $appModuleExist = true;
                }
            }
        }
        return array($info, $appModuleExist);
    }

    /**
     * 获取app 组件信息
     */
    private function getAppModuleInfo($pId, $appUUID, $appTypeValue, $voluuid)
    {
        $sql = "SELECT id,app_uuid,module_name,module_detail FROM cdp_vol_app_module WHERE app_uuid = '{$appUUID}'";
        $data = $this->dbSelect($sql);
        $moduleInfo = array();
        if (!empty($data)) {
            foreach ($data as $d) {
                $id = $pId . $d['module_name'];
                $moduleDetail = json_decode($d['module_detail']);

                $moduleLogFile = $moduleDetail->module_log_file;
                $moduleConfigFile = $moduleDetail->module_config_file;
                $moduleFileInfo = $moduleDetail->module_file_info;
                //判断组件信息是否存放指定卷中
                $isMatch = $this->getModuleVolinfo($moduleLogFile, $moduleConfigFile, $moduleFileInfo, $voluuid);
                $moduleArray = array(
                    "id" => $id . "123",
                    "pId" => $pId,
                    "name" => $d['module_name'],
                    "title" => $d['module_name'],
                    "uuid" => $d['app_uuid'],
                    "module_name" => $d['module_name'],
                    "icon" => "./img/db/database.png",
                    "app_type_value" => $appTypeValue,
                    "clickshow" => false,
                    "checked" => false,
                    "open" => false,
                    "isApp" => false,
                    "chkDisabled" => false,
                    'ismatch' => $isMatch
                );
                if ($isMatch) {
                    $moduleInfo[] = $moduleArray;
                }
            }
        }
        return $moduleInfo;
    }

    /**
     * 获取自动接管配置的备机及挂载点信息
     * @param unknown $taskuuid
     */
    private function getAutoTakeoverVolInfo($taskuuid, $voluuid)
    {
        $sql = "select ti.takeover_standby_agent_uuid,vol.takeover_target_mount_point,vol.standby_target_vol_uuid 
            from cdp_vol_task_vol vol,cdp_vol_task_takeover_info ti 
            where ti.task_uuid = vol.task_uuid and ti.task_uuid = ? and vol.vol_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid, $voluuid));
        $standbyAgentUuid = $data[0]['takeover_standby_agent_uuid'];
        $standbyAgent = $this->getHostInfo($standbyAgentUuid);
        $standbyTargetVoluuid = $data[0]['standby_target_vol_uuid'];
        $takeoverStandbyRealMountPoint = $data[0]['takeover_standby_real_mount_point'];
        $dataArray = array(
            'standby_agent' => $standbyAgent,
            'mount_point' => $data[0]['takeover_target_mount_point'],
            'standby_target_vol_uuid' => $standbyTargetVoluuid,
            'standby_real_mount_point' => $takeoverStandbyRealMountPoint
        );
        return $dataArray;
    }

    /**
     * 通过卷uuid获取磁盘卷基本信息
     * @param string vol_uuid
     */
    private function getVolInfo($vol_uuid)
    {
        if ($vol_uuid) {
            $sql = "SELECT capacity,free_space,display_name from bd_agent_vol where vol_uuid =? ";
            $data = $this->dbSelect($sql, array($vol_uuid));
            $capacity = $data[0]['capacity'];
            $freeSpace = $data[0]['free_space'];
            $displayName = $data[0]['display_name'];
            $volList = array(
                "vol_name" => $displayName,
                "capacity" => $capacity
            );
        } else {
            $volList = array(
                "vol_name" => "--",
                "capacity" => 0
            );
        }
        return $volList;
    }

    /**
     * 获取当前任务配置的应用信息
     * @param unknown $params
     */
    private function getTakeoverAppTree($voluuid, $masterAgentuuid, $takeoverTimeStr, $standbyHostuuid, $taskRunningStage, $taskUUID)
    {

        $sql = "SELECT agent.master_agent_detail,agent.id,agent.master_agent_uuid
                from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol 
                where agent.master_agent_uuid = ? and agent.id = vol.backup_agent_id 
                  and (UNIX_TIMESTAMP(vol.start_timestamp) <= UNIX_TIMESTAMP(?)) 
                  and  (UNIX_TIMESTAMP(vol.end_timestamp) >= UNIX_TIMESTAMP(?))";
        $data = $this->dbSelect($sql, array($masterAgentuuid, $takeoverTimeStr, $takeoverTimeStr));
        $host_info = "";
        $agentIdList = [];
        $os_type = "";
        $appExist = false;
        if (!empty($data)) {
            foreach ($data as $row) {
                $master_agent_detail = json_decode($row['master_agent_detail']);
                $hostname = $master_agent_detail->hostname;
                $agent_ip = $master_agent_detail->agent_ip;
                $os_type = $master_agent_detail->os_type;
                $host_info = $hostname . "(" . $agent_ip . ")";
                array_push($agentIdList, $row['id']);
            }
            $type_icon = "./img/platform/linux.png";
            if ($os_type == 'Windows') {
                $type_icon = "./img/platform/windows.png";
            }
            $agent_uuid = $row['master_agent_uuid'];
            $tree[] = array(
                "id" => $agent_uuid,
                "pId" => "",
                "name" => $hostname,
                "title" => $agent_ip,
                "isParent" => true,
                "uuid" => $agent_uuid,
                "nocheck" => true,
                "isApp" => false,
                "type" => 1,
                "icon" => $type_icon,
                "clickshow" => false,
                "checked" => false,
                "chkDisabled" => false,
                "open" => true,
            );

            $appTypeData = $this->getTakeoverAppType($masterAgentuuid, $tree, $agentIdList, $takeoverTimeStr, $standbyHostuuid, $voluuid, $taskRunningStage, $taskUUID);//应用类型
            $childTree = $appTypeData[0];
            $appTypeExist = $appTypeData[1];
            if ($appTypeExist) {
                $appTree = $childTree;
                $appExist = true;
            }
        }
        if ($appExist) {
            $appDataInfo = $appTree;
        } else {
            $appDataInfo = array();
        }

        return $appDataInfo;
    }

    /**
     * 获取接管应用类型
     */
    private function getTakeoverAppType($agentuuid, $appTree, $agentIdList, $takeoverTimeStr, $standbyHostuuid, $voluuid, $taskRunningStage, $taskUUID)
    {
        $id_str = join(",", $agentIdList);
        $sql = "SELECT DISTINCT id,app_type,app_name,app_uuid FROM cdp_vol_backup_app where backup_agent_id in ({$id_str}) GROUP BY app_type";
        $data = $this->dbSelect($sql);
        $appExist = false;
        if (count($data) > 0) {
            foreach ($data as $appInfo) {
                $alaisName = $appInfo['app_name'];
                $appTypeValue = $appInfo['app_type'];
                $appUuid = $appInfo['app_uuid'];
                $appTypeDes = xphp_get_config('db', 'DB_TYPE_DES')[intval($appTypeValue)] . PHP_EOL;
                if ($appTypeValue == xphp_get_config('db', 'DB_TYPE')['SQLSERVER']) {
                    $icon = "./img/db/sqlserver.png";
                } else if ($appTypeValue == xphp_get_config('db', 'DB_TYPE')['ORACLE']) {
                    $icon = "./img/db/oracle.png";
                } else if ($appTypeValue == xphp_get_config('db', 'DB_TYPE')['MYSQL']) {
                    $icon = "./img/db/mysql.png";
                }
                $id = $agentuuid . $appTypeValue;
                $appTree[] = array(
                    "id" => $id,
                    "pId" => $agentuuid,
                    "name" => $appTypeDes,
                    "title" => xphp_get_lang('UI_VOL_CDP_BACKUP_DB_APPLICATION'),
                    "appTypeValue" => $appTypeValue,
                    'agent_id_list' => $agentIdList,
                    "icon" => $icon,
                    "isApp" => false,
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" => true,
                );
                $appData = $this->getTakeoverAppData($agentuuid, $id, $appTypeValue, $agentIdList, $takeoverTimeStr, $standbyHostuuid, $voluuid);
                $childTree = $appData[0];
                $appModuleExist = $appData[1];
                if ($appModuleExist) {
                    $appTree = array_merge($appTree, $childTree);
                    $appExist = true;
                }
            }
        }
        return array($appTree, $appExist);
    }

    /**
     * 获取接管应用信息
     * @return array|unknown[][]|string[][]|boolean[][]|fetchAll()[][]
     */
    private function getTakeoverAppData($agentUUID, $pId, $appTypeValue, $agentIdList, $takeoverTimeStr, $standbyHostuuid, $voluuid)
    {
        $id_str = join(",", $agentIdList);
        $sql = "SELECT DISTINCT id,app_type,app_name,app_uuid
                FROM cdp_vol_backup_app
                WHERE backup_agent_id in ({$id_str}) and app_type = ? GROUP BY app_uuid,app_name";
        $data = $this->dbSelect($sql, array($appTypeValue));
        $info = array();
        $appModuleExist = false;
        foreach ($data as $d) {
            $id = $pId . $d['app_uuid'];
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
                'appUUID' => $d['app_uuid'],
                "clickshow" => false,
                "checked" => false,
                "isApp" => true,
                "chkDisabled" => false,
            );
            $childTree = $this->getTakeoverAppModuleInfo($id, $d['app_uuid'], $agentIdList, $takeoverTimeStr, $voluuid, $appTypeValue);
            if (!empty($childTree)) {
                $info = array_merge($info, $childTree);
                $appModuleExist = true;
            }
        }
        return array($info, $appModuleExist);
    }

    /**
     * 获取接管app 组件信息
     */
    private function getTakeoverAppModuleInfo($pId, $appUUID, $agent_id_list, $takeoverTimeStr, $voluuid, $appTypeValue)
    {
        $sql = "SELECT DISTINCT module.id,module.module_name,module.module_detail
               FROM cdp_vol_backup_app_module module,cdp_vol_backup_app app
               WHERE module.backup_app_id = app.id and app.app_uuid = ? and
                     (unix_timestamp(module.start_timestamp) <= ? or unix_timestamp(module.start_timestamp) >=?)
                    GROUP BY module_name";
        $data = $this->dbSelect($sql, array($appUUID, $takeoverTimeStr, $takeoverTimeStr));
        if (!empty($data)) {
            $moduleInfo = array();
            foreach ($data as $d) {
                $moduleDetail = json_decode($d['module_detail']);

                $moduleLogFile = $moduleDetail->module_log_file;
                $moduleConfigFile = $moduleDetail->module_config_file;
                $moduleFileInfo = $moduleDetail->module_file_info;

                $isMatch = $this->getModuleVolinfo($moduleLogFile, $moduleConfigFile, $moduleFileInfo, $voluuid);

                $id = $pId . $d['id'];
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
                if ($isMatch) {
                    $moduleInfo[] = $moduleArray;
                }
            }
        }
        return $moduleInfo;
    }

    /**
     * 获取回切目标主机应用信息,回切过程中显示接管备机的应用信息
     * @param $taskRunningStage
     * @param $taskUUID
     */
    private function getFailbackAppTree($taskUUID, $volUUID, $agentuuid)
    {
        $failbackHostuuid = $this->getFailBackupAgentInfo($taskUUID);
        $sql = "select DISTINCT app_type,alias_name from bd_agent_app where agent_uuid = '{$failbackHostuuid}' GROUP BY app_type";
        $data = $this->dbSelect($sql);
        $appTree = array();
        if (count($data) > 0) {
            foreach ($data as $appInfo) {
                $alaisName = $appInfo['alias_name'];
                $appTypeValue = $appInfo['app_type'];
                $appTypeDes = xphp_get_desc('Db', 'DB_TYPE_DES')[intval($appTypeValue)] . PHP_EOL;
                if ($appTypeValue == xphp_get_config('db', 'DB_TYPE')['SQLSERVER']) {
                    $icon = "./img/db/sqlserver.png";
                } else if ($appTypeValue == xphp_get_config('db', 'DB_TYPE')['ORACLE']) {
                    $icon = "./img/db/oracle.png";
                } else if ($appTypeValue == xphp_get_config('db', 'DB_TYPE')['MYSQL']) {
                    $icon = "./img/db/mysql.png";
                }
                $id = $failbackHostuuid . $appTypeValue;
                $appTree[] = array(
                    "id" => $id,
                    "pId" => $failbackHostuuid,
                    "name" => $appTypeDes,
                    "title" => xphp_get_lang('UI_VOL_CDP_BACKUP_DB_APPLICATION'),
                    "appTypeValue" => $appTypeValue,
                    "icon" => $icon,
                    "isApp" => false,
                    "clickshow" => false,
                    "checked" => false,
                    "chkDisabled" => false,
                    "open" => true,
                    "module_vol_info" => array()
                );

                $childTree = $this->getFailbackAppData($agentuuid, $id, $appTypeValue, $volUUID);
                if (!empty($childTree)) {
                    $appTree = array_merge($appTree, $childTree);
                } else {
                    $appTree = array();
                }
            }
        }
        return $appTree;

    }

    /**
     * 获取应用类型下的应用信息，此处需要注意节点对应关系
     */
    private function getFailbackAppData($agentUUID, $pId, $appTypeValue, $volUUID)
    {
        $sql = "select app_name,app_username,app_password,app_type,app_uuid,online_flag from bd_agent_app 
                where agent_uuid = ? and app_type = ? group by app_uuid";
        $data = $this->dbSelect($sql, array($agentUUID, $appTypeValue));
        $info = array();
        foreach ($data as $d) {
            $id = $pId . $d['app_uuid'];
            $chkDisabled = false;
            $nodeName = $d['app_name'];
            $nodeTitle = $d['app_name'];
            if ($d['online_flag'] == xphp_get_config('app', 'FLAG')['UNSET']) {
                $chkDisabled = true;
                $nodeName = $d['app_name'] . "(" . xphp_get_lang('WEB_AGENT_STATUS_OFFLINE') . ")";
                $nodeTitle = $d['app_name'] . xphp_get_lang('UI_VOL_CDP_BACKUP_APPLICATION_OFFLINE');
            }

            $info[] = array(
                "id" => $id,
                "pId" => $pId,
                "name" => $nodeName,
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
                'vol_uuid' => $volUUID
            );
            $childTree = $this->getFailbackAppModuleInfo($id, $d['app_uuid'], $appTypeValue, $chkDisabled, $volUUID);
            if (!empty($childTree)) {
                $info = array_merge($info, $childTree);
            } else {
                $info = array();
            }
        }
        return $info;
    }

    /**
     * 获取app 组件信息
     */
    private function getFailbackAppModuleInfo($pId, $appUUID, $appTypeValue, $parentNodeOnlineFlag, $volUUID)
    {
        $sql = "SELECT id,app_uuid,module_name,module_detail FROM cdp_vol_app_module WHERE app_uuid = ?";
        $data = $this->dbSelect($sql, array($appUUID));
        $moduleInfo = array();
        foreach ($data as $d) {

            $id = $pId . $d['id'];
            $moduleDetail = json_decode($d['module_detail']);

            $module_log_file = $moduleDetail->module_log_file;
            $module_config_file = $moduleDetail->module_config_file;
            $module_file_info = $moduleDetail->module_file_info;

            $isMatch = $this->getModuleVolinfo($module_log_file, $module_config_file, $module_file_info, $volUUID);
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
                "isApp" => false,
                "nocheck" => true,
                "chkDisabled" => true,
                'ismatch' => $isMatch,
            );
            if ($isMatch) {
                $moduleInfo[] = $moduleArray;
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
    private function getModuleVolinfo($obj1, $obj2, $obj3, $agentVoluuid)
    {
        $volInfo = array();
        $objArry = array();
        $objArry[] = $obj1;
        $objArry[] = $obj2;
        $objArry[] = $obj3;
        $inVolume = false;
        if ($objArry != null) {
            for ($i = 0; $i < count($objArry); $i++) {
                $list = $objArry[$i];
                if ($list == null) {
                    continue;
                }
                for ($n = 0; $n < count($list); $n++) {
                    $mountPath = $list[$n]->mount_path;
                    $volUuid = $list[$n]->vol_uuid;

                    if ($agentVoluuid == $volUuid) {
                        $inVolume = true;
                        break;
                    }
                }
            }
        }
        return $inVolume;
    }

    /**
     * 获取备份任务回切目标机及卷信息
     * @param unknown $taskuuid
     */
    private function getTaskFailbackHostInfo($taskuuid, $voluuid)
    {
        $sql = "select  vol.takeover_failback_target_vol_uuid,failback.failback_target_agent_uuid,vol.recovery_target_disk_uuid
                from cdp_vol_task_vol vol,cdp_vol_task_takeover_failback_info failback 
                where  failback.task_uuid = vol.task_uuid and failback.task_uuid = ? and vol.vol_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid, $voluuid));
        $failbackInfo = array();
        if (!empty($data)) {
            $failbackTargetAgentuuid = $data[0]['failback_target_agent_uuid'];
            $takeoverFailbackTargetVoluuid = $data[0]['takeover_failback_target_vol_uuid'];
            $recoveryTargetDiskuuid = $data[0]['recovery_target_disk_uuid'];

            $targetVol = $this->getAgentVol($takeoverFailbackTargetVoluuid);
            $agentInfo = $this->getBdAgentInfo($failbackTargetAgentuuid);
            $targetDisk = $this->getRecoverTargetDisk($recoveryTargetDiskuuid);
            $agentName = $agentInfo['agentInfo'];
            ;
            $failbackInfo = array(
                'agent_info' => $agentName,
                'target_vol' => $targetVol,
                'target_disk' => $targetDisk,
                'target_host_uuid' => $failbackTargetAgentuuid,
            );
        } else {
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
     * 通过恢复目标磁盘UUID获取目标磁盘信息
     */
    private function getRecoverTargetDisk($disk_uuid)
    {
        $sql = "select display_name from bd_agent_disk where disk_uuid = ?";
        $data = $this->dbSelect($sql, array($disk_uuid));
        $recoverTargetDisk = "";
        if (!empty($data)) {
            $recoverTargetDisk = $data[0]['display_name'];
        }
        return $recoverTargetDisk;
    }

    /**
     * 获取恢复时间点描述信息，分别解析标签点备注和事件信息
     * @param string $taskuuid
     * @param string $timepointStr
     * @param string $voluuid
     * @return string timepointStr
     */
    private function getTimePointDescription($taskuuid, $timepointStr, $voluuid)
    {
        $sql = "SELECT vol_set.backup_agent_id 
                from cdp_vol_task_vol vol,cdp_vol_backup_vol_set as vol_set 
                where vol.task_uuid = ? 
                    and vol.vol_uuid=? 
                    and vol.recovery_target_timestamp = ? 
                    and vol.vol_uuid = vol_set.vol_uuid 
                    and unix_timestamp(vol.recovery_target_timestamp) 
                    BETWEEN unix_timestamp(vol_set.start_timestamp) AND unix_timestamp(vol_set.end_timestamp)";
        $data = $this->dbSelect($sql, array($taskuuid, $voluuid, $timepointStr));
        $timepointDesc = xphp_get_lang('UI_VOL_CDP_ANY_TIME_POINT');
        if (!empty($data)) {
            $backupAgentId = $data[0]['backup_agent_id'];
            $labelSql = "select remarks from  cdp_vol_agent_label_set where backup_agent_id = ? and label_timestamp = ?";
            $labelData = $this->dbSelect($labelSql, array($backupAgentId, $timepointStr));
            $labelTimePointStr = '';
            if (!empty($labelData)) {
                $labelTimePointStr = xphp_get_lang('UI_VOL_CDP_LABEL_POINTS') . "：" . $labelData[0]['remarks'];
            }
            $eventSql = "select description_key,description_param from cdp_vol_agent_event_info where backup_agent_id = ? and event_time = ?";
            $eventData = $this->dbSelect($eventSql, array($backupAgentId, $timepointStr));
            $eventInfoStr = '';
            if (!empty($eventData)) {
                $eventInfoStr = xphp_get_lang('UI_VOL_CDP_EVENT_INFO') . "，" . (new VolcdpRecover())->getEventDesriptionNotice($eventData[0]['description_key'], $eventData[0]['description_param']);
            }
            if (!empty($labelData) || !empty($eventData)) {
                $timepointDesc = $labelTimePointStr . "<br>" . $eventInfoStr;
            }

        }
        return $timepointDesc;
    }

    /**
     * 获取任务在各个运行阶段下的容量执行信息
     * @param unknown $taskUUID
     * @param unknown $taskRunningStage
     */
    private function getTaskRunningCapacityInfo($taskUUID, $taskRunningStage, $volUUID, $taskType, $rebuildPartFlag)
    {
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
        $data = $this->dbSelect($sql, array($taskUUID, $volUUID));
        if (!empty($data)) {
            //任务阶段处于 逆向初始同步,回切,初始同步
            switch ($taskType) {
                case xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP']:
                    if (
                        $taskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
                        || $taskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']
                        || $taskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['INIT_SYNC']
                    ) {
                        $totalSize = $data[0]['total_size'];
                        $totalSizeStr = v1_calsize($totalSize, true);

                        $validSize = $data[0]['valid_size'];
                        $validSizeStr = v1_calsize($validSize, true);
                        $completedValidSize = $data[0]['completed_valid_size'];
                        $completedValidFlag = true;
                        if ($completedValidSize == 0 || $validSize == 0) {
                            $initialSynCapacity = 0;
                        } else {
                            $initialSynCapacity = $data[0]['total_size'] * ($completedValidSize / $validSize);//初始同步容量采用的总容量*有效数据百分比来获取，确保进度平滑
                        }
                        $completedValidStr = v1_calsize($completedValidSize, $this->verifyValueFalg($completedValidSize));
                        if ($completedValidStr == xphp_get_config('app')['NULLSPACE']) {
                            $completedValidStr = "0B";
                        }
                        $volumeValidDataInfo = $validSizeStr . "/" . $completedValidStr;

                        $completedValidSizeStr = v1_calsize($initialSynCapacity, $this->verifyValueFalg($data[0]['completed_valid_size']));
                        if ($completedValidSizeStr == xphp_get_config('app', 'NULLSPACE')) {
                            $completedValidSizeStr = "0B";
                        }
                        $volumeInfo = $totalSizeStr . " / " . $completedValidSizeStr;

                        $taskRunningCapacityInfo = array(
                            'completed_size' => v1_calsize($data[0]['completed_size'], $this->verifyValueFalg($data[0]['completed_size'])),
                            'volume_valid_info' => $volumeValidDataInfo,
                            'write_size' => v1_calsize($data[0]['write_size'], $this->verifyValueFalg($data[0]['write_size'])),
                            'transport_size' => v1_calsize($data[0]['transport_size'], $this->verifyValueFalg($data[0]['transport_size'])),
                            'volume_info' => $volumeInfo
                        );
                    } else if (
                        $taskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['REALTIME_SYNC']
                        || $taskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['WAIT_CONVERT_TO_REALTIME_SYNC']
                        || $taskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['SERVER_CONS_CHECK']
                        || $taskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
                    ) { //任务阶段处于实时同或数据一致性校验阶段，回切实时同步阶段
                        $totalSize = $data[0]['total_size'];
                        $validSize = $data[0]['valid_size'];

                        $completedValidSize = $data[0]['completed_valid_size'];

                        $completedSize = $data[0]['completed_size'];

                        $writeSize = $data[0]['write_size'];
                        $transportSize = $data[0]['transport_size'];
                        $totalSizeStr = v1_calsize($totalSize, $this->verifyValueFalg($totalSize));
                        $validSizeStr = v1_calsize($validSize, $this->verifyValueFalg($validSize));
                        if ($validSizeStr == xphp_get_config('app', 'NULLSPACE')) {
                            $validSizeStr = "0B";
                        }
                        if ($totalSizeStr == xphp_get_config('app', 'NULLSPACE')) {
                            $totalSizeStr = "0B";
                        }
                        $completedValidStr = v1_calsize($completedValidSize, $this->verifyValueFalg($completedValidSize));
                        $volumeInfo = $totalSizeStr . " / " . $validSizeStr;

                        $taskRunningCapacityInfo = array(
                            'completed_size' => v1_calsize($completedSize, $this->verifyValueFalg($completedSize)),
                            'volume_valid_info' => $completedValidStr,
                            'write_size' => v1_calsize($writeSize, $this->verifyValueFalg($writeSize)),
                            'transport_size' => v1_calsize($transportSize, $this->verifyValueFalg($transportSize)),
                            'volume_info' => $volumeInfo
                        );
                    }
                    break;
                case xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY']:
                    $totalSize = $data[0]['total_size'];
                    $totalSizeStr = v1_calsize(intval($totalSize), $this->verifyValueFalg($totalSize));

                    $validSize = $data[0]['valid_size'];
                    $validSizeStr = v1_calsize($validSize, $this->verifyValueFalg($validSize));
                    $completedSize = $data[0]['completed_size'];

                    $completedValidSize = $data[0]['completed_valid_size'];
                    if ($completedValidSize == 0 || $validSize == 0) {
                        $initialSynCapacity = 0;
                    } else {
                        $initialSynCapacity = $totalSize * ($completedValidSize / $validSize);//初始同步容量采用的总容量*有效数据百分比来获取，确保进度平滑
                    }

                    $completedValidStr = v1_calsize($completedValidSize, $this->verifyValueFalg($completedValidSize));
                    if ($validSizeStr == xphp_get_config('app', 'NULLSPACE')) {
                        $validSizeStr = "0B";
                    }
                    if ($completedValidStr == xphp_get_config('app', 'NULLSPACE')) {
                        $completedValidStr = "0B";
                    }
                    $volumeValidDataInfo = $validSizeStr . "/" . $completedValidStr;
                    if ($totalSizeStr == xphp_get_config('app', 'NULLSPACE')) {
                        $totalSizeStr = "0B";
                    }

                    $volumeInfo = $totalSizeStr . " / " . v1_calsize($initialSynCapacity, $this->verifyValueFalg($initialSynCapacity));
                    if ($completedValidSize == 0 || $validSize == 0) {
                        $volumeInfo = $totalSizeStr . " / 0B";
                    }
                    $writeSize = $data[0]['write_size'];
                    $transportSize = $data[0]['transport_size'];
                    $taskRunningCapacityInfo = array(
                        'completed_size' => v1_calsize($completedSize, $this->verifyValueFalg($completedSize)),
                        'volume_valid_info' => $volumeValidDataInfo,
                        'write_size' => v1_calsize($writeSize, $this->verifyValueFalg($writeSize)),
                        'transport_size' => v1_calsize($transportSize, $this->verifyValueFalg($transportSize)),
                        'volume_info' => $volumeInfo
                    );
                    break;
                case xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER']:
                    if ($taskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC'] || $taskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']) {
                        $totalSize = $data[0]['total_size'];
                        $totalSizeStr = v1_calsize($totalSize, true);
                        $validSize = $data[0]['valid_size'];
                        $validSizeStr = v1_calsize($validSize, true);
                        $completedValidSize = $data[0]['completed_valid_size'];
                        if ($completedValidSize == 0 || $validSize == 0) {
                            $initialSynCapacity = 0;
                        } else {
                            $initialSynCapacity = $data[0]['total_size'] * ($completedValidSize / $validSize);  //初始同步容量采用的总容量*有效数据百分比来获取，确保进度平滑
                        }
                        $completedValidStr = v1_calsize($completedValidSize, true);
                        if ($completedValidStr == xphp_get_config('app', 'NULLSPACE')) {
                            $completedValidStr = "0B";
                        }
                        $volumeValidDataInfo = $validSizeStr . "/" . $completedValidStr;
                        $initialSynCapacityStr = v1_calsize($initialSynCapacity, true);
                        if ($initialSynCapacityStr == xphp_get_config('app', 'NULLSPACE')) {
                            $initialSynCapacityStr = "0B";
                        }
                        $volumeInfo = $totalSizeStr . " / " . $initialSynCapacityStr;

                        $taskRunningCapacityInfo = array(
                            'completed_size' => v1_calsize($data[0]['completed_size'], $this->verifyValueFalg($data[0]['completed_size'])),
                            'volume_valid_info' => $volumeValidDataInfo,
                            'volume_info' => $volumeInfo,
                            'write_size' => v1_calsize($data[0]['write_size'], $this->verifyValueFalg($data[0]['write_size'])),
                            'transport_size' => v1_calsize($data[0]['transport_size'], $this->verifyValueFalg($data[0]['transport_size'])),

                        );
                    } else if ($taskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']) {
                        $totalSize = $data[0]['total_size'];
                        $totalSizeStr = v1_calsize($totalSize, $this->verifyValueFalg($totalSize));
                        $validSize = $data[0]['valid_size'];
                        $validSizeStr = v1_calsize($validSize, $this->verifyValueFalg($validSize));
                        if ($validSizeStr == xphp_get_config('app', 'NULLSPACE')) {
                            $validSizeStr = "0B";
                        }
                        $completedValidSize = $data[0]['completed_valid_size'];
                        $completedValidStr = v1_calsize($completedValidSize, $this->verifyValueFalg($completedValidSize));
                        $volumeInfo = $totalSizeStr . " / " . $validSizeStr;
                        $taskRunningCapacityInfo = array(
                            'completed_size' => v1_calsize($data[0]['completed_size'], $this->verifyValueFalg($data[0]['completed_size'])),
                            'volume_valid_info' => $completedValidStr,
                            'write_size' => v1_calsize($data[0]['write_size'], $this->verifyValueFalg($data[0]['write_size'])),
                            'transport_size' => v1_calsize($data[0]['transport_size'], $this->verifyValueFalg($data[0]['transport_size'])),
                            'volume_info' => $volumeInfo
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
    private function verifyValueFalg($value)
    {
        $flag = true;
        if (!$value) {
            $flag = false;
        }
        return $flag;
    }

    /**
     * 获取恢复数据源主机信息
     */
    private function getDataSourceHosstInfo($master_agent_uuid_)
    {
        $sql = "select DISTINCT master_agent_detail from cdp_vol_backup_agent where master_agent_uuid ='{$master_agent_uuid_}'";
        $data = $this->dbSelect($sql);
        $dataSourceHost = "--";
        if (!empty($data)) {
            $master_agent_detail = json_decode($data[0]['master_agent_detail']);
            $hostName = $master_agent_detail->hostname;
            $agentName = $master_agent_detail->agent_name;
            $agentIp = $master_agent_detail->ip;
            $hostNameStr = $this->agentStr($agentName, $hostName, $agentIp);
        }

        return $hostNameStr;
    }

    /**
     * 通过恢复数据源卷UUID获取恢复卷信息
     * @param vol_uuid
     */
    private function getDataSourceVolInfo($vol_uuid, $timePoint)
    {
        $sql = "select DISTINCT vol_name,vol_display_name,capacity from cdp_vol_backup_vol_set where vol_uuid = ? 
            and ?  BETWEEN start_timestamp and end_timestamp ";
        $data = $this->dbSelect($sql, array($vol_uuid, $timePoint));
        $dataSourceVol = "";
        if (!empty($data)) {
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
     * 获取指定存储所在节点
     */
    private function storageLocationNode($storageUuid)
    {
        $sql = "select node_uuid from bd_storage_resource where storage_uuid = ?";
        $data = $this->dbSelect($sql, array($storageUuid));
        $nodeuuid = "";
        if (!empty($data)) {
            $nodeuuid = $data[0]['node_uuid'];
        }
        return $nodeuuid;
    }

    /**
     * 得到节点展示名称
     * @param string $ip
     * @param string $nickname
     * @param string $hostname
     */
    private function getNodeShowName($ip, $nickname, $hostname)
    {
        if (empty($ip) && empty($nickname) && empty($hostname)) {
            return "--";
        }
        //如果IP和昵称一样,显示主机名+IP,否则,显示昵称+IP
        if ($ip == $nickname || empty($nickname)) {
            $name = $hostname . '(' . $ip . ')';
        } else {
            $name = $nickname . '(' . $ip . ')';
        }
        return $name;
    }

    /**
     * 获取可供回切用的主机信息
     */
    private function getTakeoverHost($fb_target_uuid, $task_uuid, $create_task_type, $taskMasterUuid)
    {
        $list = array();
        $agentuuidArr = (new Client())->getClientUuids();
        if (empty($agentuuidArr)) {
            return json_encode($list);
        }
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,agent_type,net_model
                from bd_agent
                where  (agent_type =? or agent_type =?)  ";
        if (is_array($agentuuidArr)) {
            $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
            $sql .= " and agent_uuid in {$agentuuidArrStr}";
        }
        if ($create_task_type == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP']) {  //备份任务回切目标机器可以是数据源主机
            $dataSql = $sql;
            $isAutoTakeover = true;
            $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL'], xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS']);
        } else {
            $dataSql = $sql;
            $isAutoTakeover = false;
            $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL'], xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS']);
        }
        $data = $this->dbSelect($dataSql, $sqlParams);
        $list = array();
        foreach ($data as $host_info) {
            $agentUuid = $host_info['agent_uuid'];
            $netModel = $host_info['net_model'];
            $onlineFlag = $host_info['online_flag'];
            $agentType = $host_info['agent_type'];
            $agentName = $host_info['agent_name'];
            $hostName = $host_info['hostname'];
            $ip = $host_info['ip'];

            $agentEnabled = $this->getAgentEnabled($agentUuid, $task_uuid, $isAutoTakeover, $taskMasterUuid);
            if (!$agentEnabled) { //不可用
                continue;
            }

            if (xphp_get_config('app', 'FLAG')['SET'] == $onlineFlag) {
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_ONLINE');
            } else {
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_OFFLINE');
            }

            if (!empty($agentName) && $agentName != $ip) {
                $hostInfo = $agentName ? $agentName . "(" . $ip . ")" : $hostName . "(" . $ip . ")";
            } else {
                $hostInfo = $hostName . "(" . $ip . ")";
            }

            if (xphp_get_config('app', 'FLAG')['SET'] == $onlineFlag) {
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_ONLINE');
            } else {
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_OFFLINE');
                $hostInfo = $hostInfo . "--" . $statusDes;
            }

            $taskInfo = $this->taskExist($agentUuid);  //获取任务是否存在，获取任务类型
            $taskName = "";
            $bkTaskIsRunning = xphp_get_config('app', 'FLAG')['UNSET'];
            if (count($taskInfo) > 0) {
                $taskName = $taskInfo['task_name'];
                $taskType = $taskInfo['task_type'];
                $taskStatus = $taskInfo['task_status'];
                if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'] && $taskType == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
                    $bkTaskIsRunning = xphp_get_config('app', 'FLAG')['SET'];
                    ;
                }
            }
            $hostOsType = $host_info['os_type'];
            $taskMasterAgentOsType = $this->getHostOsTypeForTaskuuid($task_uuid, $create_task_type);

            if ($hostOsType != $taskMasterAgentOsType && $agentType != xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS']) {
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
        return $list;
    }

    /**
     * 获取筛选主机卷信息
     * @param：agent_uuid
     */
    private function getHostVolInfoByUUID($hostUuid)
    {
        $agentSql = "select agent_type from bd_agent where agent_uuid = ? ";
        $data = $this->dbSelect($agentSql, array($hostUuid));
        $agentType = 1;
        if (!empty($data)) {
            $agentType = $data[0]['agent_type'];
        }

        $sql = "select vol.vol_name,vol.vol_uuid,vol.capacity,vol.free_space,vol.is_boot,vol.display_name,vol.mount_point,vol.detail
                from bd_agent_disk as disk,bd_agent_vol as vol
                where vol.disk_uuid = disk.disk_uuid and disk.agent_uuid = ?";
        $data = $this->dbSelect($sql, array($hostUuid));
        $list = array();
        $HostMountPointArry = array();
        foreach ($data as $v) {
            $detail = json_decode($v['detail']);
            $mountPoint = $v['mount_point'];
            $volName = $v['vol_name'];
            $volTypeValue = $detail->vol_type;
            $canSelect = true;

            $volTypeValue = $detail->vol_type;
            //保留分区、恢复分区、pv分区、扩展分区
            if (
                $volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_WIN_RESERVE_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_WIN_RECOVERY_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_PV_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_EXTEND_VOLUME']
            ) {
                continue;
            }
            //swap 分区
//            if($mountPoint =="[SWAP]"){
//                continue;
//            }
            if ($volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_SWAP_VOLUME']) {
                continue;
            }
            //内存操作系统的内置分区，如 live 等,以lvice-去过滤
            $pos = strstr($volName, "live-");
            if ($pos !== false) {
                continue;
            }
            if (
                ($volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_BOOT_VOLUME']
                    || $volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_SYSTEM_VOLUME']
                    || $volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_EFI_VOLUME'])
                && $agentType != xphp_get_config('client', 'AGENT_TYPE', 'resources')['MEMORY_OS']
            ) {
                $canSelect = false;
            }

            // 普通分区，未挂载分区 自由勾选
            if (!empty($v['mount_point'])) {
                array_push($HostMountPointArry, $v['mount_point']);
            }
            $list[] = array(
                "uuid" => $v['vol_uuid'],
                "capacity_value" => $v['capacity'],
                "capacity" => v1_calsize($v['capacity'], true),
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
    private function getHostDiskInfo($uuid)
    {
        $sql = "select disk_uuid,capacity,free_space,display_name,detail from bd_agent_disk where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $list = array();
        foreach ($data as $v) {
            $diskDetail = json_decode($v['detail']);
            $deviceType = $diskDetail->device_type;
            if ($deviceType == "12") {
                continue;
            }
            $list[] = array(
                "uuid" => $v['disk_uuid'],
                "capacity_value" => $v['capacity'],
                "capacity" => v1_calsize($v['capacity'], true),
                "free_space" => $v['free_space'],
                "display_name" => $v['display_name']
            );
        }
        return $list;
    }

    /**
     * 通过任务UUID获取任务主机或数据源对应的操作系统类型
     * @param $taskuuid
     */
    private function getHostOsTypeForTaskuuid($taskuuid, $taskType)
    {
        if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER']) {  //接管任务
            $sql = "select agent.master_agent_detail,agent.id from cdp_vol_backup_agent agent,cdp_vol_task task 
                    where task.task_uuid =? and task.master_agent_uuid = agent.master_agent_uuid ORDER BY agent.id desc LIMIT 0,1";
        } else {
            $sql = "SELECT agent.os_type from cdp_vol_task task,bd_agent agent 
                    where task.task_uuid = ? and task.master_agent_uuid = agent.agent_uuid";
        }

        $osType = "";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (!empty($data)) {
            if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER']) {  //接管任务
                $detail = $data[0]['master_agent_detail'];
                $detailInfo = json_decode($detail);
                $osType = $detailInfo->os_type;
            } else {
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
    private function taskExist($agentUUID)
    {
        $sql = "SELECT task.task_status,task.task_name,task_type FROM bd_task task,cdp_vol_task_takeover_info takeover 
            WHERE takeover.task_uuid = task.task_uuid and task.task_type = ? and takeover.takeover_standby_agent_uuid = ? and task.delete_flag = ? ";
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER'];
        $delFlag = xphp_get_config('app', 'FLAG')['UNSET'];
        $data = $this->dbSelect($sql, array($taskType, $agentUUID, $delFlag));
        $taskList = array();
        if (!empty($data)) {
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
    private function getAgentEnabled($host_uuid, $task_uuid, $isAutoTakeover, $taskMasterUuid)
    {
        if ($isAutoTakeover && ($taskMasterUuid == $host_uuid)) {
            return true;
        }
        $sql = "select vol_task.task_uuid,task.task_status,task.task_type from cdp_vol_task vol_task,bd_task task 
            where (vol_task.master_agent_uuid = ? or vol_task.standby_agent_uuid = ? or vol_task.host_ha_standby_agent_uuid = ?) 
            and task.task_uuid =vol_task.task_uuid and task.task_type !=? ";
        $data = $this->dbSelect($sql, array($host_uuid, $host_uuid, $host_uuid, xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY']));
        $isEnabled = false;
        if (!empty($data)) {
            foreach ($data as $v) {
                if ($task_uuid == $v['task_uuid']) {
                    $isEnabled = true;
                } else {
                    $isEnabled = false;
                    return $isEnabled;
                }
                if ($v['task_type'] == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'] && $v['task_status'] == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
                    $isEnabled = false;
                    return $isEnabled;
                }
            }
        } else {
            $isEnabled = true;
        }

        //目标机器是否已做为恢复目标机器被使用
        $sqlRe = "select task_uuid from cdp_vol_task where recovery_target_agent_uuid =?";
        $dataRe = $this->dbSelect($sqlRe, array($host_uuid));
        if (!empty($dataRe)) {
            $isEnabled = false;
            return $isEnabled;
        } else {
            $isEnabled = true;
        }
        //目标机器是否已做为接管备机被使用
        $sqlTo = "select task_uuid from cdp_vol_task_takeover_info where takeover_standby_agent_uuid = ?";
        $dataTo = $this->dbSelect($sqlTo, array($host_uuid));
        if (!empty($dataTo)) {
            $isEnabled = false;
            return $isEnabled;
        } else {
            $isEnabled = true;
        }

        //目标机器是否已做为回切目标主机被使用
        $sqlFa = "select task_uuid from cdp_vol_task_takeover_failback_info where failback_target_agent_uuid = ?";
        $dataFa = $this->dbSelect($sqlFa, array($host_uuid));
        if (!empty($dataFa)) {
            foreach ($dataFa as $v) {
                if ($task_uuid == $v['task_uuid']) {  //如果是当前任务，运行被选择
                    $isEnabled = true;
                } else {
                    $isEnabled = false;
                    return $isEnabled;
                }
            }
        } else {
            $isEnabled = true;
        }

        return $isEnabled;
    }

    /**
     * 获取任务备机信息
     * @param unknown $taskuuid
     */
    private function getTaskStandbyHostInfo($taskuuid)
    {
        $standbyNicList = [];
        $standbySql = "select takeover_standby_agent_uuid,takeover_vm_hypervisor  from cdp_vol_task_takeover_info where task_uuid = ?";
        $standbyData = $this->dbSelect($standbySql, array($taskuuid));
        $standbyAgentuuid = $standbyData[0]['takeover_standby_agent_uuid'];
        $takeoverAgentType = $standbyData[0]['takeover_vm_hypervisor'];
        if ($takeoverAgentType == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {
            $updateNetCard = $this->updateHostNetCardinf($standbyAgentuuid);
            if ($updateNetCard) {
                $standbyNicList = (new VolcdpBackUp())->getHostNetworkInfo(['agent_uuid' => $standbyAgentuuid]);

            }
        } else if ($takeoverAgentType != xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {  //内嵌虚拟机
            $standbyNicList = $this->getTempAgentConfigInfo($standbyAgentuuid);
        }

        $cdpVolTaskTakeoverInfo = array(
            'stand_agent_uuid' => $standbyAgentuuid,
            'standby_nic_list' => $standbyNicList,
            'takeover_agent_type' => $takeoverAgentType,
        );
        return $cdpVolTaskTakeoverInfo;
    }

    /**
     * 获取模板主机配置信息
     * @return void
     */
    private function getTempAgentConfigInfo($uuid)
    {
        $sql = "select takeover_vm_config from cdp_vol_task_takeover_info where takeover_standby_agent_uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        $config = json_decode($data[0]['takeover_vm_config']);
        $businessNicSet = $config->interfaces;
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

        $refreshSet = array($refreshAgentSet);
        $pfMSg = array();
        $pfMSg['refresh_agent_set'] = $refreshSet;
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $nodeuuid = (new Node())->getLocalNodeUUID();  //获取主节点UUID
        $opName = 'VOL_CDP_TASK_SCANNING_NIC_INFO';
        //        $mbResult = $this->mbVolCdpMsg($nodeuuid, $opName, $msg);

        //        $result = $mbResult['result'];
        // $msg = $mbResult['msg'];
        $operate = (new VolcdpOpcode())->getOpcodeDes($opName);
        //返回结果到UI
        $result = true;
        if ($result) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * 得到卷CDP任务传输策略
     * @param string $taskuuid  任务UUID
     * @param int $strategyid   策略ID
     */
    public function getVolCdpTransportStrategy($taskuuid, $strategyid, $currentTaskRunningStage)
    {
        $info = array();
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
        if (
            $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
            || $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
            || $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']
        ) {  //任务处于回切阶段

            $fbSql = "SELECT transport_compress_flag,transport_encrypt_flag,transport_thread_num,transport_block_size,transport_compress_method
                FROM cdp_vol_task_takeover_failback_info  WHERE task_uuid = ? ";
            $fbData = $this->dbSelect($fbSql, array($taskuuid));
            $encrypt = $fbData[0]['transport_encrypt_flag'];
            $compress = $fbData[0]['transport_compress_flag'];
            $transportBlockSize = $fbData[0]['transport_block_size'];
            $compressMethod = $fbData[0]['transport_compress_method'];
        }

        $info['encrypt'] = v1_parse_flag_to_bool($encrypt);
        $info['compress'] = v1_parse_flag_to_bool($compress);
        $info['compress_method'] = $compressMethod;
        $info['transport_block_size'] = v1_calsize($transportBlockSize);
        $info['mode'] = xphp_get_lang('UI_BACKUP_TRANSPORT_NBD');
        $info['max_transport_speed'] = $data[0]['max_transport_speed']; //最大传输速度
        $info['reconnect_times'] = $data[0]['reconnect_times'];  //重连次数
        $info['reconnect_interval'] = $data[0]['reconnect_interval']; //重连间隔
        $info['encrypt_method'] = $data[0]['encrypt_method']; //传输加密算法
        $name = "";
        if (!empty($data[0]['ip'])) {
            $name = $data[0]['ip'] . ":" . $data[0]['port'];
            if (!empty($data[0]['alias_name'])) {
                $name .= "(" . $data[0]['alias_name'] . ")";
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
    private function getHostInfo($agent_uuid, $tasktype = 1, $taskuuid = '')
    {
        $sql = "select  ";
        if (xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'] == $tasktype) {  //备份
            $sql = "SELECT hostname,ip,os_type FROM bd_agent where agent_uuid = ?";
            $data = $this->dbSelect($sql, array($agent_uuid));
            $hostname = $data[0]['hostname'];
            $agentIp = $data[0]['ip'];
            $osType = $data[0]['os_type'];

            $hostInfo = $hostname . "(" . $agentIp . ")";
            $agentInfo = array(
                "host_info" => $hostInfo,
                "agent_ip" => $agentIp,
                'os_type' => $osType,
            );
        } else if (xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY'] == $tasktype || xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER'] == $tasktype) {  //恢复，接管
            $agentInfo = $this->getVolCdpBackupAgentInfo($agent_uuid);
        } else if (xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER']) {
            $sql = "select takeover_agent_role from cdp_vol_task_takeover_info where task_uuid = ?";
            $data = $this->dbSelect($sql, array($taskuuid));
            $takeoverAgentRole = $data[0]['takeover_agent_role'];
            if ($takeoverAgentRole == xphp_get_config('client', 'AGENT_TYPE', 'resources')['NORMAL']) {
                $agentInfo = $this->getVolCdpBackupAgentInfo($agent_uuid);
            } else if ($takeoverAgentRole == xphp_get_config('module', 'MODULE_TYPE')['TEMP_AGENT']) {
                $tempSql = "select name,os_type from vm_emd where uuid = ?";
                $data = $this->dbSelect($tempSql, array($agent_uuid));
                $hostInfo = $data[0]['name'] . "(" . xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC') . ")";
                $agentInfo = array(
                    "host_info" => $hostInfo,
                    "agent_ip" => xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC'),
                    'os_type' => $data[0]['os_type'],
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
        } catch (Throwable $e) {
            $hostname = "--";
            $agentIp = "--";
            $osType = "--";
        }
        $hostInfo = $hostname . "(" . $agentIp . ")";
        $agentInfo = array(
            "host_info" => $hostInfo,
            "agent_ip" => $agentIp,
            'os_type' => $osType,
        );
        return $agentInfo;
    }

    /**
     * 获取备份任务数据流向map
     * @param string taskuuid,int task_type
     */
    public function getBackupTaskMapInfo($params)
    {
        $taskUuid = $params['taskuuid'];
        $taskType = $params['tasktype'];
        $sql = "select task.task_status,task.agent_uuid,task.node_uuid,vol_task.current_task_running_stage,vol_task.master_agent_uuid,
                    vol_task.standby_agent_uuid,vol_task.auto_takeover_flag,vol_task.recovery_data_source 
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
        if (
            $currentTaskStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
            || $currentTaskStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
            || $currentTaskStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
        ) {
            $masterAgentUuid = $this->getFailBackupAgentInfo($taskUuid);
        }
        //主机信息
        $masterInfo = $this->getBdAgentInfo($masterAgentUuid);
        $masterAgent = $masterInfo['agentInfo'];   //主机名
        $masterAgentIsOnline = $masterInfo['isOnline'];    //主机在线状态
        $masterAgentAppIsOnline = $this->getHostAppStatus($masterAgentUuid);  //主机应用是否在线


        if ($standbyAgentUuid != "") { //备机信息
            $standbyInfo = $this->getBdAgentInfo($standbyAgentUuid);
            $standby = $standbyInfo['agentInfo'];   //备机名
            $standbyIp = $standbyInfo['ip'];
            $standbyIsOnline = $standbyInfo['isOnline'];    //在线状态
            $standbyAppIsOnline = $this->getHostAppStatus($standbyAgentUuid);  //备机应用是否在线
            $standbyIsConf = xphp_get_desc('Volcdp', 'STANDBYCONF')['CONFIGURED'];  //任务配置备机
            if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY'] && $recoveryDataSource != xphp_get_desc('Volcdp', 'RECOVERY_DATA_SOURCE')['STANDBY']) {
                $standbyIsConf = xphp_get_desc('Volcdp', 'STANDBYCONF')['NOT_CONF'];  //未配置备机
            }
            $standbyIsShow = xphp_get_desc('Volcdp', 'STANDBYCONF')['CONFIGURED'];  //任务配置备机，相关图标显示
        } else {  //未配置备机
            $standbyIsConf = xphp_get_desc('Volcdp', 'STANDBYCONF')['NOT_CONF'];  //未配置备机
            $standby = "";
            $standbyIsOnline = 0;
            $standbyAppIsOnline = 0;
            $standbyIsShow = xphp_get_desc('Volcdp', 'STANDBYCONF')['NOT_CONF'];  //任务未配置备机，相关图标隐藏
        }

        if ($autoTakeoverFlag == xphp_get_desc('Volcdp', 'TAKEOVER_TYPE')['AUTO_TAKEOVER']) {
            $takeoverAgentUuid = $data[0]['takeover_standby_agent_uuid'];
            $autoTakeoverSql = "select takeover_standby_agent_uuid,takeover_vm_hypervisor 
                                from cdp_vol_task_takeover_info where task_uuid = ?";
            $takeoverData = $this->dbSelect($autoTakeoverSql, array($taskUuid));
            $takeoverAgentUuid = $takeoverData[0]['takeover_standby_agent_uuid'];
            $takeoverAgentType = $takeoverData[0]['takeover_vm_hypervisor'];

            if ($takeoverAgentType == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {
                $standbyInfo = $this->getAutoTakeoverHostInfo($taskUuid);
                $standby = $standbyInfo['agentInfo'];   //备机名
                $standbyIsOnline = $standbyInfo['isOnline'];    //在线状态
                $standbyIp = $standbyInfo['ip'];
            } else if ($takeoverAgentType != xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {
                $tempAgentInfo = $this->getTempAgentInfo($takeoverAgentUuid, $taskUuid);  //获取接管目标机器
                $standby = $tempAgentInfo['agent_name'];
                $standbyIsOnline = xphp_get_config('app', 'FLAG')['SET'];
                $standbyIp = $standby . "(" . xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC') . ")";
            }
            $standbyAppIsOnline = $this->getHostAppStatus($takeoverAgentUuid);  //备机应用是否在线
            $standbyIsShow = xphp_get_desc('Volcdp', 'STANDBYCONF')['CONFIGURED'];  //任务配置备机，相关图标显示
        }
        $backupServerStatus = 1; //保留获取备份服务器服务状态项
        $info = array(
            "masterDesc" => $masterAgent,
            "masterIp" => $masterInfo['ip'],
            "standbyIp" => $standbyIp,
            "standbyDesc" => $standby,
            "nodeInfo" => $nodeInfo['nodeInfo'],
            "nodeIp" => $nodeInfo['ip'],
            "standbyIsConf" => v1_parse_flag_to_bool($standbyIsShow),
            'currentTaskStage' => $currentTaskStage,
            "masterAgentMapStatus" => $this->parseMasterMapStatus($masterAgentIsOnline, $masterAgentAppIsOnline, $taskStatus, $currentTaskStage),
            "hostToBSTransStatus" => $this->parseHostToBSTransStatus($taskStatus, $currentTaskStage, $taskType, $recoveryDataSource),    //主机与备份服务器的传输示意图
            "backupServerMap" => $backupServerStatus,
            "bsToStandbyTransStatus" => $this->parseBsToStandbyTransStatus($taskStatus, $taskUuid, $currentTaskStage, $taskType, $recoveryDataSource, $standbyIsConf), //备份服务器到备机传输示意图
            "standbyMapStatus" => $this->parseStandbyMapStatus($standbyIsOnline, $standbyAppIsOnline, $taskStatus, $currentTaskStage),
            "dataIsStandbyToHost" => $this->parseDataIsStandbyToHost($taskStatus, $currentTaskStage, $taskType, $recoveryDataSource),   //解析是否满足数据从备机直接到目标机器
        );
        return $info;
    }

    /**
     * 获取恢复任务数据流向map
     * @params string taskuuid,int task_type
     */
    public function getRecoverTaskMapInfo($params)
    {
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
        if ($recoveryDataSource == 2) {
            $standbyInfo = $this->getBdAgentInfo($standby_agent_uuid);  //恢复数据源为备机,获取备机信息
            $standby = $standbyInfo['agentInfo'];                   //恢复数据源主机名
            $standbyIsOnline = $standbyInfo['isOnline'];            //数据源主机在线状态
            $standbyAppIsOnline = 1;
            $standbyIsConf = 1;
        } else {
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
            "standbyIsConf" => v1_parse_flag_to_bool($standbyIsConf), //是否配置备机
            "currentTaskStage" => $currentTaskStage,
            "masterAgentMapStatus" => $this->parseMasterMapStatus($standbyIsOnline, $standbyAppIsOnline, $taskStatus, $currentTaskStage),
            "hostToBSTransStatus" => $this->parseHostToBSTransStatus($taskStatus, $currentTaskStage, $taskType, $recoveryDataSource),    //主机与备份服务器的传输示意图
            "backupServerMap" => $backupServerStatus,
            "bsToStandbyTransStatus" => $this->parseBsToStandbyTransStatus($taskStatus, $taskUuid, $currentTaskStage, $taskType, $recoveryDataSource, $standbyIsConf), //备份服务器到备机传输示意图
            "standbyMapStatus" => $this->parseStandbyMapStatus($standbyIsOnline, $standbyAppIsOnline, $taskStatus, $currentTaskStage),
            "dataIsStandbyToHost" => $this->parseDataIsStandbyToHost($taskStatus, $currentTaskStage, $taskType, $recoveryDataSource)   //解析是否满足数据从备机直接到目标机器
        );
        return $info;
    }

    /**
     * 获取接管任务数据流向map
     * @param string taskuuid,int task_type
     */
    public function getTakeoverTaskMapInfo($params)
    {
        $taskUuid = $params['taskuuid'];
        $taskType = $params['tasktype'];
        $sql = "select task.task_status,task.agent_uuid,task.node_uuid,vol_task.current_task_running_stage,vol_task.master_agent_uuid,
				    take.takeover_standby_agent_uuid,take.takeover_vm_hypervisor  
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

        if ($takeoverAgentType == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {
            $standbyInfo = $this->getBdAgentInfo($takeoverStandbyAgentUuid);  //获取接管目标机器
            $standby = $standbyInfo['agentInfo'];                   //接管目标主机名
            $standbyIsOnline = $standbyInfo['isOnline'];            //接管目标主机在线状态
            $standbyIp = $standbyInfo['ip'];  //备机IP
        } else if ($takeoverAgentType != xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {
            $tempAgentInfo = $this->getTempAgentInfo($takeoverStandbyAgentUuid, $taskUuid);  //获取接管目标机器
            $standby = $tempAgentInfo['agent_name'];
            $standbyIsOnline = xphp_get_config('app', 'FLAG')['SET'];
            $standbyIp = $standby . "(" . xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC') . ")";
        }


        $standbyAppIsOnline = $this->getHostAppStatus($takeoverStandbyAgentUuid);  //备机应用是否在线;
        $standbyIsConf = 1;
        $dataSourceAgentInfo = $this->getDataSourceAgentInfo($agent_uuid);  //获取数据主机信息
        $backupServerStatus = 1; //保留获取备份服务器服务状态项
        $masterDesc = $dataSourceAgentInfo['host_info'];   //数据源Host
        $masterIp = $dataSourceAgentInfo['agent_ip'];   //数据源IP

        //回切
        if (
            $currentTaskStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']
            || $currentTaskStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
            || $currentTaskStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']
        ) {
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
            "standbyIsConf" => v1_parse_flag_to_bool($standbyIsConf), //是否配置备机
            "currentTaskStage" => $currentTaskStage,  //任务阶段
            "taskStatus" => $taskStatus,
            "masterAgentMapStatus" => $this->parseMasterMapStatus($standbyIsOnline, $standbyAppIsOnline, $taskStatus, $currentTaskStage),
            "hostToBSTransStatus" => $this->parseHostToBSTransStatus($taskStatus, $currentTaskStage, $taskType, $dataSource),    //主机与备份服务器的传输示意图
            "backupServerMap" => $backupServerStatus,
            "bsToStandbyTransStatus" => $this->parseBsToStandbyTransStatus($taskStatus, $taskUuid, $currentTaskStage, $taskType, $dataSource, $standbyIsConf), //备份服务器到备机传输示意图
            "standbyMapStatus" => $this->parseStandbyMapStatus($standbyIsOnline, $standbyAppIsOnline, $taskStatus, $currentTaskStage),
            "dataIsStandbyToHost" => $this->parseDataIsStandbyToHost($taskStatus, $currentTaskStage, $taskType, $dataSource)   //解析是否满足数据从备机直接到目标机器
        );
        return $info;
    }

    /**
     * 获取备份数据源对应的客户端信息
     */
    private function getDataSourceAgentInfo($master_agent_uuid_)
    {
        $sql = "select DISTINCT master_agent_detail from cdp_vol_backup_agent where master_agent_uuid ='{$master_agent_uuid_}'";
        $data = $this->dbSelect($sql);
        $hostInfo = "--";
        $agentIp = "--";
        if (!empty($data)) {
            $master_agent_detail = json_decode($data[0]['master_agent_detail']);
            $hostname = $master_agent_detail->hostname;
            $agentIp = $master_agent_detail->ip;
            $hostInfo = $hostname . "(" . $agentIp . ")";
        }

        $agentInfo = array(
            "host_info" => $hostInfo,
            "agent_ip" => $agentIp
        );
        return $agentInfo;
    }

    /**
     * 获取备份节点信息
     * @param string nodeuuid
     */
    private function getNodeServersInfo($node_uuid)
    {
        $sql = "SELECT ip,host_name from bd_node where node_uuid =? ";
        $data = $this->dbSelect($sql, array($node_uuid));
        $ip = $data[0]['ip'];
        $host_name = $data[0]['host_name'];
        $hostArry = array(
            "nodeInfo" => $host_name . "(" . $ip . ")",
            "ip" => $data[0]['ip']
        );
        return $hostArry;
    }

    /**
     * 任务在回切阶段，根据任务uuid获取回切目标主机信息
     */
    private function getFailBackupAgentInfo($task_uuid)
    {
        $sql = "select failback_target_agent_uuid from cdp_vol_task_takeover_failback_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $agentuuid = $data[0]['failback_target_agent_uuid'];
        return $agentuuid;
    }

    /**
     * 获取客户端对应的应用是否在线
     * @param string agent_uuid
     * @return int app_type
     */
    private function getHostAppStatus($node_uuid)
    {
        $sql = "select app_type,online_flag from bd_agent_app where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($node_uuid));
        $appOnLine = 0;
        if (!empty($data)) {
            foreach ($data as $d) {
                $appIsOnline = $d['online_flag'];
                if ($appIsOnline == 1) {
                    $appOnLine = 1;
                }
            }
        }
        return $appOnLine;
    }

    /**
     * 获取备份任务配置的自动接管机器信息
     * @param unknown $task_uuid
     */
    private function getAutoTakeoverHostInfo($task_uuid)
    {
        $sql = "select agent.agent_name,agent.hostname,agent.ip,agent.online_flag 
            from bd_agent as agent,cdp_vol_task_takeover_info as takeover_info 
            where agent.agent_uuid = takeover_info.takeover_standby_agent_uuid and takeover_info.task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $agentArry = array(
            "agentInfo" => "--",
            "isOnline" => 0,
            "ip" => "--"
        );
        if (!empty($data)) {
            $agentName = $data[0]['agent_name'];
            $agentIp = $data[0]['ip'];
            $agentHostName = $data[0]['hostname'];
            $agentInfo = $agentHostName . "(" . $agentIp . ")";
            $agentIsOnline = $data[0]['online_flag'];
            $agentArry = array(
                "agentInfo" => $agentInfo,
                "isOnline" => $agentIsOnline,
                "ip" => $data[0]['ip']
            );
        }
        return $agentArry;
    }

    /**
     * 获取模板代理信息
     * @return void
     */
    private function getTempAgentInfo($uuid, $task_uuid)
    {
        $sql = "select name from vm_emd where uuid = ? and task_uuid =?";
        $data = $this->dbSelect($sql, array($uuid, $task_uuid));
        $agentName = $data[0]['name'];
        $agentArry = array(
            "agent_name" => $agentName,
            "ip" => ""
        );
        return $agentArry;
    }

    /**
     * 解析目标主机示意图
     * @param $masterAgentIsOnline:主机在线状态,$masterAgentAppIsOnline:主机应用在线状态,
     *        $task_status:任务状态,$task_stage:任务阶段
     * @return int hostMapStatus
     */
    private function parseMasterMapStatus($masterAgentIsOnline, $masterAgentAppIsOnline, $task_status, $task_stage)
    {
        //[0:设备离线,1:设备在线 ;服务在线且 对外提供服务;2:设备在线,未对外提供服务]
        if ($masterAgentIsOnline == xphp_get_config('app', 'FLAG')['SET']) {
            $hostMapStatus = 1;  //主机在线
        } else {
            $hostMapStatus = 0;  //离线
        }

        return $hostMapStatus;
    }

    /**
     * 解析主机到备份服务器的传输示意图
     * @param $task_status:任务状态,$task_stage:任务阶段
     * return int status
     */
    private function parseHostToBSTransStatus($task_status, $current_task_stage, $taskType, $recovery_data_source)
    {
        $transMapStatus = 0;  //默认状态
        switch ($task_status) {  //[1. 连通无状态,2. 连通有数据传输;3. 连接异常(网络异常),4. 连通任务暂停;5. 连通有心跳;6.任务出错;7:反向数据(与2方向相反);]
            //任务状态:新建/停止
            case xphp_get_config('task', 'TASKSTATUS')['WAITTING']:
            case xphp_get_config('task', 'TASKSTATUS')['STOPPED']:
                $transMapStatus = 1;
                break;
            case xphp_get_config('task', 'TASKSTATUS')['RUNNING']:  //任务状态:运行中
                switch ($current_task_stage) {
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['IN_TAKEOVER']:
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['IN_TAKEOVER_STARTING']:
                        $transMapStatus = 1;
                        break;
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']:
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']:
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']:
                        $transMapStatus = 5; //连通有心跳
                        break;
                    default:
                        $transMapStatus = 2;    // 连通有数据传输
                }
                break;
            case xphp_get_config('task', 'TASKSTATUS')['PAUSING']:
                $transMapStatus = 4;
                break;
            case xphp_get_config('task', 'TASKSTATUS')['ERROR']:
                $transMapStatus = 6;
                break;
            case xphp_get_config('task', 'TASKSTATUS')['NETWORK_FAULT']:
                $transMapStatus = 3;
                break;
            default:
                $transMapStatus = 1;
        }
        if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY']) {  //任务类型:恢复
            //备份服务器到目标机器
            if ($recovery_data_source == 1 && $task_status == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) { //恢复数据源为备份服务器
                $transMapStatus = 7;
            } elseif ($recovery_data_source == 2 && $task_status == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
                $transMapStatus = 5;
            } else if (($recovery_data_source == 2 || $recovery_data_source == 1) && $task_status == xphp_get_config('task', 'TASKSTATUS')['NETWORK_FAULT']) {
                $transMapStatus = 3;
            } else {
                $transMapStatus = 1;    //连通无状态
            }

        } elseif ($task_status == xphp_get_config('task', 'TASKSTATUS')['ERROR']) { //任务出错
            $transMapStatus = 6;    //任务出错
        }
        return $transMapStatus;
    }

    /**
     * 解析备份服务器到备机的传输示意图
     * @param $task_status:任务状态,$task_stage:任务阶段
     * return int status
     */
    private function parseBsToStandbyTransStatus($taskStatus, $taskUuid, $currentTaskStage, $taskType, $recoveryDataSource, $standbyIsConf)
    {
        $sqlISonline = "select agent.online_flag
                         from bd_task as task,bd_agent as agent,cdp_vol_task as vol_task
                         where task.task_uuid = vol_task.task_uuid and vol_task.standby_agent_uuid = agent.agent_uuid and task.task_uuid = ?";
        $dataISonline = $this->dbSelect($sqlISonline, array($taskUuid));
        if ($dataISonline[0]['online_flag'] == 2) {
            $taskStatus = 6;  // 备机离线状态也显示网络异常情况
        }
        //[1. 连通无状态,2. 连通有数据传输;3. 连接异常(网络异常),4. 连通任务暂停;5. 连通有心跳;6.任务出错;7:反向数据(与2方向相反)]
        $transMapStatus = 0;//默认状态
        switch ($taskStatus) {
            case xphp_get_config('task', 'TASKSTATUS')['WAITTING']:
            case xphp_get_config('task', 'TASKSTATUS')['STOPPED']:
                $transMapStatus = 1;
                break;
            case xphp_get_config('task', 'TASKSTATUS')['RUNNING']:
                $transMapStatus = 1;    // 连通有数据传输
                switch ($currentTaskStage) {
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['IN_TAKEOVER']:
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['IN_TAKEOVER_STARTING']:
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['INIT_SYNC']:
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['REALTIME_SYNC']:
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['WAIT_CONVERT_TO_REALTIME_SYNC']:
                        if ($standbyIsConf == xphp_get_desc('Volcdp', 'STANDBYCONF')['CONFIGURED']) {
                            $transMapStatus = 2;
                        }
                        break;
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC']:
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']:
                    case xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']:
                        $transMapStatus = 5;
                        break;
                }
                break;
            case xphp_get_config('task', 'TASKSTATUS')['SUCCESSED']:
                if ($currentTaskStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['IN_TAKEOVER'] || $currentTaskStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['IN_TAKEOVER_STARTING']) { //接管完成,任务阶段处于接管中
                    $transMapStatus = 5;
                }
                break;
            case xphp_get_config('task', 'TASKSTATUS')['PAUSING']:
                $transMapStatus = 4; //心跳保持
                break;
            case xphp_get_config('task', 'TASKSTATUS')['ERROR']:
                $transMapStatus = 6; //任务出错
                break;
            case xphp_get_config('task', 'TASKSTATUS')['NETWORK_FAULT']:
                $transMapStatus = 3; //网络出错
                break;
            default:
                $transMapStatus = 1; //连通无状态
        }
        return $transMapStatus;
    }

    /**
     * 解析目标备机示意图
     * @param $masterAgentIsOnline:主机在线状态,$masterAgentAppIsOnline:主机应用在线状态,
     *        $task_status:任务状态,$task_stage:任务阶段
     * @return int hostMapStatus
     */
    private function parseStandbyMapStatus($standbyIsOnline, $standbyAppIsOnline, $task_status, $task_stage)
    {
        //[0:设备离线,1:设备在线 ;服务在线且 对外提供服务;2:设备在线,未对外提供服务]
        if ($standbyIsOnline == xphp_get_config('app', 'FLAG')['SET']) {
            $standbyMapStatus = 1; //设备在线；
        } else {
            $standbyMapStatus = 0;  //离线
        }
        return $standbyMapStatus;
    }

    /**
     * 解析是否满足数据从备机直接到目标机器的数据流向
     * @param $task_status:任务状态;$current_task_stage:任务阶段;$taskType:任务类型,$recovery_data_source:恢复数据来源
     * @return int flag;
     */
    private function parseDataIsStandbyToHost($task_status, $current_task_stage, $taskType, $recovery_data_source)
    {
        $dataIsStandbyToHostMap = 0; // 0:不显示备机到目标机器示意图;1:显示数据流向;2:传输出错

        //备份任务或手动接管任务,任务阶段处于逆向初始同步或逆向实时同步状态
        if (
            ($current_task_stage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_INIT_SYNC'])
            || $current_task_stage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']
            || $current_task_stage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE')['FAILBACK_IN_STARTING']
            && ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP'] || $taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER'])
        ) {
            if ($task_status == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) { //任务运行中
                $dataIsStandbyToHostMap = 1;
            } elseif ($task_status == xphp_get_config('task', 'TASKSTATUS')['ERROR']) { //任务出错
                $dataIsStandbyToHostMap = 2;
            }
        }
        //恢复任务   恢复数据来源与备机 (默认值：0（备份作业），1：恢复数据来源于备份服务器，2：恢复数据来源与备机)
        if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY'] && $recovery_data_source == 2) {
            if ($task_status == xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
                $dataIsStandbyToHostMap = 1;
            } elseif ($task_status == xphp_get_config('task', 'TASKSTATUS')['ERROR']) { //任务出错
                $dataIsStandbyToHostMap = 2;
            }
        }
        return $dataIsStandbyToHostMap;
    }

    /**
     * 根据任务状态计算进度
     * @param int $taskStatus
     * @param int $totalSize
     * @param int $currentSize
     * @param boolean $percentFlag  是否一定要得到百分比格式
     */
    public function getTaskTotalProgress($taskStatus, $totalSize, $currentSize, $percentFlag, $tasktype)
    {
        if (
            $tasktype == xphp_get_config('task', 'TASKTYPE')['VM_FILE_RECOVERY'] ||
            $tasktype == xphp_get_config('task', 'TASKTYPE')['VM_INSTANT_RECOVERY'] ||
            $tasktype == xphp_get_config('task', 'TASKTYPE')['DB_CDP_BACKUP'] ||
            $tasktype == xphp_get_config('task', 'TASKTYPE')['FILE_CDP_BACKUP'] ||
            $tasktype == xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']
        ) {
            //细粒度恢复,瞬时恢复,数据库实时备份,文件实时备份不显示进度
            return xphp_get_config('app', 'NULLSPACE');
        }

        $speed = v1_calpercent($totalSize, $currentSize);

        if (
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] &&
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['PAUSED'] &&
            $taskStatus != xphp_get_config('task', 'TASKSTATUS')['ABNORMAL']
        ) {
            //如果任务没有运行
            if ($percentFlag) {
                return "0%";
            } else {
                return xphp_get_config('app', 'NULLSPACE');
            }
        }

        //解决round后出现一位小数的问题,比如32.1%,自动优化成32.10%,这样的话界面上不会出现进度回退的现象
        $speed = sprintf("%.2f", substr($speed, 0, -1)) . "%";
        return $speed;
    }

    /**
     * 获取当前任务所有执行卷的实时同步有效数据总和
     * @param task_uuid
     */
    public function getTaskCurrentTotalSize($taskUuid)
    {
        $sql = "select total_object_completed_valid_size from bd_running_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $totalVaildSize = "--";
        if (!empty($data)) {
            $sizeFlag = true;
            if ($data[0]['total_object_completed_valid_size'] == 0) {
                $sizeFlag = false;
            }
            $totalVaildSize = v1_calsize($data[0]['total_object_completed_valid_size'], $sizeFlag);
        }
        return $totalVaildSize;
    }

    /**
     * 获取服务端数据一致性校验的容量信息
     * @param $taskUuid
     */
    public function getServerConsCheckInfo($taskUuid)
    {
        $sql = "select total_size,completed_size,current_vol_uuid from cdp_vol_task_data_consistency_check_info where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUuid));
        $totalSize = "--";
        $completedSize = "0";
        $totalProgress = "0";
        $currentVol = "--";
        if (!empty($data)) {
            $totalSize = $data[0]['total_size'];
            $completedSize = $data[0]['completed_size'];
            $currentVoluuid = $data[0]['current_vol_uuid'];
            $currentVol = $this->getAgentVol($currentVoluuid);

            $speed = v1_calpercent($totalSize, $completedSize);
            $totalProgress = sprintf("%.2f", substr($speed, 0, -1)) . "%";
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
     * 通过目标卷UUID获取卷信息
     * @param target_vol_uuid
     */
    private function getAgentVol($target_vol_uuid)
    {
        $sql = "select display_name from bd_agent_vol where vol_uuid = '{$target_vol_uuid}'";
        $data = $this->dbSelect($sql);
        $recoverTargetVol = "";
        if (!empty($data)) {
            $recoverTargetVol = $data[0]['display_name'];
        }
        return $recoverTargetVol;
    }

    /**
     * 获取回切配置
     * @param unknown $taskuuid
     */
    public function getFailbackInfo($taskuuid)
    {
        $sql = "select failback_target_agent_uuid,memory_cache_alloc_space,file_cache_alloc_space,file_cache_storage_path,transport_encrypt_flag,
                    transport_compress_flag,monitor_data_io_replication_mode,storage_uuid,transport_thread_num,transport_block_size,mirror_backup_flag 
                from cdp_vol_task_takeover_failback_info 
                where task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $failbackInfo = array();
        if (!empty($data)) {
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
    public function getTaskStartTime($startTime, $taskStatus, $taskuuid, $taskType)
    {
        if ($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP']) {
            $sql = "select dst_start_timepoint from bd_backup_timepoint where task_uuid = ? ORDER BY id DESC LIMIT 0,1";
            $data = $this->dbSelect($sql, array($taskuuid));
            if (!empty($data)) {
                $startTime = $data[0]['dst_start_timepoint'];
            } else {
                return 0;
            }
        }
        return $startTime;
    }

    /**
     * 根据任务状态计算速度
     * @param unknown $taskStatus
     * @param unknown $speed
     */
    private function getJobSpeed($taskStatus, $speed)
    {
        if ($taskStatus != xphp_get_config('task', 'TASKSTATUS')['RUNNING']) {
            //如果任务没有运行
            return xphp_get_config('app', 'NULLSPACE');
        }
        return v1_calspeed($speed);
    }

    /**
     * 过滤开始时间
     * @param unknown $startTime
     * @return multitype:|unknown
     */
    private function getStartTIme($startTime, $status)
    {
        if (
            $status == xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ||
            $status == xphp_get_config('task', 'TASKSTATUS')['NETWORK_FAULT'] ||
            $status == xphp_get_config('task', 'TASKSTATUS')['ABNORMAL'] ||
            $status == xphp_get_config('task', 'TASKSTATUS')['SUCCESSED']
        ) {
            return $this->parseDate($startTime);
        }
        return xphp_get_config('app', 'TIMESPACE');
    }

    /**
     * 获取任务appliance信息
     * @param string $taskuuid
     */
    private function getJobAppliance($taskuuid)
    {
        $sql = "select ba.ip, ba.agent_name from bd_agent ba, vm_task vt where vt.agent_uuid = ba.agent_uuid and vt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        if (!empty($data)) {
            $flag = true;
            $des = $data[0]['agent_name'] . '(' . $data[0]['ip'] . ')';
        } else {
            $flag = false;
            $des = xphp_get_lang('WEB_PLATFORM_PUBLIC_NONE');
        }

        return ['flag' => $flag, 'des' => $des];
    }

    /**
     * 获取卷cdp任务缓存配置
     * @param string task_uuid
     * @return array
     */
    public function getVolCdpTaskCacheInfo($taskuuid, $currentTaskRunningStage)
    {
        $used_str = xphp_get_lang('UI_RECOVERY_STORAGE_VALID_SIZE');  //可用空间
        $free_space_str = xphp_get_lang('UI_RECOVERY_STORAGE_USED_SIZE');  //已用空间

        $sql = "SELECT agent_memory_cache_alloc_space,agent_memory_cache_used_space,agent_cache_file_storage_path,agent_file_cache_alloc_space,agent_file_cache_used_space
                FROM cdp_vol_task_cache_info
                WHERE task_uuid = ? ";

        $data = $this->dbSelect($sql, array($taskuuid));
        $memory_cache_space = $data[0]['agent_memory_cache_alloc_space'];
        $memory_cache_used = $data[0]['agent_memory_cache_used_space'] ?? 0;
        $memory_free_space = 0;
        $memory_cache_flag = false;
        $file_cache_space = $data[0]['agent_file_cache_alloc_space'];
        $file_cache_used = $data[0]['agent_file_cache_used_space'] ?? 0;
        $file_cache_path = $data[0]['agent_cache_file_storage_path'];

        //获取当前任务是否配置回切，如果配置回切且任务阶段处于回切阶段需要获取回切的相关缓存配置，其他任务阶段获取任务配置的缓存配置
        if (
            $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE', 'FAILBACK_INIT_SYNC')
            || $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE', 'FAILBACK_REALTIME_SYNC')
            || $currentTaskRunningStage == xphp_get_desc('Volcdp', 'TASK_RUNNING_STAGE', 'FAILBACK_IN_STARTING')
        ) { //任务处于回切阶段
            $fbSql = "SELECT memory_cache_alloc_space,file_cache_alloc_space,file_cache_storage_path 
                FROM cdp_vol_task_takeover_failback_info  WHERE task_uuid = ? ";

            $fbData = $this->dbSelect($fbSql, array($taskuuid));
            $memory_cache_space = $fbData[0]['memory_cache_alloc_space'];  //回切配置的内存缓存空间
            $file_cache_space = $fbData[0]['file_cache_alloc_space'];  //回切配置文件缓存空间
            $file_cache_path = $fbData[0]['file_cache_storage_path'];  //回切配置的文件缓存路径
        }

        $info = array();
        $info['agent_memory_cache_des'] = "--";
        $memoryCacheUsedStr = v1_calsize($memory_cache_used);
        if ($memoryCacheUsedStr == "--") {
            $memoryCacheUsedStr = "0B";
        }
        if ($memory_cache_space != 0) {
            $memory_free_space = $memory_cache_space - $memory_cache_used;
            $info['agent_memory_cache_des'] = $used_str . " " . v1_calsize($memory_free_space) . " / " . $free_space_str . " " . $memoryCacheUsedStr;
            $memory_cache_flag = true;
        }

        $file_free_space = 0;
        if ($file_cache_space != 0) {
            $file_free_space = $file_cache_space - $file_cache_used;
        }
        $fileCacheUsedStr = v1_calsize($file_cache_used);
        if ($fileCacheUsedStr == "--") {
            $fileCacheUsedStr = "0B";
        }
        $info['agent_file_cache_des'] = $used_str . " " . v1_calsize($file_free_space) . "/" . $free_space_str . " " . $fileCacheUsedStr;
        $info['file_cach_path'] = $file_cache_path;
        $info['memory_cache_flag'] = $memory_cache_flag;
        return $info;
    }

    /**
     * 获取卷cdp任务IO复制模式
     * @param int io_mode
     * @return IO mode string
     */
    private function getVolCdpTaskIoMode($io_mode)
    {
        $IoModeStr = xphp_get_desc('Volcdp', 'IoReplicationMode')[$io_mode]; //Io复制模式
        return $IoModeStr;
    }

    /**
     * 获取自动接管相关配置
     * @param task_uuid,auto_takeover_flag,task_type
     * @retrun array
     */
    public function getAutoTakeoverConfInfo($task_uuid, $auto_takeover_flag, $task_type)
    {
        $take_info = array();
        if ($auto_takeover_flag == 1 && xphp_get_config('task', 'TASKTYPE')['BACKUP']) {
            try {
                $sql = "select takeover_standby_agent_uuid,agent_failback_standby_ip,app_takeover_flag,app_consecutive_failure_num,
                        app_fault_detection_interval,agent_heartbeat_failure_time,takeover_vm_hypervisor
                    from cdp_vol_task_takeover_info
                    where task_uuid = ? and takeover_type = ? ";
                $data = $this->dbSelect($sql, array($task_uuid, $auto_takeover_flag));

                $failbackup_standby_ip = $data[0]['agent_failback_standby_ip'];
                $app_takeover_flag = $data[0]['app_takeover_flag'];
                $app_consecutive_failure_num = $data[0]['app_consecutive_failure_num']; //App连续失败次数
                $app_fault_detection_interval = $data[0]['app_fault_detection_interval']; //App故障检测间隔时间
                $takeoverAgentType = $data[0]['takeover_vm_hypervisor'];  //虚拟机模块定义
                $heartbeatFailureTime = $data[0]['agent_heartbeat_failure_time'];
                $consoleUrl = "";
                $tempStatus = 0;
                if ($takeoverAgentType == xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {
                    $standby_agent = $this->getHostInfo($data[0]['takeover_standby_agent_uuid']);
                    $take_info['standby_agent'] = $standby_agent; //接管备机
                } else if ($takeoverAgentType != xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {  //内嵌虚拟机
                    $sql = "select name,console_url,status from vm_emd where uuid = ? ";
                    $data = $this->dbSelect($sql, array($data[0]['takeover_standby_agent_uuid']));
                    $take_info['standby_agent'] = $data[0]['name'] . "(" . xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC') . ")"; //接管备机
                    $consoleUrl = $data[0]['console_url'];
                    $tempStatus = $data[0]['status'];
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
                if ($takeoverAgentType != xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {   //模板虚拟机
                    $this->updateTempAgent($task_uuid);
                }
            } catch (Exception $e) {
                $take_info = array();
            }
        }
        return $take_info;
    }

    /**
     * 获取任务监控脚本信息
     * @param string $task_uuid
     * @return script array
     */
    private function get_takeover_script($task_uuid)
    {
        $script_info = array();
        $scriptList = array();
        $sql = "select script_type,script_path,exec_type,exec_interval,trigger_fail_num from cdp_vol_task_takeover_script where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        if (!empty($data)) {
            foreach ($data as $d) {
                $script_info['script_type'] = $d['script_type'];
                $script_info['script_path'] = $d['script_path'];
                $script_info['exec_type'] = $d['exec_type'];
                $script_info['exec_interval'] = $d['exec_interval'];
                $script_info['trigger_fail_num'] = $d['trigger_fail_num'];

                $scriptList[] = $script_info;
            }
        }
        return $scriptList;
    }

    /**
     * @return void
     * 更新模板主机状态
     **/
    private function updateTempAgent($task_uuid)
    {
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

    /**
     * 获取手动接管相关配置信息
     * @param task_uuid,task_type
     * @return array
     */
    public function getHandoverConfInfo($task_uuid, $task_type)
    {
        $handOverConf = array();
        if (xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER'] == $task_type) {
            try {
                $sql = "select takeover_standby_agent_uuid,agent_failback_standby_ip,app_takeover_flag,takeover_timestamp,
                        master_agent_ip_switch_flag,takeover_vm_config,takeover_agent_role,takeover_vm_hypervisor  
                     FROM cdp_vol_task_takeover_info 
                    where  task_uuid = ? and takeover_type = ?";
                $data = $this->dbSelect($sql, array($task_uuid, xphp_get_desc('Volcdp', 'TAKEOVER_TYPE')['HAND_OVER']));
                $failbackup_standby_ip = $data[0]['agent_failback_standby_ip'];
                $app_takeover_flag = $data[0]['app_takeover_flag'];
                $tempInfo = $this->getTempAgentConsoleUrl($data[0]['takeover_standby_agent_uuid']);
                $takeoverStandbyAgentuuid = $data[0]['takeover_standby_agent_uuid'];
                $takeoverAgentType = $data[0]['takeover_vm_hypervisor'];
                if ($takeoverAgentType != xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {
                    $takeoverVmConfig = $data[0]['takeover_vm_config'];
                    $vmConfig = json_decode($takeoverVmConfig);
                    $standby = $vmConfig->vm_name;
                    $standbyAgent = $standby . "(" . xphp_get_lang('UI_VOL_CDP_TAKE_TEMP_AGENT_DESC') . ")";
                } else {
                    $sqlAgent = "SELECT hostname,ip,agent_name FROM bd_agent where agent_uuid = ?";
                    $dataAgent = $this->dbSelect($sqlAgent, array($takeoverStandbyAgentuuid));
                    $hostName = $dataAgent[0]['hostname'];
                    $ip = $dataAgent[0]['ip'];
                    $agentName = $dataAgent[0]['agent_name'];
                    $standbyAgent = $this->agentStr($agentName, $hostName, $ip);
                }

                $handOverConf['failbackup_ip'] = $failbackup_standby_ip;
                $handOverConf['standby_agent'] = $standbyAgent; //接管备机
                $handOverConf['app_takeover_flag'] = $app_takeover_flag; //是否启用自动接管
                $handOverConf['script_info'] = $this->get_takeover_script($task_uuid);
                $handOverConf['takeover_timestamp'] = $data[0]['takeover_timestamp'];
                $handOverConf['master_ip_switch'] = $data[0]['master_agent_ip_switch_flag'];
                $handOverConf['temp_console_url'] = $tempInfo['console_url'];
                $handOverConf['temp_status'] = $tempInfo['status'];
                $handOverConf['takeover_agent_type'] = $takeoverAgentType;
                $handOverConf['takeover_agent_role'] = $data[0]['takeover_agent_role'];
                $handOverConf['takeover_standby_agent_uuid'] = $data[0]['takeover_standby_agent_uuid'];
                if ($takeoverAgentType != xphp_get_config('vm', 'VMHYPERVISORTYPE')['VM_HYPERVISOR_TYPE_UNKNOWN']) {   //模板虚拟机
                    $this->updateTempAgent($task_uuid);
                }
            } catch (Exception $e) {
                $handOverConf = array();
            }
        }
        return $handOverConf;
    }

    /*
     * 获取模板主机console url
     */
    private function getTempAgentConsoleUrl($uuid)
    {
        $sql = "select console_url,status from vm_emd where uuid = ?";
        $data = $this->dbSelect($sql, array($uuid));
        if (!empty($data)) {
            $consoleUrl = $data[0]['console_url'];
            $serverIp = $_SERVER['SERVER_ADDR'];
            $consoleUrl = str_replace("0.0.0.0:6080", $serverIp . '/web_console', $consoleUrl);
            $consoleUrl = 'https://' . $_SERVER['HTTP_HOST'] . ':6080/jump.html?url=' . urlencode($consoleUrl);
            $tempStatus = $data[0]['status'];
        } else {
            $tempStatus = 0;
            $consoleUrl = "";
        }
        $tempInfo = array(
            'status' => $tempStatus,
            'console_url' => $consoleUrl,
        );
        return $tempInfo;
    }


}
