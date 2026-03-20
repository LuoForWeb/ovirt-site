<?php

namespace app\v2\file\v0\logic;

use app\v2\common\logic\Base;
use app\v2\common\logic\JobInfo;
use \app\v2\job\v0\logic\JobInfo as JobInfos;
use app\v2\tape\v0\logic\TapeInfo;
use app\v2\common\logic\Backup;
use app\v2\hadoop\v0\logic\HadoopBackUp;
use app\v2\hadoop\v0\logic\HadoopJobInfo;
use app\v2\s3\v0\logic\ObsJobInfo;

/**
 * note          文件任务信息 logic
 * @author       wuxian@vinchin.com
 * @date         2025/8/27 15:20
 * @version      1.0.0
 * @copyright    Copyright 2025 vinchin.com
 */
class FileJobInfo extends Base
{

    /**
     * 任务详情: 得到文件模块任务基本信息
     * @param array $params 参数
     * @return array 任务基本信息
     */
    public function getBasicInfo(array $params)
    {
        $taskUUID = $params['jobs_uuid'];
        $info = JobInfos::instance()->getJobInfo($taskUUID);
        $sub_module_type = $params['sub_module_type'];
        $tableNickname = '';//表名缩写
        $tableName = '';//表名
        $sqlJoin = '';//拼接的join语句
        $tableFields = '';//拼接的字段
        switch (intval($sub_module_type)) {
            case xphp_get_config('module', 'SUBMODULE_TYPE')['FS']:
            case xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']:
            case xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']:
                $tableNickname = 'ft';
                $tableName = 'fs_task';
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['NAS']:
                $tableNickname = 'nt';
                $tableName = 'nas_task';
                break;
            default:
                return array('flag' => false);
        }
        $sqlJoin = "JOIN {$tableName} {$tableNickname} ON bt.task_uuid = {$tableNickname}.task_uuid";
        $tableFields = "
            {$tableNickname}.snap_shot_flag,
            {$tableNickname}.detail,
            {$tableNickname}.file_archive_flag,
            {$tableNickname}.skip_file_alarm_flag,
            {$tableNickname}.skip_file_alarm_min_num,
            {$tableNickname}.skip_file_alarm_min_ratio,
            {$tableNickname}.permission_operate_flag,
            {$tableNickname}.same_file_strategy,
            {$tableNickname}.link_file_pass_flag,
            {$tableNickname}.dir_tree_recovery_flag
        ";
        if (intval($sub_module_type) == xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP'] 
        || intval($sub_module_type) == xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']) {
            $tableFields .= "
                ,{$tableNickname}.proxy_uuid
            ";
        }
        $sql = "SELECT DISTINCT
                    bt.task_uuid,
                    bt.task_name,
                    bt.module_type,
                    bt.sub_module_type,
                    bt.task_type,
                    bt.task_status,
                    bt.thread_num,
                    bt.worm_flag,
                    bt.virus_scan_flag,
                    bt.integrity_check_flag,
                    bt.strategy_id,
                    UNIX_TIMESTAMP( bt.create_time ) AS create_time,
                    bt.transport_ip_segment,
                    bt.node_uuid,
                    bt.storage_uuid,
                    bt.current_stage,
                    bt.stage_percent,
                    bu.user_name,
                    UNIX_TIMESTAMP( bs.next_start_time ) AS next_start_time,
                    bt.ignore_resource_limiting_flag,
                    bt.user_uuid,
                    bri.total_object_size,
                    bri.total_object_completed_size,
                    bri.speed,
                    UNIX_TIMESTAMP( bri.start_time ) AS start_time,
                    {$tableFields},
                    fpl.new_root_path,
                    bt.task_orchestration_plan_flag,
                    btsc.worm_protection_time,
                    btsc.virus_scan_config_list,
                    btsc.integrity_check_strategy,
                    btsc.backup_integrity_check_full_error_policy,
                    btsc.backup_integrity_check_inc_error_policy,
                    btsc.recovery_integrity_check_error_policy,
                    brs.network_retry_times,
                    brs.network_retry_interval,
                    brs.op_retry_times,
                    brs.op_retry_interval,
                    brs.task_retry_object,
                    brs.task_retry_times,
                    brs.task_retry_interval,
                    bsr.worm_flag AS worm_storage_flag,
                    bsrp.storage_pool_type,
                    bss.compressed_flag, 
                    bss.compress_method, 
                    bss.encrypted_flag,
                    bss.password,
                    bss.password_auto_flag, 
                    bss.encrypt_method, 
                    bts.encrypt_flag, 
                    bts.compress_flag,
                    bts.network_uuid, 
                    bts.reconnect_times, 
                    bts.reconnect_interval, 
                    bts.encrypt_method as transport_method,
                    bts.network_pool_uuid,
                    bres.strategy_type,
                    bres.number,
                    bres.strategy_mode,
                    bal.detail AS bal_detail
                FROM
                    bd_task bt
                    JOIN bd_user bu ON bt.user_uuid = bu.user_uuid
                    JOIN bd_running_info bri ON bt.task_uuid = bri.task_uuid
                    JOIN bd_strategy bs ON bt.strategy_id = bs.strategy_id
                    {$sqlJoin}
                    JOIN fs_path_list fpl ON bt.task_uuid = fpl.task_uuid
                    JOIN bd_task_safe_config btsc ON bt.task_uuid = btsc.task_uuid
                    JOIN bd_retry_strategy brs ON bt.task_uuid = brs.task_uuid
                    JOIN bd_task_agent_list bal ON bt.task_uuid = bal.task_uuid
                    LEFT JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid 
                    LEFT JOIN bd_storage_resource_pool bsrp ON bt.storage_pool_uuid = bsrp.storage_pool_uuid
                    LEFT JOIN bd_storage_strategy bss ON bt.strategy_id = bss.strategy_id
                    LEFT JOIN bd_transport_strategy bts ON bt.strategy_id = bts.strategy_id
                    LEFT JOIN bd_reserved_strategy bres ON bt.strategy_id = bres.strategy_id
                WHERE
                    bt.task_uuid = ? GROUP BY bt.task_uuid";
        $data = $this->dbSelect($sql, array($taskUUID));
        $basicInfo = array();
        $tapeHandler = new TapeInfo();
        $Job = new JobInfo();
        if(!$data){
            return array('flag' => false);
        }
        foreach ($data as $d){
            $info['storage_info']['storage_pool_type'] = $d['storage_pool_type'];
            $detail = json_decode($d['detail'], true);
            $basicInfo = array(
                'job_name' => $info['job_name'],
                'job_type' => $info['job_type'],
                'submodle_type' => $d['sub_module_type'],
                'job_status'    => $info['job_status'],
                'total_size' => $info['total_size'],
                'complate_size' => $info['complate_size'],
                'create_time' => $info['create_time'],
                'start_time' => $info['start_time'],
                'interval_time' => $info['interval_time'],
                'thread_num' => $info['thread_num'],
                'timestamp' => $info['timestamp'],
                'progress' => $info['progress'],
                'next_time' => $info['next_time'],
                'storage_info'  => $info['storage_info'],
                'speed_limit'  => $info['speed_limit'], // 限速策略
                'time_strategy'  => $info['time_strategy'], // 时间策略
                'time_strategy_backup_type' => Backup::instance()->getTimeStrategyBackupType($d['strategy_id']),
                'reserve_strategy'  => $info['reserve_strategy'], // 保留策略
                'transport_strategy'  => $info['transport_strategy'], // 传输策略
                'module_type' => $info['module_type'],
                'module_type_des' => $info['module_type_des'],
                'job_type_des' => $info['job_type_des'],
                'flag' => true,
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($d['task_orchestration_plan_flag']),
                'transport_ip_segment' => $d['transport_ip_segment'],
                'snap_shot_flag' => v1_parse_flag_to_bool($d['snap_shot_flag']),
                'agent_config_info' => $this-> getAgentConfigInfo($d['task_uuid']),
                'network_flag' => $this->getNodeNetworkFlag($taskUUID),//检查显示传输网络标志
                'scan_thread_num' => intval($detail['scan_thread_num']),
                'scan_file_num' => intval($detail['scan_file_num']),
                'file_archive_flag' => $d['file_archive_flag'],
                'skip_file_alarm_flag' => $d['skip_file_alarm_flag'] == 1 ? true : false,
                'skip_file_alarm_min_num' => $d['skip_file_alarm_min_num'],
                'skip_file_alarm_min_ratio' => $d['skip_file_alarm_min_ratio'],
                'permission_operate_flag' => $d['permission_operate_flag'] == 1 ? true : false,
                'same_file_strategy' => $d['same_file_strategy'],
                'link_file_pass_flag' => $d['link_file_pass_flag'] == 1 ? true : false,
                'dir_tree_recovery_flag' => $d['dir_tree_recovery_flag'] == 1 ? true : false,
                'new_root_path' => $d['new_root_path'] == '' ? xphp_get_lang('UI_FILE_RECOVERY_OLD_PATH')  : $d['new_root_path'],
                'des_module_type' => $d['sub_module_type'],
                'src_module_type' => $this->getNasResorceInfo($taskUUID)['source_agent_type'],//agent_type
                'src_sub_module_type' => $this->getNasResorceInfo($taskUUID)['src_type'],//sub_module_type
                'task_orchestration_plan_flag' => v1_parse_flag_to_bool($d['task_orchestration_plan_flag']),
                'tape_strategy' => $tapeHandler->getTapeGroupStrategy(array("group_uuid" => $d['storage_uuid'])) ?? '',
                'storage_type' => $this->getStorageTypeByTaskId($taskUUID, $d['task_type']),
                //安全策略
                'safe_strategy' => $info['safe_strategy'],
                'retry_strategy' => $info['retry_strategy'],
                'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($info['ignore_resource_limiting_flag']),
                'current_stage' => $info['current_stage'], // 任务阶段
                'stage_percent' => $info['stage_percent'], // 任务百分比
                'current_stage_value' => $info['current_stage_value'], // 任务阶段+任务百分比
                'task_user_uuid' => $d['user_uuid'],
                //限速策略
                'speed_info' => $this->getSpeedStrategy($taskUUID),
                //------------给备份任务组装的数据-------------------
                 //保留策略
                 'brs' => array(
                    'type' => $d['strategy_type'],
                    'number' => $d['number'],
                    'strategy_mode' => $d['strategy_mode'],
                    'gfs_strategy_item_list' =>  Backup::instance()->getGfsStrategyInfo($taskUUID),
                ),
                //存储策略
                'bss' => array(
                    'compress' => v1_parse_flag_to_bool($d['compressed_flag']),
                    'encrypt' => v1_parse_flag_to_bool($d['encrypted_flag']),
                    'password_auto_flag' => v1_parse_flag_to_bool($d['password_auto_flag']),
                    'password' => base64_encode(v1_pt_pass_decrypt($d['password'])),
                    'compress_method' => $d['compress_method'],
                    'encrypt_method' => $d['encrypt_method'],
                ),
                //传输策略
                'bts' => array(
                    'encrypt' => v1_parse_flag_to_bool($d['encrypt_flag']),
                    'compress' => v1_parse_flag_to_bool($d['compress_flag']),
                    'network' => $d['network_uuid'],
                    'reconnect_times' => intval($d['reconnect_times']),
                    'reconnect_interval' => intval($d['reconnect_interval']),
                    'encrypt_method' => intval($d['transport_method']),
                    'network_pool_uuid' => $d['network_pool_uuid'],
                    '' => "",
                    'appliance_uuid' => $d['proxy_uuid'],
                    'appliance_pool_uuid' => $this->getJobAgentPoolInfo($taskUUID),
                ),
                //时间策略
                'time_strategy_edit' => Backup::instance()->getTimeStrategyInfo($d['strategy_id']),
                'wildcard_mode' => intval(json_decode($d['bal_detail'],true)["wildcard_mode"]),
            );
        }
        return $basicInfo;
    }

