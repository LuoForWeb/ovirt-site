<?php

namespace app\v1\complete_machine_volcdp\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Base;
use app\v1\common\logic\Recover;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Client;
use xphp\BLLHandler;

class CmRecover extends Base
{

    /**
     * 创建恢复任务
     */
    public function createRecoverJob($params)
    {
        $taskname = htmlspecialchars_decode($params['task_name']);
        $moduletypeArr = xphp_get_config('module', 'MODULE_TYPE');
        $moduletype = $moduletypeArr['VOL_CDP'];
        $recoveryposition = xphp_get_config('task', 'RECOVERY_POSITION')['ORIGINAL'];
        $recoverytimetype = intval($params['typeInfo']['type']);
        $timestrategylist = (new Recover())->groupRecoverTimeList($params['typeInfo']);
        $transportstrategy = (new Backup())->groupTransportStrategy($params['typeInfo']['high']['trasfer']);
        //组合时间策略
        $pfMsg = (new BLLHandler())->pfCreateRecoveryTaskMessage(
            $taskname,
            $moduletype,
            $recoveryposition,
            $recoverytimetype,
            $timestrategylist,
            $transportstrategy
        );
        // 获取任务名称
        $pfMsg['task_name'] = htmlspecialchars_decode($params['task_name']);
        // 获取模块类型
        $pfMsg['module_type'] = xphp_get_config('module','MODULE_TYPE')['VOL_CDP'];
        // 获取任务类型
        $pfMsg['task_type'] = xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY'];
        //组合通用策略---------------------------
        $params['transport_strategy']['encrypt_flag'] = v1_parse_bool_to_flag($params['transport_strategy']['encrypt_flag']);
        $params['transport_strategy']['compress_flag'] = v1_parse_bool_to_flag($params['transport_strategy']['compress_flag']);
        $pfMsg['transport_strategy'] = $params['transport_strategy'];
        //组合限速策略
        $pfMsg['speed_limit_strategy'] = $this->groupTaskSpeedGlobalList($params['speed_limit_strategy']);
        $pfMsg['strategy_group_uuid'] = '';
        $pfMsg['storage_uuid' ] = $params['storage_uuid'];
        $pfMsg['node_uuid' ] = $params['node_uuid'];
        $pfMsg['backup_server_ip' ] = $params['backup_server_ip'];
        $pfMsg['safe_config_strategy'] = $params['safe_config_strategy'];
        $pfMsg['script_list'] = $params['script_list'];
        $pfMsg['dev_type'] = $params['dev_type'];
        $pfMsg['master_agent_uuid'] = $params['master_agent_uuid'];
        $pfMsg['restore_mode'] = $params['restore_mode'];
        $pfMsg['restore_data_source'] = $params['restore_data_source'];
        $pfMsg['recovery_target_agent_uuid'] = $params['recovery_target_agent_uuid'];
        $pfMsg['rebuild_partition_flag'] = $params['rebuild_partition_flag'];
        $pfMsg['thread_num'] = $params['thread_num'];
        $params['recovery_object']['restore_ip_change_flag'] = v1_parse_bool_to_flag($params['recovery_object']['restore_ip_change_flag']);
        $pfMsg['recovery_object'] = $params['recovery_object'];
        $pfMsg['recovery_position'] = $params['recovery_position'];
        $pfMsg['timepoint_uuid'] = $params['timepoint_uuid'];
        $msg = json_encode($pfMsg);
        //发送消息
        //得到恢复创建操作码
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $mbResult = $this->mbVolCdpMsg($params['node_uuid'], $opName, $msg);
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
     * 获取选中客户端可供恢复的时间区间
     * @param  array
     */
    public function ClientBackupSetTimeRange($params)
    {
        $user_uuid = xphp_get_user_info()['userUuid'];
        $agent_uuid = $params['agent_uuid'];
        $createTaskType = $params['task_type'];
        $taskuuid = $params['task_uuid'];
        $firsTime = $this->getClientFirstTime($params);
        $newTime = $this->getClientNewTime($params);

        $data = $this->getAgentData($agent_uuid,$createTaskType,$user_uuid,$taskuuid);

        $srcEndTimeStamp = $data[0]['src_end_timepoint'];
        $timestamp = date('Y-m-d H:i:s',$srcEndTimeStamp);
        if($newTime=="--"){
            $newTime = $timestamp;
        }else if($newTime<$timestamp){
            $newTime = $timestamp;
        }
        $list = array();
        $list[] = array(
            'time_range' => $firsTime." -- ".$newTime
        );
        return $list;
    }

    /**
     *
     */
    public function getClientBackupSetNewTime($params)
    {
        $user_uuid = xphp_get_user_info()['userUuid'];
        $agent_uuid = $params['agent_uuid'];
        $createTaskType = $params['task_type'];
        $taskuuid = $params['task_uuid'];

        $list = array();

        $newTime = $this->getClientNewTime($params);
        $data = $this->getAgentData($agent_uuid,$createTaskType,$user_uuid,$taskuuid);
        $srcEndTimeStamp = $data[0]['src_end_timepoint'];
        $timestamp = date('Y-m-d H:i:s',$srcEndTimeStamp);

        if($data[0]['encrypted_flag'] == xphp_get_config('app','FLAG')['SET']) { // 时间点加锁
            $encryptedFlag = true;
        }else {
            $encryptedFlag = false;
        }
        $detail = json_decode($data[0]['detail'],true);
        $passwordAutoFlag = intval($detail['password_auto_flag'])==xphp_get_config('app','FLAG')['SET'] ? true: false;
        if($newTime=="--"){
            $newTime = $timestamp;
        }else if($newTime<$timestamp){
            $newTime = $timestamp;
        }

        $list[] = array(
            'new_timestamp' => $newTime,
            'newTime' =>$newTime,
            'timestamp'=>$timestamp,
            'storage_location' => $data[0]['storage_location'],
            'storage_uuid' => $data[0]['storage_uuid'],
            'node_uuid' => $data[0]['node_uuid'],
            'timepoint_uuid' => $data[0]['timepoint_uuid'],
            'encrypted_flag' => $encryptedFlag,
            'password_auto_flag' => $passwordAutoFlag,
        );
        return $list;
    }

    /**
     * 获取客户端备份时间轴对应的数据
     */
    public function getAgentTimelineData($params)
    {
        $agentUuid = $params['agent_uuid'];
        $timeIntervalType = $params['time_interval'];
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];
        $taskuuid = $params['task_uuid'];
        $timeLineData = array();
        $records = array();
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看
        $useruuid = xphp_get_user_info()['userUuid'];

        if(
            $timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_TEN_MIN']
            || $timeIntervalType==xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_ONE_HOUR']
        ){
            $sql = "select se.id,se.backup_timestamp 
                    from cdp_vol_backup_agent_second_level_data_flow se,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                    where agent.master_agent_uuid = ? 
                    and agent.id = se.backup_agent_id 
                    and agent.storage_location!=? 
                    and bbt.timepoint_uuid = agent.timepoint_uuid 
                    and bbt.task_uuid = ?";
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid ='{$useruuid}' ";
            }

            $sql .= " and agent.storage_location!=? ";
            $sqlParams = array(
                $agentUuid,
                xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],
                $taskuuid,
                xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']
            );

