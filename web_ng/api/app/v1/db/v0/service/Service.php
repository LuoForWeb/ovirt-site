<?php

namespace app\v1\db\v0\service;

use app\v1\common\service\Base;
use app\v1\resources\v0\logic\Node;

/**
 * note           数据库 服务通信 service
 * @author       wanggongxi@vinchin.com
 * @date         2023/4/10 10:52
 * @version      1.0.0
 * @copyright    Copyright 2023 vinchin.com
 */
class Service extends Base
{
    /**
     * 统一发送消息到后台  -- 任务相关
     * @param string $taskUuid  任务uuid
     * @param int    $subModule 子模块编号
     * @param string $opName    操作码
     * @param array  $msg       消息
     * @param bool   $sync      是否同步
     * @param bool   $command   是否命令
     * @return array
     */
    public function opUnifyMsg(
        string $taskUuid,
        int $subModule,
        string $opName,
        array $msg,
        $sync = false,
        $command = false
    ) {

        $nodeuuid = (new Node())->getNodeUUIDWithTaskUUID($taskUuid);   //任务UUID对应的存储节点uuid

        return $this->mbDBMsg($nodeuuid, $subModule, $opName, json_encode($msg), $sync, $command);
    }

    /**
     * 加载数据库应用实例的数据库列表的服务
     * @param array $dbData 数据库数据
     * @return array
     */
    public function loadInstanceDatabaseService(array $dbData): array
    {
        $opName = 'DB_INSTANCE_OP_SCAN_DB';
        if ($dbData['db_type'] == xphp_get_config('db', 'DB_TYPE')['ORACLE']) {
            $opName = 'DB_INSTANCE_OP_SCAN_TABLE_SPACE';
        }
        $msg = [
            'agent_uuid' => $dbData['agent_uuid'],
            'db_type' => $dbData['db_type'],
            'instance_name' => $dbData['instance_name'],
            'auth_type' => $dbData['auth_type'],
            'username' => $dbData['username'],
            'password' => $dbData['password'],
        ];

        /**
         * 返回示例
         * {
         *   "result": true,
         *   "msg": {
         *     "table_space_list": ["table_space1", "table_space2"],
         *     "db_list": ["db1", "db2"]
         *   }
         * }
         */
        return $this->mbDBMsg($this->getLocalNodeUuid(), $dbData['db_type'], $opName, json_encode($msg), true);
    }

    /**
     * 加载数据库数据文件服务
     * @param array  $appInfo        应用信息
     * @param string $tableSpaceName 表空间名称
     * @return array
     */
    public function loadDbDataFileService(array $appInfo, string $tableSpaceName): array
    {
        $opName = 'DB_INSTANCE_OP_GET_TABLE_SPACE_DATA_FILE';
        $msg = [
            'agent_uuid' => $appInfo['agent_uuid'],
            'db_type' => $appInfo['app_type'],
            'instance_name' => $appInfo['app_name'],
            'auth_type' => $appInfo['app_auth_type'],
            'username' => $appInfo['app_username'],
            'password' => $appInfo['app_password'],
            'table_space_name' => $tableSpaceName,
        ];
        /**
         * 返回示例
         * {
         *   "result": true,
         *   "msg": {
         *     "data_file_list": [{
         *       "data_file": "/home/oracle/app/oradata/orcl/data_D-ORCL_TS-SYSTEM_FNO-1"
         *     }]
         *   }
         * }
         */
        return $this->mbDBMsg($this->getLocalNodeUuid(), $appInfo['app_type'], $opName, json_encode($msg), true);
    }

    public function parseAndConvertSpfileService(
        int $dbType,
        string $nodeUuid,
        string $spfilePath,
        string $newInstanceName,
        string $oracleHomePath,
        string $oracleBasePath
    ): array {
        $opName = 'DB_INSTANCE_OP_PARSE_AND_CONVERT_SPFILE';
        $msg = [
            'spfile_path' => $spfilePath,
            'new_instance_name' => $newInstanceName,
            'oracle_home_path' => $oracleHomePath,
            'oracle_base_path' => $oracleBasePath,
        ];
        return $this->mbDBMsg($nodeUuid, $dbType, $opName, json_encode($msg), true);
    }