    /**
     * 任务详情-根据任务uuid获取存储类型--只用于恢复任务
     * @param string $taskUUID 任务uuid
     * @param string $taskType 任务类型
     * @return string 存储类型
     */
    private function getStorageTypeByTaskId($taskUUID,$taskType){
        $storageType = '';
        if ($taskType == xphp_get_config('task', 'TASKTYPE')['BACKUP']) {
            return $storageType;
        }
        $sql = "select bsr.storage_type from bd_storage_resource bsr, bd_backup_timepoint bbt, fs_path_list fpl where 
        fpl.task_uuid = ? and fpl.recovery_timepoint_uuid = bbt.timepoint_uuid and bbt.storage_uuid = bsr.storage_uuid";
        $data = $this->dbSelect($sql, array($taskUUID));
        if (!empty($data)) {
            $storageType = $data[0]['storage_type'];
        }
        return $storageType;
    }

    /**
     * 任务详情-通配符
     * @param unknown $params
     */
    private function getAgentConfigInfo($task_uuid) {
        $sql = "select agent_uuid, detail from bd_task_agent_list where task_uuid = ?";
        $data = $this->dbSelect($sql, array($task_uuid));
        $info = array();
        foreach($data as $d) {
            $detail = json_decode($d['detail'], true);
            //增加object_name字段
            $detail['object_name'] = $this->getObjectDes($detail['object_uuid'], $detail['sub_module_type']);
            $info['info'][] = array(
                'agent_uuid' => $d['agent_uuid'],
                'detail' => $detail,
            );
        }
        //是否配置了通配符
        $count = 0;
        $flag = false;
        foreach($data as $d) {
            $count += intval(json_decode($d['detail'],true)["wildcard_mode"]);
        }
        if( $count > 0) {//大于0则配置了通配符
            $flag = true;
        }
        $info['mode'] = $flag;
        return $info;
    }

