<?php

namespace app\v1\complete_machine_volcdp\v0\logic;

use app\v1\common\logic\Base;
use app\v1\job\v0\logic\JobInfo;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Storage;

/**
 * note          卷实时 --数据管理 logic
 * @author       jiangyongjie@vinchin.com
 * @date         2024-7-31 17:40:28
 * @version      1.0.0
 * @copyright    Copyright 2024 vinchin.com
 */
class CmBackupSetData extends Base
{
    /**
     * 校验备份集的状态在对应的创建任务类型下是否可用
     * @param integer $storageStatus：备份集状态
     * @param integer $createTaskType：任务创建类型
     */

    /**
     * 获取选择客户端及卷对应的标签点信息
     * @param host_uuid:选择主机UUID,vol_uuid:当前选中卷
     */
    public  function getBackupSetTagPointInfo($params)
    {

        $hostUuid = $params['host_uuid'];
        $timeList = $params['time_list']; //通过时间轴查看标签点的详情
        $backupSetId = $params['backup_set_id'];

        $start = $params['offset'];
        $length = $params['limit'];
        // $draw = $params['draw'];
        $sortColumn = 0;
        $sortType = $params['order'];
        $sortArr = array('label.label_timestamp');

        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看
        $useruuid = xphp_get_user_info()['userUuid'];
        $taskuuid = $params['task_uuid'];
        if(!empty($timeList)){
            $confTime = $timeList[0]['confTime'];
            $timeInterval = $timeList[0]['timeInterval'];

            if($timeInterval==1||$timeInterval==2){ //秒级时间表
                $sql = "select label.id,label.label_timestamp,label.remarks,agent.storage_location  
                    from cdp_vol_agent_label_set as label,cdp_vol_backup_agent as agent, bd_backup_timepoint bt 
                    where agent.id = label.backup_agent_id and agent.master_agent_uuid =? and label.label_timestamp = ? 
                      and agent.timepoint_uuid = bt.timepoint_uuid and bt.task_uuid = ?";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $sql .= " and bt.user_uuid in " . $useruuidArr;
                }else{
                    $sql .= " and bt.user_uuid = '{$useruuid}' ";
                }
                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

                $sqlParams = array($hostUuid,$confTime,$taskuuid,$start, $length);
                $countParams = array($hostUuid,$confTime,$taskuuid);

                $data = $this->dbSelect($sql, $sqlParams);
                $countData = $this->dbSelect($sqlCount,$countParams);

            }else if($timeInterval==3 || $timeInterval==4){  //分钟级表
                $minDataSql = "select flow.backup_start_timestamp 
                    from cdp_vol_backup_agent_minute_level_data_flow flow ,cdp_vol_backup_agent agent,bd_backup_timepoint bt 
                    where flow.backup_agent_id = agent.id and agent.master_agent_uuid = ? and flow.backup_end_timestamp = ? 
                      and agent.timepoint_uuid = bt.timepoint_uuid and bt.task_uuid = ?";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $minDataSql .= " and bt.user_uuid in " . $useruuidArr;
                }else{
                    $minDataSql .= " and bt.user_uuid = '{$useruuid}' ";
                }

                $minData = $this->dbSelect($minDataSql,array($hostUuid,$confTime,$taskuuid));
                $startTimestamp = $minData[0]['backup_start_timestamp'];
                $sql = $this->getTimeIntervalData($startTimestamp,$confTime);
                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

                $sqlParams = array($hostUuid,$start, $length);
                $countParams = array($hostUuid);
                $data = $this->dbSelect($sql,$sqlParams);
                $countData = $this->dbSelect($sqlCount,$countParams);

            }else if($timeInterval==5 || $timeInterval==6){ //小时级表
                $hourDataSql = "select flow.backup_start_timestamp 
                    from cdp_vol_backup_agent_hour_level_data_flow flow ,cdp_vol_backup_agent agent,bd_backup_timepoint bt 
                    where flow.backup_agent_id = agent.id and agent.master_agent_uuid = ? and flow.backup_end_timestamp = ? 
                      and agent.timepoint_uuid = bt.timepoint_uuid and bt.task_uuid = ?";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $hourDataSql .= " and bt.user_uuid in " . $useruuidArr;
                }else{
                    $hourDataSql .= " and bt.user_uuid = '{$useruuid}' ";
                }
                $minData = $this->dbSelect($hourDataSql,array($hostUuid,$confTime,$taskuuid));
                $startTimestamp = $minData[0]['backup_start_timestamp'];

                $sql = $this->getTimeIntervalData($startTimestamp,$confTime);
                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

                $sqlParams = array($hostUuid,$start, $length);
                $countParams = array($hostUuid);

