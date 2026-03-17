<?php

namespace app\v1\db\v0\logic;

use app\v1\common\logic\Backup;
use app\v1\exchange\v0\logic\ExchangeJobInfo;
use app\v1\opcode\DbProtectOpcode;
use app\v1\system\v0\logic\Index;
use app\v1\tenant\v0\logic\Tenant;
use stdClass;

/**
 * note          数据库 备份管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbBackUp extends Backup
{
    /**
     * @param array $params 参数
     * @return array
     */
    public function getDbType(array $params): array
    {
        $setFlag = xphp_get_config('app', 'FLAG');
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $allDbTypeDes = xphp_get_config('db', 'DB_TYPE_DES');
        unset($allDbType[0]);  // 删除未知类别
        unset($allDbType[1000]);  // 删除Exchange Server
        $allAgentType = xphp_get_config('client', 'AGENT_TYPE', 'resources');
        unset($allDbType['UNKNOWN']);  // 删除未知类别
        if (isset($params['is_auth_db']) && $params['is_auth_db']) {  // 查询数据库, 获取已认证的数据库类别
            $sql = "SELECT app_type FROM bd_agent_app WHERE 1=1 ";

            // 权限判断
            if(v1_auth_need_check_look()) {
                $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'agent_uuid');
                $sql .= " AND ($resourceUuidSql) ";
            }

            $sql .= " GROUP BY app_type ";
            $data = $this->dbSelect($sql);
            $this->filterDbType($data, $allDbType);
        }

        if (isset($params['is_client_online']) && $params['is_client_online']) { // 客户端在线的数据库类别
            $sql = "SELECT baa.app_type
                    FROM bd_agent_app baa
                        LEFT JOIN bd_agent ba ON ba.agent_uuid=baa.agent_uuid
                    WHERE ba.online_flag = ? AND ba.agent_type != ? ";

            // 权限判断
            if(v1_auth_need_check_look()) {
                $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'ba.agent_uuid');
                $sql .= " AND ($resourceUuidSql) ";
            }

            $data = $this->dbSelect($sql, [$setFlag['SET'], $allAgentType['APPLIANCE']]);
            $this->filterDbType($data, $allDbType);
        }
        if (isset($params['timepoint_flag']) && $params['timepoint_flag']) {  // 从备份点里查询
            $allModuleType = xphp_get_config('module', 'MODULE_TYPE');
            $moduleTypes = '(' . $allModuleType['DB'] . ',' . $allModuleType['BACKUP_COPY_CLIENT'] . ')';
            $sql = "SELECT dbt.db_type AS app_type, bbt.task_uuid, bbt.task_name, bbt.timepoint_uuid, bbt.backup_mode, bbt.chain_uuid
                    FROM bd_backup_timepoint bbt
                        INNER JOIN db_backup_timepoint dbt ON dbt.timepoint_uuid = bbt.timepoint_uuid
                        INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                    WHERE bbt.deleted_flag = ? AND bbt.available_flag = ? AND bbt.import_flag = ?
                        AND bbt.data_local_flag = ? AND bbt.module_type IN $moduleTypes ";
            $sqlParams = [$setFlag['UNSET'], $setFlag['SET'], $setFlag['UNSET'], $setFlag['SET']];
            if (isset($params['timepoint_node_uuid']) && $params['timepoint_node_uuid']) {
                $sql .= ' AND (bsr.node_uuid = ? OR bbt.real_node_uuid = ? ) ';
                $sqlParams[] = $params['timepoint_node_uuid'];
                $sqlParams[] = $params['timepoint_node_uuid'];
            }

            // 权限判断
            if(v1_auth_need_check_look()) {
                $resourceUuidSql = v1_auth_get_users('db_protect');
                $sql .= " AND bbt.user_uuid IN ($resourceUuidSql) ";
            }

            $loginUser = xphp_get_user_info();
            // 非租户用户不能查看租户的资源
            if (!$loginUser['tenantuuid']) {
                $userUuidData = $this->dbSelect("SELECT user_uuid FROM mt_user_tenant");
                if (is_array($userUuidData) && $userUuidData) {
                    $userUuidList = array_column($userUuidData, 'user_uuid');
                    $userUuidList = array_values(array_unique($userUuidList));
                    $userUuids = "'" . implode("', '", $userUuidList) . "'";
                    $sql .= " AND bbt.user_uuid NOT IN ($userUuids) ";
                }
            }
            $data = $this->dbSelect($sql, $sqlParams);
            // 过滤掉Oracle没有完备点的备份链子
            $chainList = [];
            foreach ($data as $row) {
                if ($row['chain_uuid']) {
                    if (!isset($chainList[$row['chain_uuid']])) {
                        $chainList[$row['chain_uuid']] = [];
                    }
                    $chainList[$row['chain_uuid']][] = $row;
                }
            }
            if ($chainList) {
                $excludeChainUuidList = [];
                $allBackupMode = xphp_get_config('db', 'BACKUP_MODE', 'db');
                foreach ($chainList as $chainUuid => $timepointList) {
                    $findFullFlag = false;
                    foreach ($timepointList as $timepoint) {
                        if ($timepoint['backup_mode'] == $allBackupMode['full_backup']) {
                            $findFullFlag = true;
                            break;
                        }
                    }
                    if (!$findFullFlag) {
                        $excludeChainUuidList[] = $chainUuid;
                    }
                }
                $data = array_filter($data, function($row) use ($excludeChainUuidList) {
                    return !in_array($row['chain_uuid'], $excludeChainUuidList);
                });
            }
            $this->filterDbType($data, $allDbType);
        }

        $rows = [];
        foreach ($allDbTypeDes as $dbType => $dbTypeDes) { // 这里使用配置文件的数据库类别，避免使用数据库，保证以配置文件为主
            if (!in_array($dbType, $allDbType)) {
                continue;
            }
            $rows[] = [
                'name' => $dbTypeDes,
                'type' => $dbType,
            ];
        }
        return [
            'rows' => $rows,
            'total' => count($rows),
        ];
    }

    /**
     * @param mixed $data      查询的数据
     * @param array $allDbType 所有的数据库类别
     * @return void
     */
    private function filterDbType($data, array &$allDbType)
    {
        if (!$data || !is_array($data)) {
            $data = [];
        }
        $selectType = array_map(function ($row) {
            return (int) $row['app_type'];
        }, $data);
        foreach ($allDbType as $key => $type) {
            if (!in_array($type, $selectType, true)) {
                unset($allDbType[$key]);
            }
        }
    }

    /**
     * 加载数据库应用/实例的数据库信息
     * @param string $appUuid 客户端uuid
     * @return array
     */
    public function loadInstanceDatabase(string $appUuid): array
    {
        $opName = 'DB_INSTANCE_OP_SCAN_DB';
        $operate = $this->pfOpcode->getOpcodeDes($opName);
        $sql = "SELECT app_name AS instance_name, app_auth_type AS auth_type, app_username AS username,
                    app_password AS password, app_type AS db_type, agent_uuid, cluster_flag, cluster_uuid, cluster_name, cluster_type,
                    app_detail
                FROM bd_agent_app WHERE app_uuid = ?";
        $appData = $this->dbSelect($sql, [$appUuid]);
        if (!is_array($appData) || !$appData) {
            return $this->sendResult(xphp_get_lang('WEB_DB_BACKUP_INSTANCE_NOT_EXISTS'), false, 0);
        }
        $appData = $appData[0];
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        if ($appData['db_type'] == $allDbType['TIDB'] && $appData['cluster_uuid']) {  // TiDB用中控机扫描
            $sql = "SELECT app_name AS instance_name, app_auth_type AS auth_type, app_username AS username,
                        app_password AS password, app_type AS db_type, agent_uuid, cluster_flag, cluster_uuid, cluster_name, cluster_type,
                        app_detail
                    FROM bd_agent_app WHERE cluster_uuid = ? ";
            $tidbClusterList = $this->dbSelect($sql, [$appData['cluster_uuid']]);
            $appData = $tidbClusterList[0];
            foreach ($tidbClusterList as $tidbAppInfo) {
                $appDetail = json_decode($tidbAppInfo['app_detail'], true);
                if (strpos($appDetail['node_role'], 'deploy') !== false) {
                    $appData = $tidbAppInfo;
                    break;
                }
            }
        }

        $serviceRet = $this->service()->loadInstanceDatabaseService($appData);

        if (!$serviceRet['result']) {
            $this->muOpResult(false, $operate, $serviceRet['msg'], '', $serviceRet['errorCode']);
            return $this->sendResult('failed', false, 0);
        }

        $dbList = $serviceRet['msg']['db_list'];
        if ($appData['db_type'] == $allDbType['ORACLE']) {
            $dbList = $serviceRet['msg']['table_space_list'];
        }

        $allDbBackupJob = $this->getAllDbBackupJob(intval($appData['db_type']));
        $appDetail = json_decode($appData['app_detail'], true);
        if (!$appDetail) {
            $appDetail = new stdClass();
        }
        $rows = [];
        foreach ($dbList as $db) {
            $appData['db_name'] = $db['db_name'];
            if ($appData['db_type'] == $allDbType['ORACLE']) {
                $appData['db_name'] = $db['table_space'];
            }
            $dbBackupInfo = $this->getDbBackupInfo($appData, $allDbBackupJob);
            $dbClusterBackupInfo = $this->getDbClusterBackupInfo($appData, $dbBackupInfo, $allDbBackupJob);
            $rows[] = [
                'db_name' => $appData['db_name'],
                'recovery_mode' => intval($db['recovery_mode'] ?: 0),
                'instance_name' => $appData['instance_name'],
                'is_cluster' => v1_parse_flag_to_bool($appData['cluster_flag']) && $appData['cluster_uuid'],
                'cluster_uuid' => $appData['cluster_uuid'],
                'cluster_name' => $appData['cluster_name'],
                'cluster_type' => intval($appData['cluster_type']),  // MongoDB: 1single 2repset 3shard
                'is_backup_job' => (bool) $dbBackupInfo,
                'is_cluster_backup_job' => (bool) $dbClusterBackupInfo,
                'agent_uuid' => $appData['agent_uuid'],
                'app_uuid' => $appUuid,
                'db_type' => (int) $appData['db_type'],
                'job_name' => $dbBackupInfo['task_name'] ?? '',
                'job_uuid' => $dbBackupInfo['task_uuid'] ?? '',
                'cluster_job_name' => $dbClusterBackupInfo['task_name'] ?? '',
                'cluster_job_uuid' => $dbClusterBackupInfo['task_uuid'] ?? '',
                'app_detail' => $appDetail,
            ];
        }
        return $this->sendResult('', true, 200, [
            'rows' => $rows,
            'total' => count($rows),
        ]);
    }

    /**
     * 加载数据库数据文件
     * @param array $params 参数
     * @return array
     */
    public function loadDbDataFile(array $params): array
    {
        $sql = "SELECT agent_uuid FROM bd_agent WHERE agent_uuid = ? ";
        $agentData = $this->dbSelect($sql, [$params['agent_uuid']]);
        if (!$agentData || !is_array($agentData)) {
            return $this->sendResult(xphp_get_lang('WEB_AGENT_NO_AGENT'), false, 0);
        }

        $sql = "SELECT agent_uuid, app_name, app_type, app_username, app_password, app_auth_type
                FROM bd_agent_app
                WHERE agent_uuid = ? AND app_type = ? AND app_name = ? ";
        $appData = $this->dbSelect($sql, [$params['agent_uuid'], $params['db_type'], $params['instance_name']]);
        if (!$appData ||!is_array($appData)) {
            return $this->sendResult(xphp_get_lang('WEB_DB_BACKUP_INSTANCE_NOT_EXISTS'), false, 0);
        }

        $opName = 'DB_INSTANCE_OP_GET_TABLE_SPACE_DATA_FILE';
        $dbProtectHandler = new DbProtectOpcode();
        $operate = $dbProtectHandler->getOpcodeDes($opName);
        $dataFileRet = $this->service()->loadDbDataFileService($appData[0], $params['table_space_name']);
        if (!$dataFileRet['result']) {
            $this->muOpResult(false, $operate, $dataFileRet['msg'], '', $dataFileRet['errorCode']);
            return $this->sendResult('failed', false, 0);  // 这一步执行不到，上一步直接exit了
        }
        $msg = $dataFileRet['msg'];
        return $this->sendResult('', true, 200, [
            'rows' => array_map(function ($row) {
                return [
                    'file_name' => $row['file_name'],
                    'file_id' => $row['file_id'],
                ];
            }, $msg['data_file_list']),
            'total' => count($msg['data_file_list']),
        ]);
    }

    /**
     * 获取所有数据库备份任务信息
     * @param int $dbType 数据库类别
     * @return array
     */
    private function getAllDbBackupJob(int $dbType): array
    {
        $setFlag = xphp_get_config('app', 'FLAG');
        $sql = "SELECT bt.task_name, bt.task_uuid, dt.db_type, dl.instance_name, dl.db_uuid, dl.db_name, dl.agent_uuid
                FROM bd_task bt
                    INNER JOIN db_task dt ON bt.task_uuid=dt.task_uuid
                    LEFT JOIN db_list dl ON bt.task_uuid=dl.task_uuid
                WHERE bt.task_uuid = dt.task_uuid AND dt.task_uuid = dl.task_uuid AND bt.task_type = ?
                    AND dt.db_type = ? ";
        $data = $this->dbSelect($sql, [xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'], $dbType]);
        $dbList = [];
        foreach ($data as $row) {
            // 查询集群信息
            $sql = "SELECT baa1.agent_uuid, baa1.cluster_uuid 
                    FROM bd_agent_app baa
                    	LEFT JOIN bd_agent_app baa1 ON baa.cluster_uuid = baa1.cluster_uuid 
                    WHERE baa.agent_uuid = ? AND baa.app_name = ? AND baa.cluster_flag = ? AND baa1.cluster_flag = ?
                        AND baa.cluster_uuid IS NOT NULL AND baa.cluster_uuid != ''
                        AND baa1.cluster_uuid IS NOT NULL AND baa1.cluster_uuid != '' ";
            $sqlParams = [$row['agent_uuid'], $row['instance_name'], $setFlag['SET'], $setFlag['SET']];
            $cluster = $this->dbSelect($sql, $sqlParams);
            $clusterAgentUuid = [];
            $clusterUuid = '';
            if (is_array($cluster) && $cluster) {
                foreach ($cluster as $agent) {
                    $clusterAgentUuid[] = $agent['agent_uuid'];
                    if ($agent['agent_uuid'] == $row['agent_uuid']) {
                        $clusterUuid = $agent['cluster_uuid'];
                    }
                }
            }
            $dbList[] = [
                'db_uuid' => $row['db_uuid'],
                'db_name' => $row['db_name'],
                'instance_name' => $row['instance_name'],
                'agent_uuid' => $row['agent_uuid'],
                'task_uuid' => $row['task_uuid'],
                'task_name' => $row['task_name'],
                'db_type' => (int) $row['db_type'],
                'cluster_uuid' => $clusterUuid,
                'cluster_agent_uuid' => $clusterAgentUuid,
            ];
        }

        return $dbList;
    }

    /**
     * 获取数据库备份任务信息
     * @param array $appData        应用信息
     * @param array $allDbBackupJob 数据库备份任务信息
     * @return array
     */
    private function getDbBackupInfo(array $appData, array $allDbBackupJob): array
    {
        // 如果没有备份,直接返回
        if (!$allDbBackupJob) {
            return [];
        }
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        // 遍历备份中的数据库信息，判断该数据库是否在备份中
        foreach ($allDbBackupJob as $dbBackupJob) {
            if (
                $appData['instance_name'] == $dbBackupJob['instance_name']
                && $appData['agent_uuid'] == $dbBackupJob['agent_uuid']
            ) {
                if ($allDbType['SQLSERVER'] == $appData['db_type'] || $allDbType['SAPHANA'] == $appData['db_type']) {
                    if ($appData['db_name'] == $dbBackupJob['db_name']) {
                        return $dbBackupJob;
                    }
                } else {
                    return $dbBackupJob;
                }
            }
        }
        return [];
    }

    /**
     * 获取到客户端所在集群的所有客户端uuid
     * @param string $agentUuid      客户端uuid
     * @param string $instanceName   实例名称
     * @param array  $allDbBackupJob 数据库备份信息
     * @return array
     */
    private function getClusterAgentUuid(string $agentUuid, string $instanceName, array $allDbBackupJob): array
    {
        foreach ($allDbBackupJob as $dbBackupJob) {
            if ($instanceName != $dbBackupJob['instance_name']) {
                continue;
            }
            if ($dbBackupJob['cluster_uuid']) { // 集群
                if (in_array($agentUuid, $dbBackupJob['cluster_agent_uuid'])) {  // 有集群
                    return $dbBackupJob['cluster_agent_uuid'];
                }
            } elseif ($agentUuid == $dbBackupJob['agentuuid']) { // 单机
                return [$agentUuid];
            }
        }
        return [];
    }

    /**
     * 判断应用集群里面有无数据库备份任务
     * @param array $appData        应用信息
     * @param array $dbBackupInfo   数据库备份任务
     * @param array $allDbBackupJob 数据库备份任务信息
     * @return array
     */
    private function getDbClusterBackupInfo(array $appData, array $dbBackupInfo, array $allDbBackupJob): array
    {
        if (!$allDbBackupJob || !v1_parse_flag_to_bool($appData['cluster_flag'])) {
            return [];
        }
        if (!$appData['cluster_uuid']) {
            return [];
        }
        if ($dbBackupInfo) {
            return $dbBackupInfo;
        }
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $clusterAgentUuidList = $this->getClusterAgentUuid(
            $appData['agent_uuid'],
            $appData['instance_name'],
            $allDbBackupJob
        );
        foreach ($allDbBackupJob as $dbBackupJob) {
            foreach ($clusterAgentUuidList as $clusterAgentUuid) {
                if (
                    $appData['instance_name'] == $dbBackupJob['instance_name']
                    && $dbBackupJob['agent_uuid'] == $clusterAgentUuid
                ) {
                    if (
                        $allDbType['SQLSERVER'] == $appData['db_type'] ||
                        $allDbType['SAPHANA'] == $appData['db_type']
                    ) {
                        if ($appData['db_name'] == $dbBackupJob['db_name']) {
                            return $dbBackupJob;
                        }
                    } else {
                        return $dbBackupJob;
                    }
                }
            }
        }
        return [];
    }

    /**
     * 构建数据库备份任务消息
     * @param array $params 参数
     * @return array
     */
    private function buildDbBackupJobMsg(array $params): array
    {
        // $sql = "SELECT * FROM bd_task WHERE task_name = ? AND task_uuid != ? ";
        // if ($this->dbSelect($sql, [htmlspecialchars_decode($params['job_name']), $params['jobs_uuid']])) {
        //     return $this->sendResult(
        //         sprintf(xphp_get_lang('WEB_COMMON_TASK_NAME_ALREADY_EXISTS'), htmlspecialchars_decode($params['job_name'])),
        //         false,
        //         0
        //     );
        // }

        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $dbType = intval($params['db_type']);
        $deleteArchivelogFlag = v1_parse_bool_to_flag(!!$params['advanced_strategy']['delete_archivelog_flag']);
        $detail = '';
        switch ($dbType) {
            case $allDbType['ORACLE']:
                $deleteArchivelogFlag = intval($params['advanced_strategy']['delete_archivelog_flag']);
                break;
            case $allDbType['POSTGRE']:
            case $allDbType['ANTDB']:
            case $allDbType['KINGBASE']:
            case $allDbType['UXDB']:
            case $allDbType['HIGHGO']:
            case $allDbType['OPENGAUSS']:
            case $allDbType['VASTBASE']:
                $deleteArchivelogFlag = intval($params['advanced_strategy']['delete_archivelog_flag']);
                break;
        }
        $compressFlag = v1_parse_bool_to_flag(!!$params['advanced_strategy']['compress_flag']);
        switch ($dbType) {
            case $allDbType['POSTGRE']:
            case $allDbType['ANTDB']:
            case $allDbType['KINGBASE']:
            case $allDbType['OPENGAUSS']:
            case $allDbType['VASTBASE']:
            case $allDbType['UXDB']:
            case $allDbType['HIGHGO']:
                $compressFlag = intval($params['advanced_strategy']['compress_flag']);
                // postgres系统detail为告警配置
                if ($params['advanced_strategy']['warning_setting']['warn_check']) {
                    if ($params['advanced_strategy']['warning_setting']['warn_type'] == xphp_get_config('db', 'WARN_TYPE', 'db')['size']) {
                        // 传入的是Bit，无需额外处理
                        $params['advanced_strategy']['warning_setting']['warn_value'] = intval($params['advanced_strategy']['warning_setting']['warn_value']);
                    }
                }
                $detail = json_encode($params['advanced_strategy']['warning_setting']);
                break;
        }
        // 传输网络格式化
        $params['transport_strategy']['network_uuid'] = $this->buildNetworkUuid(
            $params['backup_target']['node_uuid'] ?: '',
            $params['transport_strategy']['network_uuid'] ?: ''
        );
        // 包略策略的保留模式
        $reservedMode = xphp_get_config('system', 'RESERVED_STRATEGY_MODE')['CHAIN'];
        if ($dbType == $allDbType['MONGODB']) {
            $reservedMode = $params['reserved_strategy']['reserved_mode'];
        }
        $backupDbsInfo = $this->buildDbBackupDbsInfo($params['backup_source'], $dbType, $params['jobs_uuid'] ?: '');
        $backupAgentUuidMap = [];
        foreach ($backupDbsInfo as $dbInfo) {
            $backupAgentUuidMap[$dbInfo['agent_uuid']] = $dbInfo['agent_uuid'];
        }
        $backupAgentUuidMap = array_values($backupAgentUuidMap);
        if (count($backupAgentUuidMap) > 1) {
            // 多主机备份不显示传输网络
            $params['transport_strategy']['network_uuid'] = '';
        }
        return [
            'task_uuid' => $params['jobs_uuid'] ?: '',
            'task_name' => htmlspecialchars_decode(v1_remove_escape($params['task_name'])),
            'sub_task_name' => htmlspecialchars_decode(v1_remove_escape($params['sub_task_name'] ?: '')),
            'depend_task_uuid' => $params['depend_task_uuid'] ?: '',
            'module_type' => xphp_get_config('module', 'MODULE_TYPE')['DB'],
            'task_type' => xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'],
            'db_type' => $dbType,
            'auto_find_sr_flag' => v1_parse_bool_to_flag(false),
            'node_uuid' => $params['backup_target']['node_uuid'] ?: '',
            'node_pool_uuid' => $params['backup_target']['node_pool_uuid'] ?: '',
            'storage_uuid' => $params['backup_target']['storage_uuid'] ?: '',
            'storage_pool_uuid' => $params['backup_target']['storage_pool_uuid'] ?: '',
            'agent_uuid' => '',
            'agent_pool_uuid' => '',
            'time_strategy_backup_type' => xphp_get_config('task', 'TIME_STRATEGY_BACKUP_TYPE')[$params['time_strategy']['type']],
            'backup_dbs_info' => $backupDbsInfo,
            'time_strategy_list' => $this->groupBackupTimeList($params['time_strategy'], $params['strategy_group_uuid']),
            'reserved_strategy' => $this->pfReservedStrategyMessage(
                $params['reserved_strategy']['reserved_type'],
                $params['reserved_strategy']['value'],
                v1_parse_bool_to_flag(false),
                '',
                $reservedMode,
                $params['reserved_strategy']['enable_flag']
            ),
            'transport_strategy' => $this->getTransportStrategyMessage(
                v1_parse_bool_to_flag($params['transport_strategy']['encrypt_flag']),
                v1_parse_bool_to_flag(false),
                v1_parse_bool_to_flag(false),
                0,
                $params['transport_strategy']['network_uuid'] ?: '',
                '',
                0,
                $params['transport_strategy']['encrypt_method'] ?: '',
                v1_parse_bool_to_flag(false),
                '',
                $params['transport_strategy']['network_pool_uuid'] ?: '',
                $params['transport_strategy']['agent_uuid'] ?: '',
                $params['transport_strategy']['agent_pool_uuid'] ?: '',
                $params['transport_strategy']['max_object_transport_parallel_nums'] ?: 0
            ),
            'storage_strategy' => $this->groupStorageStrategy($params['storage_strategy'], $params['strategy_group_uuid']),
            'speed_limit_strategy_list' => [],
            'speed_limit_strategy' => $this->groupTaskSpeedGlobalList($params['speed_strategy']),
            'auto_log_backup_interval' => intval($params['advanced_strategy']['auto_log_backup_interval']),
            'transport_priority' => intval($params['transport_strategy']['transport_mode']),
            'backup_level' => 0,
            'check_db_flag' => v1_parse_bool_to_flag($params['advanced_strategy']['check_db_flag']),
            'checksum_flag' => v1_parse_bool_to_flag($params['advanced_strategy']['checksum_flag']),
            'channel_count' => intval($params['advanced_strategy']['channel_count']),
            'thread_num' => intval($params['advanced_strategy']['thread_num']),
            'last_archive_days' => intval($params['advanced_strategy']['archive_num']),
            'delete_archive_log_flag' => $deleteArchivelogFlag,
            'compress_flag' => $compressFlag,
            'compress_method' => intval($params['advanced_strategy']['compress_method']),
            'compress_level' => intval($params['advanced_strategy']['compress_level']),
            'strategy_group_uuid' => $params['strategy_group_uuid'],
            'detail' => $detail,
            'set_filesperset_flag' => v1_parse_bool_to_flag($params['advanced_strategy']['set_filesperset_flag']),
            'datafile_filesperset_num' => intval($params['advanced_strategy']['datafile_filesperset_num']),
            'archivelog_filesperset_num' => intval($params['advanced_strategy']['archivelog_filesperset_num']),
            'log_backup_days_flag' => v1_parse_bool_to_flag($params['advanced_strategy']['log_backup_days_flag']),
            'log_backup_days' => intval($params['advanced_strategy']['log_backup_days']),
            'log_backup_times_flag' => v1_parse_bool_to_flag($params['advanced_strategy']['log_backup_times_flag']),
            'log_backup_times' => intval($params['advanced_strategy']['log_backup_times']),
            'skip_inaccessible_file_flag' => v1_parse_bool_to_flag($params['advanced_strategy']['skip_inaccessible_file_flag']),
            'skip_offline_file_flag' => v1_parse_bool_to_flag($params['advanced_strategy']['skip_offline_file_flag']),
            'enable_bct_flag' => v1_parse_bool_to_flag($params['advanced_strategy']['enable_bct_flag']),
            'set_section_size_flag' => intval($params['advanced_strategy']['set_section_size_flag']),
            'section_size' => intval($params['advanced_strategy']['section_size']),
            'multi_task_flag' => v1_parse_bool_to_flag($params['advanced_strategy']['multi_task_flag']),
            'ignore_resource_limiting_flag' => v1_parse_bool_to_flag($params['advanced_strategy']['ignore_resource_limiting_flag']),
            'custom_rman_cmd' => $params['advanced_strategy']['custom_rman_cmd'],
            'retry_strategy' => $this->groupRetryStrategy($params['retry_strategy']),
            'safe_config_strategy' => $this->groupSafeConfigStrategy($params['safe_config_strategy'], $params['backup_target']['storage_uuid']),
            'gfs_strategy_item_list' => $params['reserved_strategy']['gfs_strategy_item_list'],
        ];
    }

    /**
     * @param array $dbBackupInfo  备份任务信息
     * @param array $fullBackupMsg 完备任务消息
     * @return array
     */
    private function buildSyncDbBackupJobMsg(array $dbBackupInfo, array $fullBackupMsg): array
    {
        // 查询主任务信息
        $sql = "SELECT dt.task_uuid
                FROM db_list dl
                    INNER JOIN db_task dt ON dt.task_uuid = dl.task_uuid
                WHERE dl.db_name = ? AND dl.agent_uuid = ? AND dl.instance_name = ?
                    AND dt.multi_task_flag = ? AND dt.db_type = ? ";
        $masterBackupDbsInfo = $fullBackupMsg['backup_dbs_info'];
        $masterTaskData = $this->dbSelect($sql, [
            $masterBackupDbsInfo[0]['db_name'],
            $masterBackupDbsInfo[0]['agent_uuid'],
            $masterBackupDbsInfo[0]['instance_name'],
            xphp_get_config('app')['FLAG']['UNSET'],
            xphp_get_config('db', 'DB_TYPE')['ORACLE']
        ]);
        $logTimeStrategy = $dbBackupInfo['time_strategy']['strategy'][0];
        $timeStrategy = [
            'type' => 'manual',
        ];
        if ($dbBackupInfo['time_strategy']['backup_type'] == xphp_get_config('task', 'TIME_STRATEGY_BACKUP_TYPE')['strategy']) {
            $logTimeStrategy['days'] = $logTimeStrategy['week_days'];
            $timeStrategy = [
                'type' => 'strategy',
                'log_info' => [
                    'mode' => $logTimeStrategy['backup_mode'],
                    'type' => $logTimeStrategy['time_type'],
                    'start_time' => $logTimeStrategy['start_time'],
                    'roll_flag' => $logTimeStrategy['is_rollback'],
                    'roll_interval' => $logTimeStrategy['roll_interval'],
                    'end_time' => $logTimeStrategy['roll_end_time'],
                    'roll_end_time' => $logTimeStrategy['roll_end_time'],
                    'days' => $logTimeStrategy['days'],
                ],
            ];
        }
        $networkUuid = $dbBackupInfo['transport_strategy']['network_uuid'];
        $networkPoolUuid = $dbBackupInfo['transport_strategy']['network_pool_uuid'];
        if ($fullBackupMsg['node_uuid'] != $dbBackupInfo['backup_target']['node_uuid']) {
            $networkUuid = $fullBackupMsg['transport_strategy']['network_uuid'];
            $networkPoolUuid = $fullBackupMsg['transport_strategy']['network_pool_uuid'];
        }
        $safeConfigStrategy = $fullBackupMsg['safe_config_strategy'];
        $safeConfigStrategy['integrity_check_config']['full_error_policy'] = xphp_get_config('safe', 'INTEGRITY_FULL_ERROR_POLICY')['STOPBACKUP'];  // 归档日志任务只有中止备份
        return [
            'task_uuid' => $dbBackupInfo['job_uuid'],
            'task_name' => htmlspecialchars_decode(v1_remove_escape($dbBackupInfo['job_name'])),
            'sub_task_name' => '',
            'depend_task_uuid' => $masterTaskData[0]['task_uuid'],
            'module_type' => xphp_get_config('module', 'MODULE_TYPE')['DB'],
            'task_type' => xphp_get_config('task', 'TASKTYPE')['DB_BACKUP'],
            'db_type' => xphp_get_config('db', 'DB_TYPE')['ORACLE'],
            'auto_find_sr_flag' => v1_parse_bool_to_flag(false),
            'node_uuid' => $fullBackupMsg['node_uuid'],
            'node_pool_uuid' => $fullBackupMsg['node_pool_uuid'],
            'storage_uuid' => $fullBackupMsg['storage_uuid'],
            'storage_pool_uuid' => $fullBackupMsg['storage_pool_uuid'],
            'agent_uuid' => '',
            'agent_pool_uuid' => '',
            'time_strategy_backup_type' => $dbBackupInfo['time_strategy']['backup_type'],
            'backup_dbs_info' => $this->buildDbBackupDbsInfo($dbBackupInfo['backup_source'], $dbBackupInfo['db_type'], $dbBackupInfo['job_uuid']),
            'time_strategy_list' => $this->groupBackupTimeList($timeStrategy, $dbBackupInfo['strategy_group_uuid']),
            'reserved_strategy' => $fullBackupMsg['reserved_strategy'],
            'transport_strategy' => $this->getTransportStrategyMessage(
                v1_parse_bool_to_flag($dbBackupInfo['transport_strategy']['encrypt_flag']),
                v1_parse_bool_to_flag(false),
                v1_parse_bool_to_flag(false),
                0,
                $networkUuid,
                '',
                0,
                $dbBackupInfo['transport_strategy']['encrypt_method'],
                v1_parse_bool_to_flag(false),
                '',
                $networkPoolUuid,
                '',
                '',
                $dbBackupInfo['transport_strategy']['max_object_transport_parallel_nums']
            ),
            'storage_strategy' => $fullBackupMsg['storage_strategy'],
            'speed_limit_strategy_list' => [],
            'speed_limit_strategy' => $this->groupTaskSpeedGlobalList($dbBackupInfo['speed_strategy']),
            'auto_log_backup_interval' => intval($dbBackupInfo['advanced_strategy']['auto_log_backup_interval']),
            'transport_priority' => intval($dbBackupInfo['transport_strategy']['transport_mode']),
            'backup_level' => 0,
            'check_db_flag' => v1_parse_bool_to_flag($dbBackupInfo['advanced_strategy']['check_db_flag']),
            'checksum_flag' => v1_parse_bool_to_flag($dbBackupInfo['advanced_strategy']['check_sum_flag']),
            'channel_count' => intval($dbBackupInfo['advanced_strategy']['channel_count']),
            'thread_num' => intval($dbBackupInfo['advanced_strategy']['thread_num']),
            'last_archive_days' => intval($dbBackupInfo['advanced_strategy']['archive_num']),
            'delete_archive_log_flag' => intval($dbBackupInfo['advanced_strategy']['delete_archivelog_flag']),
            'compress_flag' => v1_parse_bool_to_flag($dbBackupInfo['advanced_strategy']['compress_flag']),
            'compress_method' => intval($dbBackupInfo['advanced_strategy']['compress_method']),
            'compress_level' => intval($dbBackupInfo['advanced_strategy']['compress_level']),
            'strategy_group_uuid' => $dbBackupInfo['strategy_group_uuid'],
            'detail' => '',
            'set_filesperset_flag' => v1_parse_bool_to_flag($dbBackupInfo['advanced_strategy']['set_filesperset_flag']),
            'datafile_filesperset_num' => intval($dbBackupInfo['advanced_strategy']['datafile_filesperset_num']),
            'archivelog_filesperset_num' => intval($dbBackupInfo['advanced_strategy']['archivelog_filesperset_num']),
            'log_backup_days_flag' => v1_parse_bool_to_flag($dbBackupInfo['advanced_strategy']['log_backup_days_flag']),
            'log_backup_days' => intval($dbBackupInfo['advanced_strategy']['log_backup_days']),
            'log_backup_times_flag' => v1_parse_bool_to_flag($dbBackupInfo['advanced_strategy']['log_backup_times_flag']),
            'log_backup_times' => intval($dbBackupInfo['advanced_strategy']['log_backup_times']),
            'skip_inaccessible_file_flag' => v1_parse_bool_to_flag($dbBackupInfo['advanced_strategy']['skip_inaccessible_file_flag']),
            'skip_offline_file_flag' => v1_parse_bool_to_flag($dbBackupInfo['advanced_strategy']['skip_offline_file_flag']),
            'enable_bct_flag' => v1_parse_bool_to_flag($dbBackupInfo['advanced_strategy']['enable_bct_flag']),
            'set_section_size_flag' => intval($dbBackupInfo['advanced_strategy']['set_section_size_flag']),
            'section_size' => intval($dbBackupInfo['advanced_strategy']['section_size']),
            'multi_task_flag' => v1_parse_bool_to_flag($dbBackupInfo['advanced_strategy']['multi_task_flag']),
            'ignore_resource_limiting_flag' => v1_parse_bool_to_flag($dbBackupInfo['advanced_strategy']['ignore_resource_limiting_flag']),
            'custom_rman_cmd' => $dbBackupInfo['advanced_strategy']['custom_rman_cmd'],
            'retry_strategy' => $this->groupRetryStrategy($dbBackupInfo['retry_strategy']),
            'safe_config_strategy' => $safeConfigStrategy,
            'gfs_strategy_item_list' => $fullBackupMsg['gfs_strategy_item_list'],
        ];
    }

    /**
     * 同步数据库备份任务
     * @param array $fullBackupMsg      完备任务消息
     * @param array $backupTaskInfoList 备份任务信息
     * @return array
     */
    private function syncDbBackupJob(array $fullBackupMsg, array $backupTaskInfoList): array
    {
        if ($fullBackupMsg['db_type'] != xphp_get_config('db', 'DB_TYPE')['ORACLE']) {
            return $this->sendResult('');
        }
        if ($fullBackupMsg['multi_task_flag'] == v1_parse_bool_to_flag(true)) {  // 归档日志任务不需要同步配置给主任务
            return $this->sendResult('');
        }

        if (!$backupTaskInfoList) {
            return $this->sendResult('');
        }

        $backupTaskInfo = null;
        foreach ($backupTaskInfoList as $backupInfo) {
            if ($backupInfo['multi_task_flag'] == v1_parse_bool_to_flag(true)) {
                $backupTaskInfo = $backupInfo;
                break;
            }
        }
        if (!$backupTaskInfo) {
            return $this->sendResult('');
        }

        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $operate = $this->pfOpcode->getOpcodeDes($opName);
        $dbBackupInfo = $this->getDbBackupJob($backupTaskInfo['task_uuid']);
        $msg = $this->buildSyncDbBackupJobMsg($dbBackupInfo['data'], $fullBackupMsg);
        $editResult = $this->service()->editDbBackupJobService($msg);
        if (!$editResult['result']) {
            $message = xphp_get_lang('WEB_DB_BACKUP_SYNC_ASSOCIATED_TASK_CONFIG') . xphp_get_lang('WEB_PUBLIC_FAILURE');
            $this->muOpResult(false, $operate, $editResult['msg'], 0, $editResult['errorCode']);
            return $this->sendResult($message, false, 0);
        }

        $message = xphp_get_lang('WEB_DB_BACKUP_SYNC_ASSOCIATED_TASK_CONFIG') . xphp_get_lang('WEB_PUBLIC_SUCCESS');
        return $this->sendResult($message);
    }

    /**
     * 根据数据库信息获取备份任务信息
     * @param array $backupDbsInfo 备份数据库信息
     * @param int   $dbType        数据库类型
     * @return array
     */
    private function getBackupTaskByBackupDbsInfo(array $backupDbsInfo, int $dbType): array
    {
        $sql = "SELECT bt.task_uuid, bt.task_name, bt.task_status,
                    dt.multi_task_flag, dt.db_type,
                    dl.instance_name, dl.db_name, dl.agent_uuid
                FROM db_task dt
                    INNER JOIN db_list dl ON dt.task_uuid = dl.task_uuid
                    INNER JOIN bd_task bt ON dt.task_uuid = bt.task_uuid
                WHERE dt.db_type = ? ";
        $instanceList = [];
        foreach ($backupDbsInfo as $dbInfo) {
            $instanceList[] = " ( dl.agent_uuid = '{$dbInfo['agent_uuid']}' AND dl.instance_name = '{$dbInfo['instance_name']}' AND dl.db_name = '{$dbInfo['db_name']}' ) ";
        }
        $instanceDes = ' ( ' . implode(' OR ', $instanceList) . ' ) ';
        $sql .= " AND $instanceDes ";
        $data = $this->dbSelect($sql, [$dbType]);
        if (!is_array($data)) {
            return [];
        }
        $backupTaskInfo = [];
        foreach ($data as $row) {
            $backupTaskInfo[] = [
                'task_name' => $row['task_name'],
                'task_uuid' => $row['task_uuid'],
                'task_status' => $row['task_status'],
                'multi_task_flag' => $row['multi_task_flag'],
                'db_type' => $row['db_type'],
                'instance_name' => $row['instance_name'],
                'db_name' => $row['db_name'],
                'agent_uuid' => $row['agent_uuid'],
            ];
        }
        return $backupTaskInfo;
    }

    /**
     * 创建数据库恢复任务
     * @param array $params 请求参数
     * @return array
     */
    public function createDbBackupJob(array $params): array
    {
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            return $this->sendResult(xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED'), false, 0);
        }
        $dbType = intval($params['db_type']);
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        $operate = $this->pfOpcode->getOpcodeDes($opName);

        $msg = $this->buildDbBackupJobMsg($params);
        $checkRet = $this->checkDbTaskLegal($msg['backup_dbs_info'], $dbType);
        if (false === $checkRet['success']) {
            return $checkRet;
        }
        $backupTaskInfoList = $this->getBackupTaskByBackupDbsInfo($msg['backup_dbs_info'], $dbType);
        if ($dbType == xphp_get_config('db', 'DB_TYPE')['ORACLE']) {  // 判断任务是否处于停止状态
            if ($backupTaskInfoList) {
                foreach ($backupTaskInfoList as $backupInfo) {
                    if ($backupInfo['task_status'] != xphp_get_config('task', 'TASKSTATUS')['STOPPED']) {
                        return $this->sendResult(
                            sprintf(xphp_get_lang('WEB_DB_BACKUP_ASSOCIATED_TASK_NOT_STOPPED'), $backupInfo['task_name']),
                            false,
                            0
                        );
                    }
                }
            }
        }
        $createResult = $this->service()->createDbBackupJobService($msg);
        if (!$createResult['result']) {
            $message = xphp_get_lang('UI_DB_ADD_NEW_BACKUP_JOB') . xphp_get_lang('WEB_PUBLIC_FAILURE');
            $this->muOpResult(false, $operate, $createResult['msg'], 0, $createResult['errorCode']);
            return $this->sendResult($message, false, 0);
        }

        // 同步备份任务信息
        $syncRet = $this->syncDbBackupJob($msg, $backupTaskInfoList);
        if (false === $syncRet['success']) {
            return $syncRet;
        }

        $message = xphp_get_lang('UI_DB_ADD_NEW_BACKUP_JOB') . xphp_get_lang('WEB_PUBLIC_SUCCESS');
        return $this->sendResult($message);
    }

    /**
     * 判断数据库是否合法
     * @param array   $dbInfo  数据库信息
     * @param int     $dbType  数据库类型
     * @param ?string $jobUuid 数据库备份任务uuid
     * @return array
     */
    private function checkDbTaskLegal(array $dbInfo, int $dbType, string $jobUuid = ''): array
    {
        $systemLicenseInfo = json_decode((new Index())->getSystemLisenceInfo(), true);
        if (!$systemLicenseInfo['status']) {
            return $this->sendResult($systemLicenseInfo['statusDes'], false, 0);
        }
        // 租户验证
        if ($_SESSION['tenantuuid']) {
            $tenantHandler = new Tenant();
            $tenantHandler->checkTenantAuth(
                xphp_get_config('module', 'MODULE_TYPE')['DB'],
                $dbInfo,
                $jobUuid
            );
        }
        // 检查数据库是否已经建了备份任务
        if ($dbType == xphp_get_config('db', 'DB_TYPE')['ORACLE'])  {  // Oracle可以建立多个备份任务
            return $this->sendResult('');
        }
        $allDbBackupJob = $this->getAllDbBackupJob($dbType);
        foreach ($allDbBackupJob as $backupJob) {
            foreach ($dbInfo as $db) {
                if (
                    $backupJob['db_name'] == $db['db_name']
                    && $backupJob['instance_name'] == $db['instance_name']
                    && $backupJob['agent_uuid'] == $db['agent_uuid']
                    && $jobUuid != $backupJob['task_uuid']
                ) {
                    return $this->sendResult(xphp_get_lang('WEB_DB_BACKUP_ALREADY_IN_TASK'), false, 0);
                }
            }
        }
        return $this->sendResult('');
    }

    /**
     * 构建备份源信息
     * @param array  $backupSource 备份源
     * @param int    $dbType       数据库类型
     * @param string $jobsUuid     任务uuid
     * @return array
     */
    private function buildDbBackupDbsInfo(array $backupSource, int $dbType, string $jobsUuid = ''): array
    {
        $dbInfoMap = [];
        if ($jobsUuid) {
            $sql = "SELECT db_uuid, error_code FROM db_list WHERE task_uuid = ? ";
            $dbInfoData = $this->dbSelect($sql, [$jobsUuid]);
            $dbInfoMap = [];
            if (is_array($dbInfoData)) {
                foreach ($dbInfoData as $dbInfo) {
                    $dbInfoMap[$dbInfo['db_uuid']] = $dbInfo;
                }
            }
        }
        $backupDbsInfo = [];
        foreach ($backupSource as $backupDbInfo) {
            $detail = '';
            if ($dbType == xphp_get_config('db', 'DB_TYPE')['ORACLE']) {
                $detail = json_encode($backupDbInfo['detail'], JSON_UNESCAPED_SLASHES);
            }
            $backupDbsInfo[] = [
                'group_uuid' => $backupDbInfo['agent_group_uuid'],
                'agent_uuid' => $backupDbInfo['agent_uuid'],
                'instance_name' => $backupDbInfo['instance_name'],
                'db_name' => $backupDbInfo['db_name'],
                'db_uuid' => $backupDbInfo['db_uuid'],
                'error_code' => isset($dbInfoMap[$backupDbInfo['db_uuid']]) ? $dbInfoMap[$backupDbInfo['db_uuid']]['error_code'] : 0,
                'dir_path' => $backupDbInfo['dir_path'],
                'data_file_path' => '',
                'log_file_path' => '',
                'before_task_script' => $backupDbInfo['before_task_script'] ? json_encode($backupDbInfo['before_task_script']) : '',
                'after_task_script' => $backupDbInfo['after_task_script'] ? json_encode($backupDbInfo['after_task_script']) : '',
                'detail' => $detail,
            ];
        }
        return $backupDbsInfo;
    }

    /**
     * 传输策略消息
     * @param int    $encryptflag       加密flag
     * @param int    $compressflag      压缩flag
     * @param int    $speedlimitflag    限速flag
     * @param int    $maxspeed          最大速度
     * @param int    $networkuuid       网关uuid
     * @param string $strategygroupuuid 资源分组uuid
     * @param int    $appliance_agency_flag 传输代理flag
     * @param string $appliance_uuid 传输代理uuid
     * @return array $transportStrategyMessage
     */
    public function getTransportStrategyMessage(
        $encryptflag,
        $compressflag,
        $speedlimitflag,
        $maxspeed,
        $networkuuid,
        $strategygroupuuid,
        $compress_method,
        $encrypt_method,
        $appliance_agency_flag,
        $appliance_uuid,
        string $networkPoolUuid = '',
        $agentUuid = '',
        $agentPoolUuid = '',
        $maxParallelNums = 0
    ): array {
        return array(
            'encrypt_flag' => $encryptflag,
            'compress_flag' => $compressflag,
            'speed_limit_flag' => $speedlimitflag,
            'max_speed' => $maxspeed,
            'network_uuid' => $networkuuid,
            'network_pool_uuid' => $networkPoolUuid,
            'agent_uuid' => $agentUuid,
            'agent_pool_uuid' => $agentPoolUuid,
            'strategy_group_uuid' => $strategygroupuuid,
            'compress_method' => $compress_method,
            'encrypt_method' => $encrypt_method,
            'max_object_transport_parallel_nums' => $maxParallelNums,
            'appliance_agency_flag' => $appliance_agency_flag,
            'appliance_uuid' => $appliance_uuid
        );
    }

    /**
     * 构建存储策略
     * @param array $storage 存储策略
     * @return array
     */
    public function groupStorageStrategy(array $storage, $strategyGroupUuid = ''): array
    {
        $compressMethod = intval($storage['compress_method']);
        $encryptMethod = intval($storage['encrypt_method']);
        if (!$storage['compress_flag']) {  // 未开启压缩，默认使用1
            $compressMethod = 1;
        }
        if (!$storage['encrypt_flag']) {  // 未开启加密，默认使用1
            $encryptMethod = 1;
        }
        return [
            'deduplication_flag' => v1_parse_bool_to_flag($storage['deduplication_flag']),
            'block_size' => intval($storage['block_size']) * 1024,
            'encrypt_flag' => v1_parse_bool_to_flag($storage['encrypt_flag']),
            'password_auto_flag' => v1_parse_bool_to_flag($storage['auto_password_flag']),
            'password' => $storage['password'],
            'compress_flag' => v1_parse_bool_to_flag($storage['compress_flag']),
            'compress_method' => $compressMethod,
            'encrypt_method' => $encryptMethod,
            'strategy_group_uuid' => $strategyGroupUuid,
        ];
    }

    /**
     * 修改数据库备份任务
     * @param array $params 请求参数
     * @return array
     */
    public function editDbBackupJob(array $params): array
    {
        $expireDate = v1_license_get_expire_days();
        if ($expireDate['expire_days'] < 0) {
            return $this->sendResult(xphp_get_lang('UI_LICENSE_AUTH_INFO_EXPIRED'), false, 0);
        }
        // 检查备份任务是否支持修改
        $checkRet = $this->checkDbTaskUpdateEnable($params['jobs_uuid']);
        if (false === $checkRet['success']) {
            return $checkRet;
        }
        $dbType = intval($params['db_type']);
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        $operate = $this->pfOpcode->getOpcodeDes($opName);

        $msg = $this->buildDbBackupJobMsg($params);
        $backupTaskInfoList = $this->getBackupTaskByBackupDbsInfo($msg['backup_dbs_info'], $dbType);
        if ($dbType == xphp_get_config('db', 'DB_TYPE')['ORACLE']) {  // 判断任务是否处于停止状态
            if ($backupTaskInfoList) {
                foreach ($backupTaskInfoList as $backupInfo) {
                    if ($backupInfo['task_status'] != xphp_get_config('task', 'TASKSTATUS')['STOPPED']) {
                        return $this->sendResult(
                            sprintf(xphp_get_lang('WEB_DB_BACKUP_ASSOCIATED_TASK_NOT_STOPPED'), $backupInfo['task_name']),
                            false,
                            0
                        );
                    }
                }
            }
        }
        $editResult = $this->service()->editDbBackupJobService($msg);
        if (!$editResult['result']) {
            $message = xphp_get_lang('UI_DB_MODIFY_BACKUP_JOB') . xphp_get_lang('WEB_PUBLIC_FAILURE');
            $this->muOpResult(false, $operate, $editResult['msg'], 0, $editResult['errorCode']);
            return $this->sendResult($message, false, 0);
        }

        // 同步备份任务信息
        $syncRet = $this->syncDbBackupJob($msg, $backupTaskInfoList);
        if (false === $syncRet['success']) {
            return $syncRet;
        }

        $message = xphp_get_lang('UI_DB_MODIFY_BACKUP_JOB') . xphp_get_lang('WEB_PUBLIC_SUCCESS');
        return $this->sendResult($message);
    }

    /**
     * 判断任务是否可修改
     * @param string $jobsUuid 任务uuid
     * @return array
     */
    private function checkDbTaskUpdateEnable(string $jobsUuid): array
    {
        $allTaskStatus = xphp_get_config('task', 'TASKSTATUS');
        $jobInfo = $this->checkTaskExists($jobsUuid, [xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']]);
        if (false === $jobInfo) {  // 检查任务是否存在
            return $this->sendResult(xphp_get_lang('WEB_DB_BACKUP_TASK_NOT_EXISTS'), false, 0);
        }
        if ($allTaskStatus['STOPPED'] !== (int) $jobInfo['task_status']) {  // 检查任务是否处于停止状态
            return $this->sendResult(xphp_get_lang('WEB_DB_BACKUP_TASK_NOT_STOPPED'), false, 0);
        }
        // 一次性备份的备份时间已经在验证器里面验证了
        return $this->sendResult('');
    }

    /**
     * 获取数据库备份任务
     * @param string $jobsUuid                数据库备份任务uuid
     * @param bool   $withEncryptPasswordFlag 是否携带加密密码
     * @return array
     */
    public function getDbBackupJob(string $jobsUuid, bool $withEncryptPasswordFlag = false): array
    {
        // 判断备份任务是否存在
        $jobInfo = $this->checkTaskExists($jobsUuid, [xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']]);
        if (false === $jobInfo) {
            return $this->sendResult(xphp_get_lang('WEB_DB_BACKUP_TASK_NOT_EXISTS'), false, 0);
        }

        $dbTaskInfo = $this->dbSelect("SELECT * FROM db_task WHERE task_uuid = ? ", [$jobInfo['task_uuid']])[0];

        $backupSource = $this->getDbBackupTaskBackupSource($jobsUuid);
        $backupTarget = $this->getDbBackupTaskBackupTarget($jobInfo);

        $associatedJobInfo = $this->getDbAssociatedJobInfo($dbTaskInfo);
        $timeStrategy = $this->getDbBackupTaskTimeStrategy((int) $jobInfo['strategy_id']);
        $speedStrategy = $this->getSpeedStrategy($jobsUuid);
        $storageStrategy = $this->getDbBackupTaskStorageStrategy((int) $jobInfo['strategy_id'], $withEncryptPasswordFlag);
        $reservedStrategy = $this->getDbBackupTaskReservedStrategy((int) $jobInfo['strategy_id'], $jobsUuid);
        $transportStrategy = $this->getDbBackupTaskTransportStrategy($jobInfo);
        $advancedStrategy = $this->getDbBackupTaskAdvancedStrategy($jobInfo, $dbTaskInfo);

        return $this->sendResult('', true, 200, [
            'job_name' => $jobInfo['task_name'],
            'job_uuid' => $jobInfo['task_uuid'],
            'job_status' => $jobInfo['task_status'],
            'db_type' => intval($dbTaskInfo['db_type']),
            'associated_job_info' => $associatedJobInfo,
            'backup_source' => $backupSource,
            'backup_target' => $backupTarget,
            'time_strategy' => $timeStrategy,
            'speed_strategy' => $speedStrategy,
            'storage_strategy' => $storageStrategy,
            'reserved_strategy' => $reservedStrategy,
            'transport_strategy' => $transportStrategy,
            'advanced_strategy' => $advancedStrategy,
            // 重试策略
            'retry_strategy' => ExchangeJobInfo::instance()->getRetryStrategy($jobsUuid),
            // 安全策略
            'safe_config_strategy' => $this->getSafeConfigStrategy($jobsUuid),
            'strategy_group_uuid' => $jobInfo['strategy_group_uuid'],  // 策略组uuid
        ]);
    }

    /**
     * 格式化脚本配置
     * @param array $scriptConfig 脚本配置
     * @return array|null
     */
    private function formatScriptConfig(array $scriptConfig)
    {
        if (!$scriptConfig) {
            return null;
        }
        foreach ($scriptConfig as $key => $item) {
            $scriptConfig[$key]['script_method'] = intval($item['script_method']);
            $scriptConfig[$key]['script_type'] = intval($item['script_type']);
        }
        return $scriptConfig;
    }

    /**
     * 获取备份任务的备份源信息
     * @param string $jobUuid 备份任务uuid
     * @return array
     */
    private function getDbBackupTaskBackupSource(string $jobUuid): array
    {
        // 备份任务的代理一定存在的
        $sql = "SELECT dl.db_name, dl.instance_name, dl.dir_path, dl.db_uuid, dl.detail,
                    dl.before_task_script, dl.after_task_script, dl.agent_uuid,
                    baa.cluster_flag, baa.cluster_uuid, baa.app_type,
                    ba.group_uuid
                FROM db_list dl
                    LEFT JOIN bd_agent_app baa ON baa.agent_uuid = dl.agent_uuid AND dl.instance_name = baa.app_name
                    LEFT JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                WHERE dl.task_uuid = ? ";
        $data = $this->dbSelect($sql, [$jobUuid]);
        if (!is_array($data)) {
            $data = [];
        }
        $sql = "SELECT cluster_uuid, cluster_flag, agent_uuid, app_name, app_type
                FROM bd_agent_app
                WHERE 1 = 1 ";
        $allClusterUuidList = array_values(array_unique(array_column($data, 'cluster_uuid')));
        $allClusterUuids = "'" . implode("','", $allClusterUuidList) . "'";
        $allAgentUuidList = array_values(array_unique(array_column($data, 'agent_uuid')));
        $allAgentUuids = "'" . implode("','", $allAgentUuidList) . "'";
        if ($allAgentUuidList && $allClusterUuidList) {
            // 获取所有备份任务的数据库实例信息
            $sql .= " AND (agent_uuid IN ($allAgentUuids) OR cluster_uuid IN ($allClusterUuids)) ";
        } elseif ($allAgentUuidList) {
            $sql .= " AND agent_uuid IN ($allAgentUuids) ";
        } elseif ($allClusterUuidList) {
            $sql .= " AND cluster_uuid IN ($allClusterUuids) ";
        }
        $agentAppData = $this->dbSelect($sql);
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $backupSource = [];
        foreach ($data as $row) {
            $detail = new stdClass();
            if ($row['app_type'] == $allDbType['ORACLE']) {
                $detail = json_decode($row['detail'], true);
                $detail = [
                    'skip_datafiles_bad_block_info' => $detail['skip_datafiles_bad_block_info'] ?? [],
                ];
            }
            $clusterFlag = v1_parse_flag_to_bool($row['cluster_flag']) && $row['cluster_uuid'];
            $agentUuidList = [$row['agent_uuid']];
            if ($clusterFlag) {
                $agentUuidList = [];
                foreach ($agentAppData as $agentAppRow) {
                    if ($agentAppRow['cluster_uuid'] == $row['cluster_uuid']) {
                        $agentUuidList[] = $agentAppRow['agent_uuid'];
                    }
                }
                $agentUuidList = array_values(array_unique($agentUuidList));
            }
            $backupSource[] = [
                'db_name' => $row['db_name'],
                'instance_name' => $row['instance_name'],
                'agent_uuid' => $row['agent_uuid'],
                'agent_uuid_list' => $agentUuidList,
                'agent_group_uuid' => $row['group_uuid'],
                'dir_path' => $row['dir_path'],
                'db_uuid' => $row['db_uuid'],
                'cluster_uuid' => $row['cluster_uuid'],
                'cluster_flag' => $clusterFlag,
                'before_task_script' => $this->formatScriptConfig(json_decode($row['before_task_script'], true) ?: []),
                'after_task_script' => $this->formatScriptConfig(json_decode($row['after_task_script'], true) ?: []),
                'detail' => $detail,
            ];
        }
        return $backupSource;
    }

    /**
     * 获取备份任务的备份目的地信息
     * @param array $jobInfo 备份任务信息
     * @return array
     */
    private function getDbBackupTaskBackupTarget(array $jobInfo): array
    {
        // 查询存储类型和池类型
        $storageType = 0;
        $storagePoolType = 0;
        if ($jobInfo['storage_pool_uuid']) {
            $sql = "SELECT storage_pool_type, storage_pool_nickname FROM bd_storage_resource_pool WHERE storage_pool_uuid = ? ";
            $storagePoolData = $this->dbSelect($sql, [$jobInfo['storage_pool_uuid']]);
            if (is_array($storagePoolData) && count($storagePoolData) > 0) {
                $storagePoolType = $storagePoolData[0]['storage_pool_type'];
            }
        }
        if ($jobInfo['storage_uuid']) {
            $sql = "SELECT storage_type FROM bd_storage_resource WHERE storage_uuid = ? ";
            $storageData = $this->dbSelect($sql, [$jobInfo['storage_uuid']]);
            if (is_array($storageData) && count($storageData) > 0) {
                $storageType = $storageData[0]['storage_type'];
            }
        }
        return [
            'node_uuid' => $jobInfo['node_uuid'] ?: '',
            'node_pool_uuid' => $jobInfo['node_pool_uuid'] ?: '',
            'storage_uuid' => $jobInfo['storage_uuid'] ?: '',
            'storage_pool_uuid' => $jobInfo['storage_pool_uuid'] ?: '',
            'storage_pool_type' => $storagePoolType,
            'storage_type' => $storageType,
        ];
    }

    private function getDbAssociatedJobInfo(array $dbTaskInfo): array
    {
        // 关联任务
        $associatedJobFlag = false; // 数据库是否关联任务，目前只有TiDB和Oracle有
        $associatedJobType = ''; // 数据库关联任务类别，取值【master slave】，目前只有TiDB和Oracle有
        $associatedJobName = ''; // 数据库关联任务名称，目前只有TiDB和Oracle有
        $associatedJobUuid = ''; // 数据库关联任务uuid，目前只有TiDB和Oracle有
        $associatedJobStatus = 0; // 数据库关联任务状态，目前只有TiDB和Oracle有

        $allDbType = xphp_get_config('db', 'DB_TYPE');
        if (
            $dbTaskInfo['db_type'] == $allDbType['ORACLE'] ||
            $dbTaskInfo['db_type'] == $allDbType['TIDB']
        ) {
            $associatedJobInfo = (new DbJobInfo())->getDbAssociatedJobInfo($dbTaskInfo['task_uuid']);
            if ($associatedJobInfo) {
                $associatedJobFlag = true;
                if ($dbTaskInfo['db_type'] == $allDbType['TIDB']) {
                    if ($associatedJobInfo[0]['depend_task_uuid']) {  // 关联任务是子任务
                        $associatedJobType = 'slave';
                    } else {
                        $associatedJobType = 'master';
                    }
                } else {
                    if ($associatedJobInfo[0]['multi_task_flag']) {  // 关联任务是子任务
                        $associatedJobType = 'slave';
                    } else {
                        $associatedJobType = 'master';
                    }
                }
                $associatedJobName = $associatedJobInfo[0]['task_name'];
                $associatedJobUuid = $associatedJobInfo[0]['task_uuid'];
                $associatedJobStatus = $associatedJobInfo[0]['task_status'];
            }
        }
        return [
            'associated_job_flag' => $associatedJobFlag,
            'associated_job_type' => $associatedJobType,
            'associated_job_name' => $associatedJobName,
            'associated_job_uuid' => $associatedJobUuid,
            'associated_job_status' => $associatedJobStatus,
        ];
    }

    /**
     * 获取备份任务的时间策略
     * @param int $strategyId 备份策略ID
     * @return array
     */
    private function getDbBackupTaskTimeStrategy(int $strategyId): array
    {
        $allBackupType = xphp_get_config('task', 'TIME_STRATEGY_BACKUP_TYPE');
        // 查询时间策略信息
        $sql = "SELECT strategy_id, mode, strategy_type, days, start_time,
                    roll_flag, roll_interval, roll_end_time, full_backup_compensation_flag
                FROM bd_time_strategy WHERE strategy_id = ? ";
        $data = $this->dbSelect($sql, [$strategyId]);
        $sql = "SELECT time_strategy_backup_type FROM bd_strategy WHERE strategy_id = ? ";
        $strategyData = $this->dbSelect($sql, [$strategyId]);

        $timeStrategy = [
            'backup_type' => intval($strategyData[0]['time_strategy_backup_type']),
            'backup_type_name' => 'strategy',
        ];
        switch ($timeStrategy['backup_type']) {
            case $allBackupType['strategy']:  // 按策略备份
                foreach ($data as $row) {
                    if (!isset($timeStrategy['strategy'])) {
                        $timeStrategy['strategy'] = [];
                    }
                    $isRollback = v1_parse_flag_to_bool($row['roll_flag']);
                    $days = $row['days'] ? array_map('intval', str_split($row['days'])) : [];
                    $timeStrategy['strategy'][] = [
                        'backup_mode' => (int) $row['mode'],
                        'time_type' => (int) $row['strategy_type'],
                        'days' => $days,
                        'start_time' => $row['start_time'],
                        'is_rollback' => $isRollback,
                        'roll_interval' => v1_sec_to_time($isRollback ? (int) $row['roll_interval'] : 0),
                        'roll_end_time' => $isRollback ? $row['roll_end_time'] : '',
                        'frequency' => $this->parseTimeStrategyFrequency($row['strategy_type'], $row['days']),
                        'full_backup_compensation_flag' => v1_parse_flag_to_bool($row['full_backup_compensation_flag']),
                    ];
                }
                break;
            case $allBackupType['oncetime']:  // 一次性备份
                $timeStrategy['backup_type_name'] = 'oncetime';
                $timeStrategy['oncetime'] = [
                    'backup_time' => $data[0]['start_time'],
                ];
                break;
            default:  // 手动启动
                $timeStrategy['backup_type_name'] = 'manual';
                break;
        }
        return $timeStrategy;
    }

    /**
     * 获取备份任务的存储策略
     * @param int  $strategyId              备份策略ID
     * @param bool $withEncryptPasswordFlag 是否携带加密密码
     * @return array
     */
    private function getDbBackupTaskStorageStrategy(int $strategyId, bool $withEncryptPasswordFlag): array
    {
        $sql = "SELECT deduplication_flag, compressed_flag, encrypted_flag,
                    password_auto_flag, password, compress_method, encrypt_method, block_size
                FROM bd_storage_strategy WHERE strategy_id = ? ";
        $data = $this->dbSelect($sql, [$strategyId])[0];
        $isAutoPassword = v1_parse_flag_to_bool($data['password_auto_flag']);
        $storageStrategy = [
            'deduplication_flag' => v1_parse_flag_to_bool($data['deduplication_flag']),
            'compress_flag' => v1_parse_flag_to_bool($data['compressed_flag']),
            'compress_method' => intval($data['compress_method']),
            'encrypt_flag' => v1_parse_flag_to_bool($data['encrypted_flag']),
            'encrypt_method' => intval($data['encrypt_method']),
            'auto_password_flag' => $isAutoPassword,
            'block_size' => intval($data['block_size']) / 1024,
        ];
        if ($withEncryptPasswordFlag) {
            $storageStrategy['encrypt_password'] = $isAutoPassword? '' : base64_encode(v1_pt_pass_decrypt($data['password']));  // 不返回加密密码
        }
        return $storageStrategy;
    }

    /**
     * 获取备份任务的保留策略
     * @param int    $strategyId 备份策略ID
     * @param string $jobsUuid   备份任务UUID
     * @return array
     */
    private function getDbBackupTaskReservedStrategy(int $strategyId, string $jobsUuid): array
    {
        $sql = "SELECT strategy_type, number, strategy_mode FROM bd_reserved_strategy WHERE strategy_id = ? ";
        $data = $this->dbSelect($sql, [$strategyId])[0];
        // GFS
        $sql = "SELECT level1_type, level2_type, retention_num FROM bd_task_gfs_retention_strategy WHERE task_uuid = ? ";
        $gfsData= $this->dbSelect($sql, [$jobsUuid]);
        $gfsStrategyItemList = [];
        if (is_array($gfsData) && $gfsData) {
            foreach ($gfsData as $row) {
                $gfsStrategyItemList[] = [
                    'level1_type' => (int) $row['level1_type'],  // 1按周 2按月 3按年
                    'level2_type' => (int) $row['level2_type'],  // 按周[1-7, 星期一到星期日] 按月[1-2, 第一周-最后一周] 按年[1-12, 一月-十二月]
                    'retention_num' => intval($row['retention_num']),  // 保留个数
                ];
            }
        }
        return [
            'strategy_mode' => intval($data['strategy_mode']),  // 保留模式(1:按备份点保留, 2:按备份链保留)
            'reserved_type' => (int) $data['strategy_type'],  // 保留方法(1:按个数保留, 2:按天数保留)
            'value' => (int) $data['number'],
            'gfs_strategy_item_list' => $gfsStrategyItemList,
        ];
    }

    /**
     * 获取备份任务的传输策略
     * @param array $jobInfo    bd_task任务信息
     * @return array
     */
    private function getDbBackupTaskTransportStrategy(array $jobInfo): array
    {
        $allTransportMode = xphp_get_config('db', 'TRANSPORT_MODE', 'db');
        $sql = "SELECT encrypt_flag, network_uuid, encrypt_method, network_pool_uuid, max_object_transport_parallel_nums
                FROM bd_transport_strategy WHERE strategy_id = ? ";
        $transportInfo = $this->dbSelect($sql, [$jobInfo['strategy_id']])[0];
        return [
            'encrypt_flag' => v1_parse_flag_to_bool($transportInfo['encrypt_flag']),
            'encrypt_method' => intval($transportInfo['encrypt_method']),
            'transport_mode' => $allTransportMode['network'],  // 目前只有网络传输
            'network_uuid' => $transportInfo['network_uuid'] ?: '',
            'network_pool_uuid' => $transportInfo['network_pool_uuid'] ?: '',
            'max_object_transport_parallel_nums' => intval($transportInfo['max_object_transport_parallel_nums']),  // MongoDB客户端并行数量
        ];
    }

    /**
     * 获取备份任务的高级策略
     * @param array $jobInfo    bd_task任务信息
     * @param array $dbTaskInfo db_task任务信息
     * @return array
     */
    private function getDbBackupTaskAdvancedStrategy(array $jobInfo, array $dbTaskInfo): array
    {
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $compressedFlag = v1_parse_flag_to_bool($dbTaskInfo['compress_flag']);
        $deleteArchivelogFlag = v1_parse_flag_to_bool($dbTaskInfo['delete_archive_log_flag']);
        $warningSetting = new stdClass();
        $archiveNum = 0;
        if (
            $dbTaskInfo['db_type'] == $allDbType['POSTGRE'] ||
            $dbTaskInfo['db_type'] == $allDbType['ANTDB'] ||
            $dbTaskInfo['db_type'] == $allDbType['KINGBASE'] ||
            $dbTaskInfo['db_type'] == $allDbType['UXDB'] ||
            $dbTaskInfo['db_type'] == $allDbType['HIGHGO'] ||
            $dbTaskInfo['db_type'] == $allDbType['OPENGAUSS'] ||
            $dbTaskInfo['db_type'] == $allDbType['VASTBASE']
        ) {
            $compressedFlag = false;
            $deleteArchivelogFlag = (int) $dbTaskInfo['delete_archive_log_flag'];
            $warningSetting = json_decode($dbTaskInfo['detail'], true);
            $warningSetting['warn_check'] = (bool) $warningSetting['warn_check'];
            $warningSetting['warn_type'] = (int) $warningSetting['warn_type'];
            if ($warningSetting['warn_type'] == xphp_get_config('db', 'WARN_TYPE', 'db')['size']) {
                // 存储的是Bit，需要转化为GB，即1GB=1024MB=1024*1024KB=1024*1024*1024B
                $warningSetting['warn_value'] = intval($warningSetting['warn_value']) / 1024 / 1024 / 1024;
            } else {
                $warningSetting['warn_value'] = (int) $warningSetting['warn_value'];
            }
        } elseif ($dbTaskInfo['db_type'] === $allDbType['ORACLE']) {
            $deleteArchivelogFlag = intval($dbTaskInfo['delete_archive_log_flag']);
            if ($deleteArchivelogFlag === xphp_get_config('db', 'ARCHIVELOG_RESERVE_TYPE', 'db')['reserve_days']) {
                $archiveNum = intval($dbTaskInfo['last_archive_days']);
            }
        }
        return [
            'check_db_flag' => v1_parse_flag_to_bool($dbTaskInfo['check_db_flag']),  // SQL server检查数据库, Oracle检查归档日志文件
            /*
             * SQL Server: SQL Server压缩
             * Oracle: Oracle压缩
             * MySQL/MariaDB: 源端压缩
             * DM: DM压缩
             * SAP HANA: SAP HANA压缩
             */
            'compress_flag' => $compressedFlag,
            'check_sum_flag' => v1_parse_flag_to_bool($dbTaskInfo['checksum_flag']),  // SQL Server校验
            'thread_num' => intval($jobInfo['thread_num']),  // Oracle: 传输线程的个数 MongoDB: 传输线程的个数
            'channel_count' => intval($dbTaskInfo['channel_count']),  // 通道数【SAPHANA MySQL MariaDB Oracle】
            /*
             * oracle：归档日志保留策略【1删除所有已备份的归档日志 2不删除归档日志 3保留x天的归档日志不删除】
             * DM: 删除归档日志
             * pg系: 删除归档日志【1归档备份后删除 2不删除 3删除全部】
             */
            'delete_archivelog_flag' => $deleteArchivelogFlag,
            'archive_num' => $archiveNum,  // Oracle: delete_archivelog_flag为3时，保留x天的归档日志不删除；否则为0
            'warning_setting' => $warningSetting,  // pg系：告警配置
            'set_filesperset_flag' => v1_parse_flag_to_bool($dbTaskInfo['set_filesperset_flag']),  // Oracle: 是否配置filesperset
            'datafile_filesperset_num' => (int) $dbTaskInfo['datafile_filesperset_num'],  // Oracle: datafile个数
            'archivelog_filesperset_num' => (int) $dbTaskInfo['archivelog_filesperset_num'],  // Oracle: archivelog个数
            'auto_log_backup_interval' => intval($dbTaskInfo['auto_log_backup_interval']),  // SAP HANA: 日志备份间隔（秒）
            'log_backup_times_flag' => v1_parse_flag_to_bool($dbTaskInfo['log_backup_times_flag']),  // Oracle：归档日志备份次数【true开启 false关闭】
            'log_backup_times' => intval($dbTaskInfo['log_backup_times']),  // Oracle：最大重复备份次数
            'log_backup_days_flag' => v1_parse_flag_to_bool($dbTaskInfo['log_backup_days_flag']),  // Oracle：归档日志备份天数【true开启 false关闭】
            'log_backup_days' => intval($dbTaskInfo['log_backup_days']),  // Oracle：日志最近备份天数
            'skip_inaccessible_file_flag' => v1_parse_flag_to_bool($dbTaskInfo['skip_inaccessible_file_flag']),  // Oracle跳过不可访问文件
            'skip_offline_file_flag' => v1_parse_flag_to_bool($dbTaskInfo['skip_offline_file_flag']),  // Oracle跳过脱机文件
            'enable_bct_flag' => v1_parse_flag_to_bool($dbTaskInfo['enable_bct_flag']),  // Oracle BCT(增量备份效率)
            'set_section_size_flag' => v1_parse_flag_to_bool($dbTaskInfo['set_section_size_flag']),  // Oracle多段传输
            'section_size' => intval($dbTaskInfo['section_size']),  // Oracle分块大小【单位GB】
            'compress_method' => intval($dbTaskInfo['compress_method']),  // TiDB压缩方法【lz4 snappy zstd】
            'compress_level' => intval($dbTaskInfo['compress_level']),  // TiDB：压缩等级【压缩方式为zstd时设置，1~16】 DM: 压缩方式【1~9 默认5】
            'multi_task_flag' => v1_parse_flag_to_bool($dbTaskInfo['multi_task_flag']),  // Oracle: 备份任务标识【true归档日志备份任务 false普通备份任务】
            'custom_rman_cmd' => $dbTaskInfo['custom_rman_cmd'] ?: '',  // Oracle自定义rman命令
            'ignore_resource_limiting_flag' => v1_parse_flag_to_bool($jobInfo['ignore_resource_limiting_flag']),  // 忽略节点资源限制
        ];
    }

    /**
     * 获取实例的备份任务信息
     * @return array
     */
    public function getAllInstanceBackupInfo(): array
    {
        $sql = "SELECT bt.task_name, bt.task_uuid, bt.node_uuid, bt.storage_uuid, bt.task_status,
                    bt.storage_pool_uuid, bt.node_pool_uuid,
                    dt.db_type, dt.multi_task_flag, dt.depend_task_uuid,
                    dl.instance_name, dl.db_name, dl.agent_uuid, dl.db_uuid
                FROM bd_task bt
                    INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid
                    INNER JOIN db_list dl ON bt.task_uuid = dl.task_uuid
                WHERE bt.task_type = ? ";
        $taskData = $this->dbSelect($sql, [xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']]);
        $taskList = [];
        foreach ($taskData as $row) {
            // 查看任务的实例信息(集群任务只关联一个任务但有多个实例)
            $sql = "SELECT cluster_uuid, app_name, agent_uuid, app_type, cluster_flag
                    FROM bd_agent_app
                    WHERE app_name = ? AND agent_uuid = ? AND app_type = ? ";
            $clusterData = $this->dbSelect($sql, [$row['instance_name'], $row['agent_uuid'], $row['db_type']]);
            if (v1_parse_flag_to_bool($clusterData[0]['cluster_flag']) && $clusterData[0]['cluster_uuid']) {
                $sql = "SELECT app_name, agent_uuid, app_type FROM bd_agent_app WHERE cluster_uuid = ? ";
                $appList = $this->dbSelect($sql, [$clusterData[0]['cluster_uuid']]);
                foreach ($appList as $appInfo) {
                    $taskList[] = [
                        'task_name' => $row['task_name'],
                        'task_uuid' => $row['task_uuid'],
                        'task_status' => $row['task_status'],
                        'db_type' => $row['db_type'],
                        'db_name' => $row['db_name'],
                        'instance_name' => $appInfo['app_name'],
                        'agent_uuid' => $appInfo['agent_uuid'],
                        'db_uuid' => $row['db_uuid'],
                        'node_uuid' => $row['node_uuid'],
                        'storage_uuid' => $row['storage_uuid'],
                        'node_pool_uuid' => $row['node_pool_uuid'] ?: '',
                        'storage_pool_uuid' => $row['storage_pool_uuid'] ?: '',
                        'multi_task_flag' => v1_parse_flag_to_bool($row['multi_task_flag']),
                        'depend_task_uuid' => $row['depend_task_uuid'],
                    ];
                }
            } else {
                $taskList[] = [
                    'task_name' => $row['task_name'],
                    'task_uuid' => $row['task_uuid'],
                    'task_status' => $row['task_status'],
                    'db_type' => $row['db_type'],
                    'db_name' => $row['db_name'],
                    'instance_name' => $clusterData[0]['app_name'],
                    'agent_uuid' => $clusterData[0]['agent_uuid'],
                    'db_uuid' => $row['db_uuid'],
                    'node_uuid' => $row['node_uuid'],
                    'storage_uuid' => $row['storage_uuid'],
                    'node_pool_uuid' => $row['node_pool_uuid'] ?: '',
                    'storage_pool_uuid' => $row['storage_pool_uuid'] ?: '',
                    'multi_task_flag' => v1_parse_flag_to_bool($row['multi_task_flag']),
                    'depend_task_uuid' => $row['depend_task_uuid'],
                ];
            }
        }
        return $taskList;
    }

    /**
     * 获取实例的恢复任务信息
     * @return array
     */
    private function getAllInstanceRecoverInfo(): array
    {
        /**
         * 定时恢复最新备份点
         * 恢复源: db_list.source_agent_uuid、db_list.source_instance_name、db_list.source_db_name
         * 恢复目标: bd_task_agent_list.agent_uuid/db_list.agent_uuid、db_list.instance_name、db_list.db_name
         *
         * 指定备份点恢复
         * 恢复源: db_backup_timepoint.agent_uuid,、db_backup_timepoint.instance_name、db_backup_timepoint.db_name
         * 恢复目标: bd_task_agent_list.agent_uuid/db_list.agent_uuid、db_list.instance_name、db_list.db_name
         */
        $sql = "SELECT bt.task_name, bt.task_uuid, bt.recovery_type,
                    dt.db_type,
                    dl.db_uuid, dl.timepoint_uuid,
                    dl.instance_name, dl.db_name, dl.agent_uuid,
                    dl.source_agent_uuid, dl.source_instance_name, dl.source_db_name,
                    baa.cluster_uuid AS target_cluster_uuid
                FROM bd_task bt
                    INNER JOIN db_task dt ON bt.task_uuid = dt.task_uuid
                    INNER JOIN db_list dl ON bt.task_uuid = dl.task_uuid
                    INNER JOIN bd_agent_app baa ON baa.agent_uuid = dl.agent_uuid AND dl.instance_name = baa.app_name
                WHERE (bt.task_type = ? OR bt.task_type = ? ) ";
        $sqlParams = [
            xphp_get_config('task', 'TASKTYPE')['DB_RECOVERY'],
            xphp_get_config('task', 'TASKTYPE')['DRILL']
        ];
        $taskData = $this->dbSelect($sql, $sqlParams);
        if (!is_array($taskData) || !$taskData) {
            return [];
        }
        $timepointUuidList = [];
        foreach ($taskData as $taskInfo) {
            if ($taskInfo['recovery_type'] == xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db')['SPECIFIC_TIMEPOINT']) {
                $timepointUuidList[] = $taskInfo['timepoint_uuid'];
            }
        }
        $timepointUuidList = array_values(array_unique($timepointUuidList));
        $timepointMap = [];
        if ($timepointUuidList) {
            $timepointUuids = "'" . implode("', '", $timepointUuidList) . "'";
            $sql = "SELECT db_name, instance_name, agent_uuid, timepoint_uuid FROM db_backup_timepoint WHERE timepoint_uuid IN ($timepointUuids) ";
            $timepointData = $this->dbSelect($sql);
            foreach ($timepointData as $row) {
                $timepointMap[$row['timepoint_uuid']] = [
                    'agent_uuid' => $row['agent_uuid'],
                    'instance_name' => $row['instance_name'],
                    'db_name' => $row['db_name'],
                ];
            }
        }

        $taskList = [];
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        foreach ($taskData as $taskInfo) {
            if ($taskInfo['target_cluster_uuid']) {  // 恢复目标是集群
                $sql = "SELECT app_name, agent_uuid, app_type, app_service_name FROM bd_agent_app WHERE cluster_uuid = ? ";
                $targetClusterData = $this->dbSelect($sql, [$taskInfo['target_cluster_uuid']]);
                foreach ($targetClusterData as $targetAppInfo) {
                    $targetDbName = $taskInfo['db_name'];
                    if ($targetAppInfo['app_type'] == $allDbType['ORACLE']) {
                        $targetDbName = $targetAppInfo['app_service_name'];
                    }
                    $tmpTask = [
                        'job_uuid' => $taskInfo['task_uuid'],
                        'job_name' => $taskInfo['task_name'],
                        'db_type' => intval($taskInfo['db_type']),
                        'source_agent_uuid' => $taskInfo['source_agent_uuid'],
                        'source_instance_name' => $taskInfo['source_instance_name'],
                        'source_db_name' => $taskInfo['source_db_name'],
                        'target_agent_uuid' => $targetAppInfo['agent_uuid'],
                        'target_instance_name' => $targetAppInfo['app_name'],
                        'target_db_name' => $targetDbName,
                        'target_cluster_uuid' => $taskInfo['target_cluster_uuid'],
                        'db_uuid' => $taskInfo['db_uuid'],
                        'recovery_source_type' => intval($taskInfo['recovery_type']),
                    ];
                    if ($taskInfo['recovery_type'] == xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db')['SPECIFIC_TIMEPOINT']) {
                        $tmpTask['source_agent_uuid'] = $timepointMap[$taskInfo['timepoint_uuid']]['agent_uuid'];
                        $tmpTask['source_instance_name'] = $timepointMap[$taskInfo['timepoint_uuid']]['instance_name'];
                        $tmpTask['source_db_name'] = $timepointMap[$taskInfo['timepoint_uuid']]['db_name'];
                    } else {
                        $sql = "SELECT cluster_uuid FROM bd_agent_app WHERE agent_uuid = ? AND app_name = ? ";
                        $sourceAgentData = $this->dbSelect($sql, [$taskInfo['source_agent_uuid'], $taskInfo['source_instance_name']]);
                        if ($sourceAgentData[0]['cluster_uuid']) {
                            $sql = "SELECT app_name, agent_uuid, app_type, app_service_name FROM bd_agent_app WHERE cluster_uuid = ? ";
                            $sourceClusterData = $this->dbSelect($sql, [$sourceAgentData[0]['cluster_uuid']]);
                            foreach ($sourceClusterData as $sourceAppInfo) {
                                if ($sourceAppInfo['app_type'] == $allDbType['ORACLE']) {
                                    $tmpTask['source_db_name'] = $sourceAppInfo['app_service_name'];
                                }
                                $tmpTask['source_agent_uuid'] = $sourceAppInfo['agent_uuid'];
                                $tmpTask['source_instance_name'] = $sourceAppInfo['app_name'];
                                $taskList[] = $tmpTask;
                            }
                            continue;
                        }
                    }
                    $taskList[] = $tmpTask;
                }
            } else {
                $tmpTask = [
                    'job_uuid' => $taskInfo['task_uuid'],
                    'job_name' => $taskInfo['task_name'],
                    'db_type' => intval($taskInfo['db_type']),
                    'source_agent_uuid' => $taskInfo['source_agent_uuid'],
                    'source_instance_name' => $taskInfo['source_instance_name'],
                    'source_db_name' => $taskInfo['source_db_name'],
                    'target_agent_uuid' => $taskInfo['agent_uuid'],
                    'target_instance_name' => $taskInfo['instance_name'],
                    'target_db_name' => $taskInfo['db_name'],
                    'target_cluster_uuid' => $taskInfo['target_cluster_uuid'],
                    'db_uuid' => $taskInfo['db_uuid'],
                    'recovery_source_type' => intval($taskInfo['recovery_type']),
                ];
                if ($taskInfo['recovery_type'] == xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db')['SPECIFIC_TIMEPOINT']) {
                    $tmpTask['source_agent_uuid'] = $timepointMap[$taskInfo['timepoint_uuid']]['agent_uuid'];
                    $tmpTask['source_instance_name'] = $timepointMap[$taskInfo['timepoint_uuid']]['instance_name'];
                    $tmpTask['source_db_name'] = $timepointMap[$taskInfo['timepoint_uuid']]['db_name'];
                } else {
                    $sql = "SELECT cluster_uuid FROM bd_agent_app WHERE agent_uuid = ? AND app_name = ? ";
                    $sourceAgentData = $this->dbSelect($sql, [$taskInfo['source_agent_uuid'], $taskInfo['source_instance_name']]);
                    if ($sourceAgentData[0]['cluster_uuid']) {
                        $sql = "SELECT app_name, agent_uuid, app_type, app_service_name FROM bd_agent_app WHERE cluster_uuid = ? ";
                        $sourceClusterData = $this->dbSelect($sql, [$sourceAgentData[0]['cluster_uuid']]);
                        foreach ($sourceClusterData as $sourceAppInfo) {
                            if ($sourceAppInfo['app_type'] == $allDbType['ORACLE']) {
                                $tmpTask['source_db_name'] = $sourceAppInfo['app_service_name'];
                            }
                            $tmpTask['source_agent_uuid'] = $sourceAppInfo['agent_uuid'];
                            $tmpTask['source_instance_name'] = $sourceAppInfo['app_name'];
                            $taskList[] = $tmpTask;
                        }
                        continue;
                    }
                }
                $taskList[] = $tmpTask;
            }
        }
        return $taskList;
    }

    /**
     * 获取所有的数据库实例
     * @param array $params 参数
     * @return array
     */
    public function getAllInstance(array $params): array
    {
        $sql = "SELECT ba.agent_uuid, ba.agent_name, ba.ip, ba.hostname, ba.os_type,
                    ba.online_flag, ba.authorization_module, ba.net_model, ba.agent_package_type,
                    baa.app_uuid, baa.app_name, baa.app_type, baa.app_auth_type, baa.app_listen_ip,
                    baa.app_detail, baa.app_version,
                    baa.cluster_flag, baa.cluster_uuid, baa.cluster_name, baa.cluster_service_ip,
                    baa.cluster_type, baa.app_service_name,
                    bag.group_uuid, bag.group_name
                FROM bd_agent_app baa
                    INNER JOIN bd_agent ba ON baa.agent_uuid = ba.agent_uuid
                    INNER JOIN bd_agent_group bag ON ba.group_uuid = bag.group_uuid
                WHERE 1=1 ";
        $sqlParams = [];
        if (isset($params['db_type']) && $params['db_type']) {
            $sql .= ' AND baa.app_type = ? ';
            $sqlParams[] = $params['db_type'];
        }

        // 权限判断
        if(v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_source_by_type(xphp_get_config('resource', 'RESOURCE_TYPE')['CLIENT'], 'ba.agent_uuid');
            $sql .= " AND ($resourceUuidSql) ";
        }

        $sql .= ' ORDER BY ba.online_flag ASC';
        $data = $this->dbSelect($sql, $sqlParams);
        $allBackupTask = $this->getAllInstanceBackupInfo();
        $allRecoveryTask = $this->getAllInstanceRecoverInfo();

        $rows = [];
        foreach ($data as $row) {
            $appDetail = [];
            try {
                $appDetail = json_decode($row['app_detail'], true);
            } catch (\Exception $e) {
                $appDetail = [];
            }
            $authModule = [];
            try {
                $authModule = json_decode($row['authorization_module'], true);
            } catch (\Exception $e) {
                $authModule = [];
            }

            $dbBackupInfo = [];
            foreach ($allBackupTask as $backupTask) {
                if (
                    $backupTask['instance_name'] === $row['app_name'] &&
                    $backupTask['db_type'] === $row['app_type'] &&
                    $backupTask['agent_uuid'] === $row['agent_uuid']
                ) {
                    $dbBackupInfo[] = [
                        'job_uuid' => $backupTask['task_uuid'],
                        'job_name' => $backupTask['task_name'],
                        'job_status' => intval($backupTask['task_status']),
                        'instance_name' => $backupTask['instance_name'],
                        'db_name' => $backupTask['db_name'],
                        'db_uuid' => $backupTask['db_uuid'],  // db_list里面的db_uuid用于修改备份任务使用
                        'node_uuid' => $backupTask['node_uuid'],  // 节点uuid
                        'storage_uuid' => $backupTask['storage_uuid'], // 存储uuid
                        'node_pool_uuid' => $backupTask['node_pool_uuid'],  // 计算资源池uuid
                        'storage_pool_uuid' => $backupTask['storage_pool_uuid'],  // 存储资源池uuid
                        'multi_task_flag' => $backupTask['multi_task_flag'], // Oracle多任务标志
                    ];
                }
            }
            $dbSourceRecoveryInfo = [];
            $dbTargetRecoveryInfo = [];
            foreach ($allRecoveryTask as $recoveryTask) {
                if (
                    $recoveryTask['source_instance_name'] === $row['app_name'] &&
                    $recoveryTask['db_type'] === $row['app_type'] &&
                    $recoveryTask['source_agent_uuid'] === $row['agent_uuid']
                ) {
                    $dbSourceRecoveryInfo[] = $recoveryTask;
                }
                if (
                    $recoveryTask['target_instance_name'] === $row['app_name'] &&
                    $recoveryTask['db_type'] === $row['app_type'] &&
                    $recoveryTask['target_agent_uuid'] === $row['agent_uuid']
                ) {
                    $dbTargetRecoveryInfo[] = $recoveryTask;
                }
            }

            $dbcdpBackupInfo = $this->getInstanceBackupInfo($row['agent_uuid']);

            $dbcdpRecoverInfo = $this->getInstanceRecoverInfo($row['agent_uuid'],$row['cluster_uuid']);

            $rows[] = [
                'db_type' => intval($row['app_type']),
                'instance_name' => $row['app_name'],
                'app_uuid' => $row['app_uuid'],
                'app_auth_type' => intval($row['app_auth_type']),
                'app_listen_ip' => $row['app_listen_ip'] ?: '',  // SQL Server的集群服务IP存在这里
                'app_detail' => $appDetail ?: new stdClass(),  // new stdClass()返回值是{}
                'db_backup_info' => $dbBackupInfo ?: [],
                'db_source_recovery_info' => $dbSourceRecoveryInfo?: [],
                'db_target_recovery_info' => $dbTargetRecoveryInfo?: [],
                'app_type' => $row['app_type'],
                'app_version' => $row['app_version'],
                'cluster_info' => [
                    'cluster_flag' => v1_parse_flag_to_bool($row['cluster_flag']) && $row['cluster_uuid'],
                    'cluster_uuid' => $row['cluster_uuid'],
                    'cluster_name' => $row['cluster_name'],
                    'cluster_service_ip' => $row['cluster_service_ip'],
                    'cluster_type' => intval($row['cluster_type']),
                    'online_flag' => v1_parse_flag_to_bool($row['online_flag']),
                    'app_service_name' => $row['app_service_name'],
                    'app_type' => $row['app_type'],
                    'app_version' => $row['app_version'],
                ],
                'agent_info' => [
                    'agent_uuid' => $row['agent_uuid'],
                    'agent_name' => $row['agent_name'],
                    'hostname' => $row['hostname'],
                    'ip' => $row['ip'],
                    'online_flag' => v1_parse_flag_to_bool($row['online_flag']),
                    'agent_package_type' => intval($row['agent_package_type']),
                    'authorization_module' => [
                        'file' => isset($authModule['file']) && $authModule['file'],
                        'database' => isset($authModule['database']) && $authModule['database'],
                        'os' => isset($authModule['os']) && $authModule['os'],
//                        'dbcdp' => isset($authModule['dbcdp']) && $authModule['dbcdp'],
                        'dbcdp'=>true,
                    ],
                    'os_type' => $row['os_type'],
                    'net_model' => intval($row['net_model']),
                    'group_uuid' => $row['group_uuid'],
                    'group_name' => $row['group_name'],
                ],
                'db_cdp_recover_info' => $dbcdpRecoverInfo,
                'db_cdp_backup_info' => $dbcdpBackupInfo
            ];
        }
        return $this->sendResult('', true, 200, [
            'rows' => $rows,
            'total' => count($rows),
        ]);
    }

    /**
     * 获取数据库实时实例的备份任务信息
     * @return array
     */
    public function getInstanceBackupInfo($agentUuid = '')
    {
        // 判断是集群还是单机
        $sql = "select cluster_flag,cluster_uuid from bd_agent_app where agent_uuid = ?";
        $data = $this->dbSelect($sql, array($agentUuid));
        if ($data[0]['cluster_flag'] == xphp_get_config('app')['FLAG']['SET'] && $data[0]['cluster_uuid']) {
            $sql = "SELECT BT.task_name, BT.task_uuid FROM bd_task BT
                        JOIN cdp_db_dr_task CDDT ON BT.task_uuid = CDDT.task_uuid
                        WHERE (
                        (CDDT.source_agent_uuid IN (SELECT agent_uuid FROM bd_agent_app WHERE cluster_uuid =?) AND BT.task_type = 46)
                        OR
                        (CDDT.target_agent_uuid IN (SELECT agent_uuid FROM bd_agent_app WHERE cluster_uuid =?) AND BT.task_type IN (46, 47))
                        )
                        UNION
                        SELECT BT.task_name, BT.task_uuid
                        FROM bd_task BT
                        JOIN cdp_db_dr_task_takeover_failback_info CDDTTFI ON BT.task_uuid = CDDTTFI.task_uuid
                        WHERE CDDTTFI.failback_target_agent_uuid IN (SELECT agent_uuid FROM bd_agent_app WHERE cluster_uuid =?)";

            $sqlParams = array($data[0]['cluster_uuid'],$data[0]['cluster_uuid'],$data[0]['cluster_uuid']);
            $data = $this->dbSelect($sql, $sqlParams);
        } else {
            $sql = "SELECT BT.task_name, BT.task_uuid
                            FROM bd_task BT
                            JOIN cdp_db_dr_task CDDT ON BT.task_uuid = CDDT.task_uuid
                            WHERE (CDDT.source_agent_uuid = ? AND BT.task_type = 46)
                            OR (CDDT.target_agent_uuid = ? AND BT.task_type = 46)
                            OR (CDDT.target_agent_uuid = ? AND BT.task_type = 47)
                            UNION
                            SELECT BT.task_name, BT.task_uuid
                            FROM bd_task BT
                            JOIN cdp_db_dr_task_takeover_failback_info CDDTTFI ON BT.task_uuid = CDDTTFI.task_uuid
                            WHERE CDDTTFI.failback_target_agent_uuid = ?";

            $sqlParams = array($agentUuid,$agentUuid,$agentUuid,$agentUuid);
            $data = $this->dbSelect($sql, $sqlParams);
        }
        $taskList = [];
        foreach ($data as $d) {
            $taskList[] = [
                'job_name' => $d['task_name'],
                'job_uuid' => $d['task_uuid']
            ];
        }

        return $taskList;
    }

    /**
     * 恢复目标端的限制条件
     * 返回的task_uuid和task_name为空则可作为恢复目标
     */
    public function getInstanceRecoverInfo($agentUuid = '', $clusteruuid)
    {
        if (!empty($clusteruuid)) {
            // 集群
            $sql = "SELECT
	                    BT.task_uuid,BT.task_name 
                    FROM
	                    bd_task BT 
                    WHERE
	                EXISTS (
	                SELECT
		            1 
	                FROM cdp_db_dr_task CDDT
		            JOIN bd_task BT ON BT.task_uuid = CDDT.task_uuid 
	                WHERE
		            (
			        ( CDDT.source_agent_uuid IN ( SELECT agent_uuid FROM bd_agent_app WHERE cluster_uuid = ? ) AND BT.task_type = 46) 
			        OR (
				    CDDT.target_agent_uuid IN ( SELECT agent_uuid FROM bd_agent_app WHERE cluster_uuid = ? ) AND BT.task_type IN (46, 47 )) 
		            ) 
	                )";
            $data = $this->dbSelect($sql, array($clusteruuid,$clusteruuid));
        } else {
            // 单
            $sql = "SELECT
	                BT.task_uuid,BT.task_name 
                    FROM bd_task BT 
                    WHERE
	                EXISTS (
	                SELECT
		            1 
	                FROM
		            cdp_db_dr_task CDDT
		            JOIN bd_task BT ON BT.task_uuid = CDDT.task_uuid 
	                WHERE
		            ( CDDT.source_agent_uuid = ? AND BT.task_type = 46 ) 
	                OR ( CDDT.target_agent_uuid = ? AND BT.task_type IN (46, 47)))";
            $data = $this->dbSelect($sql, array($agentUuid,$agentUuid));
        }

        $taskList = [];
        foreach ($data as $d) {
            $agentSql = "SELECT source_agent_uuid,target_agent_uuid FROM cdp_db_dr_task WHERE task_uuid = ?";
            $agentSqlData = $this->dbSelect($agentSql,array($d['task_uuid']));
            $taskList[] = [
                'source_agent_uuid' => $agentSqlData[0]['source_agent_uuid'],
                'target_agent_uuid' => $agentSqlData[0]['target_agent_uuid'],
                'job_name' => $d['task_name'],
                'job_uuid' => $d['task_uuid']
            ];
        }

        return $taskList;

    }
}