    /**
     * 根据uuid和子模块类型获取描述
     * @param string $uuid 对象uuid
     * @param string $type 子模块类型
     * @return string 描述
     */
    private function getObjectDes($uuid, $type) {
        switch (intval($type)) {
            case xphp_get_config('module', 'SUBMODULE_TYPE')['FS']:
                $sql = "select agent_uuid, agent_name as nick_name, hostname as name, ip from bd_agent where agent_uuid = ?";
                $data = $this->dbSelect($sql, array($uuid));
                if (!empty($data)) {
                    return ($data[0]['nick_name'] == $data[0]['ip']) ? $data[0]['name'] . '(' . $data[0]['ip'] . ')' : $data[0]['nick_name'] . '(' . $data[0]['ip'] . ')';
                }
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['NAS']:
                $sql = "select nas_nickname as nick_name, share_path as name, ip from nas_storage_resource where nas_uuid = ?";
                $data = $this->dbSelect($sql, array($uuid));
                if (!empty($data)) {
                    return $data[0]['ip'] . '(' . ($data[0]['nick_name'] == $data[0]['ip'] ? $data[0]['name'] : $data[0]['nick_name']) .  ')';
                }
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']:
                $sql = 'SELECT obs.obs_nickname, obs.access_key_id, obs.endpoint_override FROM obs_resource obs WHERE obs.id IS NOT NULL and obs.obs_uuid = ?';
                if (!empty($data)) {
                    return $data[0]['obs_nickname'] . '(' . $data[0]['access_key_id'] . '@' . $data[0]['endpoint_override'] . ')';
                }
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']:
                $sql  = "select hc.hadoop_cluster_name from hadoop_cluster as hc where hc.hadoop_cluster_uuid = ?";
                $data = $this->dbSelect($sql, array($uuid));
                //查找集群下的节点
                $noderesult =  HadoopBackUp::instance() -> getClusterNode($uuid);
                $nodelist =  array();
                $nodestr = '';
                foreach($noderesult as $node){
                    $nodelist[] =  $node['namenode_ip'];
                }
                $nodestr = implode(" ",$nodelist);
                if (!empty($data)) {
                    return $data[0]['hadoop_cluster_name'] . "(". $nodestr .")";
                }
                break;
            default:
                break;
        }
       return '--';
    }

