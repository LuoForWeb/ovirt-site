<?php

namespace app\v1\db\v0\logic;

use app\v1\common\logic\Base;
use app\v1\opcode\DbProtectOpcode;
use app\v1\tenant\v0\logic\Tenant;
use app\v1\user\v0\logic\User;
use app\v1\backupData\v0\logic\DataManage;

/**
 * note          数据库 之数据管理 logic
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/6 16:18
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class DbData extends Base
{
    /**
     * 获取数据库备份数据
     * @param array $params 请求参数
     * @return array
     */
    public function getDbBackupData(array $params): array
    {
        $allModuleType = xphp_get_config('module', 'MODULE_TYPE');
        $setFlag = xphp_get_config('app', 'FLAG');
        $withCopyData = $params['with_copy_data'] ?? false;
        $recoveryFlag = $params['recovery_flag'] ?? true;
        $moduleTypes = '(' . $allModuleType['DB'] . ',' . $allModuleType['BACKUP_COPY_CLIENT'] . ')';
        $sql = "SELECT bbt.task_uuid, bbt.task_type, bbt.task_name, bbt.task_create_time,
                    bbt.backup_mode, bbt.chain_uuid, bt.task_uuid AS is_not_del,
                    dbt.db_type, dbt.instance_name, dbt.agent_uuid, dbt.agent_ip, dbt.dir_path,
                    dbt.db_name, dbt.db_uuid, dbt.cluster_uuid, dbt.cluster_name
                FROM bd_backup_timepoint bbt
                    INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid 
                    INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid=bsr.storage_uuid
                    LEFT JOIN bd_task bt ON bt.task_uuid = bbt.task_uuid 
                WHERE bbt.deleted_flag = ? AND bbt.available_flag = ? AND bbt.import_flag = ?
                    AND bbt.module_type IN $moduleTypes AND bbt.data_local_flag = ? ";
        $sqlParams = [
            $setFlag['UNSET'],
            $setFlag['SET'],
            $setFlag['UNSET'],
            $setFlag['SET'],
        ];
        // 副本数据
        if (!$withCopyData) {
            $sql .= ' AND bbt.copy_flag = ? ';
            $sqlParams[] = $setFlag['UNSET'];
        }
        // 节点过滤
        if (isset($params['node_uuid']) && $params['node_uuid']) {
            $sql .= ' AND (bsr.node_uuid = ? OR bbt.real_node_uuid = ? ) ';
            $sqlParams[] = $params['node_uuid'];
            $sqlParams[] = $params['node_uuid'];
        }
        // 存储设备过滤
        if (isset($params['storage_uuid']) && $params['storage_uuid']) {
            $sql .= ' AND bbt.storage_uuid = ? ';
            $sqlParams[] = $params['storage_uuid'];
        }
        // 数据库类型过滤
        if (isset($params['db_type']) && $params['db_type']) {
            $sql .= ' AND dbt.db_type = ? ';
            $sqlParams[] = $params['db_type'];
        }
        // 获取租户管理员是否可以控制所有备份数据标志
        $allManageFlag = false;
        $tenantHandler = new Tenant();
        $userHandler = new User();
        $tenantMangerFlag = $userHandler->pCheckTenantManager();
        if (!$_SESSION['tenantuuid']) {
            $settings = $tenantHandler->pGetTenantSettings($_SESSION['tenantuuid']);
            $allManageFlag = $settings['common']['datamanage'];
        }


        // 权限判断
        if(v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_users('db_protect');
            $sql .= " AND bbt.user_uuid IN ($resourceUuidSql) ";
        }
        //具有管理所有备份数据的租户管理员查询租户内所有用户数据
        if ($withCopyData && $tenantMangerFlag && $allManageFlag) {
            $userList = $tenantHandler->getTenantAllUser($_SESSION['tenantuuid']);
            $userListDes = "'" . implode("','", $userList) . "'";
            $sql .= " AND bbt.user_uuid IN ($userListDes) ";
        }
        $sql .= ' ORDER BY bbt.timepoint DESC ';  // 按照备份时间倒序排序

        $dbDataList = $this->dbSelect($sql, $sqlParams);
        if (!is_array($dbDataList) || !$dbDataList) {
            $dbDataList = [];
        }
        // 过滤掉Oracle没有完备点的备份链子
        $chainList = [];
        foreach ($dbDataList as $row) {
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
            $dbDataList = array_filter($dbDataList, function($row) use ($excludeChainUuidList) {
                return !in_array($row['chain_uuid'], $excludeChainUuidList);
            });
        }
        return $this->sendResult('', true, 200, [
            'db_type_data' => $this->getDbTypeData($dbDataList),
            'job_data' => $this->getJobData($dbDataList),
            'instance_data' => $this->getInstanceData($dbDataList),
            'db_data' => $this->getDbData($dbDataList),
        ]);
    }

    /**
     * 获取备份数据的数据类别数据
     * @param array $dbDataList 数据库备份数据
     * @return string[]
     */
    private function getDbTypeData(array $dbDataList): array
    {
        $allDbTypeDes = xphp_get_config('db', 'DB_TYPE_DES');
        $dbTypeData = [];
        foreach ($allDbTypeDes as $dbType => $dbTypeDes) {
            foreach ($dbDataList as $row) {
                if ($row['db_type'] != $dbType) {
                    continue;
                }
                if (!isset($dbTypeData[$row['db_type']])) {
                    $dbTypeData[$row['db_type']] = [
                        'db_type' => $dbType,
                        'db_type_name' => $dbTypeDes,
                    ];
                }
            }
        }
        return array_values($dbTypeData);
    }

    /**
     * 获取备份数据的任务数据
     * @param array $dbDataList 数据库备份数据
     * @return array
     */
    private function getJobData(array $dbDataList): array
    {
        $jobData = [];
        foreach ($dbDataList as $row) {
            if (!isset($jobData[$row['task_uuid']])) {
                $jobData[$row['task_uuid']] = [
                    'job_uuid' => $row['task_uuid'],
                    'job_name' => $row['task_name'],
                    'db_type' => (int) $row['db_type'],
                    'create_time' => $row['task_create_time'],
                    'job_type' => (int) $row['task_type'],
                    'is_del' => !$row['is_not_del'],
                ];
            }
        }
        return array_values($jobData);
    }

    /**
     * 获取备份数据的实例信息
     * @param array $dbDataList 数据库备份数据
     * @return array
     */
    private function getInstanceData(array $dbDataList): array
    {
        $instanceData = [];
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        foreach ($dbDataList as $row) {
            $key = $row['agent_uuid'] . '_' . $row['instance_name'];
            $clusterFlag = !!$row['cluster_uuid'];
            $clusterNodeList = [];
            $backupInstanceList = [];
            if ($clusterFlag) {
                $key = $row['cluster_uuid'];
                $backupInstanceList = [
                    $row['agent_uuid'] => [
                        'agent_uuid' => $row['agent_uuid'],
                        'instance_name' => $row['instance_name'],
                    ],
                ];
                // 构建集群的各个节点信息
                $start = strpos($row['dir_path'], '(');
                $end = strrpos($row['dir_path'], ')');
                $agentStr = substr($row['dir_path'], $start + 1, $end - $start - 1);
                $agentList = explode(',', $agentStr);
                if ($row['db_type'] == $allDbType['TIDB']) {
                    $agentList = explode(', ', $agentStr);
                }
                foreach ($agentList as $agentInfoStr) {
                    $instanceInfo = explode('/', $agentInfoStr);
                    $agentIp = trim($instanceInfo[0]);
                    $agentInstanceName = trim($instanceInfo[1]);
                    $clusterNodeList[] = [
                        'ip' => $agentIp,
                        'name' => $agentInstanceName,
                    ];
                }
            }
            if (!isset($instanceData[$key])) {
                $instanceData[$key] = [
                    'instance_name' => $row['instance_name'],
                    'db_name' => $row['db_name'],
                    'db_uuid' => $row['db_uuid'],
                    'agent_uuid' => $row['agent_uuid'],
                    'agent_ip' => $row['agent_ip'],
                    'db_type' => (int) $row['db_type'],
                    'job_uuid_list' => [
                        $row['task_uuid'] => 1,
                    ],
                    'is_cluster' => $clusterFlag,
                    'cluster_uuid' => $row['cluster_uuid'],
                    'cluster_name' => $clusterFlag ? $row['cluster_name'] : '',
                    'cluster_node_list' => $clusterNodeList,
                    'backup_instance_list' => $backupInstanceList,
                    'dir_path' => $row['dir_path'],
                ];
            } else {
                $instanceData[$key]['job_uuid_list'][$row['task_uuid']] = 1;
                if ($clusterFlag) {
                    $instanceData[$key]['backup_instance_list'][$row['agent_uuid']] = [
                        'agent_uuid' => $row['agent_uuid'],
                        'instance_name' => $row['instance_name'],
                    ];
                }
            }
        }
        foreach ($instanceData as $key => $instanceInfo) {
            $instanceData[$key]['job_uuid_list'] = array_keys($instanceInfo['job_uuid_list']);
            $instanceData[$key]['backup_instance_list'] = array_values($instanceInfo['backup_instance_list']);
        }
        return array_values($instanceData);
    }

    /**
     * 获取备份数据的数据库信息
     * @param array $dbDataList 数据库备份数据
     * @return array
     */
    private function getDbData(array $dbDataList): array
    {
        $dbData = [];
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        foreach ($dbDataList as $row) {
            $key =  $row['agent_uuid'] . '_' . $row['instance_name'] . $row['db_name'];
            $clusterFlag = !!$row['cluster_uuid'];
            $clusterNodeList = [];
            $backupInstanceList = [];
            if ($clusterFlag) {
                $key = $row['cluster_uuid'] . $row['db_name'];
                $backupInstanceList = [
                    $row['agent_uuid'] => [
                        'agent_uuid' => $row['agent_uuid'],
                        'instance_name' => $row['instance_name'],
                    ],
                ];
                // 构建集群的各个节点信息
                $start = strpos($row['dir_path'], '(');
                $end = strrpos($row['dir_path'], ')');
                $agentStr = substr($row['dir_path'], $start + 1, $end - $start - 1);
                $agentList = explode(',', $agentStr);
                if ($row['db_type'] == $allDbType['TIDB']) {
                    $agentList = explode(', ', $agentStr);
                }
                foreach ($agentList as $agentInfoStr) {
                    $instanceInfo = explode('/', $agentInfoStr);
                    $agentIp = trim($instanceInfo[0]);
                    $agentInstanceName = trim($instanceInfo[1]);
                    $clusterNodeList[] = [
                        'ip' => $agentIp,
                        'name' => $agentInstanceName,
                    ];
                }
            }
            if (!isset($dbData[$key])) {
                $dbData[$key] = [
                    'db_name' => $row['db_name'],
                    'instance_name' => $row['instance_name'],
                    'agent_uuid' => $row['agent_uuid'],
                    'db_uuid' => $row['db_uuid'],
                    'db_type' => (int) $row['db_type'],
                    'job_uuid_list' => [
                        $row['task_uuid'] => 1,
                    ],
                    'is_cluster' => $clusterFlag,
                    'cluster_uuid' => $row['cluster_uuid'],
                    'cluster_name' => $clusterFlag ? $row['cluster_name'] : '',
                    'cluster_node_list' => $clusterNodeList,
                    'backup_instance_list' => $backupInstanceList,
                    'dir_path' => $row['dir_path'],
                ];
            } else {
                $dbData[$key]['job_uuid_list'][$row['task_uuid']] = 1;
                if ($clusterFlag) {
                    $dbData[$key]['backup_instance_list'][$row['agent_uuid']] = [
                        'agent_uuid' => $row['agent_uuid'],
                        'instance_name' => $row['instance_name'],
                    ];
                }
            }
        }
        foreach ($dbData as $key => $dbInfo) {
            $dbData[$key]['job_uuid_list'] = array_keys($dbInfo['job_uuid_list']);
            $dbData[$key]['backup_instance_list'] = array_values($dbInfo['backup_instance_list']);
        }
        return array_values($dbData);
    }

    /**
     * 获取数据库实例或单个数据库的备份点信息
     * @param array $params 全部请求参数
     * @return array
     */
    public function getDbBackupTimePoint(array $params): array
    {
        $jobsUuid = $params['jobs_uuid'];
        $jobType = (int) $params['job_type'];
        $isCluster = $params['is_cluster'];
        $agentClusterUuid = $params['agent_cluster_uuid'];
        $dbType = (int) $params['db_type'];
        $instanceName = $params['instance_name'];
        $setFlag = xphp_get_config('app', 'FLAG');
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $allBackupMode = xphp_get_config('db', 'BACKUP_MODE', 'db');
        $allModuleType = xphp_get_config('module', 'MODULE_TYPE');
        $moduleTypes = '(' . $allModuleType['DB'] . ',' . $allModuleType['BACKUP_COPY_CLIENT'] . ')';
        $sql = "SELECT bbt.timepoint, dbt.db_type, bbt.backup_mode, bbt.timepoint_uuid,
                    bbt.task_uuid, bbt.task_type, bbt.task_create_time,
                    bbt.encrypted_flag, bbt.importance_flag, bbt.remarks, bbt.detail,
                    bbt.total_size, bbt.write_size, bbt.storage_uuid, bbt.depend_point_uuid,
                    bbt.src_start_timepoint, bbt.src_end_timepoint, bbt.real_node_uuid,
                    dbt.instance_name, dbt.db_uuid, dbt.dir_path, dbt.db_name, dbt.db_config,
                    dbt.agent_uuid
                FROM bd_backup_timepoint bbt
                    INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                WHERE bbt.task_uuid = ? AND bbt.task_type = ? AND dbt.db_type = ? AND dbt.instance_name = ?
                    AND bbt.module_type IN $moduleTypes AND bbt.deleted_flag = ? AND bbt.available_flag = ?
                    AND bbt.import_flag = ? AND bbt.data_local_flag = ? ";
        $sqlParams = [
            $jobsUuid,
            $jobType,
            $dbType,
            $instanceName,
            $setFlag['UNSET'],
            $setFlag['SET'],
            $setFlag['UNSET'],
            $setFlag['SET'],
        ];
        $sql .= ' AND dbt.agent_uuid = ? AND dbt.db_type = ? ';
        $sqlParams[] = $agentClusterUuid;
        $sqlParams[] = $dbType;

        if (isset($params['db_uuid']) && $params['db_uuid']) {  // 数据库uuid
            $sql .= ' AND dbt.db_uuid = ? ';
            $sqlParams = array_merge($sqlParams, [$params['db_uuid']]);
        }

        // 查询所有备份点，用于构建备份点的完备点，公共表表达式(CET)太慢了
        $allData = $this->dbSelect($sql, $sqlParams);
        if (!is_array($allData)) {
            $allData = [];
        }

        if (isset($params['only_forever']) && $params['only_forever']) {  // 是否只返回永久备份点
            $sql .= " AND bbt.importance_flag = {$setFlag['SET']} ";
        }

        if (isset($params['backup_mode']) && $params['backup_mode']) {  // 是否只返回永久备份点
            if ($params['backup_mode'] == $allBackupMode['log_backup']) {  // 归档日志与日志编码一致
                if (isset($params['log_type']) && $params['log_type']) {
                    $archiveLogDbType = [  // 归档日志的数据库类别
                        $allDbType['ORACLE'],
                        $allDbType['DM'],
                        $allDbType['POSTGRE'],
                        $allDbType['ANTDB'],
                        $allDbType['KINGBASE'],
                        $allDbType['UXDB'],
                        $allDbType['HIGHGO'],
                        $allDbType['OPENGAUSS'],
                        $allDbType['VASTBASE'],
                    ];
                    if ($params['log_type'] == 1 && in_array($dbType, $archiveLogDbType)) {
                        $sql .= ' AND 1<>1 ';  // 搜索日志备份，但数据库类被是归档日志备份
                    } elseif ($params['log_type'] == 2 && !in_array($dbType, $archiveLogDbType)) {
                        $sql .= ' AND 1<>1 ';  // 搜索归档日志备份，但数据库类被不是归档日志备份
                    }
                } else {
                    $sql .= ' AND 1<>1 ';  // 日志和归档日志没有传log_type表示不能查询
                }
            }
            $sql .= " AND bbt.backup_mode = {$params['backup_mode']} ";
        }

        if (isset($params['start_time']) && $params['start_time']) {  // 备份点开始时间
            $sql .= ' AND bbt.timepoint >= ? ';
            $sqlParams = array_merge($sqlParams, [$params['start_time']]);
        }

        if (isset($params['end_time']) && $params['end_time']) {  // 备份点结束时间
            $sql .= ' AND bbt.timepoint <= ? ';
            $sqlParams = array_merge($sqlParams, [$params['end_time']]);
        }

        $data = $this->dbSelect($sql, $sqlParams);
        if (!is_array($data)) {
            $data = [];
        }
        $timepointUuidList = array_column($data, 'timepoint_uuid');
        $allStorageData = (new DataManage())->getStorageInfoByTimepoints($timepointUuidList);
        $rows = [];
        foreach ($data as $row) {
            $storageInfo = [];
            foreach ($allStorageData as $storageData) {
                if ($row['timepoint_uuid'] == $storageData['timepoint_uuid']) {
                    $storageInfo = [
                        'storage_uuid' => $storageData['storage_uuid'],
                        'storage_type' => $storageData['storage_type'],
                        'storage_status' => $storageData['storage_status'],
                        'storage_nickname' => $storageData['storage_nickname'],
                        'storage_online_flag' => $storageData['storage_online_flag'],
                        'node_online_flag' => $storageData['node_online_flag'],
                        'node_uuid' => $storageData['node_uuid'],
                        'node_nickname' => $storageData['node_nickname'],
                        'node_ip' => $storageData['node_ip'],
                        'node_hostname' => $storageData['node_hostname'],
                        'node_type' => $storageData['node_type'],
                    ];
                }
            }
            $config = json_decode($row['detail'], true);
            if ($config) {
                if (isset($config['compress_flag'])) {
                    $config['compress_flag'] = v1_parse_flag_to_bool($config['compress_flag']);
                }
                if (isset($config['compress_method'])) {
                    $config['compress_method'] = intval($config['compress_method']);
                }
                unset($config['password']);
                if (isset($config['password_auto_flag'])) {
                    $config['password_auto_flag'] = v1_parse_flag_to_bool($config['password_auto_flag']);
                }
                if (isset($config['real_storage_type'])) {
                    $config['real_storage_type'] = intval($config['real_storage_type']);
                }
            }
            $dbConfig = json_decode($row['db_config'], true);
            if (!$dbConfig) {
                $dbConfig = new \stdClass();
            }
            $rows[] = [
                'agent_uuid' => $row['agent_uuid'],
                'time_point' => $row['timepoint'],
                'db_type' => (int) $row['db_type'],
                'backup_mode' => (int) $row['backup_mode'],
                'time_point_uuid' => $row['timepoint_uuid'],
                'job_uuid' => $row['task_uuid'],
                'job_type' => (int) $row['task_type'],
                'job_create_time' => $row['task_create_time'],
                'instance_name' => $row['instance_name'],
                'db_name' => $row['db_name'],
                'db_uuid' => $row['db_uuid'],
                'dir_path' => $row['dir_path'],
                'is_encrypted' => v1_parse_flag_to_bool($row['encrypted_flag']),
                'config' => $config,
                'is_forever' => v1_parse_flag_to_bool($row['importance_flag']),
                'remark' => $row['remarks'] ?? '',
                'total_size' => (int) $row['total_size'],
                'write_size' => (int) $row['write_size'],
                'storage_info' => $storageInfo,
                'full_time_point_uuid' => $this->getFullTimePointByTimePoint(
                    $row['timepoint_uuid'],
                    (int) $row['backup_mode'],
                    $allData
                ),
                'db_config' => $dbConfig,
                'src_start_time_point' => $row['src_start_timepoint'],
                'src_end_time_point' => $row['src_end_timepoint'],
            ];
        }
        return $this->sendResult('', true, 200, [
            'rows' => $rows,
            'total' => count($rows),
        ]);
    }

    /**
     * 批量获取数据库实例的备份点信息
     * @param array $params 全部请求参数
     * @return array
     */
    public function getDbBackupTimePointList(array $params): array
    {
        $agentList = $params['agent_list'] ?? [];
        $dbType = (int) $params['db_type'];
        $setFlag = xphp_get_config('app', 'FLAG');
        $allModuleType = xphp_get_config('module', 'MODULE_TYPE');
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $allDbType = xphp_get_config('db', 'DB_TYPE');
        $moduleTypes = '(' . $allModuleType['DB'] . ',' . $allModuleType['BACKUP_COPY_CLIENT'] . ')';
        $sql = "SELECT bbt.timepoint, dbt.db_type, bbt.backup_mode, bbt.timepoint_uuid,
                    bbt.task_uuid, bbt.task_name, bbt.task_type, bbt.task_create_time,
                    bbt.encrypted_flag, bbt.importance_flag, bbt.remarks, bbt.detail,
                    bbt.total_size, bbt.write_size, bbt.storage_uuid, bbt.depend_point_uuid,
                    bbt.src_start_timepoint, bbt.src_end_timepoint, bbt.real_node_uuid, bbt.chain_uuid,
                    bbt.integrity_check_flag, bbt.merge_status, bbt.operation_status,
                    dbt.instance_name, dbt.db_uuid, dbt.dir_path, dbt.db_name, dbt.db_config, dbt.agent_uuid,
                    dbt.latest_src_end_timepoint, dbt.latest_timepoint_uuid, dbt.db_incarnation, dbt.resetlogs_time,
                    dbt.cluster_uuid, dbt.cluster_name, dbt.depend_task_uuid, dbt.checkpoint_time,
                    bt.task_uuid AS task_delete_flag,
                    bbtsi.virus_scan_status, bbtsi.integrity_check_status
                FROM bd_backup_timepoint bbt
                    INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                    LEFT JOIN bd_task bt ON bbt.task_uuid=bt.task_uuid
                    LEFT JOIN bd_backup_timepoint_safe_info bbtsi ON bbt.timepoint_uuid = bbtsi.timepoint_uuid
                WHERE dbt.db_type = ?
                    AND bbt.module_type IN $moduleTypes AND bbt.deleted_flag = ? AND bbt.available_flag = ?
                    AND bbt.import_flag = ? AND bbt.data_local_flag = ? ";
        $sqlParams = [
            $dbType,
            $setFlag['UNSET'],
            $setFlag['SET'],
            $setFlag['UNSET'],
            $setFlag['SET'],
        ];

        $filterList = [];
        // 代理筛选条件
        foreach ($agentList as $agentInfo) {
            $agentUuid = $agentInfo['agent_uuid'];
            $instanceName = $agentInfo['instance_name'];
            $jobUuid = $agentInfo['job_uuid'] ?? '';
            $subSql = "(dbt.agent_uuid = '{$agentUuid}' AND dbt.instance_name = '{$instanceName}'";
            if ($jobUuid) {
                $subSql .= " AND bbt.task_uuid = '{$jobUuid}' ";
            }
            $dbName = $agentInfo['db_name'] ?? '';
            if ($dbName && ($dbType == $allDbType['SQLSERVER'] || $dbType == $allDbType['SAPHANA'])) {  // SQL Server和SAP HANA可以按数据库名称筛选
                $subSql .= " AND dbt.db_name = '{$dbName}' ";
            }
            $subSql .= ')';
            $filterList[] = $subSql;
        }
        $filters = '(' . implode(' OR ', $filterList) . ')';
        $sql .= " AND $filters ";
        $allowTaskTypeList = [
            $allTaskType['DB_BACKUP']
        ];
        // 查询副本数据
        if (isset($params['with_copy_flag']) && $params['with_copy_flag']) {
            $allowTaskTypeList[] = $allTaskType['BACKUP_COPY'];
        }
        // 查询副本回传数据
        if (isset($params['with_copy_back_flag']) && $params['with_copy_back_flag']) {
            $allowTaskTypeList[] = $allTaskType['BACKUP_COPY_FETCH'];
        }
        $taskSql = implode(' OR ', array_fill(0, count($allowTaskTypeList), 'bbt.task_type = ?'));
        $sql .= " AND ( $taskSql ) ";
        $sqlParams = array_merge($sqlParams, $allowTaskTypeList);
        // 存储设备过滤
        if (isset($params['storage_uuid']) && $params['storage_uuid']) {
            $sql .= ' AND bbt.storage_uuid = ? ';
            $sqlParams[] = $params['storage_uuid'];
        }

        // 604升级到605，605 Oracle不支持恢复604的点
        if ($dbType == $allDbType['ORACLE']) {
            $sql .= " AND dbt.resetlogs_time != 0 ";
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

        // 排序
        $sql .= " ORDER BY bbt.timepoint ASC ";

        $data = $this->dbSelect($sql, $sqlParams);
        if (!is_array($data)) {
            $data = [];
        }
        $timepointUuidList = array_column($data, 'timepoint_uuid');
        $allStorageData = (new DataManage())->getStorageInfoByTimepoints($timepointUuidList);
        $rows = [];

        // 备份链子
        $chainList = [];
        foreach ($data as $row) {
            $storageInfo = [];
            $clusterFlag = !!$row['cluster_uuid'];
            foreach ($allStorageData as $storageData) {
                if ($row['timepoint_uuid'] == $storageData['timepoint_uuid']) {
                    $storageInfo = [
                        'storage_uuid' => $storageData['storage_uuid'],
                        'storage_type' => $storageData['storage_type'],
                        'storage_status' => $storageData['storage_status'],
                        'storage_nickname' => $storageData['storage_nickname'],
                        'storage_online_flag' => $storageData['storage_online_flag'],
                        'node_online_flag' => $storageData['node_online_flag'],
                        'node_uuid' => $storageData['node_uuid'],
                        'node_nickname' => $storageData['node_nickname'],
                        'node_ip' => $storageData['node_ip'],
                        'node_hostname' => $storageData['node_hostname'],
                        'node_type' => $storageData['node_type'],
                    ];
                }
            }
            $config = json_decode($row['detail'], true);
            if ($config) {
                if (isset($config['compress_flag'])) {
                    $config['compress_flag'] = v1_parse_flag_to_bool($config['compress_flag']);
                }
                if (isset($config['compress_method'])) {
                    $config['compress_method'] = intval($config['compress_method']);
                }
                unset($config['password']);
                if (isset($config['password_auto_flag'])) {
                    $config['password_auto_flag'] = v1_parse_flag_to_bool($config['password_auto_flag']);
                }
                if (isset($config['real_storage_type'])) {
                    $config['real_storage_type'] = intval($config['real_storage_type']);
                }
            }
            $dbConfig = json_decode($row['db_config'], true);
            if (!$dbConfig) {
                $dbConfig = new \stdClass();
            }
            $timepointStatus = $this->getTimePointStatus(
                $row['merge_status'],
                $row['operation_status'],
                $row['virus_scan_status'],
                $row['integrity_check_status']
            );
            if ($row['chain_uuid']) {
                if (!isset($chainList[$row['chain_uuid']])) {
                    $chainList[$row['chain_uuid']] = [];
                }
                $chainList[$row['chain_uuid']][] = $row;
            }
            $timepointStatus['merge_status'] = intval($row['merge_status']);
            $timepointStatus['operation_status'] = intval($row['operation_status']);
            $timepointStatus['virus_scan_status'] = intval($row['virus_scan_status']);
            $timepointStatus['integrity_check_status'] = intval($row['integrity_check_status']);
            $rows[] = [
                'agent_uuid' => $row['agent_uuid'],
                'time_point' => $row['timepoint'],
                'db_type' => (int) $row['db_type'],
                'backup_mode' => (int) $row['backup_mode'],
                'time_point_uuid' => $row['timepoint_uuid'],
                'job_uuid' => $row['task_uuid'],
                'job_name' => $row['task_name'],
                'job_type' => (int) $row['task_type'],
                'job_create_time' => $row['task_create_time'],
                'job_delete_flag' => !$row['task_delete_flag'],
                'instance_name' => $row['instance_name'],
                'db_name' => $row['db_name'],
                'db_uuid' => $row['db_uuid'],
                'dir_path' => $row['dir_path'],
                'is_encrypted' => v1_parse_flag_to_bool($row['encrypted_flag']),
                'config' => $config,
                'is_forever' => v1_parse_flag_to_bool($row['import_flag']),
                'remark' => $row['remarks'] ?? '',
                'total_size' => (int) $row['total_size'],
                'write_size' => (int) $row['write_size'],
                'storage_info' => $storageInfo,
                'full_time_point_uuid' => $this->getFullTimePointByTimePoint(
                    $row['timepoint_uuid'],
                    (int) $row['backup_mode'],
                    $data
                ),
                'is_cluster' => $clusterFlag,
                'cluster_uuid' => $row['cluster_uuid'],
                'cluster_name' => $row['cluster_name'],
                'db_config' => $dbConfig,
                'src_start_time_point' => $row['src_start_timepoint'],
                'src_end_time_point' => $row['src_end_timepoint'],
                'latest_src_end_timepoint' => $row['latest_src_end_timepoint'],
                'latest_timepoint_uuid' => $row['latest_timepoint_uuid'],
                'db_incarnation' => $row['db_incarnation'],
                'resetlogs_time' => $row['resetlogs_time'],
                'depend_task_uuid' => $row['depend_task_uuid'] ?: '',  // Oracle用
                'checkpoint_time' => $row['checkpoint_time'] ?: 0,  // Oracle用
                'chain_uuid' => $row['chain_uuid'] ?: '',  // Oracle用
                'integrity_check_flag' => v1_parse_flag_to_bool($row['integrity_check_flag']),  // 完整性校验开关
                'timepoint_status' => $timepointStatus,  // 备份点状态
            ];
        }
        // 过滤掉Oracle没有完备点的备份链子
        if ($dbType == $allDbType['ORACLE']) {
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
            $rows = array_filter($rows, function ($row) use ($excludeChainUuidList) {
                return !in_array($row['chain_uuid'], $excludeChainUuidList);
            });
        }
        return $this->sendResult('', true, 200, [
            'rows' => $rows,
            'total' => count($rows),
        ]);
    }

    /**
     * 获取备份点的完备点
     * @param string $timePointUuid 备份点uuid
     * @param int    $backupMode    备份模式【1完备...】
     * @param array  $allData       所有数据
     * @return string
     */
    private function getFullTimePointByTimePoint(string $timePointUuid, int $backupMode, array $allData): string
    {
        $fullBackupMode = xphp_get_config('db', 'BACKUP_MODE', 'db')['full_backup'];
        if ($backupMode == $fullBackupMode) {
            return $timePointUuid;
        }

        // 用索引构建比较快
        $dependentUuidList = [
            $timePointUuid => 1,
        ];
        $fullTimePointUuid = null;
        $loopFlag = true;
        while ($loopFlag) {
            $loopFlag = false;
            foreach ($allData as $point) {
                if (
                    isset($dependentUuidList[$point['timepoint_uuid']]) &&  // 这个备份点被需要查询的点依赖
                    !isset($dependentUuidList[$point['depend_point_uuid']]) // 这个备份点的以来点还没有加入缓存中
                ) {
                    if ($point['backup_mode'] == $fullBackupMode) {
                        $fullTimePointUuid = $point['timepoint_uuid'];
                        break;
                    }
                    $loopFlag = true;
                    $dependentUuidList[$point['depend_point_uuid']] = 1;
                }
            }
            if (!is_null($fullTimePointUuid)) {
                break;
            }
        }
        if (is_null($fullTimePointUuid)) {
            return $timePointUuid;
        }
        return $fullTimePointUuid;
    }

    /**
     * 校验数据加密密码是否正确
     * @param string $encryptPassword 加密的密码
     * @param string $timePointUuid   备份点uuid
     * @return array
     */
    public function verifyDbBackupPassword(string $encryptPassword, string $timePointUuid): array
    {
        $sql = "SELECT bbt.timepoint_uuid, bbt.detail FROM bd_backup_timepoint bbt WHERE bbt.timepoint_uuid = ? ";
        $timePointInfo = $this->dbSelect($sql, [$timePointUuid]);
        if (!$timePointInfo || !is_array($timePointInfo)) {
            return $this->sendResult(xphp_get_lang('WEB_DB_TIME_POINT_NOT_EXISTS'), false, 0);
        }
        $detail = json_decode($timePointInfo[0]['detail'], true);
        if (
            !$detail ||
            !isset($detail['password']) ||
            !isset($detail['password_auto_flag']) ||
            $detail['password_auto_flag'] == v1_parse_bool_to_flag(true)
        ) {
            // 没有设密码或者自动生成密码
            return $this->sendResult(xphp_get_lang('WEB_COMMON_VERIFY_PASSWORD_SUCCESS'));
        }
        if (base64_decode($encryptPassword) != v1_pt_pass_decrypt($detail['password'])) {
            return $this->sendResult(xphp_get_lang('WEB_COMMON_VERIFY_PASSWORD_ERROR'), false, 0);
        }
        return $this->sendResult(xphp_get_lang('WEB_COMMON_VERIFY_PASSWORD_SUCCESS'));
    }

    /**
     * 删除数据库备份时间点
     * @param array $timePointList 需要删除的备份时间点列表
     * @param array $backupDbList  需要删除的数据库列表
     * @param int   $dbType        数据库类别
     * @return array
     */
    public function deleteDbBackupTimePoint(array $timePointList, array $backupDbList, int $dbType): array
    {
        $opName = 'BD_BACKUP_POINT_OP_BATCH_DELETE';
        $operate = $this->pfOpcode->getOpcodeDes($opName);
        $dbTimePointList = $this->getTimePointListByDb($backupDbList, $dbType);
        $timePointList = array_merge($timePointList, $dbTimePointList);
        $timePointList = array_values(array_unique($timePointList));

        $checkRet = $this->checkTimePointListDeleteEnable($timePointList, $dbType);
        if (!$checkRet['success']) {
            return $checkRet;
        }
        $allNodeMapping = $this->getAllNodeUuidByTimePoint("'" . implode("', '", $timePointList) . "'");

        foreach ($allNodeMapping as $nodeUuid => $timePointUuidList) {
            $retService = $this->service()->deleteDbBackupTimePointService(
                $timePointUuidList,
                $nodeUuid,
                $dbType
            );
            if (!$retService['result']) {
                $this->muOpResult(false, $operate, $retService['msg'], 0, $retService['errorCode']);
                return $this->sendResult(xphp_get_lang('WEB_DB_DELETE_TIME_POINT_ERROR'), false, 0);
            }
        }
        $detail = array_map(function ($timePointUuid) {
            return sprintf(xphp_get_lang('WEB_DB_DELETE_TIME_POINT_DETAIL'), $timePointUuid);
        }, $timePointList);
        return $this->sendResult(xphp_get_lang('WEB_DB_DELETE_TIME_POINT_SUCCESS'), true, 200, $detail);
    }

    /**
     * 根据数据库获取时间点列表
     * @param array $backupDbList 需要删除的数据库列表
     * @param int   $dbType       数据库类别
     * @return array
     */
    private function getTimePointListByDb(array $backupDbList, int $dbType): array
    {
        $timePointList = [];
        $sql = "SELECT bbt.timepoint_uuid
                FROM bd_backup_timepoint bbt
                    LEFT JOIN db_backup_timepoint dbt on bbt.timepoint_uuid = dbt.timepoint_uuid
                WHERE bbt.task_uuid = ? AND dbt.instance_name = ? AND dbt.db_name = ?
                    AND dbt.agent_uuid = ? AND dbt.db_type = ? ";
        foreach ($backupDbList as $dbInfo) {
            $sqlParams = [
                $dbInfo['job_uuid'],
                $dbInfo['instance_name'],
                $dbInfo['db_name'],
                $dbInfo['agent_uuid'],
                $dbType
            ];
            $data = $this->dbSelect($sql, $sqlParams);
            if (!$data || !is_array($data)) {
                continue;
            }
            foreach ($data as $row) {
                $timePointList[] = $row['timepoint_uuid'];
            }
        }
        return $timePointList;
    }

    /**
     * 检查是否拥有主机保护的操作权限
     * @param array $userUuidList 操作资源的拥有者uuid列表
     * @return bool
     */
    private function checkHostOperatePermission(array $userUuidList): bool
    {
        // 关联管理用户判断 数据库保护 - 操作 db_protect_operate
        $checkOperate = xphp_check_operate('db_protect_operate', $userUuidList);
        if (!$checkOperate) {
            // 没权限操作
            return false;
        }
        return true;
    }

    /**
     * 获取完备点uuid
     * @param string $timePointUuid 备份点uuid
     * @param array  $timePointList 所有备份点
     * @return string
     */
    private function getFullTimePointUuid(string $timePointUuid, array $timePointList): string
    {
        $allBackupMode = xphp_get_config('task', 'BACKUP_MODE');
        $sql = "SELECT backup_mode FROM bd_backup_timepoint WHERE timepoint_uuid = ? ";
        $timePointInfo = $this->dbSelect($sql, [$timePointUuid]);
        if (!is_array($timePointInfo) || $timePointInfo[0]['backup_mode'] == $allBackupMode['FULL']) {
            return $timePointUuid;
        }
        // 其他备份点
        return $this->getFullTimePointByTimePoint($timePointUuid, $timePointInfo[0]['backup_info'], $timePointList);
    }

    /**
     * 根据完备点获取备份链
     * @param string $timePointUuid 备份点uuid
     * @param string $jobUuid       任务uuid
     * @return array
     */
    public function getBackupChainByFullTimePoint(string $timePointUuid, string $jobUuid): array
    {
        $sql = "SELECT timepoint_uuid, depend_point_uuid FROM bd_backup_timepoint WHERE task_uuid = ? ";
        $timePointList = $this->dbSelect($sql, [$jobUuid]);
        if (!is_array($timePointList)) {
            $timePointList = [];
        }
        $fullTimePointUuid = $this->getFullTimePointUuid($timePointUuid, $timePointList);

        $backupChain = [
            $fullTimePointUuid => 1
        ];
        $loopFlag = true;
        while ($loopFlag) {
            $loopFlag = false;
            foreach ($timePointList as $row) {
                if (
                    isset($backupChain[$row['depend_point_uuid']]) &&  // 当前备份点依赖缓存中的备份点
                    !isset($backupChain[$row['timepoint_uuid']])  // 当前缓存中没有这个备份点
                ) {
                    $backupChain[$row['timepoint_uuid']] = 1;
                }
            }
        }
        return array_keys($backupChain);
    }

    /**
     * 判断时间点能否被删除
     * @param array $timePointList 备份时间列表
     * @param int   $dbType        数据库类别
     * @return array
     */
    public function checkTimePointListDeleteEnable(array $timePointList, int $dbType): array
    {
        if (!$timePointList) {  // 备份点为空，无法删除
            return $this->sendResult(xphp_get_lang('WEB_DB_DELETE_TIME_POINT_NOT_EMPTY'), false, 0);
        }

        /**
         * 1、恢复任务无论是什么状态，使用的备份链都不能删除
         * 2、备份任务处于运行、异常状态，该备份任务的全部链都不能删除
         * 3、备份任务处于等待状态，最近一条备份链不能删除
         * 4、当这个备份任务有副本任务的时候，触发保留策略时会检查这个副本任务是不是处于停止状态
         *      1) 如果副本任务在停止状态那就不管
         *      2) 如果不是停止状态，那进一步检查，副本任务拷贝过现在触发保留策略的这条链没有
         *          1> 如果没有拷贝过，那就不能删除这条链
         *          2> 如果拷贝过，那就可以删除
         * 5、Oracle数据库不能删除最后一个备份链
         * 6、处于磁带上的备份点不能删除(tape = 10)
         *
         * 名词解释
         * 1、备份链: 完备点+所有依赖他的其他点所组成的一条链子
         */
        $allTaskType = xphp_get_config('task', 'TASKTYPE');
        $allTaskStatus = xphp_get_config('task', 'TASKSTATUS');
        $allStorageType = xphp_get_config('resource', 'BD_STORAGE_TYPE');
        $allDbType = xphp_get_config('db', 'DB_TYPE');

        $timePoints = "'" . implode("', '", $timePointList) . "'";

        // 验证所有备份点都存在
        $sql = "SELECT timepoint_uuid FROM bd_backup_timepoint WHERE timepoint_uuid IN ($timePoints) ";
        $timepointData = $this->dbSelect($sql);
        if (is_array($timepointData) && count($timepointData) != count($timePointList)) {
            $findTimepointList = array_column($timepointData, 'timepoint_uuid');
            $notFoundTimepointList = array_diff($timePointList, $findTimepointList);
            return $this->sendResult(
                sprintf(xphp_get_lang('WEB_DB_DELETE_TIME_POINT_NOT_FOUND'), implode('、', $notFoundTimepointList)),
                false,
                0
            );
        }

        $sql = "SELECT task_uuid FROM db_list WHERE timepoint_uuid IN ($timePoints)";
        $data = $this->dbSelect($sql);
        // 1. 恢复任务无论是什么状态，使用的备份链都不能删除
        if ($data) {
            return $this->sendResult(xphp_get_lang('WEB_DB_DELETE_TIME_POINT_IN_RECOVERY'), false, 0);
        }

        // 6. 处于磁带上的备份点不能删除(tape = 10)
        $sql = "SELECT *
                FROM bd_backup_timepoint bbt
                    INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                WHERE bbt.timepoint_uuid IN ($timePoints) AND bsr.storage_type = ? ";
        $tapePointData = $this->dbSelect($sql, [$allStorageType['TAPE']]);
        if (is_array($tapePointData) && $tapePointData) {
            return $this->sendResult(xphp_get_lang('UI_TAPE_DELETE_BACKUP_POINT_TIPS'), false, 0);
        }

        /**
         * 验证权限
         */
        $sql = "SELECT user_uuid FROM bd_backup_timepoint WHERE timepoint_uuid IN ($timePoints)";
        $userUuidData = $this->dbSelect($sql);
        if (!is_array($userUuidData)) {
            $userUuidData = [];
        }
        if (!$this->checkHostOperatePermission(array_column($userUuidData, 'user_uuid'))) {
            return $this->sendResult(xphp_get_lang('WEB_ERROR_BD_PRIVILEGE_ERROR'), false, 0);
        }

        // 获取这些备份点任务的所有运行状态
        $taskList = $this->getTaskStatusByTimePoint($timePoints, $allTaskType['DB_BACKUP']);
        if (count($timePointList) === 1 && count($taskList)) {  // 删除单个完全备份点
            $timePointList = $this->getBackupChainByFullTimePoint($timePointList[0], $taskList[0]['task_uuid']);
        }

        $backupTaskList = array_unique(array_column($taskList, 'task_uuid'));  // 备份任务的uuid列表
        $backupChainList = [];  // 备份链列表
        $backupTimePointMap = [];
        foreach ($taskList as $taskInfo) {
            if (!isset($backupTimePointMap[$taskInfo['task_uuid']])) {
                $backupTimePointMap[$taskInfo['task_uuid']] = [];
            }
            $backupTimePointMap[$taskInfo['task_uuid']][] = $taskInfo['timepoint_uuid'];
            // 2. 备份任务处于运行、异常状态，该备份任务的全部链都不能删除
            if (
                $taskInfo['task_status'] == $allTaskStatus['RUNNING']
                || $taskInfo['task_status'] == $allTaskStatus['NETWORK_FAULT']
                || $taskInfo['task_status'] == $allTaskStatus['ABNORMAL']
                || $taskInfo['task_status'] == $allTaskStatus['ERROR']
                || $taskInfo['task_status'] == $allTaskStatus['STOPPING']
                || $taskInfo['task_status'] == $allTaskStatus['STARTING']
            ) {
                return $this->sendResult(xphp_get_lang('WEB_DB_DELETE_TIME_POINT_IN_BACKUP'), false, 0);
            }
            // 3. 备份任务处于等待，最近一条备份链不能删除
            if ($taskInfo['task_status'] == $allTaskStatus['WAITTING']) {
                if (!isset($backupChainList[$taskInfo['task_uuid']])) {
                    $backupChainList[$taskInfo['task_uuid']] = $this->getLastBackupChain($taskInfo['task_uuid']);
                }
                $backupChain = $backupChainList[$taskInfo['task_uuid']];
                foreach ($timePointList as $timePointUuid) {
                    if (isset($backupChain[$timePointUuid])) {  // 标识备份点在最后的备份连上，不能删除
                        return $this->sendResult(xphp_get_lang('WEB_DB_DELETE_TIME_POINT_IN_BACKUP'), false, 0);
                    }
                }
            }
            // 5. Oracle的最后一条备份链不论什么情况都不能删除 bug#10607
            if ($dbType == $allDbType['ORACLE']) {
                if (!isset($backupChainList[$taskInfo['task_uuid']])) {
                    $backupChainList[$taskInfo['task_uuid']] = $this->getLastBackupChain($taskInfo['task_uuid']);
                }
                $backupChain = $backupChainList[$taskInfo['task_uuid']];
                foreach ($timePointList as $timePointUuid) {
                    if (isset($backupChain[$timePointUuid])) {  // 标识备份点在最后的备份连上，不能删除
                        return $this->sendResult(
                            xphp_get_lang('WEB_DB_DELETE_TIME_POINT_ORACLE_NEWEST_BACKUP_CHAIN'),
                            false,
                            0
                        );
                    }
                }
            }
        }

        // 数据库副本任务查询
        if ($backupTaskList) {
            $backupTaskDes = implode("','", $backupTaskList);
            // 查询副本任务
            $sql = "SELECT bt.task_uuid, bt.task_status, cl.source_task_uuid
                    FROM copy_list cl INNER JOIN bd_task bt ON cl.task_uuid=bt.task_uuid
                    WHERE source_task_uuid IN ('$backupTaskDes')";
            $copyTaskList = $this->dbSelect($sql);
            if (is_array($copyTaskList) && $copyTaskList) {
                // 查询副本备份点
                $copyTaskUuids = "'" . implode("','", array_map(function ($item) {
                        return $item['task_uuid'];
                    }, $copyTaskList)) . "'";
                $sql = "SELECT task_uuid, src_timepoint_uuid
                        FROM bd_backup_timepoint
                        WHERE copy_flag = ? AND task_uuid IN ($copyTaskUuids)";
                $copyTimePointList = $this->dbSelect($sql, [xphp_get_config('app', 'FLAG')['SET']]);
                $copyTimePointMap = [];
                if ($copyTimePointList && is_array($copyTimePointList)) {
                    foreach ($copyTimePointList as $copyTimePoint) {
                        if (!isset($copyTimePointMap[$copyTimePoint['task_uuid']])) {
                            $copyTimePointMap[$copyTimePoint['task_uuid']] = [];
                        }
                        $copyTimePointMap[$copyTimePoint['task_uuid']][] = $copyTimePoint['src_timepoint_uuid'];
                    }
                }
                foreach ($copyTaskList as $copyTaskInfo) {
                    if ($copyTaskInfo['task_status'] == $allTaskStatus['STOPPED']) {
                        continue;
                    }
                    $diff = array_diff(
                        $backupTimePointMap[$copyTaskInfo['source_task_uuid']],
                        $copyTimePointMap[$copyTaskInfo['task_uuid']] ?? []
                    );
                    if (count($diff)) {
                        return $this->sendResult(
                            xphp_get_lang('WEB_DB_DELETE_TIMEPOINT_IN_COPY_TASK_WARNING'),
                            false,
                            0
                        );
                    }
                }
            }
        }

        // 权限判断
        if(v1_auth_need_check_look()) {
            $resourceUuidSql = v1_auth_get_users('db_protect');
            // 根据 $timepointuuid 数组获取所有的用户ID，判读是否是都是自己的
            $sql = "SELECT task_name FROM bd_backup_timepoint
                    WHERE user_uuid NOT IN ($resourceUuidSql) AND timepoint_uuid IN ($timePoints) ";
            $chkList = $this->dbSelect($sql);
            if ($chkList && is_array($chkList)) {
                // 表示有不属于自己的资源 那么提示错误
                $msg = implode(';', array_unique(array_column($chkList, 'task_name')));
                return $this->sendResult(sprintf(xphp_get_lang('WEB_COMMON_PRIVILEGE_ERROR'), $msg), false, 0);
            }
        }
        return $this->sendResult('');
    }

    /**
     * 获取这些备份点任务的所有运行状态
     * @param string $timePoints   备份时间点uuid
     * @param int    $dbBackupType 备份任务的类别
     * @return array
     */
    public function getTaskStatusByTimePoint(string $timePoints, int $dbBackupType): array
    {
        $sql = "SELECT bt.task_name, bt.task_status, bt.task_uuid, bbt.timepoint_uuid, dbt.db_type
                FROM bd_task bt
                  INNER JOIN bd_backup_timepoint bbt ON bbt.task_uuid = bt.task_uuid
                  INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                WHERE bbt.timepoint_uuid IN ($timePoints)
                  AND bt.task_type = $dbBackupType ";
        $taskList = $this->dbSelect($sql);
        if (!is_array($taskList)) {
            $taskList = [];
        }
        return $taskList;
    }

    /**
     * 获取备份任务最后一条备份链
     * @param string $jobUuid 任务uuid
     * @return array
     */
    public function getLastBackupChain(string $jobUuid): array
    {
        $backupChain = [];
        $allBackupMode = xphp_get_config('task', 'BACKUP_MODE');
        // 公用表表达式速度太慢了
        // 1. 查询最后一个完备点
        $sql = "SELECT timepoint_uuid, depend_point_uuid, timepoint, task_uuid, task_name
                FROM bd_backup_timepoint
                WHERE task_uuid = ? AND backup_mode = ?
                ORDER BY timepoint DESC LIMIT 1 ";
        $fullTimePoint = $this->dbSelect($sql, [$jobUuid, $allBackupMode['FULL']]);
        if (!is_array($fullTimePoint)) {
            return $backupChain;
        }

        // 2. 大于等于最后一个完备点时间的备份点为备份链
        $sql = "SELECT timepoint_uuid, depend_point_uuid, task_uuid, task_name
                FROM bd_backup_timepoint WHERE task_uuid = ? AND timepoint >= ? ";
        $allTimePointList = $this->dbSelect($sql, [$jobUuid, $fullTimePoint[0]['timepoint']]);
        if (!$allTimePointList) {
            return $backupChain;
        }
        foreach ($allTimePointList as $row) {
            $backupChain[$row['timepoint_uuid']] = [
                'task_uuid' => $row['task_uuid'],
                'task_name' => $row['task_name'],
                'timepoint_uuid' => $row['timepoint_uuid'],
            ];
        }
        return $backupChain;
    }

    /**
     * 根据时间点UUID得到所在节点UUID
     * @param string $timePoints 备份点uuid
     * @return array
     */
    private function getAllNodeUuidByTimePoint(string $timePoints): array
    {
        $sql = "SELECT bsr.node_uuid, bbt.timepoint_uuid
                FROM bd_backup_timepoint bbt
                    INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid = bsr.storage_uuid
                WHERE bbt.timepoint_uuid IN ($timePoints) ";
        $data = $this->dbSelect($sql);
        if (!$data || !is_array($data)) {
            $data = [];
        }
        $allNodeMapping = [];
        foreach ($data as $row) {
            if (!isset($allNodeMapping)) {
                $allNodeMapping[$row['node_uuid']] = [];
            }
            $allNodeMapping[$row['node_uuid']][] = $row['timepoint_uuid'];
        }
        return $allNodeMapping;
    }

    /**
     * 读取文件内容
     * @param array  $timePoint  时间点信息
     * @param string $fileFormat 路径格式
     * @return array|string|string[]
     */
    private function getFileContentByFilePath(array $timePoint, string $fileFormat)
    {
        $filePath = sprintf($fileFormat, $timePoint['storage_uuid'], $timePoint['timepoint_uuid']);
        $readResult = $this->service()->readFileContentService($filePath, $timePoint['node_uuid']);
        if ($readResult['result']) {
            return str_replace("\n", "\n", $readResult['msg']['file_content']);
        }
        return '';
    }

    /**
     * 获取配置文件内容
     * @param string $timePointUuid 时间点uuid
     * @param int    $dbType        数据库类别
     * @return array
     */
    public function getConfigFileContent(string $timePointUuid, int $dbType): array
    {
        $sql = "SELECT bbt.storage_uuid, bbt.timepoint_uuid, bsr.node_uuid, dbt.db_config
                FROM bd_backup_timepoint bbt
                    INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid=bsr.storage_uuid
                    INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid=dbt.timepoint_uuid
                WHERE bbt.timepoint_uuid = ? AND dbt.db_type = ? ";
        $data = $this->dbSelect($sql, [$timePointUuid, $dbType]);
        if (!is_array($data) || !$data) {
            return $this->sendResult(xphp_get_lang('WEB_DB_TIME_POINT_NOT_EXISTS'), false, 0);
        }
        $dbConfig = json_decode($data[0]['db_config'], true);
        return $this->sendResult(xphp_get_lang('WEB_PUBLIC_SUCCESS'), true, 200, [
            'oracle' => [
                'pfile' => $this->getFileContentByFilePath($data[0], xphp_get_config('db', 'ORACLE_SPFILE_PATH', 'db')),
                'listener' =>
                    $this->getFileContentByFilePath($data[0], xphp_get_config('db', 'ORACLE_LISTENER_PATH', 'db')),
                'listener_path' => $dbConfig['config_file_path']['listener_file_path'] ?? '',
                'tnsnames' =>
                    $this->getFileContentByFilePath($data[0], xphp_get_config('db', 'ORACLE_TNSNAMES_PATH', 'db')),
                'tnsnames_path' => $dbConfig['config_file_path']['tnsnames_file_path'] ?? '',
                'sqlnet' =>
                    $this->getFileContentByFilePath($data[0], xphp_get_config('db', 'ORACLE_SQLNET_PATH', 'db')),
                'sqlnet_path' => $dbConfig['config_file_path']['sqlnet_file_path'] ?? '',
                'password_path' => $dbConfig['config_file_path']['password_file_path'] ?? '',
            ]
        ]);
    }

    /**
     * 解析并转换spfile文件
     * @param array $params 参数
     * @return array
     */
    public function parseConfigFileContent (array $params): array
    {
        $sql = "SELECT bbt.storage_uuid, bbt.timepoint_uuid, bsr.node_uuid, dbt.db_config
                FROM bd_backup_timepoint bbt
                    INNER JOIN bd_storage_resource bsr ON bbt.storage_uuid=bsr.storage_uuid
                    INNER JOIN db_backup_timepoint dbt ON bbt.timepoint_uuid=dbt.timepoint_uuid
                WHERE bbt.timepoint_uuid = ? AND dbt.db_type = ? ";
        $data = $this->dbSelect($sql, [$params['timepoint_uuid'], $params['db_type']]);
        if (!is_array($data) || !$data) {
            return $this->sendResult(xphp_get_lang('WEB_DB_TIME_POINT_NOT_EXISTS'), false, 0);
        }
        $spfilePath = sprintf(xphp_get_config('db', 'ORACLE_SPFILE_PATH', 'db'), $data[0]['storage_uuid'], $data[0]['timepoint_uuid']);
        $opName = 'DB_INSTANCE_OP_PARSE_AND_CONVERT_SPFILE';
        $operate = (new DbProtectOpcode())->getOpcodeDes($opName);
        $parseResult = $this->service()->parseAndConvertSpfileService(
            intval($params['db_type']),
            $data[0]['node_uuid'],
            $spfilePath,
            $params['new_instance_name'],
            $params['oracle_home_path'],
            $params['oracle_base_path']
        );
        if (!$parseResult['result']) {
            $this->muOpResult(false, $operate, $parseResult['msg'], 0, $parseResult['errorCode']);
            return $this->sendResult($parseResult['msg'], false, 0);
        }

        // 解析文件内容
        $spfileContent = $parseResult['msg']['spfile_configure_content'];
        $configList = array_filter(explode("\n", $spfileContent));
        $parseConfigList = [];
        foreach ($configList as $config) {
            $config = explode('=', $config);
            $configName = trim($config[0]);
            array_shift($config);
            $configValue = trim(implode('=', $config));
            $multiConfigValueList = [];
            $multiValueFlag = false;
            $showName = $configName;
            $showValue = $configValue;
            $asteriskFlag = false;  // 配置名是否有星号
            $singleQuoteFlag = false;  // 配置值是否有单引号
            $doubleQuoteFlag = false;  // 配置值是否有双引号
            $asteriskResult = preg_match('/^\*\..+/', $configName);
            if ($asteriskResult) {
                $asteriskFlag = true;
                $showName = substr($configName, 2);
            }
            if ($configValue[0] == "'" && $configValue[strlen($configValue) - 1] == "'") {
                $singleQuoteFlag = true;
                $showValue = substr($configValue, 1, strlen($configValue) - 2);
            }
            if ($configValue[0] == '"' && $configValue[strlen($configValue) - 1] == '"') {
                $doubleQuoteFlag = true;
                $showValue = substr($configValue, 1, strlen($configValue) - 2);
            }
            if ($configName == '*.control_files') {
                $multiValueFlag = true;
                $valueList = explode(',', $configValue);
                $valueList = array_map('trim', $valueList);
                foreach ($valueList as $value) {
                    $tmpShowValue = $value;
                    if ($singleQuoteFlag || $doubleQuoteFlag) {
                        $tmpShowValue = substr($value, 1, strlen($value) - 2);
                    }
                    $multiConfigValueList[] = [
                        'show_value' => $tmpShowValue,
                        'config_value' => $value,
                    ];
                }
            }
            $parseConfigList[] = [
                'show_name' => $showName,
                'config_name' => $configName,
                'show_value' => $showValue,
                'config_value' => $configValue,
                'multi_value_flag' => $multiValueFlag,
                'multi_config_value_list' => $multiConfigValueList,
                'asterisk_flag' => $asteriskFlag,
                'single_quote_flag' => $singleQuoteFlag,
                'double_quote_flag' => $doubleQuoteFlag,
            ];
        }
        return $this->sendResult('', true, 200, [
            'spfile_config_list' => $parseConfigList,
        ]);
    }

    /**
     * 获取数据库备份存储列表
     * @param array $params 参数
     * @return array
     */
    public function getDbBackupStorageList(array $params): array
    {
        $setFlag = xphp_get_config('app', 'FLAG');
        $allModuleType = xphp_get_config('module', 'MODULE_TYPE');
        $moduleTypes = '(' . $allModuleType['DB'] . ',' . $allModuleType['BACKUP_COPY_CLIENT'] . ')';
        $sql = "SELECT bbt.storage_uuid, bbt.timepoint_uuid, bbt.backup_mode, bbt.chain_uuid,
                    dbt.db_type
                FROM db_backup_timepoint dbt
                    INNER JOIN bd_backup_timepoint bbt ON bbt.timepoint_uuid = dbt.timepoint_uuid
                WHERE bbt.deleted_flag = ? AND bbt.available_flag = ? AND bbt.import_flag = ?
                    AND bbt.module_type IN $moduleTypes AND bbt.data_local_flag = ? ";
        $sqlParams = [
            $setFlag['UNSET'],
            $setFlag['SET'],
            $setFlag['UNSET'],
            $setFlag['SET'],
        ];
        if (isset($params['db_type'])) {
            $sql .= " AND dbt.db_type = ? ";
            $sqlParams[] = intval($params['db_type']);
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
        $dbData = $this->dbSelect($sql, $sqlParams);
        if (!is_array($dbData) || !$dbData) {
            return [
                'total' => 0,
                'rows' => [],
            ];
        }
        // 过滤掉Oracle没有完备点的备份链子
        $chainList = [];
        foreach ($dbData as $row) {
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
            $dbData = array_filter($dbData, function($row) use ($excludeChainUuidList) {
                return !in_array($row['chain_uuid'], $excludeChainUuidList);
            });
        }
        $storageUuidList = array_values(array_unique(array_column($dbData, 'storage_uuid')));
        $storageHandler = new \app\v1\resources\v0\logic\Storage();
        $storageList = $storageHandler->batchGetStorageStatus($storageUuidList);
        return $this->sendResult('', true, 200, [
            'total' => count($storageList),
            'rows' => $storageList,
        ]);
    }
}
