<?php

namespace app\v1\dbcdp\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\JobInfo;
use app\v1\opcode\PfOpcode;
use app\v1\opcode\PfOpcodePrivate;

/**
 * note          数据库实时 之任务信息 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbcdpJobInfo extends Base
{
    /**
     * 任务各个阶段的进度信息
     * @param string $taskUuid 任务uuid
     * @return array
     */
    public function getJobsProgress(string $taskUuid)
    {
        $sql = 'select cddt.task_uuid, cddt.current_task_running_stage, cddtpi.total_dict_num, 
        cddtpi.completed_dict_num,cddtpi.current_dict, cddtpi.current_dict_type, cddtpi.total_table_num, 
        cddtpi.completed_table_num, cddtpi.current_table, cddtpi.current_table_completed_size, 
        cddtpi.transmission_speed,bt.task_status,cddtpi.total_table_num,cddtpi.completed_table_num,cddtpi.replay_error_transaction_num
        from cdp_db_dr_task cddt 
        inner join  cdp_db_dr_task_progress_info cddtpi 
        on cddt.task_uuid = cddtpi.task_uuid
        inner join bd_task bt 
        on cddt.task_uuid = bt.task_uuid
        where cddt.task_uuid = ? ';
        $sqlParams = array($taskUuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $records = array(
            'completed' => 0,
            'total' => 0,
            'execution_object_type' => $data[0]['current_task_running_stage'],
            'task_status' => $data[0]['task_status'],
            'completedVal' => xphp_get_config('app', 'NULLSPACE'),
            'totalVal' => xphp_get_config('app', 'NULLSPACE'),
            'completed_table_num' => 0,
            'total_table_num' => 0,
            'replay_error_transaction_num' => 0
        );
        if (empty($data)){
            return $records;
        }
        $currenttaskStage = $data[0]['current_task_running_stage'];
        //当前任务运行阶段 字典导出10 导入11 回切导出40 回切同步50 回切约束导入54
        if (
            $currenttaskStage == xphp_get_config('dbcdp', 'DBCDP_CURRENT_DICT_TYPE')['DB_CDP_TASK_IN_DICT_EXPORT']
            || $currenttaskStage == xphp_get_config('dbcdp', 'DBCDP_CURRENT_DICT_TYPE')['DB_CDP_TASK_IN_DICT_IMPORT']
            || $currenttaskStage == xphp_get_config('dbcdp', 'DBCDP_CURRENT_DICT_TYPE')['DB_CDP_TASK_IN_FAILBACK_DICT_EXPORT']
            || $currenttaskStage == xphp_get_config('dbcdp', 'DBCDP_CURRENT_DICT_TYPE')['DB_CDP_TASK_IN_FAILBACK_FULL_SYNC']
            || $currenttaskStage == xphp_get_config('dbcdp', 'DBCDP_CURRENT_DICT_TYPE')['DB_CDP_TASK_IN_CONSTRAINT_IMPORT']
            || $currenttaskStage == xphp_get_config('dbcdp', 'DBCDP_CURRENT_DICT_TYPE')['DB_CDP_TASK_IN_FAILBACK_CONSTRAINT_IMPORT']
        ) {
            $records = array(
                'completed' => $data[0]['completed_dict_num'],
                'total' => $data[0]['total_dict_num'],
                'execution_object_type' => $data[0]['current_task_running_stage'],
                'execution_object' => empty($data[0]['current_dict']) ? '--' : $data[0]['current_dict'],
                'transmission_speed' => $data[0]['transmission_speed'],
                'task_status' => $data[0]['task_status'],
                'completedVal' => $data[0]['completed_dict_num'],
                'totalVal' => $data[0]['total_dict_num']
            );
        } elseif (
            // 全量数据同步 20
            $currenttaskStage == xphp_get_config('dbcdp', 'DBCDP_CURRENT_DICT_TYPE')['DB_CDP_TASK_IN_FULL_SYNC']
            // 回切全量数据同步 50
            || $currenttaskStage == xphp_get_config('dbcdp', 'DBCDP_CURRENT_DICT_TYPE')['DB_CDP_TASK_IN_ FAILBACK_FULL_SYNC']
        ) {
            $records = array(
                'completed' => $data[0]['completed_dict_num'],
                'total' => $data[0]['total_dict_num'],
                'execution_object_type' => $data[0]['current_task_running_stage'],
                'execution_object' => empty($data[0]['current_table']) ? '--' : $data[0]['current_table'],
                'transmission_speed' => $data[0]['transmission_speed'],
                'task_status' => $data[0]['task_status'],
                'completedVal' => $data[0]['completed_dict_num'],
                'totalVal' => $data[0]['total_dict_num']
            );
        }

        $records['completed_table_num']= $data[0]['completed_table_num'];
        $records['total_table_num']= $data[0]['total_table_num'];
        $records['replay_error_transaction_num']= $data[0]['replay_error_transaction_num'];
        return $records;
    }

    /**
     * 任务监控数据
     * @param array $params 任务各个阶段的进度信息对应参数
     * @return array
     */
    public function getJobsMonitorData(array $params = [])
    {
        $taskUuid = $params['jobs_uuid'];

        $sql = "SELECT 
                    cddt.source_agent_uuid,
                    cddt.source_cluster_flag,
                    cddt.target_cluster_flag,
                    cddt.source_app_uuid,
                    cddt.source_cluster_ctrl_uuid,
                    cddt.target_app_uuid,
                    cddt.app_tablespace_dir,
                    cddt.target_agent_uuid,
                    cddt.target_cluster_ctrl_uuid,
                    ba_source.agent_name AS source_agent_name,
                    ba_source.hostname AS source_hostname,
                    ba_source.ip AS source_agent_ip,
                    ba_target.agent_name AS target_agent_name,
                    ba_target.hostname AS target_hostname,
                    ba_target.ip AS target_agent_ip,
                    cddtpi.completed_size,
                    baa1.app_service_name as source_app_service_name,
                    baa2.app_service_name as target_app_service_name,
                    baa1.app_name as source_app_name,
                    baa2.app_name as target_app_name,
                    ba_source.ip as source_cluster_agent_ip,
                    ba_target.ip as target_cluster_agent_ip,
                    ba1.ip as source_cluster_ip,
                    ba2.ip as target_cluster_ip,
                    baa3.app_name as source_cluster_app_name,
                    baa4.app_name as target_cluster_app_name
                    FROM   
                        cdp_db_dr_task cddt
                    INNER JOIN 
                    bd_agent ba_source ON cddt.source_agent_uuid = ba_source.agent_uuid
                    INNER JOIN 
                    bd_agent_app baa1 ON cddt.source_agent_uuid = baa1.agent_uuid AND cddt.source_app_uuid = baa1.app_uuid
                    INNER JOIN 
                    cdp_db_dr_task_progress_info cddtpi ON cddt.task_uuid = cddtpi.task_uuid
                    LEFT JOIN 
                    bd_agent ba_target ON cddt.target_agent_uuid = ba_target.agent_uuid
                    LEFT JOIN 
                    bd_agent_app baa2 ON cddt.target_agent_uuid = baa2.agent_uuid AND cddt.target_app_uuid = baa2.app_uuid
                    LEFT JOIN 
                    bd_agent ba1 ON ba1.agent_uuid = cddt.source_cluster_ctrl_uuid
                    LEFT JOIN 
                    bd_agent ba2 ON ba2.agent_uuid = cddt.target_cluster_ctrl_uuid
                    LEFT JOIN
                    bd_agent_app baa3 ON baa3.agent_uuid = cddt.source_cluster_ctrl_uuid
                    LEFT JOIN
                    bd_agent_app baa4 ON baa4.agent_uuid = cddt.target_cluster_ctrl_uuid
                    WHERE 
                    cddt.task_uuid = ?";


        $sqlParams = array($taskUuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $records = array();
        $rows = [];
        foreach ($data as $d) {
            $sourceAppUuid = $d['source_app_uuid'];
            $sourceAgentUuid = $d['source_agent_uuid'];
            $targetAgentUuid = $d['target_agent_uuid'];
            $targetAppUuid = $d['target_app_uuid'];
            $sourceClusterFlag = $d['source_cluster_flag'];
            $targetClusterFlag = $d['target_cluster_flag'];

            $hostInfo = '';
            $standByInfo = '';
            $sourceAppInfo = $this->getAppInfo($sourceAppUuid);
            $targetAppInfo = $this->getAppInfo($targetAppUuid);
            $sqlSpeed = 'select bri.speed,bri.speed_time,bt.task_status,bri.total_object_transport_size
                        from bd_running_info bri,bd_task bt where bri.task_uuid = bt.task_uuid and bt.task_uuid = ?';
            $sqlSpeedData = $this->dbSelect($sqlSpeed, array($taskUuid));

            if ($sourceClusterFlag == 1) {
                //集群
                $hostInfo = $d['source_cluster_app_name'] .'('. $d['source_cluster_ip'] .')';// 源数据库实例
                $hostNode = $d['source_cluster_app_name'] . '(' . $d['source_cluster_ip'] . ')';           // 源复制节点
            } else {
                $hostInfo = $d['source_app_name'] . '(' . $d['source_agent_ip'] . ')';
                $hostNode = $d['source_agent_ip'];  //主节点
            }
            if ($targetClusterFlag == 1) {
                //集群
                $standByInfo = $d['target_cluster_app_name'] .'('. $d['target_cluster_ip'] .')';// 源数据库实例
                $standByNode = $d['target_cluster_app_name'] . '(' . $d['target_cluster_ip'] . ')';           // 目标复制节点
            } else {
                $standByInfo = $d['target_app_name'] . '(' . $d['target_agent_ip'] . ')';
                $standByNode = $d['target_agent_ip'];  //备机节点
            }
            $dBTYPE = xphp_get_config('db', 'DB_TYPE_DES');
            $rows[] = array(
                'host_info' => $hostInfo,                                                   //主机名
                'master_node' => $hostNode,                                                 //主节点
                'application_type' => $dBTYPE[$sourceAppInfo['application_type']],          //应用类型
                'monitor_application' => $sourceAppInfo['app_name'],                        //监控应用信息
                'processing_data_size' => v1_calsize($d['completed_size'], true),  //处理数据大小
                'processing_speed' => $sqlSpeedData[0]['task_status']
                != xphp_get_config('task', 'TASKSTATUS')['RUNNING'] ?
                    xphp_get_config('app', 'NULLSPACE') :
                    v1_calspeed(intval($sqlSpeedData[0]['speed'])),                         //处理速度
                'standby_info' => $standByInfo,  //备机信息
                'slave_node' => $standByNode,  //从属节点
                'monitor_application_details' => $sourceAppInfo['detail'],                  //监控应用详情
                'app_tablespace_dir' => $d['app_tablespace_dir'],                           //数据表空间存储目录
                'total_object_transport_size' => v1_calsize($sqlSpeedData[0]['total_object_transport_size'],true)  //传输大小
            );
        }
        $num = 100;
        return [
            'rows' => $rows,
            'total' => $num
        ];
    }

    /**
     * 获取指定任务的回切配置信息
     * @param array $params 对应参数
     * @return array
     */
    public function getFailBackInfo($params)
    {
        $taskUuid = $params['jobs_uuid'];
        $sql = 'select cddttfi.failback_target_app_uuid, cddttfi.failback_target_agent_uuid, cddttfi.failback_mode, 
        cddttfi.transport_encrypt_flag, cddttfi.transport_compress_flag, cddttfi.transport_encrypt_method, cddttfi.transport_compress_method, 
        cddttfi.exp_max_process_num, cddttfi.imp_max_process_num, cddttfi.file_cache_alloc_space, cddttfi.file_cache_storage_path, 
        cddttfi.target_cluster_flag, cddttfi.failback_business_ip_map, cddttfi.service_failback_mode, cddttfi.service_failback_start_time,
        cddttfi.service_failback_end_time, cddttfi.failback_app_tablespace_dir, cddttfi.failback_extend_advanced_config, cddttfi.source_file_cache_alloc_space, 
        cddttfi.source_file_cache_used_space, cddttfi.file_cache_used_space, bts.reconnect_times, bts.reconnect_interval, cddttfi.service_failback_auto_trigger_threshold
        from cdp_db_dr_task_takeover_failback_info cddttfi 
        inner join bd_transport_strategy bts 
        on cddttfi.task_uuid = bts.task_uuid
        where bts.task_uuid = ? ';
        $sqlParams = array($taskUuid);
        $data = $this->dbSelect($sql, $sqlParams);

        $failbackTargetAgentUuid = $data[0]['failback_target_agent_uuid'];
        $agentInfo = $this->getAgentInfo($failbackTargetAgentUuid);

        // 复制任务时的相关配置
        $synSql = "SELECT bts.encrypt_flag, bts.compress_flag, bts.compress_method, bts.encrypt_method,
                   bt.thread_num, cddtci.source_file_cache_alloc_space, cddtci.source_file_cache_used_space, 
                   cddtci.file_cache_used_space,cddtci.file_cache_alloc_space,
                   cddtci.file_cache_storage_path,
                   cddt.exp_max_process_num, cddt.imp_max_process_num
                   FROM bd_task bt
                   JOIN bd_transport_strategy bts ON bt.task_uuid = bts.task_uuid
                   JOIN cdp_db_dr_task cddt ON bt.task_uuid = cddt.task_uuid
                   JOIN cdp_db_dr_task_cache_info cddtci ON cddt.task_uuid = cddtci.task_uuid
                   WHERE bt.task_uuid = ?";
        $synData = $this->dbSelect($synSql, array($taskUuid));

        $records = array(
            'failback_target_app_uuid' => $data[0]['failback_target_app_uuid'],
            'target_cluster_flag' => v1_parse_flag_to_bool($data[0]['target_cluster_flag']),
            'failback_mode' => $data[0]['failback_mode'],
            'transport_encrypt_flag' => v1_parse_flag_to_bool($data[0]['transport_encrypt_flag']),
            'transport_compress_flag' => v1_parse_flag_to_bool($data[0]['transport_compress_flag']),
            'exp_max_process_num' => $data[0]['exp_max_process_num'],
            'imp_max_process_num' => $data[0]['imp_max_process_num'],
            'file_cache_alloc_space' => $data[0]['file_cache_alloc_space'],
            'file_cache_used_space' => v1_calsize($data[0]['file_cache_used_space'],true),
            'file_cache_storage_path' => $data[0]['file_cache_storage_path'],
            'failback_business_ip_map' => json_decode($data[0]['failback_business_ip_map']),
            'service_failback_mode' => $data[0]['service_failback_mode'],
            'service_failback_start_time' => $data[0]['service_failback_start_time'],
            'service_failback_end_time' => $data[0]['service_failback_end_time'],
            'transport_encrypt_method' => $data[0]['transport_encrypt_method'],
            'transport_compress_method' => $data[0]['transport_compress_method'],
            'syn_info' => array(
                'encrypt_flag' => v1_parse_flag_to_bool($synData[0]['encrypt_flag']),
                'compress_flag' => v1_parse_flag_to_bool($synData[0]['compress_flag']),
                'compress_method' => $synData[0]['compress_method'],
                'encrypt_method' => $synData[0]['encrypt_method'],
                'exp_max_process_num' => $synData[0]['exp_max_process_num'],
                'imp_max_process_num' => $synData[0]['imp_max_process_num'],
                'source_file_cache_alloc_space' => $synData[0]['source_file_cache_alloc_space'],
                'file_cache_alloc_space' => $synData[0]['file_cache_alloc_space'],
                'file_cache_storage_path' => $synData[0]['file_cache_storage_path']
            ),
            'failback_app_tablespace_dir' => $data[0]['failback_app_tablespace_dir'],
            'extend_advanced_config' => json_decode($data[0]['failback_extend_advanced_config']),
            'source_file_cache_alloc_space' => $data[0]['source_file_cache_alloc_space'],
            'source_file_cache_used_space' =>  v1_calsize($data[0]['source_file_cache_used_space'],true),
            'reconnect_times' => $data[0]['reconnect_times'],
            'reconnect_interval' => $data[0]['reconnect_interval'],
            'service_failback_auto_trigger_threshold' => $data[0]['service_failback_auto_trigger_threshold']
        );
        $records['agent_info'] = $agentInfo;
        return $records;
    }

    /**
     * 配置或修改同步任务回切配置
     * @param array $params 对应参数
     * @return array
     */
    public function editFailBackConf($params)
    {
        $taskUuid = $params['task_uuid'];
        $apptableSpaceDir = $params['app_tablespace_dir']; // 数据表空间存储目录
        $transportEncryptFlag = $params['transport_strategy']['transport_encrypt_flag'];
        $transportEncryptMethod = $params['transport_strategy']['transport_encrypt_method'];
        $transportCompressFlag = $params['transport_strategy']['transport_compress_flag'];
        $transportCompressMethod = $params['transport_strategy']['transport_compress_method'];
        $transportBlockSize = $params['transport_strategy']['transport_block_size'];
        $exp_threadNum = $params['exp_max_process_num'];  //传输导出线程个数
        $imp_threadNum = $params['imp_max_process_num'];  //传输导入线程个数
        $sourceHostUuid = $params['failback_object']['source_host_uuid'];
        $targetHostUuid = $params['failback_object']['target_host_uuid'];
        $sourceAppUuid = $params['failback_object']['source_app_uuid'];
        $targetAppUuid = $params['failback_object']['target_app_uuid'];
        $sourceClusterFlag = $params['failback_object']['source_cluster_flag'];
        $targetClusterFlag = $params['failback_object']['target_cluster_flag'];
        $failbackMode = $params['failback_object']['failback_mode'];
        $failbackBusinessIpMap = $params['failback_object']['failback_business_ip_map'];
        $source_fileCacheAllocSpace = $params['cache_config']['source_file_cache_alloc_space'];
        $fileCacheAllocSpace = $params['cache_config']['file_cache_alloc_space'];
        $fileCacheStoragePath = $params['cache_config']['file_cache_storage_path'];
        $serviceFailbackMode = $params['service_failback_config']['service_failback_mode'];
        $serviceFailbackStartTime = $params['service_failback_config']['service_failback_start_time'] ?
            strtotime($params['service_failback_config']['service_failback_start_time']) : '';
        $serviceFailbackEndTime = $params['service_failback_config']['service_failback_end_time'] ?
            strtotime($params['service_failback_config']['service_failback_end_time']) : '';
        $service_failback_auto_trigger_threshold = $params['service_failback_config']['service_failback_auto_trigger_threshold'];

        $sql = 'select node_uuid from bd_task where task_uuid = ? ';
        $sqlParams = array($taskUuid);
        $data = $this->dbSelect($sql, $sqlParams);
        $nodeuuid = $data[0]['node_uuid'];

        $records = array(
            'task_uuid' => $taskUuid,
            'exp_max_process_num' => $exp_threadNum,
            'imp_max_process_num' => $imp_threadNum,
            'app_tablespace_dir' => $apptableSpaceDir
        );
        $records['transport_strategy'] = array(
            'transport_encrypt_flag' => v1_parse_bool_to_flag($transportEncryptFlag),
            'transport_compress_flag' => v1_parse_bool_to_flag($transportCompressFlag),
            'transport_block_size' => $transportBlockSize,
            'transport_compress_method' => $transportCompressMethod,
            'transport_encrypt_method' => $transportEncryptMethod,
        );
        $records['failback_object'] = array(
            'source_host_uuid' => $sourceHostUuid,
            'target_host_uuid' => $targetHostUuid,
            'source_app_uuid' => $sourceAppUuid,
            'target_app_uuid' => $targetAppUuid,
            'source_cluster_flag' => v1_parse_bool_to_flag($sourceClusterFlag),
            'target_cluster_flag' => v1_parse_bool_to_flag($targetClusterFlag),
            'failback_mode' => $failbackMode,
            'failback_business_ip_map' => $failbackBusinessIpMap,
        );
        $records['service_failback_config'] = array(
            'service_failback_mode' => $serviceFailbackMode,
            'service_failback_start_time' => $serviceFailbackStartTime,
            'service_failback_end_time' => $serviceFailbackEndTime,
            'service_failback_auto_trigger_threshold' => $service_failback_auto_trigger_threshold
        );
        $records['cache_config'] = array(
            'source_file_cache_alloc_space' => $source_fileCacheAllocSpace,
            'file_cache_alloc_space' => $fileCacheAllocSpace,
            'file_cache_storage_path' => $fileCacheStoragePath
        );
        $records['extend_advanced_config'] = $params['extend_advanced_config'];
        $records['select_copy_users'] = $params['select_copy_users'];
        $records['retry_strategy'] = $params['retry_strategy'];
        $opName = 'DB_CDP_TASK_FAILBACK_CONFIG';
        $pfOpcode = new PfOpcode();
        $operate = $pfOpcode->getOpcodeDes($opName);
        $mbResult = $this->service()->editFailBackConf($nodeuuid, $opName, json_encode($records));
        $result = $mbResult['result'];
        $msg = $mbResult['msg'];
        //返回结果到UI
        if ($result) {
            $resultMsg = $this->muOpResult($result, $operate, $msg);
        } else {
            $resultMsg = $this->muOpResult($result, $operate, xphp_get_lang('WEB_VOL_CDP_LOG_DESC_KEY_START_FAILBACK_FAILED'), '', $mbResult['errorCode']);
        }
        return $resultMsg;
    }

    /**
     * 应用信息
     * @param array $params 对应参数
     * @return array
     */
    private function getAppInfo($params)
    {
        $sql = 'select baa.app_type, baa.cluster_name,baa.cluster_service_ip,baa.app_name,
        baa.app_service_name,baa.app_listen_ip,cdda.detail
        from  bd_agent_app baa 
        inner join cdp_db_dr_app cdda on baa.app_uuid = cdda.app_uuid
        where baa.app_uuid = ? ';
        $sqlParams = array($params);
        $data = $this->dbSelect($sql, $sqlParams);

        $appInfo = array(
            'application_type' => $data[0]['app_type'],
            'cluster_name' => $data[0]['cluster_name'],
            'cluster_service_ip' => $data[0]['cluster_service_ip'],
            'app_name' => $data[0]['app_name'],
            'app_listen_ip' => $data[0]['app_listen_ip'],
            'app_service_name' => $data[0]['app_service_name'],
            'detail' => $data[0]['detail'],
        );
        return $appInfo;
    }

    /**
     * 客户端信息
     * @param array $params 对应参数
     * @return array
     */
    private function getAgentInfo($params)
    {
        $sql = 'select ba.agent_uuid, ba.agent_name, ba.hostname, ba.ip, ba.os_type, baa.app_uuid, 
        baa.app_name, baa.cluster_flag, baa.app_version, baa.cluster_uuid, baa.cluster_service_ip, baa.cluster_name
        from bd_agent ba 
        inner join bd_agent_app baa on ba.agent_uuid = baa.agent_uuid
        where ba.agent_uuid = ? ';
        $sqlParams = array($params);
        $data = $this->dbSelect($sql, $sqlParams);
        $agentInfo = array();
        if (!empty($data)) {
            $agentInfo[] = array(
                'agent_uuid' => $data[0]['agent_uuid'],
                'agent_name' => $data[0]['agent_name'],
                'hostname' => $data[0]['hostname'],
                'ip' => $data[0]['ip'],
                'os_type' => $data[0]['os_type'],
                'app_info' => array(
                    'app_uuid' => $data[0]['app_uuid'],
                    'app_name' => $data[0]['app_name'],
                    'cluster_flag' => v1_parse_flag_to_bool($data[0]['cluster_flag']),
                    'cluster_uuid' => $data[0]['cluster_uuid'],
                    'app_version' => $data[0]['app_version'],
                    'cluster_service_ip' => $data[0]['cluster_service_ip'],
                    'cluster_name' => $data[0]['cluster_name'],
                )
            );
        }
        return $agentInfo;
    }

    /**
     * 获取任务详情模块内的信息,公共接口没有的
     * @param array $params 对应参数
     * @return array
     */
    public function getBasicInfo($params)
    {
        // 任务是否存在
        $sql = "select id from bd_task where task_uuid = ?";
        $data = $this->dbSelect($sql,array($params['jobs_uuid']));
        if(empty($data)){
            return false;
        }
        //备机
        $backupSql = 'select ba.agent_name,ba.hostname,ba.ip,ba.online_flag,cddtti.takeover_business_ip_map 
            from cdp_db_dr_task cddt 
            inner join bd_agent ba 
            on cddt.target_agent_uuid = ba.agent_uuid
			inner join cdp_db_dr_task_takeover_info cddtti
            on cddtti.task_uuid = cddt.task_uuid
            where cddt.task_uuid = ?';

        //主机+目标机
        $hostSql = 'select ba.agent_name as source_agent_name, ba.hostname as source_hostname, ba.ip as source_ip, 
                    ba.online_flag as source_online_flag,
                    ba2.agent_name as target_agent_name, ba2.hostname as target_hostname, 
                    ba2.ip as target_ip, ba2.online_flag as target_online_flag,
                    cddt.source_agent_uuid, cddt.source_cluster_flag, 
                    cddt.source_app_uuid, cddt.target_agent_uuid,baa1.cluster_uuid as source_cluster_uuid,baa2.cluster_uuid as target_cluster_uuid,
                    cddt.target_cluster_flag, cddt.target_app_uuid, cddtti.switch_ip_flag,cddtti.id,cdda.detail
                    from cdp_db_dr_task cddt
                    inner join bd_agent ba on cddt.source_agent_uuid = ba.agent_uuid
                    inner join bd_agent ba2 on cddt.target_agent_uuid = ba2.agent_uuid
                    inner join bd_agent_app baa1 on cddt.source_agent_uuid  = baa1.agent_uuid
					inner join bd_agent_app baa2 on cddt.target_agent_uuid  = baa2.agent_uuid
                    inner join cdp_db_dr_app cdda on cddt.source_app_uuid = cdda.app_uuid
                    inner join cdp_db_dr_task_takeover_info cddtti on cddtti.task_uuid = cddt.task_uuid
                    where cddt.task_uuid = ? order by cddtti.id desc limit 1';

        // 是否配置网卡
        $backupData = $this->dbSelect($backupSql, array($params['jobs_uuid']));
        $hostData = $this->dbSelect($hostSql, array($params['jobs_uuid']));

        $sql = "select cddtci.file_cache_used_space,cddtci.file_cache_storage_path,
            cddtti.app_consecutive_failure_num,cddtti.app_fault_detection_interval,
            cddtti.failback_target_ip,cddtti.agent_heartbeat_failure_time,cddt.delay_load_time, cddt.auto_takeover_flag,cddt.allow_auto_takeover_flag
            from cdp_db_dr_task_cache_info cddtci 
            inner join cdp_db_dr_task_takeover_info cddtti on 
            cddtci.task_uuid = cddtti.task_uuid inner join cdp_db_dr_task cddt
         	on cddtti.task_uuid = cddt.task_uuid
            where cddtci.task_uuid = ?";
        $data = $this->dbSelect($sql, array($params['jobs_uuid']));

        $sqlPath = 'select cddtci.source_file_cache_used_space,cddtci.source_file_cache_alloc_space,cddtci.source_file_cache_storage_path,
                    cddtci.file_cache_used_space,cddtci.file_cache_alloc_space,cddtci.file_cache_storage_path,
                    cddt.delay_load_time, cddt.auto_takeover_flag, cddt.recovery_type, cddt.extend_advanced_config from 
                    cdp_db_dr_task_cache_info cddtci 
                    inner join cdp_db_dr_task cddt
         	        on cddtci.task_uuid = cddt.task_uuid
                    where cddtci.task_uuid = ?';
        $dataPath = $this->dbSelect($sqlPath, array($params['jobs_uuid']));

        $sqlTableNum = "select total_table_num,completed_table_num from cdp_db_dr_task_progress_info where task_uuid = ?";
        $dataTableNum = $this->dbSelect($sqlTableNum, array($params['jobs_uuid']));

        $sqlAllowFailback = "select allow_failback_flag, last_redo_replay_time, latest_redo_time from cdp_db_dr_task_takeover_failback_info where task_uuid = ?";
        $dataAllowFailback = $this->dbSelect($sqlAllowFailback, array($params['jobs_uuid']));

        $sqlTakeover = "select id from cdp_db_dr_task_takeover_info where task_uuid = ?";
        $dataTakeover = $this->dbSelect($sqlTakeover, array($params['jobs_uuid']));

        $sqlTask = "select task_type,ignore_resource_limiting_flag,user_uuid from bd_task where task_uuid = ?";
        $dataTask = $this->dbSelect($sqlTask,array($params['jobs_uuid']));

        // scn范围
        $sqlScn = "SELECT 
                    cddbi.last_redo_replay_scn,
                    cddbi.latest_recv_transaction_scn,
                    cddbi.timepoint_type,
                    cddbi.latest_redo_time,
                    cddbi.last_redo_replay_time
                FROM 
                    cdp_db_dr_backup_info cddbi 
                INNER JOIN 
                    cdp_db_dr_task cddt ON cddbi.source_agent_uuid = cddt.source_agent_uuid
                WHERE 
                    cddt.task_uuid = ?
                ORDER BY 
                    cddbi.id DESC 
                LIMIT 1;";
        $dataScn = $this->dbSelect($sqlScn,array($params['jobs_uuid']));

        // 导入/导出线程
        $sqlTaskConfig = "select imp_max_process_num, exp_max_process_num, select_copy_users, sync_direction from cdp_db_dr_task where task_uuid = ?";
        $dataTaskConfig = $this->dbSelect($sqlTaskConfig,array($params['jobs_uuid']));

        $return = array(
            'backup_info' => $backupData[0]['agent_name'] ?
                $backupData[0]['agent_name'] . '(' . $backupData[0]['ip'] . ')'
                : $backupData[0]['hostname'] . '(' . $backupData[0]['ip'] . ')',
            'backup_status' => $backupData[0]['online_flag'],
            'host_status' => $hostData[0]['online_flag'],
            'host_uuid' => $hostData[0]['source_agent_uuid'],               //主机的uuid
            'host_cluster_flag' => v1_parse_flag_to_bool($hostData[0]['source_cluster_flag']),     //主机是否是集群
            'host_app_uuid' => $hostData[0]['source_app_uuid'],             //主机的应用
            'host_cluster_uuid' => $hostData[0]['source_cluster_uuid'],
            'target_host_uuid' => $hostData[0]['target_agent_uuid'],
            'target_app_uuid' => $hostData[0]['target_app_uuid'],
            'target_cluster_flag' => v1_parse_flag_to_bool($hostData[0]['target_cluster_flag']),
            'target_cluster_uuid' => $hostData[0]['target_cluster_uuid'],
            'total_tables' => !empty($dataTableNum[0]['total_table_num']) ? $dataTableNum[0]['total_table_num'] : '--',
            'completed_tables' => !empty($dataTableNum[0]['completed_table_num']) ? $dataTableNum[0]['completed_table_num'] : '--',
            'source_file_cache_used_space' => v1_calsize($dataPath[0]['source_file_cache_used_space'], true),
            'source_file_cache_alloc_space' => v1_calsize($dataPath[0]['source_file_cache_alloc_space'], true),
            'source_file_cache_alloc_space_value' => $dataPath[0]['source_file_cache_alloc_space'],
            'source_file_cache_storage_path' => $dataPath[0]['source_file_cache_storage_path'],
            'file_cache_used_space' => v1_calsize($dataPath[0]['file_cache_used_space'], true),
            'file_cache_alloc_space' => v1_calsize($dataPath[0]['file_cache_alloc_space'], true),
            'file_cache_alloc_space_value' => $dataPath[0]['file_cache_alloc_space'],
            'file_cache_storage_path' => $dataPath[0]['file_cache_storage_path'],
            'app_consecutive_failure_num' => !empty($data[0]['app_consecutive_failure_num']) ? $data[0]['app_consecutive_failure_num'] : '--',
            'agent_heartbeat_failure_time' => $data[0]['agent_heartbeat_failure_time'] ?? '--',
            'app_fault_detection_interval' => $data[0]['app_fault_detection_interval'] ?? '--',
            'failback_target_ip' => $data[0]['failback_target_ip'],
            'transportStrategy_mode' => xphp_get_lang('UI_BACKUP_TRANSPORT_NBD'),
            'delay_load_time' => v1_sec_to_day_time($dataPath[0]['delay_load_time']),
            'delay_load_time_value' => $dataPath[0]['delay_load_time'],
            'delay_load_time_flag' => intval($dataPath[0]['delay_load_time']) !== 0 ? v1_parse_flag_to_bool(xphp_get_config('app')['FLAG']['SET']) : v1_parse_flag_to_bool(xphp_get_config('app')['FLAG']['UNSET']),
            'auto_takeover_flag' => v1_parse_flag_to_bool($dataPath[0]['auto_takeover_flag']),
            'recovery_type' => $dataPath[0]['recovery_type'] == xphp_get_config('app')['FLAG']['SET'] ? xphp_get_lang('UI_DB_CDP_DETAIL_ALL_AND_INCREASE') : xphp_get_lang('UI_DB_BACKUP_ADVANCED_TAB_INCREMENT'),
            'extend_advanced_config' => json_decode($dataPath[0]['extend_advanced_config']),
            'switch_ip_flag' => v1_parse_flag_to_bool($hostData[0]['switch_ip_flag']),
            'allow_failback_flag' => v1_parse_flag_to_bool($dataAllowFailback[0]['allow_failback_flag']),
            'takeover_business_ip_map' => $backupData[0]['takeover_business_ip_map'],       // 同步配置的网卡信息
            'isfailbackConfigFlag' => !empty($dataAllowFailback[0]),                         // 是否配置回切
            'istakoverConfigFlag' => !empty($dataTakeover[0]),                               // 是否配置接管
            'task_type' => $dataTask[0]['task_type'],
            'last_redo_replay_scn' => $dataScn[0]['last_redo_replay_scn'],
            'latest_recv_transaction_scn' => $dataScn[0]['latest_recv_transaction_scn'],
            'allow_auto_takeover_flag' => v1_parse_flag_to_bool($data[0]['allow_auto_takeover_flag']),
            'exp_max_process_num' => $dataTaskConfig[0]['exp_max_process_num'],
            'imp_max_process_num' => $dataTaskConfig[0]['imp_max_process_num'],
            'select_copy_users' => json_decode($dataTaskConfig[0]['select_copy_users']),
            'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($dataTask[0]['ignore_resource_limiting_flag']),
            'sync_direction' => $dataTaskConfig[0]['sync_direction'],
            'timepoint_type' => $dataScn[0]['timepoint_type'],
            'task_user_uuid' => $dataTask[0]['user_uuid'],
            'ip_scope_info' => json_decode($hostData[0]['detail'],true)['ip_scope_info'],
            'last_redo_replay_time' => $dataScn[0]['last_redo_replay_time'],
            'latest_redo_time' => $dataScn[0]['latest_redo_time'],
            'failback_last_redo_replay_time' => $dataAllowFailback[0]['last_redo_replay_time'],
            'failback_latest_redo_time' => $dataAllowFailback[0]['latest_redo_time']
        );
        return $return;
    }

    /**
     * 获取任务数据流向源/目标机的信息
     * @param array $params 对应参数
     * @return array
     */
    public function getMachineInfo($params)
    {
        $taskUuid = $params['jobs_uuid'];
        $sql = 'select cddt.source_agent_uuid,cddt.target_agent_uuid,cddt.source_cluster_flag,cddt.target_cluster_flag,
                cddt.source_agent_uuid,cddt.target_agent_uuid,cddt.source_app_uuid,
                cddt.source_cluster_ctrl_uuid,cddt.target_cluster_ctrl_uuid,
                cddt.source_agent_online_flag,cddt.target_agent_online_flag,cddt.sync_direction,bri.speed
                from cdp_db_dr_task cddt inner join bd_running_info bri on cddt.task_uuid = bri.task_uuid
								where cddt.task_uuid = ?';
        $sqlData = $this->dbSelect($sql, array($taskUuid));

        $sql = 'select agent_heartbeat_failure_time from cdp_db_dr_task_takeover_info where task_uuid = ?';
        $heartbeatSqlData = $this->dbSelect($sql, array($taskUuid));
        $source_cluster_ctrl_ip_data = '';
        $target_cluster_ctrl_ip_data = '';
        if ($sqlData[0]['source_cluster_flag'] == 1) {
            //源机是集群
            $sourceAgentsql = 'select baa.cluster_name,baa.cluster_uuid,baa.cluster_service_ip,baa.online_flag,
                               baa.app_type,baa.app_version,baa.app_service_name,baa.app_name,ba.os_type,ba.ip,
                               cddt.source_app_uuid,cddt.source_agent_uuid
                               from cdp_db_dr_task cddt inner join bd_agent_app baa 
                               on cddt.source_agent_uuid = baa.agent_uuid inner join 
                               bd_agent ba on baa.agent_uuid = ba.agent_uuid
                               where cddt.task_uuid = ?';
            $source_cluster_ctrl_sql = 'select cddt.source_cluster_ctrl_uuid,ba.ip,baa.app_name from cdp_db_dr_task cddt 
										inner join bd_agent ba 
										on cddt.source_cluster_ctrl_uuid = ba.agent_uuid 
										inner join bd_agent_app baa
										on ba.agent_uuid = baa.agent_uuid
										where cddt.task_uuid = ?';
            $source_cluster_ctrl_ip_data = $this->dbSelect($source_cluster_ctrl_sql,array($taskUuid));
        } else {
            $sourceAgentsql = 'select ba.agent_name,ba.hostname,ba.ip,ba.online_flag,ba.os_type,ba.ip,
                               cddt.source_agent_uuid,cddt.source_app_uuid,baa.app_type,baa.app_version,baa.app_service_name,baa.app_name
                               from cdp_db_dr_task cddt
                               inner join bd_agent ba on cddt.source_agent_uuid = ba.agent_uuid
                               inner join bd_agent_app baa on cddt.source_agent_uuid = baa.agent_uuid
                               where cddt.task_uuid = ?';
        }
        if ($sqlData[0]['target_cluster_flag'] == 1) {
            //目标机是集群
            $targetAgentsql = 'select cddt.target_agent_uuid,cddt.target_app_uuid,baa.cluster_name,
                               baa.cluster_uuid,baa.app_service_name,baa.online_flag,baa.cluster_service_ip,
                               baa.app_type,baa.app_version,ba.os_type,baa.cluster_uuid,baa.app_name,ba.ip
                               from cdp_db_dr_task cddt inner join bd_agent_app baa 
                               on cddt.target_agent_uuid = baa.agent_uuid 
                               inner join bd_agent ba on baa.agent_uuid  = ba.agent_uuid
                               where cddt.task_uuid = ?';

            //目标机是机器，获取集群的状态
            $targetClusterStatusSql = 'SELECT 
                                        baa.agent_uuid, 
                                        bda.online_flag,
                                        CASE 
                                        WHEN bda.online_flag = 2 THEN 2
                                        ELSE NULL
                                        END AS flag_status
                                        FROM bd_agent_app baa
                                        INNER JOIN bd_agent bda ON baa.agent_uuid = bda.agent_uuid
                                        WHERE baa.cluster_uuid IN (
                                        SELECT baa.cluster_uuid
                                        FROM cdp_db_dr_task cddt
                                        INNER JOIN bd_agent_app baa ON cddt.target_agent_uuid = baa.agent_uuid
                                        WHERE cddt.task_uuid = ?
                                        );';

            $target_cluster_ctrl_sql = 'select cddt.target_cluster_ctrl_uuid,ba.ip,baa.app_name from cdp_db_dr_task cddt 
										inner join bd_agent ba 
										on cddt.target_cluster_ctrl_uuid = ba.agent_uuid 
										inner join bd_agent_app baa
										on ba.agent_uuid = baa.agent_uuid
										where cddt.task_uuid = ?';
            $target_cluster_ctrl_ip_data = $this->dbSelect($target_cluster_ctrl_sql,array($taskUuid));
        } else {
            $targetAgentsql = 'select cddt.target_agent_uuid,cddt.target_app_uuid,
                               ba.agent_name,ba.hostname,ba.ip,ba.online_flag,baa.app_version,
                               baa.app_type,ba.os_type,baa.cluster_uuid,baa.app_service_name,baa.app_name,ba.ip
                               from cdp_db_dr_task cddt
                               inner join bd_agent ba on cddt.target_agent_uuid = ba.agent_uuid inner join 
                               bd_agent_app baa on baa.agent_uuid = cddt.target_agent_uuid
                               where cddt.task_uuid = ?';

            $targetClusterStatusSql = 'select ba.online_flag as flag_status
									   from cdp_db_dr_task cddt  inner join bd_agent ba on cddt.target_agent_uuid = ba.agent_uuid
									   where cddt.task_uuid = ?';
        }
        $sourceAgentsqlData = $this->dbSelect($sourceAgentsql, array($taskUuid));
        $targetAgentsqlData = $this->dbSelect($targetAgentsql, array($taskUuid));
        $targetClusterStatusSqlData = $this->dbSelect($targetClusterStatusSql, array($taskUuid));

        $sqlTask = 'select bt.task_status,bt.task_type,cddt.current_task_running_stage from bd_task bt
                    inner join cdp_db_dr_task cddt on bt.task_uuid = cddt.task_uuid
                    where bt.task_uuid = ? ';
        $sqlTaskData = $this->dbSelect($sqlTask, array($taskUuid));

        // 设置回切
        $failbackSql = "SELECT 
                        cddttfi.failback_target_agent_uuid,
                        cddttfi.target_cluster_flag,
                        ba.agent_name,
                        ba.hostname,
                        ba.online_flag,
                        CASE 
                        WHEN cddttfi.target_cluster_flag = 1 THEN baa.cluster_service_ip 
                        ELSE NULL 
                        END AS cluster_service_ip 
                        FROM 
                        cdp_db_dr_task_takeover_failback_info cddttfi 
                        INNER JOIN 
                        bd_agent ba ON cddttfi.failback_target_agent_uuid = ba.agent_uuid 
                        LEFT JOIN 
                        bd_agent_app baa ON ba.agent_uuid = baa.agent_uuid 
                        WHERE 
                        cddttfi.task_uuid = ?";
        $failbackData = $this->dbSelect($failbackSql, array($taskUuid));


        $return = array(
            'job_type' => $sqlTaskData[0]['task_type'] ,
            'job_status' => $sqlTaskData[0]['task_status'],
            'current_job_stage' => $sqlTaskData[0]['current_task_running_stage'],
            'source_agent_uuid' => $sourceAgentsqlData[0]['source_agent_uuid'],
            'source_app_uuid' => $sourceAgentsqlData[0]['source_app_uuid'],
            'source_app_type' => $sourceAgentsqlData[0]['app_type'],
            'source_app_version' => $sourceAgentsqlData[0]['app_version'],
            'source_os_type' => $sourceAgentsqlData[0]['os_type'],
            'source_cluster_flag' => $sqlData[0]['source_cluster_flag'] == 1 ? true : false,
            'target_cluster_flag' => $sqlData[0]['target_cluster_flag'] == 1 ? true : false,
            'source_online_flag' => $sourceAgentsqlData[0]['online_flag'] == 1 ? true : false,
            'target_online_flag' => $targetAgentsqlData[0]['online_flag'] == 1 ? true : false,
            'source_cluster_name' => $sourceAgentsqlData[0]['cluster_name'] ?? '',
            'source_cluster_uuid' => $sourceAgentsqlData[0]['cluster_uuid'] ?? '',
            'source_app_service_name' => $sourceAgentsqlData[0]['app_name'] ?? '',
            'source_cluster_service_ip' => $sourceAgentsqlData[0]['cluster_service_ip'] ?? '',
            'target_cluster_name' => $targetAgentsqlData[0]['cluster_name'] ?? '',
            'target_app_service_name' => $targetAgentsqlData[0]['app_name'] ?? '',
            'target_cluster_service_ip' => $targetAgentsqlData[0]['cluster_service_ip'] ?? '',
            'source_agent_name' => $sourceAgentsqlData[0]['agent_name'] ?? '',
            'target_agent_name' => $targetAgentsqlData[0]['agent_name'] ?? '',
            'source_hostname' => $sourceAgentsqlData[0]['hostname'] ??  '',
            'target_hostname' => $targetAgentsqlData[0]['hostname'] ?? '',
            'source_ip' => $sourceAgentsqlData[0]['ip'] ?? '',
            'target_ip' => $targetAgentsqlData[0]['ip'] ?? '',
            'target_agent_uuid' => $targetAgentsqlData[0]['target_agent_uuid'],
            'target_cluster_uuid' =>  $targetAgentsqlData[0]['cluster_uuid'] ?? '',
            'target_app_uuid' => $targetAgentsqlData[0]['target_app_uuid'],
            'target_app_type' => $targetAgentsqlData[0]['app_type'],
            'target_app_version' => $targetAgentsqlData[0]['app_version'],
            'target_os_type' => $targetAgentsqlData[0]['os_type'],
            'source_agent_online_flag' => $sqlData[0]['source_agent_online_flag'],
            'target_agent_online_flag' => $sqlData[0]['target_agent_online_flag'],
            'agent_heartbeat_failure_time' => $heartbeatSqlData[0]['agent_heartbeat_failure_time'],
            'sync_direction' => $sqlData[0]['sync_direction'],
            'failback_flag' => $failbackData[0]['failback_target_agent_uuid'] ? true : false,
            'failback_other_flag' =>
                                    $failbackData[0]['failback_target_agent_uuid']
                                    == $sourceAgentsqlData[0]['source_agent_uuid'] ?
                                    false : true,
            'failback_target_agent_uuid' =>
                $failbackData[0]['failback_target_agent_uuid'] ?? '',   //回切目标机 是否集群都显示具体agent
            'failback_target_cluster_flag' => v1_parse_flag_to_bool($failbackData[0]['target_cluster_flag']),   //回切目标机 是否集群
            'failback_agent_name' => $failbackData[0]['agent_name'],
            'failback_host_name' => $failbackData[0]['hostname'],
            'failback_target_online_flag' => v1_parse_flag_to_bool($failbackData[0]['online_flag']),
            'speed' => v1_calspeed($sqlData[0]['speed']),
            'source_cluster_ctrl_ip' => $source_cluster_ctrl_ip_data[0]['ip'] ?? $source_cluster_ctrl_ip_data,
            'source_cluster_ctrl_app_name' => $source_cluster_ctrl_ip_data[0]['app_name'] ?? $source_cluster_ctrl_ip_data,
            'target_cluster_ctrl_ip' => $target_cluster_ctrl_ip_data[0]['ip'] ?? $target_cluster_ctrl_ip_data,
            'target_cluster_ctrl_app_name' => $target_cluster_ctrl_ip_data[0]['app_name'] ?? $target_cluster_ctrl_ip_data
        );
        return $return;
    }

    /**
     * @param array $params 对应参数
     * @return false
     */
    public function getHistoryDetails($params)
    {
        // details
        $sql = 'select average_speed, details from bd_history_task where task_uuid = ? and history_uuid = ?';
        $data = $this->dbSelect($sql, array($params['jobs_uuid'],$params['history_uuid']));
        if (empty($data)) {
            return false;
        }
        $details = json_decode($data[0]['details'],true);
        // 源机器信息
        $sourceSql = "select agent_name, ip from bd_agent where agent_uuid = ?";
        $sourceDataSql = $this->dbSelect($sourceSql,array($details['source_agent_uuid']));

        // 目标机器信息
        $targetSql = "select agent_name, ip from bd_agent where agent_uuid = ?";
        $targetDataSql = $this->dbSelect($targetSql,array($details['target_agent_uuid']));

        // 已完成
        $sizeSql = "select completed_size from cdp_db_dr_task_progress_info where task_uuid = ?";
        $sizeSqlData = $this->dbSelect($sizeSql,array($params['jobs_uuid']));

        // 选择的用户
        $userSql = "select select_copy_users from cdp_db_dr_task where task_uuid = ?";
        $userData = $this->dbSelect($userSql,array($params['jobs_uuid']));
        $select_copy_users = json_decode($userData[0]['select_copy_users'],true);

        $historyDetail[] = array(
            'host_name' => $sourceDataSql[0]['agent_name'],     // 主机信息
            'master_node' => $sourceDataSql[0]['ip'],           // 主节点
            'standby_name' => $targetDataSql[0]['agent_name'],  // 备机信息
            'slave_node' => $targetDataSql[0]['ip'],            // 从属节点
            'source_monitor_app' => $details['source_app_info']['app_detail'][0]['app_instance_name'],        // 监控目标应用信息
            'target_monitor_app' => $details['target_app_info']['app_detail'][0]['app_instance_name'],        // 监控目标应用信息
            'average_speed' => $data[0]['average_speed'],       // 处理平均速度
            'completed_size' => v1_calsize($sizeSqlData[0]['completed_size'],true),                   // 处理数据大小
            'task_status' => $details['task_status'],            // 任务状态
            'error_code_des' => v1_get_error_des($details['error_code']),                                      // 错误码描述
            'error_code' => $details['error_code'],              // 错误码描述
            'select_copy_users' => $select_copy_users
        );
        return $historyDetail;
    }

    /**
     * 获取回切源为集群时target_cluster_ctrl_uuid字段
     * @return mixed|string
     */
    public function getFailbackSourceCluster($params)
    {
        $sql = "select target_cluster_ctrl_uuid from cdp_db_dr_task where task_uuid = ?";
        $data = $this->dbSelect($sql, array($params['jobUuid']));
        $sql = "select agent_uuid,hostname,ip from bd_agent where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($data[0]['target_cluster_ctrl_uuid']));
        $return[] = array(
            'agent_uuid' => $data[0]['agent_uuid'],
            'hostname' => $data[0]['hostname'],
            'agent_ip' => $data[0]['ip'],
        );
        return $return;
    }

    /**
     * 获取自动接管/手动接管的信息
     */
    public function getTakeoverInfo($params)
    {
        $return = array(
            'auto_takeover' => array(
                'auto_takeover_flag' => false
            ),
            'manual_takeover' => array(
                'manual_takeover_flag' => false
            ),
        );
        // 自动接管
        $autoSql = 'select failback_target_ip, takeover_timestamp, takeover_type, takeover_business_ip_map, switch_ip_flag, takeover_scn
                    from cdp_db_dr_task_takeover_info 
                    where task_uuid = ? and takeover_type = ?';
        $autoData = $this->dbSelect($autoSql, array($params['jobUuid'],xphp_get_config('app')['FLAG']['SET']));
        if(!empty($autoData)){
            $return['auto_takeover']['takeover_timestamp'] = $autoData[0]['takeover_timestamp'];
            $return['auto_takeover']['failback_target_ip'] = $autoData[0]['failback_target_ip'];
            $return['auto_takeover']['takeover_business_ip_map'] = $autoData[0]['takeover_business_ip_map'];
            $return['auto_takeover']['switch_ip_flag'] = v1_parse_flag_to_bool($autoData[0]['switch_ip_flag']);
            $return['auto_takeover']['auto_takeover_flag'] = true;
        }

        // 手动接管
        $manualSql = 'select failback_target_ip, takeover_timestamp, takeover_type, takeover_business_ip_map, switch_ip_flag, takeover_scn, takeover_timepoint_type
                      from cdp_db_dr_task_takeover_info 
                      where task_uuid = ? and takeover_type = ?';
        $manualData = $this->dbSelect($manualSql, array($params['jobUuid'],xphp_get_config('app')['FLAG']['UNSET']));
        if(!empty($manualData)){
            $return['manual_takeover']['takeover_timestamp'] = $manualData[0]['takeover_timestamp'];
            $return['manual_takeover']['failback_target_ip'] = $manualData[0]['failback_target_ip'];
            $return['manual_takeover']['takeover_business_ip_map'] = $manualData[0]['takeover_business_ip_map'];
            $return['manual_takeover']['switch_ip_flag'] = v1_parse_flag_to_bool($manualData[0]['switch_ip_flag']);
            $return['manual_takeover']['takeover_scn'] = $manualData[0]['takeover_scn'];
            $return['manual_takeover']['takeover_timepoint_type'] = $manualData[0]['takeover_timepoint_type'];
            $return['manual_takeover']['manual_takeover_flag'] = true;
        }

        return $return;
    }

    /**
     * 得到数据库实时错误描述
     * @param  $status    status
     * @param  $errorCode 错误码
     * @return mixed|string
     */
    private function getDBCDPErrorCodeDes($status, $errorCode)
    {
        if (xphp_get_config('dbcdp', 'DBcdpTaskStatus')['ERROR'] == $status) {
            $des = xphp_get_config('error', 'errorCodeDes')[xphp_get_config('error', 'errorCode')[$errorCode]];
        } else {
            $des = '';
        }
        return $des;
    }

    /**
     * 根据条件组合客户端的名称
     * @param $agentName 代理名
     * @param $hostName  主机名
     * @param $ip        ip
     * @return string
     */
    private function agentStr($agentName, $hostName, $ip)
    {
        if (!empty($agentName) && $agentName != $ip) {
            $hostInfo =  $agentName ? $agentName . '(' . $ip . ')' : $hostName . '(' . $ip . ')';
        } else {
            $hostInfo = $hostName . '(' . $ip . ')';
        }
        return $hostInfo;
    }

    /**
     * 获取数据库复制历史任务详情信息
     * @param array $details
     * @param string $taskType
     * @param array $info
     * @return array
     */
    public function historyTaskDetailsHandlerDbCdp($details, $taskType, $info)
    {
        if(isset($details['resource_limiting_node_config'])){
            return $details;
        }

        $result = array();
        $i = 0;
        $CDP_DB_BACKUP = xphp_get_config('task','TASKTYPE')['CDP_DB_BACKUP'];
        $CDP_DB_RECOVERY = xphp_get_config('task','TASKTYPE')['CDP_DB_RECOVERY'];
        // 源数据库实例
        $source_app_detail = $details['source_app_info']['app_detail'];
        foreach ($source_app_detail as $detail){
            $result[$i]['source_app_info_name'] = $detail['app_instance_name'].'('.$detail['app_listen_ip'].')';
        }
        // 目标数据库实例
        $target_app_detail = $details['target_app_info']['app_detail'];
        foreach ($target_app_detail as $detail){
            $result[$i]['target_app_info_name'] = $detail['app_instance_name'].'('.$detail['app_listen_ip'].')';
        }
        // 运行阶段
        $result[$i]['task_running_stage'] = xphp_get_config('task','DBCDP_TASK_RUNNING_STAGE')[$details['task_running_stage']];
        // 选择的用户
        $select_copy_users = json_decode($details['select_copy_users'],true);
        $result[$i]['select_copy_users'] = $select_copy_users;
        // 数据表空间目录
        $result[$i]['app_tablespace_dir'] = !empty($details['app_tablespace_dir'])? $details['app_tablespace_dir'] : '--';
        // 字典总数量
        $result[$i]['completed_dict_num'] = $details['completed_dict_num'];
        // 表总数量
        $result[$i]['completed_table_num'] = $details['completed_table_num'];
        // 全量数据大小
        $result[$i]['full_sync_completed_size'] = v1_calsize(intval($details['full_sync_completed_size']),true);
        // 增量数据大小
        $result[$i]['sync_redo_log_size'] = v1_calsize(intval($details['sync_redo_log_size']),true);
        // 日志数据大小
        $result[$i]['log_sync_completed_size'] = v1_calsize(intval($details['log_sync_completed_size']),true);
        // 任务状态
        $status = xphp_get_config('task','TASKSTATUSDES')[$details['task_status']];
        $result[$i]['task_status'] = xphp_get_lang($status);
        // 描述
        $result[$i]['description'] = (new JobInfo())->getJobStatusShowPopover($details['error_code']);
        if($taskType == $CDP_DB_BACKUP) {
            // 复制
            // 接管回切数据库实例
            $result[$i]['failback_app_info_name'] = '--';
            if(!empty($details['failback_target_app_info'])){
                $failback_app_detail = $details['failback_target_app_info']['app_detail'];
                foreach ($failback_app_detail as $detail){
                    $result[$i]['failback_app_info_name'] = $info['app_instance_name'].'('.$info['app_listen_ip'].')';
                }
            }
            // 接管时间方式
            $result[$i]['timepoint_type'] = $details['takeover_timepoint_type'];
            $result[$i]['target_time'] = $details['takeover_target_time'];  //指定时间点
            $result[$i]['target_scn'] = $details['takeover_target_scn'];  //指定scn
        }else if($taskType == $CDP_DB_RECOVERY){
            // 恢复时间方式
            $result[$i]['timepoint_type'] = $details['recovery_timepoint_type'];
            $result[$i]['target_time'] = $details['recovery_target_time'];
            $result[$i]['target_scn'] = $details['recovery_target_scn'];
        }
        return $result;
    }

    /**
     * 获取单个任务的流量
     * @param string $jobUuid 任务uuid
     * @return array|bool
     */
    public function getDbcdpJobFlow(string $jobUuid)
    {
        $sql = "SELECT bri.speed, bri.speed_time, bt.task_status, cddtpi.sync_redo_log_speed, cddtpi.transmission_speed
                FROM bd_running_info bri
                INNER JOIN bd_task bt ON bri.task_uuid = bt.task_uuid
                INNER JOIN cdp_db_dr_task_progress_info cddtpi ON bri.task_uuid = cddtpi.task_uuid
                WHERE bt.task_uuid = ?";

        $data = $this->dbSelect($sql, [$jobUuid]);
        $resultInfo = array();
        if(!empty($data)){
            foreach ($data as $d){
                $resultInfo = array(
                    'speed' => v1_calspeed($data[0]['speed']),
                    'speed_value' => $d['speed'],
                    'sync_redo_log_speed_value' => $d['sync_redo_log_speed'],
                    'speed_time' => $d['speed_time'],
                    'timestamp' => TIMESTAMP,
                    'nowTime' => date('H:i:s'),
                    'transmission_speed_value' => $d['transmission_speed']
                );
            }
        }
        return $resultInfo;
    }

    /**
     * 导入/导出详情
     * @params array
     * @return array
     */
    public function getImportExportDetails($params)
    {
        $offset = $params['offset'];
        $limit = $params['limit'];
        $task_uuid = $params['task_uuid'];
        $sync_direction = $params['sync_direction'];
        $sql = "SELECT 
                combined_field,
                sync_direction,
                MAX(CASE WHEN status_type = 1 THEN table_status END) AS status_type_import,
                MAX(CASE WHEN status_type = 2 THEN table_status END) AS status_type_export
                FROM (
                SELECT 
                CASE 
                WHEN pdb_name IS NULL OR pdb_name = '' THEN CONCAT(table_owner, '.', table_name)
                ELSE CONCAT(pdb_name, '.', table_owner, '.', table_name)
                END AS combined_field,
                sync_direction,
                status_type,
                table_status
                FROM 
                cdp_db_dr_backup_table_status_info
                WHERE 
                task_uuid = ? and sync_direction = ?
                ) AS subquery
                GROUP BY combined_field, sync_direction
                ORDER BY combined_field limit ?, ?";
        $data = $this->dbSelect($sql,array($task_uuid,$sync_direction,$offset,$limit));

        $countSql = "SELECT COUNT(DISTINCT 
                     CASE 
                     WHEN pdb_name IS NULL OR pdb_name = '' 
                    THEN CONCAT(table_owner, '.', table_name)
                    ELSE CONCAT(pdb_name, '.', table_owner, '.', table_name)
                    END
                    ) AS total_records
                    FROM cdp_db_dr_backup_table_status_info
                    WHERE 
                    task_uuid = ?
                    AND sync_direction = ?";

        $countData = $this->dbSelect($countSql,array($task_uuid,$sync_direction));
        $count = $countData[0]['total_records'];
        $rows = [];
        $num = 0;
        $i = 0;
        if(!empty($data)){
            foreach ($data as $d){
                $i++;
                $rows[] = array(
                    'num' =>  $params['offset'] + $i,
                    'combined_field' => $d['combined_field'],
                    'sync_direction' => $d['sync_direction'],
                    'status_type_import' => $d['status_type_import'],
                    'status_type_export' => $d['status_type_export'],
                );
                $num++;
            }
        }
        return [
            'rows' => $rows,
            'total' => $count
        ];
    }
}