    /**
     * 获取nas源设备的信息
     * @param unknown $params
     */
    private function getNasResorceInfo($taskuuid) {
        //先找到恢复时间点
        $sql = " select fbt.detail,fbt.source_agent_type, bbt.sub_module_type from fs_path_list fpl,fs_backup_timepoint fbt,bd_backup_timepoint bbt where
        fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid and bbt.timepoint_uuid = fbt.fs_timepoint_uuid and fpl.task_uuid = ?";
    	$data = $this->dbSelect($sql, array($taskuuid));
        $detail = json_decode($data[0]['detail'], true);
        $sql = " select ip, share_path,nas_nickname from nas_storage_resource where nas_uuid = ?";
    	$resdata = $this->dbSelect($sql, array($detail["nas_uuid"]));
        if ($resdata[0]['nas_nickname'] != $resdata[0]['ip']) {
            $nas_name = $resdata[0]['ip'] . "(" . $resdata[0]['nas_nickname'] . ")";
        } else {
            $nas_name = $resdata[0]['ip'] . "(" . $resdata[0]['share_path'] . ")";
        }
        $info = array(
            "nas_name" => $nas_name,
            "source_agent_type" => $data[0]['source_agent_type'],
            "src_type" => $data[0]['sub_module_type'],
        );
        return $info;
    }

    /**
     * 获取是否显示传输网络标记
     * @param string $taskuuid
     * @return boolean
     */
    public function getNodeNetworkFlag($taskuuid){
        $sql = "select ba.net_model, bts.network_uuid from bd_transport_strategy bts, bd_agent ba, bd_task_agent_list btal where ba.agent_uuid = btal.agent_uuid and btal.task_uuid = bts.task_uuid and btal.task_uuid = ?";
        $data = $this->dbSelect($sql, array($taskuuid));
        $flag = false;  //定义传输网络标志
        foreach ($data as $d){
            if(intval($d['net_model']) == 2 && !empty($d['network_uuid'])){
                $flag = true;
            }
        }
        return $flag;
    }


    /**
     * 下载跳过文件
     * @param array $params 参数
     * @return string
     */
    public function downLoadPassFile(array $params)
    {
        $nodeUuid =  $params['node_uuid'];
        $historyUuid = $params['history_uuid'];
        $agentUuid = $params['agent_uuid'];
        $fileName = 'passfilelist';
        //获取文件的大小,如果获取失败,表示这个文件不存在或节点不可用,不能下载
        $opName = 'FS_PRIVATE_OPERATION_CODE_ABNORMAL_FILE_LIST_GET';
        $blockSize = xphp_get_config('task', 'GRAIN_FILE_BLOCK_SIZE');
        $msg = array(
            "history_uuid" => $historyUuid,
            "read_offset" => 0,
            "read_size" => $blockSize,
            "agent_uuid" => $agentUuid,
        );
        $mbResult = $this->mbFSMsg($nodeUuid, $opName, json_encode($msg), true);
        if($mbResult['result']) {
            Header("Content-type: application/octet-stream");
            Header("Accept-Ranges: bytes");
            Header("Content-Disposition: attachment; filename=". $fileName . ".txt");
            //如果大于分块大小,分块下载
            $filesize = $mbResult['msg']['file_size'];
            for($i=0; $i<$filesize; $i = $i + $blockSize){
                if($filesize - $i <= $blockSize){
                    $readLen = $filesize - $i;
                }else{
                    $readLen = $blockSize;
                }
                $msg = array(
                    "history_uuid" => $historyUuid,
                    "read_offset" => $i,
                    "read_size" => $readLen,
                    "agent_uuid" => $agentUuid,
                );
                $mbResult = $this->mbFSMsg($nodeUuid, $opName, json_encode($msg), true);
                echo  $mbResult['msg']['data'];
                ob_flush(); //将数据从php的buffer中释放出来
                flush(); //将释放出来的数据发送给浏览器
            }
        } else {
            //获取文件大小失败
            return $this->muOpResult(false, xphp_get_lang('UI_FILE_DOWNLOAD_SKIP_FILE'), xphp_get_lang('UI_FILE_DOWNLOAD_SKIP_FILE') . xphp_get_lang('WEB_ERROR_BD_GENERIC_ERROR'), "warning");
        }
    }

