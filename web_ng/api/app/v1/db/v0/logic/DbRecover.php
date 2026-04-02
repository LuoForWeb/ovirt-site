<?php

namespace app\v1\db\v0\logic;

use app\v1\common\logic\Recover;
use app\v1\opcode\DbProtectOpcode;

/**
 * note          数据库 之恢复管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbRecover extends Recover
{
    /**
     * 构建恢复任务消息
     * @param array  $params   参数
     * @param string $taskName 任务名称
     * @return array
     */
    private function buildDbRecoveryJobMsg(array $params, string $taskName): array
    {
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $backupHandler = new \app\v1\common\logic\Backup();
        $transportStrategy = $backupHandler->pfTransportStrategyMessage(
            v1_parse_bool_to_flag($params['transfer_strategy']['encrypt_flag']),
            v1_parse_bool_to_flag(false),
            v1_parse_bool_to_flag(false),
            0,
            $params['transfer_strategy']['network_uuid'] ?: '',
            '',
            0,
            $params['transfer_strategy']['reconnect_times'],
            $params['transfer_strategy']['reconnect_interval'],
            $params['transfer_strategy']['encrypt_method'],
            v1_parse_bool_to_flag(false),
            '',
            $params['transfer_strategy']['network_pool_uuid'] ?: ''
        );
        $transportStrategy['max_object_transport_parallel_nums'] = $params['transfer_strategy']['max_object_transport_parallel_nums'];

        $speedStrategy = $backupHandler->groupTaskSpeedGlobalList($params['speed_strategy']);

        $backupHandler = new \app\v1\common\logic\Backup();
        $taskType = $allTaskType['DB_RECOVERY'];
        if ($params['recovery_source_type'] == xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db')['LATEST_TIMEPOINT']) {
            $taskType = $allTaskType['DRILL'];
        }
        return [
            'task_uuid' => $params['jobs_uuid'] ?: '',
            'task_name' => $taskName,
            'module_type' => xphp_get_config('module', 'MODULE_TYPE')['DB'],
            'task_type' => $taskType,
            'db_type' => $params['db_type'],
            'recovery_position' => $params['recovery_type'],
            'recovery_level' => $params['recovery_type'],
            'recovery_time_type' => $params['time_strategy']['type'],  // 时间策略类型
            'destination_agent_uuid' => $params['recovery_target']['agent_uuid'],
            'strategy_group_uuid' => '',
            'recovery_type' => $params['recovery_source_type'],  // 指定时间点恢复 & 定时恢复最新备份点
            'recovery_time_flag' => $params['recovery_time_flag'],
            'time_strategy_list' => $this->groupRecoverTimeList($params['time_strategy']),
            'transport_strategy' => $transportStrategy,
            'channel_count' => $params['transfer_strategy']['channel_count'],
            'thread_num' => $params['transfer_strategy']['thread_num'],
            'speed_limit_strategy_list' => [],  // 限速策略
            'speed_limit_strategy' => $speedStrategy,
            'recovery_dbs_info' => $this->buildRecoveryDbInfo(
                $params['recovery_source'],
                $params['db_type'],
                $params['recovery_type'],
                $params['recovery_source_type'],
                $params['jobs_uuid'] ?: ''
            ),
            'retry_strategy' => $backupHandler->groupRetryStrategy($params['retry_strategy']),
            'ignore_resource_limiting_flag' => v1_parse_bool_to_flag($params['ignore_resource_limiting_flag'] ?? false),
            'safe_config_strategy' => $backupHandler->groupSafeConfigStrategy($params['safe_config_strategy']),
        ];
    }
    /**
     * 创建数据库备份任务
     * @param array $params 全部参数
     * @return array
     */
    public function createDbRecoveryJob(array $params): array
    {
        if ($params['recovery_source_type'] == xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db')['LATEST_TIMEPOINT']) {
            $expireDate = v1_license_get_expire_days();
            if ($expireDate['expire_days'] < 0) {
                return $this->sendResult(xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED'), false, 0);
            }
        }
        $taskName = htmlspecialchars_decode(v1_escape_wildcard($params['task_name']));
        // $sql = "SELECT * FROM bd_task WHERE task_name = ? ";
        // if ($this->dbSelect($sql, [$taskName])) {
        //     return $this->sendResult(
        //         sprintf(xphp_get_lang('WEB_COMMON_TASK_NAME_ALREADY_EXISTS'), $taskName),
        //         false,
        //         0
        //     );
        // }

        $msg = $this->buildDbRecoveryJobMsg($params, $taskName);
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        $operate = $this->pfOpcode->getOpcodeDes($opName);
        $createResult = $this->service()->createDbRecoveryJobService($msg, $params['node_uuid'] ?: '');
        if (!$createResult['result']) {
            $this->muOpResult(false, $operate, $createResult['msg'], 0, $createResult['errorCode']);
            return $this->sendResult('', false, 0);
        }
        // 恢复完成后，立即恢复或按策略恢复
        $allRecoveryTimeType = xphp_get_config('task', 'RECOVERY_TIME_TYPE');
        $startResult = true;
        if ($params['time_strategy']['type'] == $allRecoveryTimeType['IMMEDIATELY']) {
            // 立即恢复
            $startResult = $this->startRecoverJob($taskName);
        }
        if (!$startResult) {
            return $this->sendResult('', false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_DB_RECOVERY_CREATE_SUCCESS'));
    }

    /**
     * 恢复任务创建完成以后启动恢复任务
     * @param string $taskName 任务名
     * @return boolean
     */
    private function startRecoverJob(string $taskName): bool
    {
        $sql = "SELECT bt.task_uuid, bt.module_type, bt.task_type, dt.db_type
                FROM bd_task bt
                    INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid
                WHERE bt.task_name = ? AND (bt.task_type = ? OR bt.task_type = ?)
                ORDER BY bt.id DESC";
        $sqlParams = [
            $taskName,
            xphp_get_config('task', 'TASKTYPE')['DB_RECOVERY'],
            xphp_get_config('task', 'TASKTYPE')['DRILL']
        ];
        $data = $this->dbSelect($sql, $sqlParams);
        if (!$data) {
            return false;
        }
        //调用系统统一启动任务接口.不重新写
        $dbJobController = new DbJobController();
        $result = $dbJobController->startJob($data[0]['task_uuid'], xphp_get_config('task', 'BACKUP_MODE')['FULL']);
        //这里直接返回成功或失败 bool
        return $result[0];
    }

    /**
     * 构建传输策略
     * @param array   $transferStrategy  传输策略
     * @param ?string $strategyGroupUuid 策略组uuid
     * @return array
     */
    private function buildTransportStrategy(array $transferStrategy, ?string $strategyGroupUuid): array
    {
        return [
            'encrypt_flag' => v1_parse_bool_to_flag($transferStrategy['encrypt_flag']),
            'compress_flag' => v1_parse_bool_to_flag(false),
            'speed_limit_flag' => v1_parse_bool_to_flag(false),
            'max_speed' => 0,
            'network_uuid' => $transferStrategy['network_uuid'] ?: '',
            'strategy_group_uuid' => $strategyGroupUuid,
            'compress_method' => intval($transferStrategy['compress_method'] ?? 0),
            'encrypt_method' => intval($transferStrategy['encrypt_method']),
        ];
    }

    /**
     * 构建时间策略
     * @param array  $timeStrategy      时间策略
     * @param array  $recoveryInfo      恢复信息
     * @param string $strategyGroupUuid 策略组
     * @return array
     */
    private function buildTimeStrategy(array $timeStrategy, array $recoveryInfo, string $strategyGroupUuid): array
    {
        $retStrategy = [];
        $allTimeType = xphp_get_config('db', 'TIME_TYPE', 'db');
        $allSourceRecoveryType = xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db');  // 源恢复方式【恢复最新点...】
        if ($allSourceRecoveryType['LATEST_TIMEPOINT'] == $recoveryInfo['recovery_source_type']) {
            $retStrategy[] = [
                'strategy_type' => intval($timeStrategy['strategy']['time_type']),
                'mode' => xphp_get_config('db', 'BACKUP_MODE', 'db')['full_backup'],  // 恢复采用完备
                'days' => implode('', $timeStrategy['strategy']['days']),
                'start_time' => $timeStrategy['strategy']['start_time'],
                'roll_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
                'roll_interval' => 0,
                'roll_end_time' => '',
                'global_id' => 0,
                'strategy_group_uuid' => $strategyGroupUuid,
            ];
            if ($allTimeType['day'] == $timeStrategy['time_type']) {
                $retStrategy['days'] = '1111111';
            } elseif ($allTimeType['once'] == $timeStrategy['time_type']) {
                $retStrategy['days'] = '';
            }
        }
        return $retStrategy;
    }

    /**
     * 获取数据库恢复任务
     * @param string $jobsUuid 恢复任务的uuid
     * @return array
     */
    public function getDbRecoveryJob(string $jobsUuid): array
    {
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $jobInfo = $this->checkTaskExists($jobsUuid, [$allTaskType['DB_RECOVERY'], $allTaskType['DRILL']]);
        if (false === $jobInfo) {
            return $this->sendResult(xphp_get_lang('WEB_DB_RECOVERY_TASK_NOT_EXISTS'), false, 0);
        }
        // 获取恢复任务
        $sql = "SELECT dl.instance_name AS target_instance_name, dl.agent_uuid AS target_agent_uuid,
                    dl.dir_path AS target_dir_path, dl.timepoint_uuid, dl.recovery_mode, dl.log_rollback_time,
                    dl.new_db_name, dl.data_file_path, dl.log_file_path, dl.detail AS dl_detail,
                    dl.modify_pfile_flag, dl.task_uuid, dl.log_restore_start_time, dl.log_restore_end_time,
                    dl.open_db_flag, dl.source_instance_name, dl.source_db_name, dl.source_agent_uuid,
                    dl.dir_path AS dl_dir_path, dl.db_name AS dl_target_db_name,
                    dl.db_uuid AS dl_db_uuid,
                    dl.recovery_time, dl.initialize_log_area, dl.latest_timepoint_uuid,
                    dl.before_task_script, dl.after_task_script, dl.verification_script,
                    dt.channel_count, dt.recovery_time_flag, dt.db_type
                FROM db_list dl
                    INNER JOIN db_task dt ON dl.task_uuid=dt.task_uuid
                WHERE dl.task_uuid = ? ";
        $jobRecoveryList = $this->dbSelect($sql, [$jobsUuid]);
        if (!$jobRecoveryList || !is_array($jobRecoveryList)) {
            return $this->sendResult(xphp_get_lang('WEB_DB_RECOVERY_TASK_NOT_EXISTS'), false, 0);
        }

        return $this->sendResult('', true, 200, [
            'job_name' => $jobInfo['task_name'],
            'job_uuid' => $jobInfo['task_uuid'],
            'db_type' => (int) $jobRecoveryList[0]['db_type'],
            'recovery_target' => $this->getDbRecoveryTarget($jobRecoveryList[0]),
            'recovery_info' => $this->getDbRecoveryInfo($jobsUuid, $jobRecoveryList),
            'speed_strategy' => $this->getSpeedStrategy($jobsUuid),
            'transfer_strategy' => $this->getTransferStrategy($jobInfo, $jobRecoveryList[0]['channel_count']),
            'time_strategy' => $this->getTimeStrategy($jobInfo['strategy_id']),
            'retry_strategy' => $this->groupRetryStrategyInfo($jobsUuid),
            'safe_config_strategy' => $this->getSafeConfigStrategy($jobsUuid),
        ]);
    }

    /**
     * 构建恢复任务的恢复目标
     * @param array $jobRecoveryInfo 恢复任务信息
     * @return array
     */
    private function getDbRecoveryTarget(array $jobRecoveryInfo): array
    {
        // 查询集群信息
        $sql = "SELECT cluster_uuid, cluster_flag FROM bd_agent_app WHERE app_name = ? AND agent_uuid = ? AND app_type = ? ";
        $sqlParams = [
            $jobRecoveryInfo['target_instance_name'],
            $jobRecoveryInfo['target_agent_uuid'],
            $jobRecoveryInfo['db_type'],
        ];
        $clusterData = $this->dbSelect($sql, $sqlParams);
        $clusterFlag = false;
        $clusterUuid = '';
        if (is_array($clusterData)) {
            $clusterFlag = v1_parse_flag_to_bool($clusterData[0]['cluster_flag']) && $clusterData[0]['cluster_uuid'];
            $clusterUuid = $clusterData[0]['cluster_uuid'];
        }
        return [
            'agent_uuid' => $jobRecoveryInfo['target_agent_uuid'],
            'instance_name' => $jobRecoveryInfo['target_instance_name'],
            'cluster_flag' => $clusterFlag,
            'cluster_uuid' => $clusterUuid,
        ];
    }

    /**
     * 构建恢复信息
     * @param string $jobsUuid        任务uuid
     * @param array  $jobRecoveryList 恢复任务数据列表
     * @return array
     */
    private function getDbRecoveryInfo(string $jobsUuid, array $jobRecoveryList): array
    {
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $allSourceRecoveryType = xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db');
        $sql = "SELECT bt.task_name, bt.task_uuid, bt.task_type, bt.recovery_type, bt.ignore_resource_limiting_flag,
                    dt.recovery_time_flag, dt.db_type
                FROM bd_task bt
                    INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid
                WHERE bt.task_uuid = ? ";
        $jobInfo = $this->dbSelect($sql, [$jobsUuid])[0];
        if (!is_array($jobInfo)) {
            $jobInfo = [];
        }

        $recoveryInfo = [
            'recovery_type' => (int) $jobRecoveryList[0]['recovery_mode'],
            'db_config_list' => [],
            'recovery_source_type' => intval($jobInfo['recovery_type']),
            'recovery_time_flag' => intval($jobInfo['recovery_time_flag']),
            'open_db_flag' => v1_parse_flag_to_bool($jobRecoveryList[0]['open_db_flag']),
            'channel_count' => (int) $jobRecoveryList[0]['channel_count'],
            'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($jobInfo['ignore_resource_limiting_flag']),
        ];
        foreach ($jobRecoveryList as $jobRecoveryInfo) {
            $sourceClusterFlag = false;
            $detail = new \stdClass();
            $sourceClusterUuid = '';
            if ($allSourceRecoveryType['LATEST_TIMEPOINT'] == $jobInfo['recovery_type']) {  // 恢复最新点一定存在
                $sql = "SELECT cluster_uuid, cluster_flag FROM bd_agent_app WHERE app_name = ? AND app_type = ? AND agent_uuid = ? ";
                $sqlParams = [
                    $jobRecoveryInfo['source_instance_name'],
                    $jobInfo['db_type'],
                    $jobRecoveryInfo['source_agent_uuid'],
                ];
                $clusterData = $this->dbSelect($sql, $sqlParams);
                if (is_array($clusterData) && $clusterData) {
                    $sourceClusterFlag = v1_parse_flag_to_bool($clusterData[0]['cluster_flag']) && $clusterData[0]['cluster_uuid'];
                    $sourceClusterUuid = $clusterData[0]['cluster_uuid'];
                }
                switch ($jobRecoveryList[0]['db_type']) {
                    case $allDbType['MYSQL']:
                    case $allDbType['MARIA']:
                        $detail = json_decode($jobRecoveryList[0]['dl_detail'], true);
                        break;
                }
            } else {
                switch ($jobRecoveryList[0]['db_type']) {
                    case $allDbType['ORACLE']:
                        $detail = json_decode($jobRecoveryList[0]['dl_detail'], true);
                        if (isset($detail['pfile_info'])) {
                            $detail['pfile_info']['recovery_flag'] = v1_parse_flag_to_bool($detail['pfile_info']['recovery_flag']);
                        }
                        if (isset($detail['listener_info'])) {
                            $detail['listener_info']['recovery_flag'] = v1_parse_flag_to_bool($detail['listener_info']['recovery_flag']);
                        }
                        if (isset($detail['tnsnames_info'])) {
                            $detail['tnsnames_info']['recovery_flag'] = v1_parse_flag_to_bool($detail['tnsnames_info']['recovery_flag']);
                        }
                        if (isset($detail['sqlnet_info'])) {
                            $detail['sqlnet_info']['recovery_flag'] = v1_parse_flag_to_bool($detail['sqlnet_info']['recovery_flag']);
                        }
                        if (isset($detail['password_info'])) {
                            $detail['password_info']['recovery_flag'] = v1_parse_flag_to_bool($detail['password_info']['recovery_flag']);
                        }
                        break;
                    case $allDbType['MYSQL']:
                    case $allDbType['MARIA']:
                        $detail = json_decode($jobRecoveryList[0]['dl_detail'], true);
                        break;
                }
            }
            $tmpDbInfo = [
                'db_name' => $jobRecoveryInfo['dl_target_db_name'],
                'instance_name' => $jobRecoveryInfo['target_instance_name'],
                'agent_uuid' => $jobRecoveryInfo['target_agent_uuid'],
                'timepoint_uuid' => $jobRecoveryInfo['timepoint_uuid'],
                'new_db_name' => $jobRecoveryInfo['new_db_name'],
                'data_file_path' => $jobRecoveryInfo['data_file_path'],
                'log_file_path' => $jobRecoveryInfo['log_file_path'],
                'detail' => $detail,
                'dir_path' => $jobRecoveryInfo['dl_dir_path'],
                'db_uuid' => $jobRecoveryInfo['dl_db_uuid'],
                'rollback_time' => $jobRecoveryInfo['log_rollback_time'],
                'modify_pfile_flag' => v1_parse_flag_to_bool($jobRecoveryInfo['modify_pfile_flag']),
                'log_restore_start_time' => $jobRecoveryInfo['log_restore_start_time'],
                'log_restore_end_time' => $jobRecoveryInfo['log_restore_end_time'],
                'open_db_flag' => v1_parse_flag_to_bool($jobRecoveryInfo['open_db_flag']),
                'source_agent_uuid' => $jobRecoveryInfo['source_agent_uuid'],
                'source_instance_name' => $jobRecoveryInfo['source_instance_name'],
                'source_db_name' => $jobRecoveryInfo['source_db_name'],
                'source_cluster_flag' => $sourceClusterFlag,
                'source_cluster_uuid' => $sourceClusterUuid,
                'recovery_time' => $jobRecoveryInfo['recovery_time'],
                'initialize_log_area' => v1_parse_flag_to_bool($jobRecoveryInfo['initialize_log_area']),
                'latest_timepoint_uuid' => $jobRecoveryInfo['latest_timepoint_uuid'],
                'before_task_script' => json_decode($jobRecoveryInfo['before_task_script'], true) ?: [],
                'after_task_script' => json_decode($jobRecoveryInfo['after_task_script'], true) ?: [],
                'verification_script' => json_decode($jobRecoveryInfo['verification_script'], true) ?: [],
            ];
            $recoveryInfo['db_config_list'][] = $tmpDbInfo;
        }

        return $recoveryInfo;
    }

    /**
     * 构建传输策略信息
     * @param array $jobInfo      任务信息
     * @param int   $channelCount 通道数量
     * @return array
     */
    private function getTransferStrategy(array $jobInfo, $channelCount): array
    {
        $allTransportMode = xphp_get_config('db', 'TRANSPORT_MODE', 'db');
        $sql = "SELECT bts.network_uuid, bts.encrypt_flag, bts.encrypt_method, bts.network_pool_uuid,
                    bts.max_object_transport_parallel_nums,
                    bnn.ip, bnn.port
                FROM bd_transport_strategy bts
                    LEFT JOIN bd_node_network bnn on bts.network_uuid = bnn.network_uuid
                WHERE bts.task_uuid = ? ";
        $transportInfo = $this->dbSelect($sql, [$jobInfo['task_uuid']])[0];
        return [
            'encrypt_flag' => v1_parse_flag_to_bool($transportInfo['encrypt_flag']),
            'encrypt_method' => intval($transportInfo['encrypt_method']),
            'transport_mode' => $allTransportMode['network'],  // 目前只有网络传输
            'network_uuid' => $transportInfo['network_uuid'],
            'network_pool_uuid' => $transportInfo['network_pool_uuid'],
            'network_ip' => $transportInfo['ip'] ?: '',
            'network_port' => $transportInfo['port'] ?: 0,
            'thread_num' => (int) $jobInfo['thread_num'],
            'channel_count' => $channelCount,
            'max_object_transport_parallel_nums' => (int) $transportInfo['max_object_transport_parallel_nums'],
        ];
    }

    /**
     * 获取时间策略
     * @param string $strategyId 策略ID
     * @return array
     */
    private function getTimeStrategy(string $strategyId): array
    {
        $allRecoveryTimeType = xphp_get_config('task', 'RECOVERY_TIME_TYPE');
        $sql = "SELECT strategy_id, mode, strategy_type, days, start_time,
                    roll_flag, roll_interval, roll_end_time
                FROM bd_time_strategy WHERE strategy_id = ? ";
        $timeStrategyData = $this->dbSelect($sql, [$strategyId]);
        if (!is_array($timeStrategyData) || !$timeStrategyData) {
            return [
                'recovery_type' => $allRecoveryTimeType['IMMEDIATELY'],
            ];
        }
        $timeStrategy = $timeStrategyData[0];
        return [
            'recovery_type' => $allRecoveryTimeType['STRATEGY'],
            'strategy' => [
                'recovery_mode' => intval($timeStrategy['mode']),
                'time_type' => intval($timeStrategy['strategy_type']),
                'days' => $timeStrategy['days'] ? array_map('intval', str_split($timeStrategy['days'])) : [],
                'start_time' => $timeStrategy['start_time'],
                'is_rollback' => false,
                'roll_interval' => 0,
                'roll_end_time' => '',
            ],
        ];
    }

    /**
     * 构建恢复数据库信息
     * @param array  $recoverySource     恢复源信息
     * @param int    $dbType             数据库类型
     * @param int    $recoveryType       恢复方式
     * @param int    $sourceRecoveryType 源恢复方式
     * @param string $jobsUuid           任务uuid
     * @return array
     */
    private function buildRecoveryDbInfo(
        array $recoverySource,
        int $dbType,
        int $recoveryType,
        int $sourceRecoveryType,
        string $jobsUuid
    ): array {
        $sql = "SELECT db_uuid, error_code FROM db_list WHERE task_uuid = ? ";
        $dbInfoData = $this->dbSelect($sql, [$jobsUuid]);
        $dbInfoMap = [];
        if (is_array($dbInfoData)) {
            foreach ($dbInfoData as $dbInfo) {
                $dbInfoMap[$dbInfo['db_uuid']] = $dbInfo;
            }
        }
        $recoveryDbsInfo = [];
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $allRecoveryType = xphp_get_config('db', 'DB_RECOVERY_TYPE');
        switch ($dbType) {
            case $allDbType['SQLSERVER']:
            case $allDbType['SAPHANA']:  // 多数据库恢复
                foreach ($recoverySource as $dbInfo) {
                    $recoveryDbsInfo[] = [
                        'instance_name' => $dbInfo['instance_name'],
                        'src_db_name' => $dbInfo['old_db_name'],
                        'create_db_flag' => $recoveryType == $allRecoveryType['CREATE'] ? 1 : 0,
                        'new_db_name' => $dbInfo['new_db_name'],
                        'new_data_file_path' => $dbInfo['datafile_path'],
                        'new_log_file_path' => $dbInfo['logfile_path'],
                        'log_rollback_time' => $dbInfo['rollback_flag'] ? $dbInfo['rollback_time'] : '',
                        'recovery_timepoint_uuid' => $dbInfo['timepoint_uuid'],
                        'new_db_password' => $dbInfo['new_db_password'],
                        'detail' => '',
                        'recovery_mode' => $recoveryType,
                        'modify_pfile_flag' => v1_parse_bool_to_flag(false),
                        'timepoint_password' => base64_decode($dbInfo['encrypt_password']),
                        'log_restore_start_time' => $dbInfo['log_restore_start_time'],
                        'log_restore_end_time' => $dbInfo['log_restore_end_time'],
                        'open_db_flag' => v1_parse_bool_to_flag($dbInfo['open_db_flag']),
                        'source_agent_uuid' => $dbInfo['source_agent_uuid'],
                        'source_instance_name' => $dbInfo['source_instance_name'],
                        'source_db_name' => $dbInfo['source_db_name'],
                        'recovery_time' => $dbInfo['recovery_time'],
                        'initialize_log_area' => v1_parse_bool_to_flag($dbInfo['initialize_log_area']),
                        'latest_timepoint_uuid' => $dbInfo['latest_timepoint_uuid'],
                        'db_uuid' => $dbInfo['db_uuid'],
                        'error_code' => isset($dbInfoMap[$dbInfo['db_uuid']]) ? $dbInfoMap[$dbInfo['db_uuid']]['error_code'] : 0,
                        'before_task_script' => $dbInfo['before_task_script'] ? json_encode($dbInfo['before_task_script']) : '',
                        'after_task_script' => $dbInfo['after_task_script'] ? json_encode($dbInfo['after_task_script']) : '',
                        'verification_script' => $dbInfo['verification_script'] ? json_encode($dbInfo['verification_script']) : '',
                    ];
                }
                break;
            default:
                $detail = json_encode($recoverySource[0]['detail'], JSON_UNESCAPED_UNICODE);
                if ($dbType == $allDbType['ORACLE']) {
                    $detail = '';
                    if ($sourceRecoveryType == xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db')['SPECIFIC_TIMEPOINT']) {
                        if (
                            $recoveryType == $allRecoveryType['COVER'] ||
                            $recoveryType == $allRecoveryType['SPECIFY_FOLDER'] ||
                            $recoveryType == $allRecoveryType['FULL'] ||
                            $recoveryType == $allRecoveryType['INCOMPLETE']
                        ) {
                            $detail = $recoverySource[0]['detail'];
                            $detail['pfile_info']['file_content'] = v1_escape_wildcard($detail['pfile_info']['file_content']);
                            $detail['listener_info']['file_content'] = v1_escape_wildcard($detail['listener_info']['file_content']);
                            $detail['tnsnames_info']['file_content'] = v1_escape_wildcard($detail['tnsnames_info']['file_content']);
                            $detail['sqlnet_info']['file_content'] = v1_escape_wildcard($detail['sqlnet_info']['file_content']);
                            $detail['pfile_info']['recovery_flag'] = v1_parse_bool_to_flag($detail['pfile_info']['recovery_flag']);
                            $detail['listener_info']['recovery_flag'] = v1_parse_bool_to_flag($detail['listener_info']['recovery_flag']);
                            $detail['tnsnames_info']['recovery_flag'] = v1_parse_bool_to_flag($detail['tnsnames_info']['recovery_flag']);
                            $detail['sqlnet_info']['recovery_flag'] = v1_parse_bool_to_flag($detail['sqlnet_info']['recovery_flag']);
                            $detail['password_info']['recovery_flag'] = v1_parse_bool_to_flag($detail['password_info']['recovery_flag']);
                            if (
                                $recoveryType == $allRecoveryType['FULL'] ||
                                $recoveryType == $allRecoveryType['INCOMPLETE']
                            ) {  // 设置加密密码
                                foreach ($detail['recovery_timepoint_chain_info_list'] as $index => $chainInfo) {
                                    if ($chainInfo['encrypt_password']) {
                                        $detail['recovery_timepoint_chain_info_list'][$index]['encrypt_password'] = base64_decode($chainInfo['encrypt_password']);
                                    }
                                }
                            }
                            $detail = json_encode($detail);
                        } else if ($recoveryType == $allRecoveryType['EXPORT']) {
                            $detail = $recoverySource[0]['detail'];
                            foreach ($detail['recovery_timepoint_chain_info_list'] as $index => $chainInfo) {
                                if ($chainInfo['encrypt_password']) {
                                    $detail['recovery_timepoint_chain_info_list'][$index]['encrypt_password'] = base64_decode($chainInfo['encrypt_password']);
                                }
                            }
                            $detail = json_encode($detail);
                        }
                    }
                }
                $recoveryDbsInfo[] = [
                    'instance_name' => $recoverySource[0]['instance_name'],
                    'src_db_name' => $recoverySource[0]['old_db_name'],
                    'create_db_flag' => 0,
                    'new_db_name' => $recoverySource[0]['new_db_name'],
                    'new_data_file_path' => $recoverySource[0]['datafile_path'],
                    'new_log_file_path' => $recoverySource[0]['logfile_path'],
                    'log_rollback_time' => $recoverySource[0]['rollback_flag'] ? $recoverySource[0]['rollback_time'] : '',
                    'recovery_timepoint_uuid' => $recoverySource[0]['timepoint_uuid'],
                    'new_db_password' => $recoverySource[0]['new_db_password'],
                    'detail' => $detail,
                    'recovery_mode' => $recoveryType,
                    'modify_pfile_flag' => v1_parse_bool_to_flag(false),
                    'timepoint_password' => base64_decode($recoverySource[0]['encrypt_password']),
                    'log_restore_start_time' => $recoverySource[0]['log_restore_start_time'],
                    'log_restore_end_time' => $recoverySource[0]['log_restore_end_time'],
                    'open_db_flag' => v1_parse_bool_to_flag($recoverySource[0]['open_db_flag']),
                    'source_agent_uuid' => $recoverySource[0]['source_agent_uuid'],
                    'source_instance_name' => $recoverySource[0]['source_instance_name'],
                    'source_db_name' => $recoverySource[0]['source_db_name'],
                    'recovery_time' => $recoverySource[0]['recovery_time'],
                    'initialize_log_area' => v1_parse_bool_to_flag($recoverySource[0]['initialize_log_area']),
                    'latest_timepoint_uuid' => $recoverySource[0]['latest_timepoint_uuid'],
                    'db_uuid' => $recoverySource[0]['db_uuid'],
                    'error_code' => isset($dbInfoMap[$recoverySource[0]['db_uuid']]) ? $dbInfoMap[$recoverySource[0]['db_uuid']]['error_code'] : 0,
                    'before_task_script' => $recoverySource[0]['before_task_script'] ? json_encode($recoverySource[0]['before_task_script']) : '',
                    'after_task_script' => $recoverySource[0]['after_task_script'] ? json_encode($recoverySource[0]['after_task_script']) : '',
                    'verification_script' => $recoverySource[0]['verification_script'] ? json_encode($recoverySource[0]['verification_script']) : '',
                ];
                break;
        }
        return $recoveryDbsInfo;
    }

    /**
     * 修改数据库恢复任务
     * @param array $params 参数
     * @return array
     */
    public function editDbRecoveryJob(array $params): array
    {
        if ($params['recovery_source_type'] == xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db')['LATEST_TIMEPOINT']) {
            $expireDate = v1_license_get_expire_days();
            if ($expireDate['expire_days'] < 0) {
                return $this->sendResult(xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED'), false, 0);
            }
        }
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $jobInfo = $this->checkTaskExists($params['jobs_uuid'], [$allTaskType['DB_RECOVERY'], $allTaskType['DRILL']]);
        if (!$jobInfo) {
            return $this->sendResult(xphp_get_lang('WEB_DB_RECOVERY_TASK_NOT_EXISTS'), false, 0);
        }

        $taskName = htmlspecialchars_decode(v1_escape_wildcard($params['task_name']));
        // $sql = "SELECT * FROM bd_task WHERE task_name = ? AND task_uuid != ? ";
        // if ($this->dbSelect($sql, [$taskName, $params['jobs_uuid']])) {
        //     return $this->sendResult(
        //         sprintf(xphp_get_lang('WEB_COMMON_TASK_NAME_ALREADY_EXISTS'), $taskName),
        //         false,
        //         0
        //     );
        // }

        $msg = $this->buildDbRecoveryJobMsg($params, $taskName);
        $opName = 'BD_TASK_OP_RECOVERY_MODIFY';
        $operate = $this->pfOpcode->getOpcodeDes($opName);
        $editResult = $this->service()->editDbRecoveryJobService($msg, $params['node_uuid'] ?: '');
        if (!$editResult['result']) {
            $message = xphp_get_lang('UI_DB_MODIFY_RECOVERY_JOB') . xphp_get_lang('WEB_PUBLIC_FAILURE');
            $this->muOpResult(false, $operate, $editResult['msg'], 0, $editResult['errorCode']);
            return $this->sendResult('', false, 0);
        }

        $message = xphp_get_lang('UI_DB_MODIFY_RECOVERY_JOB') . xphp_get_lang('WEB_PUBLIC_SUCCESS');
        return $this->sendResult($message);
    }

    /**
     * 根据恢复时间获取Oracle恢复所使用的备份链
     * @param array $params 参数
     * @return array
     */
    public function getOracleTimepointChainRecoveryTime(array $params): array
    {
        $sourceInstanceInfoList = [];
        foreach ($params['source_instance_info_list'] as $sourceInstanceInfo) {
            $sourceInstanceInfoList[] = [
                'agent_uuid' => $sourceInstanceInfo['agent_uuid'],
                'instance_name' => $sourceInstanceInfo['instance_name'],
            ];
        }
        $appDbType = xphp_get_config('db', 'DB_RECOVERY_TYPE');
        $pathType = $params['path_type'];
        $opcodeName = 'DB_RECOVERY_TIMEPOINT_OP_GET_RECOVERY_TIMEPOINT_CHAIN_BY_TIME';
        $dbIncarnation = 0;
        $resetlogsTime = 0;
        $jobUuid = '';
        $recoveryTime = 0;
        $storageUuid = $params['storage_uuid'];
        if ($pathType == $appDbType['FULL']) {
            $opcodeName = 'DB_RECOVERY_TIMEPOINT_OP_GET_FULL_RECOVERY_TIMEPOINT_CHAIN';
        } else {
            $dbIncarnation = intval($params['db_incarnation']);
            $resetlogsTime = intval($params['resetlogs_time']);
            $jobUuid = $params['job_uuid'] ?: '';
            $recoveryTime = intval($params['recovery_time']);
        }
        $operate = (new DbProtectOpcode())->getOpcodeDes($opcodeName);
        $ret = $this->service()->getOracleTimepointChainRecoveryTimeService(
            $sourceInstanceInfoList,
            $dbIncarnation,
            $resetlogsTime,
            $jobUuid,
            $recoveryTime,
            $opcodeName,
            $storageUuid
        );
        if (!$ret['result']) {
            $this->muOpResult(false, $operate, $ret['msg'], 0, $ret['errorCode']);
            return $this->sendResult($ret['msg'], false, 0);
        }
        $timepointChain = $ret['msg']['timepoint_chain'];
        return $this->sendResult('', true, 200, [
            'timepoint_chain' => $timepointChain,
        ]);
    }

    /**
     * 获取SQL Server集群中活动节点的客户端信息
     * @param string $clusterUuid 集群UUID
     * @return array
     */
    public function getSqlserverClusterActiveNodeAgentInfo(string $clusterUuid): array
    {
        // 1. 获取集群的所有客户端
        $sql = "SELECT agent_uuid, app_listen_ip FROM bd_agent_app WHERE cluster_uuid = ? ";
        $appData = $this->dbSelect($sql, [$clusterUuid]);
        if (!is_array($appData) || !$appData) { // 没有获取到数据
            return $this->sendResult('', false, 0);
        }
        $appListenIp = $appData[0]['app_listen_ip'];
        $agentUuidList = array_column($appData, 'agent_uuid');
        // 2. 刷新客户端
        foreach ($agentUuidList as $agentUuid) {
            $this->service('\app\v1\resources\v0\service\Service')->refreshClientService($agentUuid);
        }
        // 3. 获取客户端的detail信息，从detail中获取ip信息，判断活动节点的客户端
        $agentUuids = "'" . implode("','", $agentUuidList) . "'";
        $sql = "SELECT agent_uuid, ip, detail FROM bd_agent WHERE agent_uuid IN ($agentUuids) ";
        $agentData = $this->dbSelect($sql);
        if (!is_array($agentData) || !$agentData) {
            return $this->sendResult('', false, 0);
        }
        $retAgent = [
            'active_agent_uuid' => $agentData[0]['agent_uuid'],
            'active_agent_ip' => $agentData[0]['ip'],
        ];
        $matchActiveNodeFlag = false;
        foreach ($agentData as $agentInfo) {
            if ($matchActiveNodeFlag) {
                break;
            }
            $detail = json_decode($agentInfo['detail'], true);
            if (isset($detail['nic_list']) && is_array($detail['nic_list'])) {
                foreach ($detail['nic_list'] as $nicInfo) {
                    if ($matchActiveNodeFlag) {
                        break;
                    }
                    if (isset($nicInfo['ip_set']) && is_array($nicInfo['ip_set'])) {
                        foreach ($nicInfo['ip_set'] as $ipSet) {
                            if ($ipSet['ip_addr'] == $appListenIp) {
                                $retAgent['active_agent_uuid'] = $agentInfo['agent_uuid'];
                                $retAgent['active_agent_ip'] = $agentInfo['ip'];
                                $matchActiveNodeFlag = true;
                                break;
                            }
                        }
                    }
                }
            }
        }
        return $this->sendResult('', true, 200, $retAgent);
    }
}