                $data = $this->dbSelect($sql,$sqlParams);
                $countData = $this->dbSelect($sqlCount,$countParams);
            }
        } else{
            $sql = "select label.id,label.label_timestamp,label.remarks,agent.storage_location
                from cdp_vol_agent_label_set as label,cdp_vol_backup_agent as agent,bd_backup_timepoint bt 
                where agent.id = label.backup_agent_id and agent.master_agent_uuid = ? and 
                      agent.timepoint_uuid = bt.timepoint_uuid and bt.task_uuid = ? and label.backup_agent_id = ?";

            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bt.user_uuid = '{$useruuid}' ";
            }
            $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
            $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

            $sqlParams = array($hostUuid,$taskuuid,$backupSetId,$start, $length);

            $countParams = array($hostUuid,$taskuuid,$backupSetId);
            $data = $this->dbSelect($sql, $sqlParams);
            $countData = $this->dbSelect($sqlCount,$countParams);
        }

        if (empty($data)) {
            return [
                'rows' => [],
                'total' => 0
            ];
        }
        $recodes = array();
        if(!empty($data)){
            $j = 1;
            foreach ($data as $d){
                $index_id = $j++;
                $label_id = $d['id'];
                $label_time_str = $d['label_timestamp'];
                $description = $d['remarks'];
                $storage_location = $d['storage_location'];
                $recodes[] = array(
                    'tag_point' => $label_time_str,
                    'index_id' => $index_id,
                    'remarks' => $description,
                    'storage_location' => $storage_location,
                    'label_id' => $label_id,
                );
            }
        }
        return [
            'rows' => $recodes,
            'total' => count($countData)
        ];
    }
    /**
     * 获取备份集病毒扫描时间点信息
     * @return void
     */
    public function getBackupSetSafePointInfo($params){
        $start = $params['offset'];
        $length = $params['limit'];

        $backupSetId = $params['backup_set_id'];
        // $draw = $params['draw'];
        $sortColumn = 0;
        $sortType = $params['order'];
        $sortArr = array('cvbtsi.timepoint_datetime');

        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看
        $useruuid = xphp_get_user_info()['userUuid'];
        $sql = "select cvbtsi.id,cvbtsi.timepoint_uuid,cvbtsi.timepoint_datetime,cvbtsi.virus_scan_status,cvbtsi.virus_list,cvbtsi.last_virus_scan_time,cvbtsi.verify_flag 
                from cdp_vol_backup_timepoint_safe_info cvbtsi,bd_backup_timepoint bbt,cdp_vol_backup_agent cvba 
                where bbt.timepoint_uuid = cvbtsi.timepoint_uuid and cvba.timepoint_uuid = bbt.timepoint_uuid and cvba.id = ? ";
        if (!empty($authUser)) {  // 表示有管理的用户
            $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
            $useruuidArr = "('" . implode("','", array: $useruuidArr) . "')";
            $sql .= " and bbt.user_uuid in " . $useruuidArr;
        }else{
            $sql .= " and bbt.user_uuid = '{$useruuid}' ";
        }
        $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
        $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
        $sqlParams = array($backupSetId,$start, $length);
        $countParams = array($backupSetId);

        $data = $this->dbSelect($sql,$sqlParams);
        $countData = $this->dbSelect( $sqlCount,$countParams);
        $records = array();
        if(!empty($data)){
            foreach ($data as $d){
                $eventId = $d['id'];
                $timepointUuid = $d['timepoint_uuid'];
                $timepointDatetime = $d['timepoint_datetime'];

                $virusScanStatus = $d['virus_scan_status'];
                $virusList = $d['virus_list'];

                $lastVirusScanTime = $d['last_virus_scan_time'];
                $verifyFlag = $d['verify_flag'];

                $records[] = array(
                    'timepoint_datetime' => $timepointDatetime,
                    'virus_scan_status' => $virusScanStatus,
                    'last_virus_scan_time' => $lastVirusScanTime,
                    'verify_flag' => $verifyFlag,
                    'virus_list' => $virusList,
                    'timepoint_uuid' => $timepointUuid,
                );
            }
        }
        return [
            'rows' => $records,
            'total' => count($countData)
        ];
    }

    /**
     * 获取选择任务对应客户端对应的事件信息
     * @param host_uuid:选择主机UUID,vol_uuid:当前选中卷
     */
    public function getBackupSetEventInfo($params){

        $hostUuid = $params['host_uuid'];
        $timeList = $params['time_list']; //通过时间轴查看标签点的详情
        $start = $params['offset'];
        $length = $params['limit'];

        $nodeuuid = $params['node_uuid'];
        $taskuuid = $params['task_uuid'];
        // $draw = $params['draw'];
        $sortColumn = 0;
        $sortType = $params['order'];
        $sortArr = array('event.event_time');

        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看
        $useruuid = xphp_get_user_info()['userUuid'];
        if(!empty($timeList)){
            $confTime = $timeList[0]['confTime'];
            $timeInterval = $timeList[0]['timeInterval'];
            if($timeInterval==1||$timeInterval==2){ //秒级时间表
                $sql = "select event.id,event.event_time,event.event_type,event.event_level,event.description_key,
                        event.description_param, event.event_detail,agent.storage_location 
                    from cdp_vol_agent_event_info as event,cdp_vol_backup_agent as agent,bd_backup_timepoint bbt 
                    where agent.id = event.backup_agent_id and agent.master_agent_uuid =?  and event.event_time = ? 
                      and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid =? ";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $sql .= " and bbt.user_uuid in " . $useruuidArr;
                }else{
                    $sql .= " and bbt.user_uuid = '{$useruuid}' ";
                }
                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";

                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

                $sqlParams = array($hostUuid,$confTime,$taskuuid,$start, $length);
                $countParams = array($hostUuid,$confTime,$taskuuid);

                $data = $this->dbSelect($sql,$sqlParams);
                $countData = $this->dbSelect($sqlCount,$countParams);
            }else if($timeInterval==3 || $timeInterval==4){  //分钟级表
                //获取时间范围内的开始时间
                $minDataSql = "select flow.backup_start_timestamp 
                    from cdp_vol_backup_agent_minute_level_data_flow flow ,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                    where flow.backup_agent_id = agent.id and agent.master_agent_uuid = ? and flow.backup_end_timestamp = ? 
                       and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid =? ";
                $minData = $this->dbSelect($minDataSql,array($hostUuid,$confTime,$taskuuid));
                $startTimestamp = $minData[0]['backup_start_timestamp'];

                $sql = $this->getTimeIntervalData($startTimestamp,$confTime); //组装获取事件信息的sql语句
                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

                $sqlParams = array($hostUuid,$start, $length);
                $countParams = array($hostUuid);

                $data = $this->dbSelect($sql,$sqlParams);

                $countData = $this->dbSelect($sqlCount,$countParams);

            }else if($timeInterval==5 || $timeInterval==6){ //小时级表
                $hourDataSql = "select flow.backup_start_timestamp 
                    from cdp_vol_backup_agent_hour_level_data_flow flow ,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                    where flow.backup_agent_id = agent.id and agent.master_agent_uuid = ? and flow.backup_end_timestamp = ? 
                        and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid =? ";
                $minData = $this->dbSelect($hourDataSql,array($hostUuid,$confTime,$taskuuid));
                $startTimestamp = $minData[0]['backup_start_timestamp'];

                $sql = $this->getTimeIntervalData($startTimestamp,$confTime);
                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";

                $sqlParams = array($hostUuid,$start, $length);
                $countParams = array($hostUuid);

                $data = $this->dbSelect($sql,$sqlParams);
                $countData = $this->dbSelect($sqlCount,$countParams);
            }
        }else{
            if($nodeuuid!=""){
                $sql = "select event.id,event.event_time,event.event_type,event.event_level,event.description_key,
                        event.description_param, event.event_detail 
                    from cdp_vol_agent_event_info as event,cdp_vol_backup_agent as agent, bd_backup_timepoint bt,bd_storage_resource bsr 
                    where agent.id = event.backup_agent_id and agent.master_agent_uuid= ? and agent.timepoint_uuid = bt.timepoint_uuid 
                      and bt.storage_uuid = bsr.storage_uuid and bsr.node_uuid = ? and bt.task_uuid = ?";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([Xphp::$_user['useruuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $sql .= " and bt.user_uuid in " . $useruuidArr;
                }else{
                    $sql .= " and bt.user_uuid ='{$useruuid}' ";
                }
                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
                $sqlParams = array($hostUuid,$nodeuuid,$taskuuid,$start, $length);
                $countParams = array($hostUuid,$nodeuuid,$taskuuid);
            }else{
                $sql = "select event.id,event.event_time,event.event_type,event.event_level,event.description_key,
                        event.description_param, event.event_detail
                    from cdp_vol_agent_event_info as event,cdp_vol_backup_agent as agent, bd_backup_timepoint bt
                    where agent.id = event.backup_agent_id and agent.master_agent_uuid= ? 
                        and agent.timepoint_uuid = bt.timepoint_uuid and bt.task_uuid = ?";
                if (!empty($authUser)) {  // 表示有管理的用户
                    $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                    $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                    $sql .= " and bt.user_uuid in " . $useruuidArr;
                }else{
                    $sql .= " and bt.user_uuid = '{$useruuid}' ";
                }

                $sqlCount = $sql ." order by $sortArr[$sortColumn] $sortType";
                $sql .= " order by $sortArr[$sortColumn] $sortType limit ? , ? ";
                $sqlParams = array($hostUuid,$taskuuid,$start, $length);
                $countParams = array($hostUuid,$taskuuid);
            }
            $data = $this->dbSelect($sql, $sqlParams);
            $countData = $this->dbSelect($sqlCount,$countParams);
        }
        $records = array();
        $records["data"] = array();

        if(!empty($data)){
            foreach ($data as $d){
                $eventId = $d['id'];
                $eventType = $d['event_type'];
                $eventLevel = $d['event_level'];

                $descriptionKey = $d['description_key'];
                $descriptionParam = $d['description_param'];

                $eventTimeStr = $d['event_time'];
                $eventDetail = $d['event_detail'];
                $eventDescription = $this->getEventDesriptionNotice($descriptionKey, $descriptionParam);

                $records['rows'][] = array(
                    'event_point' => $eventTimeStr,
                    'event_type' => $this->getEventTypeDesc($eventType),
                    'event_level' => $eventLevel,
                    'remarks' => $eventDescription,
                    'eventId' => $eventId,
                );
            }
        }
        $countNum = count($countData);
        $records["total"] = $countNum;

        return  $records;
    }

    /**
     * 解析系统事件及应用事件
     * @param int $logType      事件类型   系统事件1/应用事件：2
     * @param int $errorCode    错误码
     * @param string $desription    描述
     * @param string $descriptionParam  描述参数
     * @return string
     */
    public function getEventDesriptionNotice($desription, $descriptionParam)
    {
        $desStr = xphp_get_desc('Volcdp','VolCdpEventDes')[$desription];
        if($descriptionParam){
            $param = json_decode($descriptionParam, true);
            $desArr = explode("%s", $desStr);
            $desStr = "";
            foreach ($desArr as $k => $v){
                $desStr .= $v . $this->getEachParamsDes($param[$k], false);
            }
        }
        return $desStr;
    }

    /**
     * 得到事件类型
     * @param unknown $eventType 1,系统事件；2,任务事件
     */
    private function getEventTypeDesc($eventType)
    {
        if($eventType==1){
            $eventTypeDesc = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_SYSTEM_EVENT');
        }else if($eventType==2){
            $eventTypeDesc = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_TASK_EVENT');
        }
        return $eventTypeDesc;
    }

    /**
     * 得到每一项参数的描述
     * @param string $eachParams
     * @param boolean $classShowFlag  是否显示颜色,页面显示的时候要显示颜色类,发送短信和邮件的时候不显示
     * @return string
     */
    private function getEachParamsDes($eachParams, $classShowFlag = true)
    {
        if(empty($eachParams)){
            return "";
        }
        $des = "";
        $arr = explode(":", $eachParams, 2);
        switch($arr[0]){
            case "S":
                if($classShowFlag){
                    $des = '<span  class="font-blue">' . $arr[1] . '</span>';
                }else{
                    $des = $arr[1];
                }
                break;
            case "module_type":
                $des = $this->logModuleTypeDes($arr[1]);
                break;
            case "backup_mode":
                $des = $this->logBackupModeDes($arr[1]);
                break;
            default:
                break;
        }
        return $des;
    }

    /**
     * 得到日志模块描述
     * @param int $index    模块定义键的位置
     * @return string
     */
    private function logModuleTypeDes($index)
    {
        return xphp_get_desc('Volcdp','MODULE_TYPE_DES')[intval($index)];
    }

    /**
     * 得到日志备份模式描述
     * @param int $index    模块定义键的位置
     * @return string
     */
    private function logBackupModeDes($index)
    {
        return xphp_get_desc('Volcdp','BACKUP_MODE_DES')[intval($index)];
    }

    public function getBackupSetTimeRange($params){
        $flag = xphp_get_config('app','FLAG');
        $deleted_flag  = $flag['UNSET'];            // 是否删除标志位
        $available_flag = $flag['SET'];             // 时间点是否可用标志

        $nodeUuid = $params['node_uuid'];
        $userUuid = xphp_get_user_info()['userUuid'];
        $taskUuid = $params['task_uuid'];
        $hostUuid = $params['host_uuid'];
        $sql = "select DISTINCT bbt.timepoint_uuid,(cvba.id) as agent_id,cvbv.start_timestamp,cvbv.end_timestamp,cvbv.id, cvba.master_agent_detail   
            from bd_backup_timepoint bbt,cdp_vol_backup_agent cvba,cdp_vol_backup_vol_set cvbv,bd_storage_resource bsr
            where bbt.timepoint_uuid = cvba.timepoint_uuid and cvba.id = cvbv.backup_agent_id and  bbt.deleted_flag = ? and bbt.available_flag =? 
                 and cvba.master_agent_uuid = ? and bbt.task_uuid = ? and bsr.storage_uuid = bbt.storage_uuid ";
        $sqlParams = array($deleted_flag, $available_flag, $hostUuid,$taskUuid);
        if ($nodeUuid){      // 如果是选择了某个节点,显示这个节点下面的
            $sql .= "  and bsr.node_uuid = ? ";
            array_push($sqlParams, $nodeUuid);
        }
        $sql .= " GROUP BY cvba.id order by cvbv.id desc";
        $data = $this->dbSelect($sql, $sqlParams);
        $backupSetdata = array();
        if(!empty($data)){
            foreach ($data as $k){
                $cdpInfo = json_decode($k['master_agent_detail'], true);
                $allDiskList = $cdpInfo['all_disk_list'];
                $excludeDiskList = $cdpInfo['exclude_device_list'];
                $excludeList = array();
                foreach ($excludeDiskList as $disk) {
                    $excludeList[] = $disk['dev_uuid'];
                }
                $systemFlag = false;
                foreach ($allDiskList as $disk) {
                    //判断是否备份了系统盘
                    if(!in_array($disk['dev_uuid'], $excludeList) && $disk['is_system_disk_flag'] == 1){
                        $systemFlag = true;
                    }
                }
                $backupSetdata[] = array(
                    'backup_set_id' => $k['agent_id'],
                    'time_point_uuid' => $k['timepoint_uuid'],
                    'start_time' => $k['start_timestamp'],
                    'end_time' => $k['end_timestamp'],
                    'is_system_disk_flag' => $systemFlag,
                );
            }

        }
        return $backupSetdata;

    }


    /**
     * 获取当前节点下备份客户端
     * @param array
     * @return array
     */
    public function getBackupSetTree($params)
    {
        $storageuuid = $params['storage_uuid'];                   // 节点UUID,为空的时候显示所有节点数据
        $useruuid = xphp_get_user_info()['userUuid'];
        $flag = xphp_get_config('app','FLAG');
        $deleted_flag  = $flag['UNSET'];                    // 是否删除标志位
        $available_flag = $flag['SET'];                     // 时间点是否可用标志
        $taskSql = "select distinct bbt.timepoint_uuid,bbt.task_name,bbt.task_uuid,bbt.task_create_time  
                    from bd_backup_timepoint bbt
					left join bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
					left join bd_task bt on bt.task_uuid = bbt.task_uuid
					where bbt.deleted_flag = ? and bbt.available_flag = ? and bbt.module_type = ? and bbt.import_flag = ?";
        $taskSqlParams = array($deleted_flag, $available_flag, xphp_get_config('module','MODULE_TYPE')['VOL_CDP'],xphp_get_config('app','FLAG')['UNSET'],);
        if ($storageuuid){                                     // 如果是选择了某个节点,显示这个节点下面的
            $taskSql .= " and bsr.storage_uuid = ? ";
            array_push($taskSqlParams, $storageuuid);
        }

        // 排除云存储的数据
        if (!empty($params['notcloud'])) {
            $taskSql .= ' and bsr.storage_type != ? ';
            $taskSqlParams = array_merge($taskSqlParams, [xphp_get_config('storage', 'BD_STORAGE_TYPE')['CLOUD']]);
        }

        // 排除磁带的数据
        if (!empty($params['nottape'])) {
            $taskSql .= ' and bsr.storage_type != ? ';
            $taskSqlParams = array_merge($taskSqlParams, [xphp_get_config('storage', 'BD_STORAGE_TYPE')['TAPE']]);
        }
        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源
            $userUuidSql = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['complete_cdp']);
            $sqlNew = " bbt.user_uuid in ({$userUuidSql}) ";
            $taskSql .= ' and ' . $sqlNew;
        }
        $taskSql .= " group by bbt.task_uuid order by bbt.task_name desc";
        $taskData = $this->dbSelect($taskSql, $taskSqlParams);
        $filter = false;
        $filterStr = $params['search_info'];
        if( $filterStr!=""){
            $filter = true;
        }
        $currentTaskUUID = parent::getCurrentAllTaskUUID();//得到当前任务所有uuid
        $tree = array();
        if(!empty($taskData)){
            $tree[] = array(
                "id" => $storageuuid,
                "pId"=> "",
                "isParent" =>true,
                "icon" => "./img/platform/flag.svg",
                "name" => xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DATA_CENTER'),
                "open" =>  true,
                "nocheck" => true,
                "title" => xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DATA_CENTER'),
                "type" => 'data_center'
            );
        }else{
            return $tree;
        }
        foreach ($taskData as $d){
            $taskuuid = $d['task_uuid'];
            $taskName = $d['task_name'];
            //判断任务是否被删除  如果被删除则后面添加任务已删除关键字
            $taskAvailable = in_array($d['task_uuid'], $currentTaskUUID);
            $taskNameStr = $taskAvailable ? $taskName : $taskName . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";
            $taskNameFlag = false;
            if(strpos($taskNameStr,$filterStr)!==false){
                $taskNameFlag = true;
            }
            //第二层 任务名称
            $taskArray= array(
                "id" => $storageuuid.$d['task_uuid'],
                "pId" => $storageuuid,
                "name" => $taskNameStr,
                "open" => false,
                "nocheck" => true,
                "type" => 'task',
                "icon" => './img/platform/flag.svg',
                "title" => xphp_get_lang('WEB_PLATFORM_DC_TASK_CREATE_TIME') . ': ' . $d['task_create_time'],
                "isParent" => true,
            );
            $childTree = $this->getBackupAgentSetInfo($taskuuid,$storageuuid,$useruuid,$filterStr);
            if($childTree){
                $tree[] = $taskArray;
                $tree = array_merge($tree, $childTree);
            }
        }
        return $tree;
    }

    /**
     * 校验时间点是否有效
     */
    public function verifyTimePointIsValid($params)
    {
        // agent_uuid:agentUuid,
        // node_uuid:nodeUuid,
        // timepoint:timePoint,
        // vol_uuid:'',
        // task_type:CONF.TASK_TYPE.VOL_CDP_RECOVERY,
        // task_uuid:taskUuid,
        // backup_set_id : _backupSetId,


        $agentUuid = $params['agent_uuid'];
        $nodeUuid = $params['node_uuid'];
        $timepoint = $params['timepoint'];
        $volUuid = $params['vol_uuid'];
        $taskuuid = $params['task_uuid'];  //客户端对应的任务名
        $backupSetId = $params['backup_set_id'];
        $createTaskType = $params['task_type'];  //创建任务类型
        $matchingRecord= 0;
        $unixTimeInfo = $this->getUnixTimetamp($timepoint);
        $unixTime = $unixTimeInfo[0];
        //获取有效的时间区间
        $sql = "SELECT time.src_start_timepoint,time.src_end_timepoint,time.timepoint_uuid,time.storage_uuid,st.node_uuid,
                    vol.storage_status,time.encrypted_flag,time.detail,agent.storage_location,agent.master_agent_detail    
                FROM cdp_vol_backup_agent agent,bd_backup_timepoint time,bd_storage_resource st,cdp_vol_backup_vol_set vol 
                WHERE agent.timepoint_uuid = time.timepoint_uuid and agent.master_agent_uuid = ? and time.storage_uuid= st.storage_uuid 
                    and vol.backup_agent_id = agent.id and time.task_uuid = ?";

        // if($createTaskType==Xphp::$_config['TASKTYPE']['VOL_CDP_RECOVERY']){

        $sql = $sql." and (agent.storage_location!=? and agent.storage_location!=?)";
        $params = array($agentUuid,$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']);
        $data = $this->dbSelect($sql,$params);

        // }else{
        //     $sql = $sql." and agent.storage_location!=?";
        //     $data = $this->dbSelect($sql,array($agentUuid,$taskuuid,$VolCdpDes['DATA_SOURCE']['UNKNOWN']));
        // }
        $list = array();

        if(!empty($data)){
            foreach ($data as $d){
                $srcStartTime = $d['src_start_timepoint'];
                $srcEndTime = $d['src_end_timepoint'];
                $storageStatus = $d['storage_status'];
                $timepointUuid = $d['timepoint_uuid'];
                $storageLocation = $d['storage_location'];
                $masterAgentDetail = json_decode($d['master_agent_detail']);
                $osType = $masterAgentDetail->os_type;
                $systemBootType = $masterAgentDetail->system_boot_type;
                $osDiskList = $masterAgentDetail->os_disk_list;

                $detail = json_decode($d['detail'],true);
                if($d["encrypted_flag"]==1) { // 时间点加锁
                    $encryptedFlag = true;
                }else {
                    $encryptedFlag = false;
                }
                $passwordAutoFlag = intval($detail['password_auto_flag'])==1 ? true: false;
                $verifyResult =  $this->verifyBackupSetStatus($storageStatus,$createTaskType,$agentUuid,$timepoint,$timepointUuid);
                $standbySql = "select vol.end_timestamp
                            from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol , bd_backup_timepoint bbt
                            where vol.backup_agent_id = agent.id and agent.master_agent_uuid = ? and agent.storage_location !=? and agent.storage_location !=? 
                                and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid = ? ";
                $standbyData = $this->dbSelect($standbySql,array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid));
                $newTimeStr = $standbyData[0]['end_timestamp'];

                $virusScanStatus =  $this->getVirusScanInfo($timepointUuid,$timepoint);   //获取校验事件点的扫描状态
                // }
                if($unixTime>=$srcStartTime && $unixTime<=$srcEndTime){
                    if ($this->checkInBacktask($timepointUuid)) {
                        // 时间点有备份任务再用
                        $list = [];
                        break;
                    }
                    $storageUuid = $d['storage_uuid'];
                    $nodeUuid = $d['node_uuid'];
                    $timeVolInfo = $this->getSetTimeVolInfo($agentUuid,$timepointUuid,$timepoint,$volUuid,$nodeUuid,$storageUuid,$verifyResult,$osDiskList);

                    $list = array(
                        'time_vol_info' => $timeVolInfo,
                        'encrypted_flag' => $encryptedFlag,
                        'password_auto_flag' => $passwordAutoFlag,
                        'timepoint_uuid' => $timepointUuid,
                        'storage_location' =>$storageLocation,
                        'new_time_str' => $newTimeStr,
                        'os_type' =>$osType,
                        'system_boot_type' => $systemBootType,
                        'virus_scan_status'=>$virusScanStatus,  //0 – 未扫描 1 – 扫描中 2 – 健康 3 – 感染
                    );

                }
            }
        }
        return $list;
    }

    /**
     * 根据时间点uuid判断是否被备份任务占用
     * @param string $timepointUuid 时间点uuid
     * @return bool
     */
    private function checkInBacktask(string $timepointUuid): bool
    {
        $sql = 'select timepoint_uuid,task_uuid 
                from bd_backup_timepoint where task_uuid in 
                (select task_uuid from bd_backup_timepoint where timepoint_uuid = ?)
                 order by id desc';
        $data = $this->dbSelect($sql, [$timepointUuid]);
        if (empty($data)) {
            return false;
        }
        if ($data[0]['timepoint_uuid'] == $timepointUuid) {
            // 是最新的，
            $taskStatus = xphp_get_config('task', 'TASKSTATUS');
            // 再进一步查 task_status
            $task = $this->dbSelect(' select task_status from bd_task where task_uuid = ?', [$data[0]['task_uuid']]);
            if (
                empty($task) ||
                in_array($task[0]['task_status'], [$taskStatus['STOPPED'], $taskStatus['ERROR']])
            ) {
                //  如果是停止/错误，则未被备份任务占用
                return false;
            }
        } else {
            // 不是最新的 则未被备份任务占用
            return false;
        }
        // 其余情况都表示 timepoint_uuid 被备份任务占用，需要提示。
        return true;
    }

    /**
     *  获取校验事件点的扫描状态
     * @param mixed $timepointUuid
     * @param mixed $timepoint
     */
    private function getVirusScanInfo($timepointUuid,$timepoint){
        $sql = "select virus_scan_status from cdp_vol_backup_timepoint_safe_info where timepoint_uuid = ? and timepoint_datetime = ?";
        $data = $this->dbSelect($sql,array($timepointUuid,$timepoint));
        $virusScanStatus =0;
        if(!empty($data)){
            $virusScanStatus = $data[0]['virus_scan_status'];
        }
        return $virusScanStatus;
    }
    /**
     * 校验备份集的状态在对应的创建任务类型下是否可用
     * @param integer $storageStatus：备份集状态
     * @param integer $createTaskType：任务创建类型
     */
    private function verifyBackupSetStatus($storageStatus,$createTaskType,$agentUuid,$timepoint,$timepointUuid){
        if($createTaskType == xphp_get_config('task', 'TASKTYPE')['VOL_CDP_RECOVERY']){  //恢复任务校验
            $taskDesc = xphp_get_lang('WEB_OS_RECOVERY_TASK');
        }else if($createTaskType ==xphp_get_config('task', 'TASKTYPE')['VOL_CDP_TAKEOVER']){  //接管任务校验
            $taskDesc = xphp_get_lang('UI_VOL_CDP_TAKEOVER_TASK');
        }
        $verifyResult = "";
        switch ($storageStatus){
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_NEW']:  //新建状态
                $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUP_CREATE_TIP').$taskDesc;
                break;
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_INIT_SYNC']:  //初始同步
                $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUP_INIT_SYNC_TIP').$taskDesc;
                break;
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_REALTIME_SYNC']:  //实时同步
                // 如果当前关联的备份任务正在运行该状态下可以被 接管(只能有一个接管)，但不能被恢复。(注意只是最新的一段时间集，之前停了再启动的那种不算关联)
                // 如果当前关联的备份任务未运行，该状态下可以被 接管 或 恢复。
                $backupTaskArray = $this->getBackupTaskStatus($agentUuid,$timepoint);

                $taskStatus = $backupTaskArray['status'];
                $taskuuid = $backupTaskArray['task_uuid'];

                $lastSql = "select id,timepoint_uuid from bd_backup_timepoint where task_uuid =? and task_type =? order by id desc limit 0,1";
                $lastData = $this->dbSelect($lastSql, array($taskuuid,xphp_get_config('task','TASKTYPE')['VOL_CDP_BACKUP']));
                $newTimepointuuid = $lastData[0]['timepoint_uuid'];
                if($newTimepointuuid == $timepointUuid && $taskStatus==xphp_get_config('task','TASKSTATUS')['RUNNING'] && $createTaskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY']){
                    //创建恢复任务判断逻辑
                    $verifyResult =xphp_get_lang('UI_VOL_CDP_BACKUP_RUNNING_TIP').$taskDesc;
                }else if($newTimepointuuid == $timepointUuid && $taskStatus==xphp_get_config('task','TASKSTATUS')['RUNNING'] && $createTaskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){
                    //创建接管任务判断逻辑，只能创建一个接管任务
                    $takeoverSql = "SELECT task.task_name FROM cdp_vol_task vol_task,bd_task task 
                                    WHERE task.task_uuid = vol_task.task_uuid and vol_task.master_agent_uuid = ? and task.task_type = ?";
                    $taskoverData = $this->dbSelect($takeoverSql, array($agentUuid,xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']));
                    if(!empty($taskoverData)){
                        $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUP_RUNNING_CREATE_TAKEOVER_TASK_TIP').$taskDesc;
                    }
                } else {
                    //如果在回切实时同步阶段，备机也不能恢复
                    $takeoverSql = "SELECT cvt.task_uuid FROM cdp_vol_task cvt,bd_task bt,cdp_vol_task_takeover_info cti
                        WHERE cti.takeover_standby_agent_uuid =? and bt.task_uuid =cti.task_uuid and cvt.master_agent_uuid =bt.agent_uuid and  cvt.current_task_running_stage=?";
                    $lastData = $this->dbSelect($takeoverSql, [$agentUuid,xphp_get_desc('Volcdp','TASK_RUNNING_STAGE')['FAILBACK_REALTIME_SYNC']]);
                    if(!empty($lastData)){
                        $verifyResult =xphp_get_lang('UI_VOL_CDP_BACKUP_RUNNING_TIP').$taskDesc;
                    }
                }
                break;
            case xphp_get_desc('Volcdp','VOL_STORAGE_STATUS')['VOL_CDP_VOL_STORAGE_IN_IMAGE_MERGE']:  //镜像合并中
                //如果当前关联的备份任务正在运行，该状态下不能被 接管 或 恢复，则不能即不能被选择创建手动接管或恢复作业【注意只是最新的一段时间集，之前停了再启动的那种不算关联】
                $backupTaskArray = $this->getBackupTaskStatus($agentUuid,$timepoint);
                $taskStatus = $backupTaskArray['status'];
                $taskuuid = $backupTaskArray['task_uuid'];

                $lastSql = "select id,timepoint_uuid from bd_backup_timepoint where task_uuid =? order by id desc limit 0,1";
                $lastData = $this->dbSelect($lastSql, array($taskuuid));
                $newTimepointuuid = $lastData[0]['timepoint_uuid'];
                if($newTimepointuuid == $timepointUuid){
                    $verifyResult = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_MERGE_TIP').$taskDesc;
                }
                break;
        }
        return $verifyResult;
    }
    /**
     * @param $agentUuid
     * @param $timepoint
     * @return array
     * 获取关联备份任务是否有任务，并获取任务状态；
     */
    private  function getBackupTaskStatus($agentUuid,$timepoint)
    {
        $sql = "select task.task_status,task.task_uuid from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol,cdp_vol_task vol_task,bd_task task 
            where task.task_uuid = vol_task.task_uuid and vol_task.master_agent_uuid = agent.master_agent_uuid and agent.id = vol.backup_agent_id
                and (vol.start_timestamp <= ? or vol.end_timestamp >= ?) and agent.master_agent_uuid = ?";
        $taskList = $this->dbSelect($sql,array($timepoint,$timepoint,$agentUuid));
        $statusArray = array();
        if(!empty($taskList)){
            $status = $taskList[0]['task_status'];
            $taskuuid = $taskList[0]['task_uuid'];
            $statusArray = array(
                'status' => $status,
                'task_uuid' => $taskuuid
            );
        }
        return  $statusArray;
    }

    /**
     * 获取有效时间区间内的卷信息
     * @param unknown $agentUuid
     * @param unknown $timepointUuid
     */
    private function getSetTimeVolInfo($agentUuid,$timepointUuid,$timepoint,$volUuid,$nodeUuid,$storageUuid,$verifyResult,$osDiskList){
        $volSql = "select vol.vol_display_name,vol.vol_uuid,vol.standby_vol_uuid,vol.capacity,vol.is_boot,
                    agent.master_agent_detail,vol.detail, agent.dev_type
                from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol
                WHERE agent.master_agent_uuid =? and agent.timepoint_uuid = ? and vol.backup_agent_id = agent.id";
        if($volUuid!=""){
            $volSql.= " and vol.vol_uuid = '{$volUuid}'";
        }
        $volList = $this->dbSelect($volSql,array($agentUuid,$timepointUuid));
        $list = array();
        if(!empty($volList)){
            foreach ($volList as $vol){
                $standbyVoluuid = $vol['standby_vol_uuid'];
                $agentDetail = $this->parseBackupAgentDetail($vol['master_agent_detail']);
                $osType = $agentDetail['os_type'];
                $detail = $vol['detail'];
                $devType = $vol['dev_type'];

                $volDetail = json_decode($vol['detail']);
                $volType = $volDetail->vol_type;
                $systemVolume = false;
                if($volType & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SYSTEM_VOLUME']){
                // if($volType & $VolCdpDes['VOL_TYPE']['BD_SYSTEM_VOLUME']){
                    $systemVolume = true;
                }
                $uefi = false;
                if($volType & xphp_get_desc('Volcdp','VOL_TYPE')['BD_EFI_VOLUME']){
                // if($volType & $VolCdpDes['VOL_TYPE']['BD_EFI_VOLUME']){
                    $uefi = true;
                }
                $devUuid = $vol['vol_uuid'];
                if ((in_array($devUuid, $osDiskList)) && $devType == xphp_get_config('cm_cdp','DATA_MODE')['CM_DATA']) {
                    $systemVolume = true;
                }
                $list[] = array(
                    'vol_name' => $vol['vol_display_name'],
                    'new_timestamp' =>$timepoint,
                    'vol_uuid' => $devUuid,
                    'capacity' => v1_calsize($vol['capacity'], true),
                    'capacity_value' => $vol['capacity'],
                    'standby_vol' => $this->getVolInfoByVoluuid($standbyVoluuid,$agentUuid),
                    'node_uuid' => $nodeUuid,
                    'storage_uuid' => $storageUuid,
                    'is_boot' => $vol['is_boot'],
                    'os_type' => $osType,
                    'verify_result' =>$verifyResult,
                    'system_volume' =>$systemVolume,
                    'is_uefi' => $uefi,
                );
            }
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
     * 获取客户端备份时间轴对应的数据
     */
    public function getBackupSetTimelineData($params)
    {
        $agentUuid = $params['agent_uuid'];
        $backupSetId = $params['backup_set_id'];
        $timeIntervalType = $params['time_interval'];
        $taskuuid = $params['task_uuid'];
        $startTime = $params['start_time'];
        $endTime = $params['end_time'];


        $timeLineData = array();
        $records = array();
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看
        $useruuid = xphp_get_user_info()['userUuid'];

        // if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_TEN_MIN'] )
        // {
        //     $sql = "select se.id,se.backup_timestamp 
        //             from cdp_vol_backup_agent_second_level_data_flow se,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
        //             where agent.master_agent_uuid = ?  and agent.id = se.backup_agent_id and agent.storage_location!=? and bbt.timepoint_uuid = agent.timepoint_uuid 
        //                 and bbt.task_uuid = ? ";
        //     if (!empty($authUser)) {  // 表示有管理的用户
        //         $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
        //         $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
        //         $sql .= " and bbt.user_uuid in " . $useruuidArr;
        //     }else{
        //         $sql .= " and bbt.user_uuid ='{$useruuid}' ";
        //     }

        //     $sql .= " and agent.storage_location!=? ";
        //     $sqlParams = array(
        //         $agentUuid,
        //         xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],
        //         $taskuuid,
        //         xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']
        //     );

        //     if($backupSetId!=0){
        //         $sql.= " and se.backup_agent_id = ? ";
        //         array_push($sqlParams, $backupSetId);
        //     }
        //     $latestTimeSql = $sql ." order by se.id  desc limit 0,1";
        //     $data = $this->dbSelect($latestTimeSql,$sqlParams);
        //     if($startTime) {
        //         $records["time_data"]  = $this->getSecondLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType,$taskuuid,$backupSetId);
        //     } else {
        //         if(!empty($data)){
        //             $latestTime = $data[0]['backup_timestamp'];
        //             $records["time_data"]  = $this->getSecondLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid,$backupSetId);
        //         } else{
        //             $records["time_data"] =[];
        //         }
        //     }
        // } else 
        if($timeIntervalType==xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_ONE_HOUR']
                || $timeIntervalType==xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_ONE_DAY'] 
                || $timeIntervalType ==xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_SEVEN_DAY']){
            $sql = "select mi.id,mi.backup_end_timestamp 
                    from cdp_vol_backup_agent_minute_level_data_flow mi,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                    where agent.master_agent_uuid = ?  and agent.id = mi.backup_agent_id and agent.storage_location!=? and bbt.timepoint_uuid = agent.timepoint_uuid  and bbt.task_uuid = ?";
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = '{$useruuid}' ";
            }
            $sql.= " and agent.storage_location!=? ";
            $sqlParams = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']);
            if($backupSetId!=0){
                $sql .= " and mi.backup_agent_id = ? ";
                array_push($sqlParams, $backupSetId);
            }
            $latestTimeSql = $sql. " order by mi.id  desc limit 0,1";
            $data = $this->dbSelect($latestTimeSql,$sqlParams);
            if($startTime) {
                $records["time_data"]  = $this->getMinuteLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType,$taskuuid,$backupSetId);
            } else {
                if(!empty($data)){
                    $latestTime = $data[0]['backup_end_timestamp'];
                    $records["time_data"]  = $this->getMinuteLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid,$backupSetId);
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
            if($backupSetId!=0){
                $sql .= " and hour.backup_agent_id = ? ";
                array_push($sqlParams, $backupSetId);
            }

            $latestTimeSql = $sql." order by hour.id desc limit 0,1";
            $data = $this->dbSelect($latestTimeSql,$sqlParams );
            if($startTime) {
                $records["time_data"]  = $this->getHourLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType,$backupSetId);
            } else {
                if(!empty($data)){
                    $latestTime = $data[0]['backup_end_timestamp'];
                    $records["time_data"]  = $this->getHourLevelData($latestTime,$agentUuid,$timeIntervalType,$backupSetId);
                }else{
                    $records["time_data"] =[];
                }
            }
        }
        return $records;
    }

    #------------------------------------------------------------------ 以下是私有方法 ---------------------------------------------------

    private function  getSecondLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType,$taskuuid,$backupSetId)
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
        $params = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid);
        if($backupSetId !=0){
            $sql .= " and se.backup_agent_id = ?";
            array_push($params,$backupSetId);
        }
        $data = $this->dbSelect($sql, $params);
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
            // $backupSetIntervalType['LAST_TEN_MIN'] => 1,       // 秒
            $backupSetIntervalType['LAST_ONE_HOUR'] => 60,      // 秒
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
    private function  getSecondLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid,$backupSetId)
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
                and bbt.task_uuid = ? ";
        $params = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid);
        if($backupSetId !=0){
            $sql .= " and se.backup_agent_id = ?";
            array_push($params,$backupSetId);
        }
        $data = $this->dbSelect($sql, $params);

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

    private function getMinuteLevelDataCustom($startTime, $endTime,$agentUuid,$timeIntervalType,$taskuuid,$backupSetId)
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
                and bbt.task_uuid = ? ";
        $sqlParams = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid);
        if($backupSetId!=0){
            $sql .=" and mi.backup_agent_id = ?";
            array_push($sqlParams,$backupSetId);
        }
        $data = $this->dbSelect($sql, $sqlParams);
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
    private function getMinuteLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid,$backupSetId)
    {
        if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_ONE_HOUR']){
            $timeIntervalValue = 60;  //单位分钟
            $timeDif = strtotime($latestTime)-($timeIntervalValue*60);
        }else if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_ONE_DAY']){   //最近1天数据,读取分钟级表，对应的最新时间减1440分钟
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
        $sqlParams = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid);
        if($backupSetId !=0){
            $sql.= " and mi.backup_agent_id = ?";
            array_push($sqlParams,$backupSetId);
        }

        $data = $this->dbSelect($sql,$sqlParams);
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

    private function getHourLevelDataCustom($startTime, $endTime,$agentUuid,$timeIntervalType,$backupSetId)
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
                and agent.storage_location!=? ";
        $sqlParams = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']);
        if($backupSetId !=0){
            $sql.= " and hour.backup_agent_id = ?";
            array_push($sqlParams,$backupSetId);
        }
        $data = $this->dbSelect($sql, $sqlParams);
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
    private function getHourLevelData($latestTime,$agentUuid,$timeIntervalType,$backupSetId)
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
        $sqlParams = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']);
        if($backupSetId !=0){
            $sql.= " and hour.backup_agent_id = ?";
            array_push($sqlParams,$backupSetId);
        }
        $data = $this->dbSelect($sql, $sqlParams);

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
     * 获取客户端对应的备份集信息，通过任务uuid
     * @return void
     */
    private function getBackupAgentSetInfo($taskuuid,$storageuuid,$useruuid,$filterStr)
    {
        $flag = xphp_get_config('app','FLAG');
        $deleted_flag  = $flag['UNSET'];                // 是否删除标志位
        $available_flag = $flag['SET'];                 // 时间点是否可用标志
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];
        $filter = false;
        if( $filterStr!=""){
            $filter = true;
        }
        $sql = "SELECT DISTINCT agent.master_agent_uuid, agent.master_agent_detail, agent.id, bsr.node_uuid, bbt.task_name,
								bbt.task_uuid, bbt.storage_uuid, UNIX_TIMESTAMP(bbt.task_create_time) task_create_time,
								agent.timepoint_uuid, agent.storage_location,
                                ba.online_flag,ba.net_model,baa.app_name, baa.app_uuid, baa.app_type, cvba.backup_agent_id
                FROM bd_backup_timepoint bbt
                JOIN cdp_vol_backup_agent agent ON bbt.timepoint_uuid = agent.timepoint_uuid
                JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid 
				LEFT JOIN bd_agent ba ON ba.agent_uuid = agent.master_agent_uuid
				LEFT JOIN bd_agent_app baa ON ba.agent_uuid = baa.agent_uuid
				LEFT JOIN cdp_vol_backup_app cvba ON baa.app_uuid = cvba.app_uuid
                WHERE bbt.deleted_flag = ?
                AND bbt.available_flag = ? 
                AND bbt.module_type = ?
                AND agent.dev_type = ? 
                AND bbt.task_uuid = ?
                AND bbt.import_flag = ?";
        $sqlParams = array(
            $deleted_flag,
            $available_flag,
            xphp_get_config('module','MODULE_TYPE')['VOL_CDP'],
            xphp_get_config('app','FLAG')['UNSET'],
            $taskuuid,
            xphp_get_config('app','FLAG')['UNSET']
        );
        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源
            $userUuidSql = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['complete_cdp']);
            $sqlNew = " bbt.user_uuid in ({$userUuidSql}) ";
            $sql .= ' and ' . $sqlNew;
        }
        if ($storageuuid){     //如果是选择了某个节点,显示这个节点下面的
            $sql .= " and bsr.storage_uuid = ? ";
            array_push($sqlParams, $storageuuid);
        }
        $sql .= " group by agent.master_agent_uuid order by agent.id desc";
        $data = $this->dbSelect($sql, $sqlParams);

        $storageHandler = new Storage();
        $storageUuidList = array_column($data, 'storage_uuid');
        $storageStatusList = $storageHandler->batchGetStorageStatus($storageUuidList);

        $storageOfflineStatus = xphp_get_config('storage', 'STORAGE_STATUS')['OFFLINE'];

        $tree = array();
        $hostInfoList = [];
        foreach ($data as $d){
            // 判断当前时间点所在存储是否离线，是则置灰节点
            $isOfflineStorage = 1;
            foreach ($storageStatusList as $key => $storageStatusInfo) {
                if ($d['storage_uuid'] == $storageStatusInfo['storage_uuid']) {
                    $isOfflineStorage = $storageStatusInfo['storage_status'];
                    break;
                }
            }

            $agentInfoExists = false;
            $agentUUID = $d['master_agent_uuid'];
            $agentDetail = $this->parseBackupAgentDetail($d['master_agent_detail']);
            $agentName = $agentDetail['agent_name'];
            $agentIp = $agentDetail['agent_ip'];
            $hostName = $agentDetail['hostname'];
            $system_boot_type = $agentDetail['system_boot_type'];
            $storage_location = $d['storage_location'];

            if($storage_location == xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'] || $storage_location == xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']){
                continue;
            }

            $name = $this->agentStr($agentName,$hostName,$agentIp);
            if ($isOfflineStorage == $storageOfflineStatus) { // 存储离线需拼接 (存储离线)
                $name = '<span style="color:#999999">' . $name . '(' . xphp_get_lang('UI_PUBLIC_STORAGE_OFF') . ')' . '</span>';
            }

            // 判断主机IP或者uuid是否已存在数组中，兼容同一主机使用不同ip或者不同主机使用相同ip的情况
            $agentInfoExists = in_array($taskuuid.$agentUUID, $hostInfoList);
            if($agentInfoExists ){
                continue;
            }
            $strposFlag = false;
            if(strpos($agentIp,$filterStr)!==false){
                $strposFlag = true;
            }

            $hostNameFlag = false;
            if(strpos($hostName,$filterStr)!==false){
                $hostNameFlag = true;
            }
            $agentNameFlag = false;
            if(strpos($agentName,$filterStr)!==false){
                $agentNameFlag = true;
            }
            if($filter && !$strposFlag && !$hostNameFlag && !$agentNameFlag){  //筛选且筛选内容未包含在$agentName,$agentIp,$hostName中时
                continue;
            }
            $os_type = $agentDetail['os_type'];
            $backupSetNodeUuid = $d['node_uuid'];
            $backupSetStorageUuid = $d['storage_uuid'];
            $type_icon = "./img/platform/linux.svg";
            if($os_type=='Windows'){
                $type_icon = "./img/platform/windows.svg";
            }
            $agentUUID = $d['master_agent_uuid'];
            $treeId = $storageuuid.$taskuuid.$d['master_agent_uuid'].$agentIp;
            $treePid =$storageuuid.$taskuuid;
            $tree[] = array(
                "id" => $treeId,
                "pId" => $treePid,
                "name" => $name,
                "title" => xphp_get_lang('UI_VOL_CDP_BACKUP_SET_CLIENT_IP').":".$agentIp,
                "isParent" => true,
                "agent_uuid" => $agentUUID,
                "node_uuid" =>$backupSetNodeUuid,
                "storage_uuid" => $backupSetStorageUuid,
                "isApp" =>false,
                "type" => 'agent',
                "icon" => $type_icon,
                "clickshow" => false,
                "checked" => false,
                "open" =>  true,
                "host_ip" => $agentIp,
                'task_uuid' => $taskuuid,
                "os_type" => json_decode($d['master_agent_detail'])->os_type,
                "nocheck" => false,
                "timepoint_uuid" => $d['timepoint_uuid'],
                'onlineFlag' => $d['online_flag'],
                'system_boot_type' => $system_boot_type,
                'net_model' => $d['net_model'],
                'chkDisabled' => $isOfflineStorage == $storageOfflineStatus,
                'app_name' => $d['app_name']??'',
                'app_uuid' => $d['app_uuid']??'',
                'app_type' => $d['app_type']??'',
                'backup_agent_flag'=> !empty($d['backup_agent_id'])
            );
            $childTree = $this->getBackupVolData($agentUUID,$treeId,$storageuuid,$backupSetNodeUuid,$agentIp,$taskuuid);
            $tree = array_merge($tree, $childTree);
            $hostInfo = $taskuuid.$agentUUID;
            array_push($hostInfoList,$hostInfo);
        }
        return $tree;
    }

    /**
     * 获取恢复数据源主机信息
     */
    private function parseBackupAgentDetail($detail)
    {
        $bkAgentArry = array();
        if(!empty($detail)){
            $master_agent_detail = json_decode($detail);
            $agent_name = $master_agent_detail->agent_name;
            $host_name = $master_agent_detail->hostname;
            $agent_ip = $master_agent_detail->ip;
            $os_type = $master_agent_detail->os_type;
            $system_boot_type = $master_agent_detail->system_boot_type;
            $os_disk_list = $master_agent_detail->os_disk_list;
            if($agent_ip==""){
                $agent_ip = "--";
            }
            $bkAgentArry = array(
                "agent_name" =>$agent_name,
                "agent_ip" => $agent_ip,
                "os_type" => $os_type,
                "hostname" => $host_name,
                "system_boot_type" => $system_boot_type,
                "os_disk_list" => !empty($os_disk_list)?$os_disk_list:[]
            );
        }
        return $bkAgentArry;
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
     * 获取客户端对应的备份卷信息
     * @param $agentUUID:选中节点UUID,$treeId:父节点ID,$nodeuuid:存储节点uuid
     */
    private  function getBackupVolData($agentUUID,$treeId,$storageuuid,$backupSetNodeUuid,$parentAgentIp,$taskuuid)
    {
        $flag = xphp_get_config('app','FLAG');
        $deleted_flag  = $flag['UNSET'];  //是否删除标志位
        $available_flag = $flag['SET'];  //时间点是否可用标志位
        $useruuid = xphp_get_user_info()['userUuid'];
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];
        $sql = 'select vol.vol_display_name,vol.vol_uuid,vol.capacity,vol.is_boot,bbt.encrypted_flag,agent.storage_location,vol.vol_name,bsr.node_uuid,agent.master_agent_detail,bbt.timepoint_uuid,vol.detail
                from bd_backup_timepoint bbt,cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol,bd_storage_resource bsr
                where bbt.timepoint_uuid = agent.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid 
                and vol.backup_agent_id = agent.id 
                and agent.master_agent_uuid = ? 
                and bbt.deleted_flag = ? 
                and bbt.available_flag = ? 
                and bbt.module_type = ? 
                and storage_location !=? 
                and bbt.task_uuid = ?';

        $sqlParams = array($agentUUID,$deleted_flag, $available_flag, xphp_get_config('module','MODULE_TYPE')['VOL_CDP'],xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid);
        if (v1_auth_need_check_look()) {
            // 三权模式下的操作员只能查看创建的
            // 不是超级管理员也不是全局观察者，也只能看到创建和管理的资源
            $userUuidSql = v1_auth_get_users(xphp_get_config('user', 'USER_AUTH')['complete_cdp']);
            $sqlNew = " bbt.user_uuid in ({$userUuidSql}) ";
            $sql .= ' and ' . $sqlNew;
        }
        $data = $this->dbSelect($sql, $sqlParams);
        $info = array();
        $volList = [];
        foreach ($data as $d){
            $id =  $treeId.$d['vol_uuid'].$parentAgentIp;
            $volCapacity = v1_calsize($d['capacity'], true);
            $isBoot = $d['is_boot'];
            $isBootStr = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_DATA_VOL');
            if($isBoot == $flag['SET']){
                $isBootStr = xphp_get_lang('UI_VOL_CDP_BACKUP_SET_SYSTEM_VOL');
            }
            $storageLocation = $d['storage_location'];
            $masterAgentDetail = $d['master_agent_detail'];
            $agentDetail = $this->parseBackupAgentDetail($d['master_agent_detail']);
            $agentName = $agentDetail['agent_name'];
            $agentIp = $agentDetail['agent_ip'];
            $hostName = $agentDetail['hostname'];
            $os_disk_list = $agentDetail['os_disk_list'];

            if($agentIp!=$parentAgentIp){
                continue;
            }
            $exists = in_array($d['vol_name'], $volList); //判断主机IP是否已存在数组中
            if($exists){
                continue;
            }

            $volDetail = json_decode($d['detail']);
            $volType = $volDetail->vol_type;
            $uefi = false;
            if($volType & xphp_get_desc('Volcdp','VOL_TYPE')['BD_EFI_VOLUME']){
                $uefi = true;
            }
            $is_system_volume = in_array($d['vol_uuid'],$os_disk_list);
            $info[] = array(
                "id" => $id,
                "pId" => $treeId,
                "name" =>$d['vol_display_name']."(".$isBootStr."，".xphp_get_lang('UI_PUBLIC_CAPACITY').":".$volCapacity.")",
                "title" => $d['vol_display_name']."(".$isBootStr."，".xphp_get_lang('UI_PUBLIC_CAPACITY').":".$volCapacity.")",
                "vol_name" => $d['vol_name'],
                "uuid" => $d['vol_uuid'],
                "icon" => "./img/vm/pool.svg",
                'node_uuid' =>$backupSetNodeUuid,
                "agent_uuid" => $agentUUID,
                "host_ip" => $parentAgentIp,
                'task_uuid' => $taskuuid,
                "timepoint_uuid" => $d['timepoint_uuid'],
                "clickshow" => false,
                "checked" => false,
                "chkDisabled" => false,
                "nocheck" => true,  // checkbox是否显示
                "type" => 'vol',
                'is_uefi' => $uefi,
                'is_system_volume' => $is_system_volume
            );
            array_push($volList,$d['vol_name']);
        }
        return $info;
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
}