     /**
     * 获取对象列表-客户端列表、nas设备、hadoop集群、对象存储
     * @param unknown $params
     * @return object
     */
    public function getObjectList(array $params)
    {
        $taskUUID = $params['jobs_uuid'];
        $sub_module_type = $params['sub_module_type'];
        switch (intval($sub_module_type)) {
            case xphp_get_config('module', 'SUBMODULE_TYPE')['FS']: 
                $search = !empty($params['search']) ? v1_escape_wildcard($params['search']) : '';
                $info = $this->getAgentList($taskUUID, $search);
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['NAS']: 
                $info = $this->getNasList($taskUUID);
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['HADOOP']: 
                $info = $this->groupHadoopData(HadoopJobInfo::instance()->getHadoopDetail($params));
                break;
            case xphp_get_config('module', 'SUBMODULE_TYPE')['OBS']: 
                $info = $this->groupObsData(ObsJobInfo::instance()->getTaskDetail($params));
                break;
        }
        $info['total'] = count($info["rows"]);
        return $info;
    }
    
    /**
     * 客户端列表
     * @param string $taskUUID 任务uuid
     * @param string $search 搜索关键字
     * @return array $info 客户端列表信息
     */
    private function getAgentList($taskUUID, $search) {
        $sql = "SELECT DISTINCT
                    ba.ip,
                    ba.hostname,
                    ba.detail,
                    ba.agent_uuid,
                    ba.agent_name AS nickname,
                    bt.task_name,
                    bt.task_type,
                    bt.task_status,
                    bt.module_type,
                    bt.sub_module_type,
                    fpl.recovery_timepoint_uuid AS sour_timepointuuid,
                    fpl.backup_mode,
                    fpl.new_root_path,
                    fbt.agent_name,
                    fbt.agent_ip,
                    fbt.detail AS fbtdetail,
                    fbt.agent_uuid AS uuid,
                    ft.file_archive_flag,
                    btal.task_status AS agent_status,
                    btal.task_uuid,
                    btal.detail 
                FROM
                    bd_task bt
                    LEFT JOIN fs_task ft ON bt.task_uuid = ft.task_uuid
                    LEFT JOIN fs_path_list fpl ON bt.task_uuid = fpl.task_uuid
                    LEFT JOIN bd_task_agent_list btal ON fpl.task_uuid = btal.task_uuid
                    LEFT JOIN bd_agent ba ON btal.agent_uuid = ba.agent_uuid
                    LEFT JOIN fs_backup_timepoint fbt ON fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid 
                WHERE
                    bt.task_uuid = ? 
                GROUP BY
                    ba.agent_uuid 
                ORDER BY
                    btal.id";
        $data = $this->dbSelect($sql, array($taskUUID));

        $info = array(
            'rows' => array(),
            'total' => 0,
        );
        $taskType = xphp_get_desc('Pf', 'TASKTYPEDES');
        $backupModeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $agentTaskStatus = xphp_get_desc('Pf', 'AGENT_TASK_STATUS');
        // 客户端数量
        $sqlCount = "select count(agent_uuid) as total from bd_task_agent_list where task_uuid = ? ";
    	$agentcount = $this->dbSelect($sqlCount, array($taskUUID));
        $src_module_type = $this->getNasResorceInfo($taskUUID)['src_type'];
    	foreach ($data as $d){
            //文件列表
            $list = array();
            if($d['task_type'] == 2) {//恢复
                // 路径
                $sql_path = "select path_name from fs_path_list where task_uuid = ?;";
                $sqlParams_path = array($d['task_uuid']);
                // 文件个数总个数等信息
                $sql_count = "select total_fs_count,current_fs_count,current_dir_count from fs_running_info fri where fri.task_uuid = ?;";
                $sourhost = $this->getAgentNameStr($d['uuid'],$d['agent_name'],$d['agent_ip'],$src_module_type);
                // 若“别名”等于“IP”，则显示“主机名/IP”
                if($d['ip'] ==$d['nickname']) {
                    $desagent = $d['hostname'].'('.$d['ip'].')';
                }else {
                    $desagent = $d['nickname'].'('.$d['ip'].')';
                }
            }else {
                $sql_count = "select total_fs_count,current_fs_count,current_dir_count from fs_running_info fri where fri.agent_uuid = ? and fri.task_uuid = ?;";
                $sql_path = "select path_name from fs_path_list where agent_uuid = ? and task_uuid = ?;";
                $sqlParams_path = array($d['agent_uuid'],$d['task_uuid']);
                // 若“别名”等于“IP”，则显示“主机名/IP”
                if($d['ip'] ==$d['nickname']) {
                    $sourhost = $d['hostname'] . '('.$d['ip'].')';
                }else {
                    $sourhost = $d['nickname'] . '('.$d['ip'].')';
                }
            }
            //如果关键词不是空 并且 搜索内容不在源端主机名中，则跳过
            if (!empty($keyword) && strpos($sourhost, $keyword) === false) {
                continue;
            }
            $data_path = $this->dbSelect($sql_path, $sqlParams_path);
            foreach($data_path as $each_path) {
                array_push($list,$each_path['path_name']);
            }
            //文件数量
            $data_count = $this->dbSelect($sql_count, $sqlParams_path);
            //通配符
            if($d['task_status'] != 2 || $d['agent_status'] == 1) {//任务不是运行中,客户端是等待状态都应显示--
                $ttype = xphp_get_config('app', 'NULLSPACE');
                $total_fs_count = xphp_get_config('app', 'NULLSPACE');
                $current_fs_count = xphp_get_config('app', 'NULLSPACE');
                $current_dir_count = xphp_get_config('app', 'NULLSPACE');
                $agent_status =xphp_get_config('app', 'NULLSPACE');
                $progress = xphp_get_config('app', 'NULLSPACE');

            }else {
                if($d['task_type'] == 2) {//恢复
                    $ttype = $taskType[$d['task_type']];
                }else {
                    $ttype =  $backupModeDes[$d['backup_mode']]. xphp_get_lang('UI_PLATFORM_BACKUP');
                }
                $total_fs_count = $data_count[0]['total_fs_count'];
                $current_fs_count = $data_count[0]['current_fs_count'];
                $current_dir_count = $data_count[0]['current_dir_count'];
                $agent_status = $agentTaskStatus[$d['agent_status']];
                $progress = $this->getFsAgentProgress($taskUUID,$d['agent_uuid'],intval($d['task_status']),intval($d['agent_status']));
            }
            if (!empty($search) && strpos($sourhost, $search) === false) {
                continue;
            }
            $info["rows"][] = array(
                'agent_uuid' => $d['agent_uuid'],
				'source_name' => $sourhost,
                'job_type' => $d['task_type'],
                'job_type_des' => $ttype,
                'total_fs_count' => $total_fs_count,
                'current_fs_count' => $current_fs_count,
                'current_dir_count' => $current_dir_count,
                'progress' => $progress,
                'agent_status' => $d['agent_status'],
                'agent_status_des' => $agent_status,
                'file_list' => $list,
                'job_status' => $d['task_status'],
                'des_agent' => $desagent,
                'wildcard_info' => json_decode($d['detail'], true),
                'path' => $d['new_root_path'] == "" ? xphp_get_lang('UI_FILE_RECOVERY_OLD_PATH') : $d['new_root_path'],
                'file_archive_flag' => $d['file_archive_flag'],
                'des_module_type' => $d['sub_module_type'],
                'src_module_type' => $src_module_type,
                'cross_platform_flag' => $d['sub_module_type'] == $src_module_type ? false : true,//跨平台标记
                'cross_platform_des' => $this->getCrossDes($src_module_type, $d['sub_module_type'],$d['task_type']),//跨平台描述
            );
        }
        return $info;
    }