    /**
     * 创建数据库备份任务后台通信服务
     * @param array $msg 消息
     * @return array
     */
    public function createDbBackupJobService(array $msg): array
    {
        $opName = 'BD_TASK_OP_BACKUP_CREATE';
        return $this->mbDBMsg($this->getMasterNodeUuid(), $msg['db_type'], $opName, json_encode($msg), false);
    }

    /**
     * 修改数据库备份任务服务
     * @param array $msg 消息
     * @return array
     */
    public function editDbBackupJobService(array $msg): array
    {
        $opName = 'BD_TASK_OP_BACKUP_MODIFY';
        return $this->mbDBMsg($this->getMasterNodeUuid(), $msg['db_type'], $opName, json_encode($msg), false);
    }

    /**
     * 创建数据库恢复任务服务
     * @param array $msg 消息
     * @param string $nodeUuid 节点uuid
     * @return array
     */
    public function createDbRecoveryJobService(array $msg, string $nodeUuid): array
    {
        $opName = 'BD_TASK_OP_RECOVERY_CREATE';
        if ($msg['recovery_type'] == xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db')['LATEST_TIMEPOINT']) {
            $nodeUuid = $this->getTimerRecoveryNewestNodeUuid($msg['recovery_dbs_info'], $msg['db_type']);
        }
        return $this->mbDBMsg($nodeUuid, $msg['db_type'], $opName, json_encode($msg));
    }

    /**
     * 获取定时恢复最新点的节点uuid
     * 1、判断恢复源选择的实例、数据库是否存在备份任务，如果存在备份任务，那么就传到备份任务所在的节点
     * 2、如果选择的多个数据库存在多个备份任务，且备份任务在不同节点上，那么传到主节点上
     * 3、如果选择的实例、数据库不存在备份任务，那么传到主节点上
     * @param array $sourceList 源列表
     * @param int   $dbType     数据库类型
     * @return mixed
     */
    private function getTimerRecoveryNewestNodeUuid($sourceList, $dbType)
    {
        $sourceAgentUuidList = array_map(function ($row) {
            return $row['source_agent_uuid'];
        }, $sourceList);
        $sourceAgentUuids = "'" . implode("', '", $sourceAgentUuidList) . "'";
        $nodeUuid = $this->getMasterNodeUuid();  // 主节点uuid
        $allDbType = xphp_get_config('db', 'DB_TYPE');

        $instanceNameList = [];
        $dbNameList = [];
        foreach ($sourceList as $sourceInfo) {
            $instanceNameList[] = $sourceInfo['source_instance_name'];
            $dbNameList[] = $sourceInfo['source_db_name'];
        }
        $instanceNames = "'" . implode("', '", $instanceNameList) . "'";
        $dbNames = "'" . implode("', '", $dbNameList) . "'";
        $sql = "SELECT bt.node_uuid
                FROM db_list dl
                    INNER JOIN bd_task bt ON dl.task_uuid = bt.task_uuid
                    INNER JOIN db_task dt ON dl.task_uuid = dt.task_uuid
                WHERE dl.instance_name IN ($instanceNames) AND dl.agent_uuid IN ($sourceAgentUuids) AND dt.db_type = ?
                    AND bt.task_type = ? ";
        switch ($dbType) {
            case $allDbType['SQLSERVER']:
            case $allDbType['SAPHANA']:
                $sql .= " AND dl.db_name IN ($dbNames) ";
                break;
        }
        $data = $this->dbSelect($sql, [$dbType, xphp_get_config('task', 'TASKTYPE')['DB_BACKUP']]);
        if (!is_array($data) || !$data) {
            return $nodeUuid;
        }
        $nodeUuidList = array_column($data, 'node_uuid');
        $nodeUuidList = array_values(array_unique($nodeUuidList));
        if (count($nodeUuidList) === 1) {
            return $nodeUuidList[0];
        }
        return $nodeUuid;
    }

