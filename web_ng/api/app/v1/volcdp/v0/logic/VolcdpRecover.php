<?php

namespace app\v1\volcdp\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\common\logic\Base;
use app\v1\common\logic\Recover;
use app\v1\opcode\PfOpcode;
use app\v1\resources\v0\logic\Client;
use xphp\BLLHandler;

/**
 * note          卷实时 之恢复管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class VolcdpRecover extends Base
{
    /**
     * 创建恢复作业
     * @param：object
     */
    public function createRecoverJob($params)
    {
        $task_name = $params['taskName'];
        $strategy_group_uuid = $params['strategygroupuuid'];
        $time_strategy_list = (new Recover())->groupRecoverTimeList($params['timeInfo'], $strategy_group_uuid);//组合时间策略
        $module_type = xphp_get_config('module','MODULE_TYPE')['VOL_CDP'];
        $master_agent_uuid = $params['master_agent_uuid'];
        $standby_agent_uuid = $params['standby_agent_uuid'];  //数据源来自于备机,

        $recovery_position = $params['recovery_position'];
        $restore_data_source = $params['restore_data_source'];  //恢复数据来源
        $restoreType = $params['restoreType'];  //恢复类型
        $rebuild_partition_flag = $params['rebuild_partition_flag']; //是否重建分区

        $node_uuid = $params['node_uuid'];
        $recovery_target_agent_uuid = $params['recovery_target_agent_uuid'];
        $recovery_object = $params['recovery_object'];
        $speedInfo = $params['speedInfo'];
        $highInfo = $params['highInfo'];
        $thread_num = $highInfo['threadnum'];
        $transport_strategy = $params['highInfo']['transfer'];
        $recovery_time_type = intval($params['timeInfo']['type']);  //得到恢复方式

        $pfMSg = $this->pfCreateRecoveryTaskMessage($task_name, $module_type, $recovery_position,
            $recovery_time_type, $time_strategy_list, $transport_strategy);
        $pfMSg['task_type'] = xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY']; //作业类型
        $pfMSg['thread_num'] = $thread_num;
        $pfMSg['strategy_group_uuid'] = $strategy_group_uuid;
        $pfMSg['rebuild_partition_flag'] = $rebuild_partition_flag;
        $pfMSg['restore_data_source'] = $restore_data_source;
        $pfMSg['master_agent_uuid'] = $master_agent_uuid;
        $pfMSg['standby_agent_uuid'] = $standby_agent_uuid;
        $pfMSg['recovery_target_agent_uuid'] = $recovery_target_agent_uuid;
        $pfMSg['recovery_object'] = $recovery_object;
        $pfMSg['speed_limit_strategy_list'] = (new Backup())->groupTaskSpeedList($speedInfo, $strategy_group_uuid);
        // 全局限速策略
        $pfMSg['speed_limit_strategy'] = (new Backup())->groupTaskSpeedGlobalList($params['speedLimit']);
        //得到全局策略uuid (没有 传空值)
        $pfMsg['strategy_group_uuid'] = $strategy_group_uuid;
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $msg = json_encode($pfMSg, JSON_UNESCAPED_UNICODE);
        $mbResult = $this->mbVolCdpMsg($node_uuid, $opName, $msg);
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
     * 创建恢复任务消息
     * @param string $task_name             //task name
     * @param int $module_type              //product module type
     * @param int $recovery_position        //original position， other position
     * @param int $recovery_time_type       //time strategy type
     * @param array $time_strategy_list     //array[array each_strategy, array each_strategy..多项] 二维数组!!!
     *  @param array each_strategy
     *      @param   int   'strategy_type'
     *      @param   int   'mode'
     *      @param   string   'days'
     *      @param   string   'start_time'
     *      @param   int   'roll_flag'
     *      @param   int   'roll_interval'
     *      @param   string   'roll_end_time'
     *      @param   int   'global_id'
     * @param array $transport_strategy     //transport strategy
     *  @param int encrypt_flag
     *  @param int compress_flag
     *  @param int speed_limit_flag
     *  @param int max_speed
     * @return array $backupTaskMessage
     */
    protected function pfCreateRecoveryTaskMessage(
        $task_name, $module_type, $recovery_position,
        $recovery_time_type, $time_strategy_list, $transport_strategy)
    {
        $msg = array(
            'task_name' => $task_name,
            'module_type' => $module_type,
            'recovery_position' => $recovery_position,
            'recovery_time_type' => $recovery_time_type,
        );
        $time_strategy_list_array = array();
        foreach ($time_strategy_list as $list){
            //组合时间策略
            $time_strategy_list_array[] = (new BLLHandler())->pfTimeStrategyMessage(
                $list['strategy_type'], $list['mode'], $list['days'], $list['start_time'],
                $list['roll_flag'], $list['roll_interval'], $list['roll_end_time'], $list['global_id'], $list['strategy_group_uuid']);
        }
        $msg['time_strategy_list'] = $time_strategy_list_array;
        //组合传输策略
        $network = !empty($transport_strategy['network_uuid'])?$transport_strategy['network_uuid']: "";
        if($module_type==xphp_get_config('module','MODULE_TYPE')['VOL_CDP']){
            $msg['transport_strategy'] = array(
                'encrypt_flag' => $transport_strategy['encrypt_flag'],
                'compress_flag' => $transport_strategy['compress_flag'],
                'speed_limit_flag' => $transport_strategy['speed_limit_flag'],
                'max_speed' => $transport_strategy['max_speed'],
                'network_uuid' => $network,
                'strategy_group_uuid' => $transport_strategy['strategy_group_uuid'],
                'block_size' => intval($transport_strategy['block_size']),
                'reconnect_times' => $transport_strategy['reconnect_times'],
                'reconnect_interval' => $transport_strategy['reconnect_interval'],
                'compress_method' => $transport_strategy['compress_method'],
                'encrypt_method' => $transport_strategy['encrypt_method']
            );
        }else{
            $msg['transport_strategy'] = (new BLLHandler())->pfTransportStrategyMessage(
                $transport_strategy['encrypt_flag'],
                $transport_strategy['compress_flag'],
                $transport_strategy['speed_limit_flag'],
                $transport_strategy['max_speed'],
                $network,
                $transport_strategy['strategy_group_uuid'],
                $transport_strategy['compress_method'],
                $transport_strategy['reconnect_times'],
                $transport_strategy['reconnect_interval'],
                $transport_strategy['encrypt_method']
            );
        }

        return $msg;
    }

    public function getDataSourceHostInfo($params){
        $start = $params['offset'];
        $length = $params['limit'];
        $user_uuid = xphp_get_user_info()['userUuid'];
        $node_uuid = $params['node_uuid'];
        $available_flag = xphp_get_config('app','FLAG')['SET'];  //时间点是否可用标志位
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看

        $sql = 'select DISTINCT agent.master_agent_uuid,agent.master_agent_detail,agent.storage_location,bbt.timepoint,
                    bbt.user_uuid,bbt.task_name,bbt.task_uuid,bbt.id   
                from bd_backup_timepoint as bbt,bd_storage_resource as store,cdp_vol_backup_agent as agent
                where agent.timepoint_uuid = bbt.timepoint_uuid and bbt.storage_uuid = store.storage_uuid
                      and bbt.available_flag = ? and agent.storage_location !=? ';
        if (!empty($authUser)) {  // 表示有管理的用户
            $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
            $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
            $sql .= " and bbt.user_uuid in " . $useruuidArr . " group by bbt.task_uuid ORDER BY bbt.id";
            $sqlParams = array($available_flag,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$start,$length);
        }else{
            $sql .= " and bbt.user_uuid = ? group by bbt.task_uuid ORDER BY bbt.id";
            $sqlParams = array($available_flag,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$user_uuid,$start,$length);
        }

        $sql .= " limit ?,?";
        $data = $this->dbSelect($sql, $sqlParams);
        $host = array();
        $i = 0;
        $host["data"] = array();
        $hostInfoList = [];
        $userUuidData = array();
        if(!empty($data)){
            foreach ($data as $d){
                $masterAgentuuid = $d['master_agent_uuid'];
                $storage_location = $d['storage_location'];
                $master_agent_detail = json_decode($d['master_agent_detail']);
                $hostName = $master_agent_detail->hostname;
                $agentIp = $master_agent_detail->ip;
                $agentName = $master_agent_detail->agent_name;
                $taskName = $d['task_name'];
                $taskuuid = $d['task_uuid'];

                if (!empty($agentName) && $agentName != $agentIp) {
                    $hostInfo =  $agentName ? $agentName : $hostName;
                }else {
                    $hostInfo = $hostName;
                }

                $os_type = $master_agent_detail->os_type;
                $os_version = $master_agent_detail->os_version;
                $sysIcon = '<img src ="./img/platform/linux.png">';
                if(strpos($os_version,'Windows') !== false){
                    $sysIcon = '<img src ="./img/platform/windows.png">';
                }
                if($storage_location==xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'] || $storage_location==xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']){
                    continue;
                }

                $agentInfoExists = in_array($taskuuid.$masterAgentuuid, $hostInfoList); //判断主机IP或者uuid是否已存在数组中，兼容同一主机使用不同ip或者不同主机使用相同ip的情况
                if($agentInfoExists ){
                    continue;
                }

                $currentTaskUUID = (NEW VolcdpData())->getCurrentAllTaskUUID();//得到当前任务所有uuid
                $taskAvailable = in_array($d['task_uuid'], $currentTaskUUID);
                $taskNameStr = $taskAvailable ? $taskName : $taskName . "(" . xphp_get_lang('WEB_PLATFORM_DC_VM_TASK_DELETED') . ")";

                $host["rows"][] = array(
                    'host_info' => $hostInfo,
                    'agent_ip' => $agentIp,
                    'task_name' => $taskNameStr,
                    'os_version' => $sysIcon.$os_version,
                    'master_agent_uuid' => $masterAgentuuid,
                    'storage_location' => $storage_location,
                    'os_type' => $os_type,
                    'task_uuid' => $d['task_uuid']
                );
                $i++;
            }
        }
        $dataList = $host["data"];
        //判断是否具有操作权限
        $authUser = $_SESSION['authUser']['vol_cdp_protect_operate'] ?? [];
        $checkOperate = xphp_check_operate(xphp_get_user_info()['userUuid'], $userUuidData, $authUser);
        if (!$checkOperate) {
            $dataList = array();
        }

        $countNum = count($dataList);
        // 2023-03-24 NOTE: 分页，这里做简单修改，不改逻辑。采用内存分页，因为上面对查询记结果进行了二次筛选，不能直接对数据库进行分页
        $host['total'] = $i;
        return $host;
    }

    /**
     * 获取选择客户端及卷对应的标签点信息
     * @param host_uuid:选择主机UUID,vol_uuid:当前选中卷
     */
    public  function getVolTagPointInfo($params)
    {
        $hostUuid = $params['host_uuid'];
        $timeList = $params['time_list']; //通过时间轴查看标签点的详情
        $start = $params['offset'];
        $length = $params['limit'];
        $sortColumn = 0;
        $sortType = $params['sortType'];
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
                      agent.timepoint_uuid = bt.timepoint_uuid and bt.task_uuid = ?";

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
            $data = $this->dbSelect($sql, $sqlParams);
            $countData = $this->dbSelect($sqlCount,$countParams);

        }
        $volumes = array();
        $icon = '<img src ="./img/platform/timepoint.png"> ';
        if(!empty($data)){
            $j = 1;
            foreach ($data as $d){
                $index_id = $j++;
                $label_id = $d['id'];
                $label_time_str = $d['label_timestamp'];
                $description = $d['remarks'];
                $storage_location = $d['storage_location'];
                $volumes["rows"][] = array(
                    'label_time_str' => $label_time_str,
                    'index_id' => $index_id,
                    'icon_label_time_str' => $icon.$label_time_str,
                    'description' => $description,
                    'storage_location' => $storage_location,
                    'label_id' => $label_id,
                );
            }
        }
        $countNum = count($countData);
        $volumes["total"] = $countNum;
        return  $volumes;
    }

    /**
     * 获取选择客户端生成的事件信息
     * @param host_uuid:选择主机UUID,vol_uuid:当前选中卷
     */
    public  function getAgentEventInfo($params)
    {
        $volUuid = $params['vol_uuid'];
        $hostUuid = $params['host_uuid'];
        $nodeuuid = $params['nodeuuid'];
        $start = $params['offset'];
        $length = $params['limit'];
        $sortColumn = 0;
        $sortType = $params['sortType'];
        $timeList = $params['time_list']; //通过时间轴查看事件点的详情
        $sortArr = array('event.event_time');
        $taskuuid = $params['task_uuid'];

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
        $icon = '<img src ="./img/platform/timepoint.png"> ';
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
                    'iconEventTimeStr' => $icon.$eventTimeStr,
                    'eventTypeDesc' => $this->getEventTypeDesc($eventType),
                    'eventLevelStr' => $this->getEventLevelDesc($eventLevel),
                    'eventDescription' => $eventDescription,
                    'eventTimeStr' => $eventTimeStr,
                    'eventId' => $eventId,
                );
            }
        }
        $countNum = count($countData);
        $records["total"] = $countNum;
        return  $records;
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
     * 得到事件对应的级别
     * @param unknown $eventLevel
     */
    private function getEventLevelDesc($eventLevel)
    {
        $eventClass = "label label-sm label-info";   //一般日志
        $levelStr = xphp_get_lang('WEB_PLATFORM_DES_GENERAL');
        if(xphp_get_config('log','LOGLEVEL')['WARN'] == $eventLevel){
            $eventClass = "label label-sm label-warning";
            $levelStr = xphp_get_lang('WEB_PLATFORM_DES_WARNING');
        }elseif(xphp_get_config('log','LOGLEVEL')['ERROR'] == $eventLevel){
            $eventClass = "label label-sm label-danger";
            $levelStr = xphp_get_lang('WEB_PLATFORM_DES_ERROR');
        }
        $eventLevelStr = '<span class="'.$eventClass.'">'.$levelStr.'</span>';
        return $eventLevelStr;
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

    /**
     * 获取区间范围内的标签点信息
     * @param unknown $startTime
     * @param unknown $finishTime
     * @return fetchAll()
     */
    private function getTimeIntervalData($startTime,$finishTime)
    {
        $sql = "select label.id,label.label_timestamp,label.remarks,agent.storage_location
                    from cdp_vol_agent_label_set as label,cdp_vol_backup_agent as agent
                    where agent.id = label.backup_agent_id and agent.master_agent_uuid =?
                        and label.label_timestamp BETWEEN '". $startTime ."' and '". $finishTime."'";
        return $sql;
    }

    /**
     * 将选中的客户端对应的卷信息进行组装（从JS移到PHP处理是为了满足全选需求）
     * @param $params
     * @return false|string
     */
    public function getDataSourceVolInfo($params)
    {
        $data = $params['volinfo'];
        $start = $params['offset'];
        $length = $params['limit'];
        $i = 0;
        foreach ($data as $d){
            $voluuid = $d['vol_uuid'];
            $volName = $d['vol_name'];
            $capacity = $d['capacity'];
            $capacityValue = $d['capacity_value'];
            $osType = $d['os_type'];
            $timePoint = $d['new_timestamp'];
            $timeIcon = '<img src ="./img/platform/timepoint.png"> ';
            $volIcon = '<img src ="./img/vm/pool.png"> ';
            $standbyVol = $d['standby_vol'];
            $isBootVolume = $d['is_boot']; //系统卷类型：0 非系统卷，1：系统卷
            $isDisabled = "";
            $targetInfo = '<select class="form-control input-sm" name="targetvol" id='."target_volume_".$i.' >
					<option value = "'.$capacityValue.'" data-time ="'.$timePoint.'" 
					data-option ="0" data-hostvoluuid="'.$voluuid.'" data-isbootvol = "'.$isBootVolume.'" >'.xphp_get_lang('UI_VOL_CDP_SELECT_RECOVER_TARGET_VOL').' </option></select>';
            $volumes['rows'][] = array(
                'isDisabled' => $isDisabled,
                'vol_name' => $volIcon.$volName,
                'capacity' => $capacity,
                'timepoint' => $timeIcon.'<span id = "recovery_time_'.$voluuid.'">'.$timePoint.'</span>',
                'target_info' => $targetInfo,
                'time_point' => $timePoint,
                'vol_uuid' => $voluuid,
                'os_type' => $osType,
                'is' => $isBootVolume,
            );
            $i+=1;
        }
        $volumes['total'] = count($volumes['rows']);
        return $volumes;
    }


    /**
     * 获取客户端备份集信息,取最新时间点
     * @param node:node_uuid,agent_uuid:agent_uuid
     */
    public function getClientBackupSetNewTime($params)
    {
        $user_uuid = xphp_get_user_info()['userUuid'];
        $agent_uuid = $params['agent_uuid'];
        $data_source = $params['data_source'];
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
        }else if($newTime<$timestamp && $data_source==0){
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
     * 获取客户端最早备份时间点
     * @param $params
     * @return string
     */
    public function getClientFirstTime($params){
        $user_uuid = xphp_get_user_info()['userUuid'];
        $node_uuid = $params['node_uuid'];
        $agent_uuid = $params['agent_uuid'];
        $data_source = $params['data_source'];
        $createTaskType = $params['task_type'];
        $taskuuid = $params['task_uuid'];
        $firsTime = '--';
        if($data_source==xphp_get_desc('Volcdp','RECOVERY_DATA_SOURCE')['STANDBY'] && $createTaskType = xphp_get_config('task')['TASKTYPE']['VOL_CDP_TAKEOVER']){
            $firstSql = "select bbt.timepoint,bbt.timepoint_uuid
                    FROM cdp_vol_backup_agent agent,bd_backup_timepoint bbt
                    WHERE agent.timepoint_uuid = bbt.timepoint_uuid and agent.master_agent_uuid =? and bbt.user_uuid = ?
                        and agent.storage_location !=? and bbt.task_uuid =? ORDER BY bbt.timepoint limit 0,1";
            $firstData = $this->dbSelect($firstSql, array($agent_uuid,$user_uuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid));
        }else{
            $firstSql = "select bbt.timepoint,bbt.timepoint_uuid
                    FROM cdp_vol_backup_agent agent,bd_backup_timepoint bbt
                    WHERE agent.timepoint_uuid = bbt.timepoint_uuid and agent.master_agent_uuid =? and bbt.user_uuid = ?
                        and agent.storage_location !=? and agent.storage_location !=? and bbt.task_uuid =? ORDER BY bbt.timepoint limit 0,1";
            $firstData = $this->dbSelect($firstSql, array($agent_uuid,$user_uuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],$taskuuid));
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
    public function getClientNewTime($params){
        $user_uuid = xphp_get_user_info()['userUuid'];
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
        if(($data_source==xphp_get_desc('Volcdp','RECOVERY_DATA_SOURCE')['STANDBY']
                || $data_source==xphp_get_desc('Volcdp','RECOVERY_DATA_SOURCE')['UNKNOWN'])
            && $createTaskType = xphp_get_config('task')['TASKTYPE']['VOL_CDP_TAKEOVER']){
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

    /**
     * 获取客户端备份集信息
     */
    public function getClientBackupSetVolInfo($params)
    {
        $agent_uuid = $params['agent_uuid'];
        $createTaskType = $params['task_type'];
        $newTime = $this->getClientNewTime($params);
        $taskuuid = $params['task_uuid'];
        $user_uuid = xphp_get_user_info()['userUuid'];
        $data = $this->getAgentData($agent_uuid,$createTaskType,$user_uuid,$taskuuid);
        $list = array();
        $srcEndTimeStamp = $data[0]['src_end_timepoint'];
        $timepointuuid = $data[0]['timepoint_uuid'];
        $list[] = array(
            'node_uuid' => $data[0]['node_uuid'],
            'vol_set' => $this->getClientVolSet($agent_uuid,$srcEndTimeStamp,$newTime,$createTaskType,$timepointuuid,$taskuuid),
        );
        return $list;
    }

    /**
     * 获取选中客户端可供恢复的时间区间
     */
    public function ClientBackupSetTimeRange($params)
    {
        $user_uuid = xphp_get_user_info()['userUuid'];
        $agent_uuid = $params['agent_uuid'];
        $data_source = $params['data_source'];
        $createTaskType = $params['task_type'];
        $taskuuid = $params['task_uuid'];
        $firsTime = $this->getClientFirstTime($params);
        $newTime = $this->getClientNewTime($params);

        $data = $this->getAgentData($agent_uuid,$createTaskType,$user_uuid,$taskuuid);

        $srcEndTimeStamp = $data[0]['src_end_timepoint'];
        $timestamp = date('Y-m-d H:i:s',$srcEndTimeStamp);
        if($newTime=="--"){
            $newTime = $timestamp;
        }else if($newTime<$timestamp && $data_source==0){
            $newTime = $timestamp;
        }
        $list = array();
        $list[] = array(
            'time_range' => $firsTime." -- ".$newTime
        );
        return $list;
    }

    /**
     * 获取恢复目标客户端磁盘、卷及挂载点等信息
     */
    public function getRecoveryTargetHost($params)
    {
        $userUuid = xphp_get_user_info()['userUuid'];
        $nodeuuid = $params['node_uuid'];
        $masterAgentuuid = $params['master_uuid'];
        $masterOsType = $params['master_os_type'];
        $standbyuuid = $params['standby_uuid'];
        $agentuuidArr = (new Client())->getClientUuids();
        $list = array();
        if (empty($agentuuidArr)) {
            return json_encode($list);
        }
        $sql = "select id, agent_uuid, agent_name, hostname, ip, os_type, authorization_module, online_flag,agent_type,net_model
                from bd_agent
                where  (agent_type =? or agent_type = ?) ";

        if (is_array($agentuuidArr)) {
            $agentuuidArrStr = "('" . implode("','", $agentuuidArr) . "')";
            $sql .=" and agent_uuid in {$agentuuidArrStr} ";
        }

        if(!empty($standbyuuid)){
            $sql .= " and agent_uuid =?";
            $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE','resources')['NORMAL'],xphp_get_config('client', 'AGENT_TYPE','resources')['MEMORY_OS'],$standbyuuid);
        }else{
            $sqlParams = array(xphp_get_config('client', 'AGENT_TYPE','resources')['NORMAL'],xphp_get_config('client', 'AGENT_TYPE','resources')['MEMORY_OS']);
        }
        $data = $this->dbSelect($sql,$sqlParams);
        $list[] = array(
            "uuid" => "",
            "text" => xphp_get_lang('UI_VOL_CDP_TAKEOVER_TARGET_MACHINE_SELECT'),
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
            $hostVolInfo = $this->getHostVolInfoByUUID($agentUUID,$osType);

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
                'vol_info' => $hostVolInfo['vol_info'],
                'disk_info' => $this->getHostDiskInfo($agentUUID),
                'mount_info' => $hostVolInfo['mount_info'],
                'agent_type' => $host_info['agent_type'],
                'net_model' => intval($host_info['net_model']),
                'title' => $title,
            );
        }
        return $list;
    }

    /**
     * 获取主机磁盘信息
     */
    private function getHostDiskInfo($uuid){
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
                "capacity" =>v1_calsize($v['capacity'], true),
                "free_space" => $v['free_space'],
                "display_name" => $v['display_name']
            );
        }
        return $list;
    }

    /**
     * 检查选择的恢复目标是否有被其它恢复任务选中
     * -----当前仅限制是否有恢复作业，后续会完善到是否有备份作业、恢复作业是否处于运行中等
     * @param unknown $agentUuids
     */
    private function taskExist ($agentUUID)
    {
        $sql = "SELECT task.task_status,task.task_name from bd_task task,cdp_vol_task vol_task
            where vol_task.task_uuid = task.task_uuid and task.task_type = ? and vol_task.recovery_target_agent_uuid = ? and task.delete_flag = ? ";
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
        $agentSql = "select agent_type from bd_agent where agent_uuid = ? ";
        $data = $this->dbSelect($agentSql, array($uuid));
        $agentType = 1;
        if(!empty($data)){
            $agentType = $data[0]['agent_type'];
        }

        $sql = "select vol.vol_name,vol.vol_uuid,vol.capacity,vol.free_space,vol.is_boot,vol.display_name,vol.mount_point,vol.detail
                from bd_agent_disk as disk,bd_agent_vol as vol
                where vol.disk_uuid = disk.disk_uuid and disk.agent_uuid = ?";
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
     * 获取客户端对应的备份数据
     * @param node_uuid,agent_uuid;
     */
    public function getAgentBkTimelineData($params)
    {
        $nodeUuid = $params['node_uuid'];
        $agentUuid = $params['agent_uuid'];
        $timeIntervalType = $params['time_interval'];
        $volUuid = $params['vol_uuid'];
        $taskType = $params['task_type'];
        $startTime = $params['startTime'];
        $endTime = $params['endTime'];
        $taskuuid = $params['task_uuid'];
        $timeLineData = array();
        $records = array();
        $authUser = $_SESSION['authUser']['vol_cdp_protect_look'] ?? [];  // 关联管理用户判断 存储资源 - 查看
        $useruuid = xphp_get_user_info()['userUuid'];

        if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_TEN_MIN']
            || $timeIntervalType==xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_ONE_HOUR']){
            $sql = "select se.id,se.backup_timestamp 
                from cdp_vol_backup_agent_second_level_data_flow se,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
                where agent.master_agent_uuid = ? and agent.id = se.backup_agent_id and agent.storage_location!=? 
                    and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid = ?";
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid ='{$useruuid}' ";
            }

            $sql .= " and agent.storage_location!=? ";
            $sqlParams = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']);

            if($volUuid!=""){
                $sql.=" and se.vol_uuid ='{$volUuid}'";
            }
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
               where agent.master_agent_uuid = ? and agent.id = mi.backup_agent_id and agent.storage_location!=? 
                and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid = ?";
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = '{$useruuid}' ";
            }
            $sql.= " and agent.storage_location!=? ";
            $sqlParams = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']);

            if($volUuid!=""){
                $sql.=" and mi.vol_uuid ='{$volUuid}'";
            }
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
                where agent.master_agent_uuid = ? and agent.id = hour.backup_agent_id and agent.storage_location!=? 
                    and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid = ?";
            if (!empty($authUser)) {  // 表示有管理的用户
                $useruuidArr = array_merge([xphp_get_user_info()['userUuid']], $authUser);
                $useruuidArr = "('" . implode("','", $useruuidArr) . "')";
                $sql .= " and bbt.user_uuid in " . $useruuidArr;
            }else{
                $sql .= " and bbt.user_uuid = '{$useruuid}' ";
            }
            $sql.= " and agent.storage_location!=? ";
            $sqlParams = array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']);

            if($volUuid!=""){
                $sql.=" and hour.vol_uuid ='{$volUuid}'";
            }
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

    private function  getSecondLevelDataCustom($startTime, $endTime, $agentUuid, $timeIntervalType,$taskuuid){
        $timeIntervalValue = strtotime($endTime) - strtotime($startTime);  // 最多一个小时 3600点
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType, $endTime, $timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "select se.backup_timestamp,se.data_flow,se.label_point_count,se.event_point_count
            from cdp_vol_backup_agent_second_level_data_flow se,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
            where se.backup_timestamp between '". $startTime ."' and '". $endTime ."' and agent.master_agent_uuid = ? 
                and agent.id = se.backup_agent_id and agent.storage_location!=? and 
                agent.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ? ";
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
    private function getMinuteLevelDataCustom($startTime, $endTime,$agentUuid,$timeIntervalType,$taskuuid)
    {
        $timeIntervalValue = ceil((strtotime($endTime) - strtotime($startTime))/60);
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$endTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "SELECT mi.backup_end_timestamp,mi.data_flow,mi.label_point_count,mi.event_point_count
            from cdp_vol_backup_agent_minute_level_data_flow mi,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
            where mi.backup_end_timestamp BETWEEN '". $startTime ."' and '". $endTime ."' and agent.master_agent_uuid = ? 
                and agent.id = mi.backup_agent_id and agent.storage_location!=? 
                and agent.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ?";
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

    private function getHourLevelDataCustom($startTime, $endTime,$agentUuid,$timeIntervalType){
        $timeIntervalValue = floor((strtotime($endTime) - strtotime($startTime))/60/60);
        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$endTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "SELECT hour.backup_end_timestamp,hour.data_flow,hour.label_point_count,hour.event_point_count
            from cdp_vol_backup_agent_hour_level_data_flow hour,cdp_vol_backup_agent agent
            where hour.backup_end_timestamp BETWEEN '". $startTime ."' and '". $endTime ."'
                  and agent.master_agent_uuid = ? and agent.id = hour.backup_agent_id and agent.storage_location!=?";
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
    private function getHourLevelData($latestTime,$agentUuid,$timeIntervalType){
        if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_THIRTY_DAY']){  //最近30天数据,读取小时级表，对应的最新时间减720小时
            $timeIntervalValue = 720;  //30天折合720小时
        }else if($timeIntervalType == xphp_get_desc('Volcdp','BACKUP_SET_INTERVAL_TYPE')['LAST_NINETY_DAY']){  //最近90天数据,读取小时级表，对应的最新时间减2160小时
            $timeIntervalValue = 2160;  //30天折合2160个小时
        }
        $intervalTimestamp = $timeIntervalValue*60*60;  //间隔时间戳
        $timeDif = strtotime($latestTime)-$intervalTimestamp; //间隔时间

        $difTimeStr =  date('Y-m-d H:i:s',$timeDif);

        $bakcupFlowData = $this->createDefaultTimeList($timeIntervalType,$latestTime,$timeIntervalValue); //获取时间间隔周期默认的dataList
        $sql = "SELECT hour.backup_end_timestamp,hour.data_flow,hour.label_point_count,hour.event_point_count
            from cdp_vol_backup_agent_hour_level_data_flow hour,cdp_vol_backup_agent agent
            where hour.backup_end_timestamp BETWEEN '". $difTimeStr ."' and '". $latestTime ."'
                  and agent.master_agent_uuid = ? and agent.id = hour.backup_agent_id and agent.storage_location!=?";
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
     * 获取分钟级表对应的时间轴数据
     * @param $latestTime:当前客户端最新可用时间点,$agentUuid:客户端uuid,$timeType:查询时间区间类型;
     * @return $dataTimeArray:满足条件的时间列表;
     */
    private function getMinuteLevelData($latestTime,$agentUuid,$timeIntervalType,$taskuuid){
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
        $sql = "SELECT mi.backup_end_timestamp,mi.data_flow,mi.label_point_count,mi.event_point_count
            from cdp_vol_backup_agent_minute_level_data_flow mi,cdp_vol_backup_agent agent,bd_backup_timepoint bbt 
            where mi.backup_end_timestamp BETWEEN '". $difTimeStr ."' and '". $latestTime ."' and agent.master_agent_uuid = ? 
                and agent.id = mi.backup_agent_id and agent.storage_location!=? 
                and agent.timepoint_uuid = bbt.timepoint_uuid and bbt.task_uuid = ?";
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
            where se.backup_timestamp between '". $difTimeStr ."' and '". $latestTime ."' and agent.master_agent_uuid = ? 
                and agent.id = se.backup_agent_id and agent.storage_location!=? and agent.timepoint_uuid = bbt.timepoint_uuid 
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
     * 校验输入时间是否有效
     *  @param host_uuid:选择主机UUID,timepoint:配置时间
     */
    public function verifyTimepointisValid($params)
    {
        $agentUuid = $params['agent_uuid'];
        $timepoint = $params['timepoint'];
        $volUuid = $params['vol_uuid'];
        $createTaskType = $params['task_type']; //创建任务类型
        $taskuuid = $params['task_uuid']; //客户端对应的任务名
        $matchingRecord= 0;
        $unixTimeInfo = $this->getUnixTimetamp($timepoint);
        $unixTime = $unixTimeInfo[0];
        //获取有效的时间区间
        $sql = "SELECT time.src_start_timepoint,time.src_end_timepoint,time.timepoint_uuid,time.storage_uuid,st.node_uuid,
                    vol.storage_status,time.encrypted_flag,time.detail,agent.storage_location,agent.master_agent_detail    
                FROM cdp_vol_backup_agent agent,bd_backup_timepoint time,bd_storage_resource st,cdp_vol_backup_vol_set vol 
                WHERE agent.timepoint_uuid = time.timepoint_uuid and agent.master_agent_uuid = ? and time.storage_uuid= st.storage_uuid 
                    and vol.backup_agent_id = agent.id and time.task_uuid = ?";
        if($createTaskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY']){
            $sql = $sql." and (agent.storage_location!=? and agent.storage_location!=?)";
            $data = $this->dbSelect($sql,array($agentUuid,$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']));
        }else{
            $sql = $sql." and agent.storage_location!=?";
            $data = $this->dbSelect($sql,array($agentUuid,$taskuuid,xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN']));
        }
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

                $detail = json_decode($d['detail'],true);
                if($d["encrypted_flag"]==1) { // 时间点加锁
                    $encryptedFlag = true;
                }else {
                    $encryptedFlag = false;
                }
                $passwordAutoFlag = intval($detail['password_auto_flag'])==1 ? true: false;
                $verifyResult =  $this->verifyBackupSetStatus($storageStatus,$createTaskType,$agentUuid,$timepoint,$timepointUuid);
                if($storageLocation==xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY']){  //如果当前校验数据为备机，择获取最新点
                    $standbySql = "select vol.end_timestamp 
                                from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol, bd_backup_timepoint bbt 
                                where vol.backup_agent_id = agent.id and agent.master_agent_uuid = ? and agent.storage_location =? 
                                  and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid = ?";
                    $standbyData = $this->dbSelect($standbySql,array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],$taskuuid));
                    $newTimeStr = $standbyData[0]['end_timestamp'];
                }else{
                    $standbySql = "select vol.end_timestamp 
                                from cdp_vol_backup_agent agent,cdp_vol_backup_vol_set vol , bd_backup_timepoint bbt 
                                where vol.backup_agent_id = agent.id and agent.master_agent_uuid = ? and agent.storage_location !=? 
                                    and agent.storage_location !=? and bbt.timepoint_uuid = agent.timepoint_uuid and bbt.task_uuid = ? ";
                    $standbyData = $this->dbSelect($standbySql,array($agentUuid,xphp_get_desc('Volcdp','DATA_SOURCE')['STANDBY'],xphp_get_desc('Volcdp','DATA_SOURCE')['UNKNOWN'],$taskuuid));
                    $newTimeStr = $standbyData[0]['end_timestamp'];
                }
                if($unixTime>=$srcStartTime && $unixTime<=$srcEndTime){
                    $storageUuid = $d['storage_uuid'];
                    $nodeUuid = $d['node_uuid'];
                    $timeVolInfo = $this->getSetTimeVolInfo($agentUuid,$timepointUuid,$timepoint,$volUuid,$nodeUuid,$storageUuid,$verifyResult);
                    $list = array(
                        'time_vol_info' => $timeVolInfo,
                        'encrypted_flag' => $encryptedFlag,
                        'password_auto_flag' => $passwordAutoFlag,
                        'timepoint_uuid' => $timepointUuid,
                        'storage_location' =>$storageLocation,
                        'new_time_str' => $newTimeStr,
                        'os_type' =>$osType,
                    );

                }
            }
        }
        return $list;
    }

    /**
     * 时间时间字符串转换unix时间戳
     * @param unknown $params
     */
    private function getUnixTimetamp($params)
    {
        $sql = "SELECT UNIX_TIMESTAMP(?)";
        $data = $this->dbSelect($sql,array($params));
        return $data[0];
    }

    /**
     * 获取有效时间区间内的卷信息
     * @param unknown $agentUuid
     * @param unknown $timepointUuid
     */
    private function getSetTimeVolInfo($agentUuid,$timepointUuid,$timepoint,$volUuid,$nodeUuid,$storageUuid,$verifyResult)
    {
        $volSql = "select vol.vol_display_name,vol.vol_uuid,vol.standby_vol_uuid,vol.capacity,vol.is_boot,
                    agent.master_agent_detail,vol.detail 
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

                $volDetail = json_decode($vol['detail']);
                $volType = $volDetail->vol_type;
                $systemVolume = false;
                if($volType & xphp_get_desc('Volcdp','VOL_TYPE')['BD_SYSTEM_VOLUME']){
                    $systemVolume = true;
                }
                $list[] = array(
                    'vol_name' => $vol['vol_display_name'],
                    'new_timestamp' =>$timepoint,
                    'vol_uuid' => $vol['vol_uuid'],
                    'capacity' => v1_calsize($vol['capacity'], true),
                    'capacity_value' => $vol['capacity'],
                    'standby_vol' => $this->getVolInfoByVoluuid($standbyVoluuid,$agentUuid),
                    'node_uuid' => $nodeUuid,
                    'storage_uuid' => $storageUuid,
                    'is_boot' => $vol['is_boot'],
                    'os_type' => $osType,
                    'verify_result' =>$verifyResult,
                    'system_volume' =>$systemVolume,
                );
            }
        }
        return $list;
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
            if($agent_ip==""){
                $agent_ip = "--";
            }
            $bkAgentArry = array(
                "agent_name" =>$agent_name,
                "agent_ip" => $agent_ip,
                "os_type" => $os_type,
                "hostname" => $host_name,
            );
        }
        return $bkAgentArry;
    }

    /**
     * 校验备份集的状态在对应的创建任务类型下是否可用
     * @param integer $storageStatus：备份集状态
     * @param integer $createTaskType：任务创建类型
     */
    public function verifyBackupSetStatus($storageStatus,$createTaskType,$agentUuid,$timepoint,$timepointUuid)
    {
        if($createTaskType == xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY']){  //恢复任务校验
            $taskDesc = xphp_get_lang('WEB_OS_RECOVERY_TASK');
        }else if($createTaskType ==xphp_get_config('task','TASKTYPE')['VOL_CDP_TAKEOVER']){  //接管任务校验
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

                if($newTimepointuuid == $timepointUuid && xphp_get_config('task','TASKTYPE')['RUNNING'] && $createTaskType==xphp_get_config('task','TASKTYPE')['VOL_CDP_RECOVERY']){
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
     * 得到卷CDP恢复任务名
     * @param unknown $params
     */
    public function getVolCdpRecoverTaskName($params){
        $taskName =$params['task_name'];
        return array(
            'task_name' => $this->getValidTaskName($taskName)
        );
    }

    /**
     * 获取可用的任务名
     * @param string $taskName  任务名前缀
     * @return string
     */
    public function getValidTaskName($taskName){
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
     * 公共方法
     */
    private function getAgentData($agent_uuid,$createTaskType,$user_uuid,$taskuuid)
    {
        $sql = "SELECT bbt.timepoint_uuid,bbt.src_end_timepoint,agent.storage_location,st.storage_uuid,
                st.node_uuid,bbt.encrypted_flag,bbt.detail,agent.master_agent_detail 
            FROM cdp_vol_backup_agent as agent,bd_backup_timepoint as bbt,bd_storage_resource as st 
            WHERE st.storage_uuid = bbt.storage_uuid and  agent.timepoint_uuid = bbt.timepoint_uuid 
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
    public function getClientVolSet($agent_uuid,$srcEndTimeStamp,$timestamp,$createTaskType,$timepointuuid,$taskuuid)
    {
        $sql = "select DISTINCT vol.vol_display_name,vol.vol_uuid,vol.standby_vol_uuid,vol.capacity,
                agent.master_agent_detail,vol.is_boot,vol.storage_status,vol.detail 
            from cdp_vol_backup_agent as agent,cdp_vol_backup_vol_set as vol, bd_backup_timepoint as bbt
            where vol.backup_agent_id = agent.id and agent.master_agent_uuid =? and agent.timepoint_uuid = bbt.timepoint_uuid 
                and bbt.src_end_timepoint = ? and bbt.task_uuid = ?";
        $data = $this->dbSelect($sql,array($agent_uuid,$srcEndTimeStamp,$taskuuid));
        $list = array();
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
            $list[] = array(
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
        return $list;
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

}