    /**
     * nas设备列表
     * @param string $taskUUID 任务uuid
     * @return array $info nas设备列表信息
     */
    private function getNasList($taskUUID) {
        $sql = "SELECT DISTINCT
                    ba.ip,
                    ba.hostname,
                    ba.detail,
                    btal.agent_uuid,
                    bt.task_name,
                    bt.task_type,
                    bt.task_status,
                    bt.module_type,
                    bt.sub_module_type,
                    fpl.recovery_timepoint_uuid AS sour_timepointuuid,
                    fpl.backup_mode,
                    fpl.new_root_path,
                    fbt.agent_name,
                    fbt.agent_ip,
                    fbt.detail AS fbtdetail,
                    fbt.agent_uuid AS uuid,
                    nt.file_archive_flag,
                    btal.task_status AS agent_status,
                    btal.task_uuid,
                    btal.detail,
                    nt.nas_uuid 
                FROM
                    bd_task bt
                    LEFT JOIN nas_task nt ON bt.task_uuid = nt.task_uuid
                    LEFT JOIN fs_path_list fpl ON nt.task_uuid = fpl.task_uuid
                    LEFT JOIN bd_task_agent_list btal ON fpl.task_uuid = btal.task_uuid
                    LEFT JOIN bd_agent ba ON btal.agent_uuid = ba.agent_uuid
                    LEFT JOIN fs_backup_timepoint fbt ON fpl.recovery_timepoint_uuid = fbt.fs_timepoint_uuid 
                WHERE
                    nt.task_uuid = ? 
                GROUP BY
                    ba.agent_uuid 
                ORDER BY
                    ba.id";
        $data = $this->dbSelect($sql, array($taskUUID));
        $info = array(
            'rows' => array(),
            'total' => 0,
        );
        $taskType = xphp_get_desc('Pf', 'TASKTYPEDES');
        $backupModeDes = xphp_get_desc('Pf', 'BACKUP_MODE_DES');
        $agentTaskStatus = xphp_get_desc('Pf', 'AGENT_TASK_STATUS');
        // nas设备数量
        $sqlCount = "select count(nas_uuid) as total from nas_task where task_uuid = ? ";
    	$agentcount = $this->dbSelect($sqlCount, array($taskUUID));
        //恢复目标nas设备  备份源nas设备
        $nasdevice = $this->dbSelect('select nsr.ip, nsr.nas_nickname, nsr.share_path from nas_storage_resource nsr, nas_task nt where nt.nas_uuid = nsr.nas_uuid and nt.task_uuid = ?;', array($taskUUID));
        if($nasdevice[0]['nas_nickname'] == $nasdevice[0]['ip']) {
            $des_nas = $nasdevice[0]['ip'].'('.$nasdevice[0]['share_path'].')';
        }else {
            $des_nas = $nasdevice[0]['ip'].'('.$nasdevice[0]['nas_nickname'].')';
        }
        $src_module_type = $this->getNasResorceInfo($taskUUID)['src_type'];
        //文件列表
        $list = array();
        // 路径
        $sql_path = "select path_name from fs_path_list where task_uuid = ?;";
         // 文件个数总个数等信息
         $sql_count = "select total_fs_count,current_fs_count,current_dir_count from fs_running_info fri where fri.task_uuid = ?;";
        $sqlParams_path = array($taskUUID);
        $data_path = $this->dbSelect($sql_path, $sqlParams_path);
        $list = array_column($data_path,'path_name');
        //文件数量
        $data_count = $this->dbSelect($sql_count, $sqlParams_path);
        foreach ($data as $d){
            if ($d['task_type'] == 2) {//恢复
                $sourhost = $this->getAgentNameStr($d['agent_uuid'], $d['agent_name'], $d['agent_ip'],$src_module_type);
            } else {
                $sourhost = $des_nas;
            }
            if ($d['task_status']!=2 || $d['agent_status'] == 1) {//任务不是运行中,客户端不是等待状态都应显示--
                $ttype = xphp_get_config('app', 'NULLSPACE');
                $total_fs_count = xphp_get_config('app', 'NULLSPACE');
                $current_fs_count = xphp_get_config('app', 'NULLSPACE');
                $current_dir_count = xphp_get_config('app', 'NULLSPACE');
                $agent_status =xphp_get_config('app', 'NULLSPACE');

            }else {
                if($d['task_type'] == 2) {//恢复
                    $ttype = $taskType[$d['task_type']];
                }else {
                    $ttype =  $backupModeDes[$d['backup_mode']]. xphp_get_lang('UI_PLATFORM_BACKUP');
                }
                $total_fs_count = $data_count[0]['total_fs_count'];
                $current_fs_count = $data_count[0]['current_fs_count'];
                $current_dir_count = $data_count[0]['current_dir_count'];
                $agent_status = $agentTaskStatus[$d['agent_status']];
            }
            $info["rows"][] = array(
                'nas_uuid' => $d['nas_uuid'],
                'source_name' => $sourhost,
                'job_type' => $d['task_type'],
                'job_type_des' => $ttype,
                'job_status' => $d['task_status'],
                'total_fs_count' => $total_fs_count,
                'current_fs_count' => $current_fs_count,
                'current_dir_count' => $current_dir_count,
                'agent_status' => $d['agent_status'],
                'agent_status_des' => $agent_status,
                'file_list' => $list,
                'wildcard_info' => json_decode($d['detail'], true),
                'path' => $d['new_root_path'] == "" ? xphp_get_lang('UI_FILE_RECOVERY_OLD_PATH') : $d['new_root_path'],
                'share_path' => $nasdevice[0]['share_path'],
                'file_archive_flag' => $d['file_archive_flag'],
                'des_module_type' => $d['sub_module_type'],
                'src_module_type' => $src_module_type,
                'cross_platform_flag' => $d['sub_module_type'] == $src_module_type ? false : true,//跨平台标记
                'cross_platform_des' => $this->getCrossDes($src_module_type, $d['sub_module_type'],$d['task_type']),//跨平台描述
                'des_nas' => $des_nas,
            );
        }
        return $info;
    }