    /**
     * 修改数据库恢复任务服务
     * @param array  $msg      消息
     * @param string $nodeUuid 节点uuid
     * @return array
     */
    public function editDbRecoveryJobService(array $msg, string $nodeUuid): array
    {
        $opName = 'BD_TASK_OP_RECOVERY_MODIFY';
        if ($msg['recovery_type'] == xphp_get_config('db', 'BD_RECOVERY_TYPE', 'db')['LATEST_TIMEPOINT']) {
            $nodeUuid = $this->getTimerRecoveryNewestNodeUuid($msg['recovery_dbs_info'], $msg['db_type']);
        }
        return $this->mbDBMsg($nodeUuid, $msg['db_type'], $opName, json_encode($msg));
    }

    /**
     * 删除数据库备份点
     * @param array  $timePointUuidList 备份点uuid列表
     * @param string $nodeUuid          备份节点uuid
     * @param int    $dbType            数据库类别
     * @return array
     */
    public function deleteDbBackupTimePointService(array $timePointUuidList, string $nodeUuid, int $dbType): array
    {
        $opName = 'BD_BACKUP_POINT_OP_BATCH_DELETE';
        $msg = [
            'timepoint_uuids' => $timePointUuidList,
        ];
        return $this->mbDBMsg($nodeUuid, $dbType, $opName, json_encode($msg));
    }

    /**
     * 启动数据库备份任务信息
     * @param string $jobsUuid   任务uuid
     * @param int    $dbType     数据库类被
     * @param int    $backupMode 备份模式【1完全备份 2增量 3差异 4日志/归档日志】
     * @param bool   $crosscheck crosscheck
     * @param array  $dbList     启动任务的数据库信息
     * @return array
     */
    public function startBackupJobService(
        string $jobsUuid,
        int $dbType,
        int $backupMode,
        bool $crosscheck,
        array $dbList
    ): array {
        $opcodeName = 'BD_TASK_OP_BACKUP_START';
        $msg = [
            'task_uuid' => $jobsUuid,
            'backup_mode' => $backupMode,
            'time_strategy_id' => 0,
            'auto_start_flag' => xphp_get_config('app', 'FLAG')['UNSET'],
            'choose_db_list' => array_map(function ($row) {
                return [
                    'agent_uuid' => $row['agent_uuid'],
                    'db_uuid' => $row['db_uuid'],
                ];
            }, $dbList),
            'crosscheck_flag' => v1_parse_bool_to_flag($crosscheck),
        ];
        $nodeHandler = new Node();
        $nodeUuid = $nodeHandler->getNodeUUIDWithTaskUUID($jobsUuid);
        return $this->mbDBMsg($nodeUuid, $dbType, $opcodeName, json_encode($msg), false, true);
    }

    /**
     * 根据恢复时间获取Oracle恢复所使用的备份链服务
     * @param array  $sourceInstanceInfoList 源实例信息列表
     * @param int    $dbIncarnation          恢复分支
     * @param int    $resetlogsTime          重置日志时间
     * @param string $jobUuid                备份任务(主任务)的uuid
     * @param int    $recoveryTime           恢复时间（Unix 时间戳）
     * @param string $opcodeName             操作码名称
     * @param string $storageUuid            存储uuid
     * @return array
     */
    public function getOracleTimepointChainRecoveryTimeService(
        array $sourceInstanceInfoList,
        int $dbIncarnation,
        int $resetlogsTime,
        string $jobUuid,
        int $recoveryTime,
        string $opcodeName,
        string $storageUuid
    ): array {
        $msg = [
            'source_instance_info_list' => $sourceInstanceInfoList,
            'db_incarnation' => $dbIncarnation,
            'resetlogs_time' => $resetlogsTime,
            'task_uuid' => $jobUuid,
            'recovery_time' => $recoveryTime,
            'storage_uuid' => $storageUuid,
        ];
        return $this->mbDBMsg(
            $this->getMasterNodeUuid(),
            xphp_get_config('db', 'DB_TYPE')['ORACLE'],
            $opcodeName,
            json_encode($msg),
            true
        );
    }
}