            $latestTimeSql = $sql ." order by se.id  desc limit 0,1";

            $data = $this->dbSelect($latestTimeSql,$sqlParams);
            if($startTime) {
                $records["time_data"]  = $this->getSecondLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType,$taskuuid);
            } else {
                if(!empty($data)){
                    $latestTime = $data[0]['backup_timestamp'];
                    $records["time_data"]  = $this->getSecondLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid);
                } else{
                    $records["time_data"] =[];
                }
            }
        }else if($timeIntervalType==xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_ONE_DAY'] || $timeIntervalType ==xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_SEVEN_DAY']){
            $sql = "select mi.id,mi.backup_end_timestamp 
                    from cdp_vol_backup_agent_minute_level_data_flow mi,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                    where agent.master_agent_uuid = ? 
                    and agent.id = mi.backup_agent_id 
                    and agent.storage_location!=? 
                    and bbt.timepoint_uuid = agent.timepoint_uuid 
                    and bbt.task_uuid = ?";
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = '{$useruuid}' ";
            }
            $sql.= " and agent.storage_location!=? ";
            $sqlParams = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']);

            $latestTimeSql = $sql. " order by mi.id  desc limit 0,1";
            $data = $this->dbSelect($latestTimeSql,$sqlParams);
            if($startTime) {
                $records["time_data"]  = $this->getMinuteLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType,$taskuuid);
            } else {
                if(!empty($data)){
                    $latestTime = $data[0]['backup_end_timestamp'];
                    $records["time_data"]  = $this->getMinuteLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid);
                }else{
                    $records["time_data"] =[];
                }
            }
        }else if($timeIntervalType==xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_THIRTY_DAY'] || $timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_NINETY_DAY']){
            $sql = "select hour.id,hour.backup_end_timestamp 
                    from cdp_vol_backup_agent_hour_level_data_flow hour,cdp_vol_backup_agent agent,bd_backup_timepoint bbt        
                    where agent.master_agent_uuid = ? 
                    and agent.id = hour.backup_agent_id 
                    and agent.storage_location!=? 
                    and bbt.timepoint_uuid = agent.timepoint_uuid 
                    and bbt.task_uuid = ?";
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = '{$useruuid}' ";
            }
            $sql.= " and agent.storage_location!=? ";
            $sqlParams = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']);

            $latestTimeSql = $sql." order by hour.id desc limit 0,1";
            $data = $this->dbSelect($latestTimeSql,$sqlParams );
            if($startTime) {
                $records["time_data"]  = $this->getHourLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType);
            } else {
                if(!empty($data)){
                    $latestTime = $data[0]['backup_end_timestamp'];
                    $records["time_data"]  = $this->getHourLevelData($latestTime,$agentUuid,$timeIntervalType);
                }else{
                    $records["time_data"] =[];
                }
            }

        }
        return $records;
    }

    /**
     * 获取恢复目标主机
     * @param $params
     * @return array|false|string
     */
    public function getRecoveryTargetHost($params)
    {
        $masterOsType = $params['master_os_type'];
        $standbyuuid = $params['standby_uuid'];
        $agentuuidArr = (new Client())->getClientUuids();
        $list = array();
        if (empty($agentuuidArr)) {
            return json_encode($list);
        }
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,agent_type,net_model
                from bd_agent
                where  (agent_type =?)";

        if (is_array($agentuuidArr)) {
            $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
            $sql .=" and agent_uuid in {$agentuuidArrStr} ";
        }

        // 权限判断
        if(v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'agent_uuid');
            $sql .= " AND ($resourceUuidSql) ";
        }
        $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE','resources')['NORMAL']);
        $data = $this->dbSelect($sql,$sqlParams);
        $list[] = array(
            "uuid" => "",
            "text" => xphp_get_lang('UI_CM_VOL_CDP_RECOVERY_PLEASE_SELECT_RECOVERY_TARGET_HOST'),
            "value" => 0,
            "os_type" => "",
            "in_task" => false,
            "task_name" => "",
            "vol_info" => [],
        );
        foreach ($data as $host_info){
            //如果节点状态正常
            $agentUUID = $host_info['agent_uuid'];
            $agentType = $host_info['agent_type'];
            $osType = $host_info['os_type'];
            $onlineFlag = $host_info['online_flag'];

            // 排除离线
            if(xphp_get_config('app','FLAG')['UNSET'] == $onlineFlag){
                continue;
            }

            if($agentType!=xphp_get_config('client', 'AGENT_TYPE','resources')['MEMORY_OS'] && $osType!=$masterOsType){
                continue;
            }
            $inTask = false;
            $task_name = " ";
            $isCreateTask = $this->taskExist($agentUUID);
            if(count($isCreateTask)>0){
                $task_name = $isCreateTask['task_name'];
                $inTask = true;
            }

//            $hostVolInfo = $this->getHostVolInfoByUUID($agentUUID,$osType);

            $title = xphp_get_lang('UI_VOL_CDP_HOST');
            $hostDec = $this->agentStr($host_info['agent_name'],$host_info['hostname'],$host_info['ip']);
            if($host_info['agent_type']==xphp_get_config('client','AGENT_TYPE','resources')['MEMORY_OS']){
                $title = xphp_get_lang('UI_VOL_CDP_STANDBY');
            }
            if(xphp_get_config('app','FLAG')['SET'] == $onlineFlag){
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_ONLINE');
            }else{
                $statusDes = xphp_get_lang('WEB_AGENT_STATUS_OFFLINE');
                $hostDec = $hostDec."--".$statusDes;
            }

            $list[] = array(
                'uuid' => $agentUUID,
                'text' => $hostDec,
                'value' => $host_info['agent_uuid'],
                'os_type' => $osType,
                'in_task' => $inTask,
                'task_name' => $task_name,
                'agent_type' => $host_info['agent_type'],
                'net_model' => intval($host_info['net_model']),
                'title' => $title,
            );
        }
        return $list;
    }

    /**
     * 获取网卡信息
     * @param $params
     * @return array
     *
     */
    public function getNetworkCardInfo($params)
    {
        $sql = "select detail 
                from bd_agent 
                where agent_uuid = ?";
        $data = $this->dbSelect($sql,array($params['agent_uuid']));
        $info = array();
        $info['rows'] = array();
        $count = 0;
        foreach ($data as $d){
            $count++;
            $detail = json_decode($d['detail'],true);
            $info['rows'][] = array(
                'name' => $detail['nic_list'][0]['name'],
                'mac_address' => $detail['nic_list'][0]['mac_address'],
            );
        }
        $info['total'] = $count;
        return $info;
    }

    /**
     * 获取整机恢复目标主机
     * @return array
     */
    public function getRecoveryTargetMachine()
    {
        $sql = "SELECT ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.agent_type
                FROM bd_agent ba
                WHERE ba.agent_type = ? 
                AND ba.online_flag = ?
                AND NOT EXISTS (
                SELECT 1
                FROM cdp_vol_task cvt
                WHERE cvt.master_agent_uuid = ba.agent_uuid
                OR cvt.standby_agent_uuid = ba.agent_uuid
                )
                AND NOT EXISTS (
                SELECT 1
                FROM cdp_vol_task_takeover_info cvtti
                WHERE cvtti.takeover_standby_agent_uuid = ba.agent_uuid
                )
                AND NOT EXISTS (
                SELECT 1
                FROM cdp_vol_task_takeover_failback_info cvttfi
                WHERE cvttfi.failback_target_agent_uuid = ba.agent_uuid
                )";
        // 权限判断
        if(v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'agent_uuid');
            $sql .= " AND ($resourceUuidSql) ";
        }
        $data = $this->dbSelect($sql,array(xphp_get_config('client', 'AGENT_TYPE','resources')['MEMORY_OS'],xphp_get_config('app','FLAG')['SET']));
        $info = array();
        $info[] = array(
            'agent_uuid' => "",
            'text' => xphp_get_lang('UI_CM_VOL_CDP_RECOVERY_PLEASE_SELECT_RECOVERY_TARGET_HOST')
        );
        if(!empty($data)){
            foreach ($data as $d){
                $info[] =  array(
                    'agent_uuid' => $d['agent_uuid'],
                    'agent_name' => $d['agent_name'],
                    'hostname' => $d['hostname'],
                    'ip' => $d['ip'],
                    'text' => $d['hostname'].'('.$d['ip'].')',
                    'agent_type' => $d['agent_type']
                );
            }
        }
        return $info;
    }

    /**
     * 获取CPU配置
     */
    public function getHardwareConfig($params = [])
    {
        $taskType = $params['task_type'];
        if($taskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_BACKUP']){
            // 备份
            $sql = "select other_detail_hardware_info from bd_agent where agent_uuid = ?";
            $data = $this->dbSelect($sql,array($params['agent_uuid']));
            $detail = json_decode($data[0]['other_detail_hardware_info'],true);
            $info = array(
                'slots_num' => $detail['cpu_socket'],
                'slot_core_num' => $detail['cpu_core'],
                'mem_total_size' => v1_calsize_to_value_and_unit(intval($detail['memory_size']),false),
                'mem_total_size_value' => $detail['memory_size'],
                'boot_type' => $detail['boot_type']
            );
        }else{
            // 恢复  or 接管
            $sql = "select master_agent_detail from cdp_vol_backup_agent where master_agent_uuid = ? and timepoint_uuid = ?";
            $data = $this->dbSelect($sql,array($params['agent_uuid'],$params['timepoint_uuid']));
            $detail = json_decode($data[0]['master_agent_detail'],true);
            $info = array(
                'slots_num' => $detail['cpu_socket'],
                'slot_core_num' => $detail['cpu_core'],
                'mem_total_size' => v1_calsize_to_value_and_unit(intval($detail['memory_size']),false),
                'mem_total_size_value' => $detail['memory_size'],
                'boot_type' => $detail['system_boot_type']
            );
        }
        return $info;
    }

    # ———————————————————————————— 以下是私有方法 ————————————————————————————
    /**
     * 获取客户端最早备份时间点
     * @param $params
     * @return string
     */
    private function getClientFirstTime($params)
    {
        $user_uuid = xphp_get_user_info()['userUuid'];
        $node_uuid = $params['node_uuid'];
        $agent_uuid = $params['agent_uuid'];
        $createTaskType = $params['task_type'];
        $taskuuid = $params['task_uuid'];
        $firsTime = '--';
        if($createTaskType = xphp_get_config('task')['TASKTYPE']['VOL_CDP_TAKEOVER']){
            $firstSql = "select bbt.timepoint,bbt.timepoint_uuid
                         from cdp_vol_backup_agent agent,bd_backup_timepoint bbt
                         where agent.timepoint_uuid = bbt.timepoint_uuid 
                         and agent.master_agent_uuid =? 
                         and bbt.user_uuid = ?
                         and agent.storage_location !=? 
                         and bbt.task_uuid =? 
                         order by bbt.timepoint limit 0,1";
            $firstData = $this->dbSelect(
                $firstSql,
                array(
                    $agent_uuid,$user_uuid,
                    xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],
                    $taskuuid
                )
            );
        }else{
            $firstSql = "select bbt.timepoint,bbt.timepoint_uuid
                         from cdp_vol_backup_agent agent,bd_backup_timepoint bbt
                         where agent.timepoint_uuid = bbt.timepoint_uuid 
                         and agent.master_agent_uuid =? 
                         and bbt.user_uuid = ?
                         and agent.storage_location !=? 
                         and agent.storage_location !=? 
                         and bbt.task_uuid =? 
                         order by bbt.timepoint limit 0,1";
            $firstData = $this->dbSelect(
                $firstSql,
                array(
                    $agent_uuid,
                    $user_uuid,
                    xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],
                    xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],
                    $taskuuid
                )
            );
        }
        if(!empty($firstData)){
            $firsTime = $firstData[0]['timepoint'];
        }
        return $firsTime;
    }

    /**
     * 获取客户端最新时间戳
     * @param $params
     * @return string
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
                where vol.backup_agent_id = agent.id 
                and agent.master_agent_uuid = ? 
                and agent.storage_location !=? 
                and agent.timepoint_uuid = bbt.timepoint_uuid 
                and bbt.task_uuid = ? ";
        if($createTaskType = xphp_get_config('task')['TASKTYPE']['VOL_CDP_TAKEOVER']) {
            $sql = $sql." and agent.storage_location =? ORDER BY vol.end_timestamp desc LIMIT 1";
        } else{
            $sql = $sql." and agent.storage_location !=? ORDER BY vol.end_timestamp desc LIMIT 1";
        }
        $data = $this->dbSelect(
            $sql,
            array(
                $agent_uuid,
                xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],
                $taskuuid,
                xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']
            )
        );
        if(!empty($data)){
            $newTime = $data[0]['end_timestamp'];
        }
        return $newTime;
    }

    /**
     * 公共方法
     */
    private function getAgentData($agent_uuid,$createTaskType,$user_uuid,$taskuuid)
    {
        $sql = "select bbt.timepoint_uuid,bbt.src_end_timepoint,agent.storage_location,st.storage_uuid,
                st.node_uuid,bbt.encrypted_flag,bbt.detail,agent.master_agent_detail 
                from cdp_vol_backup_agent as agent,bd_backup_timepoint as bbt,bd_storage_resource as st 
                where st.storage_uuid = bbt.storage_uuid 
                and agent.timepoint_uuid = bbt.timepoint_uuid 
                and agent.master_agent_uuid = ? 
                and bbt.user_uuid = ? 
                and agent.storage_location !=? 
                and bbt.task_uuid = ? ";

        if($createTaskType == xphp_get_config('task')['TASKTYPE']['VOL_CDP_TAKEOVER']){
            $sql = $sql."   ORDER BY agent.id DESC LIMIT 0,1";
            $data = $this->dbSelect(
                $sql,
                array(
                    $agent_uuid,
                    $user_uuid,
                    xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],
                    $taskuuid
                )
            );
        }else{
            $sql = $sql." and agent.storage_location !=? ORDER BY agent.id DESC LIMIT 0,1";
            $data = $this->dbSelect(
                $sql,
                array(
                    $agent_uuid,
                    $user_uuid,
                    xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],
                    $taskuuid,
                    xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']
                )
            );
        }
        return $data;
    }

    private function  getSecondLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType,$taskuuid)
    {
        $timeIntervalValue = strtotime($endTime) - strtotime($startTime);  // 最多一个小时 3600点
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType, $endTime, $timeIntervalValue);  // 获取时间间隔周期默认的dataList
        $sql = "select se.backup_timestamp,se.data_flow,se.label_point_count,se.event_point_count
                from cdp_vol_backup_agent_second_level_data_flow se,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                where se.backup_timestamp 
                between '". $startTime ."' 
                and '". $endTime ."' 
                and agent.master_agent_uuid = ? 
                and agent.id = se.backup_agent_id 
                and agent.storage_location!=? 
                and agent.timepoint_uuid = bbt.timepoint_uuid 
                and bbt.task_uuid = ? ";
        $data = $this->dbSelect($sql, array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid));
        if(!empty($data)){
            //获取当前时间，减去最新时间，获取二维数组下标位置
            foreach ($data as $d){
                $timeStr = $d['backup_timestamp'];
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($endTime) - $timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
        return $bakcupFlowData;
    }

    /**
     * 通过当前选择的时间区间类型及当前客户端可供配置的最新时间，计算默认data list
     * @param 最新时间  $latestTime
     * @param 时间间隔  $timeInterval
     */
    private function createDefaultTimeList($timeIntervalType,$latestTime,$timeIntervalValue)
    {
        $backupSetIntervalType = xphp_get_desc('Volcdp', 'BACKUP_SET_INTERVAL_TYPE');
        $intervalMap = [
            $backupSetIntervalType['LAST_TEN_MIN'] => 1,       // 秒
            $backupSetIntervalType['LAST_ONE_HOUR'] => 1,      // 秒
            $backupSetIntervalType['LAST_ONE_DAY'] => 60,      // 分钟
            $backupSetIntervalType['LAST_SEVEN_DAY'] => 60,    // 分钟
            $backupSetIntervalType['LAST_THIRTY_DAY'] => 3600, // 小时
            $backupSetIntervalType['LAST_NINETY_DAY'] => 3600  // 小时
        ];
        $defaultData = [];
        $latestTimeStamp = strtotime($latestTime);
        for ($i = $timeIntervalValue; $i > 0; $i--) {
            $timeOffset = $intervalMap[$timeIntervalType] * $i;
            $defaultTime = $latestTimeStamp - $timeOffset;
            $defaultTimeStr = date('Y-m-d H:i:s', $defaultTime);
            $defaultFlow = 0;
            $defaultLabelCount = 0;
            $defaultEventCount = 0;
            $defaultData[] = [
                $defaultTimeStr,            // 时间
                $defaultFlow,               // 流量
                $defaultLabelCount,         // 标签数量
                $defaultEventCount          // 事件数量
            ];
        }

        return $defaultData;
    }

    /**
     * 获取秒级表对应时间轴需要的数据
     * @param $latestTime:当前客户端最新可用时间点,$agentUuid:客户端uuid,$timeType:查询时间区间类型;
     * @return $dataTimeArray:满足条件的时间列表;
     */
    private function  getSecondLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid)
    {
        if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_TEN_MIN']){   //最近10分钟数据,客户端对应的最新时间减600秒
            $timeIntervalValue = 600;
            $timeDif = strtotime($latestTime)-$timeIntervalValue;
        }else if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_ONE_HOUR']){    //最近一小时数据,客户端对应的最新时间减3600
            $timeIntervalValue = 3600;
            $timeDif = strtotime($latestTime)-$timeIntervalValue;
        }
        $difTimeStr =  date('Y-m-d H:i:s',$timeDif); //60分钟后的时间字符串
        $bakcupFlowData = array();
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$latestTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "select se.backup_timestamp,se.data_flow,se.label_point_count,se.event_point_count
                from cdp_vol_backup_agent_second_level_data_flow se,cdp_vol_backup_agent agent,bd_backup_timepoint bbt   
                where se.backup_timestamp 
                between '". $difTimeStr ."' 
                and '". $latestTime ."' 
                and agent.master_agent_uuid = ? 
                and agent.id = se.backup_agent_id 
                and agent.storage_location!=? 
                and agent.timepoint_uuid = bbt.timepoint_uuid 
                and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid));
        if(!empty($data)){
            //获取当前时间，减去最新时间，获取二维数组下标位置
            foreach ($data as $d){
                $timeStr = $d['backup_timestamp'];
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($latestTime)-$timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
        return $bakcupFlowData;
    }

    private function getMinuteLevelDataCustom($startTime, $endTime,$agentUuid,$timeIntervalType,$taskuuid)
    {
        $timeIntervalValue = ceil((strtotime($endTime) - strtotime($startTime))/60);
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$endTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "select mi.backup_end_timestamp,mi.data_flow,mi.label_point_count,mi.event_point_count
                from cdp_vol_backup_agent_minute_level_data_flow mi,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                where mi.backup_end_timestamp 
                between '". $startTime ."' 
                and '". $endTime ."' 
                and agent.master_agent_uuid = ? 
                and agent.id = mi.backup_agent_id 
                and agent.storage_location!=? 
                and agent.timepoint_uuid = bbt.timepoint_uuid 
                and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid));
        if(!empty($data)){
            foreach ($data as $d){
                $timeStr = $d['backup_end_timestamp'];
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($endTime)-$timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber/60;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
        return $bakcupFlowData;
    }

    /**
     * 获取分钟级表对应的时间轴数据
     * @param $latestTime:当前客户端最新可用时间点,$agentUuid:客户端uuid,$timeType:查询时间区间类型;
     * @return $dataTimeArray:满足条件的时间列表;
     */
    private function getMinuteLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid)
    {
        if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_ONE_DAY']){   //最近1天数据,读取分钟级表，对应的最新时间减1440分钟
            $timeIntervalValue = 1440;
            $timeDif = strtotime($latestTime)-($timeIntervalValue*60);
        }else if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_SEVEN_DAY']){    //最近一周数据,读取分钟级表，对应的最新时间减10080分钟
            $timeIntervalValue = 10080;  //单位分钟
            $timeDif = strtotime($latestTime)-($timeIntervalValue*60);
        }
        $difTimeStr =  date('Y-m-d H:i:s',$timeDif);
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$latestTime,$timeIntervalValue); //获取时间间隔周期默认的dataList

        $dataTimeArray = array();
        $sql = "select mi.backup_end_timestamp,mi.data_flow,mi.label_point_count,mi.event_point_count
                from cdp_vol_backup_agent_minute_level_data_flow mi,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                where mi.backup_end_timestamp 
                between '". $difTimeStr ."' 
                and '". $latestTime ."' 
                and agent.master_agent_uuid = ? 
                and agent.id = mi.backup_agent_id and agent.storage_location!=? 
                and agent.timepoint_uuid = bbt.timepoint_uuid 
                and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql, array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid));
        if(!empty($data)){
            foreach ($data as $d){
                $timeStr = $d['backup_end_timestamp'];
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($latestTime)-$timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber/60;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
        return $bakcupFlowData;
    }

    private function getHourLevelDataCustom($startTime, $endTime,$agentUuid,$timeIntervalType)
    {
        $timeIntervalValue = floor((strtotime($endTime) - strtotime($startTime))/60/60);
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$endTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "select hour.backup_end_timestamp,hour.data_flow,hour.label_point_count,hour.event_point_count
                from cdp_vol_backup_agent_hour_level_data_flow hour,cdp_vol_backup_agent agent
                where hour.backup_end_timestamp 
                between '". $startTime ."' 
                and '". $endTime ."'
                and agent.master_agent_uuid = ? 
                and agent.id = hour.backup_agent_id 
                and agent.storage_location!=?";
        $data = $this->dbSelect($sql, array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']));
        if(!empty($data)){
            foreach ($data as $d){
                $timeStr = $d['backup_end_timestamp'];
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($endTime)-$timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber/60/60;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
        return $bakcupFlowData;
    }

    /**
     * 获取小时级表对应的时间轴数据
     * @param $latestTime:当前客户端最新可用时间点,$agentUuid:客户端uuid,$timeType:查询时间区间类型;
     * @return $dataTimeArray:满足条件的时间列表;
     */
    private function getHourLevelData($latestTime,$agentUuid,$timeIntervalType)
    {
        if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_THIRTY_DAY']){  //最近30天数据,读取小时级表，对应的最新时间减720小时
            $timeIntervalValue = 720;  //30天折合720小时
        }else if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_NINETY_DAY']){  //最近90天数据,读取小时级表，对应的最新时间减2160小时
            $timeIntervalValue = 2160;  //30天折合2160个小时
        }
        $intervalTimestamp = $timeIntervalValue*60*60;  //间隔时间戳
        $timeDif = strtotime($latestTime)-$intervalTimestamp; //间隔时间

        $difTimeStr =  date('Y-m-d H:i:s',$timeDif);

        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$latestTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "select hour.backup_end_timestamp,hour.data_flow,hour.label_point_count,hour.event_point_count
                from cdp_vol_backup_agent_hour_level_data_flow hour,cdp_vol_backup_agent agent
                where hour.backup_end_timestamp 
                between '". $difTimeStr ."' 
                and '". $latestTime ."'
                and agent.master_agent_uuid = ? 
                and agent.id = hour.backup_agent_id 
                and agent.storage_location!=?";
        $data = $this->dbSelect($sql, array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']));
        if(!empty($data)){
            foreach ($data as $d){
                $timeStr = $d['backup_end_timestamp'];
                $timestamp = strtotime($timeStr);
                $indexDifNumber = strtotime($latestTime)-$timestamp;
                $listIndex = $timeIntervalValue-$indexDifNumber/60/60;
                $dataFlow = round($d['data_flow']/1024,2);
                $bakcupFlowData[$listIndex][0] = $timeStr;
                $bakcupFlowData[$listIndex][1] = $dataFlow;
                $bakcupFlowData[$listIndex][2] = $d['label_point_count'];
                $bakcupFlowData[$listIndex][3] = $d['event_point_count'];
            }
        }
        return $bakcupFlowData;
    }

    /**
     * 检查选择的恢复目标是否有被其它恢复任务选中
     * -----当前仅限制是否有恢复作业，后续会完善到是否有备份作业、恢复作业是否处于运行中等
     * @param unknown $agentUuids
     */
    private function taskExist ($agentUUID)
    {
        $sql = "select task.task_status,task.task_name 
                from bd_task task,cdp_vol_task vol_task
                where vol_task.task_uuid = task.task_uuid 
                and task.task_type = ? 
                and vol_task.recovery_target_agent_uuid = ? 
                and task.delete_flag = ? ";
        $taskType = xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY'];
        $delFlag = xphp_get_config('app', 'FLAG')['UNSET'];
        $data = $this->dbSelect($sql,array($taskType,$agentUUID,$delFlag));
        $taskList = array();
        if(!empty($data)){
            $taskList = array(
                'task_name' => $data[0]['task_name'],
            );
        }
        return $taskList;
    }

    /**
     * 获取筛选主机卷信息
     * @param：agent_uuid
     */
    private function getHostVolInfoByUUID($uuid,$osType)
    {
        //获取客户端类型
        $agentSql = "select agent_type 
                     from bd_agent 
                     where agent_uuid = ? ";
        $data = $this->dbSelect($agentSql, array($uuid));
        $agentType = 1;
        if(!empty($data)){
            $agentType = $data[0]['agent_type'];
        }

        $sql = "select vol.vol_name,vol.vol_uuid,vol.capacity,vol.free_space,vol.is_boot,vol.display_name,vol.mount_point,vol.detail
                from bd_agent_disk as disk,bd_agent_vol as vol
                where vol.disk_uuid = disk.disk_uuid 
                and disk.agent_uuid = ?";
        $data = $this->dbSelect($sql,array($uuid));
        $list = array();
        $HostMountPointArry = array();
        foreach ($data as $v){
            $detail = json_decode($v['detail']);
            $mountPoint = $v['mount_point'];
            $volName = $v['vol_name'];
            $volTypeValue = $detail->vol_type;
            $canSelect = true;

            //保留分区、恢复分区、pv分区、扩展分区
            if($volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_WIN_RESERVE_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_WIN_RECOVERY_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_PV_VOLUME']
                || $volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_EXTEND_VOLUME']){
                continue;
            }
//            if($mountPoint =="[SWAP]"){
//                continue;
//            }
            if($volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_SWAP_VOLUME']){
                continue;
            }
            //内存操作系统的内置分区，如 live 等,以lvice-去过滤
            $pos = strstr($volName,"live-");
            if($pos !==false){
                continue;
            }
            if(($volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_BOOT_VOLUME'] || $volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_SYSTEM_VOLUME']
                    || $volTypeValue & xphp_get_desc('Volcdp', 'VOL_TYPE')['BD_EFI_VOLUME']) && $agentType!=xphp_get_config('client', 'AGENT_TYPE','resources')['MEMORY_OS']) {
                $canSelect = false;
            }

            array_push($HostMountPointArry,$v['mount_point']);
            $list[] = array(
                "uuid" => $v['vol_uuid'],
                "capacity_value" => $v['capacity'],
                "capacity" =>v1_calsize($v['capacity'], true),
                "free_space" => $v['free_space'],
                "is_boot" => $v['is_boot'],
                "display_name" => $v['display_name'],
                "agent_type" => $agentType,
                "mount_point" => $mountPoint,
                'can_select' => $canSelect,
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
     * 获取主机磁盘信息
     */
    private function getHostDiskInfo($uuid)
    {
        $sql = "select disk_uuid,capacity,free_space,display_name,detail 
                from bd_agent_disk 
                where agent_uuid = ?";
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
                "capacity" =>v1_calsize($v['capacity'], true),
                "free_space" => $v['free_space'],
                "display_name" => $v['display_name']
            );
        }
        return $list;
    }

    /**
     * 组合全局限速策略
     * @param array $speedList 限速策略
     * @return array
     */
    public function groupTaskSpeedGlobalList($speedList)
    {
        $info = [
            'task_priority' => $speedList['level'], // 任务级别
            'remark' => ''
        ];
        if ($speedList['type'] == 1) {
            // 全局策略
            if (empty($speedList['uuid'])) {
                return [];
            }
            $info['strategy_uuid'] = $speedList['uuid'];
            $info['strategy_name'] = $speedList['name'];
            $info['strategy_type'] = $speedList['strategy_type'];
            $info['is_global'] = 1;
            $info['speed_limit_info'] = [];
            $info['extra_info'] = '';
        } else {
            // 自定义
            $info['strategy_uuid'] = '';
            $info['strategy_name'] = '';
            $info['is_global'] = 0;
            foreach ($speedList['speedInfo'] as &$speedItem) {
                $speedItem['startTime'] = v1_formart_time($speedItem['startTime']);
                $speedItem['endTime'] = v1_formart_time($speedItem['endTime']);
            }
            $info['extra_info'] = json_encode($speedList['speedInfo']);
            // 查询出type和组装下策略
            if (count($speedList['speedInfo'])) {
                $strategytype = $speedList['speedInfo'][0]['type'];
                $info['strategy_type'] = $strategytype;
                if (in_array($strategytype, [1, 4, 5])) {
                    // 这三个类型的没有days的值
                    $timelist = [];
                    foreach ($speedList['speedInfo'] as $item) {
                        $timelist[] = [
                            'start_time' => $strategytype != 5 ? v1_formart_time($item['startTime']) : $item['startTime'],
                            'end_time' => $strategytype != 5 ? v1_formart_time($item['endTime']) : $item['endTime'],
                            'speed_limited_value' => $item['value'],
                        ];
                    }
                    $speedlimitinfo[] = [
                        'days' => '',
                        'time_list' => $timelist
                    ];
                } else {
                    $speedlimitinfos = [];
                    foreach ($speedList['speedInfo'] as $item) {
                        $days = implode('', $item['days']);
                        $speedlimitinfos[$days][] = [
                            'start_time' => v1_formart_time($item['startTime']),
                            'end_time' => v1_formart_time($item['endTime']),
                            'speed_limited_value' => $item['value'],
                        ];
                    }
                    $speedlimitinfo = [];
                    foreach ($speedlimitinfos as $keys => $items) {
                        $speedlimitinfo[] = [
                            'days' => $keys,
                            'time_list' => $items,
                        ];
                    }
                }
                $info['speed_limit_info'] = $speedlimitinfo;
            } else {
                // 未定义，直接传空数组
                return [];
            }
        }
        return $info;
    }


}