    /**
     * 组装Hadoop数据让数据格式与其他模块一致
     * @return object
     */
    public function groupHadoopData($data)
    { 
        $info = array();
        $info['rows'] = $data['rows'];
        foreach ($info['rows'] as $key => $each_path) {
            $info['rows'][$key]['source_name'] = $each_path['host_name']; 
            $info['rows'][$key]['job_type_des'] = $each_path['ttype']; 
            $info['rows'][$key]['job_status'] = $each_path['taskStatus']; 
            $info['rows'][$key]['total_fs_count'] = $each_path['total_num']; 
            $info['rows'][$key]['current_fs_count'] = $each_path['completed_num']; 
            $info['rows'][$key]['current_dir_count'] = $each_path['src_data_num']; 
            $info['rows'][$key]['agent_status_des'] = $each_path['status']; 
            $info['rows'][$key]['wildcard_info'] = $each_path['wildcardinfo']; 
            $info['rows'][$key]['wildcard_info']['wildcard_mode'] = $each_path['wildcardinfo']['wildcardmode'];
            $info['rows'][$key]['file_list'] = $each_path['list']; 
            $info['rows'][$key]['src_module_type'] = $each_path['source_submodule_type']; 
        }
        return $info;
    }

     /**
     * 组装对象存储数据让数据格式与其他模块一致
     * @return object
     */
    public function groupObsData($data)
    { 
        $info = array();
        $info['rows'] = $data['rows'];
        foreach ($info['rows'] as $key => $each_path) {
            $info['rows'][$key]['source_name'] = $each_path['source_client']; 
            $info['rows'][$key]['job_type_des'] = $each_path['task_type']; 
            $info['rows'][$key]['job_status'] = $each_path['taskStatus']; 
            $info['rows'][$key]['agent_status_des'] = $each_path['agent_status']; 
            $info['rows'][$key]['wildcard_info'] = $each_path['wildcardInfo']; 
            $info['rows'][$key]['file_list'] = $each_path['path_name']; 
            $info['rows'][$key]['src_module_type'] = $each_path['source_submodule_type']; 
            $info['rows'][$key]['des_agent'] = $each_path['target_client']; 
            $info['rows'][$key]['path'] = $each_path['new_root_path']; 
        }
        return $info;
    }

