<?php

namespace app\v1\db\v0\logic;

use app\v1\common\logic\Base;
use app\v1\common\logic\JobInfo;
use app\v1\system\v0\logic\Notice;

/**
 * note          数据库 之任务信息 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/3/13 14:22
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbJobInfo extends Base
{
    /**
     * 获取数据库备份/恢复/演练任务的信息
     * @param array $params 参数
     * @return array
     */
    public function getDbJobInfo(array $params): array
    {
        $jobInfoHandler = new \app\v1\job\v0\logic\JobInfo();
        $jobInfo = $jobInfoHandler->getJobInfo($params['jobs_uuid']);
        if (!$jobInfo) {
            return $this->sendResult(xphp_get_lang('WEB_ERROR_BD_TASK_NOT_EXIST_ERROR'), false, 0);
        }
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $allDbTypeDes = xphp_get_config('db', 'DB_TYPE_DES');
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $allTaskTypeDes = xphp_get_desc('Pf', 'TASKTYPEDES');
        $allRecoveryType = xphp_get_config('db', 'DB_RECOVERY_TYPE');
        $allRecoveryTypeDes = xphp_get_config('db', 'DB_RECOVERY_DES');

        $dbJobInfo = [
            'job_name' => $jobInfo['job_name'],
            'job_uuid' => $params['jobs_uuid'],
            'task_type' => $jobInfo['job_type'],
            'task_type_des' => $allTaskTypeDes[$jobInfo['job_type']] ?? 'Unknown',
            'task_status' => $jobInfo['job_status'],
            'task_status_des' => $jobInfo['job_status_des'],
            'task_stage' => $jobInfo['current_stage'],
            'task_stage_percent' => $jobInfo['stage_percent'],
            'task_stage_des' => $jobInfo['current_stage_value'],
            'task_orchestration_plan_flag' => $jobInfo['task_orchestration_plan_flag'],
            'total_size' => $jobInfo['total_object_size'] ? $jobInfo['total_size'] : xphp_get_config('app', 'NULLSPACE'),
            'current_size' => $jobInfo['total_object_completed_size'] ? $jobInfo['complate_size'] : xphp_get_config('app', 'NULLSPACE'),
            'speed' => $jobInfo['speed'],
            'speed_time' => $jobInfo['speed_time'],
            'progress' => $jobInfo['progress'],
            'total_progress' => $jobInfo['percent_progress'],
            'create_time' => $jobInfo['create_time'],
            'start_time' => $jobInfo['start_time'],
            'duration_time' => $jobInfo['interval_time'],
            'next_time' => $jobInfo['next_time'],
            'db_type' => $jobInfo['submodle_type'],
            'db_type_des' => $allDbTypeDes[$jobInfo['submodle_type']] ?? 'Unknown',
            'tape_strategy' => $jobInfo['tape_strategy'] ?: new \stdclass(),
        ];

        $networkFlag = (new JobInfo())->getDBNodeNetworkFlag($params['jobs_uuid'], $jobInfo['job_type']);
        if ($jobInfo['job_type'] == $allTaskType['DB_BACKUP']) {
            $dbBackupInfo = (new DbBackUp())->getDbBackupJob($params['jobs_uuid'])['data'];
            // 限速策略
            $dbBackupInfo['speed_strategy']['text'] = $jobInfo['speed_limit']['text'];
            // 备份目标
            $dbBackupInfo['backup_target']['node_info'] = $jobInfo['storage_info']['node'];
            $dbBackupInfo['backup_target']['storage_info'] = $jobInfo['storage_info']['storage'];
            $dbBackupInfo['backup_target']['storage_pool_nickname'] = $jobInfo['storage_info']['storage_pool_nickname'] ?: '';
            $dbBackupInfo['backup_target']['node_pool_nickname'] = $jobInfo['storage_info']['node_pool_nickname'] ?: '';
            // 传输网络
            $dbBackupInfo['transport_strategy']['network_pool_nickname'] = $jobInfo['transport_strategy']['network_pool_nickname'] ?: '';
            $dbBackupInfo['transport_strategy']['network_name'] = $jobInfo['transport_strategy']['network'];
            $dbBackupInfo['transport_strategy']['network_flag'] = $networkFlag;

            $dbJobInfo['backup_info'] = $dbBackupInfo;
        } else {
            $dbRecoveryInfo = (new DbRecover())->getDbRecoveryJob($params['jobs_uuid'])['data'];
            // 限速策略
            $dbRecoveryInfo['speed_strategy']['text'] = $jobInfo['speed_limit']['text'];
            // 传输网络
            $dbRecoveryInfo['transfer_strategy']['network_pool_nickname'] = $jobInfo['transport_strategy']['network_pool_nickname'] ?: '';
            $dbRecoveryInfo['transfer_strategy']['network_name'] = $jobInfo['transport_strategy']['network'];
            $dbRecoveryInfo['transfer_strategy']['network_flag'] = $networkFlag;
            // 备份点信息
            $timepointList = $this->getDbRecoveryTimepointList($params['jobs_uuid']);
            $sourceStorageType = 0;
            foreach ($timepointList as $timepointInfo) {
                $sourceStorageType = intval($timepointInfo['storage_type']);
            }
            $dbRecoveryInfo['source_storage_type'] = $sourceStorageType;
            // 恢复方式
            $dbRecoveryInfo['recovery_info']['recovery_type_des'] = $allRecoveryTypeDes[$dbRecoveryInfo['recovery_info']['recovery_type']] ?? 'Unknown';
            if (!in_array($jobInfo['submodle_type'], [$allDbType['SQLSERVER'], $allDbType['SAPHANA']])) {
                if ($jobInfo['recovery_type'] == $allRecoveryType['COVER']) {
                    $dbRecoveryInfo['recovery_info']['recovery_type_des'] = xphp_get_lang('UI_DB_ORIGINAL_INSTANCE_COVERAGE_RECOVERY');
                    if ($allTaskType['DRILL'] == $jobInfo['job_type']) {
                        $dbRecoveryInfo['recovery_info']['recovery_type_des'] = xphp_get_lang('UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY');
                    }
                } elseif ($jobInfo['recovery_type'] == $allRecoveryType['CREATE']) {
                    $dbRecoveryInfo['recovery_info']['recovery_type_des'] = xphp_get_lang('UI_DB_ADD_NEW_INSTANCE_RECOVERY');
                }
            }

            $dbJobInfo['recovery_info'] = $dbRecoveryInfo;
        }
        return $this->sendResult('', true, 200, $dbJobInfo);
    }

    /**
     * 获取数据库备份/恢复/演练任务的对象运行信息
     * @param string $jobsUuid 备份任务uuid
     * @param array  $param    参数
     * @return array
     */
    public function getDbJobObjectInfo(string $jobsUuid, array $param): array
    {
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $allVmTaskStatusDes = xphp_get_desc('Vm', 'VmTaskStatus');
        $jobInfo = $this->checkTaskExists($jobsUuid, [
            $allTaskType['DB_BACKUP'],
            $allTaskType['DB_RECOVERY'],
            $allTaskType['DRILL']
        ]);
        if (!$jobInfo) {
            return $this->sendResult(xphp_get_lang('WEB_ERROR_BD_TASK_NOT_EXIST_ERROR'), false, 0);
        }
        // 查询数据库备份、恢复信息
        $sql = "SELECT dl.db_name, dl.instance_name, dl.task_status AS db_status, dl.dir_path, dl.db_uuid,
                    dl.error_code, dl.backup_mode, dl.agent_uuid, dl.detail, dl.recovery_mode,
                    dl.new_db_name, dl.data_file_path, dl.log_file_path, dl.log_rollback_time,
                    dl.recovery_time, dl.initialize_log_area,
                    dl.transport_size, dl.write_size, dl.timepoint_uuid,
                    dl.source_db_name, dl.source_instance_name, dl.source_agent_uuid,
                    dl.before_task_script, dl.after_task_script, dl.verification_script,
                    bri.current_object_transport_size, bri.current_object_write_size, bri.speed, bri.speed_time,
                    dt.db_type, dt.multi_task_flag,
                    bt.task_status, bt.task_name, bt.task_type
                FROM db_list dl
                    INNER JOIN bd_running_info bri ON dl.task_uuid = bri.task_uuid
                    INNER JOIN db_task dt ON dl.task_uuid=dt.task_uuid
                    INNER JOIN bd_task bt ON dl.task_uuid=bt.task_uuid
                WHERE dl.task_uuid = ? ";
        $data = (array) $this->dbSelect($sql, [$jobsUuid]);

        // 查询客户端信息,bd_task_agent_list有agent_uuid和cluster_uuid,分别连接ba_agent和bd_agent_app
        $dbType = (int) $data[0]['db_type'];
        $sql = "SELECT ba.ip, ba.agent_name, ba.hostname, ba.agent_uuid,
                    baa.cluster_name, baa.cluster_flag, baa.cluster_uuid, baa.agent_uuid AS cluster_agent_uuid,
                    baa.app_name, baa.app_detail, baa.app_type
                FROM bd_task_agent_list btal
                    lEFT JOIN bd_agent_app baa ON btal.agent_uuid = baa.agent_uuid
                    LEFT JOIN bd_agent ba ON btal.agent_uuid=ba.agent_uuid
                WHERE btal.task_uuid = ? AND baa.app_type = ? ";
        $agentList = (array) $this->dbSelect($sql, [$jobsUuid, $dbType]);

        // 查询这个任务的集群信息
        $clusterUuidList = array_column($agentList, 'cluster_uuid');
        $clusterUuidList = array_filter($clusterUuidList);
        $clusterUuidList = array_values(array_unique($clusterUuidList));
        $clusterAgentList = [];
        if ($clusterUuidList) {
            $clusterUuids = "'" . implode("', '", $clusterUuidList) . "'";
            $sql = "SELECT baa.cluster_uuid, baa.cluster_name, baa.cluster_type, baa.cluster_service_ip, baa.app_detail,
                        baa.app_name, baa.app_type, baa.app_service_name,
                        ba.ip, ba.agent_name, ba.hostname, ba.agent_uuid
                    FROM bd_agent_app baa
                        INNER JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                    WHERE baa.cluster_uuid IN ($clusterUuids) ";
            $clusterAgentList = (array) $this->dbSelect($sql);
        }

        $timepointList = $this->getDbRecoveryTimepointList($jobsUuid);

        // 查询演练源信息
        $sourceAgentList = [];
        $sourceClusterAgentList = [];
        if ($jobInfo['task_type'] == $allTaskType['DRILL']) {  // 演练任务
            $sql = "SELECT ba.ip, ba.agent_name, ba.hostname, ba.agent_uuid,
                    baa.cluster_name, baa.cluster_flag, baa.cluster_uuid, baa.agent_uuid AS cluster_agent_uuid,
                    baa.app_name, baa.app_detail, baa.app_type
                FROM bd_agent_app baa
                    LEFT JOIN bd_agent ba ON baa.agent_uuid=ba.agent_uuid
                WHERE baa.app_type = ? ";
            $sqlParams = [$dbType];
            $subSqlList = [];
            foreach ($data as $row) {
                $subSqlList[] = " (baa.agent_uuid = ? AND baa.app_name = ? ) ";
                $sqlParams[] = $row['source_agent_uuid'];
                $sqlParams[] = $row['source_instance_name'];
            }
            $sql .= " AND ( " . implode(" OR ", $subSqlList) . ") ";
            $sourceAgentList = (array) $this->dbSelect($sql, $sqlParams);

            // 查询这个任务的集群信息
            $sourceClusterUuidList = array_column($sourceAgentList, 'cluster_uuid');
            $sourceClusterUuidList = array_filter($sourceClusterUuidList);
            $sourceClusterUuidList = array_values(array_unique($sourceClusterUuidList));
            if ($sourceClusterUuidList) {
                $sourceClusterUuids = "'" . implode("', '", $sourceClusterUuidList) . "'";
                $sql = "SELECT baa.cluster_uuid, baa.cluster_name, baa.cluster_type, baa.cluster_service_ip, baa.app_detail,
                        baa.app_name, baa.app_type, baa.app_service_name,
                        ba.ip, ba.agent_name, ba.hostname, ba.agent_uuid
                    FROM bd_agent_app baa
                        INNER JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                    WHERE baa.cluster_uuid IN ($sourceClusterUuids) ";
                $sourceClusterAgentList = (array) $this->dbSelect($sql);
            }
        }

        $dbJobInfo = [];
        $num = 1;
        foreach ($data as $row) {
            $agentInfo = $this->getDbRunningAgentInfo($row['agent_uuid'], $row['instance_name'], $agentList);
            $clusterInfo = $this->getDbRunningClusterInfo($row['agent_uuid'], $row['instance_name'], $clusterAgentList);
            $timepointInfo = $this->getBackupTimePointInfo(
                (int) $row['task_type'],
                $row['timepoint_uuid'],
                $timepointList
            );
            $showDbName = $this->getDbRunningDbName(
                $row['db_name'],
                (int) $row['task_type'],
                (int) $row['db_type'],
                $clusterInfo,
                $timepointInfo
            );
            $showHostName = $this->getDbRunningHostName(
                (int) $row['db_type'],
                $clusterInfo,
                $agentInfo
            );
            if (isset($param['search']) && $param['search']) {
                $dbNameFlag = strpos($showDbName, $param['search']);
                $filterHostName = str_replace('<br>', '', $showHostName);
                $nameFlag = strpos($filterHostName, $param['search']);
                if ($dbNameFlag === false && $nameFlag === false) {
                    continue;
                }
            }

            // 演练任务
            $sourceAgentInfo = $this->getDbRunningAgentInfo($row['source_agent_uuid'], $row['source_instance_name'], $sourceAgentList);
            $sourceClusterInfo = $this->getDbRunningClusterInfo($row['source_agent_uuid'], $row['source_instance_name'], $sourceClusterAgentList);

            $logRollbackTime = '';
            if ($row['log_rollback_time'] && $row['log_rollback_time'] != '0000-00-00 00:00:00') {
                $logRollbackTime = $row['log_rollback_time'];
            }

            $dbJobInfo[] = [
                'num' => $num++,
                'job_name' => $row['task_name'],
                'task_type' => $row['task_type'],
                'task_status' => $row['task_status'],
                'multi_task_flag' => v1_parse_flag_to_bool($row['multi_task_flag']),
                'db_type' => $dbType,
                'instance_name' => $row['instance_name'],
                'db_name' => $row['db_name'],
                'new_db_name' => $row['new_db_name'],
                'data_file_path' => $row['data_file_path'],
                'log_file_path' => $row['log_file_path'],
                'log_rollback_time' => $logRollbackTime,
                'recovery_time' => $row['recovery_time'],
                'initialize_log_area' => v1_parse_flag_to_bool($row['initialize_log_area']),
                'show_db_name' => $showDbName,
                'show_host_name' => $showHostName,
                'backup_mode' => (int) $row['backup_mode'],  // 备份模式【完备 增量 差异 日志/归档日志】
                'recovery_mode' => intval($row['recovery_mode']),
                'transport_size' => $this->getDbRunningSize(
                    (int) $row['task_status'],
                    (int) $row['db_status'],
                    (int) $row['transport_size'],
                    (int) $row['current_object_transport_size']
                ),
                'write_size' => $this->getDbRunningSize(
                    (int) $row['task_status'],
                    (int) $row['db_status'],
                    (int) $row['write_size'],
                    (int) $row['current_object_write_size']
                ),
                'speed' => $this->getDbRunningSpeed(
                    (int) $row['db_status'],
                    (int) $row['speed'],
                    (int) $row['speed_time']
                ),
                'db_status' => (int) $row['db_status'],
                'db_status_des' => xphp_get_lang($allVmTaskStatusDes[$row['db_status']] ?? 'WEB_PLATFORM_PUBLIC_UNKNOWN'),
                'db_uuid' => $row['db_uuid'],
                'dir_path' => $row['dir_path'],
                'error_code' => (int) $row['error_code'],
                'error_msg' => $this->getDbRunningError($row['db_status'], $row['error_code']),
                'agent_info' => $agentInfo,
                'cluster_info' => $clusterInfo,
                'timepoint_info' => $timepointInfo,
                'skip_datafiles_bad_block_info' => $this->getDbJobSkipDatafilesBadBlockInfo(
                    $dbType,
                    $row['task_type'],
                    $row['detail'],
                    v1_parse_flag_to_bool($row['multi_task_flag'])
                ),
                'before_task_script' => $this->formatScriptConfig(json_decode($row['before_task_script'], true) ?: []),
                'after_task_script' => $this->formatScriptConfig(json_decode($row['after_task_script'], true) ?: []),
                'verification_script' => $this->formatScriptConfig(json_decode($row['verification_script'], true) ?: []),
                'source_db_name' => $row['source_db_name'],
                'source_instance_name' => $row['source_instance_name'],
                'source_agent_info' => $sourceAgentInfo,
                'source_cluster_info' => $sourceClusterInfo,

            ];
        }
        return $this->sendResult('', true, 200, [
            'rows' => $dbJobInfo,
            'total' => count($dbJobInfo),
        ]);
    }

    /**
     * 获取数据库任务运行信息的恢复时间点列表
     * @param string $jobsUuid 任务uuid
     * @return array
     */
    private function getDbRecoveryTimepointList(string $jobsUuid): array
    {
        // 查询数据库备份、恢复信息
        $sql = "SELECT timepoint_uuid FROM db_list WHERE task_uuid = ? ";
        $data = (array) $this->dbSelect($sql, [$jobsUuid]);

        // 查询备份时间信息
        $timepointUuids = "'" . implode("', '", array_column($data, 'timepoint_uuid')) . "'";
        $sql = "SELECT bbt.timepoint, bbt.timepoint_uuid, bbt.backup_mode, bbt.storage_uuid,
                    dbt.agent_uuid, dbt.agent_ip, dbt.dir_path, dbt.cluster_uuid,
                    dbt.cluster_name, dbt.db_name, dbt.instance_name,
                    bsr.storage_type
                FROM db_backup_timepoint dbt
                    INNER JOIN bd_backup_timepoint bbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                    INNER JOIN bd_storage_resource bsr ON bsr.storage_uuid = bbt.storage_uuid
                WHERE bbt.timepoint_uuid IN ($timepointUuids)";
        return (array) $this->dbSelect($sql);
    }

    /**
     * 格式化脚本配置
     * @param array $scriptConfig 脚本配置
     * @return array
     */
    private function formatScriptConfig(array $scriptConfig): array
    {
        if (!$scriptConfig) {
            return [];
        }
        foreach ($scriptConfig as $key => $item) {
            $scriptConfig[$key]['script_method'] = intval($item['script_method']);
            $scriptConfig[$key]['script_type'] = intval($item['script_type']);
        }
        return $scriptConfig;
    }

    /**
     * 获取数据库任务运行信息的客户端信息
     * @param string $agentUuid    客户端uuid
     * @param string $instanceName 实例名
     * @param array  $agentList    客户端列表
     * @return array
     */
    private function getDbRunningAgentInfo(string $agentUuid, string $instanceName, array $agentList): array
    {
        $retAgentInfo = [
            'agent_uuid' => '',
            'agent_name' => '',
            'hostname' => '',
            'ip' => '',
            'node_role' => '',
        ];
        foreach ($agentList as $agent) {
            if ($agentUuid == $agent['agent_uuid'] && $agent['app_name'] == $instanceName) {
                $nodeRole = '';
                if ($agent['app_type'] == xphp_get_config('db', 'DB_TYPE')['TIDB']) {
                    $appDetail = $agent['app_detail'];
                    $nodeRole = $appDetail['node_role'] ?? '';
                }
                return [
                    'agent_uuid' => $agent['agent_uuid'],
                    'agent_name' => $agent['agent_name'],
                    'hostname' => $agent['hostname'],
                    'ip' => $agent['ip'],
                    'node_role' => $nodeRole,
                ];
            }
        }
        return $retAgentInfo;
    }

    /**
     * @param string $agentUuid        客户端uuid
     * @param string $instanceName     实例名
     * @param array  $clusterAgentList 集群客户端列表
     * @return array
     */
    private function getDbRunningClusterInfo(string $agentUuid, string $instanceName, array $clusterAgentList): array
    {
        $retClusterAgentInfo = [
            'cluster_uuid' => '',
            'cluster_name' => '',
            'cluster_service_ip' => '',
            'app_service_name' => '',
            'cluster_type' => 0,
            'cluster_agent_list' => [],
        ];
        foreach ($clusterAgentList as $clusterAgent) {
            if ($clusterAgent['agent_uuid'] == $agentUuid && $clusterAgent['app_name'] == $instanceName) {
                $retClusterAgentInfo['cluster_uuid'] = $clusterAgent['cluster_uuid'];
                $retClusterAgentInfo['cluster_name'] = $clusterAgent['cluster_name'];
                $retClusterAgentInfo['cluster_service_ip'] = $clusterAgent['cluster_service_ip'];
                $retClusterAgentInfo['app_service_name'] = $clusterAgent['app_service_name'];
                $retClusterAgentInfo['cluster_type'] = $clusterAgent['cluster_type'];
                break;
            }
        }
        foreach ($clusterAgentList as $clusterAgent) {
            if ($clusterAgent['cluster_uuid'] == $retClusterAgentInfo['cluster_uuid']) {
                $nodeRole = '';
                if ($clusterAgent['app_type'] == xphp_get_config('db', 'DB_TYPE')['TIDB']) {
                    $appDetail = $clusterAgent['app_detail'];
                    $nodeRole = $appDetail['node_role'] ?? '';
                }
                $retClusterAgentInfo['cluster_agent_list'][] = [
                    'agent_uuid' => $clusterAgent['agent_uuid'],
                    'agent_name' => $clusterAgent['agent_name'],
                    'hostname' => $clusterAgent['hostname'],
                    'app_name' => $clusterAgent['app_name'],
                    'ip' => $clusterAgent['ip'],
                    'node_role' => $nodeRole,
                ];
            }
        }
        return $retClusterAgentInfo;
    }

    /**
     * 获取数据库任务运行的数据库名
     * @param string $dbName        数据库名
     * @param int    $taskType      任务类型
     * @param int    $dbType        数据库类型
     * @param array  $clusterInfo   集群信息
     * @param array  $timepointInfo 时间点信息
     * @return string
     */
    private function getDbRunningDbName(
        string $dbName,
        int $taskType,
        int $dbType,
        array $clusterInfo,
        array $timepointInfo
    ): string {
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $allowClusterDbTypeList = [
            $allDbType['DM'],
            $allDbType['POSTGRE'],
            $allDbType['ANTDB'],
            $allDbType['KINGBASE'],
            $allDbType['UXDB'],
            $allDbType['HIGHGO'],
            $allDbType['OPENGAUSS'],
            $allDbType['VASTBASE'],
            $allDbType['MONGODB'],
        ];
        if ($taskType == $allTaskType['DB_BACKUP']) {
            if ($clusterInfo['cluster_uuid']) {
                if (in_array($dbType, $allowClusterDbTypeList)) {
                    $dbName = $clusterInfo['cluster_name'];
                }
            }
        } elseif ($taskType == $allTaskType['DB_RECOVERY']) {
            if ($timepointInfo['cluster_flag']) {
                if (in_array($dbType, $allowClusterDbTypeList)) {
                    $dbName = $timepointInfo['cluster_name'];
                }
            }
        } elseif ($taskType == $allTaskType['DRILL']) {
            if ($clusterInfo['cluster_uuid']) {
                if (in_array($dbType, $allowClusterDbTypeList)) {
                    $dbName = $clusterInfo['cluster_name'];
                }
            }
        }
        return $dbName;
    }

    /**
     * 获取数据库任务运行的主机名
     * @param int   $dbType      数据库类型
     * @param array $clusterInfo 集群信息
     * @param array $agentInfo   客户端信息
     * @return string
     */
    private function getDbRunningHostName(int $dbType, array $clusterInfo, array $agentInfo): string
    {
        $showHostName = '';
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        if ($clusterInfo['cluster_uuid']) {
            if ($dbType == $allDbType['ORACLE']) {
                foreach ($clusterInfo['cluster_agent_list'] as $clusterAgentInfo) {
                    if ($clusterAgentInfo['agent_name'] && $clusterAgentInfo['agent_name'] !== $clusterAgentInfo['ip']) {
                        $showHostName .= $clusterAgentInfo['agent_name'] . '(' . $clusterAgentInfo['ip'] . ' ' . $clusterAgentInfo['app_name'] . ')<br>';
                    } else {
                        $showHostName .= $clusterAgentInfo['hostname'] . '(' . $clusterAgentInfo['ip'] . ' ' . $clusterAgentInfo['app_name'] . ')<br>';
                    }
                }
            } elseif ($dbType == $allDbType['TIDB']) {
                foreach ($clusterInfo['cluster_agent_list'] as $clusterAgentInfo) {
                    if ($clusterAgentInfo['agent_name'] && $clusterAgentInfo['agent_name'] !== $clusterAgentInfo['ip']) {
                        $showHostName .= $clusterAgentInfo['agent_name'] . '(' . $clusterAgentInfo['ip'] . ' ' . $clusterAgentInfo['node_role'] . ')<br>';
                    } else {
                        $showHostName .= $clusterAgentInfo['hostname'] . '(' . $clusterAgentInfo['ip'] . ' ' . $clusterAgentInfo['node_role'] . ')<br>';
                    }
                }
            } else {
                foreach ($clusterInfo['cluster_agent_list'] as $clusterAgentInfo) {
                    if ($clusterAgentInfo['agent_name'] && $clusterAgentInfo['agent_name'] !== $clusterAgentInfo['ip']) {
                        $showHostName .= $clusterAgentInfo['agent_name'] . '(' . $clusterAgentInfo['ip'] . ')<br>';
                    } else {
                        $showHostName .= $clusterAgentInfo['hostname'] . '(' . $clusterAgentInfo['ip'] . ')<br>';
                    }
                }
            }
        } else {
            $showHostName = $agentInfo['hostname'] . '(' . $agentInfo['ip'] . ')';
            if ($agentInfo['agent_name'] && $agentInfo['agent_name'] !== $agentInfo['ip']) {
                $showHostName = $agentInfo['agent_name'] . '(' . $agentInfo['ip'] . ')';
            }
        }
        return $showHostName;
    }

    /**
     * @param int    $dbType      数据库类型
     * @param int    $taskType    任务类型
     * @param string $detail      详情
     * @param bool   $subTaskFlag 是否为子任务
     * @return array
     */
    private function getDbJobSkipDatafilesBadBlockInfo(int $dbType, int $taskType, string $detail, bool $subTaskFlag): array
    {
        if ($dbType != xphp_get_config('db', 'DB_TYPE')['ORACLE']) {
            return [];
        }
        if ($taskType != xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']) {
            return [];
        }
        if ($subTaskFlag) {
            return [];
        }
        $detail = json_decode($detail, true);
        return $detail['skip_datafiles_bad_block_info'] ?? [];
    }

    /**
     * 获取数据库运行的大小
     * @param int $taskStatus    任务运行状态
     * @param int $dbStatus      数据库运行状态
     * @param int $transportSize 全部传输大小
     * @param int $currentSize   当前传输的大小
     * @return string
     */
    private function getDbRunningSize(int $taskStatus, int $dbStatus, int $transportSize, int $currentSize): int
    {
        $allTaskStatus = xphp_get_config('task', 'TASKSTATUS');
        $allDbTaskStatus = xphp_get_config('db', 'DB_TASK_STATUS');
        if ($taskStatus == $allTaskStatus['RUNNING'] || $taskStatus == $allTaskStatus['ABNORMAL']) {
            if ($dbStatus == $allDbTaskStatus['RUNNING']) {
                return $currentSize;
            } elseif ($dbStatus != $allDbTaskStatus['WAITTING']) {
                return $transportSize;
            }
        }
        return 0;
    }

    /**
     * 获取数据库运行的速度
     * @param int $dbStatus  数据库运行状态
     * @param int $speed     运行速度
     * @param int $speedTime 运行速度的时间
     * @return int
     */
    private function getDbRunningSpeed(int $dbStatus, int $speed, int $speedTime): int
    {
        $allTaskStatus = xphp_get_config('task', 'TASKSTATUS');
        if (time() - $speedTime > 12) {  // 超过12秒不更新就显示0
            return 0;
        }
        if ($dbStatus == $allTaskStatus['RUNNING'] || $dbStatus == $allTaskStatus['ABNORMAL']) {
            return $speed;
        }
        return 0;
    }

    /**
     * 获取数据库运行的错误信息
     * @param int $dbStatus  数据库状态
     * @param int $errorCode 错误码
     * @return string
     */
    private function getDbRunningError(int $dbStatus, int $errorCode): string
    {
        /**
         * // db task status in db_list table
         * enum DbTaskStatus
         * {
         *     DB_TASK_STATUS_UNKNOWN = 0,      //unknown task status
         *     DB_TASK_STATUS_WAITTING,         //task is waitting for running
         *     DB_TASK_STATUS_RUNNING,          //task is running
         *     DB_TASK_STATUS_FINISH,           //task is finish
         *     DB_TASK_STATUS_ERROR,            //db task status error
         * };
         */
        $allTaskStatus = xphp_get_config('task', 'TASKSTATUS');
        if (
            $dbStatus == $allTaskStatus['ERROR'] ||
            $dbStatus == $allTaskStatus['ABNORMAL'] ||
            $dbStatus == $allTaskStatus['NETWORK_FAULT']
        ) {
            $error = xphp_get_config('error');
            return $error['errorCodeDes'][$error['errorCode'][$errorCode]];
        }
        return '';
    }

    /**
     * 获取备份点信息
     * @param int    $taskType      任务类别
     * @param string $timepointUuid 备份时间点uuid
     * @param array  $timepointList 备份点列表
     * @return array
     */
    private function getBackupTimePointInfo(int $taskType, string $timepointUuid, array $timepointList): array
    {
        $retTimePointInfo = [
            'timepoint_uuid' => '',
            'timepoint' => '',
            'backup_mode' => 0,
            'agent_uuid' => '',
            'agent_ip' => '',
            'dir_path' => '',
            'cluster_flag' => false,
            'cluster_uuid' => '',
            'cluster_name' => '',
            'db_name' => '',
            'instance_name' => '',
        ];
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        if (
            ($allTaskType['DB_RECOVERY'] != $taskType && $allTaskType['DRILL'] != $taskType) ||
            !$timepointUuid
        ) {  // 如果不是数据库恢复任务或者备份时间点uuid为空，返回空
            return $retTimePointInfo;
        }
        foreach ($timepointList as $timepoint) {
            if ($timepoint['timepoint_uuid'] == $timepointUuid) {
                return [
                    'timepoint_uuid' => $timepointUuid,
                    'timepoint' => $timepoint['timepoint'],
                    'backup_mode' => (int) $timepoint['backup_mode'],
                    'agent_uuid' => $timepoint['agent_uuid'],
                    'agent_ip' => $timepoint['agent_ip'],
                    'dir_path' => $timepoint['dir_path'],
                    'cluster_flag' => !!$timepoint['cluster_uuid'],
                    'cluster_uuid' => $timepoint['cluster_uuid'],
                    'cluster_name' => $timepoint['cluster_name'],
                    'db_name' => $timepoint['db_name'],
                    'instance_name' => $timepoint['instance_name'],
                ];
            }
        }
        return $retTimePointInfo;
    }

    /**
     * 获取报告标题
     * @param string $reportName 任务名称
     * @param int    $dbTypeDes  数据库类型
     * @param string $startTime  开始时间
     * @param string $usage      用途【pdf email page】
     * @return string
     */
    private function getReportTitle(string $reportName, string $dbTypeDes, string $startTime, string $usage = 'pdf')
    {
        $recoveryTime = xphp_get_lang('UI_DB_RECOVERY_RECOVERY_TIME') . ':' . $startTime;
        $sqlValidateImg = xphp_get_config('img_base64', 'SQL_VALIDATE', 'db');
        if ($usage == 'email') {
            return <<<EOF
	    <!-- 标题 -->
	    <div style="margin-bottom: 32px">
	    	<div style="height: 58px">
                <div style="width: 58px; height: 58px; padding-right: 12px; float: left">
	    		    <img width="58px" height="58px" src="{$sqlValidateImg['logo_58x58']}" alt="logo">
                </div>
	    	    <div style="width: calc(100% - 82px); height: 58px; padding-left: 12px; float: left">
                    <div style="height: 30px; margin-bottom: 4px; display: flex; flex-direction: row; justify-content: space-between;align-items: center;">
	    		        <span style="width: 100%; font-size: 24px; font-weight: bold">$reportName</span>
                        <div style="width: calc(100% - 200px); height: 24px; text-align: right">
                            <img style="position: relative; top: -2px;" src="{$sqlValidateImg['time_icon']}" width="18px" height="18px" alt="time" />
                            <span style="font-size: 16px; color: #4A5259;">$recoveryTime</span>
                        </div>
                    </div>
                    <div style="height: 24px;">
                        <div style="width: 200px; height: 24px; float: left">
	    		            <span style="font-size: 16px; color: #4A5259; height: 24px; line-height: 24px">$dbTypeDes</span>
                        </div>
                    </div>
	    	    </div>
	    	</div>
	    </div>
	    <!-- 分割线 -->
	    <div style="height: 4px; background-color: #0FBF98; clear: both"></div>
EOF;
        } else {
            $titleWidth = 'calc(100% - 82px)';
            $dateHtml = <<<EOF
            <div style="width: 100%; height: 24px; clear: both;">
                <div style="width: 254px; float: right">
                    <div style="float: right; width: 20px; height: 20px; background-position: 0 3px; background-image: url('{$sqlValidateImg['time_icon']}'); background-repeat: no-repeat;">
                        <span style="font-size: 16px; color: #4A5259; top: 100px">$recoveryTime</span>
                    </div>
                </div>
                <div style="width: calc(100% - 254px); height: 1px; float: right;"></div>
            </div>
EOF;
            if ($usage == 'page') {
                $titleWidth = 'calc(100% - 58px)';
                $dateHtml = <<<EOF
                <img style="position: relative; top: -6px; margin-right: 8px;" src="{$sqlValidateImg['time_icon']}" width="18px" height="18px" alt="time" />
                <span style="font-size: 16px; color: #4A5259; top: 100px; line-height: 24px;">$recoveryTime</span>
EOF;
            }
            return <<<EOF
	    <!-- 标题 -->
	    <div style="margin-bottom: 32px">
	    	<div style="height: 58px">
                <div style="width: 58px; height: 58px; padding-right: 12px; float: left">
	    		    <img width="58px" height="58px" src="{$sqlValidateImg['logo_58x58']}" alt="logo">
                </div>
	    	    <div style="width: {$titleWidth}; height: 58px; padding-left: 12px; float: left">
                    <div style="height: 30px; margin-bottom: 4px">
	    		        <span style="width: 100%; font-size: 24px; font-weight: bold">$reportName</span>
                    </div>
                    <div style="height: 24px;">
                        <div style="width: 200px; height: 24px; float: left">
	    		            <span style="font-size: 16px; color: #4A5259; height: 24px; line-height: 24px">$dbTypeDes</span>
                        </div>
                        <div style="width: calc(100% - 200px); height: 24px; float: left; text-align: right; font-size: 0; line-height: 24px;">
                            {$dateHtml}
                        </div>
                    </div>
	    	    </div>
	    	</div>
	    </div>
	    <!-- 分割线 -->
	    <div style="height: 4px; background-color: #0FBF98; clear: both"></div>
EOF;
        }
    }

    /**
     * 获取报告基本信息
     * @param array  $record 数据库记录
     * @param int    $dbType 数据库类型
     * @param string $usage  用途【pdf email page】
     * @return string
     */
    private function getReportBasicJobInfo(array $record, int $dbType, string $usage = 'pdf'): string
    {
        $baseInfoDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_BASE_INFO');
        $recoveryTypeDes = xphp_get_lang('UI_RECOVERY_TYPE');
        $backupTimepointDes = xphp_get_lang('UI_RECOVERY_BACKUP_POINT');
        $sourcePathDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_SOURCE_PATH');
        $recoveryPathDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_PATH');
        $recoverySizeDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_SIZE');
        $recoverySpeedDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_SPEED');
        $startTimeDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_START_TIME');
        $endTimeDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_END_TIME');
        $recoveryDurationDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_DURATION');

        $allDbType = xphp_get_config('db', 'DB_TYPE');

        $recoveryType = xphp_get_desc('Db', 'DB_RECOVERY_LEVEL_DES')[$record['recovery_level']];
        if ($record['recovery_level'] == xphp_get_config('db', 'DB_RECOVERY_TYPE')['COVER']) {
            $recoveryType = xphp_get_lang('UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY');  // 演练任务是覆盖恢复
        } elseif ($record['recovery_level'] == xphp_get_config('db', 'DB_RECOVERY_TYPE')['CREATE']) {
            switch ($dbType) {
                case $allDbType['KINGBASE']:
                case $allDbType['POSTGRE']:
                case $allDbType['UXDB']:
                case $allDbType['HIGHGO']:
                case $allDbType['OPENGAUSS']:
                case $allDbType['ANTDB']:
                case $allDbType['VASTBASE']:
                    $recoveryType = xphp_get_lang('UI_DB_ADD_NEW_INSTANCE_RECOVERY');
                    break;
                default:
                    $recoveryType = xphp_get_lang('UI_DB_ADD_NEW_DATABASE_RECOVERY');
                    break;
            }
        }
        $backupMode = xphp_get_desc('Db', 'DB_BACKUP_MODE_DES')[$record['mode']];
        if ($record['mode'] == xphp_get_config('db', 'BACKUP_MODE', 'db')['log_backup']) {  // 日志备份
            switch ($record['db_type']) {
                case $allDbType['ORACLE']:
                case $allDbType['DM']:
                case $allDbType['POSTGRE']:
                case $allDbType['KINGBASE']:
                case $allDbType['UXDB']:
                case $allDbType['HIGHGO']:
                case $allDbType['OPENGAUSS']:
                case $allDbType['VASTBASE']:
                case $allDbType['ANTDB']:
                    $backupMode = xphp_get_lang('UI_BACKUP_ARCHIVE_LOG');
                    break;
                default:
                    break;
            }
        }
        $backupTimepoint = $record['timepoint'] . '(' . $backupMode . ')';
        $sourcePath = $record['dir_path'];
        $recoveryPath = $record['des_dir_path'];
        $recoverySize = v1_calsize($record['total_size'], true);
        $recoverySpeed = v1_calspeed($record['transfer_speed']);
        $startTime = $record['start_transfer_time'];
        $endTime = $record['end_transfer_time'];
        $duration = strtotime($record['end_transfer_time']) - strtotime($record['start_transfer_time']);
        $recoveryDuration = v1_sec_to_time($duration);

        // html定义
        $titleHtml = <<<EOF
        <div style="margin-bottom: 24px; height: 20px; width: 100%;">
            <div style="width: 30px; height: 24px; border-left: 4px solid #0FBF98; float: left;"></div>
            <div style="width: 100px; height: 24px; float: left;">
                <span style="color: #333; font-size: 16px; line-height: 24px; font-weight: bold">{$baseInfoDes}</span>
            </div>
        </div>
EOF;
        if ($usage == 'page' || $usage == 'email') {
            $titleHtml = <<<EOF
            <div style="margin-bottom: 24px; height: 20px; width: 100%; display: flex; flex-direction: row;">
                <div style="width: 4px; height: 16px; background-color: #0FBF98; border-radius: 2px!important; margin-top: 2px;"></div>
                <div style="width: 300px; height: 20px; margin-left: 8px;">
                    <span style="color: #333; font-size: 16px; line-height: 20px; font-weight: bold">{$baseInfoDes}</span>
                </div>
            </div>
EOF;
        }

        return <<<EOF
	<!-- 基本信息 -->
	<div style="margin: 32px 0 24px 0">
        {$titleHtml}
        <table style="width: 100%">
            <tbody>
                <tr style="margin-bottom: 6px">
                    <td style="width: 50%; font-size: 14px; color: #666">$recoveryTypeDes</td>
                    <td style="width: 50%; font-size: 14px; color: #666">$backupTimepointDes</td>
                </tr>
                <tr>
                    <td style="width: 50%; font-size: 14px; color: #333; font-weight: bold; vertical-align: top; word-break: break-all;">$recoveryType</td>
                    <td style="width: 50%; font-size: 14px; color: #333; font-weight: bold; vertical-align: top; word-break: break-all;">$backupTimepoint</td>
                </tr>
                <tr><td style="height: 24px"></td></tr>
                <tr style="margin-bottom: 6px">
                    <td style="width: 50%; font-size: 14px; color: #666">$sourcePathDes</td>
                    <td style="width: 50%; font-size: 14px; color: #666">$recoveryPathDes</td>
                </tr>
                <tr>
                    <td style="width: 50%; font-size: 14px; color: #333; font-weight: bold; vertical-align: top; word-break: break-all;padding-right:10px;">$sourcePath</td>
                    <td style="width: 50%; font-size: 14px; color: #333; font-weight: bold; vertical-align: top; word-break: break-all;">$recoveryPath</td>
                </tr>
                <tr><td style="height: 24px"></td></tr>
                <tr style="margin-bottom: 6px">
                    <td style="width: 50%; font-size: 14px; color: #666">$recoverySizeDes</td>
                    <td style="width: 50%; font-size: 14px; color: #666">$recoverySpeedDes</td>
                </tr>
                <tr>
                    <td style="width: 50%; font-size: 14px; color: #333; font-weight: bold; vertical-align: top; word-break: break-all;">$recoverySize</td>
                    <td style="width: 50%; font-size: 14px; color: #333; font-weight: bold; vertical-align: top; word-break: break-all;">$recoverySpeed</td>
                </tr>
                <tr><td style="height: 24px"></td></tr>
                <tr style="margin-bottom: 6px">
                    <td style="width: 50%; font-size: 14px; color: #666">$startTimeDes</td>
                    <td style="width: 50%; font-size: 14px; color: #666">$endTimeDes</td>
                </tr>
                <tr>
                    <td style="width: 50%; font-size: 14px; color: #333; font-weight: bold; vertical-align: top; word-break: break-all;">$startTime</td>
                    <td style="width: 50%; font-size: 14px; color: #333; font-weight: bold; vertical-align: top; word-break: break-all;">$endTime</td>
                </tr>
                <tr><td style="height: 24px"></td></tr>
                <tr style="margin-bottom: 6px">
                    <td style="width: 50%; font-size: 14px; color: #666">$recoveryDurationDes</td>
                </tr>
                <tr>
                    <td style="width: 50%; font-size: 14px; color: #333; font-weight: bold; vertical-align: top; word-break: break-all;">$recoveryDuration</td>
                </tr>
            </tbody>
        </table>
	</div>
	<!-- 分割线 -->
	<div style="height: 1px; background-color: #EFF2F5"></div>
EOF;
    }

    /**
     * 获取数据库状态
     * @param int    $connStatus 连接状态
     * @param string $usage      用途【pdf email page】
     * @return string
     */
    private function getReportDbStatus(int $connStatus, string $usage = 'pdf')
    {
        $connStatusFlag = v1_parse_flag_to_bool($connStatus);
        $dbStatusDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_DB_STATUS');
        $serviceStatusDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_SERVICE_STATUS');
        $dbStatus = !$connStatusFlag ? xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL') : xphp_get_lang('WEB_PLATFORM_DES_NORMAL');

        // html定义
        $dbStatusColor = !$connStatusFlag ? '#F19F00' : '#333';
        $titleHtml = <<<EOF
        <div style="margin-bottom: 24px; height: 20px; width: 100%;">
            <div style="width: 30px; height: 24px; border-left: 4px solid #0FBF98; float: left;"></div>
            <div style="width: 100px; height: 24px; float: left;">
                <span style="color: #333; font-size: 16px; font-weight: bold; line-height: 24px;">$dbStatusDes</span>
            </div>
        </div>
EOF;
        $contentHtml = <<<EOF
        <div style="width: 100%; height: 40px; border-radius: 4px; background-color: #EBEBEB; padding: 20px 16px">
            <div style="height: 40px; vertical-align: middle">
                <div style="width: 30px; height: 44px; border-left: 4px solid #50CD89; float: left"></div>
                <div style="width: 100px; height: 40px; float: left">
                    <div style="color: #333; font-size: 14px; margin-bottom: 4px;">{$serviceStatusDes}</div>
                    <div style="color: {$dbStatusColor}; font-size: 14px; font-weight: bold">{$dbStatus}</div>
                </div>
            </div>
        </div>
EOF;
        if ($usage == 'email' || $usage == 'page') {
            $titleHtml = <<<EOF
            <div style="margin-bottom: 24px; height: 20px; width: 100%; display: flex; flex-direction: row;">
                <div style="width: 4px; height: 16px; background-color: #0FBF98; border-radius: 2px!important; margin-top: 2px;"></div>
                <div style="width: 300px; height: 20px; margin-left: 8px;">
                    <span style="color: #333; font-size: 16px; line-height: 20px; font-weight: bold">{$dbStatusDes}</span>
                </div>
            </div>
EOF;
            $wrapperWidth = '100%';
            $statusHeight = '86px';
            if ($usage == 'email') {
                // $wrapperWidth = 'calc(100% - 32px)';
                $statusHeight = '46px';
            }
            $contentHtml = <<<EOF
            <div style="width: {$wrapperWidth}; height: {$statusHeight}; border-radius: 4px; background-color: #F5F5F5; padding: 20px 16px">
                <div style="height: 46px; display: flex; flex-direction: row;">
                    <div style="width: 50%; display: flex; flex-direction: row;">
                        <div style="width: 4px; height: 46px; background-color: #50CD89;"></div>
                        <div style="width: calc(100% - 16px); height: 46px; margin-left: 12px; display: flex; flex-direction: column;">
                            <div style="height: 18px; line-height: 18px; color: #333; font-size: 14px; margin-bottom: 8px;">{$serviceStatusDes}</div>
                            <div style="height: 18px; line-height: 18px; color: {$dbStatusColor}; font-size: 14px; font-weight: bold;">{$dbStatus}</div>
                        </div>
                    </div>
                </div>
            </div>
EOF;
        }
        return <<<EOF
	<!-- 数据库状态 -->
	<div style="margin: 40px 0;">
        {$titleHtml}
        {$contentHtml}
    </div>
EOF;
    }

    /**
     * 获取SQL验证报告
     * @param array  $verificationScript 验证报告
     * @param string $usage              用途【pdf email page】
     * @return string
     */
    private function getReportSqlValidate($verificationScript, $usage = 'pdf')
    {
        $sqlValidateImg = xphp_get_config('img_base64', 'SQL_VALIDATE', 'db');
        $sqlScriptDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_SCRIPT');
        $scriptNameDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_SCRIPT_NAME');
        $scriptContentDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_SCRIPT_CONTENT');
        $scriptResultDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_SCRIPT_RESULT');

        // html定义
        $titleHtml = <<<EOF
        <div style="margin-bottom: 24px; height: 20px; width: 100%;">
            <div style="width: 30px; height: 24px; border-left: 4px solid #0FBF98; float: left"></div>
            <div style="width: 100px; height: 24px; float: left;">
                <span style="color: #333; font-size: 16px; font-weight: bold; line-height: 24px;">{$sqlScriptDes}</span>
            </div>
        </div>
EOF;
        $scriptHtml = '';
        if (is_array($verificationScript)) {
            foreach ($verificationScript as $validInfo) {
                $scriptName = $validInfo['script_name'];
                $scriptContent = $validInfo['script_content'];
                $scriptResult = $validInfo['sql_result'];
                $scriptHtml .= <<<EOF
            <div style="width: 100%; background-color: #EBEBEB; padding: 20px 16px; margin-bottom: 24px;">
                <div style="margin-bottom: 24px; width: 100%; height: 40px">
                    <div style="width: 40px; height: 41px; float: left">
                        <img src="{$sqlValidateImg['script_icon_40x41']}" width="40px" height="41px" alt="script" />
                    </div>
                    <div style="width: 200px; height: 41px; float: left; margin-left: 8px;">
                        <div style="font-size: 12px; color: #666666; padding-bottom: 0;">$scriptNameDes</div>
                        <div style="font-size: 14px; color: #333333; font-weight: bold;">$scriptName</div>
                    </div>
                </div>
                <div style="font-size: 14px; color: #666; margin-bottom: 12px;">$scriptContentDes</div>
                <div style="border: 1px solid #E6E6E6; padding: 12px; background-color: #fff; margin-bottom: 12px;">
                    <pre style="padding: 0; margin: 0; font-size: 14px; color: #333; line-height: 22px; border: none; background-color: #fff;">$scriptContent</pre>
                </div>
                <div style="font-size: 14px; color: #666; margin-bottom: 12px;">$scriptResultDes</div>
                <div style="border: 1px solid #E6E6E6; padding: 12px; background-color: #fff;">
                    <pre style="padding: 0; margin: 0; font-size: 14px; color: #333; line-height: 22px; border: none; background-color: #fff;">$scriptResult</pre>
                </div>
            </div>
EOF;
            }
        } else {
            $scriptHtml = '<div style="color: #333; font-size: 14px">' . xphp_get_lang('UI_DB_RECOVERY_VALIDATE_SCRIPT_UNSET') . '</div>';
        }
        if ($usage == 'email' || $usage == 'page') {
            $titleHtml = <<<EOF
            <div style="margin-bottom: 24px; height: 20px; width: 100%; display: flex; flex-direction: row;">
                <div style="width: 4px; height: 16px; background-color: #0FBF98; border-radius: 2px!important; margin-top: 2px;"></div>
                <div style="width: 300px; height: 20px; margin-left: 8px;">
                    <span style="color: #333; font-size: 16px; line-height: 20px; font-weight: bold">{$sqlScriptDes}</span>
                </div>
            </div>
EOF;
            $scriptHtml = '';
            if (is_array($verificationScript)) {
                foreach ($verificationScript as $validInfo) {
                    $scriptName = $validInfo['script_name'];
                    $scriptContent = $validInfo['script_content'];
                    $scriptResult = $validInfo['sql_result'];
                    $wrapperWidth = '100%';
                    $inputWidth = '300px';
                    $resultWidth = '400px';
                    $inputScriptWidth = '100%';
                    $resultScriptWidth = '100%';
                    if ($usage == 'email') {
                        // $wrapperWidth = 'calc(100% - 32px)';
                        $inputWidth = '418px';
                        // $inputWidth = '42%';
                        $resultWidth = '570px';
                        $resultWidth = 'calc(100% - 430px)';
                        // $resultWidth = 'calc(58% - 12px)';
                        $inputScriptWidth = 'calc(100% - 24px)';
                        $resultScriptWidth = 'calc(100% - 24px)';
                    }
                    $scriptHtml .= <<<EOF
                    <div style="width: {$wrapperWidth}; background-color: #F5F5F5; padding: 20px 16px; margin-bottom: 24px;">
                        <div style="margin-bottom: 24px; width: 100%; height: 40px">
                            <div style="width: 40px; height: 41px; float: left">
                                <img src="{$sqlValidateImg['script_icon_40x41']}" width="40px" height="41px" alt="script" />
                            </div>
                            <div style="width: 200px; height: 39px; float: left; margin-left: 8px; padding-top: 2px;">
                                <div style="font-size: 12px; color: #666666; padding-bottom: 2px;">$scriptNameDes</div>
                                <div style="font-size: 14px; color: #333333; font-weight: bold;">$scriptName</div>
                            </div>
                        </div>
                        <div style="width: 100%; display: flex; flex-direction: row;">
                            <div style="width: {$inputWidth}; margin-right: 12px">
                                <div style="font-size: 14px; color: #666; margin-bottom: 12px;">$scriptContentDes</div>
                                <div style="border: 1px solid #E6E6E6; padding: 12px; background-color: #fff; width: {$inputScriptWidth};">
                                    <div style="padding: 0; margin: 0; font-size: 14px; color: #333; line-height: 22px; border: none; background-color: #fff; white-space: pre; height: 264px; overflow: auto;">$scriptContent</div>
                                </div>
                            </div>
                            <div style="width: {$resultWidth}">
                                <div style="font-size: 14px; color: #666; margin-bottom: 12px;">$scriptResultDes</div>
                                <div style="border: 1px solid #E6E6E6; padding: 12px; background-color: #fff; width: {$resultScriptWidth};">
                                    <div style="padding: 0; margin: 0; font-size: 14px; color: #333; line-height: 22px; border: none; background-color: #fff; white-space: pre; height: 264px; overflow: auto;">$scriptResult</div>
                                </div>
                            </div>
                        </div>
                    </div>
EOF;
                }
            } else {
                $scriptHtml = '<div style="color: #333; font-size: 14px">' . xphp_get_lang('UI_DB_RECOVERY_VALIDATE_SCRIPT_UNSET') . '</div>';
            }
        }
        return <<<EOF
	<!-- SQL验证 -->
	<div style="margin: 16px 0">
        {$titleHtml}
        {$scriptHtml}
	</div>
EOF;
    }

    /**
     * 获取数据库历史信息
     * @param array $params 参数
     * @return array
     */
    private function getDbHistoryInfo(array $params): array
    {
        $sql = "SELECT task_name, details
                FROM bd_history_task
                WHERE id = ? AND (task_type = ? OR task_type = ?) ";
        $historyTaskData = $this->dbSelect($sql, [
            $params['job_uuid'],
            xphp_get_config('task', 'TASKTYPE')['DB_RECOVERY'],
            xphp_get_config('task', 'TASKTYPE')['DRILL']
        ]);
        if (!is_array($historyTaskData) || !$historyTaskData) {
            return $this->sendResult(xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_NOT_FOUND'), false, 0);
        }
        $details = json_decode($historyTaskData[0]['details'], true);
        $record = null;
        foreach ($details as $detailInfo) {
            if (
                $detailInfo['db_type'] == $params['db_type'] &&
                $detailInfo['db_name'] == $params['db_name'] &&
                $detailInfo['instance_name'] == $params['instance_name']
            ) {
                $record = $detailInfo;
                break;
            }
        }
        if (!$record) {
            return $this->sendResult(xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_NOT_FOUND'), false, 0);
        }
        return $this->sendResult('', true, 200, [
            'record' => $record,
            'history_task' => $historyTaskData[0],
        ]);
    }

    /**
     * 构建数据库演练报告模板项
     * @param array $item 报告模板项
     * @return array
     */
    private function buildSingleDbDrillReportTemplateItem(array $item): array
    {
        return [
            'type' => $item['type'] ?? 'text',
            'value' => $item['value'],
            'uuid' => xphp_uuid(),
            'top' => $item['top'] ?? 0,
            'left' => $item['left'] ?? 0,
            'width' => $item['width'] ?? 0,
            'height' => $item['height'] ?? 0,
            'r' => $item['r'] ?? 0,
            'g' => $item['g'] ?? 0,
            'b' => $item['b'] ?? 0,
            'font_size' => $item['font_size'] ?? 0,
            'font_style' => $item['font_style'] ?? '',
            'align' => $item['align'] ?? '',
        ];
    }

    /**
     * 构建数据库演练报告标题模板
     * @param array  $historyRecord 数据库演练历史记录
     * @param string $title         标题
     * @return array
     */
    private function buildDbDrillTitleReportTemplate(array $historyRecord, string $title): array
    {
        $template = [];
        $sqlValidateImg = xphp_get_config('img_base64', 'SQL_VALIDATE', 'db');
        // LOGO
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'type' => 'base64_image',
            'value' => str_replace('data:image/png;base64,', '', $sqlValidateImg['logo_58x58']),
            'top' => 24,
            'left' => 10,
            'width' => 16,
            'height' => 16,
        ]);
        // 主标题
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => $title,
            'top' => 22,
            'left' => 30,
            'r' => 2,
            'g' => 3,
            'b' => 4,
            'font_size' => 20,
            'font_style' => 'B',
        ]);
        // 子标题
        $dbTypeDes = xphp_get_lang('UI_DB_DATABASE_TYPE') . ': ' . xphp_get_config('db', 'DB_TYPE_DES')[$historyRecord['db_type']];
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => $dbTypeDes,
            'top' => 34,
            'left' => 30,
            'r' => 74,
            'g' => 82,
            'b' => 89,
            'font_size' => 14,
        ]);
        // 恢复时间
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'type' => 'base64_image',
            'value' => str_replace('data:image/png;base64,', '', $sqlValidateImg['time_icon']),
            'top' => 34.5,
            'left' => xphp_get_language_type() == 'zh-cn'? 120 : 106,
        ]);
        $recoveryTime = xphp_get_lang('UI_DB_RECOVERY_RECOVERY_TIME') . ':' . $historyRecord['start_transfer_time'];
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => $recoveryTime,
            'top' => 34,
            'left' => 100,
            'r' => 74,
            'g' => 82,
            'b' => 89,
            'font_size' => 14,
            'align' => 'R',
        ]);
        return $template;
    }

    /**
     * 构建数据库演练报告基础信息模板
     * @param array $historyRecord 数据库演练历史记录
     * @return array
     */
    private function buildDbDrillBaseInfoReportTemplate(array $historyRecord): array
    {
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $template = [];
        // 基本信息
        $titleLine = '<span style="background-color:#0FBF98;">&nbsp;</span>';
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'type' => 'html_cell',
            'value' => $titleLine,
            'top' => 63,
            'left' => 10,
            'height' => 5,
        ]);
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_BASE_INFO'),
            'top' => 64,
            'left' => 14,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 14,
            'font_style' => 'B',
            'align' => 'L',
        ]);

        // 第一行
        // 恢复方式
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_RECOVERY_TYPE'),
            'top' => 80,
            'left' => 10,
            'r' => 102,
            'g' => 102,
            'b' => 102,
            'font_size' => 12,
            'align' => 'L',
        ]);
        $recoveryType = xphp_get_desc('Db', 'DB_RECOVERY_LEVEL_DES')[$historyRecord['recovery_level']];
        if ($historyRecord['recovery_level'] == xphp_get_config('db', 'DB_RECOVERY_TYPE')['COVER']) {
            $recoveryType = xphp_get_lang('UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY');  // 演练任务是覆盖恢复
        }
        $recoveryType = xphp_get_desc('Db', 'DB_RECOVERY_LEVEL_DES')[$historyRecord['recovery_level']];
        if ($historyRecord['recovery_level'] == xphp_get_config('db', 'DB_RECOVERY_TYPE')['COVER']) {
            $recoveryType = xphp_get_lang('UI_DB_ORIGINAL_NEWEST_TIMEPOINT_RECOVERY');  // 演练任务是覆盖恢复
        } elseif ($historyRecord['recovery_level'] == xphp_get_config('db', 'DB_RECOVERY_TYPE')['CREATE']) {
            switch ($historyRecord['db_type']) {
                case $allDbType['KINGBASE']:
                case $allDbType['POSTGRE']:
                case $allDbType['UXDB']:
                case $allDbType['HIGHGO']:
                case $allDbType['OPENGAUSS']:
                case $allDbType['ANTDB']:
                case $allDbType['VASTBASE']:
                    $recoveryType = xphp_get_lang('UI_DB_ADD_NEW_INSTANCE_RECOVERY');
                    break;
                default:
                    $recoveryType = xphp_get_lang('UI_DB_ADD_NEW_DATABASE_RECOVERY');
                    break;
            }
        }
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => $recoveryType,
            'top' => 87,
            'left' => 10,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 12,
            'font_style' => 'B',
            'align' => 'L',
        ]);
        // 备份时间点
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_RECOVERY_BACKUP_POINT'),
            'top' => 80,
            'left' => 100,
            'r' => 102,
            'g' => 102,
            'b' => 102,
            'font_size' => 12,
            'align' => 'L',
        ]);
        $backupMode = xphp_get_desc('Db', 'DB_BACKUP_MODE_DES')[$historyRecord['mode']];
        if ($historyRecord['mode'] == xphp_get_config('db', 'BACKUP_MODE', 'db')['log_backup']) {  // 日志备份
            switch ($historyRecord['db_type']) {
                case $allDbType['ORACLE']:
                case $allDbType['DM']:
                case $allDbType['POSTGRE']:
                case $allDbType['KINGBASE']:
                case $allDbType['UXDB']:
                case $allDbType['HIGHGO']:
                case $allDbType['OPENGAUSS']:
                case $allDbType['VASTBASE']:
                case $allDbType['ANTDB']:
                    $backupMode = xphp_get_lang('UI_BACKUP_ARCHIVE_LOG');
                    break;
                default:
                    break;
            }
        }
        $backupTimepoint = $historyRecord['timepoint'] . '(' . $backupMode . ')';
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => $backupTimepoint,
            'top' => 87,
            'left' => 100,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 12,
            'font_style' => 'B',
            'align' => 'L',
        ]);

        // 第二行
        // 源路径
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_SOURCE_PATH'),
            'top' => 100,
            'left' => 10,
            'r' => 102,
            'g' => 102,
            'b' => 102,
            'font_size' => 12,
            'align' => 'L',
        ]);
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => $historyRecord['dir_path'],
            'top' => 107,
            'left' => 10,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 12,
            'font_style' => 'B',
            'align' => 'L',
        ]);
        // 恢复路径
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_PATH'),
            'top' => 100,
            'left' => 100,
            'r' => 102,
            'g' => 102,
            'b' => 102,
            'font_size' => 12,
            'align' => 'L',
        ]);
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => $historyRecord['des_dir_path'],
            'top' => 107,
            'left' => 100,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 12,
            'font_style' => 'B',
            'align' => 'L',
        ]);

        // 第三行
        // 恢复总大小
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_SIZE'),
            'top' => 120,
            'left' => 10,
            'r' => 102,
            'g' => 102,
            'b' => 102,
            'font_size' => 12,
            'align' => 'L',
        ]);
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => v1_calsize($historyRecord['total_size'], true),
            'top' => 127,
            'left' => 10,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 12,
            'font_style' => 'B',
            'align' => 'L',
        ]);
        // 运行速度
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_SPEED'),
            'top' => 120,
            'left' => 100,
            'r' => 102,
            'g' => 102,
            'b' => 102,
            'font_size' => 12,
            'align' => 'L',
        ]);
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => v1_calspeed($historyRecord['transfer_speed']),
            'top' => 127,
            'left' => 100,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 12,
            'font_style' => 'B',
            'align' => 'L',
        ]);

        // 第四行
        // 开始时间
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_START_TIME'),
            'top' => 140,
            'left' => 10,
            'r' => 102,
            'g' => 102,
            'b' => 102,
            'font_size' => 12,
            'align' => 'L',
        ]);
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => $historyRecord['start_transfer_time'],
            'top' => 147,
            'left' => 10,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 12,
            'font_style' => 'B',
            'align' => 'L',
        ]);
        // 结束时间
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_END_TIME'),
            'top' => 140,
            'left' => 100,
            'r' => 102,
            'g' => 102,
            'b' => 102,
            'font_size' => 12,
            'align' => 'L',
        ]);
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => $historyRecord['start_transfer_time'],
            'top' => 147,
            'left' => 100,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 12,
            'font_style' => 'B',
            'align' => 'L',
        ]);

        // 第五行
        // 运行时长
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_RECOVERY_DURATION'),
            'top' => 160,
            'left' => 10,
            'r' => 102,
            'g' => 102,
            'b' => 102,
            'font_size' => 12,
            'align' => 'L',
        ]);
        $duration = strtotime($historyRecord['end_transfer_time']) - strtotime($historyRecord['start_transfer_time']);
        $recoveryDuration = v1_sec_to_time($duration);
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => $recoveryDuration,
            'top' => 167,
            'left' => 10,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 12,
            'font_style' => 'B',
            'align' => 'L',
        ]);
        return $template;
    }

    /**
     * 数据库状态报告模板
     * @param array $historyRecord 历史任务记录
     * @return array
     */
    private function buildDbDrillDbStatusReportTemplate(array $historyRecord): array
    {
        $template = [];
        // 数据库状态
        $secondSectionLine = '<span style="background-color:#0FBF98;">&nbsp;</span>';
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'type' => 'html_cell',
            'value' => $secondSectionLine,
            'top' => 183,
            'left' => 10,
            'width' => 5,
            'height' => 5,
        ]);
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_DB_STATUS'),
            'top' => 184,
            'left' => 14,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 14,
            'font_style' => 'B',
            'align' => 'L',
        ]);

        // 背景框
        $firstSplitLine = '<div style="background-color:#EBEBEB;"></div>';
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'type' => 'html',
            'value' => $firstSplitLine,
            'top' => 195,
            'left' => 10,
            'height' => 15,
        ]);
        $connStatusFlag = v1_parse_flag_to_bool($historyRecord['conn_status'] ?: 0);
        // 服务状态
        $serveStatusLine = '<span style="background-color:#50CD89">&nbsp;</span>';
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'type' => 'html_cell',
            'value' => $serveStatusLine,
            'top' => 198,
            'left' => 16,
            'width' => 5,
            'height' => 10,
        ]);
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_SERVICE_STATUS'),
            'top' => 198,
            'left' => 20,
            'r' => 102,
            'g' => 102,
            'b' => 102,
            'font_size' => 12,
            'align' => 'L',
        ]);
        $dbStatus = !$connStatusFlag ? xphp_get_lang('WEB_PLATFORM_DES_ABNORMAL') : xphp_get_lang('WEB_PLATFORM_DES_NORMAL');
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => $dbStatus,
            'top' => 206,
            'left' => 20,
            'r' => $connStatusFlag ? 51 : 241,
            'g' => $connStatusFlag ? 51 : 159,
            'b' => $connStatusFlag ? 51 : 0,
            'font_size' => 12,
            'font_style' => 'B',
            'align' => 'L',
        ]);
        return $template;
    }

    /**
     * 演练脚本报告模板
     * @param array $historyRecord 历史任务记录
     * @return array
     */
    private function buildDbDrillDrillScriptReportTemplate(array $historyRecord): array
    {

        $template = [[
            'type' => 'page',
        ]];
        // SQL验证
        $secondSectionLine = '<span style="background-color:#0FBF98;">&nbsp;</span>';
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'type' => 'html_cell',
            'value' => $secondSectionLine,
            'top' => 20,
            'left' => 10,
            'width' => 5,
            'height' => 5,
        ]);
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'value' => xphp_get_lang('UI_DB_RECOVERY_VALIDATE_SCRIPT'),
            'top' => 21,
            'left' => 14,
            'r' => 51,
            'g' => 51,
            'b' => 51,
            'font_size' => 14,
            'font_style' => 'B',
            'align' => 'L',
        ]);
        if (is_array($historyRecord['verification_script']) && $historyRecord['verification_script']) {
            $sqlValidateImg = xphp_get_config('img_base64', 'SQL_VALIDATE', 'db');
            $scriptNameDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_SCRIPT_NAME');
            $scriptContentDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_SCRIPT_CONTENT');
            $scriptResultDes = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_SCRIPT_RESULT');
            $scriptHtml = '';

            foreach ($historyRecord['verification_script'] as $validInfo) {
                $scriptName = $validInfo['script_name'];
                $scriptContent = str_replace("\n", '<br>', $validInfo['script_content']);
                $scriptResult = str_replace("\n", '<br>', $validInfo['sql_result']);
                $scriptHtml .= <<<HTML
<div style="background-color: #EBEBEB; font-size: 0; padding: 20px">
  <table style="font-size: 0" cellspacing="3" cellpadding="3" width="200px">
    <tr style="font-size: 0">
      <td width="10px"></td>
      <td width="50px">
        <img src="{$sqlValidateImg['script_icon_40x41']}" width="40px" height="41px" alt="script" />
      </td>
      <td height="40px">
        <table>
          <tr>
            <td style="font-size: 10px; color: #666666;" align="left">{$scriptNameDes}</td>
          </tr>
          <tr><td style="font-size: 8px">&nbsp;</td></tr>
          <tr>
            <td style="font-size: 12px; color: #333333; font-weight: bold;" align="left">{$scriptName}</td>
          </tr>
        </table>
      </td>
    </tr>
  </table>

  <table style="font-size: 0" cellspacing="3" cellpadding="3" width="100%">
    <tr style="font-size: 0">
      <td width="10px"></td>
      <td width="500px" style="font-size: 12px; color: #666666;" align="left">{$scriptContentDes}</td>
      <td width="10px"></td>
    </tr>
    <tr>
      <td></td>
      <td style="font-size: 12px; color: #333333; border: 1px solid #E6E6E6;" bgcolor="#FFFFFF">{$scriptContent}</td>
      <td></td>
    </tr>
    <tr><td style="font-size: 4px">&nbsp;</td></tr>
    <tr style="font-size: 0">
      <td width="10px"></td>
      <td width="500px" style="font-size: 12px; color: #666666;" align="left">{$scriptResultDes}</td>
      <td width="10px"></td>
    </tr>
    <tr>
      <td></td>
      <td style="font-size: 12px; color: #333333; border: 1px solid #E6E6E6;" bgcolor="#FFFFFF">{$scriptResult}</td>
      <td></td>
    </tr>
  </table>
</div>
<br />
<br />
HTML;
            }
        } else {
            $scriptHtml = '<div style="color: #333333; font-size: 12px">' . xphp_get_lang('UI_DB_RECOVERY_VALIDATE_SCRIPT_UNSET') . '</div>';
        }
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'type' => 'html',
            'value' => $scriptHtml,
            'top' => 36,
            'left' => 10,
            'height' => 0,
        ]);
        return $template;
    }

    /**
     * 获取数据库演练报告模板
     * @param array  $historyRecord 历史任务记录
     * @param string $title         报告标题
     * @return array
     */
    private function getDbDrillReportTemplate(array $historyRecord, string $title): array
    {
        // 标题信息
        $template = $this->buildDbDrillTitleReportTemplate($historyRecord, $title);

        // 分割线
        $firstSplitLine = '<div style="background-color: #0FBF98;"></div>';
        $template[] = $this->buildSingleDbDrillReportTemplateItem([
            'type' => 'html',
            'value' => $firstSplitLine,
            'top' => 50,
            'left' => 10,
            'height' => 1,
        ]);

        // 基础信息
        $baseInfoTemplate = $this->buildDbDrillBaseInfoReportTemplate($historyRecord);;
        $template = array_merge($template, $baseInfoTemplate);

        // 数据库状态
        $dbStatusTemplate = $this->buildDbDrillDbStatusReportTemplate($historyRecord);
        $template = array_merge($template, $dbStatusTemplate);

        // 演练脚本
        $drillScriptTemplate = $this->buildDbDrillDrillScriptReportTemplate($historyRecord);
        return array_merge($template, $drillScriptTemplate);
    }

    /**
     * 生成数据库验证报告
     * @param array $params 参数
     * @return array
     */
    public function generateDbValidateReport(array $params): array
    {
        $historyData = $this->getDbHistoryInfo($params);
        if (!$historyData['success']) {
            return $historyData;
        }
        $record = $historyData['data']['record'];
        $historyTask = $historyData['data']['history_task'];

        $reportName = htmlspecialchars_decode($historyTask['task_name']) . xphp_get_lang('UI_PUBLIC_REPORT');
        $reportFilename = $reportName . '_' . date('YmdHis');
        $reportMd5 = md5($params['job_uuid'] . '_' . $params['db_type'] . '_' . $params['db_name'] . '_' . $params['instance_name']);
        $pdfPath = xphp_get_config('app', 'DB_VALIDATE_REPORT_DIR') . "/{$reportFilename}.pdf";
        if (!is_dir(xphp_get_config('app', 'DB_VALIDATE_REPORT_DIR'))) {
            mkdir(xphp_get_config('app', 'DB_VALIDATE_REPORT_DIR'), 0777, true);  // umask 022, 因此创建的目录是755
            chmod(xphp_get_config('app', 'DB_VALIDATE_REPORT_DIR'), 0777);
        }

        $drillReportTemplate = $this->getDbDrillReportTemplate($record, $reportName);
        $pdfResult = (new DbJobInfoPdf())->makePdf($drillReportTemplate, 1, $reportMd5, $pdfPath, '', 1, $reportName);
        if ($pdfResult['code'] != 0) {
            return $pdfResult;
        }

        $systemHandler = new \app\v1\system\v0\logic\Index();
        $nodeHandler = new \app\v1\resources\v0\logic\Node();
        $downloadUrl = $systemHandler->groupUnifyDownloadUrl(
            $nodeHandler->getLocalNodeUUID(),
            $pdfPath,
            true
        );
        return $this->sendResult('', true, 200, [
            'report_url' => $downloadUrl,
        ]);
    }

    /**
     * 获取数据库验证报告
     * @param array $params 参数
     * @return array
     */
    public function getDbValidateReport(array $params): array
    {
        $historyData = $this->getDbHistoryInfo($params);
        if (!$historyData['success']) {
            return $historyData;
        }
        $record = $historyData['data']['record'];
        $historyTask = $historyData['data']['history_task'];

        $reportName = $historyTask['task_name'] . xphp_get_lang('UI_PUBLIC_REPORT');
        $dbTypeDes = xphp_get_lang('UI_DB_DATABASE_TYPE') . ': ' . xphp_get_config('db', 'DB_TYPE_DES')[$params['db_type']];

        $title = $this->getReportTitle($reportName, $dbTypeDes, $record['start_transfer_time'], 'page');
        $basicJobInfo = $this->getReportBasicJobInfo($record, $params['db_type'], 'page');
        $dbStatus = $this->getReportDbStatus($record['conn_status'] ?: 0, 'page');
        $sqlValidate = $this->getReportSqlValidate($record['verification_script'], 'page');
        $html = <<<EOF
        <style>
            tr, tbody, table, td, div, span, copyright, a, hr, br {
                font-family: Microsoft YaHei UI-Regular, Microsoft YaHei UI;
            }
        </style>
        <div>
            {$title}
            {$basicJobInfo}
            {$dbStatus}
            {$sqlValidate}
        </div>
EOF;
        return $this->sendResult('', true, 200, [
            'content' => $html,
        ]);
    }

    /**
     * 获取邮件页脚
     * @param string $host         主机
     * @param string $supportEmail 支持邮箱
     * @return string
     */
    private function getEmailFooter(string $host, string $supportEmail): string
    {
        $tips1 = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_EMAIL_FOOTER_TIPS1');
        $tips2 = xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_EMAIL_FOOTER_TIPS2');
        return <<<EOF
        <style>
            #supportEmail a {
                color: #6C757D!important;
            }
        </style>
        <div style="width: calc(100% - 88px); padding: 54px 44px; background-color: #F5F5F5; height: 59px;">
            <div style="font-size: 12px; color: #6C757D; height: 23px; line-height: 23px;">
                $tips1
                <a style="color: #24CCA7; text-decoration: none;" href="$host" target="_blank">$host</a>
            </div>
            <hr style="border: none;border-top: 1px dashed #DBDBDB;margin: 7px 0;">
            <div style="height: 23px; display: flex; flex-direction: row; justify-content: space-between;align-items: center; font-size: 12px; color: #6C757D; line-height: 23px">
                <span>$tips2<span id="supportEmail" style="color: #6C757D; text-decoration: underline; font-weight: bold;">$supportEmail</span></span>
                <copyright>&copy;2025 Vinchin All rights reserved</copyright>
            </div>
        </div>