    /**
     * 获取跨平台恢复描述
     * @param unknown $params
     */
    private function getCrossDes($src_module_type,$des_module_type,$task_type) {
        $des = xphp_get_config('app', 'NULLSPACE');
        if ($task_type == xphp_get_config('task', 'TASKTYPE')['RECOVERY']) {
            $des = $this->getFsSubDes($src_module_type) . xphp_get_lang('UI_NAS_RECOVER_DEVICE') . $this->getFsSubDes($des_module_type);
        }
        return $des;
    }

    private function getFsSubDes($sub_type) {
        $des = xphp_get_config('app', 'NULLSPACE');
        switch (intval($sub_type)) {
            case 1:   //fs
                $des = xphp_get_lang('UI_FILE_RECOVERY_AGENT');
                break;
            case 2:   //nas
                $des = xphp_get_lang('UI_NAS_DEVICE_NAME');
                break;
            case 3:   //hadoop
                $des = xphp_get_lang('WEB_HADOOP_CLUSTER');
                break;
            case 4:   //obs
                $des = xphp_get_lang('UI_PLATFORM_OBS');
                break;
        }
        return $des;
    }

    /**
     * 根据任务状态计算进度
     * @param string $taskUUID    任务uuid
     * @param string $agentUUID   客户端uuid
     * @param int    $taskStatus  任务状态
     * @param int    $agentStatus 客户端状态
     * @return string
     */
    protected function getFsAgentProgress(string $taskUUID, string $agentUUID, $taskStatus, $agentStatus)
    {
        $sql = "select current_object_total_size, current_object_completed_size
                from fs_running_info where task_uuid = ? and agent_uuid = ?";
        $data = $this->dbSelect($sql, array($taskUUID,$agentUUID));
        $taskStatusArr = xphp_get_config('task', 'TASKSTATUS');
        if (!in_array($taskStatus, [$taskStatusArr['RUNNING'], $taskStatusArr['PAUSED'], $taskStatusArr['ABNORMAL']])) {
            //如果任务没有运行
            return xphp_get_config('app', 'NULLSPACE');
        }
        //备份任务只有目录时，文件大小为0  导致进度计算出来一直是0
        if ($agentStatus == 4) {
            return '100.00%';
        }
        $speed = v1_calpercent($data[0]['current_object_total_size'], $data[0]['current_object_completed_size']);
        //解决round后出现一位小数的问题,比如32.1%,自动优化成32.10%,这样的话界面上不会出现进度回退的现象
        return sprintf('%.2f', substr($speed, 0, -1)) . '%';
    }

     /**
     * 1、代理没删，如果有别名就显示别名,没有别名就显示主机名,如果别名和IP一样则显示主机名
     * 2、代理已删除，显示时间点中的主机名+ip
     * @param string $agentuuid 时间点中的uuid
     * @param string $fbtname   时间点中的主机名
     * @param string $ip        时间点中的ip
     * @return string name(ip)
     */
    protected function getAgentNameStr(string $agentuuid, $fbtname, $ip)
    {
        $sql = "select agent_uuid, agent_name, hostname, ip from bd_agent where agent_uuid = ?";
        $result = $this->dbSelect($sql, array($agentuuid));
        if (!empty($result)) {
            return ($result[0]['agent_name'] == $ip ? $result[0]['hostname'] : $result[0]['agent_name']) .
                '(' . $ip . ')';
        } else {
            return $fbtname . '(' . $ip . ')';
        }
    }

    /**
     * 1、nas设备没删，如果有别名就显示别名,没有别名就显示share_path,如果别名和IP一样则显示share_path
     * 2、nas设备已删除，显示时间点中的主机名+ip
     * @param string $nasuuid $nasuuid
     * @param string $fbtname 时间点中的主机名
     * @param string $ip      时间点中的主机名
     * @return name(ip)
     */
    protected function getNasNameStr(string $nasuuid, $fbtname, $ip)
    {
        $sql = "select nas_nickname, share_path from nas_storage_resource where nas_uuid = ?";
        $result = $this->dbSelect($sql, array($nasuuid));
        if (!empty($result)) {
            return $ip . '(' .
                ($result[0]['nas_nickname'] == $ip ? $result[0]['share_path'] : $result[0]['nas_nickname']) .  ')';
        } else {
            return $ip . '(' . $fbtname . ')';
        }
    }
    
}