EOF;
    }

    /**
     * 发送数据库验证报告
     * @param array $params 参数
     * @return array
     */
    public function sendDbValidateReport(array $params): array
    {
        $historyData = $this->getDbHistoryInfo($params);
        if (!$historyData['success']) {
            return $historyData;
        }
        $record = $historyData['data']['record'];
        $historyTask = $historyData['data']['history_task'];


        $sql = "SELECT ip FROM bd_node WHERE node_type = ? LIMIT 1 ";
        $ip = $this->dbSelect($sql, [xphp_get_config('app', 'NODETYPE')['MASTER']]);
        if (!is_array($ip) || !$ip) {
            $ip = '';
        } else {
            $ip = $ip[0]['ip'];
        }
        $serverIpArr = explode(' ', $ip);
        $host = 'https://' . $serverIpArr[0];
        $systemInfo = xphp_get_config('app', 'SYSTEM_INFO');

        $reportName = $historyTask['task_name'] . xphp_get_lang('UI_PUBLIC_REPORT');
        $dbTypeDes = xphp_get_lang('UI_DB_DATABASE_TYPE') . ': ' . xphp_get_config('db', 'DB_TYPE_DES')[$params['db_type']];

        $title = $this->getReportTitle($reportName, $dbTypeDes, $record['start_transfer_time'], 'email');
        $basicJobInfo = $this->getReportBasicJobInfo($record, $params['db_type'], 'email');
        $dbStatus = $this->getReportDbStatus($record['conn_status'] ?: 0, 'email');
        $sqlValidate = $this->getReportSqlValidate($record['verification_script'], 'email');
        $emailFooter = $this->getEmailFooter($host, $systemInfo['company_email']);

        $noticeHandler = new Notice();
        $loginUser = xphp_get_user_info();
        $html = <<<EOF
        <style>
            tr, tbody, table, td, div, span, copyright, a, hr, br {
                font-family: Microsoft YaHei UI-Regular, Microsoft YaHei UI;
            }
        </style>
        <div style="width: 100%; padding-top: 30px; background-color: #EAECED;">
            <div style="width: 912px; margin: 0 auto; padding: 48px 44px; background-color: #fff;">
                {$title}
                {$basicJobInfo}
                {$dbStatus}
                {$sqlValidate}
            </div>
            <div style="width: 1000px; margin: 0 auto;">
                {$emailFooter}
            </div>
        </div>
EOF;
        $email = [];
        $sql = "SELECT email_notice_flag, db_drill_report_flag, receive_email FROM bd_email_notice WHERE email_notice_type = 1 ";
        $emailData = $this->dbSelect($sql);
        if (is_array($emailData) && $emailData) {
            $unsetFlag = xphp_get_config('app', 'FLAG')['UNSET'];
            if ($emailData[0]['email_notice_flag'] == $unsetFlag || $emailData[0]['db_drill_report_flag'] == $unsetFlag) {
                return $this->sendResult(xphp_get_lang('UI_DB_DRILL_REPORT_NOT_ENABLE_EMAIL_NOTICE'), false, 0);
            }
            $receiveEmail = json_decode($emailData[0]['receive_email'], true);
            if ($receiveEmail) {
                $email = $receiveEmail;
            }
        } else {
            return $this->sendResult(xphp_get_lang('UI_DB_DRILL_REPORT_NOT_FOUND_EMAIL_CONFIG'), false, 0);
        }
        if (!$email) {
            return $this->sendResult(xphp_get_lang('UI_DB_DRILL_REPORT_NOT_FOUND_EMAIL_RECIPIENT'), false, 0);
        }
        $emails = [
            'title' => $reportName,
            'email' => $email,
            'info' => $html,
        ];
        $return = $noticeHandler->sendEmail($emails);
        if ($return) {
            return $this->sendResult('', true, 200);
        } else {
            return $this->sendResult('', false, 0);
        }
    }

    /**
     * 通过历史任务ID发送数据库验证报告
     * @param string $historyUuid 历史任务ID
     * @return array
     */
    public function sendDrillEmailByHistoryUuid(string $historyUuid): array
    {
        $result = [];
        $sql = "SELECT details, id FROM bd_history_task WHERE history_uuid = ? ";
        $drillData = $this->dbSelect($sql, [$historyUuid]);
        if (empty($drillData)) {
            return $this->sendResult(xphp_get_lang('UI_DB_RECOVERY_VALIDATE_REPORT_NOT_FOUND'), false, 0);
        }
        $details = json_decode($drillData[0]['details'], true);
        foreach ($details as $detail) {
            $dbDrillParams = [
                'job_uuid' => $drillData[0]['id'],
                'db_type' => $detail['db_type'],
                'db_name' => $detail['db_name'],
                'instance_name' => $detail['instance_name'],
            ];
            $sendResult = $this->sendDbValidateReport($dbDrillParams);
            $result[] = [
                'job_uuid' => $historyUuid,
                'db_type' => $detail['db_type'],
                'db_name' => $detail['db_name'],
                'instance_name' => $detail['instance_name'],
                'success' => $sendResult['success'],
                'code' => $sendResult['code'],
                'message' => $sendResult['message'],
                'data' => $sendResult['data'],
            ];
        }
        return $this->sendResult('', true, 200, $result);
    }

    /**
     * 获取数据库备份任务的关联任务
     * @param string $jobsUuid 任务uuid
     * @return array
     */
    public function getDbJobAssociation(string $jobsUuid): array
    {
        $taskData = $this->checkTaskExists($jobsUuid, [xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']]);
        if (!$taskData) {  // 检测任务是否存在
            return $this->sendResult(xphp_get_lang('WEB_DB_BACKUP_TASK_NOT_EXISTS'), false, 0);
        }
        $dbTaskData = $this->dbSelect("SELECT db_type, depend_task_uuid FROM db_task WHERE task_uuid = ? ", [$jobsUuid]);
        $dbType = $dbTaskData[0]['db_type'];

        // 获取关联任务
        $sql = "SELECT bt.task_name, bt.task_uuid, bt.task_type, bt.task_status, bt.create_time,
                    dt.db_type, dt.depend_task_uuid,
                    bn.node_uuid, bn.ip AS node_ip, bn.host_name AS node_hostname, bn.node_nickname, bn.node_type,
                    bsr.storage_uuid, bsr.storage_nickname, bsr.storage_type, bsr.total_size, bsr.free_size
                FROM bd_task bt
                    INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid
                    INNER JOIN bd_node bn ON bt.node_uuid = bn.node_uuid
                    INNER JOIN bd_storage_resource bsr ON bt.storage_uuid = bsr.storage_uuid
                WHERE (bt.task_uuid = ? OR dt.depend_task_uuid = ?) AND dt.db_type = ? ";
        $associationTaskData = $this->dbSelect($sql, [$dbTaskData[0]['depend_task_uuid'], $jobsUuid, $dbType]);
        if (!is_array($associationTaskData) || !$associationTaskData) {
            $associationTaskData = [];
        }

        $rows = [];
        foreach ($associationTaskData as $associationTaskInfo) {
            if ($associationTaskInfo['task_uuid'] == $jobsUuid) {
                continue;
            }
            // 查询数据库信息
            $sql = "SELECT dl.task_uuid, dl.db_name, dl.instance_name, baa.agent_uuid, baa.cluster_flag,
                        baa.cluster_uuid, baa.cluster_name, baa.cluster_service_ip, baa.app_service_name, baa.app_detail,
                        ba.ip AS agent_ip
                    FROM db_list dl
                        INNER JOIN bd_agent_app baa ON baa.agent_uuid = dl.agent_uuid AND baa.app_name = dl.instance_name
                        INNER JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                    WHERE dl.task_uuid = ? AND baa.app_type = ? ";
            $dbData = $this->dbSelect($sql, [$associationTaskInfo['task_uuid'], $dbType]);
            if (is_array($dbData) && $dbData) {
                $clusterFlag = v1_parse_flag_to_bool($dbData[0]['cluster_flag']) && $dbData[0]['cluster_uuid'];
                if ($clusterFlag) {
                    $dbName = $dbData[0]['db_name'];
                    $sql = "SELECT '{$dbName}' AS db_name, baa.app_name AS instance_name, baa.agent_uuid, baa.cluster_flag,
                                baa.cluster_uuid, baa.cluster_name, baa.cluster_service_ip, baa.app_service_name, baa.app_detail,
                                ba.ip AS agent_ip
                            FROM bd_agent_app baa
                                INNER JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                            WHERE baa.cluster_uuid = ?
                            ORDER BY baa.id ";
                    $dbData = $this->dbSelect($sql, [$dbData[0]['cluster_uuid']]);
                }
            } else {
                $dbData = [];
            }
            $rows[] = [
                'job_uuid' => $associationTaskInfo['task_uuid'],
                'job_name' => $associationTaskInfo['task_name'],
                'depend_job_uuid' => $associationTaskInfo['depend_task_uuid'] ?: '',
                'task_type' => xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'],
                'db_type' => $dbType,
                'task_status' => $associationTaskInfo['task_status'],
                'create_time' => $associationTaskInfo['create_time'],
                'node_info' => [
                    'node_uuid' => $associationTaskInfo['node_uuid'],
                    'node_ip' => $associationTaskInfo['node_ip'],
                    'node_hostname' => $associationTaskInfo['node_hostname'],
                    'node_nickname' => $associationTaskInfo['node_nickname'] ?: '',
                    'node_type' => $associationTaskInfo['node_type'],
                ],
                'storage_info' => [
                    'storage_uuid' => $associationTaskInfo['storage_uuid'],
                    'storage_nickname' => $associationTaskInfo['storage_nickname'],
                    'storage_type' => $associationTaskInfo['storage_type'],
                    'total_size' => $associationTaskInfo['total_size'],
                    'free_size' => $associationTaskInfo['free_size'],
                    'node_uuid' => $associationTaskInfo['node_uuid'],
                ],
                'backup_db_list' => array_map(function ($dbInfo) {
                    $appDetail = json_decode($dbInfo['app_detail'], true);
                    return [
                        'db_name' => $dbInfo['db_name'],
                        'instance_name' => $dbInfo['instance_name'],
                        'cluster_flag' => v1_parse_flag_to_bool($dbInfo['cluster_flag']) && $dbInfo['cluster_uuid'],
                        'app_service_name' => $dbInfo['app_service_name'],
                        'agent_uuid' => $dbInfo['agent_uuid'],
                        'agent_ip' => $dbInfo['agent_ip'],
                        'cluster_uuid' => $dbInfo['cluster_uuid'],
                        'cluster_name' => $dbInfo['cluster_name'],
                        'cluster_service_ip' => $dbInfo['cluster_service_ip'],
                        'cluster_role' => $appDetail['node_role'] ?? '',
                    ];
                }, $dbData),
            ];
        }
        return $this->sendResult('', true, 200, [
            'rows' => $rows,
            'total' => count($rows),
            'task_type' => xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'],
            'db_type' => $dbType,
        ]);
    }

    /**
     * 根据任务uuid获取任务列表
     * @param string $taskUuid 任务uuid
     * @return array
     */
    public function getAppListByTaskUuid(string $taskUuid): array
    {
        $appList = [];
        $sql = "SELECT dt.db_type, dt.depend_task_uuid, dl.instance_name, dl.agent_uuid
                    FROM db_task dt
                        INNER JOIN db_list dl ON dt.task_uuid = dl.task_uuid
                    WHERE dt.task_uuid = ?";
        $taskData = $this->dbSelect($sql, [$taskUuid]);
        $sql = "SELECT app_name, app_type, agent_uuid, cluster_uuid FROM bd_agent_app WHERE app_type = ? ";
        $instanceList = [];
        foreach ($taskData as $taskInfo) {
            $instanceList[] = " ( app_name = '{$taskInfo['instance_name']}' AND agent_uuid = '{$taskInfo['agent_uuid']}' ) ";
        }
        $instanceDes = implode(' OR ', $instanceList);
        $sql .= " AND ($instanceDes) ";
        $appData = $this->dbSelect($sql, [$taskData[0]['db_type']]);
        if (!is_array($appData)) {
            $appData = [];
        }
        foreach ($appData as $appInfo) {
            if ($appInfo['cluster_uuid']) {  // 集群构建所有客户端应用
                $sql = "SELECT app_name, app_type, agent_uuid FROM bd_agent_app WHERE cluster_uuid = ? ";
                $clusterAppData = $this->dbSelect($sql, [$appInfo['cluster_uuid']]);
                if (!is_array($clusterAppData)) {
                    $clusterAppData = [];
                }
                foreach ($clusterAppData as $clusterAppInfo) {
                    $appList[] = [
                        'app_name' => $clusterAppInfo['app_name'],
                        'app_type' => $clusterAppInfo['app_type'],
                        'agent_uuid' => $clusterAppInfo['agent_uuid'],
                    ];
                }
            } else {
                $appList[] = [
                    'app_name' => $appInfo['app_name'],
                    'app_type' => $appInfo['app_type'],
                    'agent_uuid' => $appInfo['agent_uuid'],
                ];
            }
        }
        return $appList;
    }

    /**
     * 获取数据库关联任务信息
     * @param string $taskUuid 任务uuid
     * @return array
     */
    public function getDbAssociatedJobInfo(string $taskUuid): array
    {
        $jobTaskUuidMap = [];
        $allBackupTask = (new DbBackUp())->getAllInstanceBackupInfo();
        $appList = $this->getAppListByTaskUuid($taskUuid);
        foreach ($appList as $appInfo) {
            foreach ($allBackupTask as $backupInfo) {
                if (
                    $backupInfo['instance_name'] == $appInfo['app_name'] &&
                    $backupInfo['agent_uuid'] == $appInfo['agent_uuid'] &&
                    $backupInfo['db_type'] == $appInfo['app_type']
                ) {
                    if ($taskUuid == $backupInfo['task_uuid']) {  // 排除当前任务
                        continue;
                    }
                    $jobTaskUuidMap[$backupInfo['task_uuid']] = $backupInfo;
                }
            }
        }
        return array_values($jobTaskUuidMap);
    }

    /**
     * 根据任务uuid批量获取关联任务信息
     * @param array $taskUuids
     * @return array
     */
    public function getDbAssociatedJobInfoForTasks(array $taskUuids)
    {
        if (empty($taskUuids)) {
            return [];
        }

        // 1. 批量获取所有相关任务的应用列表
        $appListByTask = $this->getAppListByTaskUuids($taskUuids);

        // 2. 一次性获取所有数据库实例备份信息
        $allBackupTask = (new DbBackUp())->getAllInstanceBackupInfo();

        $associatedJobsMap = [];
        $taskUuidsSet = array_flip($taskUuids);

        // 3. 在内存中进行数据匹配和处理
        foreach ($taskUuids as $taskUuid) {
            $associatedJobsMap[$taskUuid] = [];
            if (!isset($appListByTask[$taskUuid])) {
                continue;
            }

            $jobTaskUuidMap = [];
            $appList = $appListByTask[$taskUuid];

            foreach ($appList as $appInfo) {
                foreach ($allBackupTask as $backupInfo) {
                    if (
                        $backupInfo['instance_name'] == $appInfo['app_name'] &&
                        $backupInfo['agent_uuid'] == $appInfo['agent_uuid'] &&
                        $backupInfo['db_type'] == $appInfo['app_type']
                    ) {
                        // 排除当前正在处理的任务列表中的任何任务
                        if (isset($taskUuidsSet[$backupInfo['task_uuid']])) {
                            continue;
                        }
                        $jobTaskUuidMap[$backupInfo['task_uuid']] = $backupInfo;
                    }
                }
            }
            $associatedJobsMap[$taskUuid] = array_values($jobTaskUuidMap);
        }

        return $associatedJobsMap;
    }

    /**
     * 批量获取任务的应用列表
     * @param array $taskUuids
     * @return array
     */
    protected function getAppListByTaskUuids(array $taskUuids)
    {
        if (empty($taskUuids)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($taskUuids), '?'));

        // 步骤 1 & 2: 通过一次JOIN查询，批量获取与任务关联的初始应用信息
        $sql = "SELECT dt.task_uuid, app.app_name, app.app_type, app.agent_uuid, app.cluster_uuid
                FROM db_task dt
                INNER JOIN db_list dl ON dt.task_uuid = dl.task_uuid
                INNER JOIN bd_agent_app app ON dt.db_type = app.app_type AND dl.instance_name = app.app_name AND dl.agent_uuid = app.agent_uuid
                WHERE dt.task_uuid IN ({$placeholders})";
        $initialAppData = $this->dbSelect($sql, $taskUuids);

        // 步骤 3: 批量获取集群应用信息
        $clusterUuids = array_unique(array_filter(array_column($initialAppData, 'cluster_uuid')));
        $clusterUuids = array_values($clusterUuids);
        $clusterAppsMap = [];
        if (!empty($clusterUuids)) {
            $clusterPlaceholders = implode(',', array_fill(0, count($clusterUuids), '?'));
            $sql = "SELECT app_name, app_type, agent_uuid, cluster_uuid FROM bd_agent_app WHERE cluster_uuid IN ({$clusterPlaceholders})";
            $clusterAppsData = $this->dbSelect($sql, $clusterUuids);
            foreach ($clusterAppsData as $clusterApp) {
                $clusterAppsMap[$clusterApp['cluster_uuid']][] = $clusterApp;
            }
        }

        // 步骤 4: 组合数据
        $appListByTask = [];
        foreach ($initialAppData as $appInfo) {
            $taskUuid = $appInfo['task_uuid'];
            if (!isset($appListByTask[$taskUuid])) {
                $appListByTask[$taskUuid] = [];
            }

            if ($appInfo['cluster_uuid'] && isset($clusterAppsMap[$appInfo['cluster_uuid']])) {
                foreach ($clusterAppsMap[$appInfo['cluster_uuid']] as $clusterApp) {
                    $appListByTask[$taskUuid][] = [
                        'app_name' => $clusterApp['app_name'],
                        'app_type' => $clusterApp['app_type'],
                        'agent_uuid' => $clusterApp['agent_uuid'],
                    ];
                }
            } else {
                $appListByTask[$taskUuid][] = [
                    'app_name' => $appInfo['app_name'],
                    'app_type' => $appInfo['app_type'],
                    'agent_uuid' => $appInfo['agent_uuid'],
                ];
            }
        }

        // 去重
        foreach ($appListByTask as $taskUuid => &$apps) {
            $apps = array_map('unserialize', array_unique(array_map('serialize', $apps)));
        }
        unset($apps);

        return $appListByTask;
    }
}
